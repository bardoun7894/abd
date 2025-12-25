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

<form id="save_workers" id="save_workers" name="save_workers" class="form" action="{{route('projects.store')}}"
    method="post" enctype="multipart/form-data" autocomplete="off">

    @csrf

    <!--begin::Layout-->
    <div class="d-flex flex-column flex-lg-row">

        <!--begin::Content-->

        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">


            <div class="card">
                <div class="card-body px-1">

                    <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10"
                        id="errorBox_worker" style="display: none !important">
                        <i class="ki-duotone ki-search-list fs-2hx text-success me-4 mb-5 mb-sm-0"><span
                                class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-column pe-0 pe-sm-10" id="displayErrors_worker">
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

                                    <input type="text" name="PROJECT_NAME_IN" id="PROJECT_NAME_IN" class="form-control fw-bold "
                                        placeholder="اسم المشروع" autocomplete="off" />
                                </div>
                            </div>


                            <div class=" col-12 col-lg-4 col-md-12 col-sm-12  mb-5">

                                <label for="worker_name" class="  form-label fs-6 fw-bold text-dark mb-3">تاريخ
                                    التسجيل</label>
                                <div class="input-daterange input-group" id="kt_datepicker">
                                    <input type="text" class="form-control input_date_" name="START_DATE_IN" id="START_DATE_IN"
                                        placeholder="من" data-col-index="5" />
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i
                                                class="fas fa-align-justify fa-fw text-dark"></i></span>
                                    </div>
                                    <input type="text" class="form-control input_date_" name="END_DATE_IN" id="END_DATE_IN"
                                        placeholder="إلى" data-col-index="5" />
                                </div>
                            </div>



                             <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">الممول</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" id="FINANCIER_IN"
                                        name="FINANCIER_IN" dir="rtl" data-placeholder="الممول">
                                        <option value="">اختر ..</option>
                                        @foreach ($list_job as $x)
                                                        <option value="{{ $x->status_id }} ">{{ $x->status_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4 col-md-12 col-sm-12   mb-5">
                                <label for="worker_name" class="  form-label fs-6 fw-bold text-dark mb-3">تنفيذ المشروع
                                </label>

                                <div class="input-daterange input-group">
                                    <input class="form-control input_date_" placeholder="من" name="ACTUAL_START_DATE_IN"
                                        id="ACTUAL_START_DATE_IN" />
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-align-justify fa-fw text-dark"></i></span>
                                    </div>
                                    <input class="form-control input_date_" placeholder="إلى" name="ACTUAL_END_DATE_IN"
                                        id="ACTUAL_END_DATE_IN" />
                                </div>
                            </div>

              



                       

                     <!--       <div class=" col-12 col-lg-5 col-md-12 col-sm-12  mb-5">
                                <label for="worker_name" class="  form-label fs-6 fw-bold text-dark mb-3">المنطقة
                                    المستهدفة بالتفصيل</label>

                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"> <i
                                                class="fas fa-map-marked fa-fw text-dark"></i></span></div>

                                    <input type="text" name="worker_name" id="worker_name" class="form-control "
                                        placeholder="المنطقة المستهدفة بالتفصيل" value="" autocomplete="off" />
                                </div>
                            </div>-->



                            <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                                <label for="TARGET_DURATION_IN" class="form-label fs-6 fw-bold text-dark mb-3">عدد ايام المشروع
                                    </label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-stopwatch-20 fa-fw text-dark"></i></span></div>

                                    <input type="number" name="TARGET_DURATION_IN" id="TARGET_DURATION_IN" class="form-control "
                                        placeholder="عدد ايام المشروع" />
                                </div>
                            </div>


                            <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                                <label for="phone" class="form-label fs-6 fw-bold text-dark mb-3">قيمة المشروع</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-donate fa-fw text-dark"></i></span></div>

                                    <input type="number" name="PROJECT_BUDGET_IN" id="PROJECT_BUDGET_IN" class="form-control "
                                        placeholder="قيمة المشروع" />
                                </div>
                            </div>

                         <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">العملة</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" id="CURRENCY_IN" name="CURRENCY_IN"
                                        dir="rtl" data-placeholder="العملة">
                                        <option value="">اختر ..</option>
                                        @foreach ($coins as $x)
                                                        <option value="{{ $x->status_id }} ">{{ $x->status_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            






                         <div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
                                <label for="PROJECT_DESCRIPTION_IN" class="  form-label fs-6 fw-bold text-dark mb-3">الهدف
                                    العام</label>
                                <textarea name="PROJECT_DESCRIPTION_IN" id="PROJECT_DESCRIPTION_IN"></textarea>
                            </div>



                         <div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
                                <label for="PROJECT_IDEA_IN" class="  form-label fs-6 fw-bold text-dark mb-3">فكرة البرنامج
                                    </label>
                                <textarea name="PROJECT_IDEA_IN" id="PROJECT_IDEA_IN"></textarea>
                            </div>


                                      <!--    <div class=" col-12 col-lg-8 col-md-12 col-sm-12  mb-5">
                                <label for="worker_name" class="  form-label fs-6 fw-bold text-dark mb-3">الهدف
                                    العام</label>

                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"> <i
                                                class="far fa-gem fa-fw text-dark"></i></span></div>

                                    <input type="text" name="PROJECT_DESCRIPTION_IN" id="PROJECT_DESCRIPTION_IN" class="form-control "
                                        placeholder="الهدف العام" value="" autocomplete="off" />
                                </div>
                            </div>-->


                          <!--  <div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
                                <label for="worker_name" class="  form-label fs-6 fw-bold text-dark mb-3">فكرة
                                    البرنامج</label>

                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"> <i
                                                class="far fa-lightbulb fa-fw text-dark"></i></span></div>

                                    <input type="text" name="PROJECT_IDEA_IN" id="PROJECT_IDEA_IN" class="form-control "
                                        placeholder="فكرة البرنامج" value="" autocomplete="off" />
                                </div>
                            </div>-->
                            
                            
                            
                            

<!--<div id="editor"></div>-->



                          <!--     <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">جهات التشغيل</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" id="list_job"
                                        name="list_job" dir="rtl" data-placeholder="جهات التشغيل">
                                        <option value="">اختر ..</option>
                                        @foreach ($list_job as $x)
                                                        <option value="{{ $x->status_id }} ">{{ $x->status_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="col-12 col-lg-3 col-md-12 col-sm-12   mb-5">
                                <label for="phone" class="form-label fs-6 fw-bold text-dark mb-3">عد المستفدين من
                                    المشروع</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-stopwatch-20 fa-fw text-dark"></i></span></div>

                                    <input type="number" name="phone" id="phone" class="form-control "
                                        placeholder="عدد المستفدين" />
                                </div>
                            </div>


                            <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                                <label for="phone" class="form-label fs-6 fw-bold text-dark mb-3">الراتب</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-dollar-sign fa-fw text-dark"></i></span></div>

                                    <input type="number" name="phone" id="phone" class="form-control "
                                        placeholder="الراتب" />
                                </div>
                            </div>



     <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">المحافظة المستهدفة </label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" id="PROJECT_AREA" name="PROJECT_AREA"
                                        dir="rtl" data-placeholder=" المحافظة المستهدفة ">
                                        <option value="">اختر ..</option>
                                        @foreach ($PROJECT_AREA as $x)
                                                        <option value="{{ $x->id }} ">{{ $x->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">المنطقة</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" id="PROJECT_AREA_DEPT" name="PROJECT_AREA_DEPT"
                                        dir="rtl" data-placeholder="المنطقة">
                                        <option value="">اختر ..</option>
                                        @foreach ($PROJECT_AREA_DEPT as $x)
                                                        <option value="{{ $x->id }} ">{{ $x->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="col-12 col-lg-12 col-md-12 col-sm-12 mb-5">
                                <label for="noremarkste" class="form-label fs-6 fw-bold text-dark mb-3">الشروط</label>

                                <textarea class="form-control " rows="1" name="remarks" id="remarks"
                                    placeholder="اكتب ملاحظة .."></textarea>
                            </div>





                            <div class="mb-0">
                                <label class="form-label">Basic example</label>
                                <input class="form-control form-control-solid flatpickr" placeholder="Pick date rage"
                                    id="kt_daterangepicker_1" />
                            </div>
-->








                            <!--end::Col-->
                        </div>
                        <!--end::Row-->


                        <!--begin::Actions-->
                        <div class="mb-0 w-150px">

                            <!--<button id="kt_docs_formvalidation_text_submit" type="submit" class="btn btn-primary">
                                    <span class="indicator-label">
                                        Validation Form
                                    </span>
                                    <span class="indicator-progress">
                                        Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>-->

                            <button type="submit" id="kt_docs_formvalidation_text_submit"
                                class="btn btn-primary font-weight-bold mr-2" name="submitButton">حفظ البيانات</button>
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
.ck-editor__editable_inline {
    min-height: 100px;
}
</style>
@endsection
@section('scripts')
<script type="text/javascript" src="{{ asset('assets/module/woker_j.js') }}?t={{ config('global.ver.version_all') }}"></script>


<script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
<script src="{{ asset('assets/js/custom/apps/invoices/create.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>




<script src="{{asset('assets/form-validation/plugin-bootstrap5/lib/umd/index.js')}}"></script>

<script src="https://unpkg.com/axios/dist/axios.min.js"></script>


<script src="https://npmcdn.com/flatpickr/dist/flatpickr.min.js"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/ru.js"></script>

<script src="https://npmcdn.com/flatpickr/dist/l10n/ar.ts.js"></script>


<script>

	ClassicEditor
		.create( document.querySelector( '#PROJECT_DESCRIPTION_IN' ) , {
        // The language code is defined in the https://en.wikipedia.org/wiki/ISO_639-1 standard.
        
    language: 'ar',
                    additionalLanguages: 'all',
                                // The UI will be English.
            ui: 'ar',

            // But the content will be edited in Arabic.
            content: 'ar'    } )
		.catch( error => {
			console.error( error );
		} );
		
		
		
		
			ClassicEditor
				.create( document.querySelector( '#PROJECT_IDEA_IN' ) , {
        // The language code is defined in the https://en.wikipedia.org/wiki/ISO_639-1 standard.
        language: 'ar',
                    additionalLanguages: 'all',
                                // The UI will be English.
            ui: 'ar',

            // But the content will be edited in Arabic.
            content: 'ar'
    } )

		.catch( error => {
			console.error( error );
		} );
</script>

<script>
flatpickr(".flatpickr", {
    enableTime: true,
    "locale": "ru",
    //     inline: true, // show the calendar inline

    weekNumbers: true // show week numbers
});
$("#kt_daterangepicker_1sssssssssssssss").flatpickr();
$("#kt_daterangepicker_1ssssssss").daterangepicker({
    singleDatePicker: true,

    showDropdowns: true,
    minYear: 1901,
    maxYear: parseInt(moment().format('YYYY'), 10)
}, );



$("#kt_daterangepicker_1xxxxxxxxxxxx").daterangepicker({

    "locale": {
        "format": "DD/MM/YYYY",
        "separator": " - ",
        "applyLabel": "Aplicar",
        "cancelLabel": "Cancelar",
        "fromLabel": "De",
        "toLabel": "Até",
        "customRangeLabel": "Custom",
        "daysOfWeek": [
            "Dom",
            "Seg",
            "Ter",
            "Qua",
            "Qui",
            "Sex",
            "Sáb"
        ],
        "monthNames": [
            "Janeiro",
            "Fevereiro",
            "Março",
            "Abril",
            "Maio",
            "Junho",
            "Julho",
            "Agosto",
            "Setembro",
            "Outubro",
            "Novembro",
            "Dezembro"
        ],
        "firstDay": 0
    }
});


$('#kt_datepicker').datepicker({
    todayHighlight: true,
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>',
    },
    format: 'dd-mm-yyyy',

});
</script>







@endsection