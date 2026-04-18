<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('journal_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->enum('timeframe', ['H4','M15','M1']);
            $table->string('path');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('journal_images'); }
};
