<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TradeDatabase;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = Category::where('user_id', $request->user()->id);
        if ($request->timeframe) $q->where('timeframe', $request->timeframe);
        if ($request->has('root_only')) $q->whereNull('parent_id');
        return response()->json(
            $q->withCount(['children'])->orderByDesc('trade_count')->orderBy('name')->get()
        );
    }

    /**
     * Suggest M15 categories that have appeared with a given H4,
     * and M1 categories that have appeared with a given H4+M15 combo.
     */
    public function suggest(Request $request)
    {
        $uid = $request->user()->id;
        $result = ['m15'=>[], 'm1'=>[]];

        if ($request->h4_category_id) {
            $m15ids = TradeDatabase::where('user_id', $uid)
                ->where('h4_category_id', $request->h4_category_id)
                ->where('is_valid', true)
                ->distinct()->pluck('m15_category_id');
            $result['m15'] = Category::whereIn('id', $m15ids)
                ->orderByDesc('trade_count')->orderBy('name')->get();
        }

        if ($request->h4_category_id && $request->m15_category_id) {
            $m1ids = TradeDatabase::where('user_id', $uid)
                ->where('h4_category_id', $request->h4_category_id)
                ->where('m15_category_id', $request->m15_category_id)
                ->where('is_valid', true)
                ->distinct()->pluck('m1_category_id');
            $result['m1'] = Category::whereIn('id', $m1ids)
                ->orderByDesc('trade_count')->orderBy('name')->get();
        }

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'timeframe'   => 'required|in:H4,M15,M1',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id',
        ]);
        $data['user_id']     = $request->user()->id;
        $data['trade_count'] = 0;
        return response()->json(Category::create($data), 201);
    }

    public function show(Request $request, Category $category)
    {
        $this->gate($request, $category);
        return response()->json($category->load('parent','children'));
    }

    public function update(Request $request, Category $category)
    {
        $this->gate($request, $category);
        $category->update($request->validate([
            'name'        => 'sometimes|string|max:100',
            'description' => 'nullable|string',
        ]));
        return response()->json($category);
    }

    public function destroy(Request $request, Category $category)
    {
        $this->gate($request, $category);
        $category->delete();
        return response()->json(['message'=>'Deleted.']);
    }

    private function gate($request, $category)
    {
        if ($category->user_id !== $request->user()->id) abort(403);
    }
}
