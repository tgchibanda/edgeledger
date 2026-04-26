<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ReferralEarning extends Model {
    protected $fillable = ['user_id','payment_id','referral_id','amount','status','available_at'];
    protected $casts    = ['available_at' => 'datetime', 'amount' => 'float'];
    public function user()     { return $this->belongsTo(User::class); }
    public function payment()  { return $this->belongsTo(Payment::class); }
    public function referral() { return $this->belongsTo(Referral::class); }
}
