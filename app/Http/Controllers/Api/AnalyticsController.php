<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\TradeDatabase;
use App\Models\Journal;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function winRates(Request $request)
    {
        $trades = TradeDatabase::where('user_id', $request->user()->id)
            ->where('is_valid', true)
            ->with(['h4','m15','m1','pair','session'])
            ->get();

        $rows = $trades->groupBy(fn($t) =>
            $t->h4_category_id.'-'.$t->m15_category_id.'-'.$t->m1_category_id.'-'.$t->entry_technique
        )->map(function($g) {
            $first   = $g->first();
            $total   = $g->count();
            $wins    = $g->where('result','win')->count();
            $losses  = $g->where('result','loss')->count();
            $avgWinR = $g->where('result','win')->whereNotNull('r_multiple')->avg('r_multiple');
            $avgLossR= $g->where('result','loss')->whereNotNull('r_multiple')->avg('r_multiple');
            $wr      = $total>0 ? $wins/$total : 0;
            $lr      = $total>0 ? $losses/$total : 0;
            $exp     = ($avgWinR!==null && $avgLossR!==null) ? round($wr*$avgWinR + $lr*$avgLossR, 2) : null;
            return [
                'h4'             => $first->h4->name   ?? '-',
                'm15'            => $first->m15->name  ?? '-',
                'm1'             => $first->m1->name   ?? '-',
                'entry_technique'=> $first->entry_technique,
                'pair'           => $first->pair->symbol ?? '-',
                'session'        => $first->session->name ?? '-',
                'total'          => $total,
                'wins'           => $wins,
                'losses'         => $losses,
                'win_rate'       => $total>0 ? round(($wins/$total)*100,1) : 0,
                'avg_win_pips'   => $g->where('result','win')->whereNotNull('pips_result')->avg('pips_result') ? round($g->where('result','win')->whereNotNull('pips_result')->avg('pips_result'),1) : null,
                'avg_loss_pips'  => $g->where('result','loss')->whereNotNull('pips_result')->avg('pips_result') ? round($g->where('result','loss')->whereNotNull('pips_result')->avg('pips_result'),1) : null,
                'avg_win_r'      => $avgWinR  ? round($avgWinR,2)  : null,
                'avg_loss_r'     => $avgLossR ? round($avgLossR,2) : null,
                'expectancy_r'   => $exp,
            ];
        })->values()->sortByDesc('win_rate')->values();

        return response()->json($rows);
    }

    public function summary(Request $request)
    {
        $uid      = $request->user()->id;
        $db       = TradeDatabase::where('user_id',$uid)->get();
        $journals = Journal::where('user_id',$uid)->get();
        $valid    = $db->where('is_valid',true);
        $total    = $valid->count();
        $wins     = $valid->where('result','win')->count();
        $losses   = $valid->where('result','loss')->count();

        $bySession = $valid->groupBy('trading_session_id')->map(function($g) {
            $t=$g->count(); $w=$g->where('result','win')->count();
            return ['session'=>optional($g->first()->session)->name??'Unknown','total'=>$t,'win_rate'=>$t>0?round(($w/$t)*100,1):0];
        })->values();

        $byPair = $valid->groupBy('pair_id')->map(function($g) {
            $t=$g->count(); $w=$g->where('result','win')->count();
            return ['pair'=>optional($g->first()->pair)->symbol??'Unknown','total'=>$t,'win_rate'=>$t>0?round(($w/$t)*100,1):0];
        })->values()->sortByDesc('total')->values();

        return response()->json([
            'total_db_trades'    => $db->count(),
            'valid_trades'       => $total,
            'invalid_trades'     => $db->where('is_valid',false)->count(),
            'wins'               => $wins,
            'losses'             => $losses,
            'win_rate'           => $total>0 ? round(($wins/$total)*100,1) : 0,
            'total_journals'     => $journals->count(),
            'completed_journals' => $journals->where('status','completed')->count(),
            'by_session'         => $bySession,
            'by_pair'            => $byPair,
        ]);
    }

    public function streaks(Request $request)
    {
        $trades = TradeDatabase::where('user_id', $request->user()->id)
            ->where('is_valid', true)
            ->whereIn('result',['win','loss'])
            ->orderBy('trade_date')
            ->pluck('result')
            ->toArray();

        $maxWin=0; $maxLoss=0; $curWin=0; $curLoss=0;
        foreach ($trades as $r) {
            if ($r==='win')  { $curWin++; $curLoss=0; $maxWin=max($maxWin,$curWin); }
            else             { $curLoss++; $curWin=0; $maxLoss=max($maxLoss,$curLoss); }
        }

        return response()->json([
            'current_win_streak'  => $curWin,
            'current_loss_streak' => $curLoss,
            'max_win_streak'      => $maxWin,
            'max_loss_streak'     => $maxLoss,
        ]);
    }

    public function expectancy(Request $request)
    {
        $valid = TradeDatabase::where('user_id', $request->user()->id)
            ->where('is_valid', true)
            ->whereNotNull('r_multiple')
            ->get();

        $wins   = $valid->where('result','win');
        $losses = $valid->where('result','loss');
        $total  = $valid->count();
        $wr     = $total>0 ? $wins->count()/$total : 0;
        $lr     = $total>0 ? $losses->count()/$total : 0;
        $avgWR  = $wins->avg('r_multiple')   ?? 0;
        $avgLR  = $losses->avg('r_multiple') ?? 0;
        $exp    = round($wr*$avgWR + $lr*$avgLR, 3);

        $byMonth = $valid->groupBy(fn($t) => optional($t->trade_date)->format('Y-m') ?? 'Unknown')
            ->map(function($g,$month) {
                $t=$g->count(); $w=$g->where('result','win')->count();
                $exp = round(($t>0?$w/$t:0)*($g->where('result','win')->avg('r_multiple')??0) + ($t>0?($t-$w)/$t:0)*($g->where('result','loss')->avg('r_multiple')??0),3);
                return ['month'=>$month,'trades'=>$t,'win_rate'=>$t>0?round(($w/$t)*100,1):0,'expectancy'=>$exp];
            })->values();

        return response()->json([
            'total_trades'    => $total,
            'win_rate'        => round($wr*100,1),
            'avg_win_r'       => round($avgWR,2),
            'avg_loss_r'      => round($avgLR,2),
            'expectancy_r'    => $exp,
            'trades_with_r'   => $total,
            'by_month'        => $byMonth,
        ]);
    }
}
