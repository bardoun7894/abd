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
    <form action="{{route('projects.store')}}" method="post" enctype="multipart/form-data" autocomplete="off">
    @csrf

    <!--begin::Layout-->
        <div class="d-flex flex-column flex-lg-row">

            <!--begin::Content-->
            <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card body-->
                    <div class="card-body p-12">
                        <!--begin::Form-->
                        <!--begin::Wrapper-->
                        <div class="mb-0">
                        
                                    <!--begin::Row-->
                                    <div class="row gx-10 mb-5">
                                        <!--begin::Col-->
                                        <div class="col-lg-12">
                                            <!--begin::Input group-->
                                         
                                            <div class="row">
                                                <div class="col-6 col-md-3 mb-5">
                                                    <label for="EMP_NO" class="form-label fs-6 fw-bolder text-gray-700 mb-3">رقم المشروع 
                                                        </label>
                                                    <!--begin::Input group-->
                                                    <div class="input-group">
                                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-id-card"></i></span></div>

                                                        <input type="number" name="emp_no" id="EMP_NO"
                                                               class="form-control form-control-solid" required
                                                               placeholder="رقم المشروع "/>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3 mb-5">
                                                    <label for="NAME" class="form-label fs-6 fw-bolder text-gray-700 mb-3">اسم
                                                        المشروع</label>
                                                    <!--begin::Input group-->
                                                    <div class="input-group">
                                                        <div class="input-group-prepend"><span class="input-group-text">  <i class="fa fa-user-edit"></i></span></div>

                                                        <input type="text" name="name" id="NAME"
                                                               class="form-control form-control-solid"
                                                               placeholder="اسم المشروع"  autocomplete="off"/>
                                                    </div>
                                                </div>



                                                <div class="col-6 col-md-3 mb-5">
                                                    <label for="GENDER" class="form-label fs-6 fw-bolder text-gray-700 mb-3">الجنس
                                                    </label>
                                                    <!--begin::Input group-->
                                                    <div class="">
                                                        <input type="text" name="gender" id="GENDER"
                                                               class="form-control form-control-solid"
                                                               placeholder="الجنس "/>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3 mb-5">
                                                    <label for="SOCIAL_STATUS" class="form-label fs-6 fw-bolder text-gray-700 mb-3">الحالة الاجتماعية
                                                        </label>
                                                    <!--begin::Input group-->
                                                    <div class="">
                                                        <input type="text" name="social_status" id="SOCIAL_STATUS"
                                                               class="form-control form-control-solid"
                                                               placeholder="الحالة الاجتماعية"/>
                                                    </div>
                                                </div>


                                                <!--end::Input group-->
                                                <div class="col-6 col-md-3 mb-5">
                                                    <label for="EMAIL" class="form-label fs-6 fw-bolder text-gray-700 mb-3">البريد الإلكتروني</label>
                                                    <!--begin::Input group-->
                                                    <div class="mb-5 input-group">
                                                            <div class="input-group-prepend"><span class="input-group-text">  <i class="fa fa-envelope"></i></span></div>

                                                            <input type="email" name="email" id="EMAIL"
                                                               class="form-control form-control-solid" autocomplete="off"
                                                               placeholder="البريد الإلكتروني"/>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3 mb-5">
                                                    <label for="MOBILE" class="form-label fs-6 fw-bolder text-gray-700 mb-3">رقم الموبايل</label>
                                                    <!--begin::Input group-->
                                                    <div class="mb-5 input-group">
                                                        <div class="input-group-prepend"><span class="input-group-text">  <i class="fa fa-phone"></i></span></div>

                                                        <input type="tel" name="mobile" id="MOBILE"
                                                               class="form-control form-control-solid"
                                                               placeholder="رقم الموبايل"/>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3 mb-5">
                                                    <label for="CURRENT_ADDRESS" class="form-label fs-6 fw-bolder text-gray-700 mb-3">العنوان</label>
                                                    <!--begin::Input group-->
                                                    <div class="mb-5 input-group">
                                                        <div class="input-group-prepend"><span class="input-group-text">  <i class="fa fa-location-arrow"></i></span></div>

                                                        <input type="text" name="address" id="CURRENT_ADDRESS"
                                                               class="form-control form-control-solid"
                                                               placeholder="العنوان" />
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3 mb-5">
                                                    <label for="PASSWORD" class="form-label fs-6 fw-bolder text-gray-700 mb-3">كلمة المرور</label>
                                                    <!--begin::Input group-->
                                                    <div class="mb-5 input-group">
                                                        <div class="input-group-prepend"><span class="input-group-text">  <i class="fa fa-key"></i></span></div>

                                                        <input type="password" name="password" id="PASSWORD"
                                                               class="form-control form-control-solid" 
                                                               placeholder="كلمة المرور" autocomplete="off"/>
                                                    </div>
                                                </div>


                                            </div>




                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                          
                                <style>
                                    .tab-pane .form-group {
                                        border-bottom-width: 1px;
                                        border-bottom-style: dashed;
                                        border-bottom-color: #eff2f5;
                                        padding-top: 1.25rem;
                                        padding-bottom: 1.25rem;
                                    }
                                    
                                </style>
                             

                      
                            <!--begin::Actions-->
                            <div class="mb-0 w-150px">

                                <button type="submit" class="btn btn-primary w-100" id="kt_invoice_submit_button">
                                    حفظ
                                </button>
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
            <!--end::Content-->

        </div>
        <!--end::Layout-->
    </form>
@endsection

{{-- Styles Section --}}
@section('styles')


@endsection
@section('scripts')

    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>

@endsection




