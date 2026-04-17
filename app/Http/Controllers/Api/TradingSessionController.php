<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\TradingSession;
class TradingSessionController extends Controller
{
    public function index() { return response()->json(TradingSession::all()); }
}
