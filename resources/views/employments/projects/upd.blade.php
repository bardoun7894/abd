<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }
    .tox-notifications-container{
    display:none !important;
}
    </style>


<script>
document.addEventListener('focusin', (e) => {
  if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
    e.stopImmediatePropagation();
  }
});
</script>
<?php
/*
dd(project);


+"project_id": "208"
  +"project_category": null
  +"project_name": "sds"
  +"start_date": "2023-07-10 00:00:00"
  +"end_date": "2023-07-10 00:00:00"
  +"financier": "95"
  +"project_description": null
  +"project_idea": null
  +"created_at": "2023-07-10 04:01:02"
  +"created_by": "413346578"
  +"actual_start_date": "2023-07-04 00:00:00"
  +"target_duration": "3"
  +"project_budget": "2"
  +"updated_at": null
  +"updated_by": null
  +"project_no": "9"
  +"status": "15006001"
  +"is_deleted": "0"
  +"currency": "15001001"
  +"actual_end_date": "2023-07-03 00:00:00"
  +"auto_close": null
  +"partner": "97"*/





?>


<form id="upd_project_data" name="upd_project_data" class="form" action="{{route('projects.updstore')}}"
    method="post" enctype="multipart/form-data" autocomplete="off">
                        @csrf


                        <input name="PROJECT_ID_IN" id="PROJECT_ID_IN" value="{{$project->project_id}}" im-insert="true"
                        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
                        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="PROJECT_ID_IN"
                        aria-describedby="basic-addon1">
            <input name="status" id="status" value="{{$project->status}}" im-insert="true"
                        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
                        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="status"
                        aria-describedby="basic-addon1">

              <!--begin::Layout-->
    <div class="d-flex flex-column flex-lg-row">

        <!--begin::Content-->

        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">


            <div class="card">
                <div class="card-body ">
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


                    <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10"
                        id="errorBox_project" style="display: none !important">
                        <i class="ki-duotone ki-search-list fs-2hx text-success me-4 mb-5 mb-sm-0"><span
                                class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-column pe-0 pe-sm-10" id="displayErrors_project">
                            <h5 class="mb-1">This is an alert</h5>
                            <span></span>
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
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"> <i
                                                class="fas fa-flag-checkered fa-fw text-dark"></i></span></div>

                                    <input type="text" name="PROJECT_NAME_IN" id="PROJECT_NAME_IN" value="{{$project->project_name}}"
                                        class="form-control fw-bold " placeholder="اسم المشروع" autocomplete="off" />
                                </div>
                            </div>


                            <div class=" col-12 col-lg-4 col-md-12 col-sm-12  mb-5">
                                <label for="project_name" class="  form-label fs-6 fw-bold text-dark mb-3">تاريخ
                                    التسجيل</label>
                                <div class="input-daterange input-group" id="kt_datepicker">
                                    <input type="text" class="form-control input_date_" name="START_DATE_IN" value="{{$project->start_date}}"
                                        id="START_DATE_IN" placeholder="من" data-col-index="5" />
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i
                                                class="fas fa-align-justify fa-fw text-dark"></i></span>
                                    </div>
                                    <input type="text" class="form-control input_date_" name="END_DATE_IN" value="{{$project->end_date}}"
                                        id="END_DATE_IN" placeholder="إلى" data-col-index="5" />
                                </div>
                            </div>


                            <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">الممول</label>
                                <div>
                                    <select class="form-select form-select_u fw-bold" data-control="select2" id="FINANCIER_IN"
                                        name="FINANCIER_IN" dir="rtl" >
                                        <option value="">اختر ..</option>
                                        @foreach ($CONT_TYPE_ID as $x)
                                        <option @selected($project->financier==$x->cont_id) value="{{ $x->cont_id }}  ">{{ $x->cont_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                             <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">الجهة الشريكة</label>
                                <div>
                                    <select class="form-select form-select_u fw-bold" data-control="select2" id="PARTNER_IN"
                                        name="PARTNER_IN" dir="rtl" >
                                        <option value="">اختر ..</option>
                                        @foreach ($CONT_TYPE_ID as $x)
                                        <option @selected($project->partner==$x->cont_id) value="{{ $x->cont_id }}">{{ $x->cont_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-lg-3 col-md-12 col-sm-12   mb-5">
                                <label for="project_name" class="  form-label fs-6 fw-bold text-dark mb-3">تنفيذ المشروع
                                </label>
                                <div class="input-daterange input-group">
                                    <input class="form-control input_date_" placeholder="من" name="ACTUAL_START_DATE_IN"  value="{{$project->actual_start_date}}"
                                        id="ACTUAL_START_DATE_IN" />
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i
                                                class="fas fa-align-justify fa-fw text-dark"></i></span>
                                    </div>
                                    <input class="form-control input_date_" placeholder="إلى" name="ACTUAL_END_DATE_IN" value="{{$project->actual_end_date}}"
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
                                    <input type="number" name="TARGET_DURATION_IN" id="TARGET_DURATION_IN"  value="{{$project->target_duration}}"
                                        class="form-control " placeholder="عدد ايام المشروع" />
                                </div>
                            </div>


                            <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                                <label for="phone" class="form-label fs-6 fw-bold text-dark mb-3">قيمة المشروع</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-donate fa-fw text-dark"></i></span></div>

                                    <input type="number" name="PROJECT_BUDGET_IN" id="PROJECT_BUDGET_IN"  value="{{$project->project_budget}}"
                                        class="form-control " placeholder="قيمة المشروع" />
                                </div>
                            </div>

                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">العملة</label>
                                <div>
                                    <select class="form-select form-select_u fw-bold" data-control="select2" id="CURRENCY_IN"
                                        name="CURRENCY_IN" dir="rtl" >
                                        <option value="">اختر ..</option>
                                        @foreach ($coins as $x)
                                        <option @selected($project->currency==$x->status_id) value="{{ $x->status_id }} ">{{ $x->status_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
                                <label for="PROJECT_DESCRIPTION_IN"
                                    class="  form-label fs-6 fw-bold text-dark mb-3">الهدف
                                    العام</label>
                                <textarea name="PROJECT_DESCRIPTION_IN" id="PROJECT_DESCRIPTION_IN">{{$project->project_description}}</textarea>
                            </div>



                            <div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
                                <label for="PROJECT_IDEA_IN" class="form-label fs-6 fw-bold text-dark mb-3">فكرة
                                    البرنامج
                                </label>
                                <textarea name="PROJECT_IDEA_IN" id="PROJECT_IDEA_IN">{{$project->project_idea}}</textarea>
                            </div>





             <div class="separator separator-content border-dark my-10 mb-8"><span class="w-150px fw-bold text-danger">جهات التشغيل</span></div>


                            <div class="col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">جهات التشغيل</label>
                                <div>
                                    <select class="form-select form-select_u fw-bold" data-control="select2" name="SIDE_ID_IN[]"
                                        id="SIDE_ID_IN" dir="rtl" data-placeholder="جهات التشغيل"
                                    multiple="multiple">
                                        <option value="">اختر ..</option>
                                        @foreach ($CONT_TYPE_ID as $x)
                                           <option value="{{$x->cont_id}}"  {{ in_array($x->cont_id,$EMPLOYMENT_PROJECTS_SIDES) ? 'selected' : '' }}>
                                            {{ $x->cont_name }}
                                        @endforeach
                                    </select>
                                </div>
                            </div>








                            <div class="separator separator-content border-dark my-10 mb-8"><span
                                    class="w-200px fw-bold text-danger">المحافظات المستهدفة</span></div>

                            <div class="col-12 col-lg-5 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">المحافظة المستهدفة </label>
                                <div>
                                    <select class="form-select form-select_u fw-bold" data-control="select2" name="PROJECT_AREA[]"
                                        id="BENEFICIARY_REGION_IN " dir="rtl" data-placeholder="المحافظة المستهدفة"
 multiple="multiple">

                                        <option value="">اختر ..</option>
                                        @foreach ($PROJECT_AREA as $x)
                                        <option value="{{ $x->id}}"  {{ in_array($x->id,$PROJECT_AREA_LIST) ? 'selected' : '' }}>
                                            {{ $x->name }}
                                        </option>


                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-lg-5 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">المنطقة</label>
                                <div>
                                    <select class="form-select form-select_u fw-bold" data-control="select2" id="PROJECT_AREA_DEPT"
                                        name="PROJECT_AREA_DEPT[]" dir="rtl" data-placeholder="المنطقة" multiple="multiple">
                                        @foreach ($PROJECT_AREA_DEPT as $x)
                                        <option value="{{ $x->id}}"  {{ in_array($x->id,$EMPLOYMENT_PROJECTS_AREA_DET) ? 'selected' : '' }}>
                                            {{ $x->name }}
                                        @endforeach
                                    </select>
                                </div>
                            </div>




                            <div class="separator separator-content border-dark my-10 mb-8"><span
                                    class="w-150px fw-bold text-danger">المؤهل العلمي</span></div>









                        <div id="kt_docs_repeater_advanced_edu">
                            <div class="form-group">

<?php
if (isset($EMPLOYMENT_BENEFICIARIES)){
$COUNT_EMPLOYMENT_BENEFICIARIES= count($EMPLOYMENT_BENEFICIARIES);
$i=1;

if($COUNT_EMPLOYMENT_BENEFICIARIES==0){
$BENEFICIARY_ID="";$project_id="";$beneficiary_type="";$beneficiary_sub_type="";$beneficiary_sub_type="";
$beneficiary_gender="";$beneficiary_count="";$beneficiary_salary="";$beneficiary_currency="";$beneficiary_region="";

?>
                                <div data-repeater-list="kt_docs_repeater_advanced_edu">
                                    <div data-repeater-item>
                                        <div class="form-group row mb-5">



                                                 <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5" style="display:none">
                                <label for="SALARY_IN" class="form-label fs-6 fw-bold text-dark mb-3">ID</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-dollar-sign fa-fw text-dark"></i></span></div>

                                    <input type="number" name="BENEFICIARY_ID" id="BENEFICIARY_ID" class="form-control "  value="{{$BENEFICIARY_ID}}"
                                        placeholder="ID" />
                                </div>
                            </div>





                                                       <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                                <label class=" form-label fs-6 fw-bold text-dark mb-3">المؤهل
                                                    المستفدين</label>
                                                <div>

                                                    <select class="form-select form-select_u fw-bold " data-control="select2"
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
                                    <select class="form-select form-select_u fw-bold" data-control="select2"
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
                                                    <select class="form-select form-select_u fw-bold" data-control="select2"
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
                                    <select class="form-select form-select_u fw-bold" data-control="select2"
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
                                                    <select class="form-select form-select_u fw-bold" data-control="select2"
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
                                <?php }  else if($COUNT_EMPLOYMENT_BENEFICIARIES!=0){
$ix=0;
foreach ($EMPLOYMENT_BENEFICIARIES as $x) {


$BENEFICIARY_ID="";$project_id="";$beneficiary_type="";$beneficiary_sub_type="";
$beneficiary_gender="";$beneficiary_count="";$beneficiary_salary="";$beneficiary_currency="";$beneficiary_region="";

$BENEFICIARY_ID=$x->id;
$project_id=$x->project_id;
$beneficiary_type=$x->beneficiary_type;
$beneficiary_sub_type=$x->beneficiary_sub_type;
$beneficiary_gender=$x->beneficiary_gender;
$beneficiary_count=$x->beneficiary_count;
$beneficiary_salary=$x->beneficiary_salary;
$beneficiary_currency=$x->beneficiary_currency;
$beneficiary_region=$x->beneficiary_region;


?>








                                        <div data-repeater-list="kt_docs_repeater_advanced_edu" id='repetr_{{$BENEFICIARY_ID}}'>
                                    <div data-repeater-item>
                                        <div class="form-group row mb-5">



                                                 <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5" style="display:none">
                                <label for="SALARY_IN" class="form-label fs-6 fw-bold text-dark mb-3">ID</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-dollar-sign fa-fw text-dark"></i></span></div>

                                    <input type="number" name="BENEFICIARY_ID" id="BENEFICIARY_ID" class="form-control "  value="{{$BENEFICIARY_ID}}"
                                        placeholder="ID" />
                                </div>
                            </div>





                                                       <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                                <label class=" form-label fs-6 fw-bold text-dark mb-3">المؤهل
                                                    المستفدين</label>
                                                <div>

                                                    <select class="form-select form-select_u fw-bold " data-control="select2"
                                                        data-placeholder="المؤهل العلمي" name="BENEFICIARY_TYPE_IN"
                                                        dir="rtl">
                                                        <option value="">اختر ..</option>
                                                        @foreach ($type_in as $x)
                                                        <option @selected($beneficiary_type==$x->status_id) value="{{ $x->status_id }} ">{{ $x->status_name}}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>


                                                                        <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">التخصص</label>
                                <div>
                                    <select class="form-select form-select_u fw-bold" data-control="select2"
                                        name="BENEFICIARY_SUB_TYPE_IN" dir="rtl" data-placeholder="التخصص">
                                        <option value="">اختر ..</option>
                                        @foreach ($sub_type_in as $x)
                                        <option  @selected($beneficiary_sub_type==$x->status_id)  value="{{ $x->status_id }} ">{{ $x->status_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>



                                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                                <label class=" form-label fs-6 fw-bold text-dark mb-3">جنس
                                                    المستهدفين</label>
                                                <div>
                                                    <select class="form-select form-select_u fw-bold" data-control="select2"
                                                        name="BENEFICIARY_GENDER_IN" dir="rtl"
                                                        data-placeholder="جنس المستفدين" data-allow-clear="true">

                                                        <option value="">اختر ..</option>
                                                        @foreach ($SEX as $x)
                                                        <option @selected($beneficiary_gender==$x->status_id)  value="{{ $x->status_id }} ">{{ $x->status_name}}</option>
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

                                                    <input type="number" name="BENEFICIARY_COUNT_IN" value="{{$beneficiary_count}}"
                                                        id="BENEFICIARY_COUNT_IN " class="form-control "
                                                        placeholder="عدد المستفدين" />
                                                </div>
                                            </div>




                            <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                                <label for="SALARY_IN" class="form-label fs-6 fw-bold text-dark mb-3">الراتب</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-dollar-sign fa-fw text-dark"></i></span></div>

                                    <input type="number" name="BENEFICIARY_SALARY_IN" id="BENEFICIARY_SALARY_IN" class="form-control " value="{{$beneficiary_salary}}"
                                        placeholder="الراتب" />
                                </div>
                            </div>





                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">العملة</label>
                                <div>
                                    <select class="form-select form-select_u fw-bold" data-control="select2"
                                        name="BENEFICIARY_CURRENCY_IN" dir="rtl" data-placeholder="العملة">
                                        <option value="">اختر ..</option>
                                        @foreach ($coins as $x)
                                        <option @selected($beneficiary_currency==$x->status_id) value="{{ $x->status_id }} ">{{ $x->status_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                                <label class=" form-label fs-6 fw-bold text-dark mb-3">المحافظة
                                                    المستهدفة </label>
                                                <div>
                                                    <select class="form-select form-select_u fw-bold" data-control="select2"
                                                        name="BENEFICIARY_REGION_IN" dir="rtl"
                                                        data-placeholder="المحافظة المستهدفة" data-allow-clear="true">

                                                        <option value="">اختر ..</option>
                                                        @foreach ($PROJECT_AREA as $x)
                                                        <option  @selected($beneficiary_region==$x->id)  value="{{ $x->id }} ">{{ $x->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <a  onclick="del_comm_time(<?php echo $BENEFICIARY_ID?>)" data-repeater-delete
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










                                                                <?php } }  } ?>





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
                                    <select class="form-select fw-bold form-select_u" data-control="select2" name="EMPLOYMENT_CONDITIONS[]"
                                        id="EMPLOYMENT_CONDITIONS" dir="rtl" data-placeholder="شروظ البرنامج"
                                        data-allow-clear="true" multiple="multiple">
                                        <option value="">اختر ..</option>
                                        @foreach ($EMPLOYMENT_CONDITIONS as $x)
                                                                                <option value="{{ $x->id}}"  {{ in_array($x->id,$employment_conditions_LIST) ? 'selected' : '' }}>
                                            {{ $x->condition_text }}

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

                            <button type="submit" id="kt_docs_submitsss"
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
                </form>

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
   <script type="text/javascript" src="{{ asset('assets/module/woker_j.js') }}?t={{ config('global.ver.version_all') }}"></script>

<script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
<script src="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.js') }}"></script>
<script src="{{ asset('assets/js/custom/documentation/forms/formrepeater.bundle.js') }}"></script>
<script src="{{ asset('assets/js/custom/documentation/documentation.js') }}"></script>
<script src="{{ asset('assets/js/custom/documentation/search.js') }}"></script>


  <!--  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>-->

    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

   <script src="https://cdn.jsdelivr.net/npm/@tinymce/tinymce-jquery@1/dist/tinymce-jquery.min.js"></script>

<script>
function del_comm_time_________(id) {
    $(document).ready(function() {
      /*  var str = id.split("_").pop();
        var default_id = parseInt(str);
        var str = parseInt(str) - 1;
        $('#btnAdd_data_comm_' + str).css('display', '');*/
        alert(id)
    });
}

    function del_comm_time(id) {
        swal.fire({
       //     title: 'حذف',
            text: 'هل انت متأكد من الحذف',
            icon: 'warning',
           /* showCancelButton: true,
            confirmButtonText: 'تأكيد الحذف',
            cancelButtonText: 'الغاء الامر',*/
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
                    url: "{{ route('projects.del_project_bref') }}",
                    'type': 'POST',
                    'dataType': 'json',
                    'async': false,

                    'data': {
                        id: id,
                    },
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                    'success': function(resp) {

                        if (resp.status == false) {
                            document.documentElement.scrollTop = 0;
                            swal.fire('خطأ', resp
                                .message);

                                 //       $('#repetr_' + id).css('display', 'none');

                        } else {
                            swal.fire('تم الحذفبنجاح', resp
                                .message);

                                        $(this).slideUp(id);
        $('#repetr_' + id).css('display', 'none');

                           // view_all_project();
                        }

                    }
                });

            } else if (result.dismiss === 'cancel') {

                swal.fire('الغاء الامر', 'خطأ');
            }
        });
    }


$(document).ready(function() {
    $(".form-select_u").select2({
        //placeholder: "Select a state",
      //  allowClear: true,
             //       width: 'resolve',
            dropdownParent: $('#view_prim_const_m .modal-content')

    });
});


$("#repeater-button_edu").click(function() {
    setTimeout(function() {

        $(".form-select_u").select2({
           /* placeholder: "Select a state",
            allowClear: true*/
        });

    }, 100);
});
$("#repeater-button").click(function() {
    setTimeout(function() {

        $(".form-select_u").select2({
        /*    placeholder: "Select a state",
            allowClear: true*/
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


$("#view_prim_const_m").on("shown.bs.modal", function() {

  //tinyMCE.get("editor").focus();
  tinymce.init({
selector: '#PROJECT_DESCRIPTION_IN',
language: 'ar',

directionality: 'rtl',
resize: false,
setup: function(editor) {
editor.on('change', function() {
tinymce.triggerSave();
});
},



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

});


      $('.input_date_').flatpickr({
           format : 'dd-mm-yyyy',
                "locale": "ar",
               // weekNumbers: true,

        });
</script>









