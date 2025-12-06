<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('product_trade')->nullable()->after('product');
            $table->string('product_scientific')->nullable()->after('product_trade');
        });

        // Backfill from existing `product` column where possible.
        // Expected existing format: "Trade Name (Scientific Name)" or just "Trade Name".
        $purchases = DB::table('purchases')->select('id','product')->get();
        foreach ($purchases as $p) {
            $trade = $p->product;
            $scientific = null;
            if (strpos($p->product, '(') !== false && strpos($p->product, ')') !== false) {
                // extract text inside parentheses
                if (preg_match('/^\s*(.*?)\s*\((.*?)\)\s*$/u', $p->product, $m)) {
                    $trade = trim($m[1]);
                    $scientific = trim($m[2]);
                }
            }
            DB::table('purchases')->where('id', $p->id)->update([
                'product_trade' => $trade,
                'product_scientific' => $scientific,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['product_trade','product_scientific']);
        });
    }
};
