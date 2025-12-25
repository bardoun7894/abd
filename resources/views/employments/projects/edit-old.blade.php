@extends('layouts.app')
@section('title',"$page_title")
@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif
    <form action="{{route('users.update',['user'=>$user->id])}}" method="post" enctype="multipart/form-data" autocomplete="off">
    @csrf
    @method('PUT')
    <!--begin::Layout-->
        <div class="d-flex flex-column flex-lg-row">
            <!--begin::Content-->
            <div class="flex-lg-row-fluid mb-10 mb-lg-0 me-lg-7 me-xl-10">
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card body-->
                    <div class="card-body p-12">
                        <!--begin::Form-->
                        <!--begin::Wrapper-->
                        <div class="mb-0">
                            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fw-bolder mb-10 fs-6">
                                <li class="nav-item">
                                    <a class="nav-link active text-gray-700" data-bs-toggle="tab" href="#basic_data">البيانات الأساسية</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-gray-700" data-bs-toggle="tab" href="#roles">الفئة والصلاحيات</a>
                                </li>
                            </ul>
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="basic_data" role="tabpanel">
                            <!--begin::Row-->
                            <div class="row gx-10 mb-5">
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Input group-->
                                    <div class="row">
                                        <div class="col-12 col-md-6 mb-5">
                                            <label for="name" class="form-label fs-6 fw-bolder text-gray-700 mb-3">اسم
                                                العضو</label>
                                            <!--begin::Input group-->
                                            <div class="">
                                                <input type="text" name="name" id="name" value="{{$user->name}}"
                                                       class="form-control form-control-solid" required
                                                       placeholder="اسم العضو"/>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 mb-5">
                                            <label for="nick_name" class="form-label fs-6 fw-bolder text-gray-700 mb-3">اسم
                                                المستخدم</label>
                                            <!--begin::Input group-->
                                            <div class="">
                                                <input type="text" name="nick_name" id="nick_name" value="{{$user->nick_name}}"
                                                       class="form-control form-control-solid" required
                                                       placeholder="اسم المستخدم"/>
                                            </div>
                                        </div>
                                    </div>

                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="mb-5">
                                        <label for="pass" class="form-label fs-6 fw-bolder text-gray-700 mb-3">كلمة المرور</label>
                                        <!--begin::Input group-->
                                        <div class="mb-5">
                                            <input type="password" name="pass" id="pass"
                                                   class="form-control form-control-solid"
                                                   placeholder="كلمة المرور" autocomplete="off"/>
                                        </div>
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="mb-5">
                                        <label for="email" class="form-label fs-6 fw-bolder text-gray-700 mb-3">البريد الإلكتروني</label>
                                        <!--begin::Input group-->
                                        <div class="mb-5">
                                            <input type="email" name="email" id="email" required value="{{$user->email}}"
                                                   class="form-control form-control-solid" autocomplete="off"
                                                   placeholder="البريد الإلكتروني"/>
                                        </div>
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="mb-5">
                                        <label for="mob" class="form-label fs-6 fw-bolder text-gray-700 mb-3">رقم الهاتف</label>
                                        <!--begin::Input group-->
                                        <div class="mb-5">
                                            <input type="tel" name="mob" id="mob" required value="{{$user->mobile}}"
                                                   class="form-control form-control-solid"
                                                   placeholder="رقم الهاتف"/>
                                        </div>
                                    </div>
                                    <!--end::Input group-->

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
                                    <!--begin::Input group-->
                                    <div class="mb-5">
                                        <!--begin::Label-->
                                        <label class="form-label fw-bolder fs-6 text-gray-700">الفئة</label>
                                        <!--end::Label-->
                                        <!--begin::Select-->
                                        <select id="role_id" name="role_id" aria-label="Select a Timezone"
                                                data-control="select2" data-hide-search="true"
                                                data-placeholder="اختر الفئة" required
                                                class="form-select form-select-solid">
                                            <option value=""></option>
                                            @foreach($roles as $role)
                                                <option @if($role->id == $user->role_id) selected @endif value="{{$role->id}}">{{$role->name}}</option>
                                            @endforeach
                                        </select>
                                        <!--end::Select-->
                                    </div>
                                    <div class="text-gray-600 fw-bold @if($user->role_id != 2) d-none @endif " id="roles__">
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
                                                    <input type="checkbox" id="all_orders" @if($user->hasPermission('order.view')||$user->hasPermission('order.create')||$user->hasPermission('order.update')||$user->hasPermission('order.delete')) checked @endif class="form-check-input all_ch"/>
                                                    <span class="form-check-label">الطلبات</span>

                                                </label>
                                            </div>
                                            <div id="Orders_menu" class="col-lg-9 row">
                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('order.view')) checked @endif name="order_view" value="1"/>
                                                        <span class="form-check-label">عرض</span>

                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('order.create')) checked @endif name="order_create" value="1"/>
                                                        <span class="form-check-label">إنشاء</span>

                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('order.update')) checked @endif name="order_update" value="1"/>
                                                        <span class="form-check-label">تعديل</span>
                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('order.delete')) checked @endif name="order_delete" value="1"/>
                                                        <span class="form-check-label">حذف</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group Section_ row">
                                            <div class="checkbox-list col-lg-3">
                                                <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                    <input type="checkbox" id="all_projects" @if($user->hasPermission('project.view')||$user->hasPermission('project.create')||$user->hasPermission('project.update')||$user->hasPermission('project.delete')) checked @endif class="form-check-input all_ch"/>
                                                    <span class="form-check-label">المشاريع</span>

                                                </label>
                                            </div>
                                            <div id="Projects_menu" class="col-lg-9 row">
                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('project.view')) checked @endif name="project_view" value="1"/>
                                                        <span class="form-check-label">عرض</span>

                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('project.create')) checked @endif name="project_create" value="1"/>
                                                        <span class="form-check-label">إنشاء</span>

                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('project.update')) checked @endif name="project_update" value="1"/>
                                                        <span class="form-check-label">تعديل</span>

                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('project.delete')) checked @endif name="project_delete" value="1"/>
                                                        <span class="form-check-label">حذف</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group Section_ row">
                                            <div class="checkbox-list col-lg-3">
                                                <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                    <input type="checkbox" id="all_gifts" @if($user->hasPermission('gift.view')||$user->hasPermission('gift.create')||$user->hasPermission('gift.update')||$user->hasPermission('gift.delete')) checked @endif class="form-check-input all_ch"/>
                                                    <span class="form-check-label">الهدايا</span>

                                                </label>
                                            </div>
                                            <div id="Gifts_menu" class="col-lg-9 row">
                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('gift.view')) checked @endif name="gift_view" value="1"/>
                                                        <span class="form-check-label">عرض</span>

                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('gift.create')) checked @endif name="gift_create" value="1"/>
                                                        <span class="form-check-label">إنشاء</span>

                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('gift.update')) checked @endif name="gift_update" value="1"/>
                                                        <span class="form-check-label">تعديل</span>
                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('gift.delete')) checked @endif name="gift_delete" value="1"/>
                                                        <span class="form-check-label">حذف</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group Section_ row">
                                            <div class="checkbox-list col-lg-3">
                                                <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                    <input type="checkbox" id="all_blogs" @if($user->hasPermission('blog.view')||$user->hasPermission('blog.create')||$user->hasPermission('blog.update')||$user->hasPermission('blog.delete')) checked @endif class="form-check-input all_ch"/>
                                                    <span class="form-check-label">المدونة</span>

                                                </label>
                                            </div>
                                            <div id="Blogs_menu" class="col-lg-9 row">
                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('blog.view')) checked @endif name="blog_view" value="1"/>
                                                        <span class="form-check-label">عرض</span>

                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('blog.create')) checked @endif name="blog_create" value="1"/>
                                                        <span class="form-check-label">إنشاء</span>

                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('blog.update')) checked @endif name="blog_update" value="1"/>
                                                        <span class="form-check-label">تعديل</span>
                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('blog.delete')) checked @endif name="blog_delete" value="1"/>
                                                        <span class="form-check-label">حذف</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group Section_ row">
                                            <div class="checkbox-list col-lg-3">
                                                <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                    <input type="checkbox" id="all_users" @if($user->hasPermission('user.view')||$user->hasPermission('user.create')||$user->hasPermission('user.update')||$user->hasPermission('user.delete')) checked @endif class="form-check-input all_ch"/>
                                                    <span class="form-check-label">الأعضاء</span>

                                                </label>
                                            </div>
                                            <div id="Users_menu" class="col-lg-9 row">
                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('user.view')) checked @endif name="user_view" value="1"/>
                                                        <span class="form-check-label">عرض</span>

                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('user.create')) checked @endif name="user_create" value="1"/>
                                                        <span class="form-check-label">إنشاء</span>

                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('user.update')) checked @endif name="user_update" value="1"/>
                                                        <span class="form-check-label">تعديل</span>

                                                    </label>
                                                </div>

                                                <div class="checkbox-list col-lg-3">
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                        <input type="checkbox" class="form-check-input ch" @if($user->hasPermission('user.delete')) checked @endif name="user_delete" value="1"/>
                                                        <span class="form-check-label">حذف</span>

                                                    </label>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <!--end::Input group-->
                                </div>

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

            <!--begin::Sidebar-->
            <div class="flex-lg-auto min-w-lg-300px">
                <!--begin::Card-->
                <div class="card" data-kt-sticky="true" data-kt-sticky-name="invoice"
                     data-kt-sticky-offset="{default: false, lg: '200px'}"
                     data-kt-sticky-width="{lg: '250px', lg: '300px'}" data-kt-sticky-left="auto"
                     data-kt-sticky-top="150px" data-kt-sticky-animation="false" data-kt-sticky-zindex="95">
                    <!--begin::Card body-->
                    <div class="card-body p-10">
                        <div class="mb-5">
                            <div>
                                <label class="form-label fw-bolder fs-6 text-gray-700 mb-3">صورة العضو</label>
                            </div>
                            <!--begin::Image input-->
                            <div class="image-input image-input-outline" data-kt-image-input="true"
                                 style="background-image: url(/assets/media/avatars/blank.png)">
                                <!--begin::Image preview wrapper-->
                                <div class="image-input-wrapper w-200px h-200px"
                                     style="background-image: @if($user->img == Null) url(/assets/media/avatars/blank.png) @else url('{{asset($user->img)}}') @endif"></div>
                                <!--end::Image preview wrapper-->

                                <!--begin::Edit button-->
                                <label
                                    class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-white shadow"
                                    data-kt-image-input-action="change"
                                    data-bs-toggle="tooltip"
                                    data-bs-dismiss="click"
                                    title="إضافة صورة">
                                    <i class="bi bi-pencil-fill fs-7"></i>

                                    <!--begin::Inputs-->
                                    <input type="file" name="image" accept=".png, .jpg, .jpeg"/>
                                    <input type="hidden" name="avatar_remove"/>
                                    <!--end::Inputs-->
                                </label>
                                <!--end::Edit button-->

                                <!--begin::Cancel button-->
                                <span
                                    class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-white shadow"
                                    data-kt-image-input-action="cancel"
                                    data-bs-toggle="tooltip"
                                    data-bs-dismiss="click"
                                    title="إلغاء">
         <i class="bi bi-x fs-2"></i>
     </span>
                                <!--end::Cancel button-->

                                <!--begin::Remove button-->
                                <span
                                    class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-white shadow"
                                    data-kt-image-input-action="remove"
                                    data-bs-toggle="tooltip"
                                    data-bs-dismiss="click"
                                    title="حذف الصورة">
         <i class="bi bi-x fs-2"></i>
     </span>
                                <!--end::Remove button-->
                            </div>
                            <!--end::Image input-->
                        </div>

                        <!--begin::Separator-->
                        <div class="separator separator-dashed mb-8"></div>
                        <!--end::Separator-->
                        <!--begin::Actions-->
                        <div class="mb-0">

                            <button type="submit" class="btn btn-primary w-100" id="kt_invoice_submit_button">
                                حفظ
                            </button>
                        </div>
                        <!--end::Actions-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Sidebar-->
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
        var checkAllRequests = document.querySelector("#all_orders")
        var checkRequests = document.querySelectorAll("#Orders_menu .ch")

        checkAllRequests.addEventListener('click', function() {
            checkRequests.forEach(function(e) {
                e.checked = checkAllRequests.checked
            })
        });


        var checkAlltypeRequests = document.querySelector("#all_projects")
        var checktypeRequests = document.querySelectorAll("#Projects_menu .ch")

        checkAlltypeRequests.addEventListener('click', function() {
            checktypeRequests.forEach(function(e) {
                e.checked = checkAlltypeRequests.checked
            })
        });

        var checkAllPaths = document.querySelector("#all_gifts")
        var checkPaths = document.querySelectorAll("#Gifts_menu .ch")

        checkAllPaths.addEventListener('click', function() {
            checkPaths.forEach(function(e) {
                e.checked = checkAllPaths.checked
            })
        });

        var checkAllDocuments = document.querySelector("#all_blogs")
        var checkDocuments = document.querySelectorAll("#Blogs_menu .ch")

        checkAllDocuments.addEventListener('click', function() {
            checkDocuments.forEach(function(e) {
                e.checked = checkAllDocuments.checked
            })
        });


        var checkAllUsers = document.querySelector("#all_users")
        var checkUsers = document.querySelectorAll("#Users_menu .ch")

        checkAllUsers.addEventListener('click', function() {
            checkUsers.forEach(function(e) {
                e.checked = checkAllUsers.checked
            })
        });

    </script>
@endsection




