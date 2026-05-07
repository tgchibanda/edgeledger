<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // A backtest session — groups many backtest trades together
        Schema::create('backtest_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');                        // e.g. "EURUSD M1 Range Strategy — Jan 2025"
            $table->foreignId('pair_id')->constrained('pairs');
            $table->string('timeframe')->default('M1');    // M1 M5 M15 M30 H1 H4 D1
            $table->text('description')->nullable();       // strategy notes
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->decimal('starting_balance', 12, 2)->default(10000);
            $table->string('status')->default('active');   // active | completed
            $table->timestamps();
        });

        // Individual backtest trades within a session
        Schema::create('backtest_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backtest_session_id')->constrained('backtest_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pair_id')->constrained('pairs');
            $table->foreignId('h4_category_id')->nullable()->constrained('categories');
            $table->foreignId('m15_category_id')->nullable()->constrained('categories');
            $table->foreignId('m1_category_id')->nullable()->constrained('categories');
            $table->string('entry_technique')->nullable();
            $table->string('result')->default('win');      // win | loss | breakeven
            $table->decimal('pips_result', 8, 1)->nullable();
            $table->decimal('r_multiple', 6, 2)->nullable();
            $table->boolean('followed_rules')->default(true);
            $table->text('notes')->nullable();
            $table->timestamp('trade_date')->nullable();
            $table->timestamps();
        });

        // Chart images for backtest trades
        Schema::create('backtest_trade_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backtest_trade_id')->constrained('backtest_trades')->cascadeOnDelete();
            $table->string('timeframe');                   // H4 | M15 | M1
            $table->string('path');
            $table->string('disk')->default('local');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backtest_trade_images');
        Schema::dropIfExists('backtest_trades');
        Schema::dropIfExists('backtest_sessions');
    }
};
