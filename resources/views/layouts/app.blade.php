<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head><base href="../../">
<title>شركة صباح النور || النظام المالي - @yield('title','لوحة التحكم')</title>
<meta name="description" content="شركة صباح النور || النظام المالي" />
<meta name="keywords" content="" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta charset="utf-8" />
<meta property="og:locale:alternate" content="ar_SA" />
<meta property="og:type" content="شركة صباح النور || النظام المالي" />
<meta property="og:title" content="شركة صباح النور || النظام المالي" />
<meta property="og:url" content="{{ url('/') }}" />
<meta property="og:site_name" content="" />
<link rel="canonical" href="" />
<link rel="shortcut icon" href="{{asset('assets/media/logos/logo.png')}}" />
<link href="{{asset('assets/plugins/global/plugins.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/style.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/fonts/dinnext/styles.rtl.css')}}" rel="stylesheet" type="text/css" />
{{-- Sabah Alnoor design layer — IBM Plex Sans Arabic + tokens. Must load AFTER the
     Metronic bundle + dinnext so it wins on cascade order. Poppins link removed:
     it was Latin-only and never touched Arabic content. --}}
<link href="{{asset('css/app-ui.css')}}?v={{ config('global.ver.version_css') }}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/flatpickr/dist/flatpickr.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/flatpickr/dist/ie.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/flatpickr/dist/plugins/confirmDate/confirmDate.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/flatpickr/dist/plugins/monthSelect/style.css')}}" rel="stylesheet" type="text/css" />
<style>
.table .btn.btn-icon.btn-sm, .table .btn-group-sm > .btn.btn-icon {
  height: calc(1em + 1.1rem + 2px) !important;
  width: calc(1em + .1rem + 1px) !important;
  padding: calc(0.2rem + 1px) calc(1rem + 1px) !important;
}
</style>
    @yield('styles')

 
</head>
<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed" data-kt-aside-minimize="on" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
<div class="d-flex flex-column flex-root">
    <div class="flex-row page d-flex flex-column-fluid">
        <div id="kt_aside" class="aside @if(Auth::user()->dark == 1) aside-dark @else aside-light @endif aside-hoverable" data-kt-drawer="true" data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_mobile_toggle">
                        @include('layouts/page_sidebar')
       </div>
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <div id="kt_header" style="" class="header align-items-stretch">
                <div class="container-fluid d-flex align-items-stretch justify-content-between">
                    <div class="d-flex align-items-center d-lg-none ms-n3 me-1" title="Show aside menu">
                        <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px" id="kt_aside_mobile_toggle">
                            <span class="mt-1 svg-icon svg-icon-2x">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
											<path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z" fill="black" />
											<path opacity="0.3" d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z" fill="black" />
										</svg>
									</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                        <a href="{{route('home')}}" class="d-lg-none">
                            <img alt="Logo" src="{{asset('assets/media/logos/logo.jpg')}}" class="h-30px" />
                        </a>
                    </div>
                    <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
                        <div class="d-flex align-items-stretch" id="kt_header_nav">
                            <div class="header-menu align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_header_menu_mobile_toggle" data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_body', lg: '#kt_header_nav'}">
                            </div>
                        </div>
                        <div class="flex-shrink-0 d-flex align-items-stretch">
                            <div class="flex-shrink-0 d-flex align-items-stretch">
                                <?php if(Perm::get_function_access(50) || Perm::get_function_access(51) || Perm::get_function_access(52) || Perm::get_function_access(53) || Perm::get_function_access(54)){?>

                                <div class="d-flex align-items-center ms-1 ms-lg-3" onclick=' load_alerts("{{ route("load_alerts") }}");'>
                                    <div class="btn btn-icon btn-active-light-primary position-relative w-30px h-30px w-md-40px h-md-40px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                                                      <span class="svg-icon svg-icon-dark svg-icon-2x"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path opacity="0.3" d="M12 22C13.6569 22 15 20.6569 15 19C15 17.3431 13.6569 16 12 16C10.3431 16 9 17.3431 9 19C9 20.6569 10.3431 22 12 22Z" fill="currentColor"/>
                                            <path d="M19 15V18C19 18.6 18.6 19 18 19H6C5.4 19 5 18.6 5 18V15C6.1 15 7 14.1 7 13V10C7 7.6 8.7 5.6 11 5.1V3C11 2.4 11.4 2 12 2C12.6 2 13 2.4 13 3V5.1C15.3 5.6 17 7.6 17 10V13C17 14.1 17.9 15 19 15ZM11 10C11 9.4 11.4 9 12 9C12.6 9 13 8.6 13 8C13 7.4 12.6 7 12 7C10.3 7 9 8.3 9 10C9 10.6 9.4 11 10 11C10.6 11 11 10.6 11 10Z" fill="currentColor"/>
                                            </svg>
                                            </span>
                                        <span class="top-0 position-absolute start-0 translate-middle badge badge-circle badge-danger animation-blink" id='count_moraslat' name='count_moraslat'></span>
                                    </div>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true" style="" id='load_alerts'>
                                    </div>
                                </div>
                                <?php } ?>
                                @php $userAuth = explode(' ',trim(Auth::user()->name)); @endphp
                        <div class="d-flex align-items-center ms-1 ms-lg-3 fw-bold text-info fs-7" style=""><span style="margin-top:-3px;font-size: 13.5px;">@auth(){{Auth::user()->name}} @else User Name @endauth</span>
                        </div>
                                <div class="d-flex align-items-center ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
                                    <div class="cursor-pointer symbol symbol-30px symbol-md-40px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                            <span class="svg-icon svg-icon-dark svg-icon-1"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path opacity="0.5" d="M12.5657 9.63427L16.75 5.44995C17.1642 5.03574 17.8358 5.03574 18.25 5.44995C18.6642 5.86416 18.6642 6.53574 18.25 6.94995L12.7071 12.4928C12.3166 12.8834 11.6834 12.8834 11.2929 12.4928L5.75 6.94995C5.33579 6.53574 5.33579 5.86416 5.75 5.44995C6.16421 5.03574 6.83579 5.03574 7.25 5.44995L11.4343 9.63427C11.7467 9.94669 12.2533 9.94668 12.5657 9.63427Z" fill="currentColor"/>
                                                <path d="M12.5657 15.6343L16.75 11.45C17.1642 11.0357 17.8358 11.0357 18.25 11.45C18.6642 11.8642 18.6642 12.5357 18.25 12.95L12.7071 18.4928C12.3166 18.8834 11.6834 18.8834 11.2929 18.4928L5.75 12.95C5.33579 12.5357 5.33579 11.8642 5.75 11.45C6.16421 11.0357 6.83579 11.0357 7.25 11.45L11.4343 15.6343C11.7467 15.9467 12.2533 15.9467 12.5657 15.6343Z" fill="currentColor"/>
                                                </svg>
                                                </span>

                                        </div>
                                    <div class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-bold fs-6 w-300px" data-kt-menu="true">
                                        <div class="px-3 menu-item">
                                            <div class="px-3 menu-content d-flex align-items-center">
                                                <div class="symbol symbol-40px me-5">
                                                    <img alt="Logo" src="{{asset('assets/media/avatars/blank.png')}}" />
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <div class="fw-bolder d-flex align-items-center fs-8">@auth(){{Auth::user()->name}} @else User Name @endauth
                                                        <span class="px-2 py-1 badge badge-light-success fw-bolder fs-8 ms-2">@auth(){{Auth::user()->role_id != null ? Auth::user()->role->name :''}} @else Employee @endauth</span>
                                                    </div>
                                                    <a  class="fw-bold text-success text-hover-primary fs-7">@auth(){{Auth::user()->email != null ? Auth::user()->email :'Email'}} @else Email @endauth</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="my-2 separator"></div>
                                        <div class="my-2 separator"></div>
                                        <div class="px-5 my-1 menu-item">
                                            <a href="{{route('edit_profile')}}" class="px-5 menu-link text-info">إعدادات الحساب</a>
                                        </div>
                                          <div class="my-2 separator"></div>
                                        <div class="px-5 menu-item">
                                               <div class="menu-item">
                                                <a class="px-5 menu-link text-red" href="{{ route('logout') }}"
                                                   onclick="event.preventDefault();
                                                         document.getElementById('logout-form').submit();">
                                                    تسجيل الخروج
                                                </a>
                                                <form id="logout-form" action="{{ route('logout') }}" method="post"
                                                      class="d-none">
                                                    @csrf
                                                </form>
                                            </div>
                                        </div>
                                        <div class="my-2 separator"></div>
                                        <div class="px-5 menu-item">
                                            <div class="px-5 menu-content">
                                                <label class="form-check form-switch form-check-custom form-check-solid pulse pulse-success" for="kt_user_menu_dark_mode_toggle">
                                                    <input class="form-check-input w-30px h-20px" type="checkbox" @if(Auth::user()->dark == 1) checked="checked" @endif onclick="darkMode()" value="1" name="mode" id="DarkMode" {{-- id="kt_user_menu_dark_mode_toggle" data-kt-url="{{route('home')}}" --}} />
                                                    <span class="pulse-ring ms-n1"></span>
                                                    <span class="text-gray-600 form-check-label fs-7">الوضع المظلم</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                <div class="toolbar" id="kt_toolbar">
                    <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
                        <div data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}" class="flex-wrap mb-5 page-title d-flex align-items-center me-3 mb-lg-0">
                               <h1 class="my-1 d-flex align-items-center fs-6 fw-bolder" style="color: #ffb822;">@yield('module','المحوسب')</h1>
                            <span class="mx-2 h-20px border-start border-white border-opacity-25 ms-3"></span>
                            <h1 class="my-1 d-flex align-items-center text-white fs-7 fw-bold">@yield('sub',"شركة صباح النور || النظام المالي")</h1>
                            <span class="mx-2 h-20px border-start border-white border-opacity-25 ms-3"></span>
                            <ul class="my-1 breadcrumb breadcrumb-separatorless fs-7">
                                <li class="breadcrumb-item">
                                     <span class="text-white text-opacity-75">@yield('title',' ')</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="post d-flex flex-column-fluid" id="kt_post">
                     <div id="kt_content_container" class="container-fluid">
                    @yield('content')
                    </div>
                </div>
            </div>
            <div class="py-4 footer d-flex flex-lg-column" id="kt_footer">
                <div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <div class="order-2 text-dark order-md-1">
                        <a href="#" target="_blank" class="text-hover-primary fw-bolder text-dark">تم التطوير لدى شركة صباح النور</a>
                        <span class="me-1 fw-bolder text-dark">© {{date("Y")}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
    <span class="svg-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
					<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="black" />
					<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="black" />
				</svg>
			</span>
</div>
<script>var hostUrl = "assets/";</script>
<script src="{{asset('assets/plugins/global/plugins.bundle.js')}}"></script>
<script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
<script src="{{asset('assets/js/custom/documentation/documentation.js')}}"></script>
<script src="{{asset('assets/js/custom/documentation/search.js')}}"></script>
<script src="{{asset('assets/plugins/custom/prismjs/prismjs.bundle.js')}}"></script>
<script src="{{asset('assets/js/custom/widgets.js')}}"></script>
<script src="{{asset('assets/js/custom/apps/chat/chat.js')}}"></script>
<script src="{{asset('assets/js/custom/modals/create-app.js')}}"></script>
<script src="{{asset('assets/js/custom/modals/upgrade-plan.js')}}"></script>
<script src="{{asset('assets/flatpickr/dist/flatpickr.min.js')}}"></script>
<script src="{{asset('assets/flatpickr/dist/plugins/rangePlugin.js')}}"></script>
<script src="{{asset('assets/flatpickr/dist/plugins/confirmDate/confirmDate.js')}}"></script>
<script src="{{asset('assets/flatpickr/dist/plugins/minMaxTimePlugin.js')}}"></script>
<script src="{{asset('assets/flatpickr/dist/plugins/monthSelect/index.js')}}"></script>
<script src="{{asset('assets/flatpickr/dist/plugins/scrollPlugin.js')}}"></script>
<script src="{{asset('assets/flatpickr/dist/plugins/weekSelect/weekSelect.js')}}"></script>
<script src="{{asset('assets/flatpickr/dist/l10n/ar.js')}}"></script>
<script src="{{asset('assets/jquery-validation/dist/jquery.validate.js')}}"></script>
<script src="{{asset('assets/jquery-validation/dist/localization/messages_ar.js')}}"></script>
<script src="{{asset('assets/jquery-validation/dist/additional-methods.js')}}"></script>
<script src="{{asset('assets/jquery-validation/dist/jquery-validation.init.js')}}"></script>
<script src="{{ asset('assets/module/main_j.js') }}?t={{ config('global.ver.version_all') }}"></script>
<script>
notify_num("{{ route('notify_num') }}");

</script>




<?php if(Perm::get_function_access(50) || Perm::get_function_access(51) || Perm::get_function_access(52) || Perm::get_function_access(53) || Perm::get_function_access(54)){?>

    <script>

function loadlink() {
                 var timer, delay = 60000*30;

                     timer = setInterval(function() {
                        notify_num("{{ route('notify_num') }}");

                     }, delay);
                 }
                 loadlink();








</script>
<?php }?>

@yield('scripts')
</body>
</html>
