<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trade_database_id')->nullable()->constrained('trade_database')->nullOnDelete();
            $table->foreignId('h4_category_id')->constrained('categories');
            $table->foreignId('m15_category_id')->constrained('categories');
            $table->foreignId('m1_category_id')->constrained('categories');
            $table->foreignId('pair_id')->constrained('pairs');
            $table->foreignId('trading_session_id')->constrained('trading_sessions');
            $table->string('entry_technique');
            $table->enum('status', ['pre_trade', 'completed'])->default('pre_trade');
            $table->enum('result', ['win', 'loss', 'breakeven'])->nullable();
            $table->boolean('followed_rules')->nullable();
            $table->boolean('is_valid')->nullable();
            $table->text('pre_trade_notes')->nullable();
            $table->text('post_trade_notes')->nullable();
            $table->boolean('promote_to_database')->default(false);
            $table->timestamp('trade_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
