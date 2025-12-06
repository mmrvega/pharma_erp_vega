@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title" data-i18n="supplier">{{ trans_key('supplier') }}</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}" data-i18n="dashboard">{{ trans_key('dashboard') }}</a></li>
		<li class="breadcrumb-item active" data-i18n="supplier">{{ trans_key('supplier') }}</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<a href="{{route('suppliers.create')}}" class="btn btn-primary float-right mt-2" data-i18n="add_supplier">{{ trans_key('add_supplier') }}</a>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Suppliers -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="supplier-table" class="datatable table table-hover table-center mb-0">
						<thead>
							<tr>
								<th data-i18n="product">{{ trans_key('product') ?? 'Product' }}</th>
								<th data-i18n="name">{{ trans_key('name') ?? 'Name' }}</th>
								<th data-i18n="phone">{{ trans_key('phone') ?? 'Phone' }}</th>
								<th data-i18n="email">{{ trans_key('email') ?? 'Email' }}</th>
								<th data-i18n="address">{{ trans_key('address') ?? 'Address' }}</th>
								<th data-i18n="company">{{ trans_key('company') ?? 'Company' }}</th>
								<th class="action-btn" data-i18n="action">{{ trans_key('action') ?? 'Action' }}</th>
							</tr>
						</thead>
						<tbody>
							{{-- @foreach ($suppliers as $supplier)
							<tr>
								<td>										
									{{$supplier->product}}
								</td>
								<td>{{$supplier->name}}</td>
								<td>{{$supplier->phone}}</td>
								<td>{{$supplier->email}}</td>
								<td>{{$supplier->address}}</td>
								<td>{{$supplier->company}}</td>
								<td>
									<div class="actions">
										<a class="btn btn-sm bg-success-light" href="{{route('edit-supplier',$supplier)}}">
											<i class="fe fe-pencil"></i> Edit
										</a>
										<a data-id="{{$supplier->id}}" href="javascript:void(0);" class="btn btn-sm bg-danger-light deletebtn" data-toggle="modal">
											<i class="fe fe-trash"></i> Delete
										</a>
									</div>
								</td>
							</tr>
							@endforeach							 --}}
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- /Suppliers-->
		
	</div>
</div>

@endsection	

@push('page-js')
<script>
    $(document).ready(function() {
        var table = $('#supplier-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{route('suppliers.index')}}",
            columns: [
                {data: 'product', name: 'product'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'phone', name: 'phone'},
                {data: 'address', name: 'address'},
                {data: 'company',name: 'company'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
        
    });
</script> 
@endpush