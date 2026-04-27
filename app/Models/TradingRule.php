<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TradingRule extends Model
{
    protected $fillable = ['user_id', 'content', 'sort_order'];
    public function user() { return $this->belongsTo(User::class); }
}