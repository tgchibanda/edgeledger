<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TradeDatabase extends Model
{
    protected $table    = 'trade_database';
    protected $fillable = [
        'user_id','h4_category_id','m15_category_id','m1_category_id',
        'pair_id','trading_session_id','entry_technique',
        'result','followed_rules','is_valid','is_reference',
        'pips_result','r_multiple','confluences','mistakes','notes','trade_date',
    ];
    protected $casts = [
        'followed_rules'=>'boolean','is_valid'=>'boolean','is_reference'=>'boolean',
        'pips_result'=>'float','r_multiple'=>'float','trade_date'=>'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function ($t) {
            $t->is_valid = (bool) $t->followed_rules;
        });
        static::created(function ($t) {
            foreach ([$t->h4_category_id, $t->m15_category_id, $t->m1_category_id] as $catId) {
                if ($catId) optional(Category::find($catId))->incrementTradeCount();
            }
        });
    }

    public function user()    { return $this->belongsTo(User::class); }
    public function h4()      { return $this->belongsTo(Category::class, 'h4_category_id'); }
    public function m15()     { return $this->belongsTo(Category::class, 'm15_category_id'); }
    public function m1()      { return $this->belongsTo(Category::class, 'm1_category_id'); }
    public function pair()    { return $this->belongsTo(Pair::class); }
    public function session() { return $this->belongsTo(TradingSession::class, 'trading_session_id'); }
    public function images()  { return $this->hasMany(TradeImage::class); }
}
