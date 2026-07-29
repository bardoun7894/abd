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
    <div id="user_reg" class="alert alert-danger d-none">
    </div>
    <form class="kt-form kt-form--label-right" enctype="multipart/form-data" id="boew_project" name="boew_project"
        accept-charset="utf-8" method="post" action="{{ route('dashboard.emps.tbl') }}" enctype="multipart/form-data">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
                <div class="card">
                    <div class="card-body px-1">
                        <div class="mb-0">
                            <div class="row gx-5 mb-5">
                                <div class=" col-12 col-lg-3 col-md-12 col-sm-12  mb-5">
                                    <label for="name" class="form-label  fs-6 fw-bold text-dark mb-3">اسم الموظف</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"> <i
                                                    class="fas fa-user-alt fa-fw fa-fw text-dark"></i></span></div>

                                        <input type="text" name="name_v" id="name_v" class="form-control fw-bold "
                                            placeholder="اسم الموظف" value="" autocomplete="off" />
                                    </div>
                                </div>

                                <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
                                    <label for="phone" class="form-label required fs-6 fw-bold text-dark mb-3">البريد
                                        الإلكتروني</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-at fa-fw text-dark"></i></span></div><input
                                            type="text" name="email_v" id="email_v"
                                            class="form-control fw-bold text-dark text-info"
                                            placeholder="البريد الإلكتروني ">
                                    </div>
                                </div>



                                <div class="col-12 col-lg-3 col-md-12 col-sm-12   mb-5"
                                    style="padding-top: 2rem !important;">
                                    <a onclick="view_all_emp()" class="btn btn-primary btn-primary--icon"
                                        id="kt_search">
                                        <span>
                                            <i class="la la-search"></i>
                                            <span>بحث</span>
                                        </span>
                                    </a>
                                    &nbsp;&nbsp;
                                    <button type="button" class="btn btn-secondary btn-secondary--icon" name="refresh"
                                        id="refresh" {{-- id="kt_reset" --}}>
                                        <span>
                                            <i class="la la-close"></i>
                                            <span>إعادة تعيين</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="result_emp_tbl" name="result_emp_tbl">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade  " tabindex="-1" id="view_prim_const_m" data-bs-focus="false">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل</h5>
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

    <div class="modal fade  " tabindex="-1" id="view_prim_const_sm" data-bs-focus="false">
        <div class="modal-dialog  modal-dialog-centered mw-550px ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل</h5>
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
    <div class="modal fade  " tabindex="-1" id="view_role_m" data-bs-focus="false" data-bs-focus="false">

        <div class="modal-dialog  modal-dialog-centered mw-550px ">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">تعديل</h5>
                <div class="btn btn-icon btn-sm btn-danger  ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="svg-icon svg-icon-2x">X</span>
                </div>
        </div>
            <div class="modal-body">
                <div id="show_module_role" name="show_module_role"> </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">اغلاق</button>
            </div>
    </div>
    </div>
</div>

@endsection
@section('styles')
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
    <script type="text/javascript"
        src="{{ asset('assets/module/emp_j.js') }}?t={{ config('global.ver.version_all') }}"></script>
    <script>
        view_all_emp("{{ route('dashboard.emps.tbl') }}");

        /* Admin-set password. «نسيت كلمة المرور» needs SMTP, which this host does
           not have, so without this a user who forgets their password is locked
           out for good. Deliberately does NOT reveal the value back to the page. */
        function reset_pw (id) {
            swal.fire({
                title: 'تغيير كلمة المرور',
                html: '<input id="pw1" type="password" class="form-control mb-3" placeholder="كلمة المرور الجديدة" autocomplete="new-password">' +
                      '<input id="pw2" type="password" class="form-control" placeholder="تأكيد كلمة المرور" autocomplete="new-password">' +
                      '<div class="text-muted fs-8 mt-2">8 أحرف على الأقل. سلّمها للمستخدم ليغيّرها بنفسه.</div>',
                showCancelButton: true,
                confirmButtonText: 'حفظ',
                cancelButtonText: 'الغاء',
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: 'btn btn-danger'
                },
                preConfirm: function () {
                    var a = document.getElementById('pw1').value;
                    var b = document.getElementById('pw2').value;
                    if (!a || a.length < 8) {
                        swal.showValidationMessage('كلمة المرور يجب ألا تقل عن 8 أحرف');
                        return false;
                    }
                    if (a !== b) {
                        swal.showValidationMessage('كلمتا المرور غير متطابقتين');
                        return false;
                    }
                    return { pw: a, pw2: b };
                }
            }).then(function (result) {
                if (!result.value) { return; }
                $.ajax({
                    url: "{{ route('dashboard.emps.reset_password') }}",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: id,
                        password: result.value.pw,
                        password_confirmation: result.value.pw2
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (resp) {
                        swal.fire(resp.status ? 'تم' : 'خطأ', resp.message_out || '');
                    },
                    error: function (xhr) {
                        var m = (xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر تغيير كلمة المرور';
                        swal.fire('خطأ', m);
                    }
                });
            });
        }

        function del_emps (id) {
            swal.fire({
                text: 'هل انت متأكد من الحذف',
                icon: 'warning',
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
                        url: "{{ route('dashboard.emps.del_emps') }}",
                        'type': 'POST',
                        'dataType': 'json',
                        'async': false,
                        'data': {
                            id: id
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        'success': function(resp) {
                            if (resp.status == false) {
                                document.documentElement.scrollTop = 0;
                                swal.fire('خطأ', resp.message);
                            } else {
                                view_all_emp("{{ route('dashboard.emps.tbl') }}");
                                swal.fire('تم الحذفبنجاح', resp.message);
                            }

                        }
                    });
                } else if (result.dismiss === 'cancel') {
                    swal.fire('الغاء الامر', 'خطأ');
                }
            });
        }




        function inactive_emp(id, active) {
            if (active == "1") {
            var title_desc ='هل انت متأكد من ايقاف الموظف';
            var confirmButtonText_desc = 'نعم';
        } else {
            var title_desc ='هل انت متأكد من تنشيط اسم المستخدم?';
            var confirmButtonText_desc = 'نعم';
        }
            swal.fire({
                text:title_desc,
                icon: 'warning',
                buttonsStyling: false,
                confirmButtonText: confirmButtonText_desc,
                showCancelButton: true,
                cancelButtonText: 'الغاء الامر',
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: 'btn btn-danger'
                }
            }).then(function(result) {
                if (result.value) {
                    $.ajax({
                        url: "{{ route('dashboard.emps.inactive_emp') }}",
                        'type': 'POST',
                        'dataType': 'json',
                        'async': false,
                        'data': {
                            id: id,
                            active:active,
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        'success': function(resp) {
                            if (resp.status == false) {
                                document.documentElement.scrollTop = 0;
                                swal.fire('خطأ', resp.message);
                            } else {
                                view_all_emp("{{ route('dashboard.emps.tbl') }}");
                                swal.fire('تم الحذفبنجاح', resp.message);
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
