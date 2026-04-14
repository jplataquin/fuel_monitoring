<?php

namespace Database\Seeders;

use App\Models\AssetType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Initial Administrator
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@fuel.com',
            'password' => Hash::make('password'),
            'role' => 'administrator',
            'is_temporary_password' => false,
        ]);

        // Sample Asset Types
        AssetType::create(['name' => 'Sedan']);
        AssetType::create(['name' => 'SUV']);
        AssetType::create(['name' => 'Truck']);
        AssetType::create(['name' => 'Van']);
        AssetType::create(['name' => 'Excavator']);
    }
}
