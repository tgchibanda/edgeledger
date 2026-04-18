<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('trade_database', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('h4_category_id')->constrained('categories');
            $table->foreignId('m15_category_id')->constrained('categories');
            $table->foreignId('m1_category_id')->constrained('categories');
            $table->foreignId('pair_id')->constrained('pairs');
            $table->foreignId('trading_session_id')->constrained('trading_sessions');
            $table->string('entry_technique', 100);
            $table->enum('result', ['win','loss','breakeven']);
            $table->boolean('followed_rules')->default(true);
            $table->boolean('is_valid')->default(true);
            $table->boolean('is_reference')->default(false);
            $table->decimal('pips_result', 8, 1)->nullable();
            $table->decimal('r_multiple', 5, 2)->nullable();
            $table->string('confluences', 500)->nullable();
            $table->string('mistakes', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('trade_date')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('trade_database'); }
};
