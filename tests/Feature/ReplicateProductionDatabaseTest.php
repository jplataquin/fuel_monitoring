<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReplicateProductionDatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register the production_replica connection dynamically as an in-memory SQLite DB
        config(['database.connections.production_replica' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]]);
    }

    public function test_replication_cancelled_without_force_when_declined()
    {
        // Assert that calling without --force and choosing "no" cancels replication
        $this->artisan('db:replicate-production')
            ->expectsConfirmation('Are you sure you want to replicate the production database? This will OVERWRITE your local database \':memory:\'.', 'no')
            ->expectsOutput('Replication cancelled.')
            ->assertExitCode(1);
    }

    public function test_replication_copies_tables_and_views_and_data()
    {
        // 1. Create a table and insert some data in the mock production database
        $prodConn = DB::connection('production_replica');
        
        $prodConn->statement('CREATE TABLE dummy_products (id INTEGER PRIMARY KEY, name TEXT, price REAL)');
        $prodConn->table('dummy_products')->insert([
            ['id' => 1, 'name' => 'Laptop', 'price' => 999.99],
            ['id' => 2, 'name' => 'Phone', 'price' => 499.99],
        ]);

        // 2. Create a view in the mock production database
        $prodConn->statement('CREATE VIEW expensive_products AS SELECT * FROM dummy_products WHERE price > 500');

        // 3. Ensure target/local database doesn't have the table or view yet
        $this->assertFalse(Schema::hasTable('dummy_products'));

        // 4. Run replication command with force and pointing to the mock production connection
        $this->artisan('db:replicate-production', [
            '--prod-connection' => 'production_replica',
            '--force' => true,
        ])
        ->expectsOutput('Executing PHP-based schema and data replication...')
        ->expectsOutput('Replicating 1 base tables...')
        ->expectsOutput('Copying 2 rows from dummy_products...')
        ->expectsOutput('Replicating 1 views...')
        ->expectsOutput('Database replication completed successfully via PHP!')
        ->expectsOutput('=== Replication Summary ===')
        ->expectsOutput('Total replicated objects: 2')
        ->assertExitCode(0);

        // 5. Verify the table and data were copied to the local/test database
        $this->assertTrue(Schema::hasTable('dummy_products'));
        $this->assertDatabaseHas('dummy_products', [
            'id' => 1,
            'name' => 'Laptop',
            'price' => 999.99,
        ]);
        $this->assertDatabaseHas('dummy_products', [
            'id' => 2,
            'name' => 'Phone',
            'price' => 499.99,
        ]);

        // 6. Verify the view was also copied
        $expensive = DB::connection()->table('expensive_products')->get();
        $this->assertCount(1, $expensive);
        $this->assertEquals('Laptop', $expensive->first()->name);
    }

    public function test_replication_excludes_framework_tables()
    {
        // 1. Create dummy product table and framework tables in production_replica
        $prodConn = DB::connection('production_replica');
        $prodConn->statement('CREATE TABLE dummy_products (id INTEGER PRIMARY KEY, name TEXT, price REAL)');
        
        $frameworkTables = ['migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'sessions', 'failed_jobs'];
        foreach ($frameworkTables as $table) {
            $prodConn->statement("CREATE TABLE {$table} (id INTEGER PRIMARY KEY, payload TEXT)");
        }

        // 2. Ensure none of these exist in target/local yet
        $this->assertFalse(Schema::hasTable('dummy_products'));
        foreach ($frameworkTables as $table) {
            $this->assertFalse(Schema::hasTable($table));
        }

        // 3. Run replication command
        $this->artisan('db:replicate-production', [
            '--prod-connection' => 'production_replica',
            '--force' => true,
        ])
        ->assertExitCode(0);

        // 4. Verify dummy_products was copied
        $this->assertTrue(Schema::hasTable('dummy_products'));

        // 5. Verify ALL framework tables were excluded/not copied
        foreach ($frameworkTables as $table) {
            $this->assertFalse(Schema::hasTable($table));
        }
    }
}
