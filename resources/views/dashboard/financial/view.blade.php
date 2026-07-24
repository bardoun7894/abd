@extends('layouts.app')
@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري')
@section('title', "$page_title")
@section('content')
    <div class="fin-log">

    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif
    <div id="user_reg" class="alert alert-danger d-none">
    </div>
    <form class="kt-form kt-form--label-right" enctype="multipart/form-data" id="boew_project" name="boew_project"
        accept-charset="utf-8" method="post" action="{{ route('dashboard.financial.tbl') }}" enctype="multipart/form-data">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="mb-10 flex-lg-row-fluid mb-lg-0">
                <div class="card">
                    <div class="px-1 card-body">
                        <div class="mb-0">
                            <div class="mb-5 row gx-5">

{{--
                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="financial_month_desc_v" class="mb-3 form-label required fs-6 fw-bold text-dark">شهر الدفع
                                        :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-calendar-alt fa-fw text-dark"></i></span></div><input
                                            type="text" name="financial_month_desc_v" id="financial_month_desc_v" value="{{$nowmonth}}"
                                            class="form-control fw-bold text-dark input_date_view_full_"
                                            placeholder="شهر الدفع " value="" autocomplete="off">
                                    </div>
                                </div> --}}


                                <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12">
                                    <label for="worker_id_v" class="mb-3 form-label required fs-6 fw-bold text-dark">اسم
                                        العامل</label>
                                    <div>
                                        <select class="form-select fw-bolder worker_id_v" id="worker_id_v"
                                            name="worker_id_v" dir="rtl" data-placeholder="اسم العامل">
                                            <option value="">اختر ..</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12">

                                    <label for="purchase_dt_from" class="mb-3 form-label fs-6 fw-bold text-dark">تاريخ
                                        الفاتورة</label>
                                    <div class="input-daterange input-group" id="kt_datepicker">
                                        <input type="text" class="form-control input_date_ flatpickr-input"
                                            name="from" id="from" placeholder="من"
                                            data-col-index="5" readonly="readonly">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i
                                                    class="fas fa-align-justify fa-fw text-dark"></i></span>
                                        </div>
                                        <input type="text" class="form-control input_date_ flatpickr-input"
                                            name="to" id="to" placeholder="إلى" data-col-index="5"
                                            readonly="readonly">
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="manager_id_v" class="mb-3 form-label fs-6 fw-bold text-dark">قائد المجموعة </label>
                                    <div>
                                        <select class="form-select fw-bold" data-control="select2" id="manager_id_v"
                                            name="manager_id_v" dir="rtl" >
                                            <option value="">الكل</option>
                                            @foreach ($manager as $x)
                                                <option value="{{ $x->manager_id }} ">{{ $x->manager_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>








                                <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12"
                                    style="padding-top: 2rem !important;">
                                    <a onclick="view_all_financial()" class="btn btn-primary btn-primary--icon"
                                        id="kt_search">
                                        <span>
                                            <i class="la la-search"></i>
                                            <span>بحث</span>
                                        </span>
                                    </a>


                                    <button type="button" class="btn btn-dark" data-kt-menu-trigger="click"
                                            data-kt-menu-placement="bottom-start"><i class="fas fa-print fa-fw"></i>
                                        تقارير
                                        <span class="rotate-180 svg-icon svg-icon-5 ms-3 me-0"><i
                                                class="fas fa-angle-down fa-fw"></i></span>
                                    </button>
                                    <div
                                        class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-150px"
                                        data-kt-menu="true">
                                        <div class="px-3 menu-item">
                                            <a data-url='{{ route('dashboard.report.print_fnancial_pdf') }}'
                                               onclick="print_fnancial_pdf('')" class="px-3 menu-link text-dark print_fnancial_pdf"><span><i
                                                        class="far fa-file-pdf fa-fw text-danger"></i></span> طباعة PDF
                                            </a>
                                        </div>
                                        <div class="px-3 menu-item">
                                            <a data-url='{{ route('dashboard.report.print_fnancial_xlsx') }}'
                                               onclick="print_fnancial_xlsx('')"
                                               class="px-3 menu-link text-dark print_fnancial_xlsx"><span><i
                                                        class="fas fa-file-excel fa-fw text-success"></i></span>طباعة
                                                EXCEL
                                            </a>
                                        </div>
                                    </div>
                                    @if(count(DB::table('payments_month')->get()) > 0)
                                    <a  class="btn btn-danger btn-danger--icon"  href="{{ route('dashboard.financial.cronadd') }}">
                                        <span>
                                            <i class="fas fa-history fa-fw"></i>
                                            <span>الشهر الجديد</span>
                                        </span>
                                    </a>
                                    @endif

                                    <a class="btn btn-info btn-info--icon" href="{{ route('dashboard.financial.ai_insights') }}">
                                        <span>
                                            <i class="fa fa-robot fa-fw"></i>
                                            <span>تحليلات الذكاء الاصطناعي</span>
                                        </span>
                                    </a>

                                    </div>
                            </div>
                        </div>
                        <div id="result_financial_tbl" name="result_financial_tbl">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade" tabindex="-1" id="view_prim_const_m">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" data-bs-focus="false">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل</h5>
                    <div class="btn btn-icon btn-sm btn-danger ms-2" data-bs-dismiss="modal" aria-label="Close">
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

    <div class="modal fade" tabindex="-1" id="view_prim_const_sm" data-bs-focus="false">
        <div class="modal-dialog modal-dialog-centered mw-550px">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-danger ms-2" data-bs-dismiss="modal" aria-label="Close">
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

    </div>{{-- /.fin-log --}}
@endsection
{{-- Styles Section --}}
@section('styles')
    <style>
        /* -------------------------------------------------------------------
           Financial ("مصاريف العمال") list — restyled to match
           resources/views/dashboard/purchase/view.blade.php +
           resources/views/dashboard/invoices/index.blade.php's shared
           emerald redesign. Scoped entirely under .fin-log so nothing leaks
           to other pages. Reuses the --sn-* brand tokens already defined in
           public/css/app-ui.css (not touched — page-scoped override, same
           approach as the purchases/invoices redesign).

           #financial_tbl is loaded via AJAX (view_all_financial() -> tbl())
           and injected into #result_financial_tbl, which lives inside
           .fin-log — so these selectors reach the AJAX-injected table too,
           without needing any JS/DataTables changes.
           ------------------------------------------------------------- */

        /* ---- filter card polish -------------------------------------------- */
        .fin-log .card {
            border-radius: var(--sn-r-lg);
            border-color: var(--sn-line);
        }
        .fin-log .form-label {
            color: var(--sn-ink) !important;
        }

        /* ---- summary tiles (اجمالي المبلغ / اجمالي المدفوع / اجمالي المتبقي) - */
        .fin-log .border-dashed {
            border-color: var(--sn-line) !important;
            border-radius: var(--sn-r-md);
            background: var(--sn-card);
            box-shadow: var(--sn-shadow-sm);
        }
        .fin-log #sum_c1,
        .fin-log #sum_sum_det_financial_month_pay_All,
        .fin-log #sum_xx {
            font-variant-numeric: tabular-nums lining-nums;
            font-feature-settings: "tnum" 1, "lnum" 1;
        }

        /* ---- table header: solid emerald fill, white bold text -------------
               Out-specifies the global tint-only .sn-thead rule
               (app-ui.css §6) on purpose, matching the purchases/invoices
               pages. ----------------------------------------------------- */
        .fin-log #financial_tbl.sn-thead thead tr,
        .fin-log #financial_tbl thead.sn-thead tr {
            background: var(--sn-emerald) !important;
            color: #fff !important;
        }
        .fin-log #financial_tbl.sn-thead thead th,
        .fin-log #financial_tbl thead.sn-thead th {
            color: #fff !important;
            font-weight: 700;
            border-bottom: 2px solid var(--sn-emerald-deep) !important;
            padding-block: .85rem;
        }

        /* ---- data legibility: dark high-contrast ink instead of washed grey - */
        .fin-log #financial_tbl td {
            color: var(--sn-ink);
        }
        .fin-log #financial_tbl .text-muted {
            color: var(--sn-ink-soft) !important;
        }
        .fin-log #financial_tbl tbody td:nth-child(6),
        .fin-log #financial_tbl tbody td:nth-child(7),
        .fin-log #financial_tbl tbody td:nth-child(8) {
            font-variant-numeric: tabular-nums lining-nums;
            font-feature-settings: "tnum" 1, "lnum" 1;
        }

        /* ---- totals row: readable emerald tint instead of the old inline
               purple-on-grey (#4a0ce7 on #B5B5C3). Column count/ids untouched
               — only the visual treatment changes. -------------------------- */
        .fin-log #financial_tbl tfoot tr.sn-tfoot-total {
            background: var(--sn-emerald-tint) !important;
            color: var(--sn-emerald-deep) !important;
        }
        .fin-log #financial_tbl tfoot tr.sn-tfoot-total th {
            color: var(--sn-emerald-deep) !important;
            font-weight: 700;
        }

        /* ---- buttons: subtle hover/active feedback --------------------------- */
        .fin-log .btn {
            transition: transform var(--sn-dur-fast) var(--sn-ease-out),
                        box-shadow var(--sn-dur-fast) var(--sn-ease-out),
                        background-color var(--sn-dur-base) var(--sn-ease-out);
        }
        .fin-log .btn:hover {
            transform: translateY(-1px);
        }
        .fin-log .btn:active {
            transform: translateY(0);
        }

        /* ---- row hover + staggered entrance -----------------------------------
               CSS-only (no per-row class needed): #financial_tbl's rows are
               generated by DataTables from AJAX JSON, but they are still
               ordinary <tr> children of the same static #financial_tbl, so
               :nth-child staggering and :hover both work without touching
               the DataTables init/columnDefs/footerCallback JS. ------------ */
        .fin-log #financial_tbl tbody tr {
            animation: sn-row-in var(--sn-dur-slow) var(--sn-ease-out) both;
            transition: background-color var(--sn-dur-fast) var(--sn-ease-out),
                        transform var(--sn-dur-fast) var(--sn-ease-out);
        }
        .fin-log #financial_tbl tbody tr:hover {
            background-color: var(--sn-emerald-tint) !important;
            transform: translateY(-1px);
        }
        @keyframes sn-row-in {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fin-log #financial_tbl tbody tr:nth-child(1)  { animation-delay: 0ms; }
        .fin-log #financial_tbl tbody tr:nth-child(2)  { animation-delay: 30ms; }
        .fin-log #financial_tbl tbody tr:nth-child(3)  { animation-delay: 60ms; }
        .fin-log #financial_tbl tbody tr:nth-child(4)  { animation-delay: 90ms; }
        .fin-log #financial_tbl tbody tr:nth-child(5)  { animation-delay: 120ms; }
        .fin-log #financial_tbl tbody tr:nth-child(6)  { animation-delay: 150ms; }
        .fin-log #financial_tbl tbody tr:nth-child(7)  { animation-delay: 180ms; }
        .fin-log #financial_tbl tbody tr:nth-child(8)  { animation-delay: 210ms; }
        .fin-log #financial_tbl tbody tr:nth-child(9)  { animation-delay: 240ms; }
        .fin-log #financial_tbl tbody tr:nth-child(10) { animation-delay: 270ms; }
        .fin-log #financial_tbl tbody tr:nth-child(11) { animation-delay: 300ms; }
        .fin-log #financial_tbl tbody tr:nth-child(12) { animation-delay: 330ms; }
        .fin-log #financial_tbl tbody tr:nth-child(n+13) { animation-delay: 350ms; }

        /* ---- accessibility: hard-disable all motion added above -------------- */
        @media (prefers-reduced-motion: reduce) {
            .fin-log *,
            .fin-log *::before,
            .fin-log *::after {
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
        src="{{ asset('assets/module/financial_j.js') }}?t={{ config('global.ver.version_all') }}"></script>
    <script>
        view_all_financial("{{ route('dashboard.financial.tbl') }}");
        function del_financial (id) {
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
                        url: "{{ route('dashboard.financial.del_financial') }}",
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
                                view_all_financial("{{ route('dashboard.financial.tbl') }}");
                                swal.fire('تم الحذفبنجاح', resp.message);
                            }

                        }
                    });
                } else if (result.dismiss === 'cancel') {
                    swal.fire('الغاء الامر', 'خطأ');
                }
            });
        }





        $('.input_date_view_full_').flatpickr({
    "locale": "ar" ,

    plugins: [
        new monthSelectPlugin({
          shorthand: false,
          dateFormat: "m-Y",
          altFormat: "F Y",
          theme: "light"

        }),

    ],
    onChange: function(selectedDates, dateStr, instance) {
  },

});


$(".worker_id_v").select2({
            placeholder: 'اختر',
            allowClear: true,
            language: {
                searching: function() {
                    return 'بحث';
                },
                loadingMore: function() {
                    return "تحميل المزيد.."
                },
                errorLoading: function() {
                    return "The results could not be loaded."
                },
                inputTooLong: function(e) {
                    var t = e.input.length - e.maximum,
                        n = "Please delete" + t + " character";
                    return t != 1 && (n += "s"), n
                },
                inputTooShort: function(e) {
                    var t = e.minimum - e.input.length,
                        n = "Please enter" + t + " or more characters";
                    return n
                },
                maximumSelected: function(e) {
                    var t = "You can only select" + e.maximum + " item";
                    return e.maximum != 1 && (t += "s"), t
                },
                noResults: function() {
                    return "No results found"
                },
                removeAllItems: function() {
                    return "Remove all items"
                }
            },
            ajax: {
                url: " {{ route('dashboard.general.sel_worker_list') }}",
                dataType: "json",
                type: "POST",
                delay: 250,
                async: false,
                casesensitive: false,
                beforeSend: function() {
                    load_message();
                },
                complete: function() {
                    unload_message();
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page || 1,
                        job: 1,
                    };
                },
                processResults: function(data, params) {
                    var resData = [];
                    data.forEach(function(value) {
                        resData.push(value);
                    });
                    var page = params.page || 1;

                    return {
                        results: $.map(resData, function(item) {
                            return {
                                text: item.ItemName+ ' - '+item.item_code,
                                id: item.id
                            };
                        }),
                        pagination: {
                            more: (page * 50) <= data[0].total_count
                        }
                    };
                },
                cache: true,
                escapeMarkup: function(m) {
                    return m;
                }
            },
        });
    </script>
@endsection
