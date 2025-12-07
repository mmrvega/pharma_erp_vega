@extends('admin.layouts.app')

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Edit Product</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Edit Product</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
				

			<!-- Edit Product -->
				<form method="post" enctype="multipart/form-data" id="update_service" action="{{route('products.update',$product)}}">
					@csrf
                    @method("PUT")
					<div class="service-fields mb-3">
						<div class="row">
							
							<div class="col-lg-12">
								<div class="form-group">
									<label>Product <span class="text-danger">*</span></label>
									<select class="select2 form-select form-control" name="product" id="product-select"> 
										@foreach ($purchases as $purchase)
											@php $selected = (!empty($product->purchase) && $product->purchase->id == $purchase->id) ? 'selected' : '' ; @endphp
											<option {{$selected}} value="{{$purchase->id}}" data-packet-size="{{$purchase->packet_size}}">{{$purchase->product}} ({{$purchase->packet_size}}/pkt)</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
					</div>
					
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Selling Price<span class="text-danger">*</span></label>
									<input class="form-control" type="text" name="price" value="{{$product->price}}">
								</div>
							</div>
	
							<div class="col-lg-6">
								<div class="form-group">
									<label>Discount (%)<span class="text-danger">*</span></label>
									<input class="form-control" value="{{$product->discount}}" type="text" name="discount" value="{{old('discount')}}">
								</div>
							</div>
							
						</div>
					</div>
	
									
					
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Barcode</label>
									<input class="form-control" type="text" name="barcode" value="{{$product->barcode}}" placeholder="Enter product barcode">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Sheets per Packet</label>
									<input class="form-control" type="number" id="packet-size-display" disabled value="{{ optional($product->purchase)->packet_size ?? 1}}">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Sell By Unit</label>
									<select class="form-select form-control" name="unit_type">
										<option value="packet" {{ $product->unit_type == 'packet' ? 'selected' : '' }}>Packet</option>
										<option value="sheet" {{ $product->unit_type == 'sheet' ? 'selected' : '' }}>Sheet</option>
									</select>
								</div>
							</div>
							<div class="col-lg-12">
								<div class="form-group">
									<label>Descriptions <span class="text-danger">*</span></label>
									<textarea class="form-control service-desc" value="{{$product->description}}" name="description">{{$product->description}}</textarea>
								</div>
							</div>
                            
						</div>
					</div>					
					
					<div class="submit-section">
						<button class="btn btn-primary submit-btn" type="submit" name="form_submit" value="submit">Submit</button>
					</div>
				</form>
			<!-- /Edit Product -->
			</div>
		</div>
	</div>			
</div>
@endsection


@push('page-js')
	<script>
		// Auto-populate packet size display when purchase selection changes
		const productSelect = document.querySelector('select[name="product"]');
		if(productSelect){
			productSelect.addEventListener('change', function(){
				const selected = this.options[this.selectedIndex];
				const packetSize = selected.getAttribute('data-packet-size') || '1';
				const display = document.getElementById('packet-size-display');
				if(display) display.value = packetSize;
			});
		}
	</script>
@endpush