<!DOCTYPE html>
<html  lang="ar" direction="rtl" style="direction: rtl;">
<head><base href="../../">
<title>وزارة العمل - @yield('title','لوحة التحكم')</title>
<meta name="description" content="وزارة العمل" />
<meta name="keywords" content="" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta charset="utf-8" />
<meta property="og:locale:alternate" content="ar_SA" />
<meta property="og:type" content="وزارة العمل - دائرة الحاسوب - فلسطين غزة بطالة تحديث بيانات العمال العمل" />
<meta property="og:title" content="وزارة العمل - دائرة الحاسوب - فلسطين غزة بطالة تحديث بيانات العمال العمل" />
<meta property="og:url" content="{{ url('/') }}" />
<meta property="og:site_name" content="" />
<link rel="canonical" href="" />
<link rel="shortcut icon" href="{{asset('assets/media/logos/icon.png')}}" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
<link href="{{asset('assets/plugins/global/plugins.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/style.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/fonts/dinnext/styles.rtl.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/flatpickr/dark.css')}}" rel="stylesheet" type="text/css" />


<style>
.table .btn.btn-icon.btn-sm, .table .btn-group-sm > .btn.btn-icon {
  height: calc(1em + 1.1rem + 2px) !important;
  width: calc(1em + .1rem + 1px) !important;
  padding: calc(0.2rem + 1px) calc(1rem + 1px) !important;
}

</style>

    @yield('styles')
    <!--end::Global Stylesheets Bundle-->
</head>
<!--end::Head-->
<!--begin::Body-->
<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed" data-kt-aside-minimize="on" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
<!--begin::Main-->
<!--begin::Root-->
<div class="d-flex flex-column flex-root">
    <!--begin::Page-->
    <div class="page d-flex flex-row flex-column-fluid">
        <!--begin::Aside-->
        <div id="kt_aside" class="aside @if(Auth::user()->dark == 1) aside-dark @else aside-light @endif aside-hoverable" data-kt-drawer="true" data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_mobile_toggle">















                        @include('layouts/page_sidebar')














        </div>
        <!--end::Aside-->
        <!--begin::Wrapper-->
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <!--begin::Header-->
            <div id="kt_header" style="" class="header align-items-stretch">
                <!--begin::Container-->
                <div class="container-fluid d-flex align-items-stretch justify-content-between">
                    <!--begin::Aside mobile toggle-->
                    <div class="d-flex align-items-center d-lg-none ms-n3 me-1" title="Show aside menu">
                        <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px" id="kt_aside_mobile_toggle">
                            <!--begin::Svg Icon | path: icons/duotune/abstract/abs015.svg-->
                            <span class="svg-icon svg-icon-2x mt-1">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
											<path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z" fill="black" />
											<path opacity="0.3" d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z" fill="black" />
										</svg>
									</span>
                            <!--end::Svg Icon-->
                        </div>
                    </div>
                    <!--end::Aside mobile toggle-->
                    <!--begin::Mobile logo-->
                    <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                        <a href="{{route('home')}}" class="d-lg-none">
                            <img alt="Logo" src="{{asset('assets/media/logos/logo.png')}}" class="h-30px" />
                        </a>
                    </div>
                    <!--end::Mobile logo-->
                    <!--begin::Wrapper-->
                    <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
                        <!--begin::Navbar-->
                        <div class="d-flex align-items-stretch" id="kt_header_nav">
                            <!--begin::Menu wrapper-->
                            <div class="header-menu align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_header_menu_mobile_toggle" data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_body', lg: '#kt_header_nav'}">
                                <!--begin::Menu-->
                                <div class="menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch" id="#kt_header_menu" data-kt-menu="true">
                                    <div class="menu-item me-lg-1">
                                        <a class="menu-link py-3" href="{{route('home')}}">
                                            <span class="menu-title">الرئيسية</span>
                                        </a>
                                    </div>
                                </div>
                                <!--end::Menu-->
                            </div>
                            <!--end::Menu wrapper-->
                        </div>
                        <!--end::Navbar-->
                        <!--begin::Topbar-->
                        <div class="d-flex align-items-stretch flex-shrink-0">
                            <!--begin::Toolbar wrapper-->
                            <div class="d-flex align-items-stretch flex-shrink-0">
                                @php $userAuth = explode(' ',trim(Auth::user()->name)); @endphp
                        <div class="d-flex align-items-center ms-1 ms-lg-3 fw-bold  fs-7" style=""><span style="margin-top:-3px;font-size: 13.5px;">@auth(){{Auth::user()->name}} @else User Name @endauth</span>
                        <!--<div class="d-flex align-items-center ms-1 ms-lg-3 fw-bold  fs-7" style=""><span style="margin-top:-3px">@auth(){{Auth::user()->name}} @isset(Auth::user()->name) {{Auth::user()->name}} @endisset @else User Name @endauth</span> -->
                        <span class="badge badge-light-success fw-bolder fs-8 px-2 py-2 ms-2">@auth(){{Auth::user()->role_id != null ? Auth::user()->role->name :'Employee'}} @else Employee @endauth</span>
                        </div>

                                <!--begin::User-->
                                <div class="d-flex align-items-center ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
                                    <!--begin::Menu wrapper-->
                                    <div class="cursor-pointer symbol symbol-30px symbol-md-40px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                        <img src="{{asset('assets/media/avatars/blank.png')}}" alt="user" />
                                    </div>
                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-bold py-4 fs-6 w-300px" data-kt-menu="true">
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <div class="menu-content d-flex align-items-center px-3">
                                                <!--begin::Avatar-->
                                                <div class="symbol symbol-40px me-5">
                                                    <img alt="Logo" src="{{asset('assets/media/avatars/blank.png')}}" />
                                                </div>


                                                <!--end::Avatar-->
                                                <!--begin::Username-->
                                                <div class="d-flex flex-column">
                                                    <div class="fw-bolder d-flex align-items-center fs-8">@auth(){{Auth::user()->name}} @isset(Auth::user()->name) {{Auth::user()->name}} @endisset @else User Name @endauth
                                                        <span class="badge badge-light-success fw-bolder fs-8 px-2 py-1 ms-2">@auth(){{Auth::user()->role_id != null ? Auth::user()->role->name :'Employee'}} @else Employee @endauth</span></div>
                                                    <a href="#" class="fw-bold text-muted text-hover-primary fs-7">@auth(){{Auth::user()->email != null ? Auth::user()->email :'Email'}} @else Email @endauth</a>
                                                </div>
                                                <!--end::Username-->
                                            </div>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu separator-->
                                        <div class="separator my-2"></div>
                                        <!--end::Menu separator-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-5">
                                            <a href="{{route('profile')}}" class="menu-link px-5">ملفي الشخصي</a>
                                        </div>

                                        <div class="separator my-2"></div>

                                        <div class="menu-item px-5 my-1">
                                            <a href="{{route('edit_profile')}}" class="menu-link px-5">إعدادات الحساب</a>
                                        </div>
                                          <div class="separator my-2"></div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-5">
                                            <a class="menu-link px-5" href="{{ route('logout') }}"
                                               onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                                تسجيل الخروج
                                            </a>

                                            <form id="logout-form" action="{{ route('logout') }}" method="get" class="d-none">
                                                @csrf
                                            </form>





                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu separator-->
                                        <div class="separator my-2"></div>
                                        <!--end::Menu separator-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-5">
                                            <div class="menu-content px-5">
                                                <label class="form-check form-switch form-check-custom form-check-solid pulse pulse-success" for="kt_user_menu_dark_mode_toggle">
                                                    <input class="form-check-input w-30px h-20px" type="checkbox" @if(Auth::user()->dark == 1) checked="checked" @endif onclick="darkMode()" value="1" name="mode" id="DarkMode" {{-- id="kt_user_menu_dark_mode_toggle" data-kt-url="{{route('home')}}" --}} />
                                                    <span class="pulse-ring ms-n1"></span>
                                                    <span class="form-check-label text-gray-600 fs-7">Dark Mode</span>
                                                </label>
                                            </div>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                                    <!--end::Menu wrapper-->
                                </div>
                                <!--end::User -->
                                <!--begin::Heaeder menu toggle-->
                                <div class="d-flex align-items-center d-lg-none ms-2 me-n3" title="Show header menu">
                                    <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px" id="kt_header_menu_mobile_toggle">
                                        <!--begin::Svg Icon | path: icons/duotune/text/txt001.svg-->
                                        <span class="svg-icon svg-icon-1">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
														<path d="M13 11H3C2.4 11 2 10.6 2 10V9C2 8.4 2.4 8 3 8H13C13.6 8 14 8.4 14 9V10C14 10.6 13.6 11 13 11ZM22 5V4C22 3.4 21.6 3 21 3H3C2.4 3 2 3.4 2 4V5C2 5.6 2.4 6 3 6H21C21.6 6 22 5.6 22 5Z" fill="black" />
														<path opacity="0.3" d="M21 16H3C2.4 16 2 15.6 2 15V14C2 13.4 2.4 13 3 13H21C21.6 13 22 13.4 22 14V15C22 15.6 21.6 16 21 16ZM14 20V19C14 18.4 13.6 18 13 18H3C2.4 18 2 18.4 2 19V20C2 20.6 2.4 21 3 21H13C13.6 21 14 20.6 14 20Z" fill="black" />
													</svg>
												</span>
                                        <!--end::Svg Icon-->
                                    </div>
                                </div>
                                <!--end::Heaeder menu toggle-->
                            </div>
                            <!--end::Toolbar wrapper-->
                        </div>
                        <!--end::Topbar-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Container-->
            </div>
            <!--end::Header-->
            <!--begin::Content-->
            <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                <!--begin::Toolbar-->
                <div class="toolbar" id="kt_toolbar">
                    <!--begin::Container-->
                    <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
                        <!--begin::Page title-->
                        <div data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}" class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0" style='background-color: #083da6;' >
                               <h1 class="d-flex align-items-center text-light  fs-7 my-1" style="color: #ffb822 !important;">@yield('module','المحوسب')</h1>
                            <span class="h-20px border-gray-200 border-start ms-3 mx-2"></span>
                            <h1 class="d-flex align-items-center text-light  fs-8 my-1">@yield('sub',"وزارة العمل")</h1>
                            <span class="h-20px border-gray-200 border-start ms-3 mx-2"></span>
                            <ul class="breadcrumb breadcrumb-separatorless  fs-8 my-1">
                                <li class="breadcrumb-item text-muted">
                                     <span class="text-muted text-hover-primary">@yield('title',' ')</span>
                                </li>
                            </ul>
                            <!--end::Breadcrumb-->
                        </div>
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Toolbar-->
                <!--begin::Post-->
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <!-- <div id="kt_content_container" class="container-xxl">-->
                     <div id="kt_content_container" class="container-fluid">
                    @yield('content')
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Post-->
            </div>
            <!--end::Content-->
            <!--begin::Footer-->
            <div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
                <div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <div class="text-dark order-2 order-md-1">
                        <a href="#" target="_blank" class=" text-hover-primary fw-bolder text-dark">تم التطوير بواسطة دائرة الحاسوب - وزارة العمل</a>
                        <span class="  me-1 fw-bolder text-dark">© {{date("Y")}}</span>
                    </div>
                    <ul class="menu menu-gray-600 menu-hover-primary fw-normal order-1">
                        <li class="menu-item">
                            <a href="#" target="_blank" class="menu-link px-2 fw-normal text-dark">الأسئلة الشائعة</a>
                        </li>
                        <li class="menu-item">
                            <a href="#" target="_blank" class="menu-link px-2 fw-normal text-dark">تواصل معنا</a>
                        </li>
                        <li class="menu-item">
                            <a href="#" target="_blank" class="menu-link px-2 fw-normal text-dark">بوابة الخدمات الحكومية</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!--end::Footer-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Page-->
</div>
<!--end::Root-->
<!--begin::Drawers-->

<!--end::Drawers-->

<!--begin::Scrolltop-->
<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
    <!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
    <span class="svg-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
					<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="black" />
					<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="black" />
				</svg>
			</span>
    <!--end::Svg Icon-->
</div>
<!--end::Scrolltop-->
<!--end::Main-->
<script>var hostUrl = "assets/";</script>
<!--begin::Javascript-->
<!--begin::Global Javascript Bundle(used by all pages)-->
<script src="{{asset('assets/plugins/global/plugins.bundle.js')}}"></script>
<script src="{{asset('assets/js/scripts.bundle.js')}}"></script>

<script src="{{asset('assets/js/custom/documentation/documentation.js')}}"></script>
<script src="{{asset('assets/js/custom/documentation/search.js')}}"></script>
<script src="{{asset('assets/plugins/custom/prismjs/prismjs.bundle.js')}}"></script>

<!--end::Global Javascript Bundle-->
<!--begin::Page Custom Javascript(used by this page)-->
<script src="{{asset('assets/js/custom/widgets.js')}}"></script>
<script src="{{asset('assets/js/custom/apps/chat/chat.js')}}"></script>
<script src="{{asset('assets/js/custom/modals/create-app.js')}}"></script>
<script src="{{asset('assets/js/custom/modals/upgrade-plan.js')}}"></script>
<script src="{{asset('assets/flatpickr/ar.js')}}"></script>


<script src="{{asset('assets/jquery-validation/dist/jquery.validate.js')}}"></script>
<script src="{{asset('assets/jquery-validation/dist/localization/messages_ar.js')}}"></script>
<script src="{{asset('assets/jquery-validation/dist/additional-methods.js')}}"></script>
<script src="{{asset('assets/jquery-validation/dist/jquery-validation.init.js')}}"></script>



<script>
    function darkMode() {
        // Get the checkbox
        var checkBox = document.getElementById("DarkMode");

        // If the checkbox is checked, display the output text
        if (checkBox.checked == true){
            $("#kt_aside").removeClass('aside-light');
            $("#kt_aside").addClass('aside-dark');
            $.ajax({
                type: 'get',
                url: '{{url('/')}}' + '/darkMode',
            });
        } else {
            $("#kt_aside").removeClass('aside-dark');
            $("#kt_aside").addClass('aside-light');
            $.ajax({
                type: 'get',
                url: '{{url('/')}}' + '/lightMode',
            });
        }
    }
</script>

 <script>
        $('.input_date_').flatpickr({
           format : 'dd-mm-yyyy',

             // dateFormat: "Y-m-d",
                         //  dateFormat: "d-m-Y",

                "locale": "ar",

               // weekNumbers: true,

        });
        flatpickr('#START_DATE_INrrrrrrrr', {
    "locale": "ar",
    "dateFormat": "Y/m/d",
});

    </script>
    <script>
        setTimeout(function() {
            $('.alert-session-flash').fadeOut('low');
        }, 5000); // <-- time in milliseconds

    </script>
@yield('scripts')

<!--end::Page Custom Javascript-->
<!--end::Javascript-->
</body>
<!--end::Body-->
</html>
