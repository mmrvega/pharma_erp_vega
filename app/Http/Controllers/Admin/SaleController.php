<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Events\PurchaseOutStock;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'sales';
        if($request->ajax()){
            $sales = Sale::latest();
            // Invoice View (Grouped by time)
            if($request->get('type') === 'invoices'){
                $sales = Sale::whereDate('created_at', \Illuminate\Support\Carbon::today())->get();
                // Group by timestamp (to the second)
                $grouped = $sales->groupBy(function($item){
                    return $item->created_at->format('Y-m-d H:i:s');
                });

                $invoices = $grouped->map(function($group){
                    $first = $group->first();
                    return [
                        'id' => $first->id, // Reference ID
                        'display_id' => 'INV-'.$first->id,
                        'invoice_time' => $first->created_at->format('H:i:s'),
                        'items_count' => $group->sum('quantity'),
                        'total_amount' => $group->sum('total_price'),
                        'created_at' => $first->created_at
                    ];
                });

                return DataTables::of($invoices)
                    ->addColumn('action', function($row){
                        return '<button data-id="'.$row['id'].'" data-route="'.route('sales.destroy.invoice', $row['id']).'" class="btn btn-danger btn-sm delete-invoice-btn" title="Delete Entire Invoice"><i class="fas fa-trash"></i></button>';
                    })
                    ->editColumn('total_amount', function($row){
                         return settings('app_currency','$').' '. number_format($row['total_amount'], 2);
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            // Normal individual sales view
            if($request->get('date') === 'today' || $request->get('filter') === 'today' || $request->get('today') == '1'){
                $sales = $sales->whereDate('created_at', \Illuminate\Support\Carbon::today());
            }
            return DataTables::of($sales)
                    ->addIndexColumn()
                    ->addColumn('product',function($sale){
                        $image = '';
                        if(!empty($sale->product)){
                            $image = null;
                            if(!empty($sale->product->purchase->image)){
                                $image = '<span class="avatar avatar-sm mr-2">
                                <img class="avatar-img" src="'.asset("storage/purchases/".$sale->product->purchase->image).'" alt="image">
                                </span>';
                            }
                            return $sale->product->purchase->product. ' ' . $image;
                        }                 
                    })
                    ->addColumn('quantity',function($sale){
                        $unitType = $sale->unit_type ?? 'packet';
                        $unitLabel = $unitType === 'sheet' ? 'sheets' : 'packets';
                        return $sale->quantity . ' ' . $unitLabel;
                    })
                    ->addColumn('total_price',function($sale){                   
                        return settings('app_currency','$').' '. $sale->total_price;
                    })
                    ->addColumn('date',function($row){
                        return date_format(date_create($row->created_at),'d M, Y H:i');
                    })
                    ->addColumn('action', function ($row) {
                        $editbtn = '<a href="'.route("sales.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                        $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('sales.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                        if (!auth()->user()->hasPermissionTo('edit-sale')) {
                            $editbtn = '';
                        }
                        if (!auth()->user()->hasPermissionTo('destroy-sale')) {
                            $deletebtn = '';
                        }
                        $btn = $editbtn.' '.$deletebtn;
                        return $btn;
                    })
                    ->rawColumns(['product','action'])
                    ->make(true);

        }
        $products = Product::get();
        return view('admin.sales.index',compact(
            'title','products',
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create sales';
        $products = Product::get();
        return view('admin.sales.create',compact(
            'title','products'
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
            'product'=>'required',
            'quantity'=>'required|integer|min:1'
        ]);
        $sold_product = Product::find($request->product);
        
        /**update quantity of
            sold item from
         purchases
        **/
        $purchased_item = Purchase::find($sold_product->purchase->id);
        $new_quantity = ($purchased_item->quantity) - ($request->quantity);
        $notification = '';
        if (!($new_quantity < 0)){

            $purchased_item->update([
                'quantity'=>$new_quantity,
            ]);

            /**
             * calcualting item's total price
            **/
            $total_price = ($request->quantity) * ($sold_product->price);
            Sale::create([
                'product_id'=>$request->product,
                'quantity'=>$request->quantity,
                'total_price'=>$total_price,
            ]);

            $notification = notify("Product has been sold");
        } 
        if($new_quantity <=1 && $new_quantity !=0){
            // send notification 
            $product = Purchase::where('quantity', '<=', 1)->first();
            event(new PurchaseOutStock($product));
            // end of notification 
            $notification = notify("Product is running out of stock!!!");
            
        }

        // After creating a single sale, redirect back to dashboard
        return redirect()->route('dashboard')->with($notification);
    }

    /**
     * POS style store: accept multiple items (AJAX or form) and create sales in bulk, return invoice view HTML.
     * Expected payload: items => [{product_id, quantity}], optional print = true
     */
    public function posStore(Request $request)
    {
        $this->validate($request, [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_type' => 'required|in:packet,sheet',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $items = $request->items;
        $createdSales = [];
        $totalAmount = 0;
        // Unified timestamp for the entire batch to allow grouping
        $now = \Illuminate\Support\Carbon::now();

        DB::beginTransaction();
        try {
            foreach ($items as $it) {
                $prod = Product::find($it['product_id']);
                if (!$prod) continue;
                $purchase = Purchase::find($prod->purchase->id);
                if (!$purchase) continue;

                $unitType = $it['unit_type'] ?? 'packet';
                $quantityToSell = $it['quantity'];

                // Smart decrement: handle packet vs sheet selling
                if ($unitType === 'packet') {
                    // Selling by packet - simple decrement
                    if ($purchase->packet_quantity < $quantityToSell) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Insufficient packets for product: '.$prod->purchase->product], 400);
                    }
                    $purchase->packet_quantity -= $quantityToSell;
                } else {
                    // Selling by sheet - needs smart breakdown
                    // First check if we have enough total sheets
                    $totalSheets = ($purchase->packet_quantity * $purchase->packet_size) + $purchase->loose_sheets;
                    if ($totalSheets < $quantityToSell) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Insufficient sheets for product: '.$prod->purchase->product], 400);
                    }

                    // Decrement from loose sheets first
                    if ($purchase->loose_sheets >= $quantityToSell) {
                        $purchase->loose_sheets -= $quantityToSell;
                    } else {
                        // Use remaining loose sheets
                        $quantityToSell -= $purchase->loose_sheets;
                        $purchase->loose_sheets = 0;

                        // Break down packets into sheets
                        $packetsToBreak = ceil($quantityToSell / $purchase->packet_size);
                        $purchase->packet_quantity -= $packetsToBreak;

                        // Add the sheets from broken packets
                        $sheetsFromBrokenPackets = $packetsToBreak * $purchase->packet_size;
                        $purchase->loose_sheets = $sheetsFromBrokenPackets - $quantityToSell;
                    }
                }

                $purchase->save();

                // Use the unit_price sent from frontend (already pro-rated for tablets)
                $unitPrice = (float) $it['unit_price'];
                $itemTotal = $quantityToSell * $unitPrice;
                
                // Create sale with explicit timestamp
                $sale = new Sale([
                    'product_id' => $prod->id,
                    'quantity' => $quantityToSell,
                    'total_price' => $itemTotal,
                    'unit_type' => $unitType,
                ]);
                $sale->created_at = $now;
                $sale->updated_at = $now;
                $sale->save();
                $createdSales[] = $sale;
                $totalAmount += $itemTotal;

                // send low-stock notification if needed
                $totalSheets = ($purchase->packet_quantity * $purchase->packet_size) + $purchase->loose_sheets;
                if ($totalSheets <= $purchase->packet_size && $totalSheets > 0) {
                    event(new PurchaseOutStock($purchase));
                }
            }

            DB::commit();

            // prepare data for invoice
            $invoiceData = [
                'sales' => $createdSales,
                'total' => $totalAmount,
            ];

            // If request expects JSON (AJAX), render a plain invoice HTML (no layout) so
            // it can be opened in a new blank tab without sidebars or other components.
            $invoiceHtml = view('admin.sales.invoice_plain', $invoiceData)->render();

            // Store the invoice HTML in session so it can be shown in a single page
            session(['last_invoice_html' => $invoiceHtml]);

            return response()->json(['success' => true, 'invoice_html' => $invoiceHtml]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show last generated invoice (stored in session) in a single page.
     */
    public function showInvoice()
    {
        $html = session('last_invoice_html');
        if (empty($html)) {
            return redirect()->route('dashboard')->with(notify('No invoice available to display'));
        }

        // Return the raw HTML so the page is exactly as generated for printing
        return response($html);
    }

    /**
     * Show a printable invoice view (invoice_plain) for latest sales or last invoice.
     * Accessible at /sales/invoice_print
     */
    public function invoicePrintLatest()
    {
        // If we have stored last invoice HTML, prefer that (return as-is)
        $html = session('last_invoice_html');
        if (!empty($html)) {
            return response($html);
        }

        // Fallback: render the invoice_plain view with the latest sales (last 10)
        $sales = Sale::latest()->limit(10)->get();
        $total = $sales->sum('total_price');
        return view('admin.sales.invoice_plain', compact('sales', 'total'));
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        $title = 'edit sale';
        $products = Product::get();
        return view('admin.sales.edit',compact(
            'title','sale','products'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        $this->validate($request,[
            'product'=>'required',
            'quantity'=>'required|integer|min:1'
        ]);
        $sold_product = Product::find($request->product);
        /**
         * update quantity of sold item from purchases
        **/
        $purchased_item = Purchase::find($sold_product->purchase->id);
        if(!empty($request->quantity)){
            $new_quantity = ($purchased_item->quantity) - ($request->quantity);
        }
        $new_quantity = $sale->quantity;
        $notification = '';
        if (!($new_quantity < 0)){
            $purchased_item->update([
                'quantity'=>$new_quantity,
            ]);

            /**
             * calcualting item's total price
            **/
            if(!empty($request->quantity)){
                $total_price = ($request->quantity) * ($sold_product->price);
            }
            $total_price = $sale->total_price;
            $sale->update([
                'product_id'=>$request->product,
                'quantity'=>$request->quantity,
                'total_price'=>$total_price,
            ]);

            $notification = notify("Product has been updated");
        } 
        if($new_quantity <=1 && $new_quantity !=0){
            // send notification 
            $product = Purchase::where('quantity', '<=', 1)->first();
            event(new PurchaseOutStock($product));
            // end of notification 
            $notification = notify("Product is running out of stock!!!");
            
        }
        return redirect()->route('sales.index')->with($notification);
    }

    /**
     * Generate sales reports index
     *
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request){
        $title = 'sales reports';
        $sales = collect(); // Empty collection to show form without initial data
        return view('admin.sales.reports',compact(
            'title',
            'sales'
        ));
    }

    /**
     * Generate sales report form post
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request){
        $this->validate($request,[
            'from_date' => 'required',
            'to_date' => 'required',
        ]);
        $title = 'sales reports';
        $sales = Sale::whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))->get();
        return view('admin.sales.reports',compact(
            'sales','title'
        ));
    }


    /**
     * Get today's sales as JSON for thermal printer
     */
    public function todaysSalesForPrint(Request $request)
    {
        $sales = Sale::whereDate('created_at', \Illuminate\Support\Carbon::today())->latest()->get();
        $totalAmount = $sales->sum('total_price');
        
        $formattedSales = $sales->map(function($s){
            $productName = optional(optional($s->product)->purchase)->product ?? 'Unknown';
            $unitType = $s->unit_type ?? 'packet';
            return [
                'product' => $productName,
                'quantity' => $s->quantity,
                'unit_type' => $unitType,
                'unit_label' => $unitType === 'sheet' ? 'sheets' : 'packets',
                'total_price' => $s->total_price,
                'time' => $s->created_at->format('H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'date' => \Illuminate\Support\Carbon::today()->toDateString(),
            'sales' => $formattedSales,
            'total' => $totalAmount,
        ]);
    }

    /**
     * Show today's sales in thermal printer format (view for printing)
     */
    public function todaysSalesPrint(Request $request)
    {
        $sales = Sale::whereDate('created_at', \Illuminate\Support\Carbon::today())->latest()->get();
        $total = $sales->sum('total_price');
        $date = \Illuminate\Support\Carbon::today()->toDateString();

        return view('admin.sales.todays_sales_print', compact('sales', 'total', 'date'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $sale = Sale::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $this->restoreStock($sale);
            $sale->delete();
            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete an entire invoice (all sales with the same timestamp as the given sale ID)
     */
    public function destroyInvoice(Request $request, $id)
    {
        $referenceSale = Sale::findOrFail($id);
        
        // Find all sales with the EXACT same created_at timestamp
        $sales = Sale::where('created_at', $referenceSale->created_at)->get();

        DB::beginTransaction();
        try {
            foreach($sales as $sale){
                $this->restoreStock($sale);
                $sale->delete(); // Soft delete
            }
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper to restore stock for a sale
     */
    private function restoreStock($sale)
    {
        $product = $sale->product;
        if (!$product || !$product->purchase) {
            return; // Cannot restore
        }

        $purchase = $product->purchase;
        $unitType = $sale->unit_type ?? null;
        $qty = (int) $sale->quantity;

        if ($unitType === 'sheet') {
            // restore sold sheets back to loose_sheets
            $purchase->loose_sheets = ($purchase->loose_sheets ?? 0) + $qty;
        } elseif ($unitType === 'packet') {
            // restore sold packets
            $purchase->packet_quantity = ($purchase->packet_quantity ?? 0) + $qty;
        } else {
            // Older records
            if (array_key_exists('quantity', $purchase->getAttributes())) {
                $purchase->quantity = ($purchase->quantity ?? 0) + $qty;
            } else {
                $purchase->packet_quantity = ($purchase->packet_quantity ?? 0) + $qty;
            }
        }
        $purchase->save();
    }
}
