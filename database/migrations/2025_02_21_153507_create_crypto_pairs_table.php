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
    Schema::create('crypto_pairs', function (Blueprint $table) {
        $table->id();
        $table->string('symbol')->unique();  
        $table->string('base_currency');
        $table->string('quote_currency');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        
        $table->index(['symbol', 'is_active']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_pairs');
    }
};
