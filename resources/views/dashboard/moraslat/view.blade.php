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
        accept-charset="utf-8" method="post" action="{{ route('dashboard.moraslat.tbl') }}" enctype="multipart/form-data">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
                <div class="card">
                    <div class="card-body px-1">
                        <div class="mb-0">
                            <div class="row gx-5 mb-5">
                                <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                                    <label for="moraslat_id_v" class="form-label   fs-6 fw-bold text-dark mb-3">رقم المعاملة
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-hashtag fa-fw text-dark"></i></span></div>
                                        <input type="number" name="moraslat_id_v" id="moraslat_id_v" class="form-control fw-bold  " placeholder="رقم المعاملة">
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                    <label for="moraslat_type_id_v" class="form-label   fs-6 fw-bold text-dark mb-3">نوع المراسلة</label>
                                    <div>
                                        <select class="form-select fw-bold  " data-control="select2" id="moraslat_type_id_v"
                                            name="moraslat_type_id_v" dir="rtl">
                                            <option value="">الكل</option>
                                            @foreach ($moraslat_type as $x)
                                                <option value="{{ $x->moraslat_type_id }} ">{{ $x->moraslat_type_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="moraslat_categoty_id_v" class="form-label required  fs-6 fw-bold text-dark mb-3">درجة الأهمية</label>
                                    <div>
                                        <select class="form-select fw-bold  " data-control="select2" id="moraslat_categoty_id_v" name="moraslat_categoty_id_v"
                                            dir="rtl">
                                            <option value="">اختر ..</option>
                                            @foreach ($moraslat_categoty as $x)
                                                <option value="{{ $x->moraslat_categoty_id }} ">{{ $x->moraslat_categoty_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>





                                <div class=" col-12 col-lg-2 col-md-12 col-sm-12  mb-5">

                                    <label for="moraslat_dt_from" class="  form-label fs-6 fw-bold text-dark mb-3">تاريخ
                                        المعاملة</label>
                                    <div class="input-daterange input-group" id="kt_datepicker">
                                        <input type="text" class="form-control input_date_ flatpickr-input"
                                            name="moraslat_dt_from" id="moraslat_dt_from" placeholder="من" data-col-index="5"
                                            readonly="readonly">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i
                                                    class="fas fa-align-justify fa-fw text-dark"></i></span>
                                        </div>
                                        <input type="text" class="form-control input_date_ flatpickr-input"
                                            name="moraslat_dt_to" id="moraslat_dt_to" placeholder="إلى" data-col-index="5"
                                            readonly="readonly">
                                    </div>
                                </div>






                                <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                    <label for="manager_id_v" class="form-label   fs-6 fw-bold text-dark mb-3">توجية الى</label>
                                    <div>
                                        <select class="form-select fw-bold  " data-control="select2" id="manager_id_v"
                                            name="manager_id_v" dir="rtl">
                                            <option value="">الكل</option>
                                            @foreach ($users as $x)
                                                <option value="{{ $x->id }} ">{{ $x->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>






                                <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                    <label for="worker_id_v" class="form-label required fs-6 fw-bold text-dark mb-3">اسم
                                        العامل</label>
                                    <div>
                                        <select class="form-select fw-bolder  worker_id_v  " id="worker_id_v"
                                            name="worker_id_v" dir="rtl" data-placeholder="اسم العامل">
                                            <option value="">اختر ..</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                    <label for="shop_id_v" class="form-label required fs-6 fw-bold text-dark mb-3">اسم المحل</label>
                                    <div>
                                        <select class="form-select fw-bolder  shop_id_v  " id="shop_id_v" name="shop_id_v" dir="rtl"
                                            data-placeholder="اسم المحل">
                                            <option value="">اختر ..</option>
                                        </select>
                                    </div>
                                </div>




                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="moraslat_status_id_v"
                                        class="form-label   fs-6 fw-bolder text-danger mb-3">نوع الاجراء</label>
                                    <div>
                                        <select class="form-select fw-bolder text-danger moraslat_status_id form-select_u "
                                            data-control="select2" id="moraslat_status_id_v" name="moraslat_status_id_v"
                                            dir="rtl" >
                                            <option value="">قيد التنفيذ + ارجاع + جديد</option>

                                            @foreach ($moraslat_status as $x)
                                                <option
                                                    value="{{ $x->moraslat_status_id }} ">
                                                    {{ $x->moraslat_status_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>



                                <div class="col-12 col-lg-3 col-md-12 col-sm-12   mb-2 "
                                    style="padding-top: 2rem !important;">
                                    <a onclick="view_all_moraslat()" class="btn btn-primary btn-primary--icon"
                                        id="kt_search">
                                        <span>
                                            <i class="la la-search"></i>
                                            <span>بحث</span>
                                        </span>
                                    </a>
                                    &nbsp;&nbsp;

                                    <button type="button" class="btn btn-secondary btn-secondary--icon" name="refresh"
                                        id="refresh">
                                        <span>
                                            <i class="la la-close"></i>
                                            <span>إعادة تعيين</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="result_moraslat_tbl" name="result_moraslat_tbl">
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
@endsection
@section('styles')
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/module/moraslat_j.js') }}?t={{ config('global.ver.version_all') }}">
    </script>
    <script>
        view_all_moraslat("{{ route('dashboard.moraslat.tbl') }}");

        function del_moraslat(id) {
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
                        url: "{{ route('dashboard.moraslat.del_moraslat') }}",
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
                                view_all_moraslat("{{ route('dashboard.moraslat.tbl') }}");
                                swal.fire('تم الحذفبنجاح', resp.message);
                            }

                        }
                    });
                } else if (result.dismiss === 'cancel') {
                    swal.fire('الغاء الامر', 'خطأ');
                }
            });
        }




        $(".worker_id_v").select2({
            placeholder: 'اختر',

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
                                text: item.ItemName,
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




        $(".shop_id_v").select2({
        placeholder: 'اختر',
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
            url: " {{ route('dashboard.general.sel_shop_list') }}",
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
                            text: item.ItemName + ' - ' + item.item_code,
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
