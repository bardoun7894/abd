<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }

    .tox-notifications-container {
        display: none !important;
    }
</style>





<form id="upd_emps_data" name="upd_emps_data" class="form" action="{{ route('dashboard.emps.updstore') }}" method="post"
    enctype="multipart/form-data" autocomplete="off">
    @csrf


    <input name="id_val" id="id_val" value="{{ $emps->id }}" im-insert="true"
        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="id_val" aria-describedby="basic-addon1">
    <!--begin::Layout-->


    <div class="d-flex flex-column flex-lg-row">

        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">


            <div class="card">
                <div class="card-body ">


                    <div class="alert alert-dismissible   d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                        id="errorBox_emp" style="display: none !important">
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
                            <span id="displayErrors_emp" class="mb-2  fw-bolder text-light"></span>
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




                    <div class="alert alert-dismissible bg-success d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                        id="successBox_emp" style="display: none !important">
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
                            <h4 class="mb-2 text-light">نجاح</h4>
                            <span id="displaysuccess_emp"></span>
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












                            <div class=" col-12 col-lg-5 col-md-12 col-sm-12 mb-5"><label for="name"
                                class="form-label required fs-6 fw-bold text-dark mb-3">اسم الموظف</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-tools fa-fw text-dark"></i></span></div><input
                                        type="text" name="name" id="name" value="{{ $emps->name }}"
                                        class="form-control fw-bold  text-dark" placeholder="اسم الموظف"
                                        autocomplete="off">
                                </div>
                            </div>

                            <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
                                <label for="phone" class="form-label required fs-6 fw-bold text-dark mb-3">البريد
                                    الإلكتروني</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-at fa-fw text-dark"></i></span></div><input
                                        type="text" name="email" id="email"
                                        class="form-control fw-bold text-dark text-info" value="{{ $emps->email }}"
                                        placeholder="البريد الإلكتروني ">
                                </div>
                            </div>





                            <div class=" col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <div class="form-group " onclick='show_job_cat(1,"{{ route("dashboard.emps.show_job_cat") }}")' data-url="{{ route('dashboard.emps.show_job_cat') }}">
                                        <label for="job"
                                             class="form-label required fs-6 fw-bold text-dark mb-3">المسمى الوظيفي</label>

                                         <div class="input-group">
                                             <input type="hidden" id="job" name="job" class="form-control"  value="{{ $emps->emp_job }}"
                                                 placeholder="المسمى الوظيفي" data-maxzpsw="0">

                                             <input type="text" readonly id="job_desc" name="job_desc" value="{{ $emps->j_c_name_ar }}"
                                                 class="form-control fw-bold  text-dark" placeholder="المسمى الوظيفي">

                                             <div class="input-group-append">
                                                 <button class="btn btn-primary" type="button">+</button>
                                             </div>
                                         </div>
                                     </div>
                                 </div>




                                    <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5 role_per_div" id="role_per_div" >
                                    <label for="role_per" class="form-label required  fs-6 fw-bolder  mb-3">مجموعة الصلاحية</label>
                                    <div>
                                        <select class="form-select fw-bolder form-select_u  " data-control="select2" id="role_per"
                                            name="role_per" dir="rtl"  data-placeholder="مجموعة الصلاحية" >
                                            <option value="">اختر ..</option>

                                            @foreach ($serach_role_data_all as $x)
                                                <option @selected($get_role_emp == $x->id)  value="{{ $x->id }} ">{{ $x->role_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="col-12 col-lg-6 col-md-12 col-sm-12 mb-5" id="manager_div">
                                    <label class=" form-label fs-6 fw-bold text-dark mb-3">قائد المجموعة</label>
                                    <div>
                                        <select class="form-select form-select_u fw-bold" data-control="select2" name="manager[]"
                                            id="manager" dir="rtl" data-placeholder="قائد المجموعة"
                                        multiple="multiple">
                                            <option value="">اختر ..</option>
                                            @foreach ($manager as $x)
                                               <option value="{{$x->manager_id}}"  {{ in_array($x->manager_id,$workers_manager) ? 'selected' : '' }}>
                                                {{ $x->manager_name }}
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                            <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label for="phone" class="form-label required fs-6 fw-bold text-dark mb-3">رقم
                                    الجوال</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                        type="text" name="phone" id="phone" value="{{ $emps->phone }}"
                                        class="form-control fw-bold text-dark text-info" placeholder="رقم الجوال ">
                                </div>
                            </div>




                            <div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
                                <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">ملاحظة
                                </label>
                                <textarea name="note" rows='1' class="form-control fw-bold" id="note" placeholder="ملاحظة">{{ $emps->note }}</textarea>
                            </div>

                        </div>







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
                                class="btn btn-primary font-weight-bold mr-2" name="submitButton">حفظ
                                البيانات</button>

                            <!--begin::Overlay Layer-->
                            <div class="overlay-layer bg-dark bg-opacity-5" id='wait_block'
                                style="display: none !important">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <!--end::Overlay Layer-->









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

@section('styles')
    <style>
        .select2-container[dir="rtl"] .select2-selection--single .select2-selection__rendered {
            padding-bottom: 2px;
        }
    </style>

<script type="text/javascript" src="{{ asset('assets/module/emp_j.js') }}?t={{ config('global.ver.version_all') }}">    </script>
<script>
        $(document).ready(function() {
            $(".form-select_u").select2({
                dropdownParent: $('#view_prim_const_m .modal-content')

            });
        });


    </script>
<?php  if($emps->emp_job==1){?>
<script>
    $('#role_per_div').css('display', 'none');
$('#manager_div').css('display', 'none');

</script>
<?php }  ?>

