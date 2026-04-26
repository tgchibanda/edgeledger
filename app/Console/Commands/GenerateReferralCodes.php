<?php
namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateReferralCodes extends Command
{
    protected $signature   = 'referral:generate';
    protected $description = 'Generate referral codes for users who do not have one';

    public function handle(): int
    {
        $users = User::whereNull('referral_code')->get();
        $count = 0;
        foreach ($users as $user) {
            $code = strtoupper(substr(md5($user->id . $user->email . 'el2025'), 0, 8));
            $slug = preg_replace('/[^a-z0-9]/', '', strtolower($user->name)) . $user->id;
            DB::table('users')->where('id', $user->id)->update([
                'referral_code' => $code,
                'referral_link' => $slug,
            ]);
            $count++;
        }
        $this->info("Generated referral codes for {$count} users.");
        return 0;
    }
}