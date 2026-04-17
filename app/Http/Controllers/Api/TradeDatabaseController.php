<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TradeDatabase;
use App\Models\TradeImage;

class TradeDatabaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        foreach ($request->file('images', []) as $timeframe => $file) {
            $path = $file->store("trades/{$trade->id}/{$timeframe}", 'local');
            TradeImage::create([
                'trade_database_id' => $trade->id,
                'timeframe' => $timeframe,
                'path' => $path
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function filter(Request $request)
    {
        $query = TradeDatabase::with(['h4', 'm15', 'm1', 'pair', 'session', 'images'])
            ->where('user_id', auth()->id())
            ->where('is_valid', true);

        if ($request->h4_category_id) $query->where('h4_category_id', $request->h4_category_id);
        if ($request->m15_category_id) $query->where('m15_category_id', $request->m15_category_id);
        if ($request->m1_category_id) $query->where('m1_category_id', $request->m1_category_id);
        if ($request->pair_id) $query->where('pair_id', $request->pair_id);
        if ($request->trading_session_id) $query->where('trading_session_id', $request->trading_session_id);

        $trades = $query->get();

        $total = $trades->count();
        $wins = $trades->where('result', 'win')->count();
        $winRate = $total > 0 ? round(($wins / $total) * 100, 1) : 0;

        $byEntry = $trades->groupBy('entry_technique')->map(function ($group) {
            $t = $group->count();
            $w = $group->where('result', 'win')->count();
            return [
                'entry_technique' => $group->first()->entry_technique,
                'total' => $t,
                'wins' => $w,
                'win_rate' => $t > 0 ? round(($w / $t) * 100, 1) : 0
            ];
        })->values();

        return response()->json(['trades' => $trades, 'stats' => [
            'total' => $total,
            'wins' => $wins,
            'win_rate' => $winRate,
            'by_entry' => $byEntry,
        ]]);
    }
}
