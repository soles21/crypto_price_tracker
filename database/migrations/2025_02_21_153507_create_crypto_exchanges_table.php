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
    Schema::create('crypto_exchanges', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->boolean('is_active')->default(true);
        $table->timestamp('last_fetch_at')->nullable();
        $table->timestamp('last_successful_fetch_at')->nullable();
        $table->timestamps();

        $table->index(['name', 'is_active']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_exchanges');
    }
};
