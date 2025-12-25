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
    <form id="save_workers" name="save_workers" class="form" action="{{ route('dashboard.workers.store') }}"
        enctype="multipart/form-data" autocomplete="off" method="POST">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="mb-10 flex-lg-row-fluid mb-lg-0">
                <div class="card">
                    <div class="px-1 card-body">
                        <div class="p-5 mb-6 alert alert-dismissible d-flex flex-column flex-sm-row w-100"
                            id="errorBox_worker" style="display: none !important">
                            <span class="mb-5 svg-icon svg-icon-2hx svg-icon-light me-4 mb-sm-0">
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
                                <span id="displayErrors_worker" class="mb-2 fw-bolder text-light"></span>
                            </div>
                            <button type="button"
                                class="top-0 m-2 position-absolute position-sm-relative m-sm-0 end-0 btn btn-icon ms-sm-auto"
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
                            <div class="mb-5 row gx-5">
                                <div class="mb-6 row">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 col-form-label fw-bold fs-6 text-dark">صورة الشخصية
                                        للعامل</label>
                                    <div class="col-lg-8">
                                        <div class="image-input image-input-outline" data-kt-image-input="true"
                                            style="background-image: url(/assets/media/avatars/blank.png)">
                                            <div class="image-input-wrapper w-125px h-125px"
                                                style="background-image: url(/assets/media/avatars/150-2.jpg)"></div>
                                            <label
                                                class="bg-white shadow btn btn-icon btn-circle btn-active-color-primary w-25px h-25px"
                                                data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                                data-bs-dismiss="click" title="Change avatar">
                                                <i class="bi bi-pencil-fill fs-7"></i>

                                                <input type="file" name="avatar" accept="image/*" />
                                                <input type="hidden" name="avatar_remove" />
                                            </label>

                                            <span
                                                class="bg-white shadow btn btn-icon btn-circle btn-active-color-primary w-25px h-25px"
                                                data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                                data-bs-dismiss="click" title="Cancel avatar">
                                                <i class="bi bi-x fs-2"></i>
                                            </span>
                                            <span
                                                class="bg-white shadow btn btn-icon btn-circle btn-active-color-primary w-25px h-25px"
                                                data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                                data-bs-dismiss="click" title="Remove avatar">
                                                <i class="bi bi-x fs-2"></i>
                                            </span>
                                        </div>
                                        <div class="form-text">نوع المسموح: png, jpg, jpeg.</div>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12"><label for="worker_name"
                                        class="mb-3 form-label required fs-6 fw-bold text-dark">اسم
                                        العامل</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-tools fa-fw text-dark"></i></span></div>
                                        <input type="text" name="worker_name" id="worker_name"
                                            class="form-control fw-bold text-dark" placeholder="اسم العامل"
                                            value="" autocomplete="off">
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12"><label for="registration_number   "
                                    class="mb-3 form-label fs-6 fw-bold text-dark">رقم الاشتراك</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-tools fa-fw text-dark"></i></span></div>
                                    <input type="text" name="registration_number" id="registration_number"
                                        class="form-control fw-bold text-dark" placeholder="رقم الاشتراك"
                                        value="" autocomplete="off">
                                </div>
                            </div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="dob" class="mb-3 form-label required fs-6 fw-bold text-dark"> تاريخ
                                        الميلاد :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-calendar-alt fa-fw text-dark"></i></span></div>
                                        <input type="text" name="dob" id="dob"
                                            class="form-control fw-bold text-dark input_date_"
                                            placeholder="تاريخ الميلاد" value="" autocomplete="off">
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="mobile" class="mb-3 form-label fs-6 fw-bold text-dark">رقم هاتف
                                        الموظف</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-phone-volume fa-fw text-dark"></i></span></div>
                                        <input type="text" name="mobile" id="mobile"
                                            class="form-control fw-bold text-dark text-info" minlenght="1"
                                            maxlength="20" placeholder="رقم هاتف الموظف ">
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="ssn" class="mb-3 form-label fs-6 fw-bold text-dark">رقم صاحب
                                        العمل</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-mobile-alt fa-fw text-dark"></i></span></div>
                                        <input type="text" name="phone" id="phone"
                                            class="form-control fw-bold text-dark text-info" minlenght="1"
                                            maxlength="20" placeholder="رقم صاحب العمل">
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="nation_id" class="mb-3 form-label fs-6 fw-bold text-dark">الجنسية</label>
                                    <div>
                                        <select class="form-select fw-bold" data-control="select2" id="nation_id"
                                            name="nation_id" dir="rtl" data-placeholder="الجنسية">
                                            <option value="">اختر ..</option>
                                            @foreach ($nation as $x)
                                                <option value="{{ $x->nation_id }} ">{{ $x->nation_name_ar }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="passport_no" class="mb-3 form-label fs-6 fw-bold text-dark">رقم
                                        الجواز</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-passport fa-fw text-dark"></i></span></div>
                                        <input type="text" name="passport_no" id="passport_no"
                                            class="form-control fw-bold text-dark text-info" minlenght="1"
                                            maxlength="50" placeholder="رقم الجواز">
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="dop" class="mb-3 form-label fs-6 fw-bold text-dark">تاريخ
                                        انتهاء الجواز :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-calendar-minus fa-fw text-dark"></i></span></div>
                                        <input type="text" name="dop" id="dop"
                                            class="form-control fw-bold text-dark input_date_"
                                            placeholder="تاريخ انتهاء الجواز" autocomplete="off">
                                    </div>
                                </div>
                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="manager_id" class="mb-3 form-label fs-6 fw-bold text-dark">قائد المجموعة</label>
                                    <div>
                                        <select class="form-select fw-bold" data-control="select2" id="manager_id"
                                            name="manager_id" dir="rtl" data-placeholder="قائد المجموعة">
                                            <option value="">اختر ..</option>
                                            @foreach ($manager as $x)
                                                <option value="{{ $x->manager_id }} ">{{ $x->manager_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12">
                                    <label for="doe" class="mb-3 form-label fs-6 fw-bold text-dark">تحميل
                                        الجواز :</label>
                                    <input class="form-control custom-file-input" type="file" name='passportfile'>
                                </div>

                                <div class="mb-5 border col-12 col-lg-2 col-md-12 col-sm-12 border-success">
                                    <label class="mb-3 form-label fs-6 fw-bold text-danger">حالة التواجد :</label>
                                    <div class="fv-row fv-plugins-icon-container fv-plugins-bootstrap5-row-invalid">
                                        <div class="mt-3 d-flex align-items-center">
                                            <label class="form-check form-check-inline form-check-solid me-5 is-invalid">
                                                <input class="form-check-input" name="inside" id="inside"
                                                    type="checkbox" checked value="1">
                                                <span class="fw-bold ps-2 fs-6 text-dark">نعم داخل المملكة</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>


                                <div class="my-10 mb-8 separator separator-content border-dark"><span
                                        class="w-150px fw-bold text-danger">بيانات الإقامة</span></div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="ssn" class="mb-3 form-label required fs-6 fw-bold text-dark">رقم
                                        الإقامة</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-id-card fa-fw text-dark"></i></span></div>
                                        <input type="text" name="ssn" id="ssn"
                                            class="form-control fw-bold text-dark text-info"
                                            data-inputmask="'alias' : 'decimal'" minlenght="1" maxlength="50"
                                            placeholder="رقم الإقامة">
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-5 col-md-12 col-sm-12">
                                    <label for="ssnfile" class="mb-3 form-label fs-6 fw-bold text-dark">تحميل
                                        الإقامة :</label>
                                    <input class="form-control custom-file-input" type="file" name='ssnfile'>
                                </div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="dos" class="mb-3 form-label required fs-6 fw-bold text-dark">تاريخ
                                        اصدار الاقامة :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-calendar-alt fa-fw text-dark"></i></span></div>
                                        <input type="text" name="dos" id="dos"
                                            class="form-control fw-bold text-dark input_date_"
                                            placeholder="تاريخ اصدار الاقامة" value="" autocomplete="off">
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="doe" class="mb-3 form-label required fs-6 fw-bold text-dark">تاريخ
                                        إنتهاء الإقامة :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-calendar-alt fa-fw text-dark"></i></span></div>
                                        <input type="text" name="doe" id="doe"
                                            class="form-control fw-bold text-dark input_date_"
                                            placeholder="تاريخ إنتهاء الإقامة" value="" autocomplete="off">
                                    </div>
                                </div>

                                <div class="my-10 mb-8 separator separator-content border-dark"><span
                                        class="w-150px fw-bold text-danger">بيانات العمل</span></div>

                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="dow" class="mb-3 form-label fs-6 fw-bold text-dark"> تاريخ
                                        التعيين :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-calendar-alt fa-fw text-dark"></i></span></div>
                                        <input type="text" name="dow" id="dow"
                                            class="form-control fw-bold text-dark input_date_"
                                            placeholder=" تاريخ التعيين" value="" autocomplete="off">
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12">
                                    <label for="work_place_id" class="mb-3 form-label fs-6 fw-bold text-dark">مكان
                                        العمل</label>
                                    <div>
                                        <select class="form-select fw-bold" data-control="select2" id="work_place_id"
                                            name="work_place_id" dir="rtl" data-placeholder="مكان العمل">
                                            <option value="">اختر ..</option>
                                            @foreach ($work_place as $x)
                                                <option value="{{ $x->work_place_id }} ">{{ $x->work_place_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12">
                                    <label for="job_id" class="mb-3 form-label fs-6 fw-bold text-dark">المهنة</label>
                                    <div>
                                        <select class="form-select fw-bold" data-control="select2" id="job_id"
                                            name="job_id" dir="rtl" data-placeholder="المهنة">
                                            <option value="">اختر ..</option>
                                            @foreach ($job as $x)
                                                <option value="{{ $x->job_id }} ">{{ $x->job_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12" id="container_file"
                                    name="container_file">
                                    <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                        <a type="button" id="add_file"
                                            class="btn btn-secondary kt-font-info kt-font-bolder"
                                            style='border-color:#232b51;'><i class="la la-chain"></i>تحميل أوراق اخرى</a>
                                    </div>
                                    <br />
                                    <div class="form-group row">
                                        <div class="input-group">
                                            <div class="form-control">
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
                                <div class="mb-5 col-12 col-lg-12 col-md-12 col-sm-12">
                                    <label for="note" class="mb-3 form-label fs-6 fw-bold text-dark">الملاحظة
                                    </label>
                                    <textarea name="note" rows="1" class="form-control fw-bold" id="note" placeholder="الملاحظة"></textarea>
                                </div>

                            </div>
                            <div class="mb-2 d-flex justify-content">
                                <button type="submit" id="kt_docs_formvalidation_text_submit"
                                    class="mr-2 btn btn-primary font-weight-bold" name="submitButton">حفظ
                                    البيانات
                                </button>
                                &nbsp;&nbsp;
                                <button type="reset" class="mr-2 btn btn-light font-weight-bold">تفريغ البيانات</button>
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
    </style>
@endsection
@section('scripts')
    <script>
        $('#add_file').on('click', function() {
            var newfield =
                '<div class="form-group row repeat"><div class="input-group"><div class="form-control custom-file"><input type="file" class="form-control custom-file-input" name="files[]" ></div><div class="input-group-append" style="padding: 0.7rem 1rem;"><a class="btn btn-lg btn-danger remove"  ><span><i class="la la-minus" style="color:#fff"></i></span></a></div></div></div>';
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
    </script>
    <script type="text/javascript" src="{{ asset('assets/module/woker_j.js') }}?t={{ config('global.ver.version_all') }}">
    </script>
    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
@endsection
