<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPacketAndUnitType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add packet_size to purchases (e.g., 10 tablets per packet)
        Schema::table('purchases', function (Blueprint $table) {
            $table->integer('packet_size')->default(1)->comment('Number of tablets/units per packet');
        });

        // Add unit_type to products (packet or tablet)
        Schema::table('products', function (Blueprint $table) {
            $table->enum('unit_type', ['packet', 'tablet'])->default('packet')->comment('Unit of measurement: packet or tablet');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('packet_size');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('unit_type');
        });
    }
}
