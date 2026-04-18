<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'user_id','trade_database_id',
        'h4_category_id','m15_category_id','m1_category_id',
        'pair_id','trading_session_id','entry_technique',
        'status','result','followed_rules','is_valid',
        'pre_trade_notes','post_trade_notes','reason_not_to_take',
        'pips_result','r_multiple','confluences','mistakes',
        'promote_to_database','trade_date',
    ];
    protected $casts = [
        'followed_rules'=>'boolean','is_valid'=>'boolean',
        'promote_to_database'=>'boolean','trade_date'=>'datetime',
        'pips_result'=>'float','r_multiple'=>'float',
    ];

    public function user()          { return $this->belongsTo(User::class); }
    public function tradeDatabase() { return $this->belongsTo(TradeDatabase::class, 'trade_database_id'); }
    public function h4()            { return $this->belongsTo(Category::class, 'h4_category_id'); }
    public function m15()           { return $this->belongsTo(Category::class, 'm15_category_id'); }
    public function m1()            { return $this->belongsTo(Category::class, 'm1_category_id'); }
    public function pair()          { return $this->belongsTo(Pair::class); }
    public function session()       { return $this->belongsTo(TradingSession::class, 'trading_session_id'); }
    public function images()        { return $this->hasMany(JournalImage::class); }
}
