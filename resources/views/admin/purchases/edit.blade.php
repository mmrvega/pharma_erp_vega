@extends('admin.layouts.app')

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Edit Purchase</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Edit Purchase</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
			
			<!-- Edit Supplier -->
			<form method="post" enctype="multipart/form-data" autocomplete="off" action="{{route('purchases.update',$purchase)}}">
				@csrf
				@method("PUT")
				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Medicine Name<span class="text-danger">*</span></label>
								<input class="form-control" type="text" value="{{$purchase->product}}" name="product" >
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Category <span class="text-danger">*</span></label>
								<select class="select2 form-select form-control" name="category"> 
									@foreach ($categories as $category)
										<option {{($purchase->category->id == $category->id) ? 'selected': ''}} value="{{$category->id}}">{{$category->name}}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Supplier <span class="text-danger">*</span></label>
								<select class="select2 form-select form-control" name="supplier"> 
									@foreach ($suppliers as $supplier)
										<option @if($purchase->supplier->id == $supplier->id) selected @endif value="{{$supplier->id}}">{{$supplier->name}}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
				</div>
				
				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Cost Price<span class="text-danger">*</span></label>
								<input class="form-control" value="{{$purchase->cost_price}}" type="text" name="cost_price">
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Packets<span class="text-danger">*</span></label>
								<input class="form-control" value="{{$purchase->packet_quantity}}" type="number" name="packet_quantity" min="0">
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Loose Tablets<span class="text-danger">*</span></label>
								<input class="form-control" value="{{$purchase->loose_tablets}}" type="number" name="loose_tablets" min="0">
							</div>
						</div>
					</div>
				</div>

				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-3">
							<div class="form-group">
								<label>Tablets per Packet<span class="text-danger">*</span></label>
								<input class="form-control" value="{{$purchase->packet_size}}" type="number" name="packet_size" min="1">
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label>Alert Before (days)</label>
								<input class="form-control" value="{{$purchase->expiry_alert_days ?? 0}}" type="number" name="expiry_alert_days" min="0">
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label>Expire Date<span class="text-danger">*</span></label>
								<input class="form-control" value="{{$purchase->expiry_date}}" type="date" name="expiry_date">
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label>Medicine Image</label>
								<input type="file" name="image" class="form-control">
							</div>
						</div>
					</div>
				</div>
				
				
				<div class="submit-section">
					<button class="btn btn-primary submit-btn" type="submit" >Submit</button>
				</div>
			</form>
			<!-- /Edit Supplier -->

			</div>
		</div>
	</div>			
</div>
@endsection	



@push('page-js')
	<!-- Select2 JS -->
	<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
@endpush




