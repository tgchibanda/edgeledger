<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model {
    protected $fillable = [
        'user_id','plan_id','status','payment_method','external_id',
        'amount','currency','started_at','current_period_start',
        'current_period_end','cancelled_at','trial_ends_at',
    ];
    protected $casts = [
        'started_at'           => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'cancelled_at'         => 'datetime',
        'trial_ends_at'        => 'datetime',
        'amount'               => 'float',
    ];
    public function user()     { return $this->belongsTo(User::class); }
    public function plan()     { return $this->belongsTo(SubscriptionPlan::class, 'plan_id'); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function isActive()    { return $this->status === 'active' && $this->current_period_end?->isFuture(); }
    public function isCancelled() { return $this->status === 'cancelled'; }
    public function daysLeft()    { return $this->current_period_end ? now()->diffInDays($this->current_period_end, false) : 0; }
}
