<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['email'=>'required|email','password'=>'required']);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email'=>['Invalid credentials.']]);
        }
        if (!$user->is_active) {
            return response()->json(['message'=>'Account is disabled. Contact your administrator.'], 403);
        }
        $token = $user->createToken('edgeledger')->plainTextToken;
        return response()->json(['token'=>$token,'user'=>$user]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:8|confirmed',
            'referral_code' => 'nullable|string',
        ]);

        // Find referrer
        $referrer = null;
        if (!empty($data['referral_code'])) {
            $referrer = User::where('referral_code', $data['referral_code'])
                           ->orWhere('referral_link', $data['referral_code'])
                           ->first();
        }

        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'role'          => 'user',
            'is_active'     => true,
            'referred_by'   => $referrer?->id,
            'trial_ends_at' => now()->addMonth(), // 1 month free trial
        ]);

        // Create referral record
        if ($referrer) {
            Referral::create([
                'referrer_id'    => $referrer->id,
                'referee_id'     => $user->id,
                'commission_pct' => 50.00,
            ]);
        }

        $token = $user->createToken('edgeledger')->plainTextToken;
        return response()->json([
            'token' => $token,
            'user'  => $user,
            'trial' => [
                'active'    => true,
                'ends_at'   => $user->trial_ends_at,
                'days_left' => 30,
            ],
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Logged out.']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $sub  = null;
        try {
            $sub = $user->subscriptions()->where('status','active')->with('plan')->latest()->first();
        } catch(\Exception $e) {}

        return response()->json([
            ...$user->toArray(),
            'has_access'   => $user->hasAccess(),
            'on_trial'     => $user->onTrial(),
            'trial_ends_at'=> $user->trial_ends_at,
            'trial_days_left' => $user->trialDaysLeft(),
            'subscription' => $sub,
        ]);
    }
}