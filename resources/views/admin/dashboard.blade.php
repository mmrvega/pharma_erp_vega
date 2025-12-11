@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    <link rel="stylesheet" href="{{asset('assets/plugins/chart.js/Chart.min.css')}}">
    <style>
        /* Recent sold product square cards */
        .recent-grid { display:flex; flex-wrap:wrap; gap:12px; }
        .recent-card.square { position:relative; width:100%; padding-top:100%; overflow:hidden; border: 1px solid #eee; transition: all 0.2s; }
        .recent-card.square:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-color: #2196f3; }
        .recent-card.square .card-body { position:absolute; inset:0; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:8px; }
        .recent-card .price { font-weight:700; color: #28a745; }
        
        /* POS Cart Styles */
        #pos-cart th { font-size: 0.9rem; background: #f8f9fa; }
        #pos-cart td { vertical-align: middle; }
        .btn-unit-toggle { min-width: 80px; font-weight: 600; }
        .btn-unit-toggle.packet { background-color: #e3f2fd; color: #1976d2; border: 1px solid #bbdefb; }
        .btn-unit-toggle.sheet { background-color: #fff3e0; color: #f57c00; border: 1px solid #ffe0b2; }
        .qty-input { width: 70px; text-align: center; font-weight: bold; }
    </style>
@endpush

@section('content')

@can('create-sale')



<div class="row">
    <!-- POS Section (Left) -->
    <div class="col-lg-8 col-md-12">
        <div class="card h-100">
            <div class="card-header">
                 <h4 class="card-title mb-1 text-white" style="color: white !important;" data-i18n="add_sale">{{ trans_key('add_sale') }}</h4>
            </div>
            <div class="card-body custom-edit-service">
                @php $products = $products ?? []; @endphp
                <!-- Create Sale (inline on dashboard) -->
                <div id="pos-app">
                    <div class="form-group">
                        <label data-i18n="search_products">{{ trans_key('search_products') }}</label>
                        <div style="position:relative;">
                            <input id="barcode-input" class="form-control form-control-lg" autocomplete="off" placeholder="Scan barcode or type product name..." autofocus />
                            <div id="search-results" class="list-group shadow-sm" style="position:absolute; top:100%; left:0; right:0; max-height:300px; overflow-y:auto; display:none; z-index:1000;"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between gap-2 mb-3">
                        <button id="pos-clear" class="btn btn-outline-danger btn-sm">Clear Cart</button>
                        <div class="d-flex gap-2">
                            <button id="pos-make-sale" class="btn btn-info" title="Save sale">
                                <i class="fas fa-save"></i> <span id="btn-text">Save Sale</span>
                            </button>
                            <!-- Print button logic handled in JS -->
                        </div>
                    </div>
                      <div class="bg-light p-2 rounded mt-2" style="border: 1px solid #ddd;">
                        <div class="d-flex justify-content-between">
                            <span style="font-size: 1.1rem;"><strong>Total</strong></span>
                            <span style="font-size: 1.1rem;"><strong id="pos-total">0.00</strong></span>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px;">
                        <table class="table table-bordered table-sm mb-0" id="pos-cart">
                            <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">
                                <tr>
                                    <th>Item</th>
                                    <th style="width:90px; text-align:center">Unit</th>
                                    <th style="width:80px; text-align:center">Quantity</th>
                                    <th style="width:100px; text-align:right">Price</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Cart Items Injected via JS -->
                            </tbody>
                        </table>
                    </div>

                  
                </div>
                <!--/ Create Sale -->
            </div>
        </div>
    </div>

    <!-- Preferred Items (Right - Add Items User Prefers) -->
    <div class="col-lg-4 col-md-12 mt-3 mt-lg-0" data-section="recent-sales">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-1 text-white" style="color: white !important;">Preferred Items</h5>
            </div>
            <div class="card-body p-2" style="max-height: 600px; overflow-y: auto;">
                <!-- Search to Add New Preferred Items -->
                <div class="form-group mb-3">
                    <label for="preferred-item-search" style="font-size: 0.9rem; font-weight: 600;">Add Item to Favorites</label>
                    <input type="text" id="preferred-item-search" class="form-control form-control-sm" placeholder="Search by name or barcode..." autocomplete="off">
                    <div id="preferred-item-results" class="list-group mt-2" style="max-height: 250px; overflow-y: auto; display:none;"></div>
                </div>
                
                <!-- Saved Preferred Items -->
                <div>
                    <label style="font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; display: block;">Your Favorites (Click to Add)</label>
                    <div id="preferred-items-grid" class="row no-gutters" style="gap: 8px;">
                        <div class="col-12 text-muted text-center p-3" style="font-size: 0.85rem;">No favorite items yet. Search above to add.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan

<!-- Tables Row -->
<div class="row mt-3">
    <!-- Today's Sales -->
    <div class="col-md-12 col-lg-6" data-section="todays-sales">
        <div class="card card-table p-3 h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <h4 class="card-title mb-0 text-white mr-3" style="color: white !important;" data-i18n="todays_sales">{{ trans_key('todays_sales') }}</h4>
                    <div class="btn-group btn-group-sm ml-2">
                        <button type="button" class="btn btn-light active" id="btn-view-sales">Items</button>
                        <button type="button" class="btn btn-outline-light" style="color:white; border-color:white;" id="btn-view-invoices">Invoices</button>
                    </div>
                </div>
                <button id="print-today-sales" class="btn btn-sm btn-info" title="{{ trans_key('print') }}">
                    <i class="fas fa-print"></i> <span data-i18n="print">{{ trans_key('print') }}</span>
                </button>
            </div>
            <div class="card-body">
                <!-- Sales Table (Items) -->
                <div class="table-responsive" id="wrapper-sales">
                    <table id="sales-table" class="datatable table table-hover table-center mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>                                                            
                        </tbody>
                    </table>
                </div>
                
                <!-- Invoices Table (Hidden by default) -->
                <div class="table-responsive" id="wrapper-invoices" style="display:none;">
                    <table id="invoices-table" class="table table-hover table-center mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th>Invoice ID</th>
                                <th>Time</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiration notifications card (expired and near-expiry products) -->
    <div class="col-md-12 col-lg-6 mt-3 mt-lg-0" data-section="alerts-card">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0" style="color: white !important;">Expiration Alerts</h4>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-danger">Expired</h5>
                        <ul class="list-group">
                            @forelse($expiredPurchases ?? [] as $p)
                                @php
                                    try { $expiry = \Illuminate\Support\Carbon::parse($p->expiry_date)->toDateString(); } catch(\Exception $e){ $expiry = $p->expiry_date; }
                                @endphp
                                <li class="list-group-item d-flex align-items-center">
                                    @if(!empty($p->image))
                                        <img src="{{ asset('storage/purchases/'.$p->image) }}" alt="" class="rounded" style="width:48px;height:48px;object-fit:cover;margin-right:12px;" />
                                    @else
                                        <div class="rounded d-flex align-items-center justify-content-center" style="width:48px;height:48px;background-color:#f0f0f0;margin-right:12px;">
                                            <i class="fe fe-package" style="font-size:24px;color:#999;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $p->product ?? 'Unknown product' }}</strong>
                                        <div class="small text-muted">Expired on: {{ $expiry }}</div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">No expired products</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-warning">Near Expiry</h5>
                        <ul class="list-group">
                            @forelse($nearExpiryPurchases ?? [] as $p)
                                @php
                                    try { $expiry = \Illuminate\Support\Carbon::parse($p->expiry_date); $daysLeft = \Illuminate\Support\Carbon::now()->startOfDay()->diffInDays($expiry->startOfDay(), false); } catch(\Exception $e){ $expiry = $p->expiry_date; $daysLeft = '-'; }
                                @endphp
                                <li class="list-group-item d-flex align-items-center">
                                    @if(!empty($p->image))
                                        <img src="{{ asset('storage/purchases/'.$p->image) }}" alt="" class="rounded" style="width:48px;height:48px;object-fit:cover;margin-right:12px;" />
                                    @else
                                        <div class="rounded d-flex align-items-center justify-content-center" style="width:48px;height:48px;background-color:#f0f0f0;margin-right:12px;">
                                            <i class="fe fe-package" style="font-size:24px;color:#999;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $p->product ?? 'Unknown product' }}</strong>
                                        <div class="small text-muted">Expires on: {{ is_object($expiry) ? $expiry->toDateString() : $expiry }} — <span class="text-{{ is_numeric($daysLeft) && $daysLeft <=0 ? 'danger' : 'muted' }}">{{ is_numeric($daysLeft) ? $daysLeft . ' day(s)' : $daysLeft }}</span></div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">No near-expiry products</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                
                <!-- Low Stock Alerts Section -->
                <div class="row mt-4 pt-3 border-top">
                    <div class="col-md-12">
                        <h5 class="text-warning">Low Stock Products</h5>
                        <ul class="list-group">
                            @forelse($lowStockPurchases ?? [] as $p)
                                <li class="list-group-item d-flex align-items-center">
                                    @if(!empty($p->image))
                                        <img src="{{ asset('storage/purchases/'.$p->image) }}" alt="" class="rounded" style="width:48px;height:48px;object-fit:cover;margin-right:12px;" />
                                    @else
                                        <div class="rounded d-flex align-items-center justify-content-center" style="width:48px;height:48px;background-color:#f0f0f0;margin-right:12px;">
                                            <i class="fe fe-package" style="font-size:24px;color:#999;"></i>
                                        </div>
                                    @endif
                                    <div style="flex: 1;">
                                        <strong>{{ $p->product ?? 'Unknown product' }}</strong>
                                        <div class="small text-muted">Stock: {{ $p->packet_quantity }} packets (threshold: {{ $p->low_stock_alert_threshold }})</div>
                                    </div>
                                    <div>
                                        <span class="badge badge-warning">Action needed</span>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">No low stock products</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Top Stats Row -->
<div class="row mb-3" data-section="stat-cards">
    <!-- Card 1: Number of Invoices/Sales Today -->
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card bg-white h-100">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-primary bg-primary-light">
                        <i class="fe fe-activity"></i>
                    </span>
                    <div class="dash-count">
                        @php
                            // Count the number of sale records (invoices)
                            $salesCount = \App\Models\Sale::whereDate('created_at', \Carbon\Carbon::today())->count();
                        @endphp
                        <h3>{{ $salesCount ?? 0 }}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    <h6 class="text-muted">Sales Today</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-primary w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Number of Items Sold Today -->
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card bg-white h-100">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-success bg-success-light">
                        <i class="fe fe-activity"></i>
                    </span>
                    <div class="dash-count">
                        @php
                            // Sum the quantity of items sold
                            $itemsSold = \App\Models\Sale::whereDate('created_at', \Carbon\Carbon::today())->sum('quantity');
                        @endphp
                        <h3>{{ $itemsSold ?? 0 }}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    <h6 class="text-muted">Items Sold Today</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-success w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Product select modal (shown when multiple matching products found) -->
<div class="modal fade" id="productSelectModal" tabindex="-1" role="dialog" aria-labelledby="productSelectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productSelectModalLabel">Select product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="product-select-list" class="list-group"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        // Initialize today's sales DataTable and store globally
        window.todaysSalesTable = $('#sales-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('sales.index')}}",
                data: function(d){
                    d.date = 'today';
                }
            },
            columns: [
                {data: 'product', name: 'product'},
                {data: 'quantity', name: 'quantity'},
                {data: 'total_price', name: 'total_price'},
			    {data: 'date', name: 'date'},
            ],
            bLengthChange: false,
            pageLength: 5,
            searching: false,
            ordering: false
        });

        // Toggle View Logic
        $('#btn-view-sales').click(function(){
            $('#wrapper-sales').show();
            $('#wrapper-invoices').hide();
            $(this).addClass('active btn-light').removeClass('btn-outline-light').css('color', '');
            $('#btn-view-invoices').removeClass('active btn-light').addClass('btn-outline-light').css('color', 'white');
            window.todaysSalesTable.ajax.reload();
        });

        $('#btn-view-invoices').click(function(){
            $('#wrapper-sales').hide();
            $('#wrapper-invoices').show();
            $(this).addClass('active btn-light').removeClass('btn-outline-light').css('color', '');
            $('#btn-view-sales').removeClass('active btn-light').addClass('btn-outline-light').css('color', 'white');

            if(!$.fn.DataTable.isDataTable('#invoices-table')){
                window.invoicesTable = $('#invoices-table').DataTable({
                   processing: true,
                   serverSide: true,
                   ajax: {
                       url: "{{route('sales.index')}}",
                       data: function(d){
                           d.type = 'invoices';
                       }
                   },
                   columns: [
                       {data: 'display_id', name: 'display_id'},
                       {data: 'invoice_time', name: 'invoice_time'},
                       {data: 'items_count', name: 'items_count'},
                       {data: 'total_amount', name: 'total_amount'},
                       {data: 'action', name: 'action', orderable: false, searchable: false},
                   ],
                   bLengthChange: false,
                   pageLength: 5,
                   searching: false,
                   ordering: false
                }); 
            } else {
                window.invoicesTable.ajax.reload();
            }
        });

        // Handle delete invoice
        $(document).on('click', '.delete-invoice-btn', function(){
            const id = $(this).data('id');
            const url = $(this).data('route');
            if(confirm('Are you sure you want to delete this invoice? This will delete all items in the invoice and restore stock.')){
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(resp){
                        if(resp.success){
                            if(window.invoicesTable) window.invoicesTable.ajax.reload();
                            if(window.todaysSalesTable) window.todaysSalesTable.ajax.reload();
                            if(typeof Snackbar !== 'undefined'){
                                Snackbar.show({ text: 'Invoice deleted successfully', pos: 'bottom-right', backgroundColor: '#4caf50' });
                            } else {
                                alert('Invoice deleted successfully');
                            }
                        } else {
                            alert('Error: ' + resp.message);
                        }
                    },
                    error: function(err){
                        console.error(err);
                        alert('Failed to delete invoice');
                    }
                });
            }
        });
        
        // Expose a refresh function for POS
        window.refreshDashboardTables = function(){
             if(window.todaysSalesTable) window.todaysSalesTable.ajax.reload();
             if(window.invoicesTable) window.invoicesTable.ajax.reload();
             // Reload stats if feasible - here we simply reload the page partials or keep simple
             // Reloading these tables is critical for user feedback
        };
    });


</script> 
<script src="{{asset('assets/plugins/chart.js/Chart.bundle.min.js')}}"></script>

<script>
// POS JS: handle barcode input, cart management, submit to POS endpoint
;(function(){
    const barcodeInput = document.getElementById('barcode-input');
    const searchResults = document.getElementById('search-results');
    const cartBody = document.querySelector('#pos-cart tbody');
    const posTotal = document.getElementById('pos-total');
    const posClear = document.getElementById('pos-clear');
    const posSubmit = document.getElementById('pos-submit');
    const posMakeSale = document.getElementById('pos-make-sale');
    const btnText = document.getElementById('btn-text');

    // Check if print invoice on sale is enabled
    const printInvoiceOnSale = {{ settings('print_invoice_on_sale', 0) ? 'true' : 'false' }};
    
    // Update button text based on setting
    if (printInvoiceOnSale && btnText) {
        btnText.textContent = 'Save & Print Invoice';
    }

    // Cart storage: Key = "ProductID_UnitType" (e.g. "101_packet", "101_sheet")
    const cart = {};
    let searchTimeout;
    let currentIndex = -1; 

    function renderCart(){
        cartBody.innerHTML = '';
        let total = 0;
        
        const keys = Object.keys(cart);
        if(keys.length === 0){
            cartBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Cart is empty. Scan barcode or click items to add.</td></tr>';
            posTotal.textContent = '0.00';
            return;
        }

        // Reverse keys to prepend items: newest at top
        keys.reverse().forEach(key => {
            const item = cart[key];
            const tr = document.createElement('tr');
            
            // Determine display values
            const isPacket = item.type === 'packet';
            const unitLabel = isPacket ? 'Packet' : 'Sheet';
            const btnClass = isPacket ? 'packet' : 'sheet';
            const price = isPacket ? parseFloat(item.price_per_packet) : parseFloat(item.price_per_sheet);
            const lineTotal = price * parseInt(item.qty);

            // Stock Info
            const stockInfo = ` <small class="text-muted d-block">Stk: ${item.packet_quantity} pkt, ${item.loose_sheets} sht</small>`;

            tr.innerHTML = `
                <td>
                    <span class="font-weight-bold text-dark">${item.name}</span>
                    ${stockInfo}
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-unit-toggle ${btnClass}" onclick="toggleCartUnit('${key}')" title="Click to swap unit">
                        ${unitLabel} <i class="fa fa-refresh ml-1"></i>
                    </button>
                </td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm qty-input mx-auto" min="1" value="${item.qty}" data-key="${key}" onchange="updateCartQty('${key}', this.value)">
                </td>
                <td class="text-right font-weight-bold">${lineTotal.toFixed(2)}</td>
                <td class="text-center">
                    <button class="btn btn-sm text-danger" onclick="removeCartItem('${key}')"><i class="fas fa-times"></i></button>
                </td>
            `;
            cartBody.appendChild(tr);
            total += lineTotal;
        });
        // Reverse again to restore original key order for consistent total calculation
        keys.reverse();
        posTotal.textContent = total.toFixed(2);
    }

    // Expose functions globally for inline onclick events
    window.toggleCartUnit = function(oldKey){
        const item = cart[oldKey];
        if(!item) return;

        // Swap type
        const newType = item.type === 'packet' ? 'sheet' : 'packet';
        const newKey = `${item.id}_${newType}`;

        // Create new item with new type
        const newItem = { ...item, type: newType };

        // If the new key already exists (e.g., swapping Packet to Sheet, but Sheet row exists), merge qty
        if(cart[newKey]){
            cart[newKey].qty += item.qty;
        } else {
            cart[newKey] = newItem;
        }

        // Remove old item
        delete cart[oldKey];
        renderCart();
    };

    window.updateCartQty = function(key, val){
        if(cart[key]){
            const qty = parseInt(val);
            if(qty > 0) {
                cart[key].qty = qty;
            } else {
                // if invalid or 0, reset to 1 or remove? Reset to 1 for safety
                cart[key].qty = 1; 
            }
            renderCart();
        }
    };

    window.removeCartItem = function(key){
        delete cart[key];
        renderCart();
    };

    function addToCart(prod){
        // Ensure numeric
        prod.price_per_packet = parseFloat(prod.price_per_packet) || 0;
        prod.price_per_sheet = parseFloat(prod.price_per_sheet) || 0;
        prod.packet_size = parseInt(prod.packet_size) || 1;
        prod.packet_quantity = parseInt(prod.packet_quantity) || 0;
        prod.loose_sheets = parseInt(prod.loose_sheets) || 0;

        // Check if item has zero stock (no packets and no sheets)
        if(prod.packet_quantity === 0 && prod.loose_sheets === 0){
            alert(`⚠️ ${prod.name}\n\nOut of Stock!\n\nThis item has 0 packets and 0 sheets available.`);
            return;
        }

        // Default to packet
        const type = 'packet';
        const key = `${prod.id}_${type}`;

        if(cart[key]){
            cart[key].qty++;
        } else {
            cart[key] = { 
                id: prod.id, 
                name: prod.name, 
                price_per_packet: prod.price_per_packet, 
                price_per_sheet: prod.price_per_sheet,
                packet_size: prod.packet_size,
                packet_quantity: prod.packet_quantity,
                loose_sheets: prod.loose_sheets,
                qty: 1,
                type: type 
            };
        }
        renderCart();
        searchResults.innerHTML = '';
        searchResults.style.display = 'none';
    }

    // Search Logic
    function clearActive(){
        const items = searchResults.querySelectorAll('.list-group-item');
        items.forEach(it => it.classList.remove('active'));
    }

    function showSearchResults(products){
        searchResults.innerHTML = '';
        currentIndex = -1;
        if(!products || products.length === 0){
            searchResults.style.display = 'none';
            return;
        }
        products.forEach((prod, idx) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action text-left';
            const packetPrice = parseFloat(prod.price_per_packet) || 0;
            item.innerHTML = `<strong>${prod.name}</strong> - ${packetPrice.toFixed(2)} /pkt - Stock: ${prod.packet_quantity}`;
            item.addEventListener('click', ()=>{ addToCart(prod); barcodeInput.focus(); });
            searchResults.appendChild(item);
        });
        searchResults.style.display = 'block';

        if(products.length === 1){
            const only = searchResults.querySelector('.list-group-item');
            if(only) only.classList.add('active');
            currentIndex = 0;
        }
    }

    function showProductModal(products){
        const list = document.getElementById('product-select-list');
        list.innerHTML = '';
        products.forEach(function(prod){
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
            const packetPrice = parseFloat(prod.price_per_packet) || 0;
            const stockText = `${prod.packet_quantity} pkt`;
            const sheetPrice = parseFloat(prod.price_per_sheet) || 0;
            const expiry = prod.expiry_display ? ('Expires: ' + prod.expiry_display) : 'No expiry';
            
            btn.innerHTML = `<div><strong>${prod.name}</strong><div class="small text-muted">${packetPrice.toFixed(2)} /pkt — ${sheetPrice.toFixed(2)} /sht — ${stockText} — <span class=\"text-warning\">${expiry}</span></div></div><div><span class=\"badge badge-primary\">Select</span></div>`;

            btn.addEventListener('click', function(){
                addToCart(prod);
                $('#productSelectModal').modal('hide');
                if(barcodeInput){ barcodeInput.value = ''; barcodeInput.focus(); }
            });
            list.appendChild(btn);
        });
        $('#productSelectModal').modal('show');
    }

    function searchProductsImmediate(query){
        if(!query || query.length < 1) return Promise.resolve({ success:false, data: [] });
        return fetch('/products/barcode/' + encodeURIComponent(query), { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.json())
            .catch(err => { console.error(err); return { success:false, data: [] }; });
    }

    function searchProducts(query){
        if(!query || query.length < 1) { searchResults.style.display = 'none'; return; }
        searchProductsImmediate(query).then(data => {
            if(data && data.success && data.data && data.data.length > 0){
                // Auto-select logic if exact barcode match
                const allSameBarcode = data.data.every(p => p.barcode && p.barcode.toString() === query.toString());
                if(allSameBarcode){
                    const available = data.data.filter(p => (parseInt(p.packet_quantity) > 0) || (parseInt(p.loose_sheets) > 0));
                    if(available.length === 1){
                        addToCart(available[0]);
                        barcodeInput.value = '';
                        return;
                    }
                    showProductModal(available.length ? available : data.data);
                    return;
                }
                showSearchResults(data.data);
            } else {
                searchResults.innerHTML = '<div class="list-group-item text-muted">No products found</div>';
                searchResults.style.display = 'block';
            }
        });
    }

    barcodeInput && barcodeInput.addEventListener('input', function(e){
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        searchTimeout = setTimeout(()=>{ searchProducts(query); }, 150);
    });

    barcodeInput && barcodeInput.addEventListener('keydown', function(e){
        const items = searchResults.querySelectorAll('.list-group-item');
        if(e.key === 'ArrowDown'){
            e.preventDefault();
            if(items.length === 0) return;
            currentIndex = Math.min(currentIndex + 1, items.length - 1);
            clearActive();
            items[currentIndex].classList.add('active');
        } else if(e.key === 'ArrowUp'){
            e.preventDefault();
            if(items.length === 0) return;
            currentIndex = Math.max(currentIndex - 1, 0);
            clearActive();
            items[currentIndex].classList.add('active');
        } else if(e.key === 'Enter'){
            e.preventDefault();
            const active = searchResults.querySelector('.list-group-item.active');
            if(active){ active.click(); barcodeInput.value = ''; searchResults.style.display = 'none'; return; }

            const code = barcodeInput.value.trim();
            if(!code) return;

            // Name search fallback
            if(!/^\d+$/.test(code)){
                searchProductsImmediate(code).then(data => {
                    if(data && data.success && data.data && data.data.length === 1){
                        addToCart(data.data[0]);
                        barcodeInput.value = '';
                    } else if(data && data.success && data.data && data.data.length > 1){
                        showProductModal(data.data);
                    }
                });
                return;
            }

            // Numeric/Barcode search
            searchProductsImmediate(code).then(data => {
                if(data && data.success && data.data && data.data.length === 1){
                    addToCart(data.data[0]);
                    barcodeInput.value = '';
                } else if(data && data.success && data.data && data.data.length > 1){
                    const available = data.data.filter(p => (parseInt(p.packet_quantity) > 0));
                    if(available.length === 1){
                         addToCart(available[0]);
                         barcodeInput.value = '';
                         return;
                    }
                    showProductModal(available.length ? available : data.data);
                }
            });
        } else if(e.key === 'Escape'){
            searchResults.style.display = 'none';
        }
    });

    // Recent items click
    document.addEventListener('click', function(e){
        const btn = e.target.closest && e.target.closest('.recent-sale-add');
        if(!btn) return;
        e.preventDefault();
        const prod = {
            id: btn.getAttribute('data-prod-id'),
            name: btn.getAttribute('data-name'),
            price_per_packet: btn.getAttribute('data-price-per-packet'),
            price_per_sheet: btn.getAttribute('data-price-per-sheet'),
            packet_size: btn.getAttribute('data-packet-size'),
            packet_quantity: btn.getAttribute('data-packet-quantity'),
            loose_sheets: btn.getAttribute('data-loose-sheets')
        };
        addToCart(prod);
        barcodeInput && (barcodeInput.value = '');
        barcodeInput && barcodeInput.focus();
    });

    posClear && posClear.addEventListener('click', function(){
        Object.keys(cart).forEach(k => delete cart[k]);
        renderCart();
        barcodeInput.focus();
    });

    // Preferred Items Management (persistent in Database)
    const preferredItemSearch = document.getElementById('preferred-item-search');
    const preferredItemResults = document.getElementById('preferred-item-results');
    const preferredItemsGrid = document.getElementById('preferred-items-grid');
    let preferredSearchTimeout;
    
    // Load preferred items from Database
    let preferredItems = {};
    
    function loadPreferredItems() {
        fetch('{{ route('preferred-items.index') }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(r => r.json())
        .then(data => {
            preferredItems = {};
            if(data.success && data.data) {
                data.data.forEach(item => {
                    preferredItems[item.id] = item;
                });
            }
            renderPreferredItems();
        }).catch(err => {
            console.warn('Error loading preferred items:', err);
        });
    }
    
    // Display saved preferred items
    function renderPreferredItems() {
        const itemCount = Object.keys(preferredItems).length;
        if(itemCount === 0) {
            preferredItemsGrid.innerHTML = '<div class="col-12 text-muted text-center p-3" style="font-size: 0.85rem;">No favorite items yet. Search above to add.</div>';
            return;
        }
        
        preferredItemsGrid.innerHTML = '';
        Object.values(preferredItems).forEach(item => {
            const card = document.createElement('div');
            card.className = 'col-6 mb-2 px-1';
            card.style.flex = '0 0 calc(50% - 4px)';
            
            const inStock = parseInt(item.packet_quantity) > 0 ? 'text-success' : 'text-danger';
            
            card.innerHTML = `
                <div class="card h-100" style="cursor: pointer; border: 1px solid #ddd; transition: all 0.2s;" onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow=''; this.style.transform=''">
                    <div class="card-body p-2 text-center position-relative" onclick="addPreferredItemToCart('${item.id}')">
                        <div style="font-size: 0.75rem; font-weight: 600; line-height: 1.2; max-height: 2.2em; overflow: hidden; margin-bottom: 4px;">${item.name}</div>
                        <div style="font-size: 0.85rem; color: #28a745; font-weight: 700;">${item.price_per_packet.toFixed(2)}</div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.7rem;"><span class="${inStock}">Stk: ${item.packet_quantity}</span></small>
                        <button class="btn btn-xs btn-link text-danger p-0 mt-1" style="font-size: 0.7rem;" onclick="event.stopPropagation(); removePreferredItem('${item.id}')">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            `;
            preferredItemsGrid.appendChild(card);
        });
    }
    
    // Add item to preferred (from search results)
    window.addToPreferred = function(prod) {
        fetch('{{ route('preferred-items.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ product_id: prod.id })
        }).then(r => r.json())
        .then(data => {
            if(data.success) {
                preferredItems[prod.id] = {
                    id: prod.id,
                    name: prod.name,
                    price_per_packet: parseFloat(prod.price_per_packet) || 0,
                    price_per_sheet: parseFloat(prod.price_per_sheet) || 0,
                    packet_size: parseInt(prod.packet_size) || 1,
                    packet_quantity: parseInt(prod.packet_quantity) || 0,
                    loose_sheets: parseInt(prod.loose_sheets) || 0
                };
                renderPreferredItems();
                preferredItemSearch.value = '';
                preferredItemResults.style.display = 'none';
                preferredItemSearch.focus();
                
                if(typeof Snackbar !== 'undefined') {
                    Snackbar.show({
                        text: '✓ Added to favorites!',
                        pos: 'bottom-right',
                        actionTextColor: '#fff',
                        backgroundColor: '#2196f3'
                    });
                }
            }
        }).catch(err => {
            console.error('Error adding to preferred:', err);
        });
    };
    
    // Add preferred item to cart (from favorites list)
    window.addPreferredItemToCart = function(prodId) {
        if(!preferredItems[prodId]) return;
        addToCart(preferredItems[prodId]);
    };
    
    // Remove item from preferred
    window.removePreferredItem = function(prodId) {
        fetch('{{ route('preferred-items.destroy', '') }}/' + prodId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(r => r.json())
        .then(data => {
            if(data.success) {
                delete preferredItems[prodId];
                renderPreferredItems();
            }
        }).catch(err => {
            console.error('Error removing preferred item:', err);
        });
    };

    if(preferredItemSearch) {
        preferredItemSearch.addEventListener('input', function(e){
            clearTimeout(preferredSearchTimeout);
            const query = e.target.value.trim();
            
            if(query.length < 1) {
                preferredItemResults.style.display = 'none';
                return;
            }

            preferredSearchTimeout = setTimeout(() => {
                searchProductsImmediate(query).then(data => {
                    if(data && data.success && data.data && data.data.length > 0) {
                        preferredItemResults.innerHTML = '';
                        data.data.forEach(prod => {
                            const isAlreadyFavorited = preferredItems[prod.id] ? true : false;
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.className = 'list-group-item list-group-item-action text-left d-flex justify-content-between align-items-center';
                            const price = parseFloat(prod.price_per_packet) || 0;
                            const stockAvailable = parseInt(prod.packet_quantity) > 0;
                            const stockClass = stockAvailable ? 'text-success' : 'text-danger';
                            const btnText = isAlreadyFavorited ? '✓ Favorited' : '+ Add to Favorites';
                            const btnClass = isAlreadyFavorited ? 'badge-success' : 'badge-primary';
                            
                            item.innerHTML = `
                                <div>
                                    <strong>${prod.name}</strong><br>
                                    <small class="text-muted">${price.toFixed(2)} /pkt</small>
                                </div>
                                <span class="badge ${btnClass}">${btnText}</span>
                            `;
                            
                            item.addEventListener('click', function(e){
                                e.preventDefault();
                                if(!isAlreadyFavorited) {
                                    addToPreferred(prod);
                                }
                            });
                            
                            preferredItemResults.appendChild(item);
                        });
                        preferredItemResults.style.display = 'block';
                    } else {
                        preferredItemResults.innerHTML = '<div class="list-group-item text-muted text-center p-2">No items found</div>';
                        preferredItemResults.style.display = 'block';
                    }
                });
            }, 200);
        });

        // Hide results when clicking outside
        document.addEventListener('click', function(e){
            if(!e.target.closest('#preferred-item-search') && !e.target.closest('#preferred-item-results')) {
                preferredItemResults.style.display = 'none';
            }
        });
    }
    
    // Initialize preferred items from database on page load
    loadPreferredItems();

    function submitSale(isPrint = false){
        const items = [];
        Object.values(cart).forEach(i => {
            const qty = parseInt(i.qty) || 0;
            if(qty > 0){
                items.push({ 
                    product_id: i.id, 
                    quantity: qty, 
                    packet_size: i.packet_size, 
                    unit_type: i.type, // 'packet' or 'sheet'
                    unit_price: i.type === 'packet' ? parseFloat(i.price_per_packet) : parseFloat(i.price_per_sheet)
                });
            }
        });

        if(items.length === 0){ alert('Cart is empty'); return; }

        fetch('{{ route('sales.pos') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ items })
        }).then(r => r.json())
        .then(data => {
            if(data.success){
                // Show success notification
                if(typeof Snackbar !== 'undefined') {
                    Snackbar.show({
                        text: '✓ Sale saved successfully!',
                        pos: 'top-right',
                        actionTextColor: '#fff',
                        backgroundColor: '#8dbf42'
                    });
                }
                if(window.refreshDashboardTables) window.refreshDashboardTables();

                // Open invoice print in new window
                if(isPrint) {
                    window.open('{{ route('sales.invoice_print') }}', '_blank');
                }
                // We typically reload page or clear form. 
                // Since this is SPA-like, we just clear form.
                Object.keys(cart).forEach(k => delete cart[k]);
                renderCart();
                barcodeInput.focus();

                // Reload dashboard data via AJAX instead of full page refresh
                reloadDashboardData();
            } else {
                alert(data.message || 'Sale failed');
            }
        }).catch(err => {
            console.error(err);
            alert('Sale request failed');
        });
    }

    // Reload dashboard cards via AJAX
    function reloadDashboardData() {
        // Reload today's sales table via DataTable (use global todaysSalesTable)
        if(typeof todaysSalesTable !== 'undefined' && todaysSalesTable) {
            try {
                todaysSalesTable.ajax.reload();
            } catch(e) {
                console.warn('Could not reload todays sales table:', e);
            }
        }

        // Fetch the dashboard page and extract all updatable sections
        fetch('{{ route('dashboard') }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(r => r.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // List of sections to reload
            const sections = ['recent-sales', 'stat-cards', 'alerts-card'];
            
            sections.forEach(sectionName => {
                const sourceSection = doc.querySelector('[data-section="' + sectionName + '"]');
                const targetSection = document.querySelector('[data-section="' + sectionName + '"]');
                
                if(sourceSection && targetSection) {
                    targetSection.innerHTML = sourceSection.innerHTML;
                    
                    // Re-attach event handlers based on section type
                    if(sectionName === 'recent-sales') {
                        attachRecentSalesClickHandlers();
                    }
                }
            });
        }).catch(err => {
            console.warn('Error reloading dashboard sections:', err);
        });
    }

    // Attach click handlers to recent sale cards
    function attachRecentSalesClickHandlers() {
        document.querySelectorAll('.recent-sale-add').forEach(card => {
            card.addEventListener('click', function(){
                const prod = {
                    id: this.dataset.prodId,
                    name: this.dataset.name,
                    price_per_packet: parseFloat(this.dataset.pricePerPacket) || 0,
                    price_per_sheet: parseFloat(this.dataset.pricePerSheet) || 0,
                    packet_size: parseInt(this.dataset.packetSize) || 1,
                    packet_quantity: parseInt(this.dataset.packetQuantity) || 0,
                    loose_sheets: parseInt(this.dataset.looseSheets) || 0
                };
                addToCart(prod);
            });
        });
    }

    posMakeSale && posMakeSale.addEventListener('click', () => submitSale(printInvoiceOnSale));
    // Button behavior: if printInvoiceOnSale is enabled, it will print after saving

    setTimeout(()=>{ barcodeInput && barcodeInput.focus(); }, 300);
})();

document.getElementById('print-today-sales') && document.getElementById('print-today-sales').addEventListener('click', function(){
    window.open('{{ route('sales.todays-print') }}', '_blank');
});
</script>
@endpush