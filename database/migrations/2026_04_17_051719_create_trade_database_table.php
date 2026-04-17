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
        Schema::create('trade_database', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('h4_category_id')->constrained('categories');
            $table->foreignId('m15_category_id')->constrained('categories');
            $table->foreignId('m1_category_id')->constrained('categories');
            $table->foreignId('pair_id')->constrained('pairs');
            $table->foreignId('trading_session_id')->constrained('trading_sessions');
            $table->string('entry_technique');
            $table->enum('result', ['win', 'loss', 'breakeven']);
            $table->boolean('followed_rules')->default(true);
            $table->boolean('is_valid')->default(true); // true if followed_rules = true
            $table->boolean('is_reference')->default(false); // promoted win
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_database');
    }
};
