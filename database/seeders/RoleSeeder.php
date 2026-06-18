<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Добавление ролей в бд.
     */
    public function run(): void
    {
        // Создаем стандартные роли
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'moderator']);
        Role::firstOrCreate(['name' => 'customer']);
        Role::firstOrCreate(['name' => 'loader']);
        Role::firstOrCreate(['name' => 'driver']);
    }
}
