<?php
// app/Models/Referral.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model {
    protected $fillable = ['referrer_id','referee_id','commission_pct','earned','paid_out'];
    public function referrer() { return $this->belongsTo(User::class, 'referrer_id'); }
    public function referee()  { return $this->belongsTo(User::class, 'referee_id'); }
    public function earnings()  { return $this->hasMany(ReferralEarning::class); }
}
