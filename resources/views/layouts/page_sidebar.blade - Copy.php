<!--begin::Brand-->
<div class="aside-logo flex-column-auto" id="kt_aside_logo">
    <!--begin::Logo-->
    <a href="{{ route('home') }}">
        <img alt="Logo" src="{{ asset('assets/media/logos/logo-1.png') }}" class="h-55px logo" />
    </a>
    <!--end::Logo-->
    <!--begin::Aside toggler-->
    <div id="kt_aside_toggle" class="btn btn-icon w-auto px-0 btn-active-color-primary aside-toggle" data-kt-toggle="true"
        data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="aside-minimize">
        <!--begin::Svg Icon | path: icons/duotune/arrows/arr079.svg-->
        <span class="svg-icon svg-icon-2 rotate-180">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path opacity="0.5"
                    d="M14.2657 11.4343L18.45 7.25C18.8642 6.83579 18.8642 6.16421 18.45 5.75C18.0358 5.33579 17.3642 5.33579 16.95 5.75L11.4071 11.2929C11.0166 11.6834 11.0166 12.3166 11.4071 12.7071L16.95 18.25C17.3642 18.6642 18.0358 18.6642 18.45 18.25C18.8642 17.8358 18.8642 17.1642 18.45 16.75L14.2657 12.5657C13.9533 12.2533 13.9533 11.7467 14.2657 11.4343Z"
                    fill="black" />
                <path
                    d="M8.2657 11.4343L12.45 7.25C12.8642 6.83579 12.8642 6.16421 12.45 5.75C12.0358 5.33579 11.3642 5.33579 10.95 5.75L5.40712 11.2929C5.01659 11.6834 5.01659 12.3166 5.40712 12.7071L10.95 18.25C11.3642 18.6642 12.0358 18.6642 12.45 18.25C12.8642 17.8358 12.8642 17.1642 12.45 16.75L8.2657 12.5657C7.95328 12.2533 7.95328 11.7467 8.2657 11.4343Z"
                    fill="black" />
            </svg>
        </span>
        <!--end::Svg Icon-->
    </div>
    <!--end::Aside toggler-->
</div>
<!--end::Brand-->
<!--begin::Aside menu-->
<div class="aside-menu flex-column-fluid">
    <!--begin::Aside Menu-->
    <div class="hover-scroll-overlay-y my-5 my-lg-5" id="kt_aside_menu_wrapper" data-kt-scroll="true"
        data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
        data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer" data-kt-scroll-wrappers="#kt_aside_menu"
        data-kt-scroll-offset="0">
        <!--begin::Menu-->
        <div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500"
            id="#kt_aside_menu" data-kt-menu="true">



            <div data-kt-menu-trigger="click"
                class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.emps.index' || Route::currentRouteName() == 'dashboard.emps.views') hover show fs-6 fw-bold @endif">
                <span class="menu-link">
                    <span class="menu-icon">
                        <span class="svg-icon svg-icon-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <path opacity="0.3"
                                    d="M21.25 18.525L13.05 21.825C12.35 22.125 11.65 22.125 10.95 21.825L2.75 18.525C1.75 18.125 1.75 16.725 2.75 16.325L4.04999 15.825L10.25 18.325C10.85 18.525 11.45 18.625 12.05 18.625C12.65 18.625 13.25 18.525 13.85 18.325L20.05 15.825L21.35 16.325C22.35 16.725 22.35 18.125 21.25 18.525ZM13.05 16.425L21.25 13.125C22.25 12.725 22.25 11.325 21.25 10.925L13.05 7.62502C12.35 7.32502 11.65 7.32502 10.95 7.62502L2.75 10.925C1.75 11.325 1.75 12.725 2.75 13.125L10.95 16.425C11.65 16.725 12.45 16.725 13.05 16.425Z"
                                    fill="black"></path>
                                <path
                                    d="M11.05 11.025L2.84998 7.725C1.84998 7.325 1.84998 5.925 2.84998 5.525L11.05 2.225C11.75 1.925 12.45 1.925 13.15 2.225L21.35 5.525C22.35 5.925 22.35 7.325 21.35 7.725L13.05 11.025C12.45 11.325 11.65 11.325 11.05 11.025Z"
                                    fill="black"></path>
                            </svg>
                        </span>
                    </span>
                    <span class="menu-title text-dark">الموظفين</span>
                    <span class="menu-arrow text-dark"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">

                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.emps.index') active @endif"
                            href="{{ route('dashboard.emps.index') }}" data-bs-toggle="tooltip" data-bs-trigger="hover"
                            data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إضافة الموظفين</span>
                        </a>
                    </div>

                </div>
                <div class="menu-sub menu-sub-accordion menu-active-bg">

                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.emps.views') active @endif"
                            href="{{ route('dashboard.emps.views') }}" data-bs-toggle="tooltip" data-bs-trigger="hover"
                            data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">ادارة الموظفين</span>
                        </a>
                    </div>

                </div>

            </div>






            <div data-kt-menu-trigger="click"
                class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.workers.index' || Route::currentRouteName() == 'dashboard.workers.views') hover show fs-6 fw-bold @endif">
                <span class="menu-link">
                    <span class="menu-icon">



<!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/kt-products/docs/metronic/html/releases/2023-07-21-074234/core/html/src/media/icons/duotune/communication/com014.svg-->
<span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M16.0173 9H15.3945C14.2833 9 13.263 9.61425 12.7431 10.5963L12.154 11.7091C12.0645 11.8781 12.1072 12.0868 12.2559 12.2071L12.6402 12.5183C13.2631 13.0225 13.7556 13.6691 14.0764 14.4035L14.2321 14.7601C14.2957 14.9058 14.4396 15 14.5987 15H18.6747C19.7297 15 20.4057 13.8774 19.912 12.945L18.6686 10.5963C18.1487 9.61425 17.1285 9 16.0173 9Z" fill="currentColor"/>
    <rect opacity="0.3" x="14" y="4" width="4" height="4" rx="2" fill="currentColor"/>
    <path d="M4.65486 14.8559C5.40389 13.1224 7.11161 12 9 12C10.8884 12 12.5961 13.1224 13.3451 14.8559L14.793 18.2067C15.3636 19.5271 14.3955 21 12.9571 21H5.04292C3.60453 21 2.63644 19.5271 3.20698 18.2067L4.65486 14.8559Z" fill="currentColor"/>
    <rect opacity="0.3" x="6" y="5" width="6" height="6" rx="3" fill="currentColor"/>
    </svg>
    </span>
    <!--end::Svg Icon-->






                    </span>
                    <span class="menu-title text-dark">العمال</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">

                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.workers.index') active @endif"
                            href="{{ route('dashboard.workers.index') }}" data-bs-toggle="tooltip"
                            data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إضافة عامل</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.workers.views') active @endif"
                            href="{{ route('dashboard.workers.views') }}" data-bs-toggle="tooltip"
                            data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إدارة العمال</span>
                        </a>
                    </div>

                </div>
            </div>








            <div data-kt-menu-trigger="click"
                class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.accountings.index' || Route::currentRouteName() == 'dashboard.accountings.views') hover show fs-6 fw-bold @endif">
                <span class="menu-link">
                    <span class="menu-icon">
                        <span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.3" d="M3 3V17H7V21H15V9H20V3H3Z" fill="currentColor"/>
                            <path d="M20 22H3C2.4 22 2 21.6 2 21V3C2 2.4 2.4 2 3 2H20C20.6 2 21 2.4 21 3V21C21 21.6 20.6 22 20 22ZM19 4H4V8H19V4ZM6 18H4V20H6V18ZM6 14H4V16H6V14ZM6 10H4V12H6V10ZM10 18H8V20H10V18ZM10 14H8V16H10V14ZM10 10H8V12H10V10ZM14 18H12V20H14V18ZM14 14H12V16H14V14ZM14 10H12V12H14V10ZM19 14H17V20H19V14ZM19 10H17V12H19V10Z" fill="currentColor"/>
                            </svg>
                            </span>
                                            </span>
                    <span class="menu-title text-dark">حسابات العمال</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">

                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.accountings.index') active @endif"
                            href="{{ route('dashboard.accountings.index') }}" data-bs-toggle="tooltip"
                            data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إضافة حسابات العمال</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.accountings.views') active @endif"
                            href="{{ route('dashboard.accountings.views') }}" data-bs-toggle="tooltip"
                            data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إدارة حسابات العمال</span>
                        </a>
                    </div>

                </div>
            </div>




            <div data-kt-menu-trigger="click"
            class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.accountings.create' || Route::currentRouteName() == 'dashboard.accountings.viewpmonth') hover show fs-6 fw-bold @endif">
            <span class="menu-link">
                <span class="menu-icon">

                    <span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.3" d="M20 18H4C3.4 18 3 17.6 3 17V7C3 6.4 3.4 6 4 6H20C20.6 6 21 6.4 21 7V17C21 17.6 20.6 18 20 18ZM12 8C10.3 8 9 9.8 9 12C9 14.2 10.3 16 12 16C13.7 16 15 14.2 15 12C15 9.8 13.7 8 12 8Z" fill="currentColor"/>
                        <path d="M18 6H20C20.6 6 21 6.4 21 7V9C19.3 9 18 7.7 18 6ZM6 6H4C3.4 6 3 6.4 3 7V9C4.7 9 6 7.7 6 6ZM21 17V15C19.3 15 18 16.3 18 18H20C20.6 18 21 17.6 21 17ZM3 15V17C3 17.6 3.4 18 4 18H6C6 16.3 4.7 15 3 15Z" fill="currentColor"/>
                        </svg>
                        </span>

                </span>
                <span class="menu-title text-dark">المدفوعات الشهرية</span>
                <span class="menu-arrow"></span>
            </span>
            <div class="menu-sub menu-sub-accordion menu-active-bg">

                <div class="menu-item">
                    <a class="menu-link @if (Route::currentRouteName() == 'dashboard.accountings.create') active @endif"
                        href="{{ route('dashboard.accountings.create') }}" data-bs-toggle="tooltip"
                        data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title text-dark">إضافة دفعة شهرية</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link @if (Route::currentRouteName() == 'dashboard.accountings.viewpmonth') active @endif"
                        href="{{ route('dashboard.accountings.viewpmonth') }}" data-bs-toggle="tooltip"
                        data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title text-dark">إدارة الدفعات الشهرية</span>
                    </a>
                </div>

            </div>
        </div>


        <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.shop.index' || Route::currentRouteName() == 'dashboard.shop.views') hover show fs-6 fw-bold @endif">
        <span class="menu-link">
            <span class="menu-icon">
                <span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.3" d="M18 10V20C18 20.6 18.4 21 19 21C19.6 21 20 20.6 20 20V10H18Z" fill="currentColor"/>
                    <path opacity="0.3" d="M11 10V17H6V10H4V20C4 20.6 4.4 21 5 21H12C12.6 21 13 20.6 13 20V10H11Z" fill="currentColor"/>
                    <path opacity="0.3" d="M10 10C10 11.1 9.1 12 8 12C6.9 12 6 11.1 6 10H10Z" fill="currentColor"/>
                    <path opacity="0.3" d="M18 10C18 11.1 17.1 12 16 12C14.9 12 14 11.1 14 10H18Z" fill="currentColor"/>
                    <path opacity="0.3" d="M14 4H10V10H14V4Z" fill="currentColor"/>
                    <path opacity="0.3" d="M17 4H20L22 10H18L17 4Z" fill="currentColor"/>
                    <path opacity="0.3" d="M7 4H4L2 10H6L7 4Z" fill="currentColor"/>
                    <path d="M6 10C6 11.1 5.1 12 4 12C2.9 12 2 11.1 2 10H6ZM10 10C10 11.1 10.9 12 12 12C13.1 12 14 11.1 14 10H10ZM18 10C18 11.1 18.9 12 20 12C21.1 12 22 11.1 22 10H18ZM19 2H5C4.4 2 4 2.4 4 3V4H20V3C20 2.4 19.6 2 19 2ZM12 17C12 16.4 11.6 16 11 16H6C5.4 16 5 16.4 5 17C5 17.6 5.4 18 6 18H11C11.6 18 12 17.6 12 17Z" fill="currentColor"/>
                    </svg>
                    </span>
            </span>
            <span class="menu-title text-dark">ادارة المحلات</span>
            <span class="menu-arrow"></span>
        </span>
        <div class="menu-sub menu-sub-accordion menu-active-bg">

            <div class="menu-item">
                <a class="menu-link @if (Route::currentRouteName() == 'dashboard.shop.index') active @endif"
                    href="{{ route('dashboard.shop.index') }}" data-bs-toggle="tooltip"
                    data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot text-dark"></span>
                    </span>
                    <span class="menu-title text-dark">إضافة محل</span>
                </a>
            </div>

            <div class="menu-item">
                <a class="menu-link @if (Route::currentRouteName() == 'dashboard.shop.views') active @endif"
                    href="{{ route('dashboard.shop.views') }}" data-bs-toggle="tooltip"
                    data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title text-dark">إدارة المحلات</span>
                </a>
            </div>

        </div>
    </div>








    <div data-kt-menu-trigger="click"
    class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.calculate.index' || Route::currentRouteName() == 'dashboard.calculate.views') hover show fs-6 fw-bold @endif">
    <span class="menu-link">
        <span class="menu-icon">



<!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/kt-products/docs/metronic/html/releases/2023-07-21-074234/core/html/src/media/icons/duotune/finance/fin002.svg-->
<span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M22 7H2V11H22V7Z" fill="currentColor"/>
    <path opacity="0.3" d="M21 19H3C2.4 19 2 18.6 2 18V6C2 5.4 2.4 5 3 5H21C21.6 5 22 5.4 22 6V18C22 18.6 21.6 19 21 19ZM14 14C14 13.4 13.6 13 13 13H5C4.4 13 4 13.4 4 14C4 14.6 4.4 15 5 15H13C13.6 15 14 14.6 14 14ZM16 15.5C16 16.3 16.7 17 17.5 17H18.5C19.3 17 20 16.3 20 15.5C20 14.7 19.3 14 18.5 14H17.5C16.7 14 16 14.7 16 15.5Z" fill="currentColor"/>
    </svg>
    </span>
    <!--end::Svg Icon-->



                                </span>
        <span class="menu-title text-dark">حسابات المحلات</span>
        <span class="menu-arrow"></span>
    </span>
    <div class="menu-sub menu-sub-accordion menu-active-bg">

        <div class="menu-item">
            <a class="menu-link @if (Route::currentRouteName() == 'dashboard.calculate.index') active @endif"
                href="{{ route('dashboard.calculate.index') }}" data-bs-toggle="tooltip"
                data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title text-dark">إضافة حسابات محل</span>
            </a>
        </div>

        <div class="menu-item">
            <a class="menu-link @if (Route::currentRouteName() == 'dashboard.calculate.views') active @endif"
                href="{{ route('dashboard.calculate.views') }}" data-bs-toggle="tooltip"
                data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title text-dark">إدارة حسابات المحلات</span>
            </a>
        </div>

    </div>
</div>



















    <div data-kt-menu-trigger="click"
    class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.manager.index' || Route::currentRouteName() == 'dashboard.manager.views') hover show fs-6 fw-bold @endif">
    <span class="menu-link">
        <span class="menu-icon">




        <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/kt-products/docs/metronic/html/releases/2023-07-21-074234/core/html/src/media/icons/duotune/abstract/abs021.svg-->
        <span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M22 12C22 17.5 17.5 22 12 22C6.5 22 2 17.5 2 12C2 6.5 6.5 2 12 2C17.5 2 22 6.5 22 12ZM14.5 4.5C10.4 4.5 7 7.9 7 12C7 16.1 10.4 19.5 14.5 19.5C18.6 19.5 22 16.1 22 12C22 7.9 18.6 4.5 14.5 4.5Z" fill="currentColor"/>
            <path opacity="0.3" d="M22 12C22 16.1 18.6 19.5 14.5 19.5C10.4 19.5 7 16.1 7 12C7 7.9 10.4 4.5 14.5 4.5C18.6 4.5 22 7.9 22 12ZM12 7C9.2 7 7 9.2 7 12C7 14.8 9.2 17 12 17C14.8 17 17 14.8 17 12C17 9.2 14.8 7 12 7Z" fill="currentColor"/>
            </svg>
            </span> </span>
            <!--end::Svg Icon-->
                <span class="menu-title text-dark">ادارة المجموعات</span>
                <span class="menu-arrow"></span>
            </span>
            <div class="menu-sub menu-sub-accordion menu-active-bg">

                <div class="menu-item">
                    <a class="menu-link @if (Route::currentRouteName() == 'dashboard.manager.index') active @endif"
                        href="{{ route('dashboard.manager.index') }}" data-bs-toggle="tooltip"
                        data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot text-dark"></span>
                        </span>
                        <span class="menu-title text-dark">إضافة مجموعة</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link @if (Route::currentRouteName() == 'dashboard.manager.views') active @endif"
                        href="{{ route('dashboard.manager.views') }}" data-bs-toggle="tooltip"
                        data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title text-dark">إدارة المجموعات</span>
                    </a>
                </div>

            </div>
        </div>









        </div>
        <!--end::Menu-->
    </div>
    <!--end::Aside Menu-->
</div>
<!--end::Aside menu-->
<!--begin::Footer-->
<!--end::Footer-->
