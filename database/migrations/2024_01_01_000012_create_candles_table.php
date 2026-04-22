<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('candles', function (Blueprint $table) {
            $table->id();
            $table->enum('pair', ['EURUSD','GBPUSD','AUDUSD']);
            $table->enum('timeframe', ['M1','M5','M15','M30','H1','H4','D1']);
            $table->timestamp('timestamp')->index();
            $table->decimal('open',  12, 5);
            $table->decimal('high',  12, 5);
            $table->decimal('low',   12, 5);
            $table->decimal('close', 12, 5);
            $table->bigInteger('volume')->default(0);
            $table->timestamps();

            // Unique per pair+timeframe+timestamp so imports are idempotent
            $table->unique(['pair','timeframe','timestamp']);
            $table->index(['pair','timeframe','timestamp']);
        });
    }
    public function down(): void { Schema::dropIfExists('candles'); }
};
