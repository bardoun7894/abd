@extends('layouts.app')
@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري')
@section('title', "$page_title")
@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif


    <div id="user_reg" class="alert alert-danger d-none"></div>
    <form id="save_emps" name="save_emps" class="form" action="{{ route('dashboard.emps.store') }}"
        enctype="multipart/form-data" autocomplete="off" method="POST">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="mb-10 flex-lg-row-fluid mb-lg-0">
                <div class="card">
                    <div class="px-1 card-body">
                        <div class="p-5 mb-6 alert alert-dismissible d-flex flex-column flex-sm-row w-100"
                        id="errorBox_emp" style="display: none !important">
                        <span class="mb-5 svg-icon svg-icon-2hx svg-icon-light me-4 mb-sm-0">
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
                            <span id="displayErrors_emp" class="mb-2 fw-bolder text-light"></span>
                        </div>
                        <button type="button"
                            class="top-0 m-2 position-absolute position-sm-relative m-sm-0 end-0 btn btn-icon ms-sm-auto"
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
                            <div class="mb-5 row gx-5">

                                <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12"><label for="name"
                                        class="mb-3 form-label required fs-6 fw-bold text-dark">اسم الموظف</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-user-alt fa-fw text-dark"></i></span></div><input
                                            type="text" name="name" id="name"
                                            class="form-control fw-bold text-dark" placeholder="اسم الموظف" value=""
                                            autocomplete="off">
                                    </div>
                                </div>



                                <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12">
                               <div class="form-group" onclick='show_job_cat(1,"{{ route("dashboard.emps.show_job_cat") }}")' data-url="{{ route('dashboard.emps.show_job_cat') }}">
                                       <label for="job"
                                            class="mb-3 form-label required fs-6 fw-bold text-dark">المسمى الوظيفي</label>

                                        <div class="input-group">
                                            <input type="hidden" id="job" name="job" class="form-control"
                                                placeholder="المسمى الوظيفي" data-maxzpsw="0">

                                            <input type="text" readonly id="job_desc" name="job_desc"
                                                class="form-control fw-bold text-dark" placeholder="المسمى الوظيفي">

                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="button">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12">
                                    <label for="email" class="mb-3 form-label required fs-6 fw-bold text-dark">البريد
                                        الإلكتروني</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-at fa-fw text-dark"></i></span></div><input
                                            type="text" name="email" id="email"
                                            class="form-control fw-bold text-dark"
                                            placeholder="البريد الإلكتروني ">
                                    </div>
                                </div>




                                <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12">
                                    <label for="password" class="mb-3 form-label required fs-6 fw-bold text-dark">كلمة
                                        المرور</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                            type="text" name="password" id="password"
                                            class="form-control fw-bold text-info" placeholder="كلمة المرور ">
                                    </div>
                                </div>






                                <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12">
                                    <label for="phone" class="mb-3 form-label required fs-6 fw-bold text-dark">رقم
                                        الجوال</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                            type="text" name="phone" id="phone"
                                            class="form-control fw-bold text-dark" placeholder="رقم الجوال ">
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12 role_per_div" id="role_per_div271" style="display: block !important">
                                    <label for="role_per" class="mb-3 form-label required fs-6 fw-bold text-dark">مجموعة الصلاحية</label>
                                    <div>
                                        <select class="form-select fw-bolder" data-control="select2" id="role_per"
                                            name="role_per" dir="rtl"  data-placeholder="مجموعة الصلاحية" >
                                            <option value="">اختر ..</option>

                                            @foreach ($serach_role_data_all as $x)
                                                <option value="{{ $x->id }} ">{{ $x->role_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12 manager_div" id="manager_div271" style="display:block !important" >
                                    <label class="mb-3 form-label fs-6 fw-bold text-dark">قائد المجموعة</label>
                                    <div>
                                        <select class="form-select fw-bold" data-control="select2" name="manager[]"
                                            id="manager" dir="rtl" data-placeholder="قائد المجموعة"
                                            data-allow-clear="true" multiple="multiple">
                                            <option value="">اختر ..</option>
                                            @foreach ($manager as $x)
                                            <option value="{{ $x->manager_id }} ">{{ $x->manager_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>





                                <div class="mb-5 col-12 col-lg-12 col-md-12 col-sm-12">
                                    <label for="note" class="mb-3 form-label fs-6 fw-bold text-dark" row='1'>الملاحظة
                                    </label>
                                    <textarea name="note" class="form-control fw-bold" id="note" placeholder="الملاحظة"></textarea>
                                </div>







                            </div>
                            <div class="mb-2 d-flex justify-content">
                                <button type="submit" id="kt_docs_formvalidation_text_submit"
                                    class="mr-2 btn btn-primary font-weight-bold" name="submitButton">حفظ
                                    البيانات</button>
                                &nbsp;&nbsp;
                                <button type="reset" class="mr-2 btn btn-light font-weight-bold">تفريغ البيانات</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade" id="view_role_m" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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


@section('styles')
    <style>
        .uppy-Root {
            font-family: inherit !important;
        }

        .uppy-size--md .uppy-Dashboard-note {
            direction: rtl !important;
        }

        a.uppy-Dashboard-poweredBy {
            display: none !important;
        }

        .uppy-Dashboard-close {
            right: -15px !important;
            background-color: #2275d7;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            text-align: center;
            font-size: 30px;
        }

        .uppy-Dashboard-close span {
            top: -3px;
            position: relative;
        }
    </style>


@endsection







@section('scripts')









    <script>
        $('#add_file').on('click', function() {
            var newfield =
                '<div class="form-group row repeat"><div class="input-group"><div class="form-control custom-file"><input type="file" class="form-control custom-file-input" name="files[]" ></div><div class="input-group-append" style="padding: 0.7rem 1rem;"><a class="btn btn-lg btn-danger remove"  ><span><i class="la la-minus" style="color:#fff"></i></span></a></div></div></div>';
            $('#container_file').append(newfield);
        });
        $(document).on('click', '.remove', function() {
            $(this).parent().parent().parent('div').remove();
        });


        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val();
            if (fileName.length > 23) {
                fileName = fileName.substr(0, 11) + "..." + fileName.substr(-10);
            }
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>


    <script type="text/javascript" src="{{ asset('assets/module/emp_j.js') }}?t={{ config('global.ver.version_all') }}">    </script>
    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>


@endsection
