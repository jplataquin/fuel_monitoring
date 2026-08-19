<?php

namespace App\Console\Commands;

use Dotenv\Dotenv;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;

class ReplicateProductionDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:replicate-production
                        {--prod-host= : Production database host}
                        {--prod-port= : Production database port}
                        {--prod-db= : Production database name}
                        {--prod-user= : Production database username}
                        {--prod-pass= : Production database password}
                        {--prod-connection= : Use an existing connection name configured in database.php}
                        {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replicate the production database schema and data to the local/development database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $localConn = config('database.default');
        $localConfig = config("database.connections.{$localConn}");
        $localDatabase = $localConfig['database'] ?? '';

        if (! $this->option('force')) {
            if (! $this->confirm("Are you sure you want to replicate the production database? This will OVERWRITE your local database '{$localDatabase}'.", false)) {
                $this->info('Replication cancelled.');

                return 1;
            }
        }

        $prodConnectionName = $this->option('prod-connection');
        $credentials = [];

        if ($prodConnectionName) {
            if (! config("database.connections.{$prodConnectionName}")) {
                $this->error("Connection '{$prodConnectionName}' is not defined in database configuration.");

                return 1;
            }
            $prodConfig = config("database.connections.{$prodConnectionName}");
            $credentials = [
                'host' => $prodConfig['host'] ?? '127.0.0.1',
                'port' => $prodConfig['port'] ?? '3306',
                'database' => $prodConfig['database'] ?? '',
                'username' => $prodConfig['username'] ?? '',
                'password' => $prodConfig['password'] ?? '',
            ];
        } else {
            $prodConnectionName = 'production_replica';
            $credentials = $this->getProductionCredentials();

            if (empty($credentials['database'])) {
                $credentials['host'] = $this->ask('Enter Production Database Host', $credentials['host'] ?: '127.0.0.1');
                $credentials['port'] = $this->ask('Enter Production Database Port', $credentials['port'] ?: '3306');
                $credentials['database'] = $this->ask('Enter Production Database Name');
                $credentials['username'] = $this->ask('Enter Production Database Username', $credentials['username'] ?: 'root');
                $credentials['password'] = $this->secret('Enter Production Database Password') ?: '';
            }

            if (empty($credentials['database'])) {
                $this->error('Production database name is required.');

                return 1;
            }

            config(["database.connections.{$prodConnectionName}" => [
                'driver' => 'mysql',
                'host' => $credentials['host'],
                'port' => $credentials['port'],
                'database' => $credentials['database'],
                'username' => $credentials['username'],
                'password' => $credentials['password'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
            ]]);
        }

        // Validate production connection
        try {
            DB::connection($prodConnectionName)->getPdo();
        } catch (\Exception $e) {
            $this->error('Could not connect to the production database: '.$e->getMessage());

            return 1;
        }

        $prodDriver = DB::connection($prodConnectionName)->getDriverName();
        $localDriver = DB::connection($localConn)->getDriverName();

        $useCli = ($prodDriver === 'mysql' && $localDriver === 'mysql');

        if ($useCli) {
            if ($this->replicateViaCli($credentials, $localConfig)) {
                $this->showReplicationSummary($localConn);

                return 0;
            }
            $this->warn('CLI replication failed or is not available. Falling back to PHP-based replication...');
        }

        $exitCode = $this->replicateViaPhp($prodConnectionName, $localConn, $prodDriver, $localDriver);

        if ($exitCode === 0) {
            $this->showReplicationSummary($localConn);
        }

        return $exitCode;
    }

    /**
     * Get production database credentials from inputs, env, or .env.production file.
     */
    protected function getProductionCredentials(): array
    {
        $credentials = [
            'host' => $this->option('prod-host') ?: env('PROD_DB_HOST'),
            'port' => $this->option('prod-port') ?: env('PROD_DB_PORT', '3306'),
            'database' => $this->option('prod-db') ?: env('PROD_DB_DATABASE'),
            'username' => $this->option('prod-user') ?: env('PROD_DB_USERNAME'),
            'password' => $this->option('prod-pass') ?: env('PROD_DB_PASSWORD'),
        ];

        if (file_exists(base_path('.env.production'))) {
            try {
                $content = file_get_contents(base_path('.env.production'));
                if (class_exists('\Dotenv\Dotenv')) {
                    $parsed = Dotenv::parse($content);
                    $credentials['host'] = $credentials['host'] ?: ($parsed['DB_HOST'] ?? null);
                    $credentials['port'] = $credentials['port'] ?: ($parsed['DB_PORT'] ?? '3306');
                    $credentials['database'] = $credentials['database'] ?: ($parsed['DB_DATABASE'] ?? null);
                    $credentials['username'] = $credentials['username'] ?: ($parsed['DB_USERNAME'] ?? null);
                    $credentials['password'] = $credentials['password'] ?: ($parsed['DB_PASSWORD'] ?? null);
                }
            } catch (\Exception $e) {
                $this->warn('Failed parsing .env.production: '.$e->getMessage());
            }
        }

        return $credentials;
    }

    /**
     * Replicate database using mysqldump and mysql CLI tools.
     */
    protected function replicateViaCli(array $prodConfig, array $localConfig): bool
    {
        $tempSqlPath = storage_path('app/temp_prod_replica.sql');

        $this->info('Exporting production database schema and data via mysqldump...');

        $ignoredTables = ['migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'sessions', 'failed_jobs'];
        $ignoreArgs = '';
        foreach ($ignoredTables as $table) {
            $ignoreArgs .= ' --ignore-table='.escapeshellarg(($prodConfig['database'] ?? '').'.'.$table);
        }

        $dumpCmd = sprintf(
            'mysqldump --no-tablespaces --set-gtid-purged=OFF -h %s -P %s -u %s%s %s',
            escapeshellarg($prodConfig['host'] ?? '127.0.0.1'),
            escapeshellarg($prodConfig['port'] ?? '3306'),
            escapeshellarg($prodConfig['username'] ?? 'root'),
            $ignoreArgs,
            escapeshellarg($prodConfig['database'] ?? '')
        );

        // Run export command
        $dumpResult = Process::env([
            'MYSQL_PWD' => $prodConfig['password'] ?? '',
        ])->run('sh -c '.escapeshellarg($dumpCmd.' > '.escapeshellarg($tempSqlPath)));

        if (! $dumpResult->successful()) {
            $this->warn('mysqldump failed: '.$dumpResult->errorOutput());
            if (file_exists($tempSqlPath)) {
                unlink($tempSqlPath);
            }

            return false;
        }

        $this->info('Importing database into local development database via mysql CLI...');

        $importCmd = sprintf(
            'mysql -h %s -P %s -u %s %s',
            escapeshellarg($localConfig['host'] ?? '127.0.0.1'),
            escapeshellarg($localConfig['port'] ?? '3306'),
            escapeshellarg($localConfig['username'] ?? 'root'),
            escapeshellarg($localConfig['database'] ?? '')
        );

        // Run import command
        $importResult = Process::env([
            'MYSQL_PWD' => $localConfig['password'] ?? '',
        ])->run('sh -c '.escapeshellarg($importCmd.' < '.escapeshellarg($tempSqlPath)));

        // Clean up temp file
        if (file_exists($tempSqlPath)) {
            unlink($tempSqlPath);
        }

        if (! $importResult->successful()) {
            $this->warn('mysql import failed: '.$importResult->errorOutput());

            return false;
        }

        $this->info('Database replication completed successfully via CLI!');

        return true;
    }

    /**
     * Replicate database using PHP PDO and SQL queries.
     */
    protected function replicateViaPhp(string $prodConn, string $localConn, string $prodDriver, string $localDriver): int
    {
        $this->info('Executing PHP-based schema and data replication...');

        // 1. Disable foreign key checks
        if ($localDriver === 'sqlite') {
            DB::connection($localConn)->statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::connection($localConn)->statement('SET FOREIGN_KEY_CHECKS = 0;');
        }

        try {
            // 2. Get list of tables
            $baseTables = [];
            $views = [];
            $ignoredTables = ['migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'sessions', 'failed_jobs'];

            if ($prodDriver === 'sqlite') {
                $tables = DB::connection($prodConn)->select("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') AND name NOT LIKE 'sqlite_%'");
                foreach ($tables as $table) {
                    if (in_array(strtolower($table->name), $ignoredTables)) {
                        continue;
                    }
                    if ($table->type === 'view') {
                        $views[] = $table->name;
                    } else {
                        $baseTables[] = $table->name;
                    }
                }
            } else {
                $tables = DB::connection($prodConn)->select('SHOW FULL TABLES');
                foreach ($tables as $table) {
                    $tableArray = (array) $table;
                    $tableName = array_values($tableArray)[0];
                    if (in_array(strtolower($tableName), $ignoredTables)) {
                        continue;
                    }
                    $tableType = $tableArray['Table_type'] ?? 'BASE TABLE';

                    if ($tableType === 'VIEW') {
                        $views[] = $tableName;
                    } else {
                        $baseTables[] = $tableName;
                    }
                }
            }

            $this->info('Replicating '.count($baseTables).' base tables...');

            // 3. Replicate base tables
            foreach ($baseTables as $tableName) {
                // Drop local table if exists
                Schema::connection($localConn)->dropIfExists($tableName);

                // Get CREATE TABLE statement
                $createSql = null;
                if ($prodDriver === 'sqlite') {
                    $createResult = DB::connection($prodConn)->select('SELECT sql FROM sqlite_master WHERE name = ?', [$tableName]);
                    $createSql = $createResult[0]->sql ?? null;
                } else {
                    $createResult = DB::connection($prodConn)->select("SHOW CREATE TABLE `{$tableName}`");
                    $createArray = (array) $createResult[0];
                    $createSql = $createArray['Create Table'] ?? null;
                }

                if (! $createSql) {
                    $this->error("Failed to get schema for table {$tableName}");

                    continue;
                }

                // Run CREATE TABLE on local connection
                DB::connection($localConn)->statement($createSql);

                // Copy table data in chunks
                $count = DB::connection($prodConn)->table($tableName)->count();
                $this->info("Copying {$count} rows from {$tableName}...");

                $chunkSize = 1000;
                $bar = $this->output->createProgressBar($count);
                $bar->start();

                for ($offset = 0; $offset < $count; $offset += $chunkSize) {
                    $rows = DB::connection($prodConn)
                        ->table($tableName)
                        ->offset($offset)
                        ->limit($chunkSize)
                        ->get();

                    $data = $rows->map(fn ($row) => (array) $row)->toArray();

                    if (! empty($data)) {
                        DB::connection($localConn)->table($tableName)->insert($data);
                    }

                    $bar->advance(count($data));
                }

                $bar->finish();
                $this->newLine();
            }

            // 4. Replicate views
            if (! empty($views)) {
                $this->info('Replicating '.count($views).' views...');
                foreach ($views as $viewName) {
                    // Drop local view if exists
                    if ($localDriver === 'sqlite') {
                        DB::connection($localConn)->statement("DROP VIEW IF EXISTS `{$viewName}`");
                    } else {
                        DB::connection($localConn)->statement("DROP VIEW IF EXISTS `{$viewName}`");
                    }

                    // Get CREATE VIEW statement
                    $createSql = null;
                    if ($prodDriver === 'sqlite') {
                        $createResult = DB::connection($prodConn)->select('SELECT sql FROM sqlite_master WHERE name = ?', [$viewName]);
                        $createSql = $createResult[0]->sql ?? null;
                    } else {
                        $createResult = DB::connection($prodConn)->select("SHOW CREATE VIEW `{$viewName}`");
                        $createArray = (array) $createResult[0];
                        $createSql = $createArray['Create View'] ?? null;
                    }

                    if ($createSql) {
                        DB::connection($localConn)->statement($createSql);
                    } else {
                        $this->error("Failed to get schema for view {$viewName}");
                    }
                }
            }

            $this->info('Database replication completed successfully via PHP!');

            return 0;

        } catch (\Exception $e) {
            $this->error('An error occurred during PHP-based replication: '.$e->getMessage());

            return 1;
        } finally {
            // 5. Re-enable foreign key checks
            if ($localDriver === 'sqlite') {
                DB::connection($localConn)->statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::connection($localConn)->statement('SET FOREIGN_KEY_CHECKS = 1;');
            }
        }
    }

    /**
     * Show a beautiful summary table of all replicated tables and their row counts.
     */
    protected function showReplicationSummary(string $connectionName)
    {
        $this->newLine();
        $this->info('=== Replication Summary ===');

        $driver = DB::connection($connectionName)->getDriverName();
        $ignoredTables = ['migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'sessions', 'failed_jobs'];
        $rows = [];

        try {
            if ($driver === 'sqlite') {
                $tables = DB::connection($connectionName)->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                foreach ($tables as $table) {
                    if (in_array(strtolower($table->name), $ignoredTables)) {
                        continue;
                    }
                    $count = DB::connection($connectionName)->table($table->name)->count();
                    $rows[] = [$table->name, $count, 'Base Table'];
                }

                $views = DB::connection($connectionName)->select("SELECT name FROM sqlite_master WHERE type='view' AND name NOT LIKE 'sqlite_%'");
                foreach ($views as $view) {
                    if (in_array(strtolower($view->name), $ignoredTables)) {
                        continue;
                    }
                    $rows[] = [$view->name, '-', 'View'];
                }
            } else {
                $tablesResult = DB::connection($connectionName)->select('SHOW FULL TABLES');
                foreach ($tablesResult as $table) {
                    $tableArray = (array) $table;
                    $tableName = array_values($tableArray)[0];
                    if (in_array(strtolower($tableName), $ignoredTables)) {
                        continue;
                    }
                    $tableType = $tableArray['Table_type'] ?? 'BASE TABLE';

                    if ($tableType === 'VIEW') {
                        $rows[] = [$tableName, '-', 'View'];
                    } else {
                        $count = DB::connection($connectionName)->table($tableName)->count();
                        $rows[] = [$tableName, $count, 'Base Table'];
                    }
                }
            }

            if (empty($rows)) {
                $this->warn('No tables or views found in the database.');

                return;
            }

            // Sort alphabetically by table name
            usort($rows, fn ($a, $b) => strcmp($a[0], $b[0]));

            $this->table(['Table Name', 'Row Count', 'Type'], $rows);
            $this->info('Total replicated objects: '.count($rows));

        } catch (\Exception $e) {
            $this->warn('Could not generate replication summary: '.$e->getMessage());
        }
    }
}
