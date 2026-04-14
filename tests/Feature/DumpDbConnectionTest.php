<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class DumpDbConnectionTest extends TestCase
{
    public function test_dump_connection()
    {
        $this->assertTrue(true);
        var_dump(config('database.default'));
        var_dump(env('DB_CONNECTION'));
        var_dump(DB::connection()->getDatabaseName());
    }
}
