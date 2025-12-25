<div class="aside-logo flex-column-auto" id="kt_aside_logo">
    <a href="{{ route('home') }}">
        <img alt="Logo" src="{{ asset('assets/media/logos/logo.jpg') }}" class="h-60px logo"/>
    </a>
    <div id="kt_aside_toggle" class="px-0 w-auto btn btn-icon btn-active-color-primary aside-toggle"
         data-kt-toggle="true"
         data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="aside-minimize">
               <span class="rotate-180 svg-icon svg-icon-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path opacity="0.5"
                      d="M14.2657 11.4343L18.45 7.25C18.8642 6.83579 18.8642 6.16421 18.45 5.75C18.0358 5.33579 17.3642 5.33579 16.95 5.75L11.4071 11.2929C11.0166 11.6834 11.0166 12.3166 11.4071 12.7071L16.95 18.25C17.3642 18.6642 18.0358 18.6642 18.45 18.25C18.8642 17.8358 18.8642 17.1642 18.45 16.75L14.2657 12.5657C13.9533 12.2533 13.9533 11.7467 14.2657 11.4343Z"
                      fill="black"/>
                <path
                    d="M8.2657 11.4343L12.45 7.25C12.8642 6.83579 12.8642 6.16421 12.45 5.75C12.0358 5.33579 11.3642 5.33579 10.95 5.75L5.40712 11.2929C5.01659 11.6834 5.01659 12.3166 5.40712 12.7071L10.95 18.25C11.3642 18.6642 12.0358 18.6642 12.45 18.25C12.8642 17.8358 12.8642 17.1642 12.45 16.75L8.2657 12.5657C7.95328 12.2533 7.95328 11.7467 8.2657 11.4343Z"
                    fill="black"/>
            </svg>
        </span>
    </div>
</div>
<div class="aside-menu flex-column-fluid">
    <div class="my-5 hover-scroll-overlay-y my-lg-5" id="kt_aside_menu_wrapper" data-kt-scroll="true"
         data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
         data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer" data-kt-scroll-wrappers="#kt_aside_menu"
         data-kt-scroll-offset="0">
        <div
            class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500"
            id="#kt_aside_menu" data-kt-menu="true">
            <?php       if(Perm::get_controll_access(1)){?>
            <div data-kt-menu-trigger="click"
                 class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.emps.index' || Route::currentRouteName() == 'dashboard.emps.views' || Route::currentRouteName() == 'dashboard.emps.add_role'  || Route::currentRouteName() == 'dashboard.emps.view_role') hover show fs-6 fw-bold @endif">
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
                    <span class="menu-title text-dark">المستخدمين و الصلاحيات</span>
                    <span class="menu-arrow text-dark"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">
                    <?php if(Perm::get_function_access(1)){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.emps.index') active @endif"
                           href="{{ route('dashboard.emps.index') }}" data-bs-toggle="tooltip" data-bs-trigger="hover"
                           data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إضافة مستخدم</span>
                        </a>
                    </div>
                    <?php  }?>
                    <?php if( Perm::get_function_access(2) || Perm::get_function_access(3) || Perm::get_function_access(4) || Perm::get_function_access(5)) {?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.emps.views') active @endif"
                           href="{{ route('dashboard.emps.views') }}" data-bs-toggle="tooltip" data-bs-trigger="hover"
                           data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">ادارة المستخدمين</span>
                        </a>
                    </div>
                    <?php  }?>
                    <?php if(Perm::get_function_access(6)){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.emps.add_role') active @endif"
                           href="{{ route('dashboard.emps.add_role') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover"
                           data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إضافة مجموعة</span>
                        </a>
                    </div>
                    <?php  }?>
                    <?php if(Perm::get_function_access(7) || Perm::get_function_access(8) || Perm::get_function_access(9)){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.emps.view_role') active @endif"
                           href="{{ route('dashboard.emps.view_role') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover"
                           data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">ادارة المجموعات</span>
                        </a>
                    </div>
                    <?php  }?>
                </div>
            </div>
            <?php  }?>
            <?php       if(Perm::get_controll_access(2)){?>
            <div data-kt-menu-trigger="click"
                 class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.workers.index' || Route::currentRouteName() == 'dashboard.workers.views' || Route::currentRouteName() == 'dashboard.workers.import') hover show fs-6 fw-bold @endif">
                <span class="menu-link">
                    <span class="menu-icon">
                       <span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M16.0173 9H15.3945C14.2833 9 13.263 9.61425 12.7431 10.5963L12.154 11.7091C12.0645 11.8781 12.1072 12.0868 12.2559 12.2071L12.6402 12.5183C13.2631 13.0225 13.7556 13.6691 14.0764 14.4035L14.2321 14.7601C14.2957 14.9058 14.4396 15 14.5987 15H18.6747C19.7297 15 20.4057 13.8774 19.912 12.945L18.6686 10.5963C18.1487 9.61425 17.1285 9 16.0173 9Z"
                                    fill="currentColor"/>
                                <rect opacity="0.3" x="14" y="4" width="4" height="4"
                                      rx="2" fill="currentColor"/>
                                <path
                                    d="M4.65486 14.8559C5.40389 13.1224 7.11161 12 9 12C10.8884 12 12.5961 13.1224 13.3451 14.8559L14.793 18.2067C15.3636 19.5271 14.3955 21 12.9571 21H5.04292C3.60453 21 2.63644 19.5271 3.20698 18.2067L4.65486 14.8559Z"
                                    fill="currentColor"/>
                                <rect opacity="0.3" x="6" y="5" width="6" height="6"
                                      rx="3" fill="currentColor"/>
                            </svg>
                        </span>
                    </span>
                    <span class="menu-title text-dark">العمال</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">
                    <?php if(Perm::get_function_access(10)){?>
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
                    <?php  }?>
                    <?php
                    if (
                    Perm::get_function_access(12) || Perm::get_function_access(13) || Perm::get_function_access(15) || Perm::get_function_access(14) ||
                    Perm::get_function_access(16) || Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19)) {?>
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
                    <?php  }?>
                    <?php if(Perm::get_function_access(72)){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.workers.import') active @endif"
                           href="{{ route('dashboard.workers.import') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">استيراد</span>
                        </a>
                    </div>
                    <?php  }?>
                </div>
            </div>
            <?php  }?>
            <?php       if(Perm::get_controll_access(3)){?>
            <div data-kt-menu-trigger="click"
                 class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.financial.index' ||
                        Route::currentRouteName() == 'dashboard.financial.views') hover show fs-6 fw-bold @endif">
                <span class="menu-link">
                    <span class="menu-icon">
                        <span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24"
                                                                             viewBox="0 0 24 24" fill="none"
                                                                             xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.3" d="M3 3V17H7V21H15V9H20V3H3Z" fill="currentColor"/>
                                <path
                                    d="M20 22H3C2.4 22 2 21.6 2 21V3C2 2.4 2.4 2 3 2H20C20.6 2 21 2.4 21 3V21C21 21.6 20.6 22 20 22ZM19 4H4V8H19V4ZM6 18H4V20H6V18ZM6 14H4V16H6V14ZM6 10H4V12H6V10ZM10 18H8V20H10V18ZM10 14H8V16H10V14ZM10 10H8V12H10V10ZM14 18H12V20H14V18ZM14 14H12V16H14V14ZM14 10H12V12H14V10ZM19 14H17V20H19V14ZM19 10H17V12H19V10Z"
                                    fill="currentColor"/>
                            </svg>
                        </span>
                    </span>
                    <span class="menu-title text-dark">مصاريف العمال </span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">
                    <?php if(Perm::get_function_access(20)){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.financial.index') active @endif"
                           href="{{ route('dashboard.financial.index') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إضافة مصروف عامل</span>
                        </a>
                    </div>
                    <?php  }?>
                    <?php
                    if (Perm::get_function_access(21) || Perm::get_function_access(22) || Perm::get_function_access(23) || Perm::get_function_access(24) || Perm::get_function_access(25)) {?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.financial.views') active @endif"
                           href="{{ route('dashboard.financial.views') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إدارة مصاريف العمال</span>
                        </a>
                    </div>
                    <?php  }?>
                </div>
            </div>
            <?php } ?>
            <?php       if(Perm::get_controll_access(4)){?>
            <div data-kt-menu-trigger="click"
                 class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.accountings.create' ||
                        Route::currentRouteName() == 'dashboard.accountings.viewpmonth') hover show fs-6 fw-bold @endif">
                <span class="menu-link">
                    <span class="menu-icon">
                        <span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24"
                                                                             viewBox="0 0 24 24" fill="none"
                                                                             xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.3"
                                      d="M20 18H4C3.4 18 3 17.6 3 17V7C3 6.4 3.4 6 4 6H20C20.6 6 21 6.4 21 7V17C21 17.6 20.6 18 20 18ZM12 8C10.3 8 9 9.8 9 12C9 14.2 10.3 16 12 16C13.7 16 15 14.2 15 12C15 9.8 13.7 8 12 8Z"
                                      fill="currentColor"/>
                                <path
                                    d="M18 6H20C20.6 6 21 6.4 21 7V9C19.3 9 18 7.7 18 6ZM6 6H4C3.4 6 3 6.4 3 7V9C4.7 9 6 7.7 6 6ZM21 17V15C19.3 15 18 16.3 18 18H20C20.6 18 21 17.6 21 17ZM3 15V17C3 17.6 3.4 18 4 18H6C6 16.3 4.7 15 3 15Z"
                                    fill="currentColor"/>
                            </svg>
                        </span>
                    </span>
                    <span class="menu-title text-dark">المدفوعات الشهرية</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">
                    <?php if(Perm::get_function_access(26)){?>
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
                    <?php } ?>
                    <?php
                    if (Perm::get_function_access(27) || Perm::get_function_access(28) || Perm::get_function_access(29)) {?>
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
                    <?php } ?>

                </div>
            </div>
            <?php } ?>
            <?php       if(Perm::get_controll_access(5)){?>
            <div data-kt-menu-trigger="click"
                 class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.shop.index' || Route::currentRouteName() == 'dashboard.shop.views') hover show fs-6 fw-bold @endif">
                <span class="menu-link">
                    <span class="menu-icon">
                        <span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24"
                                                                             viewBox="0 0 24 24" fill="none"
                                                                             xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.3" d="M18 10V20C18 20.6 18.4 21 19 21C19.6 21 20 20.6 20 20V10H18Z"
                                      fill="currentColor"/>
                                <path opacity="0.3"
                                      d="M11 10V17H6V10H4V20C4 20.6 4.4 21 5 21H12C12.6 21 13 20.6 13 20V10H11Z"
                                      fill="currentColor"/>
                                <path opacity="0.3" d="M10 10C10 11.1 9.1 12 8 12C6.9 12 6 11.1 6 10H10Z"
                                      fill="currentColor"/>
                                <path opacity="0.3" d="M18 10C18 11.1 17.1 12 16 12C14.9 12 14 11.1 14 10H18Z"
                                      fill="currentColor"/>
                                <path opacity="0.3" d="M14 4H10V10H14V4Z" fill="currentColor"/>
                                <path opacity="0.3" d="M17 4H20L22 10H18L17 4Z" fill="currentColor"/>
                                <path opacity="0.3" d="M7 4H4L2 10H6L7 4Z" fill="currentColor"/>
                                <path
                                    d="M6 10C6 11.1 5.1 12 4 12C2.9 12 2 11.1 2 10H6ZM10 10C10 11.1 10.9 12 12 12C13.1 12 14 11.1 14 10H10ZM18 10C18 11.1 18.9 12 20 12C21.1 12 22 11.1 22 10H18ZM19 2H5C4.4 2 4 2.4 4 3V4H20V3C20 2.4 19.6 2 19 2ZM12 17C12 16.4 11.6 16 11 16H6C5.4 16 5 16.4 5 17C5 17.6 5.4 18 6 18H11C11.6 18 12 17.6 12 17Z"
                                    fill="currentColor"/>
                            </svg>
                        </span>
                    </span>
                    <span class="menu-title text-dark">المحلات</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">
                    <?php if(Perm::get_function_access(30)){?>
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
                    <?php } ?>
                    <?php
                    if (Perm::get_function_access(31) || Perm::get_function_access(32) || Perm::get_function_access(33)
                    || Perm::get_function_access(34) || Perm::get_function_access(35)
                    || Perm::get_function_access(36) || Perm::get_function_access(37)
                    || Perm::get_function_access(38)) {?>
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
                    <?php } ?>

                </div>
            </div>
            <?php } ?>
            <?php       if(Perm::get_controll_access(6)){?>
            <div data-kt-menu-trigger="click"
                 class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.calculate.index' ||
                        Route::currentRouteName() == 'dashboard.calculate.views') hover show fs-6 fw-bold @endif">
                <span class="menu-link">
                    <span class="menu-icon">
                        <span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24"
                                                                             viewBox="0 0 24 24" fill="none"
                                                                             xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 7H2V11H22V7Z" fill="currentColor"/>
                                <path opacity="0.3"
                                      d="M21 19H3C2.4 19 2 18.6 2 18V6C2 5.4 2.4 5 3 5H21C21.6 5 22 5.4 22 6V18C22 18.6 21.6 19 21 19ZM14 14C14 13.4 13.6 13 13 13H5C4.4 13 4 13.4 4 14C4 14.6 4.4 15 5 15H13C13.6 15 14 14.6 14 14ZM16 15.5C16 16.3 16.7 17 17.5 17H18.5C19.3 17 20 16.3 20 15.5C20 14.7 19.3 14 18.5 14H17.5C16.7 14 16 14.7 16 15.5Z"
                                      fill="currentColor"/>
                            </svg>
                        </span>
                    </span>
                    <span class="menu-title text-dark">مصاريف المحلات</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">
                    <?php if(Perm::get_function_access(39)){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.calculate.index') active @endif"
                           href="{{ route('dashboard.calculate.index') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إضافة مصروف محل</span>
                        </a>
                    </div>
                    <?php } ?>
                    <?php
                    if (Perm::get_function_access(40) || Perm::get_function_access(41) || Perm::get_function_access(42)
                    || Perm::get_function_access(43) || Perm::get_function_access(44)) {?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.calculate.views') active @endif"
                           href="{{ route('dashboard.calculate.views') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إدارة مصاريف المحلات</span>
                        </a>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <?php } ?>
			 <?php       if(Perm::get_controll_access(12)){?>
            <div data-kt-menu-trigger="click"
                 class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.violation.index' ||
                        Route::currentRouteName() == 'dashboard.violation.views') hover show fs-6 fw-bold @endif">
                <span class="menu-link">
                    <span class="menu-icon">
<span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="currentColor"/>
    <rect x="9" y="13.0283" width="7.3536" height="1.2256" rx="0.6128" transform="rotate(-45 9 13.0283)" fill="currentColor"/>
    <rect x="9.86664" y="7.93359" width="7.3536" height="1.2256" rx="0.6128" transform="rotate(45 9.86664 7.93359)" fill="currentColor"/>
    </svg>
    </span>
                    </span>
                    <span class="menu-title text-dark">مخالفات المحلات</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">
                    <?php if(Perm::get_function_access(76)){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.violation.index') active @endif"
                           href="{{ route('dashboard.violation.index') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إضافة مخالفة محل</span>
                        </a>
                    </div>
                    <?php } ?>
                    <?php
                    if (Perm::get_function_access(77) || Perm::get_function_access(78) || Perm::get_function_access(79)
                 ) {?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.violation.views') active @endif"
                           href="{{ route('dashboard.violation.views') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إدارة مخالفات المحلات</span>
                        </a>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>



            {{-- المركبات --}}
			 <?php       if(      (Perm::get_function_access(86) || Perm::get_function_access(84) || Perm::get_function_access(87)
             )){?>
                <div data-kt-menu-trigger="click"
                     class="menu-item menu-accordion @if (Route::currentRouteName() == 'vehicles.index' ||
                     Route::currentRouteName() == 'insert_vehicle' || Route::currentRouteName() == 'vehicles.edit') hover show fs-6 fw-bold @endif">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class='fas fa-car' style='font-size:22px'></i>

                        </span>
                        <span class="menu-title text-dark">المركبات </span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <?php if(Perm::get_function_access(85)){?>
                        <div class="menu-item">
                            <a class="menu-link @if (Route::currentRouteName() == 'insert_vehicle' || Route::currentRouteName() == 'vehicles.edit') active @endif"
                               href="{{ route('insert_vehicle') }}" data-bs-toggle="tooltip"
                               data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title text-dark">إضافة  مركبة</span>
                            </a>
                        </div>
                        <?php } ?>
                        <?php
                        if (Perm::get_function_access(86) || Perm::get_function_access(84) || Perm::get_function_access(87)
                     ) {?>
                        <div class="menu-item">
                            <a class="menu-link @if (Route::currentRouteName() == 'vehicles.index') active @endif"
                               href="{{ route('vehicles.index') }}" data-bs-toggle="tooltip"
                               data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title text-dark">إدارة المركبات </span>
                            </a>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>



            <?php       if(Perm::get_controll_access(7)){?>
            <div data-kt-menu-trigger="click"
                 class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.manager.index' || Route::currentRouteName() == 'dashboard.manager.views') hover show fs-6 fw-bold @endif">
                <span class="menu-link">
                    <span class="menu-icon">
                        <span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24"
                                                                             viewBox="0 0 24 24" fill="none"
                                                                             xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M22 12C22 17.5 17.5 22 12 22C6.5 22 2 17.5 2 12C2 6.5 6.5 2 12 2C17.5 2 22 6.5 22 12ZM14.5 4.5C10.4 4.5 7 7.9 7 12C7 16.1 10.4 19.5 14.5 19.5C18.6 19.5 22 16.1 22 12C22 7.9 18.6 4.5 14.5 4.5Z"
                                    fill="currentColor"/>
                                <path opacity="0.3"
                                      d="M22 12C22 16.1 18.6 19.5 14.5 19.5C10.4 19.5 7 16.1 7 12C7 7.9 10.4 4.5 14.5 4.5C18.6 4.5 22 7.9 22 12ZM12 7C9.2 7 7 9.2 7 12C7 14.8 9.2 17 12 17C14.8 17 17 14.8 17 12C17 9.2 14.8 7 12 7Z"
                                      fill="currentColor"/>
                            </svg>
                        </span> </span>
                    <span class="menu-title text-dark">المجموعات</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">
                    <?php if(Perm::get_function_access(45)){?>
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
                    <?php } ?>
                    <?php if(Perm::get_function_access(46) || Perm::get_function_access(47) || Perm::get_function_access(48)){?>
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
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
            <?php       if(Perm::get_controll_access(8)){?>
            <div data-kt-menu-trigger="click"
                 class="menu-item menu-accordion
                 @if (Route::currentRouteName() == 'dashboard.moraslat.index' ||
                  Route::currentRouteName() == 'dashboard.moraslat.views') hover show fs-6 fw-bold @endif">
                <span class="menu-link">
                    <span class="menu-icon">
                            <span class="svg-icon svg-icon-2 svg-icon-dark"><svg xmlns="http://www.w3.org/2000/svg"
                                                                                 xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                                 width="24px" height="24px"
                                                                                 viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"/>
                                    <path
                                        d="M8,3 L8,3.5 C8,4.32842712 8.67157288,5 9.5,5 L14.5,5 C15.3284271,5 16,4.32842712 16,3.5 L16,3 L18,3 C19.1045695,3 20,3.8954305 20,5 L20,21 C20,22.1045695 19.1045695,23 18,23 L6,23 C4.8954305,23 4,22.1045695 4,21 L4,5 C4,3.8954305 4.8954305,3 6,3 L8,3 Z"
                                        fill="#000000" opacity="0.3"/>
                                    <path
                                        d="M11,2 C11,1.44771525 11.4477153,1 12,1 C12.5522847,1 13,1.44771525 13,2 L14.5,2 C14.7761424,2 15,2.22385763 15,2.5 L15,3.5 C15,3.77614237 14.7761424,4 14.5,4 L9.5,4 C9.22385763,4 9,3.77614237 9,3.5 L9,2.5 C9,2.22385763 9.22385763,2 9.5,2 L11,2 Z"
                                        fill="#000000"/>
                                    <rect fill="#000000" opacity="0.3" x="10" y="9" width="7" height="2" rx="1"/>
                                    <rect fill="#000000" opacity="0.3" x="7" y="9" width="2" height="2" rx="1"/>
                                    <rect fill="#000000" opacity="0.3" x="7" y="13" width="2" height="2" rx="1"/>
                                    <rect fill="#000000" opacity="0.3" x="10" y="13" width="7" height="2" rx="1"/>
                                    <rect fill="#000000" opacity="0.3" x="7" y="17" width="2" height="2" rx="1"/>
                                    <rect fill="#000000" opacity="0.3" x="10" y="17" width="7" height="2" rx="1"/>
                                </g>
                            </svg></span>
                    </span>
                    <span class="menu-title text-dark">المعاملات</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">
                    <?php if(Perm::get_function_access(49) ){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.moraslat.index') active @endif"
                           href="{{ route('dashboard.moraslat.index') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot text-dark"></span>
                            </span>
                            <span class="menu-title text-dark">إضافة معاملة</span>
                        </a>
                    </div>
                    <?php } ?>
                    <?php if(Perm::get_function_access(50) || Perm::get_function_access(51) || Perm::get_function_access(52) || Perm::get_function_access(53) || Perm::get_function_access(54)){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.moraslat.views') active @endif"
                           href="{{ route('dashboard.moraslat.views') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إدارة المعاملات الصادرة</span>
                        </a>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
            <?php       if(Perm::get_controll_access(9)){?>
            <div data-kt-menu-trigger="click"
                 class="menu-item menu-accordion" >
                <span class="menu-link">
                    <span class="menu-icon">
                        <span class="svg-icon svg-icon-2 svg-icon-dark"><svg xmlns="http://www.w3.org/2000/svg"
                                                                             xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                             width="24px" height="24px"
                                                                             viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"/>
                                <rect fill="#000000" opacity="0.3" x="11.5" y="2" width="2" height="4" rx="1"/>
                                <rect fill="#000000" opacity="0.3" x="11.5" y="16" width="2" height="5" rx="1"/>
                                <path
                                    d="M15.493,8.044 C15.2143319,7.68933156 14.8501689,7.40750104 14.4005,7.1985 C13.9508311,6.98949895 13.5170021,6.885 13.099,6.885 C12.8836656,6.885 12.6651678,6.90399981 12.4435,6.942 C12.2218322,6.98000019 12.0223342,7.05283279 11.845,7.1605 C11.6676658,7.2681672 11.5188339,7.40749914 11.3985,7.5785 C11.2781661,7.74950085 11.218,7.96799867 11.218,8.234 C11.218,8.46200114 11.2654995,8.65199924 11.3605,8.804 C11.4555005,8.95600076 11.5948324,9.08899943 11.7785,9.203 C11.9621676,9.31700057 12.1806654,9.42149952 12.434,9.5165 C12.6873346,9.61150047 12.9723317,9.70966616 13.289,9.811 C13.7450023,9.96300076 14.2199975,10.1308324 14.714,10.3145 C15.2080025,10.4981676 15.6576646,10.7419985 16.063,11.046 C16.4683354,11.3500015 16.8039987,11.7268311 17.07,12.1765 C17.3360013,12.6261689 17.469,13.1866633 17.469,13.858 C17.469,14.6306705 17.3265014,15.2988305 17.0415,15.8625 C16.7564986,16.4261695 16.3733357,16.8916648 15.892,17.259 C15.4106643,17.6263352 14.8596698,17.8986658 14.239,18.076 C13.6183302,18.2533342 12.97867,18.342 12.32,18.342 C11.3573285,18.342 10.4263378,18.1741683 9.527,17.8385 C8.62766217,17.5028317 7.88033631,17.0246698 7.285,16.404 L9.413,14.238 C9.74233498,14.6433354 10.176164,14.9821653 10.7145,15.2545 C11.252836,15.5268347 11.7879973,15.663 12.32,15.663 C12.5606679,15.663 12.7949989,15.6376669 13.023,15.587 C13.2510011,15.5363331 13.4504991,15.4540006 13.6215,15.34 C13.7925009,15.2259994 13.9286662,15.0740009 14.03,14.884 C14.1313338,14.693999 14.182,14.4660013 14.182,14.2 C14.182,13.9466654 14.1186673,13.7313342 13.992,13.554 C13.8653327,13.3766658 13.6848345,13.2151674 13.4505,13.0695 C13.2161655,12.9238326 12.9248351,12.7908339 12.5765,12.6705 C12.2281649,12.5501661 11.8323355,12.420334 11.389,12.281 C10.9583312,12.141666 10.5371687,11.9770009 10.1255,11.787 C9.71383127,11.596999 9.34650161,11.3531682 9.0235,11.0555 C8.70049838,10.7578318 8.44083431,10.3968355 8.2445,9.9725 C8.04816568,9.54816454 7.95,9.03200304 7.95,8.424 C7.95,7.67666293 8.10199848,7.03700266 8.406,6.505 C8.71000152,5.97299734 9.10899753,5.53600171 9.603,5.194 C10.0970025,4.85199829 10.6543302,4.60183412 11.275,4.4435 C11.8956698,4.28516587 12.5226635,4.206 13.156,4.206 C13.9160038,4.206 14.6918294,4.34533194 15.4835,4.624 C16.2751706,4.90266806 16.9686637,5.31433061 17.564,5.859 L15.493,8.044 Z"
                                    fill="#000000"/>
                            </g>
                        </svg></span>
                    </span>
                    <span class="menu-title">المصاريف</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg" style="display: none; overflow: hidden;"
                     kt-hidden-height="312">
                    <?php if(Perm::get_function_access(55) || Perm::get_function_access(56) || Perm::get_function_access(57) || Perm::get_function_access(58)){?>
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">مصاريف شراء</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion menu-active-bg">
                            <?php if(Perm::get_function_access(55) ){?>

                            <div class="menu-item">
                                <a class="menu-link" href="{{ route('dashboard.purchase.index') }}?manager=on">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">إضافة مصاريف شراء</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="{{ route('dashboard.purchase.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">  إضافة مصاريف شراء محلات</span>
                                </a>
                            </div>
                            <?php } ?>
                            <?php if( Perm::get_function_access(56) || Perm::get_function_access(57) || Perm::get_function_access(58)){?>
                            <div class="menu-item">
                                <a class="menu-link" href="{{ route('dashboard.purchase.views') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">ادارة مصاريف شراء</span>
                                </a>
                            </div>

                            <div class="menu-item">
                                <a class="menu-link" href="{{ route('dashboard.purchase.views') }}?shops=on">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">ادارة مصاريف شراء محلات </span>
                                </a>
                            </div>

                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if(Perm::get_function_access(59) || Perm::get_function_access(60) || Perm::get_function_access(61) || Perm::get_function_access(62)){?>
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">مصاريف تشغيلية </span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion menu-active-bg">
                            <?php if(Perm::get_function_access(59) ){?>
                            <div class="menu-item">
                                <a class="menu-link" href="{{ route('dashboard.expense.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">إضافة مصاريف تشغيلية</span>
                                </a>
                            </div>
                            <?php } ?>
                            <?php if(Perm::get_function_access(60) || Perm::get_function_access(61) || Perm::get_function_access(62)){?>
                            <div class="menu-item">
                                <a class="menu-link" href="{{ route('dashboard.expense.views') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">ادارة مصاريف تشغيلية</span>
                                </a>
                            </div>
                            <?php } ?>

                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
            <?php       if(Perm::get_controll_access(10)){?>
            <div data-kt-menu-trigger="click"
                 class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.vacation.index' ||
              Route::currentRouteName() == 'dashboard.vacation.views' ||
              Route::currentRouteName() == 'dashboard.vacation.views_all') hover show fs-6 fw-bold @endif">
      <span class="menu-link">
          <span class="menu-icon">
              <span class="svg-icon svg-icon-2 svg-icon-dark"><svg width="24" height="24" viewBox="0 0 24 24"
                                                                   fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path opacity="0.3"
                        d="M20.9 12.9C20.3 12.9 19.9 12.5 19.9 11.9C19.9 11.3 20.3 10.9 20.9 10.9H21.8C21.3 6.2 17.6 2.4 12.9 2V2.9C12.9 3.5 12.5 3.9 11.9 3.9C11.3 3.9 10.9 3.5 10.9 2.9V2C6.19999 2.5 2.4 6.2 2 10.9H2.89999C3.49999 10.9 3.89999 11.3 3.89999 11.9C3.89999 12.5 3.49999 12.9 2.89999 12.9H2C2.5 17.6 6.19999 21.4 10.9 21.8V20.9C10.9 20.3 11.3 19.9 11.9 19.9C12.5 19.9 12.9 20.3 12.9 20.9V21.8C17.6 21.3 21.4 17.6 21.8 12.9H20.9Z"
                        fill="currentColor"/>
                  <path
                      d="M16.9 10.9H13.6C13.4 10.6 13.2 10.4 12.9 10.2V5.90002C12.9 5.30002 12.5 4.90002 11.9 4.90002C11.3 4.90002 10.9 5.30002 10.9 5.90002V10.2C10.6 10.4 10.4 10.6 10.2 10.9H9.89999C9.29999 10.9 8.89999 11.3 8.89999 11.9C8.89999 12.5 9.29999 12.9 9.89999 12.9H10.2C10.4 13.2 10.6 13.4 10.9 13.6V13.9C10.9 14.5 11.3 14.9 11.9 14.9C12.5 14.9 12.9 14.5 12.9 13.9V13.6C13.2 13.4 13.4 13.2 13.6 12.9H16.9C17.5 12.9 17.9 12.5 17.9 11.9C17.9 11.3 17.5 10.9 16.9 10.9Z"
                      fill="currentColor"/>
                  </svg>
                  </span>
          </span>
          <span class="menu-title text-dark">الاجازات </span>
          <span class="menu-arrow"></span>
      </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">
                    <?php if(Perm::get_function_access(63) ){?>

                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.vacation.index') active @endif"
                           href="{{ route('dashboard.vacation.index') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                  <span class="menu-bullet">
                      <span class="bullet bullet-dot"></span>
                  </span>
                            <span class="menu-title text-dark">اضافة اجازة</span>
                        </a>
                    </div>
                    <?php } ?>
                    <?php if(Perm::get_function_access(64) || Perm::get_function_access(65) || Perm::get_function_access(66) || Perm::get_function_access(67) ){?>

                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.vacation.views') active @endif"
                           href="{{ route('dashboard.vacation.views') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                  <span class="menu-bullet">
                      <span class="bullet bullet-dot"></span>
                  </span>
                            <span class="menu-title text-dark">ادارة الاجازات</span>
                        </a>
                    </div>
                    <?php } ?>
                    <?php if(Perm::get_function_access(68) ){?>

                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.vacation.views_all') active @endif"
                           href="{{ route('dashboard.vacation.views_all') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                  <span class="menu-bullet">
                      <span class="bullet bullet-dot"></span>
                  </span>
                            <span class="menu-title text-dark">عرض الاجازات</span>
                        </a>
                    </div>
                    <?php } ?>

                </div>
            </div>
            <?php } ?>
            <?php       if(Perm::get_controll_access(11)){?>
            <div data-kt-menu-trigger="click"
                 class="menu-item menu-accordion @if (Route::currentRouteName() == 'dashboard.constant.workplace' ||Route::currentRouteName() == 'dashboard.constant.expensecategoty' ||
                        Route::currentRouteName() == 'dashboard.constant.job') hover show fs-6 fw-bold @endif">
                <span class="menu-link">
                    <span class="menu-icon">
<span class="svg-icon svg-icon-2 svg-icon-dark"><svg xmlns="http://www.w3.org/2000/svg"
                                                     xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                                     height="24px" viewBox="0 0 24 24" version="1.1">
    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <rect x="0" y="0" width="24" height="24"/>
        <path
            d="M5,8.6862915 L5,5 L8.6862915,5 L11.5857864,2.10050506 L14.4852814,5 L19,5 L19,9.51471863 L21.4852814,12 L19,14.4852814 L19,19 L14.4852814,19 L11.5857864,21.8994949 L8.6862915,19 L5,19 L5,15.3137085 L1.6862915,12 L5,8.6862915 Z M12,15 C13.6568542,15 15,13.6568542 15,12 C15,10.3431458 13.6568542,9 12,9 C10.3431458,9 9,10.3431458 9,12 C9,13.6568542 10.3431458,15 12,15 Z"
            fill="#000000"/>
    </g>
</svg></span>
                    </span>
                    <span class="menu-title text-dark">ثوابت النظام</span>
                    <span class="menu-arrow"></span>
                </span>



                    {{-- <a onclick="window.location.href='{{ route('tasks.index') }}'" class="menu-link @if (Route::currentRouteName() == 'tasks.index') t-active @endif"
                       href="{{ route('tasks.index') }}" data-bs-toggle="tooltip"
                     data-bs-dismiss="click" data-bs-placement="right">
                        <span class="menu-bullet">
                            <svg fill="#000000" height="23px" width="23px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
	 viewBox="0 0 220 220" xml:space="preserve">
<path d="M211.5,15.022C211.5,6.726,204.774,0,196.478,0H23.522C15.226,0,8.5,6.726,8.5,15.022v189.955
	C8.5,213.274,15.226,220,23.522,220h172.955c8.297,0,15.022-6.726,15.022-15.022V15.022z M88.5,199h-49v-45h49V199z M88.5,132h-49
	V88h49V132z M88.5,66h-49V21h49V66z M144.803,199.63l-26.306-26.306l11.205-11.205l15.101,15.102l23.65-23.65l11.205,11.205
	L144.803,199.63z M144.803,133.03l-26.306-26.306l11.205-11.205l15.101,15.101l23.65-23.65l11.205,11.205L144.803,133.03z
	 M144.803,66.429l-26.306-26.306l11.205-11.205l15.101,15.101l23.65-23.65l11.205,11.205L144.803,66.429z"/>
</svg>
                        </span>
                        <span class="menu-title text-dark">إدارة المهام</span>
                    </a> --}}

                <div class="menu-sub menu-sub-accordion menu-active-bg">
                    <?php if(Perm::get_function_access(69) ){?>

                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.constant.workplace') active @endif"
                           href="{{ route('dashboard.constant.workplace') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">مكان العمل</span>
                        </a>
                    </div>
                    <?php } ?>
                             <?php if(Perm::get_function_access(83) ){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.constant.city') active @endif"
                           href="{{ route('dashboard.constant.city') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">المدن</span>
                        </a>
                    </div>
                    <?php } ?>


                    <?php if(Perm::get_function_access(80) ){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.constant.job') active @endif"
                           href="{{ route('dashboard.constant.job') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">المهن</span>
                        </a>
                    </div>
                    <?php } ?>

                    <?php if(Perm::get_function_access(81) ){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.constant.violation_side') active @endif"
                           href="{{ route('dashboard.constant.violation') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">جهات المخالفة</span>
                        </a>
                    </div>
                    <?php } ?>

                    <?php if(Perm::get_function_access(82) ){?>
                    <div class="menu-item">
                        <a class="menu-link @if (Route::currentRouteName() == 'dashboard.constant.expensecategoty') active @endif"
                           href="{{ route('dashboard.constant.expensecategoty') }}" data-bs-toggle="tooltip"
                           data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">التصنيف الفواتير</span>
                        </a>
                    </div>
                    <?php } ?>

                </div>
            </div>
            <?php } ?>
            <?php if(Perm::get_controll_access(14)){ ?>
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="bi bi-calendar3"></i>
                    </span>
                    <span class="menu-title text-dark">الجداول والمهام</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion menu-active-bg">
                    <?php if(Perm::get_function_access(88)){ ?>
                    <div class="menu-item">
                        <a class="menu-link @if(Route::currentRouteName() == 'tasks.index') active @endif"
                           href="{{ route('tasks.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title text-dark">إدارة الجداول</span>
                        </a>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<style>
    .t-active{
        background-color: #007bff;
        color: #fff !important;
    }
    .t-active .menu-title{
        color: #fff !important;
    }
</style>
