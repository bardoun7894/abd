@extends('layouts.app')
@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري')
@section('title', "$page_title")
@section('content')
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
@endsection
@section('styles')
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
