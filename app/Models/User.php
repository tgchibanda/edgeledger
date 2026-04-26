<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name','email','password','role','is_active','referral_code','referral_link','referred_by','trial_ends_at'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean','trial_ends_at'=>'datetime'];

    // Auto-generate referral code and link when user is created
    protected static function booted(): void
    {
        static::created(function (User $user) {
            $code = strtoupper(substr(md5($user->id . $user->email . 'el2025'), 0, 8));
            $slug = preg_replace('/[^a-z0-9]/', '', strtolower($user->name)) . $user->id;
            // Use DB directly to avoid triggering model events again
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $user->id)
                ->update(['referral_code' => $code, 'referral_link' => $slug]);
            // Update the in-memory model too
            $user->referral_code = $code;
            $user->referral_link = $slug;
        });
    }

    public function isSuperuser(): bool  { return $this->role === 'superuser'; }
    public function onTrial(): bool      { return $this->trial_ends_at && $this->trial_ends_at->isFuture(); }
    public function trialDaysLeft(): int { return $this->trial_ends_at ? max(0, now()->diffInDays($this->trial_ends_at, false)) : 0; }
    public function hasAccess(): bool    { return $this->isSuperuser() || $this->onTrial() || $this->activeSubscription() !== null; }
    public function categories()         { return $this->hasMany(Category::class); }
    public function tradeDatabases()     { return $this->hasMany(TradeDatabase::class); }
    public function journals()           { return $this->hasMany(Journal::class); }
    public function pairs()              { return $this->hasMany(Pair::class); }

    // Referral relationships
    public function referredBy()         { return $this->belongsTo(User::class, 'referred_by'); }
    public function referrals()          { return $this->hasMany(Referral::class, 'referrer_id'); }
    public function referralEarnings()   { return $this->hasMany(ReferralEarning::class); }
    public function redemptions()        { return $this->hasMany(Redemption::class); }

    // Subscription
    public function subscriptions()      { return $this->hasMany(Subscription::class); }
    public function activeSubscription() { return $this->subscriptions()->where('status', 'active')->latest()->first(); }

    // Wallet balance
    public function walletBalance(): float
    {
        return $this->referralEarnings()->where('status', 'available')->sum('amount');
    }
    public function pendingBalance(): float
    {
        return $this->referralEarnings()->where('status', 'pending')->sum('amount');
    }
}