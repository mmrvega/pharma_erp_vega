@extends('admin.layouts.app')

<x-assets.datatables />


@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Purchases Reports</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Generate Purchase Reports</li>
	</ul>
</div>
@endpush

@section('content')
    @isset($purchases)
    <div class="row">
        <div class="col-md-12">
            <!-- Purchases reports-->
            <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                <a href="#generate_report" data-toggle="modal" class="btn btn-primary">Generate Report</a>
                </div>
                <div class="table-responsive">
                        <table id="purchase-table" class="datatable table table-hover table-center mb-0">
                            <thead>
                                <tr>
                                    <th>Medicine Name</th>
                                    <th>Quantity</th>
                                    <th>Total Price</th>
                                    <th>Cost Price</th>
                                    <th>Profit %</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php
                                $sumQty = 0;
                                $sumTotalPrice = 0;
                                $sumTotalCost = 0;
                            @endphp
                            @foreach ($purchases as $purchase)
                                @if(!empty($purchase->category) && !empty($purchase->supplier))
                                    @php
                                        $qty = (int) ($purchase->quantity ?? 0);
                                        $costPerUnit = (float) ($purchase->cost_price ?? 0);
                                        $sellPerUnit = (float) (optional($purchase->purchaseProduct)->price ?? 0);
                                        $totalCost = $costPerUnit * $qty;
                                        $totalPrice = $sellPerUnit * $qty;
                                        $profitPercent = $totalCost > 0 ? round((($totalPrice - $totalCost) / $totalCost) * 100, 2) : 0;
                                        $sumQty += $qty;
                                        $sumTotalPrice += $totalPrice;
                                        $sumTotalCost += $totalCost;
                                    @endphp
                                    <tr>
                                        <td>
                                            <h2 class="table-avatar">
                                                @if(!empty($purchase->image))
                                                    <span class="avatar avatar-sm mr-2">
                                                        <img class="avatar-img" src="{{ asset('storage/purchases/'.$purchase->image) }}" alt="product image">
                                                    </span>
                                                @endif
                                                {{ $purchase->product }}
                                            </h2>
                                        </td>
                                        <td>{{ $qty }}</td>
                                        <td>{{ number_format($totalPrice, 2) }}{{ AppSettings::get('app_currency', '$') }}</td>
                                        <td>{{ number_format($totalCost, 2) }}{{ AppSettings::get('app_currency', '$') }}</td>
                                        <td>{{ $profitPercent }}%</td>
                                        <td>{{ optional($purchase->created_at)->format('d M, Y') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th>{{ $sumQty }}</th>
                                    <th>{{ number_format($sumTotalPrice, 2) }}{{ AppSettings::get('app_currency', '$') }}</th>
                                    <th>{{ number_format($sumTotalCost, 2) }}{{ AppSettings::get('app_currency', '$') }}</th>
                                    <th>
                                        @php
                                            $overallPercent = $sumTotalCost > 0 ? round((($sumTotalPrice - $sumTotalCost) / $sumTotalCost) * 100, 2) : 0;
                                        @endphp
                                        {{ $overallPercent }}%
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Purchases Report -->
        </div>
    </div>
    @endisset


    <!-- Generate Modal -->
    <div class="modal fade" id="generate_report" aria-hidden="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Report</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{route('purchases.report')}}">
                        @csrf
                        <div class="row form-row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>From</label>
                                            <input type="date" name="from_date" class="form-control from_date">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>To</label>
                                            <input type="date" name="to_date" class="form-control to_date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block submit_report">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Generate Modal -->
@endsection


@push('page-js')
<script>
    $(document).ready(function(){
        $('#purchase-table').DataTable({
            dom: 'Bfrtip',		
            buttons: [
                {
                extend: 'collection',
                text: 'Export Data',
                buttons: [
                    {
                        extend: 'pdf',
                        footer: true,
                        exportOptions: {
                            columns: "thead th:not(.action-btn)"
                        }
                    },
                    {
                        extend: 'excel',
                        footer: true,
                        exportOptions: {
                            columns: "thead th:not(.action-btn)"
                        }
                    },
                    {
                        extend: 'csv',
                        footer: true,
                        exportOptions: {
                            columns: "thead th:not(.action-btn)"
                        }
                    },
                    {
                        extend: 'print',
                        footer: true,
                        exportOptions: {
                            columns: "thead th:not(.action-btn)"
                        }
                    }
                ]
                }
            ]
        });
    });
</script>
@endpush