<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(){
        $title = 'dashboard';
        $total_purchases = Purchase::where('expiry_date','!=',Carbon::now())->count();
        $total_categories = Category::count();
        $total_suppliers = Supplier::count();
        $total_sales = Sale::count();
        
        $pieChart = app()->chartjs
                ->name('pieChart')
                ->type('pie')
                ->size(['width' => 400, 'height' => 200])
                ->labels(['Total Purchases', 'Total Suppliers','Total Sales'])
                ->datasets([
                    [
                        'backgroundColor' => ['#FF6384', '#36A2EB','#7bb13c'],
                        'hoverBackgroundColor' => ['#FF6384', '#36A2EB','#7bb13c'],
                        'data' => [$total_purchases, $total_suppliers,$total_sales]
                    ]
                ])
                ->options([]);
        
        // Expired = expiry_date in the past (before today)
        $total_expired_products = Purchase::whereDate('expiry_date', '<', Carbon::now())->count();
        // Load expired purchases and near-expiry purchases (based on each purchase's expiry_alert_days)
        $allPurchasesWithExpiry = Purchase::whereNotNull('expiry_date')->get();
        $expiredPurchases = $allPurchasesWithExpiry->filter(function($p){
            try{
                return 
                    \Illuminate\Support\Carbon::parse($p->expiry_date)->startOfDay()->lt(\Illuminate\Support\Carbon::now()->startOfDay());
            } catch(\Exception $e){
                return false;
            }
        })->values();

        $nearExpiryPurchases = $allPurchasesWithExpiry->filter(function($p){
            if(empty($p->expiry_alert_days)) return false;
            try{
                $expiry = \Illuminate\Support\Carbon::parse($p->expiry_date)->startOfDay();
                $now = \Illuminate\Support\Carbon::now()->startOfDay();
                $daysLeft = $now->diffInDays($expiry, false);
                return $daysLeft >= 0 && $daysLeft <= (int)$p->expiry_alert_days;
            } catch(\Exception $e){
                return false;
            }
        })->values();
        
        // Low stock alerts: purchases where packet_quantity <= low_stock_alert_threshold
        $lowStockPurchases = Purchase::all()->filter(function($p){
            if(empty($p->low_stock_alert_threshold) || $p->low_stock_alert_threshold <= 0) return false;
            return (int)$p->packet_quantity <= (int)$p->low_stock_alert_threshold;
        })->values();

        $latest_sales = Sale::whereDate('created_at','=',Carbon::now())->get();
        $today_sales = Sale::whereDate('created_at','=',Carbon::now())->sum('total_price');

        // Load products that have an associated purchase with available quantity
        $products = Product::whereHas('purchase', function($q){
            $q->where('quantity', '>', 0);
        })->with('purchase')->get();

        return view('admin.dashboard',compact(
            'title','pieChart','total_expired_products',
            'latest_sales','today_sales','total_categories','products',
            'expiredPurchases','nearExpiryPurchases','lowStockPurchases'
        ));
    }
}
