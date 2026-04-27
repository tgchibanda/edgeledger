<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InvalidTrade extends Model
{
    protected $fillable = ['user_id','pair_id','notes','lesson'];

    public function user()   { return $this->belongsTo(User::class); }
    public function pair()   { return $this->belongsTo(Pair::class); }
    public function images() { return $this->hasMany(InvalidTradeImage::class); }

    public function mtfImage()     { return $this->hasOne(InvalidTradeImage::class)->where('type','mtf'); }
    public function entryImage()   { return $this->hasOne(InvalidTradeImage::class)->where('type','entry'); }
    public function correctImage() { return $this->hasOne(InvalidTradeImage::class)->where('type','correct'); }
}