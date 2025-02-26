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
        Schema::create('crypto_prices', function (Blueprint $table) {
            $table->id();
            $table->string('pair');
            $table->decimal('price', 24, 8);
            $table->decimal('previous_price', 24, 8)->nullable();
            $table->decimal('price_change', 24, 8)->nullable();
            $table->decimal('price_change_percentage', 10, 4)->nullable();
            $table->json('exchange_prices');
            $table->json('exchanges');
            $table->timestamps();
            
            // Add index on pair for faster lookups
            $table->index('pair');
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