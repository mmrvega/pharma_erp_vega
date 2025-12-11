@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title">Stock Summary</h4>
                    <form method="get" class="form-inline">
                        <div class="form-group mr-2">
                            <label class="mr-2">Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">All</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (int)($categoryId ?? 0) === $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary">Filter</button>
                    </form>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card p-2 clickable-card" data-filter="all">
                            <h6>Total distinct items in stock</h6>
                            <h3>{{ $items->count() }}</h3>
                            <small class="text-muted">Click to view full info</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-2 clickable-card" data-filter="packets">
                            <h6>Total packets</h6>
                            <h3>{{ $items->sum('packet_quantity') }}</h3>
                            <small class="text-muted">Click to view Name & Packets</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-2 clickable-card" data-filter="sheets">
                            <h6>Total sheets</h6>
                            <h3>{{ $items->sum('total_sheets') }}</h3>
                            <small class="text-muted">Click to view Name & Sheets</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-2 clickable-card" data-filter="available">
                            <h6>medication available</h6>
                            <h3>{{ $items->filter(function($i){ return (($i->packet_quantity ?? 0) > 0) || (($i->loose_sheets ?? 0) > 0); })->count() }}</h3>
                            <small class="text-muted">Click to view full info</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped" id="stockTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Packets</th>
                                <th>Packet size</th>
                                <th>Loose sheets</th>
                                <th>Total sheets</th>
                                <th>Price</th>
                                <th>Cost Price</th>
                                <th>Expiry</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $it)
                                <tr data-packets="{{ $it->packet_quantity ?? 0 }}" data-loose="{{ $it->loose_sheets ?? 0 }}" data-total-sheets="{{ $it->total_sheets ?? 0 }}">
                                    <td>{{ $it->name }}</td>
                                    <td>{{ $it->category }}</td>
                                    <td>{{ $it->packet_quantity }}</td>
                                    <td>{{ $it->packet_size }}</td>
                                    <td>{{ $it->loose_sheets }}</td>
                                    <td>{{ $it->total_sheets }}</td>
                                    <td>{{ is_null($it->product_price) ? '-' : number_format($it->product_price,2) . AppSettings::get('app_currency',' $') }}</td>
                                    <td>{{ number_format($it->cost_price,2) . AppSettings::get('app_currency',' $') }}</td>
                                    <td>{{ $it->expiry_date ? \Carbon\Carbon::parse($it->expiry_date)->format('d M, Y') : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header-white">
                                <h5 class="card-title mb-0">Top Selling Items</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Sold Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($topSold)
                                                <tr>
                                                    <td><strong>{{ optional($topSold->product)->purchase->product ?? optional($topSold->product)->id }}</strong></td>
                                                    <td>{{ $topSold->total_qty }}</td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">No sales found</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header-white">
                                <h5 class="card-title mb-0">Most Profitable Items</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Profit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($mostProfit)
                                                @php
                                                    $prod = \App\Models\Product::find($mostProfit->product_id);
                                                    $profitAmt = ($mostProfit->revenue ?? 0) - ($mostProfit->cost ?? 0);
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ optional($prod->purchase)->product ?? 'ID:'.$mostProfit->product_id }}</strong></td>
                                                    <td class="text-success">{{ number_format($profitAmt,2) }}{{ AppSettings::get('app_currency',' $') }}</td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">No sales found</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    
    // Configurations for different views
    const viewConfigs = {
        all: {
            title: 'All Items',
            headers: ['Name', 'Category', 'Packets', 'Packet size', 'Loose sheets', 'Total sheets', 'Price', 'Cost Price', 'Expiry'],
            indices: [0, 1, 2, 3, 4, 5, 6, 7, 8]
        },
        packets: {
            title: 'Items with Packets',
            headers: ['Name', 'Packets'],
            indices: [0, 2] // Name is 0, Packets is 2
        },
        sheets: {
            title: 'Items with Sheets',
            headers: ['Name', 'Total sheets'],
            indices: [0, 5] // Name is 0, Total Sheets is 5
        },
        available: {
            title: 'Available Items',
            headers: ['Name', 'Category', 'Packets', 'Packet size', 'Loose sheets', 'Total sheets', 'Price', 'Cost Price', 'Expiry'],
            indices: [0, 1, 2, 3, 4, 5, 6, 7, 8]
        }
    };

    function buildTableHtml(config, rows) {
        let thead = '<thead><tr>';
        config.headers.forEach(h => {
            thead += `<th>${h}</th>`;
        });
        thead += '</tr></thead>';

        let tbody = '<tbody>';
        if(rows.length === 0) {
            tbody += `<tr><td colspan="${config.headers.length}" class="text-center text-muted">No items found</td></tr>`;
        } else {
            rows.forEach(r => {
                const cells = r.cells; // Original cells
                tbody += '<tr>';
                config.indices.forEach(i => {
                    tbody += `<td>${cells[i] ? cells[i] : ''}</td>`;
                });
                tbody += '</tr>';
            });
        }
        tbody += '</tbody>';

        return `<table class="table table-striped">${thead}${tbody}</table>`;
    }

    // Save original table rows data for filtering
    // We clone the rows to preserve the original data (indices match the viewConfigs.all.indices)
    const sourceTableBody = document.querySelector('#stockTable tbody');
    const originalRowsData = [];
    if(sourceTableBody) {
        Array.from(sourceTableBody.querySelectorAll('tr')).forEach(tr => {
            const rowData = {
                element: tr,
                packets: parseInt(tr.dataset.packets || '0', 10),
                loose: parseInt(tr.dataset.loose || '0', 10),
                totalSheets: parseInt(tr.dataset.totalSheets || '0', 10),
                // We store the innerHTML of cells to reconstruct new tables
                cells: Array.from(tr.querySelectorAll('td')).map(td => td.innerHTML)
            };
            originalRowsData.push(rowData);
        });
    }

    const tableContainer = document.querySelector('.table-responsive');
    // Store original full HTML to revert if needed (though we rebuild dynamically now)
    const originalContainerHtml = tableContainer ? tableContainer.innerHTML : '';

    const cards = document.querySelectorAll('.clickable-card');
    cards.forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function(){
            const filterType = card.dataset.filter;
            const config = viewConfigs[filterType];

            // Filter rows based on logic
            const filteredRows = originalRowsData.filter(r => {
                if(filterType === 'all') return true;
                if(filterType === 'packets') return r.packets > 0; // Only show rows with packets
                if(filterType === 'sheets') return r.totalSheets > 0; // Only show rows with sheets
                if(filterType === 'available') return (r.packets > 0 || r.loose > 0);
                return true;
            });

            // Build new table
            const newTableHtml = buildTableHtml(config, filteredRows);

            if(tableContainer){
                // Toolbar
                const toolbar = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div><strong>${config.title}</strong> <span class="badge badge-secondary">${filteredRows.length}</span></div>
                        <button id="resetTableBtn" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-sync"></i> Show Default View
                        </button>
                    </div>`;
                
                tableContainer.innerHTML = toolbar + newTableHtml;

                // Reset button logic
                document.getElementById('resetTableBtn').addEventListener('click', function(){
                    // Revert to "All" view dynamically or restore original HTML
                    // Here we simply trigger the 'all' logic again or reload page-like feel
                    // But restoring innerHTML is safer to return to server-rendered state
                    tableContainer.innerHTML = originalContainerHtml; 
                });
                
                // Smooth scroll
                tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
</script>
@endpush