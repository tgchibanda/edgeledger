<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvalidTrade;
use App\Models\InvalidTradeImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvalidTradeController extends Controller
{
    private function rels() { return ['pair','images']; }

    public function index(Request $request)
    {
        $trades = InvalidTrade::with($this->rels())
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($trades);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pair_id'    => 'required|exists:pairs,id',
            'notes'      => 'nullable|string|max:1000',
            'lesson'     => 'nullable|string|max:1000',
            'mtf_image'  => 'nullable|file|image|max:10240',
            'entry_image'=> 'nullable|file|image|max:10240',
            'correct_image' => 'nullable|file|image|max:10240',
        ]);

        $trade = InvalidTrade::create([
            'user_id' => $request->user()->id,
            'pair_id' => $data['pair_id'],
            'notes'   => $data['notes']  ?? null,
            'lesson'  => $data['lesson'] ?? null,
        ]);

        $this->saveImages($request, $trade->id);

        return response()->json($trade->load($this->rels()), 201);
    }

    public function show(Request $request, $id)
    {
        $trade = InvalidTrade::with($this->rels())->findOrFail($id);
        $this->gate($request, $trade);
        return response()->json($trade);
    }

    public function update(Request $request, $id)
    {
        $trade = InvalidTrade::findOrFail($id);
        $this->gate($request, $trade);

        $data = $request->validate([
            'pair_id'       => 'sometimes|exists:pairs,id',
            'notes'         => 'nullable|string|max:1000',
            'lesson'        => 'nullable|string|max:1000',
            'mtf_image'     => 'nullable|file|image|max:10240',
            'entry_image'   => 'nullable|file|image|max:10240',
            'correct_image' => 'nullable|file|image|max:10240',
        ]);

        $trade->update(array_filter([
            'pair_id' => $data['pair_id'] ?? $trade->pair_id,
            'notes'   => $data['notes']   ?? $trade->notes,
            'lesson'  => $data['lesson']  ?? $trade->lesson,
        ], fn($v) => $v !== null));

        $this->saveImages($request, $trade->id);

        return response()->json($trade->fresh($this->rels()));
    }

    public function destroy(Request $request, $id)
    {
        $trade = InvalidTrade::findOrFail($id);
        $this->gate($request, $trade);

        foreach ($trade->images as $img) {
            Storage::disk('local')->delete($img->path);
        }
        $trade->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    private function saveImages(Request $request, int $tradeId): void
    {
        $slots = [
            'mtf_image'     => 'mtf',
            'entry_image'   => 'entry',
            'correct_image' => 'correct',
        ];

        foreach ($slots as $field => $type) {
            if ($request->hasFile($field)) {
                // Delete existing image of this type
                InvalidTradeImage::where('invalid_trade_id', $tradeId)
                    ->where('type', $type)
                    ->each(function($img) {
                        Storage::disk('local')->delete($img->path);
                        $img->delete();
                    });

                $path = $request->file($field)->store("invalid_trades/{$tradeId}", 'local');
                InvalidTradeImage::create([
                    'invalid_trade_id' => $tradeId,
                    'type'             => $type,
                    'path'             => $path,
                    'disk'             => 'local',
                ]);
            }
        }
    }

    private function gate(Request $request, InvalidTrade $trade): void
    {
        if ((int)$trade->user_id !== (int)$request->user()->id) abort(403);
    }
}