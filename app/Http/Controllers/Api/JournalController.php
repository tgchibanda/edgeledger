<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\JournalImage;
use App\Models\TradeDatabase;
use App\Models\TradeImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JournalController extends Controller
{
    private function rels() { return ['h4','m15','m1','pair','session','images','tradeDatabase']; }

    public function index(Request $request)
    {
        $q = Journal::where('user_id', $request->user()->id)->with($this->rels());
        if ($request->status)  $q->where('status',  $request->status);
        if ($request->pair_id) $q->where('pair_id', $request->pair_id);
        if ($request->result)  $q->where('result',  $request->result);
        return response()->json($q->latest()->paginate(20));
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
            'pre_trade_notes'    => 'nullable|string',
            'reason_not_to_take' => 'nullable|string',
            'confluences'        => 'nullable|string|max:500',
            'trade_date'         => 'nullable|date',
            'trade_database_id'  => 'nullable|exists:trade_database,id',
        ]);
        $data['user_id'] = $request->user()->id;
        $data['status']  = 'pre_trade';

        // Store pre-trade images
        $journal = Journal::create($data);
        $this->handleImages($request, $journal->id);
        return response()->json($journal->load($this->rels()), 201);
    }

    public function show(Request $request, Journal $journal)
    {
        $this->gate($request, $journal);
        return response()->json($journal->load($this->rels()));
    }

    public function update(Request $request, Journal $journal)
    {
        $this->gate($request, $journal);
        $data = $request->validate([
            'h4_category_id'     => 'sometimes|exists:categories,id',
            'm15_category_id'    => 'sometimes|exists:categories,id',
            'm1_category_id'     => 'sometimes|exists:categories,id',
            'pair_id'            => 'sometimes|exists:pairs,id',
            'trading_session_id' => 'sometimes|exists:trading_sessions,id',
            'entry_technique'    => 'sometimes|string|max:100',
            'pre_trade_notes'    => 'nullable|string',
            'reason_not_to_take' => 'nullable|string',
            'confluences'        => 'nullable|string|max:500',
            'trade_date'         => 'nullable|date',
        ]);
        $journal->update($data);
        return response()->json($journal->load($this->rels()));
    }

    public function destroy(Request $request, Journal $journal)
    {
        $this->gate($request, $journal);
        foreach ($journal->images as $img) Storage::disk('local')->delete($img->path);
        $journal->delete();
        return response()->json(['message'=>'Deleted.']);
    }

    public function complete(Request $request, Journal $journal)
    {
        $this->gate($request, $journal);
        $data = $request->validate([
            'result'             => 'required|in:win,loss,breakeven',
            'followed_rules'     => 'required|boolean',
            'post_trade_notes'   => 'nullable|string',
            'pips_result'        => 'nullable|numeric',
            'r_multiple'         => 'nullable|numeric',
            'mistakes'           => 'nullable|string|max:500',
        ]);
        $data['is_valid'] = (bool)$data['followed_rules'];
        $data['status']   = 'completed';
        $journal->update($data);
        $this->handleImages($request, $journal->id);
        return response()->json($journal->load($this->rels()));
    }

    public function promoteToDatabase(Request $request, Journal $journal)
    {
        $this->gate($request, $journal);
        if ($journal->result !== 'win') {
            return response()->json(['message'=>'Only winning trades can be promoted to the database.'], 422);
        }
        $trade = TradeDatabase::create([
            'user_id'            => $journal->user_id,
            'h4_category_id'     => $journal->h4_category_id,
            'm15_category_id'    => $journal->m15_category_id,
            'm1_category_id'     => $journal->m1_category_id,
            'pair_id'            => $journal->pair_id,
            'trading_session_id' => $journal->trading_session_id,
            'entry_technique'    => $journal->entry_technique,
            'result'             => $journal->result,
            'followed_rules'     => $journal->followed_rules,
            'pips_result'        => $journal->pips_result,
            'r_multiple'         => $journal->r_multiple,
            'confluences'        => $journal->confluences,
            'notes'              => $journal->post_trade_notes,
            'trade_date'         => $journal->trade_date,
            'is_reference'       => true,
        ]);
        foreach ($journal->images as $img) {
            $newPath = str_replace("journals/{$journal->id}", "trade_database/{$trade->id}", $img->path);
            if (Storage::disk('local')->exists($img->path)) {
                Storage::disk('local')->copy($img->path, $newPath);
            }
            TradeImage::create(['trade_database_id'=>$trade->id,'timeframe'=>$img->timeframe,'path'=>$newPath,'disk'=>'local']);
        }
        $journal->update(['trade_database_id'=>$trade->id,'promote_to_database'=>true]);
        return response()->json(['message'=>'Promoted to database.','trade'=>$trade->load(['h4','m15','m1','pair','session','images'])]);
    }

    private function handleImages(Request $request, int $journalId): void
    {
        foreach (['H4'=>'h4_image','M15'=>'m15_image','M1'=>'m1_image'] as $tf => $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store("journals/{$journalId}", 'local');
                JournalImage::create(['journal_id'=>$journalId,'timeframe'=>$tf,'path'=>$path]);
            }
        }
    }

    private function gate($request, $journal): void
    {
        if ($journal->user_id !== $request->user()->id) abort(403);
    }
}
