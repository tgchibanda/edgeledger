<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Pair;
use Illuminate\Http\Request;

class PairController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Pair::where('user_id',$request->user()->id)->orderBy('symbol')->get());
    }
    public function store(Request $request)
    {
        $data = $request->validate(['symbol'=>'required|string|max:20']);
        $data['symbol']  = strtoupper(trim($data['symbol']));
        $data['user_id'] = $request->user()->id;
        $pair = Pair::firstOrCreate(['user_id'=>$data['user_id'],'symbol'=>$data['symbol']],['is_active'=>true]);
        return response()->json($pair, 201);
    }
    public function show(Pair $pair) { return response()->json($pair); }
    public function update(Request $request, Pair $pair)
    {
        if ($pair->user_id !== $request->user()->id) abort(403);
        $pair->update($request->validate(['is_active'=>'boolean','symbol'=>'string|max:20']));
        return response()->json($pair);
    }
    public function destroy(Request $request, Pair $pair)
    {
        if ($pair->user_id !== $request->user()->id) abort(403);
        $pair->delete();
        return response()->json(['message'=>'Deleted.']);
    }
}
