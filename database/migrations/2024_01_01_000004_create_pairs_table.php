<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('symbol', 20);
            $table->boolean('is_active')->default(true);
            $table->unique(['user_id','symbol']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pairs'); }
};
