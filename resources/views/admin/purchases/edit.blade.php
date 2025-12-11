@extends('admin.layouts.app')

@push('page-css')

<style>
/* Small visual upgrade for the quick-load select */
#quickPurchaseSelect {
	max-width: 100%;
	border-radius: .375rem;
}
.select2-container--default .select2-selection--single {
	border-radius: .375rem;
	height: calc(1.5em + .75rem + 2px);
}
</style>
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
			<!-- This form can be used on the AJAX editor page (no id in URL). JS will set the correct action when a purchase is loaded. -->
			<form id="purchaseEditForm" method="post" enctype="multipart/form-data" autocomplete="off" action="{{ isset($purchase) ? route('purchases.update', $purchase) : '#' }}" data-update-url-template="{{ url('purchases') }}/:id">
				@csrf
				@method("PUT")
				{{-- Quick-select another purchase to copy values into the form --}}
				<div class="mb-3">
					<label class="form-label">Load Purchase</label>
					{{-- FIX: Use null coalescing operator ?? to prevent undefined variable errors --}}
					<select id="quickPurchaseSelect" class="select2 form-select" data-current-purchase-id="{{ $purchase->id ?? '' }}">
						<option value="">-- Choose purchase to load --</option>
						{{-- FIX: Ensure $allPurchases is defined and properties exist --}}
						@foreach($allPurchases ?? [] as $p)
							<option value="{{ $p->id }}" {{ ($purchase->id ?? '') == $p->id ? 'selected' : '' }}>
								{{ $p->product_scientific ? $p->product_trade . ' (' . $p->product_scientific . ')' : $p->product_trade ?? $p->product ?? 'Purchase #'.$p->id }}
								{{ optional($p->purchaseProduct)->barcode ? ' - ' . $p->purchaseProduct->barcode : '' }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Trade Name <span class="text-danger">*</span></label>
								<input class="form-control" type="text" value="{{ old('product_trade', optional($purchase ?? null)->product_trade ?? optional($purchase ?? null)->product ?? '') }}" name="product_trade" id="product_trade">
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Scientific Name</label>
								<input class="form-control" type="text" value="{{ old('product_scientific', optional($purchase ?? null)->product_scientific ?? '') }}" name="product_scientific" id="product_scientific">
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Category <span class="text-danger">*</span></label>
								<select id="category_select" class="select2 form-select form-control" name="category"> 
																	@foreach ($categories ?? [] as $category)
																		<option value="{{$category->id}}" @if(optional(optional($purchase ?? null)->category)->id == $category->id) selected @endif>{{$category->name}}</option>
																	@endforeach
																</select>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Supplier <span class="text-danger">*</span></label>
								<select id="supplier_select" class="select2 form-select form-control" name="supplier"> 
																	@foreach ($suppliers ?? [] as $supplier)
																		<option value="{{$supplier->id}}" @if(optional(optional($purchase ?? null)->supplier)->id == $supplier->id) selected @endif>{{$supplier->name}}</option>
																	@endforeach
																</select>
							</div>
						</div>
					</div>
				</div>

				{{-- Optional: Product details section (same as create) --}}
				<div class="service-fields mb-3 border-top pt-3">
					<h5>Product listing (optional)</h5>
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Selling Price</label>
								<input id="product_price" class="form-control" type="text" name="product_price" placeholder="e.g. 1500" value="{{ old('product_price', optional(optional($purchase ?? null)->purchaseProduct)->price) }}">
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Sell By Unit</label>
								<select id="product_unit_type" class="form-select form-control" name="product_unit_type">
									<option value="packet" @if(optional(optional($purchase ?? null)->purchaseProduct)->unit_type == 'packet') selected @endif>Packet</option>
									<option value="sheet" @if(optional(optional($purchase ?? null)->purchaseProduct)->unit_type == 'sheet') selected @endif>Sheet</option>
								</select>
							</div>
						</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label>Barcode</label>
							<div class="input-group">
								<input class="form-control" type="text" name="product_barcode" placeholder="Optional barcode" id="product_barcode_input" value="{{ old('product_barcode', optional(optional($purchase ?? null)->purchaseProduct)->barcode) }}">
								<button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#barcodePrintModal" title="Print barcode">
									<i class="fas fa-print"></i> Print
								</button>
							</div>
						</div>
					</div>
					</div>
				</div>
				
				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Cost Price<span class="text-danger">*</span></label>
								<input id="cost_price" class="form-control" value="{{ old('cost_price', optional($purchase ?? null)->cost_price ?? '') }}" type="text" name="cost_price">
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Packets<span class="text-danger">*</span></label>
								<input id="packet_quantity" class="form-control" value="{{ old('packet_quantity', optional($purchase ?? null)->packet_quantity ?? 0) }}" type="number" name="packet_quantity" min="0">
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Loose Sheets<span class="text-danger">*</span></label>
								<input id="loose_sheets" class="form-control" value="{{ old('loose_sheets', optional($purchase ?? null)->loose_sheets ?? 0) }}" type="number" name="loose_sheets" min="0">
							</div>
						</div>
					</div>
				</div>

				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-3">
							<div class="form-group">
								<label>Sheets per Packet<span class="text-danger">*</span></label>
								<input id="packet_size" class="form-control" value="{{ old('packet_size', optional($purchase ?? null)->packet_size ?? 1) }}" type="number" name="packet_size" min="1">
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label>Alert Before (days)</label>
								<input id="expiry_alert_days" class="form-control" value="{{ old('expiry_alert_days', optional($purchase ?? null)->expiry_alert_days ?? 0) }}" type="number" name="expiry_alert_days" min="0">
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label>Expire Date<span class="text-danger">*</span></label>
								<input id="expiry_date" class="form-control" value="{{ old('expiry_date', optional($purchase ?? null)->expiry_date ?? '') }}" type="date" name="expiry_date">
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
				
				
				<div class="submit-section d-flex justify-content-end">
					<button class="btn btn-secondary mr-2" type="submit" name="save_and_continue" value="1">Save and Continue</button>
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

	<script>
		document.addEventListener('DOMContentLoaded', function(){
			const quick = document.getElementById('quickPurchaseSelect');
			if(!quick) return;
			
			// Initialize Select2 if available
			if(typeof $ !== 'undefined' && $.fn && $.fn.select2) {
				$('#quickPurchaseSelect').select2({
					placeholder: '-- Choose purchase to load --',
					allowClear: true,
					width: '100%'
				}).on('select2:select', function (e) {
					// Ensure native change fires for our listener, or handle directly
					quick.dispatchEvent(new Event('change'));
				});
			}

			// If an id is provided in the query string (?id=...), pre-select and auto-load it
			try{
				const params = new URLSearchParams(window.location.search);
				const autoId = params.get('id');
				if(autoId){
					if(typeof $ !== 'undefined' && $.fn && $.fn.select2){
						$('#quickPurchaseSelect').val(autoId).trigger('change');
					} else {
						quick.value = autoId;
						quick.dispatchEvent(new Event('change'));
					}
				}
			} catch(e){ /* ignore */ }

			const form = document.getElementById('purchaseEditForm');
			
			quick.addEventListener('change', function(e){
				// Get value reliably
				const id = quick.value;
				// Get current ID from dataset to ensure we have the latest state (not closed-over stale value)
				const currentId = quick.dataset.currentPurchaseId || '';

				if(!id) return; // nothing selected or cleared
				
				// Prevent reloading the currently loaded purchase
				if(String(id) === String(currentId)) {
					return;
				}

				// Construct URL - handle potential base tag or relative paths by using window.location.origin
				const url = `/purchases/${id}/json`;

				console.log('Loading purchase:', id, 'from', url);

				fetch(url, {
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'Content-Type': 'application/json',
					}
				})
				.then(r => {
					if(!r.ok) throw new Error('Server returned ' + r.status);
					return r.json();
				})
				.then(data => {
					// set form action using template
					if(form && form.dataset.updateUrlTemplate){
						// Update form action to enable "PUT" on the correct resource
						form.action = form.dataset.updateUrlTemplate.replace(':id', data.id);
					}
					// populate fields
					document.getElementById('product_trade').value = data.product_trade || data.product || '';
					document.getElementById('product_scientific').value = data.product_scientific || '';
					document.getElementById('cost_price').value = data.cost_price || '';
					
					// Handle null coalescing for numbers
					document.getElementById('packet_quantity').value = (data.packet_quantity !== null && data.packet_quantity !== undefined) ? data.packet_quantity : 0;
					document.getElementById('loose_sheets').value = (data.loose_sheets !== null && data.loose_sheets !== undefined) ? data.loose_sheets : 0;
					document.getElementById('packet_size').value = (data.packet_size !== null && data.packet_size !== undefined) ? data.packet_size : 1;
					document.getElementById('expiry_date').value = data.expiry_date || '';
					
					// product optional fields
					// Handle both snake_case (Laravel default) and camelCase (if mapped)
					const pProd = data.purchase_product || data.purchaseProduct;
					if(pProd){
						document.getElementById('product_price').value = pProd.price ?? '';
						document.getElementById('product_barcode_input').value = pProd.barcode ?? '';
						// unit type
						if(document.getElementById('product_unit_type')){
							document.getElementById('product_unit_type').value = pProd.unit_type || 'packet';
						}
					} else {
						// Clear optional fields if no product details found
						document.getElementById('product_price').value = '';
						document.getElementById('product_barcode_input').value = '';
					}

					// selects: category and supplier
					if(data.category_id){
						const catEl = document.getElementById('category_select');
						if(catEl){
							catEl.value = data.category_id;
							if(typeof $ !== 'undefined' && $.fn && $.fn.select2){
								$(catEl).trigger('change');
							}
						}
					}
					if(data.supplier_id){
						const supEl = document.getElementById('supplier_select');
						if(supEl){
							supEl.value = data.supplier_id;
							if(typeof $ !== 'undefined' && $.fn && $.fn.select2){
								$(supEl).trigger('change');
							}
						}
					}
					// update current id marker
					quick.dataset.currentPurchaseId = data.id;
				})
				.catch(err => {
					console.error('Failed to load purchase', err);
					alert('Failed to load purchase details. See console.');
				});
			});
		});
	</script>
@endpush