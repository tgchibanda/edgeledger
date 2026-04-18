<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TradeImage extends Model
{
    protected $fillable = ['trade_database_id','timeframe','path','disk'];
    public function trade() { return $this->belongsTo(TradeDatabase::class, 'trade_database_id'); }
}
