<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ModeTransport;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Указываем Laravel, какие сидеры нужно запустить по очереди
        $this->call([
            RoleSeeder::class,
            ModeTransportSeeder::class,
        ]);
    }
}
