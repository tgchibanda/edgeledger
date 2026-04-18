<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class TradingSessionSeeder extends Seeder {
    public function run(): void {
        DB::table('trading_sessions')->insertOrIgnore([
            ['name'=>'London',    'open_time'=>'08:00:00','close_time'=>'16:00:00','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'New York',  'open_time'=>'13:00:00','close_time'=>'21:00:00','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Asia',      'open_time'=>'00:00:00','close_time'=>'08:00:00','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'London/NY', 'open_time'=>'13:00:00','close_time'=>'16:00:00','created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
