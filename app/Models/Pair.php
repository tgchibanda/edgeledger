<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pair extends Model
{
    protected $fillable = ['user_id','symbol','is_active'];
    protected $casts    = ['is_active'=>'boolean'];
    public function user() { return $this->belongsTo(User::class); }
}
