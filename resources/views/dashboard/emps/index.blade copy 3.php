@extends('layouts.app')
@section('module'," التشغيل ")
@section('sub',"المشاريع ")
@section('title',"$page_title")
@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif
    <div id="user_reg" class="alert alert-danger d-none">
dd
    </div>




   <!-- <div class="alert alert-dismissible bg-light-danger border border-danger border-dashed d-flex flex-column flex-sm-row w-100 p-5 mb-10" id="errorBox_worker"
    style="display: none !important">
        <i class="ki-duotone ki-message-text-2 fs-2hx text-danger me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>

        <div class="d-flex flex-column pe-0 pe-sm-10" id="displayErrors_worker">
            <h5 class="mb-1">This is an alert with a dashed border</h5>
            <span>The alert component can be used to highlight certain parts of your page for higher content visibility.</span>
        </div>

        <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
            <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>                    </button>
    </div>-->



    <!--begin::Alert-->
<div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10" id="errorBox_worker"
style="display: none !important">
    <!--begin::Icon-->
    <i class="ki-duotone ki-search-list fs-2hx text-success me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    <!--end::Icon-->

    <!--begin::Wrapper-->
    <div class="d-flex flex-column pe-0 pe-sm-10" id="displayErrors_worker">
        <!--begin::Title-->
        <h5 class="mb-1">This is an alert</h5>
        <!--end::Title-->

        <!--begin::Content-->
        <span>The alert component can be used to highlight certain parts of your page for higher content visibility.</span>
        <!--end::Content-->
    </div>
    <!--end::Wrapper-->

    <!--begin::Close-->
    <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
        <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span class="path2"></span></i>
    </button>
    <!--end::Close-->
</div>
<!--end::Alert-->








    <form autocomplete='off' class="kt-form kt-form--label-right" action="{{ route('dashboard.emps.store') }}" method="post"
        id="save_emps" name="save_emps" enctype="multipart/form-data" accept-charset="utf-8">


            @csrf

            <!--begin::Layout-->
            <div class="d-flex flex-column flex-lg-row">

                <!--begin::Content-->

                <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">

                    <!--begin::Card-->
                    <div class="card">
                        <!--begin::Card body-->
                        <div class="card-body ">
                            <!--begin::Form-->
                            <!--begin::Wrapper-->
                            <div class="mb-0">

                                <!--begin::Row-->
                                <div class="row gx-10 mb-5">
                                    <div class="row ">
                                        <div class=" col-12 col-lg-4 col-md-12 col-sm-12  mb-5">
                                            <label for="emps_name" class="  form-label fs-6 fw-bolder text-dark mb-3">اسم
                                                الموظف</label>

                                            <!--begin::Input group-->
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text"> <i
                                                            class="fa fa-user-edit text-dark"></i></span></div>

                                                <input type="text" name="emps_name" id="emps_name"
                                                    class="form-control form-control-solid" placeholder="اسم الموظف"
                                                    value="" autocomplete="off" />
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                            <label class=" form-label fs-6 fw-bolder text-dark mb-3">الجنس</label>
                                            <div>
                                                <!--<select class="form-select form-select-solid" name="filter_4" id="filter_4">-->
                                                <select class="form-select form-select-solid" data-control="select2"
                                                    id="sex" name="sex" dir="rtl"
                                                    data-placeholder="اختر الجنس" data-allow-clear="true">
                                                    <option value="">اختر ..</option>
                                                    <option value="1">ذكر</option>
                                                    <option value="2">أنثى</option>
                                                </select>
                                            </div>
                                        </div>


                                        <div class="col-12 col-lg-3 col-md-12 col-sm-12   mb-5">
                                            <label for="phone" class="form-label fs-6 fw-bolder text-dark mb-3">رقم
                                                الجوال
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text"><i
                                                            class="fa fa-id-card text-dark"></i></span></div>

                                                <input type="number" name="phone" id="phone"
                                                    class="form-control form-control-solid" placeholder="رقم الجوال " />
                                            </div>
                                        </div>


                                        <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                            <label for="email" class="form-label fs-6 fw-bolder text-dark mb-3">البريد
                                                الإلكتروني</label>
                                            <!--begin::Input group-->
                                            <div class="mb-5 input-group">
                                                <div class="input-group-prepend"><span class="input-group-text"> <i
                                                            class="fa fa-envelope text-dark"></i></span></div>

                                                <input type="email" name="email" id="email"
                                                    class="form-control form-control-solid" autocomplete="off"
                                                    placeholder="البريد الإلكتروني" />
                                            </div>
                                        </div>







                                        <div class="col-12 col-lg-3 col-md-12 col-sm-12   mb-5">
                                            <label for="phone" class="form-label fs-6 fw-bolder text-dark mb-3">الرقم
                                                الوظيفي
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text"><i
                                                            class="fa fa-id-card text-dark"></i></span></div>

                                                <input type="text" name="job_num" id="job_num"
                                                    class="form-control form-control-solid"
                                                    data-inputmask="'alias' : 'integer'" maxlength="20" minlenght="20"
                                                    placeholder="الرقم الوظيفي " />
                                            </div>
                                        </div>



                                        <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                            <label class=" form-label fs-6 fw-bolder text-dark mb-3">الجنس</label>
                                            <div>
                                                <!--<select class="form-select form-select-solid" name="filter_4" id="filter_4">-->
                                                <select class="form-select form-select-solid job_desc" data-control="select2" data-url="{{ route('dashboard.emps.show_job_cat') }}" onchange='show_job_cat(1)'
                                                    id="job_desc" name="job_desc" dir="rtl"
                                                    data-placeholder="اختر المسمى الوظيفي" data-allow-clear="true">
                                                    <option value="">اختر ..</option>
                                                    @foreach ($get_all_job_dept as $x)
                                                        <option value="{{ $x->job_dept_id }} ">{{ $x->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>



                                        <div class="col-12 col-lg-3 col-md-12 col-sm-12   mb-5">
                                            <label for="username" class="form-label fs-6 fw-bolder text-dark mb-3">
                                                اسم المستخدم
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text"><i
                                                            class="fa fa-id-card text-dark"></i></span></div>

                                                <input type="number" name="username" id="username"
                                                    class="form-control form-control-solid" placeholder="اسم المستخدم" />
                                            </div>
                                        </div>


                                        <div class="col-12 col-lg-3 col-md-12 col-sm-12   mb-5">
                                            <label for="username" class="form-label fs-6 fw-bolder text-dark mb-3">رقم
                                                كلمة المرور
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text"><i
                                                            class="fa fa-id-card text-dark"></i></span></div>

                                                <input type="text" name="password" id="password"
                                                    class="form-control form-control-solid" placeholder="كلمة المرور" />
                                            </div>
                                        </div>






                                        <div class="col-12 col-lg-12 col-md-12 col-sm-12 mb-5">
                                            <label for="noremarkste"
                                                class="form-label fs-6 fw-bolder text-dark mb-3">ملاحظات</label>
                                            <!--begin::Input group-->

                                            <textarea class="form-control form-control-solid" rows="1" name="remarks" id="remarks"
                                                placeholder="اكتب ملاحظة .."></textarea>
                                        </div>









                                        <div class="col-lg-12 row" id='change_job' name='change_job'>
                                        </div>




                                    </div>





                                    <!--end::Col-->
                                </div>
                                <!--end::Row-->


                                <!--begin::Actions-->
                                <div class="mb-0 w-150px">

                             <!--       <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#view_prim_const_m">
                                        Launch demo modal
                                    </button>
                                -->


                                    <button type="submit" id="kt_docs_formvalidation_text_submit"
                                        class="btn btn-primary active   font-weight-bold mr-2"
                                        name="submitButton">تسجيل البيانات</button>
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







        <div class="modal fade bd-example-modal-sm" tabindex="-1" id="view_prim_const_m">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modal title</h5>

                        <!--begin::Close-->
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <span class="svg-icon svg-icon-2x"></span>
                        </div>
                        <!--end::Close-->
                    </div>

                    <div class="modal-body">
                        <div id="show_module" name="show_module"> </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>





        <div class="modal fade bd-example-modal-lg" tabindex="-1" id="view_role_m" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modal title</h5>

                        <!--begin::Close-->
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <span class="svg-icon svg-icon-2x"></span>
                        </div>
                        <!--end::Close-->
                    </div>

                    <div class="modal-body">
                        <div id="show_module_role" name="show_module_role"> </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>










        <div class="modal fade bd-example-modal-lg" id="view_prim_const_m2222" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">تعديل</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <div id="show_module" name="show_module"> </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">اغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="view_role_m_____" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">تعديل</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <div id="show_module_role" name="show_module_role"> </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">اغلاق</button>
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








        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
        <script type="text/javascript" src="{{ asset('assets/emp_j.js') }}?t={{ config('global.ver.version_all') }}"></script>



        <script type="text/javascript">
            $(document).ready(function() {

              /*   $('[data-inputmask]').inputmask();
               $(".sex,.city,.region,.nation").select2({
                    width: 'resolve',
                });*/

                $('#add_file').on('click', function() {
                    var newfield =
                        '<div class="form-group row repeat"><div class="input-group mb-3"><div class="custom-file"><input type="file" class="custom-file-input" name="files[]" ><label class="custom-file-label kt-font-primary kt-font-bolder" for="customFile" data-browse="upload"></label></div><div class="input-group-append"><a class="btn btn-sm btn-danger remove"  style="padding: 0.7rem 1rem;"><span><i class="la la-minus" style="color:#fff"></i></span></a></div></div></div>';
                    $('#container_file').append(newfield);
                });
                $(document).on('click', '.remove', function() {
                    $(this).parent().parent().parent('div').remove();
                });
                $(document).on('change', '.custom-file-input', function() {
                    var i = $(this).prev('label').clone();
                    var file = this.files[0].name;
                    $(this).prev('label').text(file);
                    $(this).next('.custom-file-label').addClass("selected").html(file);
                });


            });
        </script>





















    @endsection
