<?php
namespace Database\Seeders;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        if (!SubscriptionPlan::exists()) {
            SubscriptionPlan::create([
                'name'     => 'EdgeLedger Pro',
                'price'    => 2.00,
                'currency' => 'USD',
                'interval' => 'month',
                'is_active'=> true,
                'features' => [
                    'Full trade database — unlimited trades',
                    'Pre-trade cascade filter',
                    'Win rate analytics by setup',
                    'Live MTF structure scanner',
                    'Trading journal',
                    'Training image dataset builder',
                ],
            ]);
        }
    }
}
