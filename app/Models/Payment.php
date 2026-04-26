<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    protected $fillable = ['user_id','subscription_id','amount','commission_amount','currency','status','payment_method','external_id','proof_path','paid_at'];
    protected $casts    = ['paid_at' => 'datetime', 'amount' => 'float', 'commission_amount' => 'float'];
    public function user()         { return $this->belongsTo(User::class); }
    public function subscription() { return $this->belongsTo(Subscription::class); }
}