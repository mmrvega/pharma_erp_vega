<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockController extends Controller
{
    /**
     * Show stock summary with optional category filter
     */
    public function summary(Request $request)
    {
        $title = 'stock summary';

        $categoryId = $request->query('category_id');

        $today = Carbon::today()->toDateString();

        // Base query: purchases that have packets > 0 and are not expired (or have no expiry)
        $purchasesQuery = Purchase::with('category', 'purchaseProduct')
            ->where('packet_quantity', '>', 0)
            ->where(function($q) use ($today) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $today);
            });

        if ($categoryId) {
            $purchasesQuery->where('category_id', $categoryId);
        }

        $purchases = $purchasesQuery->get();

        // Prepare items list: attach product price where available
        $items = $purchases->map(function($p) {
            return (object) [
                'id' => $p->id,
                'name' => $p->product,
                'category' => optional($p->category)->name,
                'packet_quantity' => $p->packet_quantity,
                'packet_size' => $p->packet_size,
                'loose_sheets' => $p->loose_sheets,
                'cost_price' => $p->cost_price,
                'product_price' => optional($p->purchaseProduct)->price ?? null,
                'total_sheets' => $p->packet_quantity * $p->packet_size + $p->loose_sheets,
                'expiry_date' => $p->expiry_date,
            ];
        });

        // Categories for filter
        $categories = Category::orderBy('name')->get();

        // Top sold item (by quantity)
        $topSold = Sale::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->first();

        // Most profitable item: revenue - cost
        // Compute cost correctly depending on sale unit_type:
        // - if sale.unit_type = 'sheet' then cost per unit = purchases.cost_price / purchases.packet_size
        // - else (packet) cost per unit = purchases.cost_price
        // Use NULLIF to avoid division by zero.
        $costExpr = "SUM(sales.quantity * (CASE WHEN sales.unit_type = 'sheet' THEN (purchases.cost_price / NULLIF(purchases.packet_size,0)) ELSE purchases.cost_price END)) as cost";
        $profitQuery = Sale::select('sales.product_id', DB::raw('COALESCE(SUM(sales.total_price),0) as revenue'), DB::raw($costExpr))
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->join('purchases', 'products.purchase_id', '=', 'purchases.id')
            ->groupBy('sales.product_id')
            ->orderByDesc(DB::raw('(COALESCE(SUM(sales.total_price),0) - COALESCE('.str_replace(' as cost','',$costExpr).',0))'));

        $mostProfit = $profitQuery->first();

        return view('admin.stock.summary', compact('title','items','categories','categoryId','topSold','mostProfit'));
    }
}
