<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- csrf token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ucfirst(AppSettings::get('app_name', 'App'))}} - {{ucfirst($title ?? '')}}</title>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{!empty(AppSettings::get('favicon')) ? asset('storage/'.AppSettings::get('favicon')) : asset('assets/img/favicon.png')}}">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/fontawesome.min.css')}}">
    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/feathericon.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/icons.min.css')}}">
    <!-- Snackbar CSS -->
    <link rel="stylesheet" href="{{asset('assets/plugins/snackbar/snackbar.min.css')}}">
    <!-- Sweet Alert css -->
    <link rel="stylesheet" href="{{asset('assets/plugins/sweetalert2/sweetalert2.min.css')}}">
    <!-- Select2 Css -->
    <link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2.min.css')}}">
    <!-- Main CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <!-- Luxury POS Theme CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/luxury-pos.css')}}">
    <!-- Luxury POS Cart CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/luxury-pos-cart.css')}}">
    <!-- Page CSS -->
    @stack('page-css')
    <!-- Dark Mode CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/dark-mode.css')}}">
    <!-- Theme & Language Toggle CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/theme-language-toggle.css')}}">
    <style>
        /* Mobile adjustments: ensure sidebar behaves as overlay and logo is centered */
        @media (max-width: 767.98px) {
            .header .header-left { position: relative; }
            /* Hide the logo on mobile as requested */
            .header .header-left .logo { display: none; } 
            
            /* Mobile Sidebar: Hidden off-screen by default */
            .sidebar { 
                position: fixed; 
                left: -260px; 
                top: 0; 
                height: 100%; 
                z-index: 1100; 
                background: #fff; 
                overflow: auto;
                transition: left 0.3s ease-in-out;
                width: 260px;
            }
            
            /* Show sidebar when slide-nav class is present (added by script.js) */
            .slide-nav .sidebar {
                left: 0;
            }

            /* Ensure content sits below header when sidebar overlays */
            .page-wrapper { margin-left: 0 !important; }
        }
    </style>
    <!--[if lt IE 9]>
        <script src="assets/js/html5shiv.min.js"></script>
        <script src="assets/js/respond.min.js"></script>
    <![endif]-->
</head>
<body>

    <!-- Main Wrapper -->
    <div class="main-wrapper">

        <!-- Header -->
        @include('admin.includes.header')
        <!-- /Header -->

        <!-- Sidebar -->
        @include('admin.includes.sidebar')
        <!-- /Sidebar -->

        <!-- Page Wrapper -->
        <div class="page-wrapper">

            <div class="content container-fluid">

                <!-- Page header pushed by views (title, breadcrumbs, action buttons) -->
                @stack('page-header')

                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <x-alerts.danger :error="$error" />
                    @endforeach
                @endif

                @yield('content')
                <!-- add sales modal-->
                <x-modals.add-sale />
                <!-- / add sales modal -->
            </div>
        </div>
        <!-- /Page Wrapper -->

    </div>
    <!-- /Main Wrapper -->
    
</body>
<!-- jQuery -->
<script src="{{asset('assets/plugins/jquery/jquery.min.js')}}"></script>

<!-- Bootstrap Core JS -->
<script src="{{asset('assets/js/popper.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap.min.js')}}"></script>
<script src="{{asset('assets/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>
<!-- Sweet Alert Js -->
<script src="{{asset('assets/plugins/sweetalert2/sweetalert2.min.js')}}"></script>
<!-- Snackbar Js -->
<script src="{{asset('assets/plugins/snackbar/snackbar.min.js')}}"></script>
<!-- Select2 JS -->
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<!-- Custom JS -->
<script src="{{asset('assets/js/script.js')}}"></script>
<!-- Theme & Language Manager JS -->
<script src="{{asset('assets/js/theme-language.js')}}"></script>

<script>
    // CRITICAL FIX: Global AJAX Setup to include CSRF Token for Laravel
    // This is required for all POST/DELETE/PUT AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function(){
        $('body').on('click','#deletebtn',function(){
            var id = $(this).data('id');
            var route = $(this).data('route');
            // Using Swal (standard global name) consistently
            Swal.queue([ 
                {
                    title: "{{ trans_key('are_you_sure') }}",
                    text: "{{ trans_key('cannot_revert') }}",
                    icon: "warning", // Changed type to icon for SweetAlert2 V8+ compatibility
                    showCancelButton: !0,
                    confirmButtonText: '<i class="fe fe-trash mr-1"></i> ' + "{{ trans_key('delete') }}",
                    cancelButtonText: '<i class="fa fa-times mr-1"></i> ' + "{{ trans_key('cancel') }}",
                    confirmButtonClass: "btn btn-success mt-2",
                    cancelButtonClass: "btn btn-danger ml-2 mt-2",
                    buttonsStyling: !1,
                    preConfirm: function(){
                        // CRITICAL FIX: The Promise needs the 'resolve' function to proceed to the next queue step
                        return new Promise(function(resolve){ 
                            $.ajax({
                                url: route,
                                type: "DELETE",
                                data: {"id": id},
                                success: function(){
                                    Swal.insertQueueStep( // Using Swal
                                        Swal.fire({
                                            title: "{{ trans_key('deleted') }}",
                                            text: "{{ trans_key('resource_deleted') }}",
                                            icon: "success", // Changed type to icon
                                            showConfirmButton: !1,
                                            timer: 1500,
                                        })
                                    )
                                    $('.datatable').DataTable().ajax.reload();
                                    resolve(); // CRITICAL FIX: Resolve the promise on success
                                }
                                // Note: Error handling should ideally be added here (e.g., error: function(xhr){ ... })
                            })
                        })
                    }
                }
            ]).catch(Swal.noop); // Using Swal.noop
        }); 
    });
    @if(Session::has('message'))
        var type = "{{ Session::get('alert-type', 'info') }}";
        switch(type){
            case 'info':
                Snackbar.show({
                    text: "{{ Session::get('message') }}",
                    actionTextColor: '#fff',
                    backgroundColor: '#2196f3'
                });
                break;

            case 'warning':
                Snackbar.show({
                    text: "{{ Session::get('message') }}",
                    pos: 'top-right',
                    actionTextColor: '#fff',
                    backgroundColor: '#e2a03f'
                });
                break;

            case 'success':
                Snackbar.show({
                    text: "{{ Session::get('message') }}",
                    pos: 'top-right',
                    actionTextColor: '#fff',
                    backgroundColor: '#8dbf42'
                });
                break;

            case 'danger':
                Snackbar.show({
                    text: "{{ Session::get('message') }}",
                    pos: 'top-right',
                    actionTextColor: '#fff',
                    backgroundColor: '#e7515a'
                });
                break;
        }
    @endif
</script>
<!-- Page JS -->
@stack('page-js')
<script>
    // Manage sidebar visibility preferences using standard theme classes.
    // Preference persisted in localStorage under key 'mini-sidebar' ('1' = mini, '0' = full).
    (function(){
        try{
            // Apply saved preference on load (only for Desktop)
            var pref = localStorage.getItem('mini-sidebar');
            var isMobile = window.matchMedia && window.matchMedia('(max-width: 767.98px)').matches;

            if(!isMobile && pref === '1'){
                document.body.classList.add('mini-sidebar');
            }

            // Sync preference when toggle_btn is clicked
            // We use jQuery here because script.js is loaded and handles the UI toggle.
            // We just need to save the state after script.js does its job.
            $(document).on('click', '#toggle_btn', function(){
                setTimeout(function(){
                    var isMini = $('body').hasClass('mini-sidebar');
                    localStorage.setItem('mini-sidebar', isMini ? '1' : '0');
                }, 100);
            });

        }catch(e){ console.error(e); }
    })();
</script>
</html>