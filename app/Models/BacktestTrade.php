<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BacktestTrade extends Model
{
    protected $fillable = [
        'backtest_session_id','user_id','pair_id',
        'h4_category_id','m15_category_id','m1_category_id',
        'entry_technique','result','pips_result','r_multiple',
        'followed_rules','notes','trade_date',
    ];
    protected $casts = [
        'followed_rules' => 'boolean',
        'pips_result'    => 'float',
        'r_multiple'     => 'float',
        'trade_date'     => 'datetime',
    ];

    public function session() { return $this->belongsTo(BacktestSession::class, 'backtest_session_id'); }
    public function user()    { return $this->belongsTo(User::class); }
    public function pair()    { return $this->belongsTo(Pair::class); }
    public function h4()      { return $this->belongsTo(Category::class, 'h4_category_id'); }
    public function m15()     { return $this->belongsTo(Category::class, 'm15_category_id'); }
    public function m1()      { return $this->belongsTo(Category::class, 'm1_category_id'); }
    public function images()  { return $this->hasMany(BacktestTradeImage::class); }
}

class BacktestTradeImage extends Model
{
    protected $fillable = ['backtest_trade_id','timeframe','path','disk'];
    public function trade() { return $this->belongsTo(BacktestTrade::class, 'backtest_trade_id'); }
}
