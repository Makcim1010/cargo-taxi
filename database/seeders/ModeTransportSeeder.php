<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ModeTransport;

class ModeTransportSeeder extends Seeder
{
    /**
     * Добавление вид техники в бд.
     */
    public function run(): void
    {
        // Создаем стандартные виды техники
        ModeTransport::firstOrCreate(['name' => 'Газель']);
        ModeTransport::firstOrCreate(['name' => '5-тонник']);
        ModeTransport::firstOrCreate(['name' => 'Трактор']);
        ModeTransport::firstOrCreate(['name' => 'Манипулятор']);
        ModeTransport::firstOrCreate(['name' => 'Трал']);
        ModeTransport::firstOrCreate(['name' => 'Эвакуатор']);
        ModeTransport::firstOrCreate(['name' => 'Автовышка']);
        ModeTransport::firstOrCreate(['name' => 'Экскаватор']);
        ModeTransport::firstOrCreate(['name' => 'Самосвал']);
    }
}
