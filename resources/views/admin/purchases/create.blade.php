@extends('admin.layouts.app')

@push('page-css')
	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Add Purchase</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Add Purchase</li>
	</ul>
</div>
@endpush


@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
				
				<!-- Add Medicine -->
				<form method="post" enctype="multipart/form-data" autocomplete="off" action="{{route('purchases.store')}}">
					@csrf
					<div class="row mb-3">
						<div class="col-12 col-md-6">
							<div class="form-group">
								<label class="form-label">Trade Name <span class="text-danger">*</span></label>
								<input class="form-control form-control-lg" type="text" name="product_trade" required placeholder="e.g. Panadol">
							</div>
						</div>
						<div class="col-12 col-md-6">
							<div class="form-group">
								<label class="form-label">Scientific Name</label>
								<input class="form-control" type="text" name="product_scientific" placeholder="e.g. Paracetamol">
							</div>
						</div>
					</div>

					<!-- Optional: Product details section (create Product record linked to this Purchase) -->
					<div class="service-fields mb-3 border-top pt-3">
						<h5>Add product listing (optional)</h5>
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group">
									<label>Selling Price</label>
									<input class="form-control" type="text" name="product_price" placeholder="e.g. 1500">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Sell By Unit</label>
									<select class="form-select form-control" name="product_unit_type">
										<option value="packet">Packet</option>
										<option value="tablet">Tablet</option>
									</select>
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Barcode</label>
									<input class="form-control" type="text" name="product_barcode" placeholder="Optional barcode">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label>Product Description</label>
									<textarea class="form-control" name="product_description" rows="2"></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-12 col-md-6">
							<label class="form-label">Category <span class="text-danger">*</span></label>
							<select class="form-select select2" name="category">
								@foreach ($categories as $category)
									<option value="{{$category->id}}">{{$category->name}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-12 col-md-6">
							<label class="form-label">Supplier <span class="text-danger">*</span></label>
							<select class="form-select select2" name="supplier">
								@foreach ($suppliers as $supplier)
									<option value="{{$supplier->id}}">{{$supplier->name}}</option>
								@endforeach
							</select>
						</div>
					</div>
					
					
					<div class="row mb-3">
						<div class="col-12 col-md-4">
							<label class="form-label">Cost Price <span class="text-danger">*</span></label>
							<input class="form-control" type="text" name="cost_price" placeholder="e.g. 1200">
						</div>
						<div class="col-12 col-md-4">
							<label class="form-label">Packets</label>
							<input class="form-control" type="number" name="packet_quantity" value="0" min="0" placeholder="Full packets">
						</div>
						<div class="col-12 col-md-4">
							<label class="form-label">Loose Tablets</label>
							<input class="form-control" type="number" name="loose_tablets" value="0" min="0" placeholder="Loose tablets">
						</div>
					</div>

					<div class="row mb-3">
						<div class="col-12 col-md-4">
							<label class="form-label">Tablets / Packet <span class="text-danger">*</span></label>
							<input class="form-control" type="number" name="packet_size" value="1" min="1" placeholder="e.g. 10">
						</div>
						<div class="col-12 col-md-4">
							<label class="form-label">Alert Before (days)</label>
							<input class="form-control" type="number" name="expiry_alert_days" value="0" min="0" placeholder="Days">
						</div>
						<div class="col-12 col-md-4">
							<label class="form-label">Low Stock Alert (packets)</label>
							<input class="form-control" type="number" name="low_stock_alert_threshold" value="0" min="0" placeholder="Alert when packets ≤">
						</div>
					</div>

					<div class="row mb-3">
						<div class="col-12">
							<label class="form-label">Medicine Image</label>
							<input type="file" name="image" class="form-control">
						</div>
					</div>

					<div class="row mb-3">
						<div class="col-12 col-md-6">
							<label class="form-label">Expire Date <span class="text-danger">*</span></label>
							<input class="form-control" type="date" name="expiry_date">
						</div>
					</div>

					<div class="d-flex justify-content-end mt-3">
						<button class="btn btn-lg btn-primary" type="submit">Submit Purchase</button>
					</div>
					</form>
				<!-- /Add Medicine -->

			</div>
		</div>
	</div>			
</div>
@endsection

@push('page-js')
	<!-- Datetimepicker JS -->
	<script src="{{asset('assets/js/moment.min.js')}}"></script>
	<script src="{{asset('assets/js/bootstrap-datetimepicker.min.js')}}"></script>	
@endpush

