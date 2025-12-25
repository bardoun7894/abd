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
        accept-charset="utf-8" method="post" action="{{ route('dashboard.shop.tbl') }}" enctype="multipart/form-data">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
                <div class="card">
                    <div class="card-body px-1">
                        <div class="mb-0">
                            <div class="row gx-5 mb-5">
                                <div class=" col-12 col-lg-4 col-md-12 col-sm-12 mb-5"><label for="shop_name"
                                    class="form-label  fs-6 fw-bold text-dark mb-3">اسم المحل</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-store-alt fa-fw text-dark"></i></span></div><input
                                        type="text" name="shop_name_v" id="shop_name_v"
                                        class="form-control fw-bold  text-dark" placeholder="اسم المحل"
                                        value="" autocomplete="off">
                                </div>
                            </div>

                            <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label for="manager_id_v" class="form-label   fs-6 fw-bold text-dark mb-3">قائد المحل</label>
                                <div>
                                    <select class="form-select fw-bold  " data-control="select2" id="manager_id_v"
                                        name="manager_id_v" dir="rtl" >
                                        <option value="">الكل</option>
                                        @foreach ($manager as $x)
                                            <option value="{{ $x->manager_id }} ">{{ $x->manager_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class=" col-12 col-lg-3 col-md-12 col-sm-12 mb-5"><label for="shop_respon_v"
                                class="form-label  fs-6 fw-bold text-dark mb-3">اسم المسؤول</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i
                                            class="fas fa-user-tie fa-fw text-dark"></i></span></div><input
                                    type="text" name="shop_respon_v" id="shop_respon_v"
                                    class="form-control fw-bold  text-dark" placeholder="اسم المسؤول"
                                    value="" autocomplete="off">
                            </div>
                        </div>
                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="shop_mobile_v" class="form-label  fs-6 fw-bold text-dark mb-3">رقم جوال المسؤول</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-phone-volume fa-fw text-dark"></i></span></div><input
                                        type="text" name="shop_mobile_v" id="shop_mobile_v"
                                        class="form-control fw-bold text-dark text-info" minlenght="1"
                                        maxlength="20" placeholder="رقم جوال المسؤول">
                                </div>
                            </div>
                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="city_id_v" class="form-label  fs-6 fw-bold text-dark mb-3">المدينة</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" id="city_id_v"
                                        name="city_id_v" dir="rtl" >
                                        <option value="">الكل</option>
                                        @foreach ($city as $x)
                                            <option value="{{ $x->city_id }} ">{{ $x->city_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>



                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="comme_no_v" class="form-label  fs-6 fw-bold text-dark mb-3">رقم السجل
                                    التجاري</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-passport fa-fw text-dark"></i></span></div><input
                                        type="text" name="comme_no_v" id="comme_no_v"
                                        class="form-control fw-bold text-dark text-info" minlenght="1"
                                        maxlength="50" placeholder="رقم السجل التجاري">
                                </div>
                            </div>


                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="municip_no_v" class="form-label  fs-6 fw-bold text-dark mb-3">رقم رخصة البلدية</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-passport fa-fw text-dark"></i></span></div><input
                                        type="text" name="municip_no_v" id="municip_no_v"
                                        class="form-control fw-bold text-dark text-info" minlenght="1"
                                        maxlength="50" placeholder="رقم رخصة البلدية">
                                </div>
                            </div>

                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="rentpay_price_v" class="form-label  fs-6 fw-bold text-dark mb-3">حالة استحقاق دفع الايجار</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" id="rentpay_price_v"
                                        name="rentpay_price_v" dir="rtl" >
                                        <option value="">الكل</option>
                                        <option value="0">يحتاج الى تحديث</option>
                                        <option value="1">له بيانات ايجار</option>

                                    </select>
                                </div>
                            </div>



                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="nation_v" class="form-label  fs-6 fw-bold text-dark mb-3">ترتيب بحسب
                                </label>
                                <div>
                                    <select class="form-select fw-bold  nation_v" data-control="select2"
                                        id="order_date" name="order_date" dir="rtl">
                                        <option value="comme_date">تاريخ إنتهاء السجل التجاري </option>
                                        <option value="municip_date">تاريخ إنتهاء سجل البلدية </option>
                                        <option value="rentpay_date">تاريخ إنتهاء عقد الاجار </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="nation_v" class="form-label  fs-6 fw-bold text-dark mb-3"> شهر انتهاء
                                    السجل التجاري </label>
                                <div>
                                    <select class="form-select fw-bold  nation_v" data-control="select2"
                                        id="comme_month" name="comme_month" dir="rtl">
                                        <option value="">غير محدد </option>
                                        <option value="1"> 1</option>
                                        <option value="2"> 2</option>
                                        <option value="3"> 3</option>
                                        <option value="4"> 4</option>
                                        <option value="5"> 5</option>
                                        <option value="6"> 6</option>
                                        <option value="7"> 7</option>
                                        <option value="8"> 8</option>
                                        <option value="9"> 9</option>
                                        <option value="10"> 10</option>
                                        <option value="11"> 11</option>
                                        <option value="12"> 12</option>

                                    </select>
                                </div>
                            </div>



                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="nation_v" class="form-label  fs-6 fw-bold text-dark mb-3"> سنة انتهاء
                                    السجل التجاري </label>
                                <div>
                                    <select class="form-select fw-bold  nation_v" data-control="select2"
                                        id="comme_year" name="comme_year" dir="rtl">
                                        <option value="">غير محدد </option>
                                        @for ($i = (int) date('Y') - 5; $i <= (int) date('Y'); $i++)
                                            <option value="{{ $i }}"> {{ $i }}</option>
                                        @endfor
                                        @for ($i = (int) date('Y') + 1; $i <= (int) date('Y') + 5; $i++)
                                            <option value="{{ $i }}"> {{ $i }}</option>
                                        @endfor

                                    </select>
                                </div>
                            </div>


                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="nation_v" class="form-label  fs-6 fw-bold text-dark mb-3"> شهر انتهاء
                                    سجل البلدية </label>
                                <div>
                                    <select class="form-select fw-bold  nation_v" data-control="select2"
                                        id="municip_month" name="municip_month" dir="rtl">
                                        <option value="">غير محدد </option>
                                        <option value="1"> 1</option>
                                        <option value="2"> 2</option>
                                        <option value="3"> 3</option>
                                        <option value="4"> 4</option>
                                        <option value="5"> 5</option>
                                        <option value="6"> 6</option>
                                        <option value="7"> 7</option>
                                        <option value="8"> 8</option>
                                        <option value="9"> 9</option>
                                        <option value="10"> 10</option>
                                        <option value="11"> 11</option>
                                        <option value="12"> 12</option>

                                    </select>
                                </div>
                            </div>



                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="nation_v" class="form-label  fs-6 fw-bold text-dark mb-3"> سنة انتهاء
                                    سجل البلدية </label>
                                <div>
                                    <select class="form-select fw-bold  nation_v" data-control="select2"
                                        id="municip_year" name="municip_year" dir="rtl">
                                        <option value="">غير محدد </option>
                                        @for ($i = (int) date('Y') - 5; $i <= (int) date('Y'); $i++)
                                            <option value="{{ $i }}"> {{ $i }}</option>
                                        @endfor
                                        @for ($i = (int) date('Y') + 1; $i <= (int) date('Y') + 5; $i++)
                                            <option value="{{ $i }}"> {{ $i }}</option>
                                        @endfor

                                    </select>
                                </div>
                            </div>


                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="nation_v" class="form-label  fs-6 fw-bold text-dark mb-3"> شهر انتهاء
                                    عقد الاجار  </label>
                                <div>
                                    <select class="form-select fw-bold  nation_v" data-control="select2"
                                        id="rentpay_month" name="rentpay_month" dir="rtl">
                                        <option value="">غير محدد </option>
                                        <option value="1"> 1</option>
                                        <option value="2"> 2</option>
                                        <option value="3"> 3</option>
                                        <option value="4"> 4</option>
                                        <option value="5"> 5</option>
                                        <option value="6"> 6</option>
                                        <option value="7"> 7</option>
                                        <option value="8"> 8</option>
                                        <option value="9"> 9</option>
                                        <option value="10"> 10</option>
                                        <option value="11"> 11</option>
                                        <option value="12"> 12</option>

                                    </select>
                                </div>
                            </div>



                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="nation_v" class="form-label  fs-6 fw-bold text-dark mb-3"> سنة انتهاء
                                    عقد الاجار </label>
                                <div>
                                    <select class="form-select fw-bold  nation_v" data-control="select2"
                                        id="rentpay_year" name="rentpay_year" dir="rtl">
                                        <option value="">غير محدد </option>
                                        @for ($i = (int) date('Y') - 5; $i <= (int) date('Y'); $i++)
                                            <option value="{{ $i }}"> {{ $i }}</option>
                                        @endfor
                                        @for ($i = (int) date('Y') + 1; $i <= (int) date('Y') + 5; $i++)
                                            <option value="{{ $i }}"> {{ $i }}</option>
                                        @endfor

                                    </select>
                                </div>
                            </div>

                                <div class="col-12 col-lg-4 col-md-12 col-sm-12   mb-5"
                                    style="padding-top: 2rem !important;">
                                    <a onclick="view_all_shop()" class="btn btn-primary btn-primary--icon"
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
                                    <div
                                        class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-150px py-4"
                                        data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a data-url='{{ route('dashboard.report.print_shop_pdf') }}'
                                               onclick="print_shop_pdf('')" class="menu-link px-3 text-dark print_shop_pdf "><span><i
                                                        class="far fa-file-pdf fa-fw text-danger "></i></span> طباعة PDF
                                            </a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a data-url='{{ route('dashboard.report.print_shop_xlsx') }}'
                                               onclick="print_shop_xlsx('')"
                                               class="menu-link px-3 text-dark print_shop_xlsx"><span><i
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
                        <div id="result_shop_tbl" name="result_shop_tbl">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade  " tabindex="-1" id="view_prim_const_m" data-bs-focus="false">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl " >
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
{{-- Styles Section --}}
@section('styles')
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
    <script type="text/javascript"
        src="{{ asset('assets/module/shop_j.js') }}?t={{ config('global.ver.version_all') }}"></script>
    <script>
        view_all_shop("{{ route('dashboard.shop.tbl') }}");
        function del_shop(id) {
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
                        url: "{{ route('dashboard.shop.del_shop') }}",
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
                                view_all_shop("{{ route('dashboard.shop.tbl') }}");
                                swal.fire('تم الحذفبنجاح', resp.message);
                            }

                        }
                    });
                } else if (result.dismiss === 'cancel') {
                    swal.fire('الغاء الامر', 'خطأ');
                }
            });
        }



    </script>
@endsection
