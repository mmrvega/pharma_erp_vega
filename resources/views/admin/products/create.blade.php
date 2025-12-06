@extends('admin.layouts.app')

@push('page-css')
    
@endpush    

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Add Product</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Add Product</li>
	</ul>
</div>
@endpush


@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
                <!-- Add Product -->
                <form method="post" enctype="multipart/form-data" id="update_service" action="{{route('products.store')}}">
                    @csrf
                    <div class="service-fields mb-3">
                        <div class="row">
                            
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Product <span class="text-danger">*</span></label>
                                    <select class="select2 form-select form-control" name="product" id="product-select"> 
                                        <option value="">-- Select a product --</option>
                                        @foreach ($purchases as $purchase)
                                            <option value="{{$purchase->id}}" data-packet-size="{{$purchase->packet_size}}">
                                                {{$purchase->product}} ({{$purchase->packet_size}} tablets/packet)
                                            </option>
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
                                    <label>Tablets per Packet</label>
                                    <input class="form-control" type="number" id="packet-size-display" disabled>
                                    <small class="text-muted">Auto-filled from the purchase</small>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Sell By Unit <span class="text-danger">*</span></label>
                                    <select class="form-select form-control" name="unit_type">
                                        <option value="packet">Packet (by package)</option>
                                        <option value="tablet">Tablet (individual tablets)</option>
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
                                    <input class="form-control" type="text" name="price" value="{{old('price')}}">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Discount (%)<span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="discount" value="0">
                                </div>
                            </div>
                            
                        </div>
                    </div>

                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Barcode</label>
                                    <input class="form-control" type="text" name="barcode" value="{{old('barcode')}}" placeholder="Enter product barcode">
                                </div>
                            </div>
                        </div>
                    </div>

                                    
                    
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Descriptions <span class="text-danger">*</span></label>
                                    <textarea class="form-control service-desc" name="description">{{old('description')}}</textarea>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn" type="submit" name="form_submit" value="submit">Submit</button>
                    </div>
                </form>
                <!-- /Add Product -->
			</div>
		</div>
	</div>			
</div>
@endsection

@push('page-js')
	<script>
        // Auto-populate packet size when product is selected
        document.getElementById('product-select').addEventListener('change', function(){
            const selected = this.options[this.selectedIndex];
            const packetSize = selected.getAttribute('data-packet-size') || '1';
            document.getElementById('packet-size-display').value = packetSize;
        });
    </script>
@endpush