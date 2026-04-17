<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TradingSession;

class TradingSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TradingSession::insert([
            ['name' => 'London',   'open_time' => '08:00:00', 'close_time' => '16:00:00'],
            ['name' => 'New York', 'open_time' => '13:00:00', 'close_time' => '21:00:00'],
            ['name' => 'Asia',     'open_time' => '00:00:00', 'close_time' => '08:00:00'],
        ]);
    }
}
