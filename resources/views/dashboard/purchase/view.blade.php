@extends('layouts.app')
@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري')
@section('title', "$page_title")
@section('content')
    <div class="pur-log">

    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif
    <div id="user_reg" class="alert alert-danger d-none">
    </div>
    <form class="kt-form kt-form--label-right" enctype="multipart/form-data" id="boew_project" name="boew_project"
        accept-charset="utf-8" method="post" action="{{ route('dashboard.purchase.tbl') }}" enctype="multipart/form-data">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="fw-bold mb-0">بحث في سجل المشتريات</h3>
                        </div>
                    </div>
                    <div class="card-body px-1">
                        <div class="mb-0">
                            <div class="row gx-5 mb-5">



                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="purchase_no_v" class="form-label required fs-6 fw-bold text-dark mb-3">رقم
                                        الفاتورة</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                            type="text" name="purchase_no_v" id="purchase_no_v"
                                            class="form-control fw-bold text-dark text-info "
                                            data-inputmask="'alias' : 'decimal'" minlenght="1" maxlength="20"
                                            placeholder="رقم الفاتورة">
                                    </div>
                                </div>




                                <div class=" col-12 col-lg-3 col-md-12 col-sm-12  mb-5">

                                    <label for="purchase_dt_from" class="  form-label fs-6 fw-bold text-dark mb-3">تاريخ
                                        الفاتورة</label>
                                    <div class="input-daterange input-group" id="kt_datepicker">
                                        <input type="text" class="form-control input_date_ flatpickr-input"
                                            name="purchase_dt_from" id="purchase_dt_from" placeholder="من"
                                            data-col-index="5" readonly="readonly">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i
                                                    class="fas fa-align-justify fa-fw text-dark"></i></span>
                                        </div>
                                        <input type="text" class="form-control input_date_ flatpickr-input"
                                            name="purchase_dt_to" id="purchase_dt_to" placeholder="إلى" data-col-index="5"
                                            readonly="readonly">
                                    </div>
                                </div>






                                <div class=" col-12 col-lg-4 col-md-12 col-sm-12 mb-5"><label for="purchase_respon"
                                        class="form-label  fs-6 fw-bold text-dark mb-3">اسم المورد</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-user-tie fa-fw text-dark"></i></span></div><input
                                            type="text" name="purchase_respon_v" id="purchase_respon_v"
                                            class="form-control fw-bold  text-dark" placeholder="اسم المورد "
                                            autocomplete="off">
                                    </div>
                                </div>

                                <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                    <label for="manager_id_v" class="form-label   fs-6 fw-bold text-dark mb-3">قائد
                                        المحل</label>
                                    <div>
                                        <input type="text" id="shops" hidden value="{{ isset($_GET['shops']) ? 'on' : 'off'}}">
                                        <select class="form-select fw-bold  " data-control="select2" id="manager_id_v"
                                            name="manager_id_v" dir="rtl">
                                            <option value="">الكل</option>
                                            @foreach ($manager as $x)
                                                <option value="{{ $x->manager_id }} ">{{ $x->manager_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                    <label for="manager_id_v" class="form-label   fs-6 fw-bold text-dark mb-3">
                                    مُدخل الفاتورة </label>
                                    <div>
                                        <select class="form-select fw-bold  " data-control="select2" id="create_users"
                                            name="create_users" dir="rtl">
                                            <option value="">الكل</option>
                                            @foreach ($create_users as $x)
                                                @if((auth()->user()->isAdmin) or (auth()->user()->id == $x->id) )
                                                <option value="{{$x->id}}">{{$x->name}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                    <label for="manager_id" class="form-label   fs-6 fw-bold text-dark mb-3">
                                        المحل</label>
                                    <div>
                                        <select class="form-select fw-bold  " data-control="select2" id="shop_id"
                                            name="shop_id" dir="rtl" data-placeholder=" المحل">
                                            <option value="">اختر ..</option>
                                            @foreach ($shops as $x)
                                                <option value="{{ $x->shop_id }} ">{{ $x->shop_name.($x->municip->municip_no??"")}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="col-12 col-lg-3 col-md-12 col-sm-12   mb-2 "
                                    style="padding-top: 2rem !important;">
                                    <a onclick="view_all_purchase()" class="btn btn-primary btn-primary--icon"
                                        id="kt_search">
                                        <span>
                                            <i class="la la-search"></i>
                                            <span>بحث</span>
                                        </span>
                                    </a>
                                    <button type="button" class="btn btn-dark" data-kt-menu-trigger="click"
                                        data-kt-menu-placement="bottom-start"><i class="fas fa-print fa-fw"></i>
                                        تقارير
                                        <span class="svg-icon svg-icon-5 rotate-180 ms-3 me-0"><i
                                                class="fas fa-angle-down fa-fw"></i></span>
                                    </button>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-150px py-4"
                                        data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a data-url='{{ route('dashboard.report.print_purchase_pdf') }}'
                                                onclick="print_purchase_pdf('')"
                                                class="menu-link px-3 text-dark print_purchase_pdf "><span><i
                                                        class="far fa-file-pdf fa-fw text-danger"></i></span> طباعة PDF
                                            </a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a data-url='{{ route('dashboard.report.print_purchase_xlsx') }}'
                                                onclick="print_purchase_xlsx('')"
                                                class="menu-link px-3 text-dark print_purchase_xlsx"><span><i
                                                        class="fas fa-file-excel fa-fw text-success"></i></span>طباعة
                                                EXCEL
                                            </a>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-secondary btn-secondary--icon" name="refresh"
                                        id="refresh" {{-- id="kt_reset" --}}>
                                        <span>
                                            <i class="la la-close"></i>
                                            <span>إعادة تعيين</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="result_purchase_tbl" name="result_purchase_tbl" class="pur-log-results">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade  " tabindex="-1" id="view_prim_const_m" data-bs-focus="false">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل</h5>
                    <div class="btn btn-icon btn-sm btn-danger  ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <span class="svg-icon svg-icon-2x">X</span>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="show_module" name="show_module"> </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">اغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade  " tabindex="-1" id="view_prim_const_sm" data-bs-focus="false">
        <div class="modal-dialog  modal-dialog-centered mw-550px ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-danger  ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <span class="svg-icon svg-icon-2x">X</span>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="show_module_sm" name="show_module_sm"> </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">اغلاق</button>
                </div>
            </div>
        </div>
    </div>

    </div>{{-- /.pur-log --}}
@endsection
{{-- Styles Section --}}
@section('styles')
    <style>
        /* -------------------------------------------------------------------
           Purchases list — restyled to match resources/views/dashboard/
           invoices/index.blade.php's "سجل عمليات الاستخراج" redesign.
           Scoped entirely under .pur-log so nothing leaks to other pages.
           Reuses the --sn-* brand tokens already defined in
           public/css/app-ui.css (not touched — page-scoped override, same
           approach as the invoices redesign).

           #purchase_tbl is loaded via AJAX (view_all_purchase() -> tbl())
           and injected into #result_purchase_tbl, which lives inside
           .pur-log — so these selectors reach the AJAX-injected table too,
           without needing any JS/DataTables changes.
           ------------------------------------------------------------- */

        /* ---- filter card polish -------------------------------------------- */
        .pur-log .card {
            border-radius: var(--sn-r-lg);
            border-color: var(--sn-line);
        }
        .pur-log .form-label {
            color: var(--sn-ink) !important;
        }

        /* ---- table header: solid emerald fill, white bold text -------------
               Out-specifies the global tint-only .sn-thead rule
               (app-ui.css §6) on purpose, matching the invoices page. ------ */
        .pur-log #purchase_tbl.sn-thead thead tr,
        .pur-log #purchase_tbl thead.sn-thead tr {
            background: var(--sn-emerald) !important;
            color: #fff !important;
        }
        .pur-log #purchase_tbl.sn-thead thead th,
        .pur-log #purchase_tbl thead.sn-thead th {
            color: #fff !important;
            font-weight: 700;
            border-bottom: 2px solid var(--sn-emerald-deep) !important;
            padding-block: .85rem;
        }

        /* ---- data legibility: dark high-contrast ink instead of washed grey - */
        .pur-log #purchase_tbl td {
            color: var(--sn-ink);
        }
        .pur-log #purchase_tbl .text-muted {
            color: var(--sn-ink-soft) !important;
        }
        .pur-log #purchase_tbl tbody td:nth-child(4),
        .pur-log #purchase_tbl tbody td:nth-child(5),
        .pur-log #purchase_tbl tbody td:nth-child(6) {
            font-variant-numeric: tabular-nums lining-nums;
            font-feature-settings: "tnum" 1, "lnum" 1;
        }

        /* ---- totals row: readable emerald tint instead of the old inline
               purple-on-grey (#4a0ce7 on #B5B5C3). Column count/ids (#tex,
               #without_tex) untouched — only the visual treatment changes. -- */
        .pur-log #purchase_tbl tfoot tr.sn-tfoot-total {
            background: var(--sn-emerald-tint) !important;
            color: var(--sn-emerald-deep) !important;
        }
        .pur-log #purchase_tbl tfoot tr.sn-tfoot-total th {
            color: var(--sn-emerald-deep) !important;
            font-weight: 700;
        }

        /* ---- buttons: subtle hover/active feedback --------------------------- */
        .pur-log .btn {
            transition: transform var(--sn-dur-fast) var(--sn-ease-out),
                        box-shadow var(--sn-dur-fast) var(--sn-ease-out),
                        background-color var(--sn-dur-base) var(--sn-ease-out);
        }
        .pur-log .btn:hover {
            transform: translateY(-1px);
        }
        .pur-log .btn:active {
            transform: translateY(0);
        }

        /* ---- row hover + staggered entrance -----------------------------------
               CSS-only (no per-row class needed): #purchase_tbl's rows are
               generated by DataTables from AJAX JSON, but they are still
               ordinary <tr> children of the same static #purchase_tbl, so
               :nth-child staggering and :hover both work without touching
               the DataTables init/columnDefs/footerCallback JS. ------------ */
        .pur-log #purchase_tbl tbody tr {
            animation: sn-row-in var(--sn-dur-slow) var(--sn-ease-out) both;
            transition: background-color var(--sn-dur-fast) var(--sn-ease-out),
                        transform var(--sn-dur-fast) var(--sn-ease-out);
        }
        .pur-log #purchase_tbl tbody tr:hover {
            background-color: var(--sn-emerald-tint) !important;
            transform: translateY(-1px);
        }
        @keyframes sn-row-in {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .pur-log #purchase_tbl tbody tr:nth-child(1)  { animation-delay: 0ms; }
        .pur-log #purchase_tbl tbody tr:nth-child(2)  { animation-delay: 30ms; }
        .pur-log #purchase_tbl tbody tr:nth-child(3)  { animation-delay: 60ms; }
        .pur-log #purchase_tbl tbody tr:nth-child(4)  { animation-delay: 90ms; }
        .pur-log #purchase_tbl tbody tr:nth-child(5)  { animation-delay: 120ms; }
        .pur-log #purchase_tbl tbody tr:nth-child(6)  { animation-delay: 150ms; }
        .pur-log #purchase_tbl tbody tr:nth-child(7)  { animation-delay: 180ms; }
        .pur-log #purchase_tbl tbody tr:nth-child(8)  { animation-delay: 210ms; }
        .pur-log #purchase_tbl tbody tr:nth-child(9)  { animation-delay: 240ms; }
        .pur-log #purchase_tbl tbody tr:nth-child(10) { animation-delay: 270ms; }
        .pur-log #purchase_tbl tbody tr:nth-child(11) { animation-delay: 300ms; }
        .pur-log #purchase_tbl tbody tr:nth-child(12) { animation-delay: 330ms; }
        .pur-log #purchase_tbl tbody tr:nth-child(n+13) { animation-delay: 350ms; }

        /* ---- accessibility: hard-disable all motion added above -------------- */
        @media (prefers-reduced-motion: reduce) {
            .pur-log *,
            .pur-log *::before,
            .pur-log *::after {
                animation: none !important;
                transition: none !important;
                transform: none !important;
            }
        }
    </style>
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
    <script type="text/javascript"
        src="{{ asset('assets/module/purchase_j.js') }}?t={{ config('global.ver.version_all') }}"></script>
    <script>
        view_all_purchase("{{ route('dashboard.purchase.tbl') }}");

        function del_purchase(id) {
            swal.fire({
                text: 'هل انت متأكد من الحذف',
                icon: 'warning',
                buttonsStyling: false,
                confirmButtonText: 'تأكيد الحذف',
                showCancelButton: true,
                cancelButtonText: 'الغاء الامر',
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: 'btn btn-danger'
                }
            }).then(function(result) {
                if (result.value) {
                    $.ajax({
                        url: "{{ route('dashboard.purchase.del_purchase') }}",
                        'type': 'POST',
                        'dataType': 'json',
                        'async': false,
                        'data': {
                            id: id
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        'success': function(resp) {
                            if (resp.status == false) {
                                document.documentElement.scrollTop = 0;
                                swal.fire('خطأ', resp.message);
                            } else {
                                view_all_purchase("{{ route('dashboard.purchase.tbl') }}");
                                swal.fire('تم الحذفبنجاح', resp.message);
                            }

                        }
                    });
                } else if (result.dismiss === 'cancel') {
                    swal.fire('الغاء الامر', 'خطأ');
                }
            });
        }

        setTimeout(function() {
            view_all_purchase();
        }, 1500)

         //حذف خيار الكل من قائد المحل
         @if (auth()->user()->emp_job != 1)
            document.getElementById("manager_id_v").options[0].remove();
            setTimeout(function() {
                view_all_expense();
            }, 1500)
        @endif

    </script>
@endsection
