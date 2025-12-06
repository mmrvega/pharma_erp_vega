@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    <link rel="stylesheet" href="{{asset('assets/plugins/chart.js/Chart.min.css')}}">
@endpush

@section('content')

@can('create-sale')
<div class="row">
    <div class="col-md-19 col-lg-11">
        <div class="card">
            <div class="card-body custom-edit-service">
                <h4 class="card-title" data-i18n="add_sale">{{ trans_key('add_sale') }}</h4>
                @php $products = $products ?? []; @endphp
                <!-- Create Sale (inline on dashboard) -->
                <div id="pos-app">
                    <div class="form-group">
                        <label data-i18n="search_products">{{ trans_key('search_products') }}</label>
                        <div style="position:relative;">
                            <input id="barcode-input" class="form-control" autocomplete="off" placeholder="Focus here and scan barcode or type product name" />
                            <div id="search-results" class="list-group" style="position:absolute; top:100%; left:0; right:0; max-height:250px; overflow-y:auto; display:none; z-index:1000;"></div>
                        </div>
                    </div>

                    <table class="table table-sm" id="pos-cart">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th style="width:60px; text-align:center">Qty</th>
                                <th style="width:70px; text-align:center">Unit</th>
                                <th style="width:80px; text-align:right">Price</th>
                                <th style="width:80px; text-align:right">Total</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align:right"><strong>Total</strong></td>
                                <td style="text-align:right"><strong id="pos-total">0.00</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="d-flex justify-content-between gap-2">
                        <button id="pos-clear" class="btn btn-secondary">Clear</button>
                        <div class="d-flex gap-2">
                            <button id="pos-make-sale" class="btn btn-info" title="Save and continue selling">
                                <i class="fas fa-shopping-cart"></i>  Sale
                            </button>
                          <!--  <button id="pos-submit" class="btn btn-success" data-i18n="finalize_sale">{{ trans_key('finalize_sale') }}</button>   remove it for enabling print and sale method-->
                        </div>
                    </div>
                </div>
                <!--/ Create Sale -->
            </div>
        </div>
    </div>
</div>
@endcan

<div class="row">
    <div class="col-md-12 col-lg-6">
        <div class="card card-table p-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-1 text-white" 
    style="color: white !important;" 
    data-i18n="todays_sales">{{ trans_key('todays_sales') }}</h4>
                <button id="print-today-sales" class="btn btn-sm btn-info" title="{{ trans_key('print') }}">
                    <i class="fas fa-print"></i> <span data-i18n="print">{{ trans_key('print') }}</span>
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="sales-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                                                                                      
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal to select/save default printer when none selected --}}
    <div class="modal fade" id="printer-select-modal" tabindex="-1" role="dialog" aria-labelledby="printerSelectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printerSelectModalLabel">Select Default Printer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p data-i18n="save_printer_help">{{ trans_key('save_printer_help') ?? 'If you want to save a default printer for automatic printing, enter its name or identifier below. This will be used as your preferred printer for quick prints.' }}</p>
                    <div class="form-group">
                        <label for="default-printer-input">Printer name / identifier</label>
                        <input type="text" id="default-printer-input" class="form-control" placeholder="e.g. POS-Printer-01" value="{{ AppSettings::get('default_printer') }}" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-i18n="cancel">{{ trans_key('cancel') }}</button>
                    <button type="button" id="save-default-printer" class="btn btn-primary" data-i18n="save_and_print">{{ trans_key('save_and_print') }}</button>
                </div>
            </div>
        </div>
    </div>



<!-- Expiration notifications card (expired and near-expiry products) -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0" style="color: white !important;">Expiration Alerts</h4>
            </div>
            <div class="card-body">
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

<!-- Low Stock Alerts card - REMOVED: now part of Expiration Alerts card above -->

<div class="row">
    <div class="col-xl-12 col-sm-8 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-success">
                        <i class="fe fe-credit-card"></i>
                    </span>
                    <div class="dash-count">
                        @php
                            $todayInvoices = \App\Models\Sale::whereDate('created_at', \Carbon\Carbon::today())->count();
                        @endphp
                        <h3>{{ $todayInvoices }}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    
                    <h6 class="text-muted" data-i18n="invoices_today">{{ trans_key('invoices_today') }}</h6>
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
        var table = $('#sales-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('sales.index')}}",
                data: function(d){
                    // request only today's sales for the dashboard widget
                    d.date = 'today';
                }
            },
            columns: [
                {data: 'product', name: 'product'},
                {data: 'quantity', name: 'quantity'},
                {data: 'total_price', name: 'total_price'},
			{data: 'date', name: 'date'},
            ]
        });
        
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

    // cart stored as {productId: {id,name,price,qty,total}}
    const cart = {};
    let searchTimeout;
    let currentIndex = -1; // for arrow-key navigation

    function renderCart(){
        cartBody.innerHTML = '';
        let total = 0;
        Object.values(cart).forEach(item => {
            const tr = document.createElement('tr');
            // Calculate available quantity based on unit type
            let availDisplay = '';
            if (item.unit_type === 'tablet') {
                availDisplay = ` (${item.total_tablets} tabs)`;
            } else {
                availDisplay = ` (${item.packet_quantity} pkt)`;
            }
            
            // Calculate current price based on unit type
            let currentPrice = item.unit_type === 'tablet' ? item.price_per_tablet : item.price_per_packet;
            let lineTotal = currentPrice * item.qty;
            
            tr.innerHTML = `
                <td>${item.name}${availDisplay}</td>
                <td style="text-align:center"><input class="form-control form-control-sm pos-qty" data-id="${item.id}" value="${item.qty}" style="width:50px; margin:0 auto;" /></td>
                <td style="text-align:center"><button class="btn btn-sm btn-outline-primary pos-unit-toggle" data-id="${item.id}" title="Click to toggle packet/tablet">${item.unit_type === 'tablet' ? 'Tablets' : 'Packets'}</button></td>
                <td style="text-align:right">${currentPrice.toFixed(2)}</td>
                <td style="text-align:right">${lineTotal.toFixed(2)}</td>
                <td style="text-align:center"><button class="btn btn-sm btn-danger pos-remove" data-id="${item.id}">×</button></td>
            `;
            cartBody.appendChild(tr);
            total += lineTotal;
        });
        posTotal.textContent = total.toFixed(2);
    }

    function addToCart(prod){
        // ensure numeric prices
        prod.price_per_packet = parseFloat(prod.price_per_packet) || 0;
        prod.price_per_tablet = parseFloat(prod.price_per_tablet) || 0;
        prod.packet_size = prod.packet_size || 1;
        prod.unit_type = prod.unit_type || 'packet';
        prod.packet_quantity = prod.packet_quantity || 0;
        prod.loose_tablets = prod.loose_tablets || 0;
        prod.total_tablets = (prod.packet_quantity * prod.packet_size) + prod.loose_tablets;
        
        if(cart[prod.id]){
            cart[prod.id].qty += 1;
        } else {
            cart[prod.id] = { 
                id: prod.id, 
                name: prod.name, 
                price_per_packet: prod.price_per_packet, 
                price_per_tablet: prod.price_per_tablet,
                qty: 1, 
                packet_size: prod.packet_size, 
                unit_type: prod.unit_type,
                packet_quantity: prod.packet_quantity,
                loose_tablets: prod.loose_tablets,
                total_tablets: prod.total_tablets
            };
        }
        renderCart();
        searchResults.innerHTML = '';
        searchResults.style.display = 'none';
    }

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
        // If more than one product is returned, prefer to show only available items in a modal
        if(products.length > 1){
            // filter products that have packets or tablets available
            const available = products.filter(p => (parseInt(p.packet_quantity) > 0) || (parseInt(p.loose_tablets) > 0) || (parseInt(p.total_tablets) > 0));
            if(available.length === 1){
                // only one available => add it directly
                addToCart(available[0]);
                barcodeInput.value = '';
                return;
            }
            // if multiple available or none available, show modal (if none available we still show all with out-of-stock notes)
            showProductModal(available.length ? available : products);
            return;
        }

        // otherwise render the inline search results (single item or small set)
        products.forEach((prod, idx) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action text-left';
            // show packet price and total tablets
            const packetPrice = parseFloat(prod.price_per_packet) || 0;
            const tabletPrice = parseFloat(prod.price_per_tablet) || 0;
            item.innerHTML = `<strong>${prod.name}</strong> - ${packetPrice.toFixed(2)} /pkt (${tabletPrice.toFixed(2)}/tab) - Stock: ${prod.packet_quantity} pkt + ${prod.loose_tablets} tabs`;
            item.setAttribute('data-idx', idx);
            item.addEventListener('click', ()=>{ addToCart(prod); barcodeInput.focus(); });
            searchResults.appendChild(item);
        });
        searchResults.style.display = 'block';

        // if only one result, focus it (so Enter will add quickly)
        if(products.length === 1){
            const only = searchResults.querySelector('.list-group-item');
            if(only) only.classList.add('active');
            currentIndex = 0;
        }
    }

    function showProductModal(products){
        const list = document.getElementById('product-select-list');
        list.innerHTML = '';
        products.forEach(function(prod, idx){
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
            const packetPrice = parseFloat(prod.price_per_packet) || 0;
            const tabletPrice = parseFloat(prod.price_per_tablet) || 0;
            const expiry = prod.expiry_display ? ('Expires: ' + prod.expiry_display) : 'No expiry';
            const stockText = `Stock: ${prod.packet_quantity} pkt + ${prod.loose_tablets} tabs`;
            btn.innerHTML = `<div><strong>${prod.name}</strong><div class="small text-muted">${packetPrice.toFixed(2)} /pkt — ${tabletPrice.toFixed(2)} /tab — ${stockText} — <span class=\"text-warning\">${expiry}</span></div></div><div><span class="badge badge-primary">Select</span></div>`;
            btn.addEventListener('click', function(){
                addToCart(prod);
                // hide modal and clear search
                $('#productSelectModal').modal('hide');
                if(barcodeInput){ barcodeInput.value = ''; barcodeInput.focus(); }
            });
            list.appendChild(btn);
        });
        $('#productSelectModal').modal('show');
    }

    // perform immediate search (no debounce), returns promise resolving to data
    function searchProductsImmediate(query){
        if(!query || query.length < 1) {
            return Promise.resolve({ success:false, data: [] });
        }
        return fetch('/products/barcode/' + encodeURIComponent(query), { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.json())
            .catch(err => { console.error(err); return { success:false, data: [] }; });
    }

    // search for products by barcode or name via AJAX (debounced)
    function searchProducts(query){
        if(!query || query.length < 1) {
            searchResults.style.display = 'none';
            return;
        }
        
        searchProductsImmediate(query).then(data => {
            if(data && data.success && data.data && data.data.length > 0){
                // if all returned items share the exact barcode equal to query, auto-add the one with inventory
                const allSameBarcode = data.data.every(p => p.barcode && p.barcode.toString() === query.toString());
                if(allSameBarcode){
                    // if multiple items share same barcode, prefer to show modal when more than one has inventory
                    const available = data.data.filter(p => (parseInt(p.packet_quantity) > 0) || (parseInt(p.loose_tablets) > 0) || (parseInt(p.total_tablets) > 0));
                    if(available.length === 1){
                        addToCart(available[0]);
                        barcodeInput.value = '';
                        return;
                    }
                    // multiple available -> show modal so user picks; none available -> still show modal to inform user
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

    // input event: search as user types (shorter debounce for speed)
    barcodeInput && barcodeInput.addEventListener('input', function(e){
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        searchTimeout = setTimeout(()=>{ searchProducts(query); }, 150);
    });

    // handle keyboard navigation and Enter behaviour
    barcodeInput && barcodeInput.addEventListener('keydown', function(e){
        const items = searchResults.querySelectorAll('.list-group-item');
        if(e.key === 'ArrowDown'){
            e.preventDefault();
            if(items.length === 0) return;
            currentIndex = Math.min(currentIndex + 1, items.length - 1);
            clearActive();
            items[currentIndex].classList.add('active');
            return;
        } else if(e.key === 'ArrowUp'){
            e.preventDefault();
            if(items.length === 0) return;
            currentIndex = Math.max(currentIndex - 1, 0);
            clearActive();
            items[currentIndex].classList.add('active');
            return;
        } else if(e.key === 'Enter'){
            e.preventDefault();
            // if a highlighted item exists, click it
            const active = searchResults.querySelector('.list-group-item.active');
            if(active){ active.click(); barcodeInput.value = ''; searchResults.style.display = 'none'; return; }

            // otherwise perform immediate search and if exactly one result, add it
            const code = barcodeInput.value.trim();
            if(!code) return;
            searchProductsImmediate(code).then(data => {
                if(data && data.success && data.data && data.data.length === 1){
                    addToCart(data.data[0]);
                    barcodeInput.value = '';
                } else if(data && data.success && data.data && data.data.length > 1){
                    // if all results share the exact barcode, auto-add the one with inventory
                    const allSameBarcode = data.data.every(p => p.barcode && p.barcode.toString() === code.toString());
                    if(allSameBarcode){
                        const available = data.data.filter(p => (parseInt(p.packet_quantity) > 0) || (parseInt(p.loose_tablets) > 0) || (parseInt(p.total_tablets) > 0));
                        if(available.length === 1){
                            addToCart(available[0]);
                            barcodeInput.value = '';
                            return;
                        }
                        showProductModal(available.length ? available : data.data);
                        return;
                    }
                    // show results for user to pick
                    showSearchResults(data.data);
                } else {
                    searchResults.innerHTML = '<div class="list-group-item text-muted">No products found</div>';
                    searchResults.style.display = 'block';
                }
            });
            return;
        } else if(e.key === 'Escape'){
            searchResults.style.display = 'none';
            return;
        }
    });

    // handle qty change and remove
    document.addEventListener('input', function(e){
        if(e.target && e.target.classList.contains('pos-qty')){
            const id = e.target.getAttribute('data-id');
            const val = parseInt(e.target.value) || 1;
            if(cart[id]){
                cart[id].qty = val;
                renderCart();
            }
        }
    });
    
    // handle unit type toggle (packet ↔ tablet)
    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('pos-unit-toggle')){
            e.preventDefault();
            const id = e.target.getAttribute('data-id');
            if(cart[id]){
                // Toggle between packet and tablet
                cart[id].unit_type = cart[id].unit_type === 'packet' ? 'tablet' : 'packet';
                renderCart();
            }
        }
    });
    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('pos-remove')){
            const id = e.target.getAttribute('data-id');
            delete cart[id];
            renderCart();
        }
    });

    posClear && posClear.addEventListener('click', function(){
        Object.keys(cart).forEach(k => delete cart[k]);
        renderCart();
        barcodeInput.focus();
    });

    posSubmit && posSubmit.addEventListener('click', function(){
        const items = Object.values(cart).map(i => {
            // Calculate the correct price based on unit type
            const unitPrice = i.unit_type === 'tablet' ? i.price_per_tablet : i.price_per_packet;
            return { 
                product_id: i.id, 
                quantity: i.qty,
                packet_size: i.packet_size,
                unit_type: i.unit_type,
                unit_price: unitPrice
            };
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
                // open invoice_print route in a new tab to show and print the invoice
                window.open('{{ route('sales.invoice_print') }}', '_blank');
                // clear cart
                Object.keys(cart).forEach(k => delete cart[k]);
                renderCart();
                barcodeInput.focus();
                // Refresh the dashboard to update sales table and alerts
                setTimeout(() => {
                    location.reload();
                }, 1500);
              //  alert('Sale complete! Invoice opened in new window.');
            } else {
                alert(data.message || 'Sale failed');
            }
        }).catch(err=>{
            console.error(err);
            alert('Sale request failed');
        });
    });

    // Make Sale button: save sale and continue selling without printing invoice
    const posMakeSale = document.getElementById('pos-make-sale');
    posMakeSale && posMakeSale.addEventListener('click', function(){
        const items = Object.values(cart).map(i => {
            // Calculate the correct price based on unit type
            const unitPrice = i.unit_type === 'tablet' ? i.price_per_tablet : i.price_per_packet;
            return { 
                product_id: i.id, 
                quantity: i.qty,
                packet_size: i.packet_size,
                unit_type: i.unit_type,
                unit_price: unitPrice
            };
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
                // clear cart and focus back to barcode input (continue selling)
                Object.keys(cart).forEach(k => delete cart[k]);
                renderCart();
                barcodeInput.focus();
                // Refresh the dashboard to update sales table and alerts
                setTimeout(() => {
                    location.reload();
                }, 800);
            } else {
                alert(data.message || 'Sale failed');
            }
        }).catch(err=>{
            console.error(err);
            alert('Sale request failed');
        });
    });

    // autofocus barcode input on load
    setTimeout(()=>{ barcodeInput && barcodeInput.focus(); }, 300);
})();

// Print today's sales to thermal printer
document.getElementById('print-today-sales') && document.getElementById('print-today-sales').addEventListener('click', function(){
    // Open the print view in a new tab (similar to invoice)
    window.open('{{ route('sales.todays-print') }}', '_blank');
});
//--kiosk-printing
</script>
@endpush