<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candle;
use Illuminate\Http\Request;

class CandleController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'pair'      => 'required|in:EURUSD,GBPUSD,AUDUSD',
            'timeframe' => 'required|in:M1,M5,M15,M30,H1,H4,D1',
            'from'      => 'nullable|date',
            'to'        => 'nullable|date',
            'limit'     => 'nullable|integer|min:1|max:5000',
        ]);

        $q = Candle::where('pair',      $request->pair)
                   ->where('timeframe', $request->timeframe)
                   ->orderBy('timestamp', 'asc');

        if ($request->from) $q->where('timestamp', '>=', $request->from);
        if ($request->to)   $q->where('timestamp', '<=', $request->to);

        $limit = $request->limit ?? 500;
        $candles = $q->limit($limit)->get();

        // Return lightweight array for chart consumption
        return response()->json($candles->map(fn($c) => [
            'time'   => $c->timestamp->timestamp,   // Unix timestamp
            'open'   => $c->open,
            'high'   => $c->high,
            'low'    => $c->low,
            'close'  => $c->close,
            'volume' => $c->volume,
        ]));
    }

    // Returns earliest and latest available dates for a pair+timeframe
    public function range(Request $request)
    {
        $request->validate([
            'pair'      => 'required|in:EURUSD,GBPUSD,AUDUSD',
            'timeframe' => 'required|in:M1,M5,M15,M30,H1,H4,D1',
        ]);

        $first = Candle::where('pair', $request->pair)
            ->where('timeframe', $request->timeframe)
            ->orderBy('timestamp','asc')->first();
        $last  = Candle::where('pair', $request->pair)
            ->where('timeframe', $request->timeframe)
            ->orderBy('timestamp','desc')->first();

        return response()->json([
            'first' => $first?->timestamp,
            'last'  => $last?->timestamp,
            'count' => Candle::where('pair',$request->pair)
                              ->where('timeframe',$request->timeframe)->count(),
        ]);
    }
}
