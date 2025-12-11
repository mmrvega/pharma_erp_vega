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
								<label class="form-label">Scientific Name <span class="text-danger">*</span></label>
								<input class="form-control" type="text" name="product_scientific" required placeholder="e.g. Paracetamol">
							</div>
						</div>
					</div>

					<!-- Optional: Product details section (create Product record linked to this Purchase) -->
					<div class="service-fields mb-3 border-top pt-3">
						<h5>Add product listing </h5>
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group">
									<label>Selling Price <span class="text-danger">*</span></label>
									<input class="form-control" type="text" name="product_price" required placeholder="e.g. 1500">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Sell By Unit</label>
									<select class="form-select form-control" name="product_unit_type">
										<option value="packet">Packet</option>
										<option value="sheet">Sheet</option>
									</select>
								</div>
							</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Barcode</label>
								<div class="input-group">
									<input class="form-control" type="text" name="product_barcode" placeholder="Optional barcode" id="product_barcode_input">
									<button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#barcodePrintModal" title="Print barcode">
										<i class="fas fa-print"></i> Print
									</button>
								</div>
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
							<label class="form-label">Loose Sheets</label>
							<input class="form-control" type="number" name="loose_sheets" value="0" min="0" placeholder="Loose sheets">
						</div>
					</div>

					<div class="row mb-3">
						<div class="col-12 col-md-4">
							<label class="form-label">Sheets / Packet <span class="text-danger">*</span></label>
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
						<div class="col-12 col-md-6">
							<label class="form-label">Expire Date <span class="text-danger">*</span></label>
							<input class="form-control" type="date" name="expiry_date">
						</div>
					</div>
									
					<div class="row mb-3">
						<div class="col-12">
							<label class="form-label">Medicine Image</label>
							<input type="file" name="image" class="form-control">
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


					

					<div class="d-flex justify-content-end mt-3">
						<button class="btn btn-lg btn-secondary mr-2" type="submit" name="save_and_continue" value="1">Save and Continue</button>
						<button class="btn btn-lg btn-primary" type="submit">save</button>
					</div>
					</form>
				<!-- /Add Medicine -->

			</div>
		</div>
	</div>			
</div>
@endsection

<!-- Barcode Print Modal -->
<div class="modal fade" id="barcodePrintModal" tabindex="-1" aria-labelledby="barcodePrintLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="barcodePrintLabel">Print Barcode</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="barcodeInput" class="form-label">Barcode Value</label>
					<input type="text" class="form-control form-control-lg" id="barcodeInput" placeholder="Enter or paste barcode" autofocus>
					<small class="form-text text-muted">Use current field value if empty</small>
				</div>
				<div class="form-group mt-3">
					<label for="barcodeFormat" class="form-label">Format</label>
					<select class="form-select" id="barcodeFormat">
						<option value="code128">Code 128</option>
						<option value="ean13">EAN-13</option>
						<option value="code39">Code 39</option>
						<option value="upca">UPC-A</option>
					</select>
				</div>
				<div class="alert alert-info" role="alert">
					<small>The PDF will be sized as a standard barcode label (100mm × 50mm). You can print it directly to a barcode printer.</small>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="printBarcodeBtn">Print Barcode</button>
			</div>
		</div>
	</div>
</div>

@push('page-js')
	<!-- JSBarcode for barcode generation -->
	<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js" defer></script>
	
	<!-- html2pdf for PDF generation -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" defer></script>

	<!-- Datetimepicker JS -->
	<script src="{{asset('assets/js/moment.min.js')}}"></script>
	<script src="{{asset('assets/js/bootstrap-datetimepicker.min.js')}}"></script>	
	
	<script>
		// Barcode printing functionality
		function initBarcodePrinting() {
			const barcodeInput = document.getElementById('barcodeInput');
			const barcodeFormat = document.getElementById('barcodeFormat');
			const printBarcodeBtn = document.getElementById('printBarcodeBtn');
			const productBarcodeInput = document.getElementById('product_barcode_input');
			const barcodePrintModal = document.getElementById('barcodePrintModal');

			if (!barcodeInput || !printBarcodeBtn) {
				console.log('Barcode elements not found');
				return;
			}

			console.log('Barcode printing initialized');

			// When modal opens, populate barcode input with current value
			if (barcodePrintModal) {
				// Use jQuery event for Bootstrap 3/4 modal (project uses bootstrap.min.js from assets)
				$('#barcodePrintModal').on('show.bs.modal', function() {
					console.log('Modal opened');
					if (productBarcodeInput && productBarcodeInput.value) {
						barcodeInput.value = productBarcodeInput.value;
						console.log('Prefilled with: ' + barcodeInput.value);
					}
					barcodeInput.focus();
				});
			}

			// Print barcode function
			printBarcodeBtn.addEventListener('click', function(e) {
				e.preventDefault();
				console.log('Print button clicked');
				
				// Check if libraries are loaded
				if (typeof JsBarcode === 'undefined') {
					alert('Barcode library is still loading. Please wait a moment and try again.');
					return;
				}
				if (typeof html2pdf === 'undefined') {
					alert('PDF library is still loading. Please wait a moment and try again.');
					return;
				}

				let barcode = barcodeInput.value.trim();
				
				if (!barcode) {
					alert('Please enter a barcode value');
					barcodeInput.focus();
					return;
				}

				console.log('Generating barcode for: ' + barcode);
				
				const format = barcodeFormat.value;
				console.log('Format: ' + format);
				
				try {
					// Create a temporary container for the barcode
					const tempContainer = document.createElement('div');
					tempContainer.style.width = '100mm';
					tempContainer.style.height = '50mm';
					tempContainer.style.display = 'flex';
					tempContainer.style.alignItems = 'center';
					tempContainer.style.justifyContent = 'center';
					tempContainer.style.background = 'white';
					tempContainer.style.padding = '5mm';
					tempContainer.style.boxSizing = 'border-box';
					tempContainer.style.fontFamily = 'Arial, sans-serif';

					// Create SVG for barcode
					const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
					tempContainer.appendChild(svg);

					// Generate barcode
					JsBarcode(svg, barcode, {
						format: format,
						width: 2,
						height: 35,
						displayValue: true,
						fontSize: 14,
						margin: 5,
						lineColor: '#000000',
						background: '#ffffff'
					});

					console.log('Barcode generated, creating PDF...');

					// Create PDF
					const opt = {
						margin: 0,
						filename: 'barcode_' + barcode + '.pdf',
						image: { type: 'png', quality: 0.98 },
						html2canvas: { scale: 2, useCORS: true, allowTaint: true },
						jsPDF: { 
							unit: 'mm', 
							format: [100, 50], 
							orientation: 'landscape',
							hotfixes: ['px_scaling']
						}
					};

					html2pdf().set(opt).from(tempContainer).save();

					console.log('PDF saved');

					// Update input field if empty
					if (productBarcodeInput && !productBarcodeInput.value) {
						productBarcodeInput.value = barcode;
					}

					// Close modal using jQuery for Bootstrap 3/4
					try {
						$('#barcodePrintModal').modal('hide');
					} catch (e) {
						console.warn('Could not hide modal via jQuery, trying bootstrap instance method', e);
						if (typeof bootstrap !== 'undefined') {
							const modal = bootstrap.Modal.getInstance(barcodePrintModal);
							if (modal) modal.hide();
						}
					}

					alert('✓ Barcode PDF generated successfully!\nFile: barcode_' + barcode + '.pdf\n\nCheck your Downloads folder.');
				} catch (error) {
					console.error('Barcode generation error:', error);
					alert('Error generating barcode:\n' + error.message + '\n\nMake sure:\n- Barcode value is entered\n- Libraries are fully loaded\n- Try a different format');
				}
			});

			// Allow Enter key to generate
			barcodeInput.addEventListener('keypress', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					printBarcodeBtn.click();
				}
			});
		}

		// Initialize when document is ready
		document.addEventListener('DOMContentLoaded', initBarcodePrinting);
		
		// Also try to initialize after a short delay to ensure libraries are loaded
		setTimeout(initBarcodePrinting, 1000);
	</script>
@endpush

