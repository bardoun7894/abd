@extends('layouts.app')
@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري ')
@section('title', "$page_title")
@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif
    <div id="user_reg" class="alert alert-danger d-none"></div>
    <form id="save_violation" name="save_violation" class="form" action="{{ route('dashboard.violation.store') }}"
        enctype="multipart/form-data" autocomplete="off" method="POST">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
                <div class="card">
                    <div class="card-body px-1">
                        <div class="alert alert-dismissible   d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                            id="errorBox_violation" style="display: none !important">
                            <span class="svg-icon svg-icon-2hx svg-icon-light me-4 mb-5 mb-sm-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <path opacity="0.3"
                                        d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z"
                                        fill="black"></path>
                                    <path
                                        d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z"
                                        fill="black"></path>
                                </svg>
                            </span>
                            <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                                <span id="displayErrors_violation" class="mb-2  fw-bolder text-light"></span>
                            </div>
                            <button type="button"
                                class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                                data-bs-dismiss="alert">
                                <span class="svg-icon svg-icon-2x svg-icon-light">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none">
                                        <rect opacity="0.5" x="6" y="17.3137" width="16" height="2"
                                            rx="1" transform="rotate(-45 6 17.3137)" fill="black"></rect>
                                        <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                            transform="rotate(45 7.41422 6)" fill="black"></rect>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <div class="mb-0">
                            <div class="row gx-5 mb-5">




                                <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
                                    <label for="shop_id" class="form-label required fs-6 fw-bold text-dark mb-3">اسم المحل</label>
                                    <div>
                                        <select class="form-select fw-bolder  shop_id  " id="shop_id"
                                            name="shop_id" dir="rtl" data-placeholder="اسم المحل">
                                            <option value="">اختر ..</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="violation_no" class="form-label  fs-6 fw-bold text-dark mb-3">رقم المخالفة</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-hashtag fa-fw text-dark text-dark"></i></span></div><input
                                            type="text" name="violation_no" id="violation_no"
                                            class="form-control fw-bold text-dark text-info" minlenght="1"
                                            maxlength="50" placeholder="رقم المخالفة">
                                    </div>
                                </div>


                                <div class=" col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="violation_dt" class="form-label  fs-6 fw-bold text-dark mb-3">تاريخ المخالفة :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-calendar-minus fa-fw text-dark"></i></span></div>
                                        <input type="text" name="violation_dt" id="violation_dt"
                                            class="form-control fw-bold  text-dark input_date_"
                                            placeholder="تاريخ المخالفة" autocomplete="off">
                                    </div>
                                </div>

                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="violation_val" class="form-label required fs-6 fw-bold text-dark mb-3">قيمة المخالفة </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                            type="text" name="violation_val" id="violation_val"
                                            class="form-control fw-bold text-dark text-info form-control-solid"
                                            data-inputmask="'alias' : 'decimal'" minlenght="1" maxlength="20"
                                            placeholder="قيمة المخالفة  ">
                                    </div>
                                </div>
                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="violation_side_id" class="form-label  fs-6 fw-bold text-dark mb-3">جهة المخالفة</label>
                                    <div>
                                        <select class="form-select fw-bold  " data-control="select2" id="violation_side_id"
                                            name="violation_side_id" dir="rtl" data-placeholder="جهة المخالفة ">
                                            <option value="">اختر ..</option>
                                            @foreach ($violation_side as $x)
                                                <option value="{{$x->violation_side_id}} ">{{$x->violation_side_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class=" col-12 col-lg-2 col-md-12 col-sm-12 mb-5 border-gray-500 border ">
                                    <label class="form-label  fs-6 fw-bold text-dark mb-3">هل تم دفعة المخالفة  :</label>
                                    <div class=" fv-row fv-plugins-icon-container fv-plugins-bootstrap5-row-invalid">
                                        <div class="d-flex align-items-center mt-3">
                                            <label class="form-check form-check-inline form-check-solid me-5 is-invalid">
                                                <input class="form-check-input" name="violation_ispay" id="violation_ispay"
                                                    type="checkbox" checked value="1">
                                                <span class="fw-bold ps-2 fs-6 fw-bold  text-success">نعم تم الدفع</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>







                                <div class=" col-12 col-lg-5 col-md-12 col-sm-12  mb-5">
                                    <label for="violation_cause" class="  form-label fs-6 fw-bold text-dark mb-3">سبب المخالفات
                                    </label>
                                    <textarea name="violation_cause" row='1' class="form-control fw-bold" id="violation_cause" placeholder="سبب المخالفات "></textarea>
                                </div>


                                <div class=" col-12 col-lg-5 col-md-12 col-sm-12 mb-5" id="container_file"
                                name="container_file">
                                <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                    <a type="button" id="add_file"
                                        class="btn btn-secondary kt-font-info kt-font-bolder"
                                        style='border-color:#232b51;'><i class="la la-chain"></i>تحميل أوراق اخرى</a>
                                </div>
                                <br />
                                <div class="form-group row">
                                    <div class="input-group ">
                                        <div class="form-control ">
                                            <input type="file" class="form-control custom-file-input"
                                                placeholder="ملف مرفق" name="files[]" multiple>
                                        </div>
                                        <div class="input-group-append" style="padding: 0.7rem 1rem;">
                                            <a class="btn btn-lg btn-danger remove" style="padding: 0.7rem 1rem;">
                                                <span>
                                                    <i class="la la-minus" style="color:#fff"></i>
                                                </span>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </div>



                            </div>
                            <div class=" mb-2 d-flex justify-content ">
                                <button type="submit" id="kt_docs_formvalidation_text_submit"
                                    class="btn btn-primary font-weight-bold mr-2" name="submitButton">حفظ
                                    البيانات</button>
                                &nbsp;&nbsp;
                                <button type="reset" class="btn btn-light font-weight-bold mr-2">تفريغ البيانات</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection


@section('styles')
    <style>
        .uppy-Root {
            font-family: inherit !important;
        }

        .uppy-size--md .uppy-Dashboard-note {
            direction: rtl !important;
        }

        a.uppy-Dashboard-poweredBy {
            display: none !important;
        }

        .uppy-Dashboard-close {
            right: -15px !important;
            background-color: #2275d7;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            text-align: center;
            font-size: 30px;
        }

        .uppy-Dashboard-close span {
            top: -3px;
            position: relative;
        }
    </style>


@endsection







@section('scripts')









    <script>









        $('#add_file').on('click', function() {
            var newfield =
                '<div class="form-group row repeat"><div class="input-group "><div class="form-control custom-file"><input type="file" class="form-control custom-file-input" name="files[]" ></div><div class="input-group-append" style="padding: 0.7rem 1rem;"><a class="btn btn-lg btn-danger remove"  ><span><i class="la la-minus" style="color:#fff"></i></span></a></div></div></div>';
            $('#container_file').append(newfield);
        });
        $(document).on('click', '.remove', function() {
            $(this).parent().parent().parent('div').remove();
        });


        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val();
            if (fileName.length > 23) {
                fileName = fileName.substr(0, 11) + "..." + fileName.substr(-10);
            }
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        $(".shop_id").select2({
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
    <script type="text/javascript" src="{{ asset('assets/module/violation_j.js') }}?t={{ config('global.ver.version_all') }}">
    </script>
    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
@endsection
