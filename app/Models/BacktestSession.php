<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BacktestSession extends Model
{
    protected $fillable = [
        'user_id','name','pair_id','timeframe','description',
        'date_from','date_to','starting_balance','status',
    ];
    protected $casts = ['date_from' => 'date', 'date_to' => 'date', 'starting_balance' => 'float'];

    public function user()   { return $this->belongsTo(User::class); }
    public function pair()   { return $this->belongsTo(Pair::class); }
    public function trades() { return $this->hasMany(BacktestTrade::class); }

    public function stats(): array
    {
        $trades = $this->trades;
        $total  = $trades->count();

        if (!$total) {
            return [
                'total'         => 0, 'wins'     => 0, 'losses'  => 0,
                'be'            => 0, 'win_rate' => 0, 'total_r' => 0,
                'avg_r'         => 0, 'expectancy' => 0,
                'profit_factor' => 0, 'disciplined' => 0, 'impulsive' => 0,
            ];
        }

        $wins   = $trades->where('result', 'win')->count();
        $losses = $trades->where('result', 'loss')->count();
        $be     = $trades->where('result', 'breakeven')->count();

        $winR   = (float) $trades->where('result', 'win')->sum('r_multiple');
        $lossR  = abs((float) $trades->where('result', 'loss')->sum('r_multiple'));

        $totalR       = (float) $trades->sum('r_multiple');
        $avgR         = round($totalR / $total, 2);
        $winRate      = round(($wins / $total) * 100, 1);
        $expectancy   = round(($winR - $lossR) / $total, 3);
        $profitFactor = $lossR > 0 ? round($winR / $lossR, 2) : ($winR > 0 ? 99.0 : 0.0);
        $disciplined  = $trades->where('followed_rules', true)->count();
        $impulsive    = $trades->where('followed_rules', false)->count();

        // Return explicit array — no compact() to avoid undefined variable errors
        return [
            'total'         => $total,
            'wins'          => $wins,
            'losses'        => $losses,
            'be'            => $be,
            'win_rate'      => $winRate,
            'total_r'       => round($totalR, 2),
            'avg_r'         => $avgR,
            'expectancy'    => $expectancy,
            'profit_factor' => $profitFactor,
            'disciplined'   => $disciplined,
            'impulsive'     => $impulsive,
        ];
    }
}