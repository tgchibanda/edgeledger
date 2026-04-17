<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeDatabase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'h4_category_id',
        'm15_category_id',
        'm1_category_id',
        'pair_id',
        'trading_session_id',
        'entry_technique',
        'result',
        'followed_rules',
        'is_valid',
        'is_reference',
        'notes'
    ];

    protected $casts = ['followed_rules' => 'boolean', 'is_valid' => 'boolean', 'is_reference' => 'boolean'];

    public function h4()
    {
        return $this->belongsTo(Category::class, 'h4_category_id');
    }
    public function m15()
    {
        return $this->belongsTo(Category::class, 'm15_category_id');
    }
    public function m1()
    {
        return $this->belongsTo(Category::class, 'm1_category_id');
    }
    public function pair()
    {
        return $this->belongsTo(Pair::class);
    }
    public function session()
    {
        return $this->belongsTo(TradingSession::class, 'trading_session_id');
    }
    public function images()
    {
        return $this->hasMany(TradeImage::class);
    }

    // Auto-set is_valid when followed_rules is set
    protected static function booted(): void
    {
        static::saving(function ($trade) {
            $trade->is_valid = $trade->followed_rules;
        });
    }
}
