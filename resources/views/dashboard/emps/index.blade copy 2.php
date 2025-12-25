@extends('layouts.dashboard')
@section('title', 'sss')
@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif
    <div id="user_reg" class="alert alert-danger d-none">

    </div>

    <form autocomplete='off' class="kt-form kt-form--label-right" action="{{ route('dashboard.emps.store') }}" method="post"
        id="save_Emps" name="save_Emps" enctype="multipart/form-data" accept-charset="utf-8">

        <form id="save_workers" id="save_workers" name="save_workers" class="form"
            action="{{ route('dashboard.workers.store') }}" enctype="multipart/form-data" autocomplete="off">
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


                                <!--        <div class="col-lg-3">
                                            <div class="form-group "  data-url="{{ route('dashboard.emps.show_job_cat') }}" onclick='show_job_cat(1)'>
                                                <label>المسمى الوظيفي</label>
                                                <div class="input-group">
                                                    <input type="hidden" id="job" name="job"
                                                        class="form-control" placeholder="job_title" data-maxzpsw="0">

                                                    <input type="text" readonly id="job_desc" name="job_desc"
                                                        class="form-control kt-font-dark kt-font-bolder"
                                                        placeholder="job_title">

                                                    <div class="input-group-append">
                                                        <button class="btn btn-primary" type="button">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>-->


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
                                        <a href="javascript:void(0)" id="create_item" class="btn btn-primary er fs-6 px-8 py-4 ms-3" >  <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                                            <span class="svg-icon svg-icon-2">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                                                <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1" transform="rotate(-90 11.364 20.364)" fill="black" />
                                                                                <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="black" />
                                                                            </svg>
                                                                        </span>
                                            <!--end::Svg Icon-->إنشاء صنف</a>

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









    <!-- Modal Add-Edit -->
    <div class="izi-modal d-none"
         data-iziModal-fullscreen="true"
         data-iziModal-title=""
         data-iziModal-icon=""
         data-iziModal-padding="20"
         data-iziModal-autoopen="false"
         data-iziModal-headercolor="#009EF7"
         id="ajax-modal">
        <form class="form_modal" id="Form_" name="Form_">
            <input type="hidden" name="item_id" id="item_id">

        <!--begin::Input group-->
            <div class="row g-9 mb-4">
                <div class="col-md-4 fv-row">
                    <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                        <span class="required">اسم الصنف</span>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">  <i class="fa fa-user-edit"></i></span></div>

                        <!--end::Label-->
                    <input type="text" class="form-control form-control-solid" placeholder="اسم الصنف" id="item_name" name="item_name" />
                    <!--begin::Col-->
                </div>

                    <div id="items_list"></div>
                </div>
                <div class="col-md-4 fv-row">
                    <label class="required fs-6 fw-bold mb-2">التصنيف الفرعي</label>
                    <select class="form-select form-select-solid" data-dropdown-parent="#ajax-modal"  data-control="select2" dir="rtl" id="item_class_sub" name="item_class_sub"  data-placeholder="اختر التصنيف الفرعي">


                    </select>

                </div>


                <!--end::Col-->

            </div>
            <!--end::Input group-->
            <div class="row g-9 mb-4">

                <!--end::Col-->

                <div class="col-md-4 fv-row">
                    <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                        <span class="required">الحد الأدنى</span>
                    </label>
                    <!--end::Label-->
                    <input type="number" class="form-control form-control-solid" placeholder="الحد الأدنى" id="min_qnt" name="min_qnt" />
                    <!--begin::Col-->
                </div>
                <div class="col-md-4 fv-row">
                    <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                        <span class="">الحد الأقصى</span>
                    </label>
                    <!--end::Label-->
                    <input type="number" class="form-control form-control-solid" placeholder="الحد الأقصى" id="max_qnt" name="max_qnt" />
                    <!--begin::Col-->
                </div>
            </div>

            <!--end::Input group-->
            <!--begin::Input group-->
            <div class="d-flex flex-column mb-4">
                <label class="fs-6 fw-bold mb-2">ملاحظات</label>
                <textarea class="form-control form-control-solid" rows="3" name="note1" id="note1" placeholder="اكتب ملاحظة .."></textarea>
            </div>
            <!--end::Input group-->
            <div class="row g-9 mb-6">
                <div class="col-md-2 fv-row " style="margin-top: 60px">
                    <!--begin::Input group-->

                    <!--begin::Switch-->
                    <label class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox"  onclick="chk_toggle()" id="enabled" name="enabled" value="1" checked="checked" />
                        <label class="form-check-label fs-6 fw-bolder text-gray-700" for="enabled">
                            التفعيل
                        </label>
                    </label>
                    <!--end::Switch-->
                </div>
                <div class="col-md-2 fv-row " style="margin-top: 60px">
                    <div class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input" onclick="chk_toggle2()" type="checkbox" name="expired" value="0" id="expired" />
                        <label class="form-check-label fs-6 fw-bolder text-gray-700" for="expired">
                            مؤرخ (تاريخ صلاحية)
                        </label>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" id="btn-save" value="create" class="btn btn-primary me-3">
                    <span class="indicator-label">حفظ</span>
                    <span class="indicator-progress">الرجاء الانتظار...
															<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
                <button data-izimodal-close class="btn btn-light me-3">إلغاء</button>

            </div>

        </form>

    </div>


























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
        <script src="{{ asset('assets/js/custom/modals/new-target.js') }}"></script>

        <script src="{{ asset('assets/js/custom/iziModal/iziModal.min.js')}}"></script>

        <script>
        $('#ajax-modal').iziModal({
            width: 1200,
        });
        $('#create_item').click(function () {
            $('#ajax-modal').removeClass('d-none');
            $('#ajax-modal').iziModal('open');
            $('#ajax-modal').iziModal('setTitle', "إنشاء صنف جديد");
            $('#ajax-modal').iziModal('setIcon', 'fa fa-plus');

        });
        </script>

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
