<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model {
    protected $fillable = ['name','price','currency','interval','features','is_active'];
    protected $casts    = ['features' => 'array', 'is_active' => 'boolean', 'price' => 'float'];
    public function subscriptions() { return $this->hasMany(Subscription::class, 'plan_id'); }
}
