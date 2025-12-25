
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

    </div>

    <form class="kt-form kt-form--label-right" enctype="multipart/form-data" id="boew_project" name="boew_project"
        accept-charset="utf-8" method="post" action="{{ route('projects.tbl') }}" enctype="multipart/form-data"
        accept-charset="utf-8">

        @csrf
        <div class="d-flex flex-column flex-lg-row">

            <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">

                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card body-->
                <div class="card-body px-1">

                        <!--begin::Form-->
                        <!--begin::Wrapper-->
                        <div class="mb-0">

                            <!--begin::Row-->
                        <div class="row gx-5 mb-5">

                                                            <div class=" col-12 col-lg-5 col-md-12 col-sm-12  mb-5">
                                <label for="PROJECT_NAME_IN_V" class="  form-label fs-6 fw-bold text-dark mb-3">اسم
                                    المشروع</label>
                                <!--begin::Input group-->
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"> <i
                                                class="fas fa-flag-checkered fa-fw text-dark"></i></span></div>

                                    <input type="text" name="PROJECT_NAME_IN_V" id="PROJECT_NAME_IN_V"
                                        class="form-control fw-bold " placeholder="اسم المشروع" autocomplete="off" />
                                </div>
                            </div>

           <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">الممول</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" id="FINANCIER_IN_V"
                                        name="FINANCIER_IN_V" dir="rtl" data-placeholder="الممول">
                                        <option value="">اختر ..</option>
                                        @foreach ($CONT_TYPE_ID as $x)
                                        <option value="{{ $x->cont_id }} ">{{ $x->cont_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>





              <div class=" col-12 col-lg-4 col-md-12 col-sm-12  mb-5">

                                <label for="project_name" class="  form-label fs-6 fw-bold text-dark mb-3">تاريخ
                                    التسجيل</label>
                                <div class="input-daterange input-group" id="kt_datepicker">
                                    <input type="text" class="form-control input_date_" name="START_DATE_IN_V"
                                        id="START_DATE_IN_V" placeholder="من" data-col-index="5" />
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i
                                                class="fas fa-align-justify fa-fw text-dark"></i></span>
                                    </div>
                                    <input type="text" class="form-control input_date_" name="END_DATE_IN_V"
                                        id="END_DATE_IN_V" placeholder="إلى" data-col-index="5" />
                                </div>
                            </div>


                            <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">جهات التشغيل</label>
                                <div>
                                    <select class="form-select fw-bold" data-control="select2" name="SIDE_ID_IN_V[]"
                                        id="SIDE_ID_IN_V" dir="rtl" data-placeholder="جهات التشغيل"
                                        data-allow-clear="true" multiple="multiple">
                                        <option value="">اختر ..</option>
                                        @foreach ($CONT_TYPE_ID as $x)
                                        <option value="{{ $x->cont_id }} ">{{ $x->cont_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>








                                    <div class="col-12 col-lg-3 col-md-12 col-sm-12   mb-5" style="padding-top: 2rem !important;">
                                        <a onclick="view_all_project()" class="btn btn-primary btn-primary--icon" id="kt_search">
                                            <span>
                                                <i class="la la-search"></i>
                                                <span>بحث</span>
                                            </span>
                                        </a>
                                        &nbsp;&nbsp;
                                    <button type="button" class="btn btn-secondary btn-secondary--icon" name="refresh" id="refresh" {{--id="kt_reset"--}}>
						<span>
							<i class="la la-close"></i>
							<span>إعادة تعيين</span>
						</span>
                                    </button>
                                    </div>







                                 <!--   <div class="row mt-8">
                                        <div class="col-lg-12">
                                            <a class="btn btn-primary btn-primary--icon" id="kt_search">
                                                <span>
                                                    <i class="la la-search"></i>
                                                    <span>بحث</span>
                                                </span>
                                            </a>
                                            &nbsp;&nbsp;
                                            <button class="btn btn-secondary btn-secondary--icon" id="kt_reset">
                                                <span>
                                                    <i class="la la-close"></i>
                                                    <span>Reset</span>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                -->









                                <!--end::Col-->
                            </div>
                            <!--end::Row-->


                          <!--  <div class="mb-0 w-150px">


                                <a onclick="view_all_project()" id="kt_docs_formvalidation_text_submit"
                                    class="btn btn-primary active   font-weight-bold mr-2" name="submitButton">بحث
                                    البيانات</a>
                            </div>-->

                            <!--<div class="row mt-8">
                                <div class="col-lg-12">
                                    <a class="btn btn-primary btn-primary--icon" id="kt_search">
                                        <span>
                                            <i class="la la-search"></i>
                                            <span>بحث</span>
                                        </span>
                                    </a>
                                    &nbsp;&nbsp;
                                    <button class="btn btn-secondary btn-secondary--icon" id="kt_reset">
                                        <span>
                                            <i class="la la-close"></i>
                                            <span>Reset</span>
                                        </span>
                                    </button>
                                </div>
                            </div>-->
                            <!--end::Actions-->

                        </div>
                        <!--end::Wrapper-->
                        <!--end::Form-->
                        <div id="result_project_tbl" name="result_project_tbl">



                        </div>
















                    </div>
                    <!--end::Card body-->

                </div>
                <!--end::Card-->

            </div>



        </div>
        <!--end::Layout-->
    </form>







     <div class="modal fade  " tabindex="-1" id="view_prim_const_m">
<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل</h5>
                       <!--begin::Close-->
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




     <div class="modal fade  " tabindex="-1" id="view_prim_const_sm">
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








    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
<script type="text/javascript" src="{{ asset('assets/module/woker_j.js') }}?t={{ config('global.ver.version_all') }}"></script>














<script>
view_all_project();
    function del_project(id) {
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
                    url: "{{ route('projects.del_project') }}",
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
                        // view_all_project();

                        if (resp.status == false) {
                            document.documentElement.scrollTop = 0;
                            swal.fire('خطأ', resp
                                .message);
                        } else {
                            swal.fire('تم الحذفبنجاح', resp
                                .message);
                            view_all_project();
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
