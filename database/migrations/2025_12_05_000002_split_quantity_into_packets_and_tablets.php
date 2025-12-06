<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SplitQuantityIntoPacketsAndTablets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Add new columns for packet and tablet tracking
            $table->integer('packet_quantity')->default(0)->comment('Number of full packets');
            $table->integer('loose_tablets')->default(0)->comment('Number of loose tablets (0-packet_size)');
            
            // Keep the old 'quantity' column for now (can be dropped in future migration)
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
            $table->dropColumn('packet_quantity');
            $table->dropColumn('loose_tablets');
        });
    }
}
