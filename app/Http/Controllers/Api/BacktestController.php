<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BacktestSession;
use App\Models\BacktestTrade;
use App\Models\BacktestTradeImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BacktestController extends Controller
{
    // ── SESSIONS ──────────────────────────────────────────────────

    public function sessions(Request $request)
    {
        $sessions = BacktestSession::with('pair')
            ->where('user_id', $request->user()->id)
            ->withCount('trades')
            ->latest()
            ->get();

        $result = $sessions->map(function ($s) {
            $arr = $s->toArray();
            $arr['stats'] = $s->stats();
            return $arr;
        })->values(); // values() ensures JSON array not object

        return response()->json($result);
    }

    public function createSession(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:150',
            'pair_id'          => 'required|exists:pairs,id',
            'timeframe'        => 'required|in:M1,M5,M15,M30,H1,H4,D1',
            'description'      => 'nullable|string|max:1000',
            'date_from'        => 'nullable|date',
            'date_to'          => 'nullable|date',
            'starting_balance' => 'nullable|numeric|min:0',
        ]);
        $data['user_id'] = $request->user()->id;
        $session = BacktestSession::create($data);
        $arr = $session->load('pair')->toArray();
        $arr['stats'] = $session->stats();
        return response()->json($arr, 201);
    }

    public function updateSession(Request $request, BacktestSession $backtestSession)
    {
        $this->gate($request, $backtestSession);
        $data = $request->validate([
            'name'        => 'sometimes|string|max:150',
            'description' => 'nullable|string|max:1000',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date',
            'status'      => 'sometimes|in:active,completed',
        ]);
        $backtestSession->update($data);
        $arr = $backtestSession->fresh('pair')->toArray();
        $arr['stats'] = $backtestSession->fresh()->stats();
        return response()->json($arr);
    }

    public function deleteSession(Request $request, BacktestSession $backtestSession)
    {
        $this->gate($request, $backtestSession);
        foreach ($backtestSession->trades as $trade) {
            foreach ($trade->images as $img) Storage::disk('local')->delete($img->path);
        }
        $backtestSession->delete();
        return response()->json(['message' => 'Session deleted.']);
    }

    public function sessionStats(Request $request, BacktestSession $backtestSession)
    {
        $this->gate($request, $backtestSession);
        $backtestSession->load(['pair', 'trades.h4', 'trades.m15', 'trades.m1']);

        $bySetup = $backtestSession->trades
            ->groupBy(function ($t) {
                return implode(' → ', array_filter([
                    $t->h4?->name, $t->m15?->name, $t->m1?->name
                ])) ?: 'No setup';
            })
            ->map(function ($group) {
                $total = $group->count();
                $wins  = $group->where('result', 'win')->count();
                return [
                    'total'    => $total,
                    'wins'     => $wins,
                    'win_rate' => $total ? round($wins / $total * 100, 1) : 0,
                    'avg_r'    => round($group->avg('r_multiple') ?? 0, 2),
                ];
            });

        return response()->json([
            'session'  => $backtestSession,
            'stats'    => $backtestSession->stats(),
            'by_setup' => $bySetup,
        ]);
    }

    // ── TRADES ────────────────────────────────────────────────────

    public function trades(Request $request, BacktestSession $backtestSession)
    {
        $this->gate($request, $backtestSession);
        return response()->json(
            $backtestSession->trades()
                ->with(['pair', 'h4', 'm15', 'm1', 'images'])
                ->latest()
                ->get()
        );
    }

    public function storeTrade(Request $request, BacktestSession $backtestSession)
    {
        $this->gate($request, $backtestSession);
        $data = $request->validate([
            'pair_id'         => 'nullable|exists:pairs,id',
            'h4_category_id'  => 'nullable|exists:categories,id',
            'm15_category_id' => 'nullable|exists:categories,id',
            'm1_category_id'  => 'nullable|exists:categories,id',
            'entry_technique' => 'nullable|string|max:150',
            'result'          => 'required|in:win,loss,breakeven',
            'pips_result'     => 'nullable|numeric',
            'r_multiple'      => 'nullable|numeric',
            'followed_rules'  => 'required|in:0,1,true,false',
            'notes'           => 'nullable|string|max:1000',
            'trade_date'      => 'nullable|date',
        ]);

        $data['followed_rules'] = filter_var($data['followed_rules'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $data['user_id']        = $request->user()->id;
        $data['pair_id']        = $data['pair_id'] ?? $backtestSession->pair_id;

        $trade = $backtestSession->trades()->create($data);
        $this->saveImages($request, $trade->id);

        return response()->json($trade->load(['pair', 'h4', 'm15', 'm1', 'images']), 201);
    }

    public function updateTrade(Request $request, BacktestSession $backtestSession, BacktestTrade $backtestTrade)
    {
        $this->gate($request, $backtestSession);
        $data = $request->validate([
            'h4_category_id'  => 'nullable|exists:categories,id',
            'm15_category_id' => 'nullable|exists:categories,id',
            'm1_category_id'  => 'nullable|exists:categories,id',
            'entry_technique' => 'nullable|string|max:150',
            'result'          => 'sometimes|in:win,loss,breakeven',
            'pips_result'     => 'nullable|numeric',
            'r_multiple'      => 'nullable|numeric',
            'followed_rules'  => 'sometimes|in:0,1,true,false',
            'notes'           => 'nullable|string|max:1000',
            'trade_date'      => 'nullable|date',
        ]);
        if (isset($data['followed_rules'])) {
            $data['followed_rules'] = filter_var($data['followed_rules'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        }
        $backtestTrade->update($data);
        $this->saveImages($request, $backtestTrade->id);
        return response()->json($backtestTrade->fresh(['pair', 'h4', 'm15', 'm1', 'images']));
    }

    public function destroyTrade(Request $request, BacktestSession $backtestSession, BacktestTrade $backtestTrade)
    {
        $this->gate($request, $backtestSession);
        foreach ($backtestTrade->images as $img) Storage::disk('local')->delete($img->path);
        $backtestTrade->delete();
        return response()->json(['message' => 'Trade deleted.']);
    }

    // ── Dashboard summary ─────────────────────────────────────────

    public function summary(Request $request)
    {
        $sessions  = BacktestSession::with('trades')
            ->where('user_id', $request->user()->id)
            ->get();

        $allTrades = $sessions->flatMap(fn($s) => $s->trades);
        $total     = $allTrades->count();

        $winR  = (float) $allTrades->where('result', 'win')->sum('r_multiple');
        $lossR = abs((float) $allTrades->where('result', 'loss')->sum('r_multiple'));

        return response()->json([
            'total_sessions' => $sessions->count(),
            'total_trades'   => $total,
            'wins'           => $allTrades->where('result', 'win')->count(),
            'losses'         => $allTrades->where('result', 'loss')->count(),
            'win_rate'       => $total ? round($allTrades->where('result', 'win')->count() / $total * 100, 1) : 0,
            'total_r'        => round($allTrades->sum('r_multiple'), 2),
            'avg_r'          => $total ? round($allTrades->avg('r_multiple'), 2) : 0,
            'expectancy'     => $total ? round(($winR - $lossR) / $total, 3) : 0,
            'recent_sessions'=> $sessions->sortByDesc('created_at')->take(3)->map(fn($s) => [
                'id'       => $s->id,
                'name'     => $s->name,
                'trades'   => $s->trades->count(),
                'win_rate' => $s->stats()['win_rate'],
                'total_r'  => $s->stats()['total_r'],
            ])->values(),
        ]);
    }

    private function saveImages(Request $request, int $tradeId): void
    {
        foreach (['H4' => 'h4_image', 'M15' => 'm15_image', 'M1' => 'm1_image'] as $tf => $field) {
            if ($request->hasFile($field)) {
                BacktestTradeImage::where('backtest_trade_id', $tradeId)
                    ->where('timeframe', $tf)
                    ->each(function ($img) {
                        Storage::disk('local')->delete($img->path);
                        $img->delete();
                    });
                $path = $request->file($field)->store("backtest_trades/{$tradeId}", 'local');
                BacktestTradeImage::create([
                    'backtest_trade_id' => $tradeId,
                    'timeframe'         => $tf,
                    'path'              => $path,
                    'disk'              => 'local',
                ]);
            }
        }
    }

    private function gate(Request $request, BacktestSession $session): void
    {
        if ((int)$session->user_id !== (int)$request->user()->id) abort(403);
    }
}