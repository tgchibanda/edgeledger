<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['user_id','parent_id','timeframe','name','description','trade_count','last_traded_at'];
    protected $casts    = ['last_traded_at'=>'datetime'];

    public function user()     { return $this->belongsTo(User::class); }
    public function parent()   { return $this->belongsTo(Category::class, 'parent_id'); }
    public function children() { return $this->hasMany(Category::class, 'parent_id'); }

    public function incrementTradeCount(): void
    {
        $this->increment('trade_count');
        $this->update(['last_traded_at' => now()]);
    }
}
