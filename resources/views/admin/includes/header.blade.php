<!-- Header -->
<div class="header">

	<!-- Logo -->
	<div class="header-left" style="display: flex; align-items: center;">
		
		@php
			$logo = AppSettings::get('logo');
			$logoSmall = AppSettings::get('logo-small');
		@endphp
		<a href="{{route('dashboard')}}" class="logo">
			<img src="{{ $logo ? asset('storage/' . $logo) : asset('assets/img/logo.png') }}" alt="Logo">
		</a>
		<a href="{{route('dashboard')}}" class="logo logo-small">
			<img src="{{ $logoSmall ? asset('storage/' . $logoSmall) : asset('assets/img/logo-small.png') }}" alt="Logo" width="30" height="30">
		</a>
		<a href="javascript:void(0);" id="toggle_btn" >
		<i class="fe fe-text-align-left"></i>
		</a>
	</div>
	<!-- /Logo -->
	
	
	
	<!-- Mobile Menu Toggle -->
	<a class="mobile_btn" id="mobile_btn">
		<i class="fa fa-bars"></i>
	</a>
	<!-- /Mobile Menu Toggle -->
	
	<!-- Header Right Menu -->
	<ul class="nav user-menu">

		<!-- User Menu -->
		<li class="nav-item dropdown has-arrow">
			<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
				<span class="user-img"><img class="rounded-circle" src="{{!empty(auth()->user()->avatar) ? asset('storage/users/'.auth()->user()->avatar): asset('assets/img/avatar.png')}}" width="31" alt="avatar"></span>
			</a>
			<div class="dropdown-menu">
				<div class="user-header">
					<div class="avatar avatar-sm">
						<img src="{{!empty(auth()->user()->avatar) ? asset('storage/users/'.auth()->user()->avatar): asset('assets/img/avatar.png')}}" alt="User Image" class="avatar-img rounded-circle">
					</div>
					<div class="user-text">
						<h6>{{auth()->user()->name}}</h6>
					</div>
				</div>
				
				<a class="dropdown-item" href="{{route('profile')}}">My Profile</a>
				@can('view-settings')<a class="dropdown-item" href="{{route('settings')}}">Settings</a>@endcan
				
				<a href="javascript:void(0)" class="dropdown-item">
					<form action="{{route('logout')}}" method="post">
					@csrf
					<button type="submit" class="btn">Logout</button>
				</form>
				</a>
			</div>
		</li>
		<!-- /User Menu -->
		
	</ul>
	<!-- /Header Right Menu -->
	
</div>
<!-- /Header -->

@push('page-js')
<script>
	(function(){
		// Poll unread notifications every 5 seconds and update badge + dropdown
		function renderNotifications(resp){
			if(!resp || !resp.data) return;
			var count = resp.count || 0;
			// update badge
			$('.noti-dropdown .badge-pill, .fe.fe-bell').each(function(){ /* noop to ensure jQuery loaded */ });
			$('.noti-dropdown .badge-pill').text(count);
			// build list
			var list = '';
			resp.data.forEach(function(n){
				var d = n.data || {};
				var isRead = n.read === true;
				var productName = d.product_name || d.product || 'Product';
				var title = '';
				if(d.quantity !== undefined){
					title = '<h6 class="text-danger">Stock Alert</h6><p class="noti-details"><span class="noti-title">'+productName+' is only '+d.quantity+' left.</span><span>Please update the purchase quantity</span></p>';
				} else if(d.days_left !== undefined){
					if(d.days_left < 0){
						title = '<h6 class="text-warning">Expiry Alert</h6><p class="noti-details"><span class="noti-title">'+productName+' has already expired ('+(d.expiry_date||'')+').</span><span>Please take appropriate action.</span></p>';
					} else {
						title = '<h6 class="text-warning">Expiry Alert</h6><p class="noti-details"><span class="noti-title">'+productName+' will expire in '+d.days_left+' day(s) ('+(d.expiry_date||'')+').</span><span>Please take appropriate action.</span></p>';
					}
				} else {
					title = '<h6 class="text-info">Notification</h6><p class="noti-details"><span class="noti-title">'+productName+'</span></p>';
				}

				var img = '';
				if(d.image){
					img = '<span class="avatar avatar-sm"><img class="avatar-img rounded-circle" src="'+(''+"/storage/purchases/"+d.image)+'" alt="Product image"></span>';
				} else {
					img = '<span class="avatar avatar-sm"><img class="avatar-img rounded-circle" src="/assets/img/default-product.png" alt="Product image"></span>';
				}

				// Preserve read notifications in the list and visually mark them
				var liClass = 'notification-message' + (isRead ? ' read' : '');
				var liStyle = isRead ? 'opacity:0.7;' : '';
				list += '<li class="'+liClass+'" style="'+liStyle+'"><a href="/notification-read"><div class="media">'+img+'<div class="media-body">'+title+'<p class="noti-time"><span class="notification-time">'+(n.created_at||'')+'</span></p></div></div></a></li>';
			});

			if(list === ''){
				list = '<li class="notification-message"><div class="media"><div class="media-body"><p class="noti-details"><span class="noti-title">No new notifications</span></p></div></div></li>';
			}

			$('.notification-list').html(list);
		}

		function poll(){
			$.ajax({
				url: "{{ route('notifications.unread') }}",
				method: 'GET',
				success: function(resp){
					renderNotifications(resp);
				}
			});
		}

		// start immediately then every 5s
		$(function(){
			poll();
			setInterval(poll, 5000);
		});
	})();
</script>
@endpush