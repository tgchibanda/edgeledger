<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('trade_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_database_id')->constrained('trade_database')->cascadeOnDelete();
            $table->enum('timeframe', ['H4','M15','M1']);
            $table->string('path');
            $table->string('disk')->default('local');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('trade_images'); }
};
