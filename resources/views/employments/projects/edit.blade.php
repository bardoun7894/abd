@extends('layouts.app')
@section('module',"وزارة العمل ")
@section('sub',"المشاريع ")
@section('title',"$page_title")
@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif
    <form action="{{route('projects.update',['project'=>$user->id])}}" method="post" enctype="multipart/form-data" autocomplete="off">
    @csrf
    @method('PUT')
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
                            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fw-bolder mb-8 fs-6">
                                <li class="nav-item">
                                    <a class="nav-link active text-gray-700" data-bs-toggle="tab" href="#basic_data">البيانات الأساسية</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-gray-700" data-bs-toggle="tab" href="#roles">القسم والصلاحيات</a>
                                </li>
                            </ul>
                            <div class="d-flex mb-10">
                            <div class="fs-6 fw-bolder me-10"><span style="color: #d3224c">الاسم: </span> <span>{{$user->name}}</span></div>
                            <div class="fs-6 fw-bolder me-10"><span style="color: #d3224c">هوية رقم: </span> <span>{{$user->ssn}}</span></div>
                            <div class="fs-6 fw-bolder "><span style="color: #d3224c">الرقم الوظيفي: </span> <span>{{$user->emp_no}}</span></div>
                            </div>
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="basic_data" role="tabpanel">
                                    <!--begin::Row-->
                                    <div class="row gx-10 mb-5">
                                        <!--begin::Col-->
                                        <div class="col-lg-12">
                                            <!--begin::Input group-->
                                            <div class="row">


                                                <!--end::Input group-->
                                                <div class="col-6 col-md-3 mb-5">
                                                    <label for="EMAIL" class="form-label fs-6 fw-bolder text-gray-700 mb-3">البريد الإلكتروني</label>
                                                    <!--begin::Input group-->
                                                    <div class="mb-5">
                                                        <input type="email" name="email" id="EMAIL"  value="{{$user->email}}"
                                                               class="form-control form-control-solid"
                                                               placeholder="البريد الإلكتروني"/>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3 mb-5">
                                                    <label for="MOBILE" class="form-label fs-6 fw-bolder text-gray-700 mb-3">رقم الموبايل</label>
                                                    <!--begin::Input group-->
                                                    <div class="mb-5">
                                                        <input type="tel" name="mobile" id="MOBILE"  value="{{$user->mobile}}"
                                                               class="form-control form-control-solid"
                                                               placeholder="رقم الموبايل"/>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3 mb-5">
                                                    <label for="CURRENT_ADDRESS" class="form-label fs-6 fw-bolder text-gray-700 mb-3">العنوان</label>
                                                    <!--begin::Input group-->
                                                    <div class="mb-5">
                                                        <input type="text" name="address" id="CURRENT_ADDRESS" value="{{$user->address}}"
                                                               class="form-control form-control-solid"
                                                               placeholder="العنوان" />
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3 mb-5">
                                                    <label for="PASSWORD" class="form-label fs-6 fw-bolder text-gray-700 mb-3">كلمة المرور</label>
                                                    <!--begin::Input group-->
                                                    <div class="mb-5">
                                                        <input type="password" name="password" id="PASSWORD"
                                                               class="form-control form-control-solid"
                                                               placeholder="كلمة المرور" />
                                                    </div>
                                                </div>


                                            </div>

                                            <!--begin::Actions-->

                                            <!--end::Actions-->


                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <style>
                                    .tab-pane .form-group {
                                        border-bottom-width: 1px;
                                        border-bottom-style: dashed;
                                        border-bottom-color: #eff2f5;
                                        padding-top: 1.25rem;
                                        padding-bottom: 1.25rem;
                                    }
                                </style>
                                <div class="tab-pane fade" id="roles" role="tabpanel">
                                    <!--begin::Row-->
                                    <div class="row gx-10 mb-5">
                                        <!--begin::Col-->

                                        <!--begin::Input group-->
                                        <div class="col-6 col-md-6 mb-5">
                                            <!--begin::Label-->
                                            <label class="form-label fw-bolder fs-6 text-gray-700">القسم</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <select id="department_id" name="department_id" aria-label="Select a Timezone"
                                                    data-control="select2" dir="rtl" data-hide-search="true"
                                                    data-placeholder="اختر القسم"
                                                    class="form-select form-select-solid">
                                                <option value=""></option>
                                                @foreach(\App\Models\Department::all() as $item)
                                                    <option @if($item->id == $user->dept_id) selected @endif value="{{$item->id}}">{{$item->name}}</option>
                                                @endforeach
                                            </select>
                                            <!--end::Select-->
                                        </div>
                                        <!--begin::Input group-->
                                        <div class="col-6 col-md-6 mb-5">
                                            <!--begin::Label-->
                                            <label class="form-label fw-bolder fs-6 text-gray-700">المسمى الوظيفي</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <select id="role_id" name="role_id" aria-label="Select a Timezone"
                                                    data-control="select2" dir="rtl" data-hide-search="true"
                                                    data-placeholder="اختر المسمى" required
                                                    class="form-select form-select-solid">
                                                <option value=""></option>
                                                @foreach(\App\Models\Role::all() as $role)
                                                    <option @if($role->id == $user->role_id) selected @endif value="{{$role->id}}">{{$role->name}}</option>
                                                @endforeach

                                            </select>
                                            <!--end::Select-->
                                        </div>
                                        <!--end::Input group-->
                                        <div class="col-lg-12">
                                            <div class="text-gray-600 fw-bold @if($user->role_id == 1) d-none @endif " id="roles__">
                                                <label class="form-label fw-bolder fs-6 text-gray-700">الصلاحيات:</label>

                                                <div class="form-group Section_ checkbox-list">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" name="dashboard" @if($user->hasPermission('dashboard')) checked @endif class="form-check-input ch" id="dashboard" value="dashboard"/>
                                                        <span class="form-check-label">لوحة القيادة (Dashboard)</span>
                                                    </label>
                                                </div>
                                                <div class="form-group Section_ row">
                                                    <div class="checkbox-list col-lg-3">
                                                        <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                            <input type="checkbox" id="all_items" @if($user->hasPermission('item.view')||$user->hasPermission('item.create')||$user->hasPermission('item.update')||$user->hasPermission('item.delete')) checked @endif  class="form-check-input all_ch"/>
                                                            <span class="form-check-label">الأصناف</span>

                                                        </label>
                                                    </div>
                                                    <div id="Items_menu" class="col-lg-9 row">
                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('item.view')) checked @endif name="item_view" value="1"/>
                                                                <span class="form-check-label">عرض</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('item.create')) checked @endif name="item_create" value="1"/>
                                                                <span class="form-check-label">إنشاء</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('item.update')) checked @endif name="item_update" value="1"/>
                                                                <span class="form-check-label">تعديل</span>
                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('item.delete')) checked @endif name="item_delete" value="1"/>
                                                                <span class="form-check-label">حذف</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group Section_ row">
                                                    <div class="checkbox-list col-lg-3">
                                                        <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                            <input type="checkbox" id="all_invoices" @if($user->hasPermission('invoice.view')||$user->hasPermission('invoice.create')||$user->hasPermission('invoice.update')||$user->hasPermission('invoice.delete')) checked @endif  class="form-check-input all_ch"/>
                                                            <span class="form-check-label">الفواتير</span>

                                                        </label>
                                                    </div>
                                                    <div id="Invoices_menu" class="col-lg-9 row">
                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('invoice.view')) checked @endif name="invoice_view" value="1"/>
                                                                <span class="form-check-label">عرض</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('invoice.create')) checked @endif name="invoice_create" value="1"/>
                                                                <span class="form-check-label">إنشاء</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('invoice.update')) checked @endif name="invoice_update" value="1"/>
                                                                <span class="form-check-label">تعديل</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('invoice.delete')) checked @endif name="invoice_delete" value="1"/>
                                                                <span class="form-check-label">حذف</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group Section_ row">
                                                    <div class="checkbox-list col-lg-3">
                                                        <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                            <input type="checkbox" id="all_sides" @if($user->hasPermission('side.view')||$user->hasPermission('side.create')||$user->hasPermission('side.update')||$user->hasPermission('side.delete')) checked @endif  class="form-check-input all_ch"/>
                                                            <span class="form-check-label">الموردين</span>

                                                        </label>
                                                    </div>
                                                    <div id="Sides_menu" class="col-lg-9 row">
                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('side.view')) checked @endif name="side_view" value="1"/>
                                                                <span class="form-check-label">عرض</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('side.create')) checked @endif name="side_create" value="1"/>
                                                                <span class="form-check-label">إنشاء</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('side.update')) checked @endif name="side_update" value="1"/>
                                                                <span class="form-check-label">تعديل</span>
                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('side.delete')) checked @endif name="side_delete" value="1"/>
                                                                <span class="form-check-label">حذف</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group Section_ row">
                                                    <div class="checkbox-list col-lg-3">
                                                        <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                            <input type="checkbox" id="all_comities" @if($user->hasPermission('comity.view')||$user->hasPermission('comity.create')||$user->hasPermission('comity.update')||$user->hasPermission('comity.delete')) checked @endif  class="form-check-input all_ch"/>
                                                            <span class="form-check-label">اللجان</span>

                                                        </label>
                                                    </div>
                                                    <div id="Comities_menu" class="col-lg-9 row">
                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('comity.view')) checked @endif name="Comity_view" value="1"/>
                                                                <span class="form-check-label">عرض</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('comity.create')) checked @endif name="Comity_create" value="1"/>
                                                                <span class="form-check-label">إنشاء</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('comity.update')) checked @endif name="Comity_update" value="1"/>
                                                                <span class="form-check-label">تعديل</span>
                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('comity.delete')) checked @endif name="Comity_delete" value="1"/>
                                                                <span class="form-check-label">حذف</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group Section_ row">
                                                    <div class="checkbox-list col-lg-3">
                                                        <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                            <input type="checkbox" id="all_users" @if($user->hasPermission('user.view')||$user->hasPermission('user.create')||$user->hasPermission('user.update')||$user->hasPermission('user.delete')) checked @endif  class="form-check-input all_ch"/>
                                                            <span class="form-check-label">المستخدمين</span>

                                                        </label>
                                                    </div>
                                                    <div id="Users_menu" class="col-lg-9 row">
                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('user.view')) checked @endif name="user_view" value="1"/>
                                                                <span class="form-check-label">عرض</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('user.create')) checked @endif name="user_create" value="1"/>
                                                                <span class="form-check-label">إنشاء</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('user.update')) checked @endif name="user_update" value="1"/>
                                                                <span class="form-check-label">تعديل</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('user.delete')) checked @endif name="user_delete" value="1"/>
                                                                <span class="form-check-label">حذف</span>

                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group Section_ row">
                                                    <div class="checkbox-list col-lg-3">
                                                        <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                            <input type="checkbox" id="all_constants" @if($user->hasPermission('constant.view')||$user->hasPermission('constant.create')||$user->hasPermission('constant.update')||$user->hasPermission('constant.delete')) checked @endif  class="form-check-input all_ch"/>
                                                            <span class="form-check-label">الثوابت</span>

                                                        </label>
                                                    </div>
                                                    <div id="Constants_menu" class="col-lg-9 row">
                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('constant.view')) checked @endif name="constant_view" value="1"/>
                                                                <span class="form-check-label">عرض</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('constant.create')) checked @endif name="constant_create" value="1"/>
                                                                <span class="form-check-label">إنشاء</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('constant.update')) checked @endif name="constant_update" value="1"/>
                                                                <span class="form-check-label">تعديل</span>

                                                            </label>
                                                        </div>

                                                        <div class="checkbox-list col-lg-2">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('constant.delete')) checked @endif name="constant_delete" value="1"/>
                                                                <span class="form-check-label">حذف</span>

                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>

                            </div>

                            <div class="mb-0 w-150px">

                                <button type="submit" class="btn btn-primary w-100" id="kt_invoice_submit_button">
                                    حفظ
                                </button>
                            </div>
                        </div>
                        <!--end::Wrapper-->
                        <!--end::Form-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>


        <!--end::Layout-->
        </div>
    </form>
@endsection

{{-- Styles Section --}}
@section('styles')


@endsection
@section('scripts')

    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
    <script src="{{ asset('assets/js/custom/apps/invoices/create.js') }}"></script>
    <script src="{{asset('assets/plugins/custom/formrepeater/formrepeater.bundle.js')}}"></script>
    <script>
        $('#role_id').change(function () {
            var optionSelected = $(this).find("option:selected");
            var valueSelected = optionSelected.val();
            if (valueSelected == 2) {
                $('#roles__').removeClass('d-none');
            }else{
                $('#roles__').addClass('d-none');
            }
        });
    </script>
    <script>
        var checkAllItems = document.querySelector("#all_items")
        var checkItems = document.querySelectorAll("#Items_menu .ch")

        checkAllItems.addEventListener('click', function() {
            checkItems.forEach(function(e) {
                e.checked = checkAllItems.checked
            })
        });


        var checkAllInvoices = document.querySelector("#all_invoices")
        var checkInvoices = document.querySelectorAll("#Invoices_menu .ch")

        checkAllInvoices.addEventListener('click', function() {
            checkInvoices.forEach(function(e) {
                e.checked = checkAllInvoices.checked
            })
        });

        var checkAllSides = document.querySelector("#all_sides")
        var checkSides = document.querySelectorAll("#Sides_menu .ch")

        checkAllSides.addEventListener('click', function() {
            checkSides.forEach(function(e) {
                e.checked = checkAllSides.checked
            })
        });

        var checkAllComities = document.querySelector("#all_comities")
        var checkComities = document.querySelectorAll("#Comities_menu .ch")

        checkAllComities.addEventListener('click', function() {
            checkComities.forEach(function(e) {
                e.checked = checkAllComities.checked
            })
        });


        var checkAllUsers = document.querySelector("#all_users")
        var checkUsers = document.querySelectorAll("#Users_menu .ch")

        checkAllUsers.addEventListener('click', function() {
            checkUsers.forEach(function(e) {
                e.checked = checkAllUsers.checked
            })
        });

        var checkAllConstants = document.querySelector("#all_constants")
        var checkConstants = document.querySelectorAll("#Constants_menu .ch")

        checkAllConstants.addEventListener('click', function() {
            checkConstants.forEach(function(e) {
                e.checked = checkAllConstants.checked
            })
        });

    </script>
@endsection




