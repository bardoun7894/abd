<!DOCTYPE html>
<html  lang="ar" direction="rtl" style="direction: rtl;">
<!--begin::Head-->
<head><base href="../../">
    <title>وزارة العمل - @yield('title','لوحة التحكم')</title>

    <meta name="description" content="وزارة العمل" />
    <meta name="keywords" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="Metronic - Bootstrap 5 HTML, VueJS, React, Angular &amp; Laravel Admin Dashboard Theme" />
    <meta property="og:url" content="" />
    <meta property="og:site_name" content="" />
    <link rel="canonical" href="" />
    <link rel="shortcut icon" href="{{asset('assets/media/logos/logo.jpg')}}" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="{{asset('assets/plugins/global/plugins.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/style.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/fonts/dinnext/styles.rtl.css')}}" rel="stylesheet" type="text/css" />
    <style>
        html,body{
            font-family: DIN Next LT Arabic, Sans-serif !important;
            font-size: 15px !important;
        }
        @media (min-width: 1400px){
            .container-xxl, .container-xl, .container-lg, .container-md, .container-sm, .container {
                max-width: 1430px;
            }
        }
        .card .card-header .card-title, .card .card-header .card-title .card-label {
            font-weight: 500;
            font-size: 1.675rem;
            color: #d3224c;
        }
        .aside-menu{
            font-size: 14px !important;
        }
        .aside-menu .menu-accordion .menu-link .menu-title {
            font-weight: 600;
        }
        .aside-menu .menu-accordion .menu-sub-accordion .menu-link .menu-title {
            font-weight: 600;
        }
        .menu-title {
            font-size: 15px !important;
        }
        .toolbar {
            /*background-color: #1e1e2d;*/
            background-color: #024593;
        }
        .input-group-text {
            /*border-top-left-radius: 0;*/
            /*border-bottom-left-radius: 0;*/
            border: 1px solid #adafba;
            padding: 0.71rem 1rem;
            border-radius: 0;
        }
        .page-item.active .page-link {
            border-radius: 0;
        }
        #kt_datatable_filter.dataTables_filter{
            display: none;
        }
        .invalid-feedback {
            display: block;
        }
    </style>
    <style>
        button.btn,a.btn {
            padding: calc(0.75rem + 1px) calc(1.5rem + 1px) !important;
        }
        div.dt-buttons {
            margin-top: 13px;
        }
        .dt-buttons .btn{
            /*border-radius: 7px !important;*/
            border-radius: 0 !important;
            margin-right: 10px !important;
            padding: 7px 15px 10px !important;
            font-size: 15px;
        }
        .form-control {
            padding: 0.46rem 1rem;
            font-size: 1rem;
        }
        .accordion-body {
            padding: 0;
        }
        .accordion-body .card-body {
            padding: 1rem 0;
        }
        .input-group-append .input-group-text {
            padding: 0.71rem 1rem;
            /*padding: 1rem;*/
            border-radius: 0 !important;
        }
        .btn.btn-outline-dashed {
            border-radius: 0;
        }
        .search_input label{
            margin-bottom: 5px;
            font-weight: 600;
        }
        [type="tel"], [type="url"], [type="email"], [type="number"] {
            direction: rtl;
        }
        .table.gy-5 th, .table.gy-5 td {
            padding-top: 0.7rem;
            padding-bottom: 0.7rem;
            text-align: center;
            vertical-align: middle;
        }
        .table.gy-5 th{
            padding-right: 10px!important;
            padding-left: 10px!important;
            padding-top: 0.4rem!important;
        }
        .table,.table tr {
            font-size: 15px !important;
        }
        .select2-container--bootstrap5 .select2-selection--multiple:not(.form-select-sm):not(.form-select-lg) .select2-selection__choice .select2-selection__choice__remove {
            margin-left: 0.5rem;
            margin-right: 0;
        }
        .select2-container--bootstrap5 .select2-selection--multiple:not(.form-select-sm):not(.form-select-lg) .select2-selection__choice .select2-selection__choice__display {
            margin-right: 1.2rem;
            margin-left: 0;
        }
        .select2-container--bootstrap5 .select2-search.select2-search--inline .select2-search__field {
            font-weight: 400;
            font-family: inherit;
            height: 20px;
        }
        .form-control,.select2-selection,.form-select {
            border: 1px solid #adafba !important;
            border-radius: 0;
        }

        .form-select {
            line-height: 1.2;
            font-size: 14px;
            padding: 0.55rem 1rem 0.75rem 3rem;
            /*padding: 0.45rem 1rem 0.75rem 3rem;*/
        }
        .select2-container--bootstrap5 .select2-selection--multiple:not(.form-select-sm):not(.form-select-lg) {
            padding-top: calc((1.3rem + 2px - 0.8rem) / 2);
            padding-bottom: calc((1.34rem + 1px - 1.2rem) / 2);
        }

        .btn:not(.btn-outline):not(.btn-dashed):not(.border-hover):not(.border-active):not(.btn-flush):not(.btn-icon) {
            /*border-radius: 20px;*/
            border-radius: 0;
            padding: calc(0.55rem + 1px) calc(1.5rem + 1px) !important;
        }
        table .btn i {
            font-size: 1.2rem;
        }
        .iziModal .iziModal-header.iziModal-noSubtitle {
            padding: 10px 15px 12px 40px !important;
        }
        .iziModal .iziModal-header-icon {
            float: right !important;
        }
        .iziModal .iziModal-button-close {
            left: 10px;
            right:inherit !important;
        }
        .iziModal .iziModal-header.iziModal-noSubtitle .iziModal-header-icon {
            padding-left: 13px !important;
            padding-right: inherit !important;
        }
        .iziModal .iziModal-header-subtitle, .iziModal .iziModal-header-title {
            font-family: inherit !important;
        }
        .iziModal .iziModal-header-title {
            line-height: 1.5 !important;
            margin-top: -4px !important;
        }
        .table.gy-5 th, .table.gy-5 td,.table tfoot tr:last-child, .table tbody tr:last-child,.table tfoot tr:last-child th, .table tfoot tr:last-child td, .table tbody tr:last-child th, .table tbody tr:last-child td {
            border: 1px solid #e6e7e8 !important;
        }
        .table.gy-5 td {
            padding-top: 0.3rem!important;
            padding-bottom: 0.3rem!important;
        }
        .table .btn.btn-icon.btn-sm,.table .btn-group-sm > .btn.btn-icon {
            height: calc(1em + 1.1rem + 2px) !important;
            width: calc(1em + 1.1rem + 2px) !important;
            padding: calc(0.75rem + 1px) calc(1rem + 1px) !important;
        }
        .btn.btn-icon.btn-sm,.btn-group-sm > .btn.btn-icon {
            border-radius: 0 !important;
        }
        .table thead tr{
            /*background: #1e1e2d;*/
            /*background: #2d2d3f;*/
            background: #024593;
        }
        .table thead tr th{
            color: #ffffff;
            /*padding-bottom: 1rem !important;*/
            padding-bottom: .6rem !important;
        }
        table.dataTable>thead .sorting:after, table.dataTable>thead .sorting:before, table.dataTable>thead .sorting_asc:after, table.dataTable>thead .sorting_asc:before, table.dataTable>thead .sorting_asc_disabled:after, table.dataTable>thead .sorting_asc_disabled:before, table.dataTable>thead .sorting_desc:after, table.dataTable>thead .sorting_desc:before, table.dataTable>thead .sorting_desc_disabled:after, table.dataTable>thead .sorting_desc_disabled:before {
            display: none !important;
        }
        .fw-bold {
            font-weight: 600 !important;
            font-size: 14px !important;
        }
        .card .card-header .card-title .card-label {
            font-weight: 600;
        }
        .iziModal .iziModal-header.iziModal-noSubtitle .iziModal-header-title {
            font-size: 16px!important;
            font-weight: 600!important;
        }
        .form-check.form-check-custom .fv-plugins-message-container.invalid-feedback{
            position: absolute;
            right: 0;
        }
        #kt_datatable_wrapper{
            position: relative;
        }
        #kt_datatable_wrapper #kt_datatable_filter{
            position: absolute;
            /*top: 0;*/
            /*right: 237px;*/
            top: 5px;
            left: 15%;
        }
        .form-check-input[type=checkbox] {
            border-radius: 0 !important;
            cursor:pointer;
        }
        /*#kt_datatable_wrapper #kt_datatable_filter input[type=search]{*/
        /*min-height: 35px !important;*/
        /*padding: 0.26rem 1rem !important;*/
        /*}*/
    </style>
{{--    <style>--}}
{{--        input[type="date"]::-webkit-datetime-edit, input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-clear-button {--}}
{{--            color: #fff;--}}
{{--            position: relative;--}}
{{--        }--}}

{{--        input[type="date"]::-webkit-datetime-edit-year-field{--}}
{{--            position: absolute !important;--}}
{{--            border-left:1px solid #8c8c8c;--}}
{{--            padding: 2px;--}}
{{--            color:#000;--}}
{{--            left: 56px;--}}
{{--        }--}}

{{--        input[type="date"]::-webkit-datetime-edit-month-field{--}}
{{--            position: absolute !important;--}}
{{--            border-left:1px solid #8c8c8c;--}}
{{--            padding: 2px;--}}
{{--            color:#000;--}}
{{--            left: 26px;--}}
{{--        }--}}


{{--        input[type="date"]::-webkit-datetime-edit-day-field{--}}
{{--            position: absolute !important;--}}
{{--            color:#000;--}}
{{--            padding: 2px;--}}
{{--            left: 4px;--}}

{{--        }--}}
{{--    </style>--}}
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
            <!--begin::Brand-->
            <div class="aside-logo flex-column-auto" id="kt_aside_logo">
                <!--begin::Logo-->
                <a href="{{route('home')}}">
                    <img alt="Logo" src="{{asset('assets/media/logos/logo-1.png')}}" class="h-55px logo" />
                </a>
                <!--end::Logo-->
                <!--begin::Aside toggler-->
                <div id="kt_aside_toggle" class="btn btn-icon w-auto px-0 btn-active-color-primary aside-toggle" data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="aside-minimize">
                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr079.svg-->
                    <span class="svg-icon svg-icon-1 rotate-180">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
									<path opacity="0.5" d="M14.2657 11.4343L18.45 7.25C18.8642 6.83579 18.8642 6.16421 18.45 5.75C18.0358 5.33579 17.3642 5.33579 16.95 5.75L11.4071 11.2929C11.0166 11.6834 11.0166 12.3166 11.4071 12.7071L16.95 18.25C17.3642 18.6642 18.0358 18.6642 18.45 18.25C18.8642 17.8358 18.8642 17.1642 18.45 16.75L14.2657 12.5657C13.9533 12.2533 13.9533 11.7467 14.2657 11.4343Z" fill="black" />
									<path d="M8.2657 11.4343L12.45 7.25C12.8642 6.83579 12.8642 6.16421 12.45 5.75C12.0358 5.33579 11.3642 5.33579 10.95 5.75L5.40712 11.2929C5.01659 11.6834 5.01659 12.3166 5.40712 12.7071L10.95 18.25C11.3642 18.6642 12.0358 18.6642 12.45 18.25C12.8642 17.8358 12.8642 17.1642 12.45 16.75L8.2657 12.5657C7.95328 12.2533 7.95328 11.7467 8.2657 11.4343Z" fill="black" />
								</svg>
							</span>
                    <!--end::Svg Icon-->
                </div>
                <!--end::Aside toggler-->
            </div>
            <!--end::Brand-->
















































































































































            <!--end::Aside menu-->
            <!--begin::Footer-->
            <!--end::Footer-->
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
                                <!--begin::Search-->

                                <!--end::Search-->
                                <!--begin::Activities-->

                                <!--end::Activities-->
                                <!--begin::Notifications-->
                                <!--                                <div class="d-flex align-items-center ms-1 ms-lg-3">-->
                                <!--begin::Menu- wrapper-->
                                <!--                                    <div class="btn btn-icon btn-active-light-primary position-relative w-30px h-30px w-md-40px h-md-40px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">-->
                                <!--begin::Svg Icon | path: icons/duotune/general/gen022.svg-->
                                <!--                                        <span class="svg-icon svg-icon-1">-->
                                <!--													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">-->
                                <!--														<path d="M11.2929 2.70711C11.6834 2.31658 12.3166 2.31658 12.7071 2.70711L15.2929 5.29289C15.6834 5.68342 15.6834 6.31658 15.2929 6.70711L12.7071 9.29289C12.3166 9.68342 11.6834 9.68342 11.2929 9.29289L8.70711 6.70711C8.31658 6.31658 8.31658 5.68342 8.70711 5.29289L11.2929 2.70711Z" fill="black" />-->
                                <!--														<path d="M11.2929 14.7071C11.6834 14.3166 12.3166 14.3166 12.7071 14.7071L15.2929 17.2929C15.6834 17.6834 15.6834 18.3166 15.2929 18.7071L12.7071 21.2929C12.3166 21.6834 11.6834 21.6834 11.2929 21.2929L8.70711 18.7071C8.31658 18.3166 8.31658 17.6834 8.70711 17.2929L11.2929 14.7071Z" fill="black" />-->
                                <!--														<path opacity="0.3" d="M5.29289 8.70711C5.68342 8.31658 6.31658 8.31658 6.70711 8.70711L9.29289 11.2929C9.68342 11.6834 9.68342 12.3166 9.29289 12.7071L6.70711 15.2929C6.31658 15.6834 5.68342 15.6834 5.29289 15.2929L2.70711 12.7071C2.31658 12.3166 2.31658 11.6834 2.70711 11.2929L5.29289 8.70711Z" fill="black" />-->
                                <!--														<path opacity="0.3" d="M17.2929 8.70711C17.6834 8.31658 18.3166 8.31658 18.7071 8.70711L21.2929 11.2929C21.6834 11.6834 21.6834 12.3166 21.2929 12.7071L18.7071 15.2929C18.3166 15.6834 17.6834 15.6834 17.2929 15.2929L14.7071 12.7071C14.3166 12.3166 14.3166 11.6834 14.7071 11.2929L17.2929 8.70711Z" fill="black" />-->
                                <!--													</svg>-->
                                <!--												</span>-->
                                <!--end::Svg Icon-->
                                <!--                                    </div>-->
                                <!--begin::Menu-->
                                <!--                                    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true">-->
                                <!--begin::Heading-->
                            <!--                                        <div class="d-flex flex-column bgi-no-repeat rounded-top" style="background-image:url('{{asset("assets/media/misc/pattern-1.jpg")}}')">-->
                                <!--begin::Title-->
                                <!--                                            <h3 class="text-white fw-bold px-9 mt-10 mb-6">الإشعارات-->
                                <!--                                                <span class="fs-8 opacity-75 ps-3">24 إشعار</span></h3>-->
                                <!--end::Title-->
                                <!--begin::Tabs-->
                                <!--                                            <ul class="nav nav-line-tabs nav-line-tabs-2x nav-stretch fw-bold px-9">-->
                                <!--                                                <li class="nav-item">-->
                                <!--                                                    <a class="nav-link text-white opacity-75 opacity-state-100 pb-4 active" data-bs-toggle="tab" href="#kt_topbar_notifications_1">الإشعارات</a>-->
                                <!--                                                </li>-->
                                <!--                                                <li class="nav-item">-->
                                <!--                                                    <a class="nav-link text-white opacity-75 opacity-state-100 pb-4" data-bs-toggle="tab" href="#kt_topbar_notifications_3">السجلات</a>-->
                                <!--                                                </li>-->
                                <!--                                            </ul>-->
                                <!--end::Tabs-->
                                <!--                                        </div>-->
                                <!--end::Heading-->
                                <!--begin::Tab content-->
                                <!--                                        <div class="tab-content">-->
                                <!--begin::Tab panel-->
                                <!--                                            <div class="tab-pane fade show active" id="kt_topbar_notifications_1" role="tabpanel">-->
                                <!--begin::Items-->
                                <!--                                                <div class="scroll-y mh-325px my-5 px-8">-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center">-->
                                <!--begin::Symbol-->
                                <!--                                                            <div class="symbol symbol-35px me-4">-->
                                <!--																		<span class="symbol-label bg-light-primary">-->
                                <!--begin::Svg Icon | path: icons/duotune/technology/teh008.svg-->
                                <!--																			<span class="svg-icon svg-icon-2 svg-icon-primary">-->
                                <!--																				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">-->
                                <!--																					<path opacity="0.3" d="M11 6.5C11 9 9 11 6.5 11C4 11 2 9 2 6.5C2 4 4 2 6.5 2C9 2 11 4 11 6.5ZM17.5 2C15 2 13 4 13 6.5C13 9 15 11 17.5 11C20 11 22 9 22 6.5C22 4 20 2 17.5 2ZM6.5 13C4 13 2 15 2 17.5C2 20 4 22 6.5 22C9 22 11 20 11 17.5C11 15 9 13 6.5 13ZM17.5 13C15 13 13 15 13 17.5C13 20 15 22 17.5 22C20 22 22 20 22 17.5C22 15 20 13 17.5 13Z" fill="black" />-->
                                <!--																					<path d="M17.5 16C17.5 16 17.4 16 17.5 16L16.7 15.3C16.1 14.7 15.7 13.9 15.6 13.1C15.5 12.4 15.5 11.6 15.6 10.8C15.7 9.99999 16.1 9.19998 16.7 8.59998L17.4 7.90002H17.5C18.3 7.90002 19 7.20002 19 6.40002C19 5.60002 18.3 4.90002 17.5 4.90002C16.7 4.90002 16 5.60002 16 6.40002V6.5L15.3 7.20001C14.7 7.80001 13.9 8.19999 13.1 8.29999C12.4 8.39999 11.6 8.39999 10.8 8.29999C9.99999 8.19999 9.20001 7.80001 8.60001 7.20001L7.89999 6.5V6.40002C7.89999 5.60002 7.19999 4.90002 6.39999 4.90002C5.59999 4.90002 4.89999 5.60002 4.89999 6.40002C4.89999 7.20002 5.59999 7.90002 6.39999 7.90002H6.5L7.20001 8.59998C7.80001 9.19998 8.19999 9.99999 8.29999 10.8C8.39999 11.5 8.39999 12.3 8.29999 13.1C8.19999 13.9 7.80001 14.7 7.20001 15.3L6.5 16H6.39999C5.59999 16 4.89999 16.7 4.89999 17.5C4.89999 18.3 5.59999 19 6.39999 19C7.19999 19 7.89999 18.3 7.89999 17.5V17.4L8.60001 16.7C9.20001 16.1 9.99999 15.7 10.8 15.6C11.5 15.5 12.3 15.5 13.1 15.6C13.9 15.7 14.7 16.1 15.3 16.7L16 17.4V17.5C16 18.3 16.7 19 17.5 19C18.3 19 19 18.3 19 17.5C19 16.7 18.3 16 17.5 16Z" fill="black" />-->
                                <!--																				</svg>-->
                                <!--																			</span>-->
                                <!--end::Svg Icon-->
                                <!--																		</span>-->
                                <!--                                                            </div>-->
                                <!--end::Symbol-->
                                <!--begin::Title-->
                                <!--                                                            <div class="mb-0 me-2">-->
                                <!--                                                                <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-bolder">تم إضافة صنف جديد</a>-->
                            <!--{{--                                                                <div class="text-gray-400 fs-7">Phase 1 development</div>--}}-->
                                <!--                                                            </div>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">منذ ساعة</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center">-->
                                <!--begin::Symbol-->
                                <!--                                                            <div class="symbol symbol-35px me-4">-->
                                <!--																		<span class="symbol-label bg-light-danger">-->
                                <!--begin::Svg Icon | path: icons/duotune/general/gen044.svg-->
                                <!--																			<span class="svg-icon svg-icon-2 svg-icon-danger">-->
                                <!--																				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">-->
                                <!--																					<rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="black" />-->
                                <!--																					<rect x="11" y="14" width="7" height="2" rx="1" transform="rotate(-90 11 14)" fill="black" />-->
                                <!--																					<rect x="11" y="17" width="2" height="2" rx="1" transform="rotate(-90 11 17)" fill="black" />-->
                                <!--																				</svg>-->
                                <!--																			</span>-->
                                <!--end::Svg Icon-->
                                <!--																		</span>-->
                                <!--                                                            </div>-->
                                <!--end::Symbol-->
                                <!--begin::Title-->
                                <!--                                                            <div class="mb-0 me-2">-->
                                <!--                                                                <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-bolder">تم حذف صنف</a>-->
                            <!--{{--                                                                <div class="text-gray-400 fs-7">Confidential staff documents</div>--}}-->
                                <!--                                                            </div>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">منذ ساعتين</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center">-->
                                <!--begin::Symbol-->
                                <!--                                                            <div class="symbol symbol-35px me-4">-->
                                <!--																		<span class="symbol-label bg-light-warning">-->
                                <!--begin::Svg Icon | path: icons/duotune/finance/fin006.svg-->
                                <!--																			<span class="svg-icon svg-icon-2 svg-icon-warning">-->
                                <!--																				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">-->
                                <!--																					<path opacity="0.3" d="M20 15H4C2.9 15 2 14.1 2 13V7C2 6.4 2.4 6 3 6H21C21.6 6 22 6.4 22 7V13C22 14.1 21.1 15 20 15ZM13 12H11C10.5 12 10 12.4 10 13V16C10 16.5 10.4 17 11 17H13C13.6 17 14 16.6 14 16V13C14 12.4 13.6 12 13 12Z" fill="black" />-->
                                <!--																					<path d="M14 6V5H10V6H8V5C8 3.9 8.9 3 10 3H14C15.1 3 16 3.9 16 5V6H14ZM20 15H14V16C14 16.6 13.5 17 13 17H11C10.5 17 10 16.6 10 16V15H4C3.6 15 3.3 14.9 3 14.7V18C3 19.1 3.9 20 5 20H19C20.1 20 21 19.1 21 18V14.7C20.7 14.9 20.4 15 20 15Z" fill="black" />-->
                                <!--																				</svg>-->
                                <!--																			</span>-->
                                <!--end::Svg Icon-->
                                <!--																		</span>-->
                                <!--                                                            </div>-->
                                <!--end::Symbol-->
                                <!--begin::Title-->
                                <!--                                                            <div class="mb-0 me-2">-->
                                <!--                                                                <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-bolder">تم تعديل تصنيف</a>-->
                            <!--{{--                                                                <div class="text-gray-400 fs-7">Corporeate staff profiles</div>--}}-->
                                <!--                                                            </div>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">منذ 5 ساعات</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center">-->
                                <!--begin::Symbol-->
                                <!--                                                            <div class="symbol symbol-35px me-4">-->
                                <!--																		<span class="symbol-label bg-light-success">-->
                                <!--begin::Svg Icon | path: icons/duotune/files/fil023.svg-->
                                <!--																			<span class="svg-icon svg-icon-2 svg-icon-success">-->
                                <!--																				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">-->
                                <!--																					<path opacity="0.3" d="M5 15C3.3 15 2 13.7 2 12C2 10.3 3.3 9 5 9H5.10001C5.00001 8.7 5 8.3 5 8C5 5.2 7.2 3 10 3C11.9 3 13.5 4 14.3 5.5C14.8 5.2 15.4 5 16 5C17.7 5 19 6.3 19 8C19 8.4 18.9 8.7 18.8 9C18.9 9 18.9 9 19 9C20.7 9 22 10.3 22 12C22 13.7 20.7 15 19 15H5ZM5 12.6H13L9.7 9.29999C9.3 8.89999 8.7 8.89999 8.3 9.29999L5 12.6Z" fill="black" />-->
                                <!--																					<path d="M17 17.4V12C17 11.4 16.6 11 16 11C15.4 11 15 11.4 15 12V17.4H17Z" fill="black" />-->
                                <!--																					<path opacity="0.3" d="M12 17.4H20L16.7 20.7C16.3 21.1 15.7 21.1 15.3 20.7L12 17.4Z" fill="black" />-->
                                <!--																					<path d="M8 12.6V18C8 18.6 8.4 19 9 19C9.6 19 10 18.6 10 18V12.6H8Z" fill="black" />-->
                                <!--																				</svg>-->
                                <!--																			</span>-->
                                <!--end::Svg Icon-->
                                <!--																		</span>-->
                                <!--                                                            </div>-->
                                <!--end::Symbol-->
                                <!--begin::Title-->
                                <!--                                                            <div class="mb-0 me-2">-->
                                <!--                                                                <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-bolder">تم إضافة تصنيف جديد</a>-->
                            <!--{{--                                                                <div class="text-gray-400 fs-7">New frontend admin theme</div>--}}-->
                                <!--                                                            </div>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">منذ يوم</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center">-->
                                <!--begin::Symbol-->
                                <!--                                                            <div class="symbol symbol-35px me-4">-->
                                <!--																		<span class="symbol-label bg-light-primary">-->
                                <!--begin::Svg Icon | path: icons/duotune/maps/map001.svg-->
                                <!--																			<span class="svg-icon svg-icon-2 svg-icon-primary">-->
                                <!--																				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">-->
                                <!--																					<path opacity="0.3" d="M6 22H4V3C4 2.4 4.4 2 5 2C5.6 2 6 2.4 6 3V22Z" fill="black" />-->
                                <!--																					<path d="M18 14H4V4H18C18.8 4 19.2 4.9 18.7 5.5L16 9L18.8 12.5C19.3 13.1 18.8 14 18 14Z" fill="black" />-->
                                <!--																				</svg>-->
                                <!--																			</span>-->
                                <!--end::Svg Icon-->
                                <!--																		</span>-->
                                <!--                                                            </div>-->
                                <!--end::Symbol-->
                                <!--begin::Title-->
                                <!--                                                            <div class="mb-0 me-2">-->
                                <!--                                                                <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-bolder">تم تعديل صنف</a>-->
                            <!--{{--                                                                <div class="text-gray-400 fs-7">Product launch status update</div>--}}-->
                                <!--                                                            </div>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">منذ أسبوع</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->

                                <!--                                                </div>-->
                                <!--end::Items-->
                                <!--begin::View more-->
                                <!--                                                <div class="py-3 text-center border-top">-->
                                <!--                                                    <a href="#" class="btn btn-color-gray-600 btn-active-color-primary">عرض الكل-->
                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr064.svg-->
                                <!--                                                        <span class="svg-icon svg-icon-5">-->
                                <!--																<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">-->
                                <!--																	<rect opacity="0.5" x="18" y="13" width="13" height="2" rx="1" transform="rotate(-180 18 13)" fill="black" />-->
                                <!--																	<path d="M15.4343 12.5657L11.25 16.75C10.8358 17.1642 10.8358 17.8358 11.25 18.25C11.6642 18.6642 12.3358 18.6642 12.75 18.25L18.2929 12.7071C18.6834 12.3166 18.6834 11.6834 18.2929 11.2929L12.75 5.75C12.3358 5.33579 11.6642 5.33579 11.25 5.75C10.8358 6.16421 10.8358 6.83579 11.25 7.25L15.4343 11.4343C15.7467 11.7467 15.7467 12.2533 15.4343 12.5657Z" fill="black" />-->
                                <!--																</svg>-->
                                <!--															</span>-->
                                <!--end::Svg Icon--></a>
                                <!--                                                </div>-->
                                <!--end::View more-->
                                <!--                                            </div>-->
                                <!--end::Tab panel-->
                                <!--begin::Tab panel-->
                                <!--end::Tab panel-->
                                <!--begin::Tab panel-->
                                <!--                                            <div class="tab-pane fade" id="kt_topbar_notifications_3" role="tabpanel">-->
                                <!--begin::Items-->
                                <!--                                                <div class="scroll-y mh-325px my-5 px-8">-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center me-2">-->
                                <!--begin::Code-->
                                <!--                                                            <span class="w-70px badge badge-light-success me-4">200 OK</span>-->
                                <!--end::Code-->
                                <!--begin::Title-->
                                <!--                                                            <a href="#" class="text-gray-800 text-hover-primary fw-bold">تصنيف جديد</a>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">الأن</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center me-2">-->
                                <!--begin::Code-->
                                <!--                                                            <span class="w-70px badge badge-light-danger me-4">500 ERR</span>-->
                                <!--end::Code-->
                                <!--begin::Title-->
                                <!--                                                            <a href="#" class="text-gray-800 text-hover-primary fw-bold">صنف جديد</a>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">منذ ساعتين</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center me-2">-->
                                <!--begin::Code-->
                                <!--                                                            <span class="w-70px badge badge-light-success me-4">200 OK</span>-->
                                <!--end::Code-->
                                <!--begin::Title-->
                                <!--                                                            <a href="#" class="text-gray-800 text-hover-primary fw-bold">مستخدم جديد</a>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">منذ 5 ساعات</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center me-2">-->
                                <!--begin::Code-->
                                <!--                                                            <span class="w-70px badge badge-light-warning me-4">300 WRN</span>-->
                                <!--end::Code-->
                                <!--begin::Title-->
                                <!--                                                            <a href="#" class="text-gray-800 text-hover-primary fw-bold">تعديل صنف</a>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">منذ يومين</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center me-2">-->
                                <!--begin::Code-->
                                <!--                                                            <span class="w-70px badge badge-light-success me-4">200 OK</span>-->
                                <!--end::Code-->
                                <!--begin::Title-->
                                <!--                                                            <a href="#" class="text-gray-800 text-hover-primary fw-bold">حذف تصنيف</a>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">منذ أسبوع</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center me-2">-->
                                <!--begin::Code-->
                                <!--                                                            <span class="w-70px badge badge-light-success me-4">200 OK</span>-->
                                <!--end::Code-->
                                <!--begin::Title-->
                                <!--                                                            <a href="#" class="text-gray-800 text-hover-primary fw-bold">حذف صنف</a>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">منذ أسبوعين</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center me-2">-->
                                <!--begin::Code-->
                                <!--                                                            <span class="w-70px badge badge-light-warning me-4">300 WRN</span>-->
                                <!--end::Code-->
                                <!--begin::Title-->
                                <!--                                                            <a href="#" class="text-gray-800 text-hover-primary fw-bold">تعديل تصنيف</a>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">15 يوليو</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->
                                <!--begin::Item-->
                                <!--                                                    <div class="d-flex flex-stack py-4">-->
                                <!--begin::Section-->
                                <!--                                                        <div class="d-flex align-items-center me-2">-->
                                <!--begin::Code-->
                                <!--                                                            <span class="w-70px badge badge-light-warning me-4">300 WRN</span>-->
                                <!--end::Code-->
                                <!--begin::Title-->
                                <!--                                                            <a href="#" class="text-gray-800 text-hover-primary fw-bold">مستخدم جديد</a>-->
                                <!--end::Title-->
                                <!--                                                        </div>-->
                                <!--end::Section-->
                                <!--begin::Label-->
                                <!--                                                        <span class="badge badge-light fs-8">3 يوليو</span>-->
                                <!--end::Label-->
                                <!--                                                    </div>-->
                                <!--end::Item-->

                                <!--                                                </div>-->
                                <!--end::Items-->
                                <!--begin::View more-->
                                <!--                                                <div class="py-3 text-center border-top">-->
                                <!--                                                    <a href="#" class="btn btn-color-gray-600 btn-active-color-primary">عرض الكل-->
                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr064.svg-->
                                <!--                                                        <span class="svg-icon svg-icon-5">-->
                                <!--																<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">-->
                                <!--																	<rect opacity="0.5" x="18" y="13" width="13" height="2" rx="1" transform="rotate(-180 18 13)" fill="black" />-->
                                <!--																	<path d="M15.4343 12.5657L11.25 16.75C10.8358 17.1642 10.8358 17.8358 11.25 18.25C11.6642 18.6642 12.3358 18.6642 12.75 18.25L18.2929 12.7071C18.6834 12.3166 18.6834 11.6834 18.2929 11.2929L12.75 5.75C12.3358 5.33579 11.6642 5.33579 11.25 5.75C10.8358 6.16421 10.8358 6.83579 11.25 7.25L15.4343 11.4343C15.7467 11.7467 15.7467 12.2533 15.4343 12.5657Z" fill="black" />-->
                                <!--																</svg>-->
                                <!--															</span>-->
                                <!--end::Svg Icon--></a>
                                <!--                                                </div>-->
                                <!--end::View more-->
                                <!--                                            </div>-->
                                <!--end::Tab panel-->
                                <!--                                        </div>-->
                                <!--end::Tab content-->
                                <!--                                    </div>-->
                                <!--end::Menu-->
                                <!--end::Menu wrapper-->
                                <!--                                </div>-->
                                <!--end::Notifications-->
                                <!--begin::Chat-->

                                <!--end::Chat-->
                                <!--begin::Quick links-->

                                <!--end::Quick links-->
                                @php $userAuth = explode(' ',trim(Auth::user()->name)); @endphp
                                <div class="d-flex align-items-center ms-1 ms-lg-3 fw-bold  fs-7" style=""><span style="margin-top:-3px">@auth(){{Auth::user()->name}} @else User Name @endauth</span>
                                <!--<div class="d-flex align-items-center ms-1 ms-lg-3 fw-bold  fs-7" style=""><span style="margin-top:-3px">@auth(){{$userAuth[0].' '.$userAuth[1].' '.$userAuth[2].' '.$userAuth[3]}} @isset($userAuth[4]) {{$userAuth[4]}} @endisset @else User Name @endauth</span> -->
                                    <span class="badge badge-light-success fw-bolder fs-8 px-2 py-1 ms-2">@auth(){{Auth::user()->role_id != null ? Auth::user()->role->name :'Employee'}} @else Employee @endauth</span>
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
                                                    <div class="fw-bolder d-flex align-items-center fs-8">@auth(){{$userAuth[0].' '.$userAuth[3]}} @isset($userAuth[4]) {{$userAuth[4]}} @endisset @else User Name @endauth
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
                        <div data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}" class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                            <!--begin::Title-->
                            <h1 class="d-flex align-items-center text-light fw-bolder fs-4 my-1" style="color: #F5DEB3 !important;">@yield('module','المخازن')</h1>
                            <!--end::Title-->
                            <!--begin::Separator-->
                            <span class="h-20px border-gray-200 border-start mx-4"></span>
                            <h1 class="d-flex align-items-center text-light fw-bolder fs-5 my-1">@yield('sub',"وزارة العمل")</h1>
                            <!--end::Title-->
                            <!--begin::Separator-->
                            <span class="h-20px border-gray-200 border-start mx-4"></span>
                            <!--begin::Breadcrumb-->
                            <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                                <!--begin::Item-->
                                <li class="breadcrumb-item text-muted">
                                    <span class="text-muted text-hover-primary">@yield('title',' ')</span>
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                            {{--                                <li class="breadcrumb-item">--}}
                            {{--                                    <span class="bullet bg-gray-200 w-5px h-2px"></span>--}}
                            {{--                                </li>--}}
                            <!--end::Item-->
                                <!--begin::Item-->

                                <!--end::Item-->
                                <!--begin::Item-->

                                <!--end::Item-->
                                <!--begin::Item-->
                                <!--end::Item-->
                            </ul>
                            <!--end::Breadcrumb-->
                        </div>
                        <!--end::Page title-->
                        <!--begin::Actions-->
                        <!--end::Actions-->
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Toolbar-->
                <!--begin::Post-->
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <!--begin::Container-->
                    <div id="kt_content_container" class="container-xxl">
                        @yield('content')
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Post-->
            </div>
            <!--end::Content-->
            <!--begin::Footer-->
            <div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
                <!--begin::Container-->
                <div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <!--begin::Copyright-->
                    <div class="text-dark order-2 order-md-1">
                        <a href="#" target="_blank" class="text-gray-800 text-hover-primary">تم التطوير بواسطة دائرة الحاسوب - وزارة العمل</a>
                        <span class="text-muted fw-bold me-1">© {{date("Y")}}</span>
                    </div>
                    <!--end::Copyright-->
                    <!--begin::Menu-->
                    <ul class="menu menu-gray-600 menu-hover-primary fw-bold order-1">
                        <li class="menu-item">
                            <a href="#" target="_blank" class="menu-link px-2">الأسئلة الشائعة</a>
                        </li>
                        <li class="menu-item">
                            <a href="#" target="_blank" class="menu-link px-2">تواصل معنا</a>
                        </li>
                        <li class="menu-item">
                            <a href="#" target="_blank" class="menu-link px-2">بوابة الخدمات الحكومية</a>
                        </li>
                    </ul>
                    <!--end::Menu-->
                </div>
                <!--end::Container-->
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
<!--end::Global Javascript Bundle-->
<!--begin::Page Custom Javascript(used by this page)-->
<script src="{{asset('assets/js/custom/widgets.js')}}"></script>
<script src="{{asset('assets/js/custom/apps/chat/chat.js')}}"></script>
<script src="{{asset('assets/js/custom/modals/create-app.js')}}"></script>
<script src="{{asset('assets/js/custom/modals/upgrade-plan.js')}}"></script>
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
