<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('price_aggregates', function (Blueprint $table) {
            $table->decimal('price_change', 20, 8)->default(0)->change();
            $table->decimal('price_change_percent', 8, 4)->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('price_aggregates', function (Blueprint $table) {
            $table->decimal('price_change', 20, 8)->change();
            $table->decimal('price_change_percent', 8, 4)->change();
        });
    }
};