<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        $this->call([TradingSessionSeeder::class, SuperuserSeeder::class, SubscriptionPlanSeeder::class]);
    }
}
