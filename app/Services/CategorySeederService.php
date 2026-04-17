<?php
namespace App\Services;
use App\Models\Category;

class CategorySeederService
{
    public static function defaultCategories(): array
    {
        return [
            'H4' => [
                'Bullish BOS','Bearish BOS','Bullish CHoCH','Bearish CHoCH',
                'Bullish OB','Bearish OB','FVG Bullish','FVG Bearish',
                'EQH','EQL','Liquidity Sweep High','Liquidity Sweep Low',
                'Premium Zone','Discount Zone','Bullish MSS','Bearish MSS',
            ],
            'M15' => [
                'Bullish BOS','Bearish BOS','Bullish CHoCH','Bearish CHoCH',
                'Bullish OB','Bearish OB','FVG Bullish','FVG Bearish',
                'Inducement','Rejection Block','NWOG','NDOG',
                'Bullish Breaker','Bearish Breaker',
            ],
            'M1' => [
                'M1 Bullish BOS','M1 Bearish BOS','M1 CHoCH',
                'M1 OB Entry','M1 FVG Entry','M1 Rejection','M1 MSS',
                'M1 Volume Imbalance','M1 Breaker Block','M1 Liquidity Grab',
            ],
        ];
    }

    public static function seedForUser(int $userId): void
    {
        if (Category::where('user_id', $userId)->exists()) return;

        foreach (self::defaultCategories() as $tf => $names) {
            foreach ($names as $name) {
                Category::create([
                    'user_id'   => $userId,
                    'timeframe' => $tf,
                    'name'      => $name,
                    'trade_count' => 0,
                ]);
            }
        }
    }
}
