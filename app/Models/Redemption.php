<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Redemption extends Model {
    protected $fillable = ['user_id','amount','status','payment_method','payment_details','admin_notes','paid_at'];
    protected $casts    = ['paid_at' => 'datetime', 'amount' => 'float'];
    public function user() { return $this->belongsTo(User::class); }
}
