<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('crypto_prices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pair_id')->constrained('crypto_pairs');
        $table->foreignId('exchange_id')->constrained('crypto_exchanges');
        $table->decimal('price', 20, 8);
        $table->decimal('volume', 20, 8)->nullable();
        $table->timestamp('fetched_at');
        $table->boolean('is_valid')->default(true);
        $table->json('raw_data')->nullable();
        $table->timestamps();

        $table->index(['pair_id', 'exchange_id', 'fetched_at']);
        $table->index(['fetched_at', 'is_valid']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_prices');
    }
};
