<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\Redemption;
use App\Models\ReferralEarning;
use App\Models\User;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    // Get current user's referral stats and wallet
    public function stats(Request $request)
    {
        $user = $request->user();
        $user->load(['referrals.referee']);

        $totalReferrals = $user->referrals()->count();
        $activeReferrals = $user->referrals()
            ->whereHas('referee.subscriptions', fn($q) => $q->where('status','active'))
            ->count();

        $available = $user->walletBalance();
        $pending   = $user->pendingBalance();
        $totalEarned = $user->referralEarnings()->sum('amount');
        $totalPaid   = $user->redemptions()->where('status','paid')->sum('amount');

        return response()->json([
            'referral_code'   => $user->referral_code,
            'referral_link'   => $user->referral_link,
            'referral_url'    => config('app.url') . '/register?ref=' . $user->referral_code,
            'total_referrals' => $totalReferrals,
            'active_referrals'=> $activeReferrals,
            'commission_pct'  => 50,
            'wallet' => [
                'available'    => $available,
                'pending'      => $pending,
                'total_earned' => $totalEarned,
                'total_paid'   => $totalPaid,
            ],
            'referrals' => $user->referrals->map(fn($r) => [
                'id'         => $r->id,
                'name'       => $r->referee->name,
                'joined'     => $r->created_at->format('M d, Y'),
                'earned'     => $r->earned,
                'is_active'  => $r->referee->activeSubscription() !== null,
            ]),
            'recent_earnings' => $user->referralEarnings()
                ->with('payment')
                ->latest()
                ->take(10)
                ->get()
                ->map(fn($e) => [
                    'amount'    => $e->amount,
                    'status'    => $e->status,
                    'date'      => $e->created_at->format('M d, Y'),
                    'source'    => 'Referral commission',
                ]),
        ]);
    }

    // Track referral when user visits /register?ref=CODE
    public function track(Request $request)
    {
        $ref  = $request->ref;
        $user = User::where('referral_code', $ref)
                    ->orWhere('referral_link', $ref)
                    ->first();

        if (!$user) {
            return response()->json(['valid' => false]);
        }

        return response()->json([
            'valid'        => true,
            'referrer'     => $user->name,
            'referrer_id'  => $user->id,
        ]);
    }

    // Request a payout
    public function redeem(Request $request)
    {
        $data = $request->validate([
            'amount'          => 'required|numeric|min:5',
            'payment_method'  => 'required|in:paypal,bank,crypto',
            'payment_details' => 'required|string|max:255',
        ]);

        $user      = $request->user();
        $available = $user->walletBalance();

        if ($data['amount'] > $available) {
            return response()->json(['message' => "Insufficient balance. Available: \${$available}"], 422);
        }

        // Create redemption request
        $redemption = Redemption::create([
            'user_id'         => $user->id,
            'amount'          => $data['amount'],
            'status'          => 'pending',
            'payment_method'  => $data['payment_method'],
            'payment_details' => $data['payment_details'],
        ]);

        // Mark earnings as redeemed
        $toMark = $user->referralEarnings()
            ->where('status', 'available')
            ->orderBy('created_at')
            ->get();

        $remaining = $data['amount'];
        foreach ($toMark as $earning) {
            if ($remaining <= 0) break;
            $deduct = min($earning->amount, $remaining);
            if ($deduct >= $earning->amount) {
                $earning->update(['status' => 'redeemed']);
            } else {
                // Partial — split the earning
                $earning->update(['amount' => $earning->amount - $deduct]);
                ReferralEarning::create([
                    'user_id'     => $user->id,
                    'payment_id'  => $earning->payment_id,
                    'referral_id' => $earning->referral_id,
                    'amount'      => $deduct,
                    'status'      => 'redeemed',
                    'available_at'=> $earning->available_at,
                ]);
            }
            $remaining -= $deduct;
        }

        return response()->json(['message' => 'Redemption request submitted. Admin will process within 3-5 business days.', 'redemption' => $redemption], 201);
    }

    public function redemptionHistory(Request $request)
    {
        return response()->json(
            $request->user()->redemptions()->latest()->get()
        );
    }
}