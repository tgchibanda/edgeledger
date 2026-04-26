<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Redemption;
use App\Models\ReferralEarning;
use App\Models\User;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    // Get/update the subscription plan price
    public function getPlan()
    {
        $plan = SubscriptionPlan::first() ?? SubscriptionPlan::create([
            'name' => 'EdgeLedger Pro', 'price' => 2.00, 'currency' => 'USD',
            'interval' => 'month', 'is_active' => true,
            'features' => ['Full trade database','Pre-trade filter','Analytics','Scanner','Journal'],
        ]);
        return response()->json($plan);
    }

    public function updatePlan(Request $request)
    {
        $data = $request->validate([
            'price'    => 'required|numeric|min:0.5|max:999',
            'name'     => 'nullable|string|max:100',
            'currency' => 'nullable|string|size:3',
            'features' => 'nullable|array',
        ]);

        $plan = SubscriptionPlan::first() ?? new SubscriptionPlan();
        $plan->fill($data)->save();

        return response()->json($plan);
    }

    // All subscriptions with user info
    public function subscriptions(Request $request)
    {
        $subs = Subscription::with(['user','plan'])
            ->latest()
            ->paginate(30);
        return response()->json($subs);
    }

    // Manually activate a subscription (admin records cash/manual payment)
    public function activateSubscription(Request $request, User $user)
    {
        $data = $request->validate([
            'payment_method' => 'required|string',
            'payment_ref'    => 'nullable|string',
            'months'         => 'nullable|integer|min:1|max:12',
        ]);
        $months = $data['months'] ?? 1;
        $plan   = SubscriptionPlan::where('is_active', true)->firstOrFail();

        $user->subscriptions()->where('status','active')->update(['status'=>'cancelled','cancelled_at'=>now()]);

        $now = now();
        $sub = Subscription::create([
            'user_id'              => $user->id,
            'plan_id'              => $plan->id,
            'status'               => 'active',
            'payment_method'       => $data['payment_method'],
            'external_id'          => $data['payment_ref'] ?? null,
            'amount'               => $plan->price * $months,
            'currency'             => $plan->currency,
            'started_at'           => $now,
            'current_period_start' => $now,
            'current_period_end'   => $now->copy()->addMonths($months),
        ]);

        Payment::create([
            'user_id'         => $user->id,
            'subscription_id' => $sub->id,
            'amount'          => $plan->price * $months,
            'currency'        => $plan->currency,
            'status'          => 'completed',
            'payment_method'  => $data['payment_method'],
            'external_id'     => $data['payment_ref'] ?? null,
            'paid_at'         => $now,
        ]);

        return response()->json(['message' => "Subscription activated for {$user->name}.", 'subscription' => $sub->load('plan')]);
    }

    // All redemption requests
    public function redemptions()
    {
        return response()->json(
            Redemption::with('user')->latest()->paginate(30)
        );
    }

    // Approve/reject redemption
    public function processRedemption(Request $request, Redemption $redemption)
    {
        $data = $request->validate([
            'status'      => 'required|in:approved,paid,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $redemption->update([
            'status'      => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? null,
            'paid_at'     => $data['status'] === 'paid' ? now() : null,
        ]);

        return response()->json($redemption->fresh('user'));
    }

    // Revenue overview
    public function overview()
    {
        return response()->json([
            'total_revenue'       => Payment::where('status','completed')->sum('amount'),
            'total_commission'    => Payment::where('status','completed')->sum('commission_amount'),
            'active_subscribers'  => Subscription::where('status','active')->count(),
            'total_subscribers'   => Subscription::distinct('user_id')->count('user_id'),
            'pending_redemptions' => Redemption::where('status','pending')->sum('amount'),
            'monthly_revenue'     => Payment::where('status','completed')
                ->whereMonth('paid_at', now()->month)
                ->sum('amount'),
        ]);
    }
}
