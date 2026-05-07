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
    private function rels()
    {
        return ['pair', 'session', 'h4', 'm15', 'm1', 'images'];
    }

    public function index(Request $request)
    {
        $journals = Journal::with($this->rels())
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($journals);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pair_id'            => 'required|exists:pairs,id',
            'trading_session_id' => 'required|exists:trading_sessions,id',
            'h4_category_id'     => 'required|exists:categories,id',
            'm15_category_id'    => 'required|exists:categories,id',
            'm1_category_id'     => 'required|exists:categories,id',
            'entry_technique'    => 'required|string|max:100',
            'pre_trade_notes'    => 'nullable|string',
            'h4_image'           => 'nullable|file|image|max:10240',
            'm15_image'          => 'nullable|file|image|max:10240',
            'm1_image'           => 'nullable|file|image|max:10240',
        ]);

        $data['user_id'] = $request->user()->id;
        $journal = Journal::create($data);
        $this->handleImages($request, $journal->id);

        return response()->json($journal->load($this->rels()), 201);
    }

    public function show(Request $request, $journal)
    {
        $j = Journal::with($this->rels())->findOrFail($journal);
        $this->gate($request, $j);
        return response()->json($j);
    }

    public function update(Request $request, $journal)
    {
        $j = Journal::findOrFail($journal);
        $this->gate($request, $j);

        $data = $request->validate([
            'pre_trade_notes'  => 'nullable|string',
            'post_trade_notes' => 'nullable|string',
            'result'           => 'nullable|in:win,loss,breakeven',
            'followed_rules'   => 'nullable|in:0,1,true,false',
            'pips_result'      => 'nullable|numeric',
            'r_multiple'       => 'nullable|numeric',
            'mistakes'         => 'nullable|string',
            'status'           => 'nullable|in:pre,completed',
        ]);

        if (isset($data['followed_rules'])) {
            $data['followed_rules'] = filter_var($data['followed_rules'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        }

        $j->update($data);
        $this->handleImages($request, $j->id);

        return response()->json($j->fresh($this->rels()));
    }

    public function complete(Request $request, $journal)
    {
        $j = Journal::findOrFail($journal);
        $this->gate($request, $j);

        $data = $request->validate([
            'result'           => 'required|in:win,loss,breakeven',
            'followed_rules'   => 'required|in:0,1,true,false',
            'post_trade_notes' => 'nullable|string',
            'pips_result'      => 'nullable|numeric',
            'r_multiple'       => 'nullable|numeric',
            'mistakes'         => 'nullable|string',
            'promote'          => 'nullable|boolean',
        ]);

        $data['followed_rules'] = filter_var($data['followed_rules'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $data['status'] = 'completed';

        $j->update($data);
        $this->handleImages($request, $j->id);

        return response()->json($j->fresh($this->rels()));
    }

    public function promoteToDatabase(Request $request, $journal)
    {
        $j = Journal::with($this->rels())->findOrFail($journal);
        $this->gate($request, $j);

        if ($j->status !== 'completed') {
            return response()->json(['message' => 'Complete the journal entry before promoting.'], 422);
        }

        $trade = TradeDatabase::create([
            'user_id'            => $request->user()->id,
            'pair_id'            => $j->pair_id,
            'trading_session_id' => $j->trading_session_id,
            'h4_category_id'     => $j->h4_category_id,
            'm15_category_id'    => $j->m15_category_id,
            'm1_category_id'     => $j->m1_category_id,
            'entry_technique'    => $j->entry_technique,
            'result'             => $j->result,
            'followed_rules'     => $j->followed_rules ? 1 : 0,
            'pips_result'        => $j->pips_result,
            'r_multiple'         => $j->r_multiple,
            'confluences'        => $j->pre_trade_notes,
            'mistakes'           => $j->mistakes,
            'notes'              => $j->post_trade_notes,
        ]);

        foreach ($j->images as $img) {
            $newPath = 'trade_database/' . $trade->id . '/' . basename($img->path);
            if (Storage::disk('local')->copy($img->path, $newPath)) {
                TradeImage::create([
                    'trade_database_id' => $trade->id,
                    'timeframe'         => $img->timeframe,
                    'path'              => $newPath,
                    'disk'              => 'local',
                ]);
            }
        }

        return response()->json(['message' => 'Promoted to trade database.', 'trade' => $trade]);
    }

    public function destroy(Request $request, $journal)
    {
        $j = Journal::findOrFail($journal);
        $this->gate($request, $j);
        foreach ($j->images as $img) Storage::disk('local')->delete($img->path);
        $j->delete();
        return response()->json(['message' => 'Deleted.']);
    }

    private function handleImages(Request $request, int $journalId): void
    {
        foreach (['H4' => 'h4_image', 'M15' => 'm15_image', 'M1' => 'm1_image'] as $tf => $field) {
            if ($request->hasFile($field)) {
                JournalImage::where('journal_id', $journalId)
                    ->where('timeframe', $tf)
                    ->each(function ($img) {
                        Storage::disk('local')->delete($img->path);
                        $img->delete();
                    });
                $path = $request->file($field)->store("journals/{$journalId}", 'local');
                JournalImage::create([
                    'journal_id' => $journalId,
                    'timeframe'  => $tf,
                    'path'       => $path,
                    'disk'       => 'local',
                ]);
            }
        }
    }

    private function gate(Request $request, Journal $journal): void
    {
        if ((int)$journal->user_id !== (int)$request->user()->id) abort(403);
    }
}