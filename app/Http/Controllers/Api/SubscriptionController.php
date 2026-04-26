<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\ReferralEarning;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    // Get active plan + user's subscription status
    public function plan(Request $request)
    {
        $plan = SubscriptionPlan::where('is_active', true)->first();
        $user = $request->user();

        // Get most recent subscription (active or pending)
        $sub = $user->subscriptions()
            ->whereIn('status', ['active','pending','cancelled'])
            ->with('plan')
            ->latest()
            ->first();

        return response()->json([
            'plan'         => $plan,
            'subscription' => $sub,
            'is_active'    => $sub && $sub->isActive(),
            'days_left'    => $sub ? $sub->daysLeft() : 0,
        ]);
    }

    // Manual payment submission — sets pending until admin approves
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'payment_method' => 'required|string',
            'payment_ref'    => 'nullable|string',
            'screenshot'     => 'nullable|file|image|max:5120',
        ]);

        $plan = SubscriptionPlan::where('is_active', true)->firstOrFail();
        $user = $request->user();

        // Handle screenshot — store separately
        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store('payment_proofs', 'local');
        }

        $now = Carbon::now();

        // Cancel any existing pending submission first
        $user->subscriptions()->where('status', 'pending')->delete();

        // Create subscription in pending state — admin activates it
        $sub = Subscription::create([
            'user_id'              => $user->id,
            'plan_id'              => $plan->id,
            'status'               => 'pending',
            'payment_method'       => $data['payment_method'],
            'external_id'          => $data['payment_ref'] ?? null,
            'amount'               => $plan->price,
            'currency'             => $plan->currency,
            'started_at'           => $now,
            'current_period_start' => $now,
            'current_period_end'   => $now->copy()->addMonth(),
        ]);

        // Record pending payment — proof_path stored separately
        Payment::create([
            'user_id'         => $user->id,
            'subscription_id' => $sub->id,
            'amount'          => $plan->price,
            'currency'        => $plan->currency,
            'status'          => 'pending',
            'payment_method'  => $data['payment_method'],
            'external_id'     => $data['payment_ref'] ?? null,
            'proof_path'      => $screenshotPath,
            'paid_at'         => $now,
        ]);

        return response()->json([
            'message'      => 'Payment submitted. Your subscription will be activated once we confirm your payment.',
            'subscription' => $sub->load('plan'),
        ], 201);
    }

    // Renew an existing subscription (record next month's payment)
    public function renew(Request $request)
    {
        $data = $request->validate([
            'payment_method' => 'required|string',
            'payment_ref'    => 'nullable|string',
        ]);

        $user = $request->user();
        $sub  = $user->subscriptions()->where('status','active')->latest()->first();
        $plan = SubscriptionPlan::where('is_active', true)->firstOrFail();

        if (!$sub) {
            return $this->subscribe($request);
        }

        // Extend period
        $sub->update([
            'current_period_start' => $sub->current_period_end,
            'current_period_end'   => $sub->current_period_end->copy()->addMonth(),
            'status'               => 'active',
            'external_id'          => $data['payment_ref'] ?? $sub->external_id,
        ]);

        $payment = Payment::create([
            'user_id'         => $user->id,
            'subscription_id' => $sub->id,
            'amount'          => $plan->price,
            'currency'        => $plan->currency,
            'status'          => 'completed',
            'payment_method'  => $data['payment_method'],
            'external_id'     => $data['payment_ref'] ?? null,
            'paid_at'         => now(),
        ]);

        $this->creditReferralCommission($user, $payment, $plan->price);

        return response()->json(['message' => 'Subscription renewed.', 'subscription' => $sub->fresh('plan')]);
    }

    public function cancel(Request $request)
    {
        $user = $request->user();
        $sub  = $user->subscriptions()->where('status','active')->latest()->first();

        if (!$sub) {
            return response()->json(['message' => 'No active subscription.'], 404);
        }

        $sub->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Subscription cancelled. You retain access until ' . $sub->current_period_end->format('M d, Y') . '.',
            'subscription' => $sub->fresh('plan'),
        ]);
    }

    public function history(Request $request)
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->with('subscription.plan')
            ->latest('paid_at')
            ->get();

        return response()->json($payments);
    }

    private function creditReferralCommission(object $user, Payment $payment, float $amount): void
    {
        if (!$user->referred_by) return;

        $referral = Referral::where('referrer_id', $user->referred_by)
                            ->where('referee_id',  $user->id)
                            ->first();

        if (!$referral) return;

        $commission = round($amount * ($referral->commission_pct / 100), 2);

        ReferralEarning::create([
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
}