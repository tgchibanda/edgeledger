<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'user_id',
        'pair_id',
        'trading_session_id',
        'h4_category_id',
        'm15_category_id',
        'm1_category_id',
        'entry_technique',
        'pre_trade_notes',
        'post_trade_notes',
        'result',
        'followed_rules',
        'pips_result',
        'r_multiple',
        'mistakes',
        'status',
    ];

    protected $casts = [
        'followed_rules' => 'boolean',
        'pips_result'    => 'float',
        'r_multiple'     => 'float',
    ];

    // Relations
    public function user()    { return $this->belongsTo(User::class); }
    public function pair()    { return $this->belongsTo(Pair::class); }
    public function session() { return $this->belongsTo(TradingSession::class, 'trading_session_id'); }
    public function h4()      { return $this->belongsTo(Category::class, 'h4_category_id'); }
    public function m15()     { return $this->belongsTo(Category::class, 'm15_category_id'); }
    public function m1()      { return $this->belongsTo(Category::class, 'm1_category_id'); }
    public function images()  { return $this->hasMany(JournalImage::class); }
}