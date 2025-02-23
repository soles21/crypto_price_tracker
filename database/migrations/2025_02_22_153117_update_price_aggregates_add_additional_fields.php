<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePriceAggregatesAddAdditionalFields extends Migration
{
    public function up()
    {
        Schema::table('price_aggregates', function (Blueprint $table) {
            // Add new columns
            if (!Schema::hasColumn('price_aggregates', 'price_change')) {
                $table->decimal('price_change', 24, 8)->after('low_price')->default(0);
            }
            if (!Schema::hasColumn('price_aggregates', 'price_change_percent')) {
                $table->decimal('price_change_percent', 10, 4)->after('price_change')->default(0);
            }
            if (!Schema::hasColumn('price_aggregates', 'volume')) {
                $table->decimal('volume', 24, 8)->after('price_change_percent')->default(0);
            }
            if (!Schema::hasColumn('price_aggregates', 'exchange_data')) {
                $table->json('exchange_data')->after('exchange_ids')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('price_aggregates', function (Blueprint $table) {
            $table->dropColumn([
                'price_change',
                'price_change_percent',
                'volume',
                'exchange_data'
            ]);
        });
    }
}