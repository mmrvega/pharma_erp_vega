@extends('admin.layouts.app')

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title" data-i18n="edit">{{ trans_key('edit') }} {{ trans_key('supplier') }}</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}" data-i18n="dashboard">{{ trans_key('dashboard') }}</a></li>
		<li class="breadcrumb-item active" data-i18n="edit_supplier">{{ trans_key('edit') }} {{ trans_key('supplier') }}</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
			
			<!-- Edit Supplier -->
			<form method="post" enctype="multipart/form-data" action="{{route('suppliers.update',$supplier)}}">
				@csrf
				@method("PUT")
				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label data-i18n="name">{{ trans_key('name') }}<span class="text-danger">*</span></label>
								<input class="form-control" type="text" value="{{$supplier->name ?? old('name')}}" name="name">
							</div>
						</div>
						<div class="col-lg-6">
							<label data-i18n="email">{{ trans_key('email') }}<span class="text-danger">*</span></label>
							<input class="form-control" type="text" value="{{$supplier->email ?? old('email')}}" name="email" >
						</div>
					</div>
				</div>

				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label data-i18n="phone">{{ trans_key('phone') }}<span class="text-danger">*</span></label>
								<input class="form-control" type="text" value="{{$supplier->phone ?? old('phone')}}" name="phone">
							</div>
						</div>
						<div class="col-lg-6">
							<label data-i18n="company">{{ trans_key('company') }}<span class="text-danger">*</span></label>
							<input class="form-control" type="text" value="{{$supplier->company ?? old('company')}}" name="company">
						</div>
					</div>
				</div>

				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label data-i18n="address">{{ trans_key('address') }} <span class="text-danger">*</span></label>
								<input type="text" name="address" value="{{$supplier->address ?? old('address')}}" class="form-control">
							</div>
						</div>
						<div class="col-lg-6">
							<label data-i18n="product">{{ trans_key('product') }}</label>
							<input type="text" name="product" value="{{$supplier->product ?? old('product')}}" class="form-control">
						</div>
					</div>
				</div>	
				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-12">
							<label data-i18n="comment">{{ trans_key('comment') ?? 'Comment' }}</label>
							<textarea name="comment" class="form-control" value="{{$supplier->comment ?? old('comment')}}" cols="30" rows="10">{{$supplier->comment}}</textarea>
						</div>
					</div>
				</div>		
				
				
				<div class="submit-section">
					<button class="btn btn-primary submit-btn" type="submit" name="form_submit" value="submit" data-i18n="submit">{{ trans_key('submit') }}</button>
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




