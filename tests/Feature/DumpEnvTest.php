<?php

namespace Tests\Feature;

use Tests\TestCase;

class DumpEnvTest extends TestCase
{
    public function test_env()
    {
        var_dump(env('DB_CONNECTION'));
        $this->assertTrue(true);
    }
}
