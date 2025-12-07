@extends('admin.layouts.app')
@php
    $title ='settings';
@endphp

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">General Settings</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item"><a href="javascript:(0)">Settings</a></li>
		<li class="breadcrumb-item active">General Settings</li>
	</ul>
</div>
@endpush

@section('content')

<div class="row">				
	<div class="col-12">
		@include('app_settings::_settings')	
	</div>
</div>

<!-- Custom POS Settings -->
<div class="row mt-4">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<h4 class="card-title">POS Settings</h4>
			</div>
			<div class="card-body">
				<form method="post" action="{{ route('settings.save') ?? '#' }}" class="form-horizontal">
					@csrf
					
					<div class="form-group row">
						<label class="col-md-3 col-form-label">Sale Action</label>
						<div class="col-md-9">
							<div class="custom-control custom-checkbox">
								<input type="hidden" name="print_invoice_on_sale" value="0">
								<input type="checkbox" class="custom-control-input" id="print_invoice_on_sale" name="print_invoice_on_sale" value="1" {{ settings('print_invoice_on_sale', 0) ? 'checked' : '' }}>
								<label class="custom-control-label" for="print_invoice_on_sale">
									Print Invoice on Sale (instead of Save Sale button)
								</label>
								<small class="form-text text-muted d-block mt-2">
									When enabled: Clicking the button will save the sale and automatically open the invoice for printing.
									<br>When disabled: Button will save the sale and continue selling.
								</small>
							</div>
						</div>
					</div>

					<div class="form-group row">
						<div class="col-md-9 offset-md-3">
							<button type="submit" class="btn btn-primary">Save Settings</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

