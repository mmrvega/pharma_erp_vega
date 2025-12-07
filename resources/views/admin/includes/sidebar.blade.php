<!-- Sidebar -->
<div class="sidebar" id="sidebar">
	<div class="sidebar-inner slimscroll">
		<div id="sidebar-menu" class="sidebar-menu">
			
			<ul>
				<!--<li class="menu-title"> 
					<span data-i18n="main">{{ trans_key('main') }}</span>
				</li>-->
				<li class="{{ route_is('dashboard') ? 'active' : '' }}"> 
					<a href="{{route('dashboard')}}"><i class="fe fe-home"></i> <span data-i18n="dashboard">{{ trans_key('dashboard') }}</span></a>
				</li>
				
				@can('view-purchase')
			<li class="submenu">
				<a href="#"><i class="fe fe-star-o"></i> <span data-i18n="purchase">{{ trans_key('purchase') }}</span> <i class="fa fa-chevron-right"></i></a>
					<ul style="display: none;">
						<li><a class="{{ route_is('purchases.*') ? 'active' : '' }}" href="{{route('purchases.index')}}" data-i18n="purchase">{{ trans_key('purchase') }}</a></li>
						@can('create-purchase')
						<li><a class="{{ route_is('purchases.create') ? 'active' : '' }}" href="{{route('purchases.create')}}" data-i18n="add_purchase">{{ trans_key('add_purchase') }}</a></li>
						@endcan
					</ul>
				</li>
				@endcan
				
				@can('view-category')
				<li class="{{ route_is('categories.*') ? 'active' : '' }}"> 
					<a href="{{route('categories.index')}}"><i class="fe fe-layout"></i> <span data-i18n="categories">{{ trans_key('categories') }}</span></a>
				</li>
				@endcan
				
			@can('view-products')
			<li class="submenu">
				<a href="#"><i class="fe fe-document"></i> <span data-i18n="products">{{ trans_key('products') }}</span> <i class="fa fa-chevron-right"></i></a>
					<ul style="display: none;">
						<li><a class="{{ route_is(('products.*')) ? 'active' : '' }}" href="{{route('products.index')}}" data-i18n="products">{{ trans_key('products') }}</a></li>
						@can('create-product')<li><a class="{{ route_is('products.create') ? 'active' : '' }}" href="{{route('products.create')}}" data-i18n="add_product">{{ trans_key('add_product') }}</a></li>@endcan
						@can('view-outstock-products')<li><a class="{{ route_is('outstock') ? 'active' : '' }}" href="{{route('outstock')}}" data-i18n="out_stock">{{ trans_key('out_stock') }}</a></li>@endcan
						@can('view-expired-products')<li><a class="{{ route_is('expired') ? 'active' : '' }}" href="{{route('expired')}}" data-i18n="expired">{{ trans_key('expired') }}</a></li>@endcan
					</ul>
				</li>
				@endcan
				
			@can('view-sales')
			<li class="submenu">
				<a href="#"><i class="fe fe-activity"></i> <span data-i18n="sale">{{ trans_key('sale') }}</span> <i class="fa fa-chevron-right"></i></a>
					<ul style="display: none;">
						<li><a class="{{ route_is('sales.*') ? 'active' : '' }}" href="{{route('sales.index')}}" data-i18n="sales">{{ trans_key('sales') }}</a></li>
						@can('create-sale')
						<li><a class="{{ route_is('sales.create') ? 'active' : '' }}" href="{{route('sales.create')}}" data-i18n="add_sale">{{ trans_key('add_sale') }}</a></li>
						@endcan
					</ul>
				</li>
				@endcan
				
			@can('view-supplier')
			<li class="submenu">
				<a href="#"><i class="fe fe-user"></i> <span data-i18n="supplier">{{ trans_key('supplier') }}</span> <i class="fa fa-chevron-right"></i></a>
					<ul style="display: none;">
						<li><a class="{{ route_is('suppliers.*') ? 'active' : '' }}" href="{{route('suppliers.index')}}" data-i18n="supplier">{{ trans_key('supplier') }}</a></li>
						@can('create-supplier')<li><a class="{{ route_is('suppliers.create') ? 'active' : '' }}" href="{{route('suppliers.create')}}" data-i18n="add_supplier">{{ trans_key('add_supplier') }}</a></li>@endcan
					</ul>
				</li>
				@endcan

			@can('view-reports')
			<li class="submenu">
				<a href="#"><i class="fe fe-document"></i> <span data-i18n="reports">{{ trans_key('reports') }}</span> <i class="fa fa-chevron-right"></i></a>
					<ul style="display: none;">
						<li><a class="{{ route_is('sales.report') ? 'active' : '' }}" href="{{route('sales.report')}}" data-i18n="sale_report">{{ trans_key('sale_report') }}</a></li>
						<li><a class="{{ route_is('purchases.report') ? 'active' : '' }}" href="{{route('purchases.report')}}" data-i18n="purchase_report">{{ trans_key('purchase_report') }}</a></li>
					</ul>
				</li>
				@endcan

			@can('view-access-control')
			<li class="submenu">
				<a href="#"><i class="fe fe-lock"></i> <span data-i18n="access_control">{{ trans_key('access_control') }}</span> <i class="fa fa-chevron-right"></i></a>
					<ul style="display: none;">
						@can('view-permission')
						<li><a class="{{ route_is('permissions.index') ? 'active' : '' }}" href="{{route('permissions.index')}}" data-i18n="permissions">{{ trans_key('permissions') }}</a></li>
						@endcan
						@can('view-role')
						<li><a class="{{ route_is('roles.*') ? 'active' : '' }}" href="{{route('roles.index')}}" data-i18n="roles">{{ trans_key('roles') }}</a></li>
						@endcan
					</ul>
				</li>                    
				@endcan

				@can('view-users')
				<li class="{{ route_is('users.*') ? 'active' : '' }}"> 
					<a href="{{route('users.index')}}"><i class="fe fe-users"></i> <span data-i18n="users">{{ trans_key('users') }}</span></a>
				</li>
				@endcan
				
				<li class="{{ route_is('profile') ? 'active' : '' }}"> 
					<a href="{{route('profile')}}"><i class="fe fe-user-plus"></i> <span data-i18n="profile">{{ trans_key('profile') }}</span></a>
				</li>
				<li class="{{ route_is('backup.index') ? 'active' : '' }}"> 
					<a href="{{route('backup.index')}}"><i class="material-icons">backup</i> <span data-i18n="backups">{{ trans_key('backups') }}</span></a>
				</li>
				@can('view-settings')
				<li class="{{ route_is('settings') ? 'active' : '' }}"> 
					<a href="{{route('settings')}}">
						<i class="material-icons">settings</i>
						 <span data-i18n="settings">{{ trans_key('settings') }}</span>
					</a>
				</li>
				@endcan
			</ul>
		</div>
	</div>
</div>
<!-- /Sidebar -->