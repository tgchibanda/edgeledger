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
    // Quick count for nav badge
    public function pendingCount()
    {
        $count = Subscription::where('status', 'pending')->count();
        return response()->json(['count' => $count]);
    }

    // Serve payment proof image to admin
    public function serveProof(Request $request, \App\Models\Payment $payment)
    {
        if (!$payment->proof_path) {
            abort(404);
        }
        $path = storage_path('app/' . $payment->proof_path);
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->file($path);
    }

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
        $subs = Subscription::with(['user', 'plan', 'payments' => function($q) {
                $q->latest()->limit(1);
            }])
            ->whereIn('status', ['active','pending','cancelled'])
            ->latest()
            ->paginate(50);
        return response()->json($subs);
    }

    // Manually activate a subscription (admin approves payment or records cash)
    public function activateSubscription(Request $request, User $user)
    {
        $data = $request->validate([
            'payment_method' => 'nullable|string',
            'payment_ref'    => 'nullable|string',
            'months'         => 'nullable|integer|min:1|max:12',
        ]);
        $months = $data['months'] ?? 1;
        $plan   = SubscriptionPlan::where('is_active', true)->firstOrFail();
        $now    = now();

        // Check if there's already a pending subscription to activate
        $pendingSub = $user->subscriptions()->where('status', 'pending')->latest()->first();

        if ($pendingSub) {
            // Activate the pending subscription
            $pendingSub->update([
                'status'               => 'active',
                'current_period_start' => $now,
                'current_period_end'   => $now->copy()->addMonths($months),
                'external_id'          => $data['payment_ref'] ?? $pendingSub->external_id,
            ]);

            // Update the associated pending payment to completed
            $pendingSub->payments()->where('status', 'pending')->update([
                'status'  => 'completed',
                'paid_at' => $now,
            ]);

            $payment = $pendingSub->payments()->latest()->first();
            if ($payment) {
                $this->creditReferralCommission($user, $payment, $plan->price);
            }

            return response()->json([
                'message'      => "Subscription activated for {$user->name}.",
                'subscription' => $pendingSub->fresh('plan'),
            ]);
        }

        // No pending sub — create a fresh one (admin manually adding)
        // Cancel any existing active subscriptions first
        $user->subscriptions()->where('status', 'active')->update([
            'status'       => 'cancelled',
            'cancelled_at' => $now,
        ]);

        $sub = Subscription::create([
            'user_id'              => $user->id,
            'plan_id'              => $plan->id,
            'status'               => 'active',
            'payment_method'       => $data['payment_method'] ?? 'manual',
            'external_id'          => $data['payment_ref'] ?? null,
            'amount'               => $plan->price * $months,
            'currency'             => $plan->currency,
            'started_at'           => $now,
            'current_period_start' => $now,
            'current_period_end'   => $now->copy()->addMonths($months),
        ]);

        $payment = Payment::create([
            'user_id'         => $user->id,
            'subscription_id' => $sub->id,
            'amount'          => $plan->price * $months,
            'currency'        => $plan->currency,
            'status'          => 'completed',
            'payment_method'  => $data['payment_method'] ?? 'manual',
            'external_id'     => $data['payment_ref'] ?? null,
            'paid_at'         => $now,
        ]);

        $this->creditReferralCommission($user, $payment, $plan->price * $months);

        return response()->json([
            'message'      => "Subscription activated for {$user->name}.",
            'subscription' => $sub->load('plan'),
        ]);
    }

    private function creditReferralCommission(User $user, Payment $payment, float $amount): void
    {
        if (!$user->referred_by) return;
        $referral = \App\Models\Referral::where('referrer_id', $user->referred_by)
                                         ->where('referee_id', $user->id)->first();
        if (!$referral) return;
        $commission = round($amount * ($referral->commission_pct / 100), 2);
        \App\Models\ReferralEarning::create([
            'user_id'      => $user->referred_by,
            'payment_id'   => $payment->id,
            'referral_id'  => $referral->id,
            'amount'       => $commission,
            'status'       => 'available',
            'available_at' => now(),
        ]);
        $referral->increment('earned', $commission);
        $payment->update(['commission_amount' => $commission]);
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
            'pending_activations' => Subscription::where('status','pending')->count(),
            'total_subscribers'   => Subscription::distinct('user_id')->count('user_id'),
            'pending_redemptions' => Redemption::where('status','pending')->sum('amount'),
            'monthly_revenue'     => Payment::where('status','completed')
                ->whereMonth('paid_at', now()->month)
                ->sum('amount'),
        ]);
    }
}