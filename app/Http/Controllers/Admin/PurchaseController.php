<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use QCod\AppSettings\Setting\AppSettings;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'purchases';
        if($request->ajax()){
            $purchases = Purchase::get();
            return DataTables::of($purchases)
                ->addColumn('product',function($purchase){
                    $image = '';
                    if(!empty($purchase->image)){
                        $image = '<span class="avatar avatar-sm mr-2">
						<img class="avatar-img" src="'.asset("storage/purchases/".$purchase->image).'" alt="product">
					    </span>';
                    }                 
                    return $purchase->product.' ' . $image;
                })
                ->addColumn('category',function($purchase){
                    if(!empty($purchase->category)){
                        return $purchase->category->name;
                    }
                })
                ->addColumn('cost_price',function($purchase){
                    return settings('app_currency','$'). ' '. $purchase->cost_price;
                })
                ->addColumn('supplier',function($purchase){
                    return $purchase->supplier->name;
                })
                ->addColumn('expiry_date',function($purchase){
                    return date_format(date_create($purchase->expiry_date),'d M, Y');
                })
                ->addColumn('quantity',function($purchase){
                    // Display inventory as "X packets + Y sheets"
                    return $purchase->formatted_inventory;
                })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("purchases.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('purchases.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-purchase')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    $btn = $editbtn.' '.$deletebtn;
                    return $btn;
                })
                ->rawColumns(['product','action'])
                ->make(true);
        }
        return view('admin.purchases.index',compact(
            'title'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        return view('admin.purchases.create',compact(
            'title','categories','suppliers'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'product_trade'=>'required|max:200',
            'product_scientific'=>'nullable|max:200',
            'category'=>'required',
            'cost_price'=>'required|min:1',
            'packet_quantity'=>'required|integer|min:0',
            'loose_sheets'=>'required|integer|min:0',
            'packet_size'=>'required|integer|min:1',
            'expiry_date'=>'required',
            'expiry_alert_days'=>'nullable|integer|min:0',
            'low_stock_alert_threshold'=>'nullable|integer|min:0',
            'supplier'=>'required',
            'image'=>'file|image|mimes:jpg,jpeg,png,gif',
            // optional product fields
            'product_price'=>'nullable|numeric|min:0',
            'product_unit_type'=>'nullable|in:packet,sheet',
            'product_barcode'=>'nullable|max:255',
        ]);
        $imageName = null;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
        $productName = trim($request->product_trade . ($request->product_scientific ? ' (' . $request->product_scientific . ')' : ''));

        $purchase = Purchase::create([
            'product'=>$productName,
            'product_trade' => $request->product_trade,
            'product_scientific' => $request->product_scientific ?? null,
            'category_id'=>$request->category,
            'supplier_id'=>$request->supplier,
            'cost_price'=>$request->cost_price,
            'quantity'=>$request->packet_quantity,  // keep for backward compatibility
            'packet_quantity'=>$request->packet_quantity,
            'loose_sheets'=>$request->loose_sheets,
            'packet_size'=>$request->packet_size,
            'expiry_alert_days'=>$request->expiry_alert_days ?? null,
            'low_stock_alert_threshold'=>$request->low_stock_alert_threshold ?? null,
            'expiry_date'=>$request->expiry_date,
            'image'=>$imageName,
        ]);

        // If optional product info provided, create a Product linked to this purchase
        if($request->filled('product_price') || $request->filled('product_barcode') || $request->filled('product_unit_type')){
            $price = $request->product_price ?: 0;
            \App\Models\Product::create([
                'purchase_id' => $purchase->id,
                'price' => $price,
                'discount' => $request->product_discount ?? 0,
                'description' => $request->product_description ?? null,
                'barcode' => $request->product_barcode ?? null,
                'unit_type' => $request->product_unit_type ?? 'packet',
            ]);
        }

        $notifications = notify("Purchase has been added");
        return redirect()->route('purchases.index')->with($notifications);
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Purchase $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        $title = 'edit purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        return view('admin.purchases.edit',compact(
            'title','purchase','categories','suppliers'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Purchase $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Purchase $purchase)
    {
        $this->validate($request,[
            'product_trade'=>'required|max:200',
            'product_scientific'=>'nullable|max:200',
            'category'=>'required',
            'cost_price'=>'required|min:1',
            'packet_quantity'=>'required|integer|min:0',
            'loose_sheets'=>'required|integer|min:0',
            'packet_size'=>'required|integer|min:1',
            'expiry_date'=>'required',
            'expiry_alert_days'=>'nullable|integer|min:0',
            'low_stock_alert_threshold'=>'nullable|integer|min:0',
            'supplier'=>'required',
            'image'=>'file|image|mimes:jpg,jpeg,png,gif',
        ]);
        $imageName = $purchase->image;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
        $productName = trim($request->product_trade . ($request->product_scientific ? ' (' . $request->product_scientific . ')' : ''));
        $purchase->update([
            'product'=>$productName,
            'product_trade' => $request->product_trade,
            'product_scientific' => $request->product_scientific ?? null,
            'category_id'=>$request->category,
            'supplier_id'=>$request->supplier,
            'cost_price'=>$request->cost_price,
            'quantity'=>$request->packet_quantity,
            'packet_quantity'=>$request->packet_quantity,
            'loose_sheets'=>$request->loose_sheets,
            'packet_size'=>$request->packet_size,
            'expiry_alert_days'=>$request->expiry_alert_days ?? $purchase->expiry_alert_days,
            'low_stock_alert_threshold'=>$request->low_stock_alert_threshold ?? $purchase->low_stock_alert_threshold,
            'expiry_date'=>$request->expiry_date,
            'image'=>$imageName,
        ]);
        $notifications = notify("Purchase has been updated");
        return redirect()->route('purchases.index')->with($notifications);
    }

    public function reports(){
        $title ='purchase reports';
        return view('admin.purchases.reports',compact('title'));
    }

    public function generateReport(Request $request){
        $this->validate($request,[
            'from_date' => 'required',
            'to_date' => 'required'
        ]);
        $title = 'purchases reports';
        $purchases = Purchase::whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))->get();
        return view('admin.purchases.reports',compact(
            'purchases','title'
        ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        return Purchase::findOrFail($request->id)->delete();
    }
}
