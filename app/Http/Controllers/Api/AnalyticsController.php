<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TradeDatabase;

class AnalyticsController extends Controller
{
    public function winRates()
{
    $trades = TradeDatabase::where('user_id', auth()->id())
        ->where('is_valid', true)
        ->with(['h4','m15','m1','pair','session'])
        ->get();

    $grouped = $trades->groupBy(fn($t) =>
        $t->h4_category_id.'-'.$t->m15_category_id.'-'.$t->m1_category_id.'-'.$t->entry_technique
    );

    $rows = $grouped->map(function ($group) {
        $first = $group->first();
        $total = $group->count();
        $wins = $group->where('result', 'win')->count();
        return [
            'h4' => $first->h4->name ?? '-',
            'm15' => $first->m15->name ?? '-',
            'm1' => $first->m1->name ?? '-',
            'entry_technique' => $first->entry_technique,
            'total' => $total,
            'wins' => $wins,
            'losses' => $total - $wins,
            'win_rate' => round(($wins / $total) * 100, 1),
        ];
    })->values();

    return response()->json($rows);
}
}
