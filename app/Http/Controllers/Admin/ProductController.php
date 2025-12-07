<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use QCod\AppSettings\Setting\AppSettings;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'products';
        if ($request->ajax()) {
            $products = Product::latest();
            return DataTables::of($products)
                ->addColumn('product',function($product){
                    $image = '';
                    if(!empty($product->purchase)){
                        $image = null;
                        if(!empty($product->purchase->image)){
                            $image = '<span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="'.asset("storage/purchases/".$product->purchase->image).'" alt="image">
                            </span>';
                        }
                        return $product->purchase->product. ' ' . $image;
                    }
                })

                ->addColumn('category',function($product){
                    $category = null;
                    if(!empty($product->purchase->category)){
                        $category = $product->purchase->category->name;
                    }
                    return $category;
                })
                ->addColumn('price',function($product){
                    return settings('app_currency','$').' '. $product->price;
                })
                ->addColumn('quantity',function($product){
                    if(!empty($product->purchase)){
                        return $product->purchase->quantity;
                    }
                })
                ->addColumn('expiry_date',function($product){
                    if(!empty($product->purchase)){
                        return date_format(date_create($product->purchase->expiry_date),'d M, Y');
                    }
                })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("products.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('products.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-product')) {
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
        return view('admin.products.index',compact(
            'title'
        ));
    }

    /**
     * Search products by barcode OR product name (AJAX)
     * Returns array of matching products
     *
     * @param string $query
     * @return \Illuminate\Http\JsonResponse
     */
    public function barcodeLookup($query)
    {
        // Query DB directly for matching products to improve speed
        $q = trim($query);

        // Join purchases so we can order by purchase inventory (prefer packets available)
        $products = Product::with('purchase')
            ->join('purchases', 'purchases.id', '=', 'products.purchase_id')
            ->where(function($subQ){
                $subQ->where('purchases.packet_quantity', '>', 0)
                     ->orWhere('purchases.loose_sheets', '>', 0)
                     ->orWhere('purchases.quantity', '>', 0);
            })
            ->where(function($subQ) use ($q) {
                $subQ->where('products.barcode', $q)
                      ->orWhere('purchases.product', 'like', '%'.$q.'%');
            })
            ->orderBy('purchases.packet_quantity', 'desc')
            ->orderBy('purchases.loose_sheets', 'desc')
            ->orderBy('products.id', 'asc')
            ->select('products.*')
            ->limit(12)
            ->get();

        if ($products->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No products found', 'data' => []], 404);
        }

        // return array of matching products with numeric price for quicker client use
        $data = $products->map(function($product) {
            $purchase = $product->purchase;
            $totalSheets = ($purchase->packet_quantity * $purchase->packet_size) + $purchase->loose_sheets;
            $pricePerPacket = (float) $product->price;
            $pricePerSheet = $purchase->packet_size > 0 ? $pricePerPacket / $purchase->packet_size : 0;
            
            return [
                'id' => $product->id,
                'name' => $product->purchase->product,
                'price_per_packet' => $pricePerPacket,
                'price_per_sheet' => $pricePerSheet,
                'packet_quantity' => $purchase->packet_quantity,
                'loose_sheets' => $purchase->loose_sheets,
                'total_sheets' => $totalSheets,
                'packet_size' => $purchase->packet_size ?? 1,
                'unit_type' => $product->unit_type ?? 'packet',
                'barcode' => $product->barcode,
                'expiry_date' => !empty($purchase->expiry_date) ? $purchase->expiry_date : null,
                'expiry_display' => !empty($purchase->expiry_date) ? date_format(date_create($purchase->expiry_date),'d M, Y') : null,
            ];
        })->values()->toArray();

        return response()->json(['success' => true, 'data' => $data]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'add product';
        $purchases = Purchase::get();
        return view('admin.products.create',compact(
            'title','purchases'
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
            'product'=>'required|max:200',
            'price'=>'required|min:1',
            'discount'=>'nullable',
            'description'=>'nullable|max:255',
            'barcode'=>'nullable|max:255',
        ]);
        $price = $request->price;
        if($request->discount >0){
           $price = $request->discount * $request->price;
        }
        Product::create([
            'purchase_id'=>$request->product,
            'price'=>$price,
            'discount'=>$request->discount,
            'description'=>$request->description,
            'barcode' => $request->barcode,
        ]);
        $notification = notify("Product has been added");
        return redirect()->route('products.index')->with($notification);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $title = 'edit product';
        $purchases = Purchase::get();
        return view('admin.products.edit',compact(
            'title','product','purchases'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $this->validate($request,[
            'product'=>'required|max:200',
            'price'=>'required',
            'discount'=>'nullable',
            'description'=>'nullable|max:255',
            'barcode'=>'nullable|max:255',
        ]);

        $price = $request->price;
        if($request->discount >0){
           $price = $request->discount * $request->price;
        }
       $product->update([
            'purchase_id'=>$request->product,
            'price'=>$price,
            'discount'=>$request->discount,
            'description'=>$request->description,
            'barcode' => $request->barcode,
        ]);
        $notification = notify('product has been updated');
        return redirect()->route('products.index')->with($notification);
    }

     /**
     * Display a listing of expired resources.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function expired(Request $request){
        $title = "expired Products";
        if($request->ajax()){
            $products = Purchase::whereDate('expiry_date', '<=', Carbon::now())->get();
            return DataTables::of($products)
                ->addColumn('product', function($purchase) {
                    $image = '';
                    if (!empty($purchase->image)) {
                        $image = '<span class="avatar avatar-sm mr-2">'
                            . '<img class="avatar-img" src="' . asset("storage/purchases/" . $purchase->image) . '" alt="image">'
                            . '</span>';
                    }
                    return $purchase->product . ' ' . $image;
                })

                ->addColumn('category', function($purchase) {
                    return $purchase->category ? $purchase->category->name : null;
                })

                ->addColumn('price', function($purchase) {
                    return settings('app_currency', '$') . ' ' . $purchase->cost_price;
                })

                ->addColumn('quantity', function($purchase) {
                    return $purchase->quantity;
                })

                ->addColumn('discount', function($purchase) {
                    // If a related product record exists, prefer its discount value
                    if (!empty($purchase->purchaseProduct) && isset($purchase->purchaseProduct->discount)) {
                        return $purchase->purchaseProduct->discount;
                    }
                    return null;
                })

                ->addColumn('expiry_date', function($purchase) {
                    return date_format(date_create($purchase->expiry_date), 'd M, Y');
                })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("products.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('products.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-product')) {
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

        return view('admin.products.expired',compact(
            'title',
        ));
    }

    /**
     * Display a listing of out of stock resources.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function outstock(Request $request){
        $title = "outstocked Products";
        if($request->ajax()){
            $products = Product::whereHas('purchase', function($q){
                return $q->where('quantity', '<=', 0);
            })->get();
            return DataTables::of($products)
                ->addColumn('product',function($product){
                    $image = '';
                    if(!empty($product->purchase)){
                        $image = null;
                        if(!empty($product->purchase->image)){
                            $image = '<span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="'.asset("storage/purchases/".$product->purchase->image).'" alt="image">
                            </span>';
                        }
                        return $product->purchase->product. ' ' . $image;
                    }
                })
               
                ->addColumn('category',function($product){
                    $category = null;
                    if(!empty($product->purchase->category)){
                        $category = $product->purchase->category->name;
                    }
                    return $category;
                })
                ->addColumn('price',function($product){
                    return settings('app_currency','$').' '. $product->price;
                })
                ->addColumn('quantity',function($product){
                    if(!empty($product->purchase)){
                        return $product->purchase->quantity;
                    }
                })
                ->addColumn('expiry_date',function($product){
                    if(!empty($product->purchase)){
                        return date_format(date_create($product->purchase->expiry_date),'d M, Y');
                    }
                })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("products.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('products.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-product')) {
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
        $product = Purchase::where('quantity', '<=', 0)->first();
        return view('admin.products.outstock',compact(
            'title',
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
        return Product::findOrFail($request->id)->delete();
    }
}
