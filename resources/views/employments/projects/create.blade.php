@extends('layouts.app')
@section('module',"وزارة العمل ")
@section('sub',"أنظمة الوزارة ")
@section('title',"$page_title")
@section('content')
@if (session()->has('alert.success'))
<div class="alert alert-success">
    {{ session('alert.success') }}
</div>
@endif
<div id="user_reg" class="alert alert-danger d-none"></div>
<!--end::Alert-->

<form id="save_project" name="save_project" class="form" action="{{route('projects.store')}}"
    method="post" enctype="multipart/form-data" autocomplete="off">

    @csrf

    <!--begin::Layout-->
    <div class="d-flex flex-column flex-lg-row">

        <!--begin::Content-->

        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">


            <div class="card">
                <div class="card-body px-1">

                    <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10"
                        id="errorBox_project" style="display: none !important">
                        <i class="ki-duotone ki-search-list fs-2hx text-success me-4 mb-5 mb-sm-0"><span
                                class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-column pe-0 pe-sm-10" id="displayErrors_project">
                            <h5 class="mb-1">This is an alert</h5>
                            <span>The alert component can be used to highlight certain parts of your page for higher
                                content visibility.</span>
                        </div>
                        <button type="button"
                            class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                            data-bs-dismiss="alert">
                            <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span
                                    class="path2"></span></i>
                        </button>
                    </div>
                    <div class="mb-0">

                        <div class="row gx-5 mb-5">
                            <div class=" col-12 col-lg-5 col-md-12 col-sm-12  mb-5">
                                <label for="PROJECT_NAME_IN" class="  form-label fs-6 fw-bold text-dark mb-3">اسم
                                    المشروع</label>
                                <!--begin::Input group-->
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"> <i
                                                class="fas fa-flag-checkered fa-fw text-dark"></i></span></div>

                                    <input type="text" name="PROJECT_NAME_IN" id="PROJECT_NAME_IN"
                                        class="form-control fw-bold " placeholder="اسم المشروع" autocomplete="off" />
                                </div>
                            </div>


                            <div class=" col-12 col-lg-4 col-md-12 col-sm-12  mb-5">

                                <label for="project_name" class="  form-label fs-6 fw-bold text-dark mb-3">تاريخ
                                    التسجيل</label>
                                <div class="input-daterange input-group" id="kt_datepicker">
                                    <input type="text" class="form-control input_date_" name="START_DATE_IN"
                                        id="START_DATE_IN" placeholder="من" data-col-index="5" />
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i
                                                class="fas fa-align-justify fa-fw text-dark"></i></span>
                                    </div>
                                    <input type="text" class="form-control input_date_" name="END_DATE_IN"
                                        id="END_DATE_IN" placeholder="إلى" data-col-index="5" />
                                </div>
                            </div>



                            <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">الممول</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" id="FINANCIER_IN"
                                        name="FINANCIER_IN" dir="rtl" data-placeholder="الممول">
                                        <option value="">اختر ..</option>
                                        @foreach ($CONT_TYPE_ID as $x)
                                        <option value="{{ $x->cont_id }} ">{{ $x->cont_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                             <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">الجهة الشريكة</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" id="PARTNER_IN"
                                        name="PARTNER_IN" dir="rtl" data-placeholder="الجهة الشريكة">
                                        <option value="">اختر ..</option>
                                        @foreach ($CONT_TYPE_ID as $x)
                                        <option value="{{ $x->cont_id }} ">{{ $x->cont_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-lg-3 col-md-12 col-sm-12   mb-5">
                                <label for="project_name" class="  form-label fs-6 fw-bold text-dark mb-3">تنفيذ المشروع
                                </label>

                                <div class="input-daterange input-group">
                                    <input class="form-control input_date_" placeholder="من" name="ACTUAL_START_DATE_IN"
                                        id="ACTUAL_START_DATE_IN" />
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i
                                                class="fas fa-align-justify fa-fw text-dark"></i></span>
                                    </div>
                                    <input class="form-control input_date_" placeholder="إلى" name="ACTUAL_END_DATE_IN"
                                        id="ACTUAL_END_DATE_IN" />
                                </div>
                            </div>










                            <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                                <label for="TARGET_DURATION_IN" class="form-label fs-6 fw-bold text-dark mb-3">عدد ايام
                                    المشروع
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-stopwatch-20 fa-fw text-dark"></i></span></div>

                                    <input type="number" name="TARGET_DURATION_IN" id="TARGET_DURATION_IN"
                                        class="form-control " placeholder="عدد ايام المشروع" />
                                </div>
                            </div>


                            <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                                <label for="phone" class="form-label fs-6 fw-bold text-dark mb-3">قيمة المشروع</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-donate fa-fw text-dark"></i></span></div>

                                    <input type="number" name="PROJECT_BUDGET_IN" id="PROJECT_BUDGET_IN"
                                        class="form-control " placeholder="قيمة المشروع" />
                                </div>
                            </div>

                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">العملة</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" id="CURRENCY_IN"
                                        name="CURRENCY_IN" dir="rtl" data-placeholder="العملة">
                                        <option value="">اختر ..</option>
                                        @foreach ($coins as $x)
                                        <option value="{{ $x->status_id }} ">{{ $x->status_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
















                            <div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
                                <label for="PROJECT_DESCRIPTION_IN"
                                    class="  form-label fs-6 fw-bold text-dark mb-3">الهدف
                                    العام</label>
                                <textarea name="PROJECT_DESCRIPTION_IN" id="PROJECT_DESCRIPTION_IN"></textarea>
                            </div>



                            <div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
                                <label for="PROJECT_IDEA_IN" class="form-label fs-6 fw-bold text-dark mb-3">فكرة
                                    البرنامج
                                </label>
                                <textarea name="PROJECT_IDEA_IN" id="PROJECT_IDEA_IN"></textarea>
                            </div>







             <div class="separator separator-content border-dark my-10 mb-8"><span
                                    class="w-150px fw-bold text-danger">جهات التشغيل</span></div>


                            <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">جهات التشغيل</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" name="SIDE_ID_IN[]"
                                        id="SIDE_ID_IN" dir="rtl" data-placeholder="جهات التشغيل"
                                        data-allow-clear="true" multiple="multiple">
                                        <option value="">اختر ..</option>
                                        @foreach ($CONT_TYPE_ID as $x)
                                        <option value="{{ $x->cont_id }} ">{{ $x->cont_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>









                            <div class="separator separator-content border-dark my-10 mb-8"><span
                                    class="w-200px fw-bold text-danger">المحافظات المستهدفة</span></div>

                            <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">المحافظة المستهدفة </label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" name="PROJECT_AREA[]"
                                        id="BENEFICIARY_REGION_IN " dir="rtl" data-placeholder="المحافظة المستهدفة"
                                        data-allow-clear="true" multiple="multiple">

                                        <option value="">اختر ..</option>
                                        @foreach ($PROJECT_AREA as $x)
                                        <option value="{{ $x->id }} ">{{ $x->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">المنطقة</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" id="PROJECT_AREA_DEPT"
                                        name="PROJECT_AREA_DEPT[]" dir="rtl" data-placeholder="المنطقة"
                                        data-allow-clear="true" multiple="multiple">
                                        <option value="">اختر ..</option>
                                        @foreach ($PROJECT_AREA_DEPT as $x)
                                        <option value="{{ $x->id }} ">{{ $x->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="separator separator-content border-dark my-10 mb-8"><span
                                    class="w-150px fw-bold text-danger">المؤهل العلمي</span></div>









                        <div id="kt_docs_repeater_advanced_edu">
                            <!--begin::Form group-->
                            <div class="form-group">
                                <div data-repeater-list="kt_docs_repeater_advanced_edu">
                                    <div data-repeater-item>
                                        <div class="form-group row mb-5">



                                                       <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                                <label class=" form-label fs-6 fw-bold text-dark mb-3">المؤهل
                                                    المستفدين</label>
                                                <div>

                                                    <select class="form-select fw-bold " data-control="select2"
                                                        data-placeholder="المؤهل العلمي" name="BENEFICIARY_TYPE_IN"
                                                        dir="rtl">
                                                        <option value="">اختر ..</option>
                                                        @foreach ($type_in as $x)
                                                        <option value="{{ $x->status_id }} ">{{ $x->status_name}}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>


                                                                        <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">التخصص</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2"
                                        name="BENEFICIARY_SUB_TYPE_IN" dir="rtl" data-placeholder="التخصص">
                                        <option value="">اختر ..</option>
                                        @foreach ($sub_type_in as $x)
                                        <option value="{{ $x->status_id }} ">{{ $x->status_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>



                                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                                <label class=" form-label fs-6 fw-bold text-dark mb-3">جنس
                                                    المستهدفين</label>
                                                <div>
                                                    <select class="form-select fw-bold" data-control="select2"
                                                        name="BENEFICIARY_GENDER_IN" dir="rtl"
                                                        data-placeholder="جنس المستفدين" data-allow-clear="true">

                                                        <option value="">اختر ..</option>
                                                        @foreach ($SEX as $x)
                                                        <option value="{{ $x->status_id }} ">{{ $x->status_name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>




                                           <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                                                <label for="EMPLOYMENT_PROJECTS_USER_COUNT"
                                                    class="form-label fs-6 fw-bold text-dark mb-3">عد المستفدين من
                                                    المشروع</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                                class="fas fa-stopwatch-20 fa-fw text-dark"></i></span>
                                                    </div>

                                                    <input type="number" name="BENEFICIARY_COUNT_IN"
                                                        id="BENEFICIARY_COUNT_IN " class="form-control "
                                                        placeholder="عدد المستفدين" />
                                                </div>
                                            </div>




                            <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                                <label for="SALARY_IN" class="form-label fs-6 fw-bold text-dark mb-3">الراتب</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-dollar-sign fa-fw text-dark"></i></span></div>

                                    <input type="number" name="BENEFICIARY_SALARY_IN" id="BENEFICIARY_SALARY_IN" class="form-control "
                                        placeholder="الراتب" />
                                </div>
                            </div>





                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">العملة</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2"
                                        name="BENEFICIARY_CURRENCY_IN" dir="rtl" data-placeholder="العملة">
                                        <option value="">اختر ..</option>
                                        @foreach ($coins as $x)
                                        <option value="{{ $x->status_id }} ">{{ $x->status_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                                <label class=" form-label fs-6 fw-bold text-dark mb-3">المحافظة
                                                    المستهدفة </label>
                                                <div>
                                                    <select class="form-select fw-bold" data-control="select2"
                                                        name="BENEFICIARY_REGION_IN" dir="rtl"
                                                        data-placeholder="المحافظة المستهدفة" data-allow-clear="true">

                                                        <option value="">اختر ..</option>
                                                        @foreach ($PROJECT_AREA as $x)
                                                        <option value="{{ $x->id }} ">{{ $x->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <a href="javascript:;" data-repeater-delete
                                                    class="btn btn-flex btn-sm btn-light-danger mt-3 mt-md-9">
                                                    <i class="fas fa-flag-checkered"><span class="path1"></span>
                                                        <span class="path2"></span><span class="path3">

                                                        </span><span class="path4"></span><span
                                                            class="path5"></span></i>
                                                    حذف
                                                </a>


                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Form group-->

                            <!--begin::Form group-->
                            <div class="form-group">
                                <a href="javascript:;" id="repeater-button_edu" data-repeater-create class="btn btn-dark">
                                    <i class="bi bi-chat-square-text-fill fs-4 me-2"></i>
                                    اضافة المزيد
                                </a>
                            </div>
                            <!--end::Form group-->
                        </div>
                        <!--end::Repeater-->


             <div class="separator separator-content border-dark my-10 mb-8"><span
                                    class="w-150px fw-bold text-danger">الشروط</span></div>


                            <div class="col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">شروط البرنامج</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" name="EMPLOYMENT_CONDITIONS[]"
                                        id="EMPLOYMENT_CONDITIONS" dir="rtl" data-placeholder="شروظ البرنامج"
                                        data-allow-clear="true" multiple="multiple">
                                        <option value="">اختر ..</option>
                                        @foreach ($EMPLOYMENT_CONDITIONS as $x)
                                        <option value="{{ $x->id }} ">{{ $x->condition_text}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


































                        </div>
                        <!--end::Row-->



























                        <!--begin::Actions-->
                        <div class="text-center mb-0  ">

                            <!--<button id="kt_docs_formvalidation_text_submit" type="submit" class="btn btn-primary">
                                    <span class="indicator-label">
                                        Validation Form
                                    </span>
                                    <span class="indicator-progress">
                                        Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>-->

                            <button type="submit"
                                class="btn btn-primary font-weight-bold mr-2" name="save_project_submit2" id="save_project_submit2">حفظ البيانات</button>
                        </div>
                        <!--end::Actions-->

                    </div>
                    <!--end::Wrapper-->
                    <!--end::Form-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>



    </div>
    <!--end::Layout-->
</form>
@endsection

{{-- Styles Section --}}
@section('styles')
<style>
.tox-tinymce {
    border-radius: 0.475rem !important;
    height: 200px !important;
}

.tox:not([dir="rtl"]) {
    direction: rtl !important;
    text-align: right !important;
}

.separator.separator-content {
    display: flex;
    align-items: center;
    border-bottom: 0;
    border-bottom-color: currentcolor;
    text-align: center;
}

.separator {
    display: block;
    height: 0;
    border-bottom: 1px solid var(--bs-border-color);
}

.my-15 {
    margin-top: 3.75rem !important;
    margin-bottom: 3.75rem !important;
}

.border-dark {
    --bs-border-opacity: 1;
    border-color: rgba(var(--bs-dark-rgb), var(--bs-border-opacity)) !important;
}

.separator.separator-content.border-dark::before,
.separator.separator-content.border-dark::after {
    border-color: #071437 !important;
}

.separator.separator-content::before {
    margin-right: 1.25rem;
}

.separator.separator-content::before,
.separator.separator-content::after {
    content: " ";
    width: 50%;
    border-bottom: 1px solid var(--bs-border-color);
}

.separator.separator-content.border-dark::before,
.separator.separator-content.border-dark::after {
    border-color: #071437 !important;
}

.separator.separator-content::before,
.separator.separator-content::after {
    content: " ";
    width: 50%;
    border-bottom: 1px solid #071437;
}
</style>
@endsection
@section('scripts')


<script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}"></script>

<script src="{{ asset('assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>




<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>


















<script src="{{ asset('assets/js/custom/documentation/forms/formrepeater.bundle.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/module/woker_j.js') }}?t={{ config('global.ver.version_all') }}"></script>

<script>
$(document).ready(function() {
    $(".form-select").select2({
        placeholder: "Select a state",
        allowClear: true
    });
});


$("#repeater-button_edu").click(function() {
    setTimeout(function() {

        $(".form-select").select2({
            placeholder: "Select a state",
            allowClear: true
        });

    }, 100);
});
$("#repeater-button").click(function() {
    setTimeout(function() {

        $(".form-select").select2({
            placeholder: "Select a state",
            allowClear: true
        });

    }, 100);
});


$('#kt_docs_repeater_advanced_edu').repeater({
    initEmpty: false,

    defaultValues: {
        'text-input': 'foo'
    },

    show: function() {
        $(this).slideDown();

        $(this).find('[data-kt-repeater="select2"]').select2();

        $(this).find('[data-kt-repeater="datepicker"]').flatpickr();

        new Tagify(this.querySelector('[data-kt-repeater="tagify"]'));
    },

    hide: function(deleteElement) {
        $(this).slideUp(deleteElement);
    },




    isFirstItemUndeletable: true,

    ready: function() {
        // Init select2
        $('[data-kt-repeater="select2"]').select2();

        // Init flatpickr
        $('[data-kt-repeater="datepicker"]').flatpickr();

        // Init Tagify
        new Tagify(document.querySelector('[data-kt-repeater="tagify"]'));
    }
});



$('#kt_docs_repeater_advanced').repeater({
    initEmpty: false,

    defaultValues: {
        'text-input': 'foo'
    },

    show: function() {
        $(this).slideDown();

        $(this).find('[data-kt-repeater="select2"]').select2();

        $(this).find('[data-kt-repeater="datepicker"]').flatpickr();

        new Tagify(this.querySelector('[data-kt-repeater="tagify"]'));
    },

    hide: function(deleteElement) {
        $(this).slideUp(deleteElement);
    },




    isFirstItemUndeletable: true,

    ready: function() {
        // Init select2
        $('[data-kt-repeater="select2"]').select2();

        // Init flatpickr
        $('[data-kt-repeater="datepicker"]').flatpickr();

        // Init Tagify
        new Tagify(document.querySelector('[data-kt-repeater="tagify"]'));
    }
});


/*
var options1 = {selector: '#PROJECT_DESCRIPTION_IN',
    directionality: 'rtl',
      resize: false,
  theme: 'silver',
  max_height: 2,
    max_width: 500,
          min_height: 100,

        setup: function (editor) {
        editor.on('change', function () {
            tinymce.triggerSave();
        });
    },
        plugins: [
    'advlist autolink lists link image charmap print preview anchor',
    'searchreplace visualblocks code fullscreen',
    'insertdatetime media table paste code help wordcount'
  ],
  toolbar: 'undo redo | formatselect | ' +
  'bold italic backcolor | alignleft aligncenter ' +
  'alignright alignjustify | bullist numlist outdent indent | ' +
  'removeformat | help',
  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',

      };

tinymce.init(options1);


var options = {selector: '#PROJECT_IDEA_IN',
    directionality: 'rtl',
        setup: function (editor) {
        editor.on('change', function () {
            tinymce.triggerSave();
        });
    },
  max_height: 2,
    max_width: 500,
      min_height: 100,
  plugins: [
    'advlist autolink lists link image charmap print preview anchor',
    'searchreplace visualblocks code fullscreen',
    'insertdatetime media table paste code help wordcount'
  ],
  toolbar: 'undo redo | formatselect | ' +
  'bold italic backcolor | alignleft aligncenter ' +
  'alignright alignjustify | bullist numlist outdent indent | ' +
  'removeformat | help',
  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',

      };


tinymce.init(options);*/


tinymce.init({
    selector: '#PROJECT_DESCRIPTION_IN',
    language: 'ar',

    directionality: 'rtl',
    //plugins: 'autoresize',
    //   autoresize_overflow_padding: 5,
    //  autoresize_bottom_margin: 25s,
    //   autoresize_overflow_padding: 5,
    //autoresize_bottom_margin: 25,

    // height: 50,
    //     min_height: 50,
    //max_height: 50,
    //width: 800,
    //   width: "100",
    //max-height: "100",
    // plugins: "autoresize",
    resize: false,
    setup: function(editor) {
        editor.on('change', function() {
            tinymce.triggerSave();
        });
    },

    /*   plugins: [
    'advlist autolink lists link image charmap print preview anchor',
    'searchreplace visualblocks code fullscreen',
    'insertdatetime media table paste code help wordcount'
  ],*/
    //height: 20,

    toolbar: 'undo redo | formatselect | ' +
        'bold italic backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | help',
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',

});


tinymce.init({
    selector: '#PROJECT_IDEA_IN',
    language: 'ar',

    directionality: 'rtl',
    setup: function(editor) {
        editor.on('change', function() {
            tinymce.triggerSave();
        });
    },
    height: 100,
    plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
    ],
    toolbar: 'undo redo | formatselect | ' +
        'bold italic backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | help',
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',

});
</script>

<script>
/*
$('#kt_datepicker').datepicker({
    todayHighlight: true,
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>',
    },
    format: 'dd-mm-yyyy',

});*/
</script>







@endsection
