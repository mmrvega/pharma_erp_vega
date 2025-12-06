@extends('admin.layouts.app')

@push('page-header')
<div class="col">
	<h3 class="page-title" data-i18n="profile">{{ trans_key('profile') }}</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}" data-i18n="dashboard">{{ trans_key('dashboard') }}</a></li>
		<li class="breadcrumb-item active" data-i18n="profile">{{ trans_key('profile') }}</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="profile-header">
			<div class="row align-items-center">
				<div class="col-auto profile-image">
					<a href="#">
						<img class="rounded-circle" alt="User Image" src="{{!empty(auth()->user()->avatar) ? asset('storage/users/'.auth()->user()->avatar): asset('assets/img/avatar.png')}}">
					</a>
				</div>
				<div class="col ml-md-n2 profile-user-info">
					<h4 class="user-name mb-0">{{auth()->user()->name}}</h4>
					<h6 class="text-muted">{{auth()->user()->email}}</h6>
				</div>

			</div>
		</div>
		<div class="profile-menu">
				<ul class="nav nav-tabs nav-tabs-solid">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#per_details_tab" data-i18n="about">{{ trans_key('about') }}</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#password_tab" data-i18n="password">{{ trans_key('password') }}</a>
				</li>
			</ul>
		</div>
		<div class="tab-content profile-tab-cont">

			<!-- Personal Details Tab -->
			<div class="tab-pane fade show active" id="per_details_tab">

				<!-- Personal Details -->
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-body">
								<h5 class="card-title d-flex justify-content-between">
									<span data-i18n="personal_details">{{ trans_key('personal_details') }}</span>
									<a class="edit-link" data-toggle="modal" href="#edit_personal_details"><i class="fa fa-edit mr-1"></i><span data-i18n="edit">{{ trans_key('edit') }}</span></a>
								</h5>
								<div class="row">
									<p class="col-sm-2 text-muted text-sm-right mb-0 mb-sm-3" data-i18n="name">{{ trans_key('name') }}</p>
									<p class="col-sm-10">{{auth()->user()->name}}</p>
								</div>

								<div class="row">
									<p class="col-sm-2 text-muted text-sm-right mb-0 mb-sm-3" data-i18n="email">{{ trans_key('email') }}</p>
									<p class="col-sm-10">{{auth()->user()->email}}</p>
								</div>

								<div class="row">
									<p class="col-sm-2 text-muted text-sm-right mv-0 mb-sm-3" data-i18n="user_role">{{ trans_key('user_role') ?? 'User Role' }}</p>
									<p class="col-sm-10">
										@foreach (auth()->user()->getRoleNames() as $role)
										{{$role}}
										@endforeach
									</p>
								</div>

							</div>
						</div>

						<!-- Edit Details Modal -->
						<div class="modal fade" id="edit_personal_details" aria-hidden="true" role="dialog">
							<div class="modal-dialog modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" data-i18n="personal_details">{{ trans_key('personal_details') }}</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<form method="POST" enctype="multipart/form-data" action="{{route('profile.update',auth()->user())}}">
											@csrf
											<div class="row form-row">
												<div class="col-12">
													<div class="form-group">
														<label data-i18n="full_name">{{ trans_key('full_name') ?? 'Full Name' }}</label>
														<input class="form-control" name="name" type="text" value="{{auth()->user()->name}}" placeholder="{{ trans_key('full_name') ?? 'Full Name' }}">
													</div>
												</div>
												<div class="col-12">
													<div class="form-group">
														<label data-i18n="email">{{ trans_key('email') }}</label>
														<input class="form-control" name="email" type="text" value="{{auth()->user()->email}}" placeholder="{{ trans_key('email') }}">
													</div>
												</div>
												@can('edit-role')
												<div class="col-12">
													<div class="form-group">
														<label>Role</label>
														<select class="form-control select edit_role" name="role">
															@foreach ($roles as $role)
																<option value="{{$role->name}}">{{$role->name}}</option>
															@endforeach
														</select>
													</div>
												</div>
												@endcan
												<div class="col-12">
													<div class="form-group">
														<label>User Avatar</label>
														<input type="file" value="{{auth()->user()->avatar}}" class="form-control" name="avatar">
													</div>
												</div>

											</div>
											<button type="submit" class="btn btn-primary btn-block" data-i18n="save_changes">{{ trans_key('save_changes') }}</button>
										</form>
									</div>
								</div>
							</div>
						</div>
						<!-- /Edit Details Modal -->

					</div>


				</div>
				<!-- /Personal Details -->

			</div>
			<!-- /Personal Details Tab -->

			<!-- Change Password Tab -->
			<div id="password_tab" class="tab-pane fade">

				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Change Password</h5>
						<div class="row">
							<div class="col-md-10 col-lg-12">
								<form method="POST" action="{{route('update-password',auth()->user())}}">
									@csrf
									@method("PUT")
									<div class="form-group">
										<label>Current Password</label>
										<input type="password" name="current_password" class="form-control" placeholder="enter your current password">
									</div>
									<div class="form-group">
										<label>New Password</label>
										<input type="password" name="password" class="form-control" placeholder="enter your new password">
									</div>
									<div class="form-group">
										<label>Confirm Password</label>
										<input type="password" name="password_confirmation" class="form-control" placeholder="repeat your new password">
									</div>
									<button class="btn btn-primary" type="submit">Save Changes</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Change Password Tab -->

		</div>
	</div>
</div>
@endsection