<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCryptoPricesAddAdditionalFields extends Migration
{
    public function up()
    {
        Schema::table('crypto_prices', function (Blueprint $table) {
            // Add new columns after 'price'
            if (!Schema::hasColumn('crypto_prices', 'high')) {
                $table->decimal('high', 24, 8)->after('price')->nullable();
            }
            if (!Schema::hasColumn('crypto_prices', 'low')) {
                $table->decimal('low', 24, 8)->after('high')->nullable();
            }
            if (!Schema::hasColumn('crypto_prices', 'volume')) {
                $table->decimal('volume', 24, 8)->after('low')->default(0);
            }
            if (!Schema::hasColumn('crypto_prices', 'price_change')) {
                $table->decimal('price_change', 24, 8)->after('volume')->default(0);
            }
            if (!Schema::hasColumn('crypto_prices', 'price_change_percent')) {
                $table->decimal('price_change_percent', 10, 4)->after('price_change')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('crypto_prices', function (Blueprint $table) {
            $table->dropColumn([
                'high',
                'low',
                'volume',
                'price_change',
                'price_change_percent'
            ]);
        });
    }
}