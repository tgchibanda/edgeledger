<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invalid_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pair_id')->constrained('pairs');
            $table->text('notes')->nullable();       // what was wrong with this trade
            $table->text('lesson')->nullable();      // what to do instead
            $table->timestamps();
        });

        Schema::create('invalid_trade_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invalid_trade_id')->constrained('invalid_trades')->cascadeOnDelete();
            $table->string('type');       // 'mtf' | 'entry' | 'correct'
            $table->string('path');
            $table->string('disk')->default('local');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invalid_trade_images');
        Schema::dropIfExists('invalid_trades');
    }
};