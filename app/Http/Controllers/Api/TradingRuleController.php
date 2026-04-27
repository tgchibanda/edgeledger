<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TradingRule;
use Illuminate\Http\Request;

class TradingRuleController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            TradingRule::where('user_id', $request->user()->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id','content','sort_order'])
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate(['content' => 'required|string|max:500']);

        $max = TradingRule::where('user_id', $request->user()->id)->max('sort_order') ?? -1;

        $rule = TradingRule::create([
            'user_id'    => $request->user()->id,
            'content'    => $data['content'],
            'sort_order' => $max + 1,
        ]);

        return response()->json($rule, 201);
    }

    public function update(Request $request, TradingRule $tradingRule)
    {
        if ((int)$tradingRule->user_id !== (int)$request->user()->id) abort(403);

        $data = $request->validate(['content' => 'required|string|max:500']);
        $tradingRule->update(['content' => $data['content']]);

        return response()->json($tradingRule);
    }

    public function destroy(Request $request, TradingRule $tradingRule)
    {
        if ((int)$tradingRule->user_id !== (int)$request->user()->id) abort(403);
        $tradingRule->delete();
        return response()->json(['message' => 'Deleted.']);
    }

    // Bulk reorder — receives [{id, sort_order}, ...]
    public function reorder(Request $request)
    {
        $data = $request->validate(['order' => 'required|array', 'order.*.id' => 'required|integer', 'order.*.sort_order' => 'required|integer']);

        foreach ($data['order'] as $item) {
            TradingRule::where('id', $item['id'])
                ->where('user_id', $request->user()->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Reordered.']);
    }
}