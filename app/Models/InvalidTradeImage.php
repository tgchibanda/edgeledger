<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InvalidTradeImage extends Model
{
    protected $fillable = ['invalid_trade_id','type','path','disk'];

    public function invalidTrade() { return $this->belongsTo(InvalidTrade::class); }
}