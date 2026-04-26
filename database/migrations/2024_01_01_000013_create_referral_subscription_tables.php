<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Add referral fields to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 10)->unique()->nullable()->after('role');
            $table->string('referral_link', 100)->unique()->nullable()->after('referral_code');
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete()->after('referral_link');
            $table->timestamp('trial_ends_at')->nullable()->after('referred_by');
        });

        // Track referral signups
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referee_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('commission_pct', 5, 2)->default(50.00);
            $table->decimal('earned', 10, 2)->default(0.00);   // total earned from this referral
            $table->decimal('paid_out', 10, 2)->default(0.00); // total paid out
            $table->timestamps();
        });

        // Subscription plans (admin sets price here)
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('EdgeLedger Pro');
            $table->decimal('price', 10, 2)->default(2.00);
            $table->string('currency', 3)->default('USD');
            $table->string('interval')->default('month'); // month | year
            $table->text('features')->nullable(); // JSON array
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // User subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans');
            $table->string('status')->default('active'); // active | cancelled | expired | trial
            $table->string('payment_method')->default('manual'); // stripe | paypal | manual
            $table->string('external_id')->nullable(); // stripe subscription id
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('started_at');
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });

        // Payment history
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('commission_amount', 10, 2)->default(0);  // referral commission
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('completed'); // completed | failed | refunded
            $table->string('payment_method')->default('manual');
            $table->string('external_id')->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();
        });

        // Referral earnings wallet
        Schema::create('referral_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('referral_id')->constrained('referrals')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending | available | redeemed
            $table->timestamp('available_at')->nullable(); // when it clears
            $table->timestamps();
        });

        // Redemption requests
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending | approved | paid | rejected
            $table->string('payment_method')->nullable(); // paypal | bank | crypto
            $table->string('payment_details')->nullable(); // PayPal email etc
            $table->text('admin_notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('redemptions');
        Schema::dropIfExists('referral_earnings');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('referrals');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['referral_code','referral_link','referred_by']);
        });
    }
};