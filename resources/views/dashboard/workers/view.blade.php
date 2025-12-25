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
        accept-charset="utf-8" method="post" action="{{ route('dashboard.workers.tbl') }}" enctype="multipart/form-data">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
                <div class="card">
                    <div class="card-body px-1">
                        <div class="mb-0">
                            <div class="row gx-5 mb-5">
                                <div class=" col-12 col-lg-3 col-md-12 col-sm-12  mb-5">
                                    <label for="worker_name_v" class="form-label  fs-6 fw-bold text-dark mb-3">اسم
                                        العامل</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"> <i
                                                    class="fas fa-tools fa-fw text-dark"></i></span></div>

                                        <input type="text" name="worker_name_v" id="worker_name_v"
                                            class="form-control fw-bold " placeholder="اسم العامل" value=""
                                            autocomplete="off" />
                                    </div>
                                </div>

                                <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                                    <label for="ssn_v" class="form-label   fs-6 fw-bold text-dark mb-3">رقم الإقامة
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-id-card fa-fw text-dark"></i></span></div>
                                        <input type="number" name="ssn_v" id="ssn_v" class="form-control fw-bold  "
                                            placeholder="رقم الإقامة" />
                                    </div>
                                </div>


                                <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                    <label for="work_place_id_v" class="form-label  fs-6 fw-bold text-dark mb-3">مكان
                                        العمل</label>
                                    <div>
                                        <select class="form-select fw-bold  work_place_id_v" data-control="select2"
                                            id="work_place_id_v" name="work_place_id_v" dir="rtl">
                                            <option value="">الكل</option>
                                            @foreach ($work_place as $x)
                                                <option value="{{ $x->work_place_id }} ">{{ $x->work_place_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="job_id_v" class="form-label  fs-6 fw-bold text-dark mb-3">المهنة</label>
                                    <div>
                                        <select class="form-select fw-bold  " data-control="select2" id="job_id_v"
                                            name="job_id_v" dir="rtl">
                                            <option value="">الكل</option>
                                            @foreach ($job as $x)
                                                <option value="{{ $x->job_id }} ">{{ $x->job_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="manager_id_v" class="form-label   fs-6 fw-bold text-dark mb-3">قائد المجموعة
                                    </label>
                                    <div>
                                        <select class="form-select fw-bold  " data-control="select2" id="manager_id_v"
                                            name="manager_id_v" dir="rtl">
                                            <option value="">الكل</option>
                                            @foreach ($manager as $x)
                                                <option value="{{ $x->manager_id }} ">{{ $x->manager_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="updatedcancal_at_v" class="form-label  fs-6 fw-bold text-dark mb-3">حالة
                                        العمل</label>
                                    <div>
                                        <select class="form-select fw-bold  updatedcancal_at_v" data-control="select2"
                                            id="updatedcancal_at_v" name="updatedcancal_at_v" dir="rtl">
                                            {{-- <option value="">الكل</option> --}}
                                            <option value="1">مستمر</option>
                                            <option value="0">منهي الخدمات</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="end_dt_v" class="form-label  fs-6 fw-bold text-dark mb-3">حالة
                                        الاقامة</label>
                                    <div>
                                        <select class="form-select fw-bold  end_dt_v" data-control="select2" id="end_dt_v"
                                            name="end_dt_v" dir="rtl">
                                            <option value="">الكل</option>
                                            <option value="1">مستمر</option>
                                            <option value="2">منتهي الاقامة</option>
                                            <option value="3">شارف على الانتهاء</option>

                                        </select>
                                    </div>
                                </div>


                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="end_p_dt_v" class="form-label  fs-6 fw-bold text-dark mb-3">حالة
                                        الجواز</label>
                                    <div>
                                        <select class="form-select fw-bold  end_p_dt_v" data-control="select2"
                                            id="end_p_dt_v" name="end_p_dt_v" dir="rtl">
                                            <option value="">الكل</option>
                                            <option value="1">مستمر</option>
                                            <option value="2">منتهي</option>
                                            <option value="3">شارف على الانتهاء</option>

                                        </select>
                                    </div>
                                </div>


                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="inside_v" class="form-label  fs-6 fw-bold text-dark mb-3">حالة
                                        التواجد</label>
                                    <div>
                                        <select class="form-select fw-bold  inside_v" data-control="select2"
                                            id="inside_v" name="inside_v" dir="rtl">
                                            <option value="">الكل</option>
                                            <option value="1">داخل المملكة</option>
                                            <option value="0">خارج المملكة</option>

                                        </select>
                                    </div>
                                </div>



                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="is_imp_v" class="form-label  fs-6 fw-bold text-dark mb-3">حالة
                                        الادخال</label>
                                    <div>
                                        <select class="form-select fw-bold  is_imp_v" data-control="select2"
                                            id="is_imp_v" name="is_imp_v" dir="rtl">
                                            <option value="">الكل</option>
                                            <option value="0">ادخال يدوي</option>
                                            <option value="1">استيراد</option>

                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="nation_v" class="form-label  fs-6 fw-bold text-dark mb-3">الجنسية</label>
                                    <div>
                                        <select class="form-select fw-bold  nation_v" data-control="select2"
                                            id="nation_v" name="nation_v" dir="rtl">
                                            <option value="">الكل</option>
                                            @foreach ($nation as $x)
                                                <option value="{{ $x->nation_id }} ">{{ $x->nation_name_ar }}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>


                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="nation_v" class="form-label  fs-6 fw-bold text-dark mb-3">ترتيب بحسب
                                    </label>
                                    <div>
                                        <select class="form-select fw-bold  nation_v" data-control="select2"
                                            id="order_date" name="order_date" dir="rtl">
                                            <option value="passport_date">تاريخ إنتهاء الإقامة </option>
                                            <option value="residence_date">تاريخ إنتهاء الجواز </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="nation_v" class="form-label  fs-6 fw-bold text-dark mb-3"> شهر انتهاء
                                        الاقامة </label>
                                    <div>
                                        <select class="form-select fw-bold  nation_v" data-control="select2"
                                            id="residence_month" name="residence_month" dir="rtl">
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
                                        الاقامة </label>
                                    <div>
                                        <select class="form-select fw-bold  nation_v" data-control="select2"
                                            id="residence_year" name="residence_year" dir="rtl">
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
                                    <label for="nation_v" class="form-label  fs-6 fw-bold text-dark mb-3"> شهر انتهاء جواز
                                        السفر </label>
                                    <div>
                                        <select class="form-select fw-bold  nation_v" data-control="select2"
                                            id="passport_month" name="passport_month" dir="rtl">
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
                                    <label for="nation_v" class="form-label  fs-6 fw-bold text-dark mb-3"> سنة انتهاء جواز
                                        السفر </label>
                                    <div>
                                        <select class="form-select fw-bold  nation_v" data-control="select2"
                                            id="passport_year" name="passport_year" dir="rtl">
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


                                <div class="col-12 col-lg-5 col-md-12 col-sm-12   mb-5"
                                    style="padding-top: 2rem !important;">
                                    <a onclick="view_all_worker()" class="btn btn-primary btn-primary--icon"
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
                                            <a data-url='{{ route('dashboard.report.print_worker_pdf') }}'
                                                onclick="print_worker_pdf('')" class="menu-link px-3 text-dark "><span><i
                                                        class="far fa-file-pdf fa-fw text-danger"></i></span> طباعة PDF
                                            </a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a data-url='{{ route('dashboard.report.print_worker_xlsx') }}'
                                                onclick="print_worker_xlsx('')"
                                                class="menu-link px-3 text-dark print_worker_xlsx"><span><i
                                                        class="fas fa-file-excel fa-fw text-success"></i></span>طباعة
                                                EXCEL
                                            </a>
                                        </div>
                                    </div>

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
                        <div id="result_worker_tbl" name="result_worker_tbl">
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
                    <div id="show_module" name="show_module"></div>
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
                    <div id="show_module_sm" name="show_module_sm"></div>
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
    <script type="text/javascript" src="{{ asset('assets/module/woker_j.js') }}?t={{ config('global.ver.version_all') }}">
    </script>
    <script>
        view_all_worker("{{ route('dashboard.workers.tbl') }}");

        function del_workers(id) {
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
                        url: "{{ route('dashboard.workers.del_workers') }}",
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
                                view_all_worker("{{ route('dashboard.workers.tbl') }}");
                                swal.fire('تم الحف بنجاح', resp.message);
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
