<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\TradeDatabase;
use App\Models\TradeImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TradeDatabaseController extends Controller
{
    private function rels() { return ['h4','m15','m1','pair','session','images']; }

    public function index(Request $request)
    {
        $q = TradeDatabase::where('user_id', $request->user()->id)->with($this->rels());
        if ($request->pair_id)    $q->where('pair_id', $request->pair_id);
        if ($request->session_id) $q->where('trading_session_id', $request->session_id);
        if ($request->result)     $q->where('result', $request->result);
        if ($request->has('reference')) $q->where('is_reference', true);
        if ($request->has('valid_only')) $q->where('is_valid', true);
        return response()->json($q->latest('trade_date')->paginate(20));
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'h4_category_id'     => 'required|exists:categories,id',
        'm15_category_id'    => 'required|exists:categories,id',
        'm1_category_id'     => 'required|exists:categories,id',
        'pair_id'            => 'required|exists:pairs,id',
        'trading_session_id' => 'required|exists:trading_sessions,id',
        'entry_technique'    => 'required|string|max:100',
        'result'             => 'required|in:win,loss,breakeven',
        'followed_rules'     => 'required',
        'pips_result'        => 'nullable|numeric',
        'r_multiple'         => 'nullable|numeric',
        'confluences'        => 'nullable|string|max:500',
        'mistakes'           => 'nullable|string|max:500',
        'notes'              => 'nullable|string',
        'trade_date'         => 'nullable|date',
    ]);

    // Convert string boolean from FormData to integer
    $data['followed_rules'] = filter_var($data['followed_rules'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    $data['user_id'] = $request->user()->id;

    $trade = TradeDatabase::create($data);
    $this->handleImages($request, $trade->id, 'trade_database', TradeImage::class, 'trade_database_id');
    return response()->json($trade->load($this->rels()), 201);
}

    public function show(Request $request, TradeDatabase $trade)
    {
        $this->gate($request, $trade);
        return response()->json($trade->load($this->rels()));
    }

    public function update(Request $request, TradeDatabase $trade)
    {
        $this->gate($request, $trade);
        $data = $request->validate([
            'h4_category_id'     => 'sometimes|exists:categories,id',
            'm15_category_id'    => 'sometimes|exists:categories,id',
            'm1_category_id'     => 'sometimes|exists:categories,id',
            'pair_id'            => 'sometimes|exists:pairs,id',
            'trading_session_id' => 'sometimes|exists:trading_sessions,id',
            'entry_technique'    => 'sometimes|string|max:100',
            'result'             => 'sometimes|in:win,loss,breakeven',
            'followed_rules'     => 'sometimes|boolean',
            'is_reference'       => 'sometimes|boolean',
            'pips_result'        => 'nullable|numeric',
            'r_multiple'         => 'nullable|numeric',
            'confluences'        => 'nullable|string|max:500',
            'mistakes'           => 'nullable|string|max:500',
            'notes'              => 'nullable|string',
            'trade_date'         => 'nullable|date',
        ]);
        $trade->update($data);
        return response()->json($trade->load($this->rels()));
    }

    public function destroy(Request $request, TradeDatabase $trade)
    {
        $this->gate($request, $trade);
        foreach ($trade->images as $img) Storage::disk('local')->delete($img->path);
        $trade->delete();
        return response()->json(['message'=>'Deleted.']);
    }

    public function filter(Request $request)
    {
        $q = TradeDatabase::with($this->rels())
            ->where('user_id', $request->user()->id)
            ->where('is_valid', true);

        if ($request->h4_category_id)     $q->where('h4_category_id',     $request->h4_category_id);
        if ($request->m15_category_id)    $q->where('m15_category_id',    $request->m15_category_id);
        if ($request->m1_category_id)     $q->where('m1_category_id',     $request->m1_category_id);
        if ($request->pair_id)            $q->where('pair_id',            $request->pair_id);
        if ($request->trading_session_id) $q->where('trading_session_id', $request->trading_session_id);

        $trades  = $q->latest('trade_date')->get();
        $total   = $trades->count();
        $wins    = $trades->where('result','win')->count();
        $losses  = $trades->where('result','loss')->count();
        $winRate = $total > 0 ? round(($wins/$total)*100,1) : 0;

        $avgWinPips  = $trades->where('result','win')->whereNotNull('pips_result')->avg('pips_result');
        $avgLossPips = $trades->where('result','loss')->whereNotNull('pips_result')->avg('pips_result');
        $avgWinR     = $trades->where('result','win')->whereNotNull('r_multiple')->avg('r_multiple');
        $avgLossR    = $trades->where('result','loss')->whereNotNull('r_multiple')->avg('r_multiple');

        $expectancyR = null;
        if ($total > 0 && $avgWinR !== null && $avgLossR !== null) {
            $wr = $wins/$total; $lr = $losses/$total;
            $expectancyR = round(($wr * $avgWinR) + ($lr * $avgLossR), 2);
        }

        $byEntry = $trades->groupBy('entry_technique')->map(function($g) {
            $t=$g->count(); $w=$g->where('result','win')->count();
            return [
                'entry_technique' => $g->first()->entry_technique,
                'total'   => $t,
                'wins'    => $w,
                'losses'  => $g->where('result','loss')->count(),
                'win_rate'=> $t>0 ? round(($w/$t)*100,1) : 0,
                'avg_r'   => round($g->whereNotNull('r_multiple')->avg('r_multiple') ?? 0, 2),
            ];
        })->values()->sortByDesc('win_rate')->values();

        return response()->json([
            'trades' => $trades,
            'stats'  => [
                'total'         => $total,
                'wins'          => $wins,
                'losses'        => $losses,
                'win_rate'      => $winRate,
                'avg_win_pips'  => $avgWinPips  ? round($avgWinPips,1)  : null,
                'avg_loss_pips' => $avgLossPips ? round($avgLossPips,1) : null,
                'avg_win_r'     => $avgWinR     ? round($avgWinR,2)     : null,
                'avg_loss_r'    => $avgLossR    ? round($avgLossR,2)    : null,
                'expectancy_r'  => $expectancyR,
                'by_entry'      => $byEntry,
            ],
        ]);
    }

    public function promote(Request $request, TradeDatabase $trade)
    {
        $this->gate($request, $trade);
        $trade->update(['is_reference' => !$trade->is_reference]);
        return response()->json($trade->load($this->rels()));
    }

    private function handleImages(Request $request, int $ownerId, string $folder, string $model, string $fk): void
    {
        foreach (['H4'=>'h4_image','M15'=>'m15_image','M1'=>'m1_image'] as $tf => $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store("{$folder}/{$ownerId}", 'local');
                $model::create([$fk=>$ownerId,'timeframe'=>$tf,'path'=>$path,'disk'=>'local']);
            }
        }
    }

    private function gate($request, $trade): void
    {
        if ($trade->user_id !== $request->user()->id) abort(403);
    }
}
