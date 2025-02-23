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
    Schema::create('price_aggregates', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pair_id')->constrained('crypto_pairs');
        $table->decimal('average_price', 20, 8);
        $table->decimal('high_price', 20, 8);
        $table->decimal('low_price', 20, 8);
        $table->decimal('price_change', 20, 8);
        $table->decimal('price_change_percent', 8, 4);
        $table->integer('number_of_sources');
        $table->json('exchange_ids');  // Array of exchange IDs used in calculation
        $table->timestamp('calculated_at');
        $table->timestamps();

        $table->index(['pair_id', 'calculated_at']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_aggregates');
    }
};
