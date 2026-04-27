<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('trading_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('trading_rules');
    }
};