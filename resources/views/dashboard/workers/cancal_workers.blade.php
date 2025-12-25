<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }
    .tox-notifications-container {
        display: none !important;
    }
</style>
<form id="cancal_workers_data" name="cancal_workers_data" class="form" action="{{ route('dashboard.workers.updcancal') }}"
    method="post" enctype="multipart/form-data" autocomplete="off">
    @csrf
    <input name="worker_id" id="worker_id" value="{{ $workers->worker_id }}" im-insert="true"
        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="worker_id"
        aria-describedby="basic-addon1">
    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
            <div class="card">
                <div class="card-body ">
                    <div class="row p-0 mb-0 px-1">
                        <div class="col">
                            <div
                                class="border border-dashed border-dark text-center min-w-125px rounded pt-4 pb-2 my-3">
                                <span class="fs-6 fw-bold text-info ">اسم العامل :</span> <span
                                    class=" fw-bold text-success d-block">{{ $workers->worker_name }}</span>
                            </div>
                        </div>
                        <div class="col">
                            <div
                                class="border border-dashed border-dark text-center min-w-125px  rounded pt-4 pb-2 my-3">
                                <span class="fs-6 fw-bold text-info ">رقم الاقامة:</span> <span
                                    class=" fw-bold text-success d-block">{{ $workers->ssn }}</span>
                            </div>
                        </div>
                        <div class="col">
                            <div
                            class="border border-dashed border-dark text-center min-w-125px  rounded pt-4 pb-2 my-3">
                                <div class="image-input image-input-outline" data-kt-image-input="true"
                                    style="background-image: url(/assets/media/avatars/blank.png)">
                                    <div class="image-input-wrapper w-60px h-60px"
                                        style="background-image: url( {{ $workers->avatar }})"></div>

                                </div>
                        </div>
                    </div>
                    </div>
                    <div class="alert alert-dismissible   d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                        id="errorBox_worker" style="display: none !important">
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
                            <span id="displayErrors_worker" class="mb-2  fw-bolder text-light"></span>
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
                        id="successBox_worker" style="display: none !important">
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
                            <span id="displaysuccess_worker"></span>
                        </div>
                        <button type="button"
                            class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                            data-bs-dismiss="alert">
                            <span class="svg-icon svg-icon-2x svg-icon-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2"
                                        rx="1" transform="rotate(-45 6 17.3137)" fill="black"></rect>
                                    <rect x="7.41422" y="6" width="16" height="2"
                                        rx="1" transform="rotate(45 7.41422 6)" fill="black"></rect>
                                </svg>
                            </span>
                        </button>
                    </div>
                    <div class="mb-0">
                        <div class="row gx-5 mb-5">
<?php if($workers->updatecancal_user != ''){ ?>
                            <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label for="canacal_type_id" class="form-label  fs-6 fw-bold text-dark mb-3">نوع
                                    الاجراء</label>
                                <div>
                                    <select class="form-select fw-bolder  form-select_u " data-control="select2"
                                            id="canacal_type_id"
                                            name="canacal_type_id" dir="rtl">
                                        <option value="">اختر ..</option>
                                        <option value="1">ارجاع الى العمل</option>
                                    </select>
                                </div>
                            </div>

<?php } ?>
                            <div class=" col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label for="doc" class="form-label required fs-6 fw-bold text-dark mb-3"> تاريخ إنهاء العمل:
                                  </label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="far fa-calendar-alt fa-fw text-dark"></i></span></div><input
                                        type="text" name="doc" id="doc"
                                        class="form-control fw-bold  text-dark input_date_picker_"
                                        value="{{ $workers->doc }}" placeholder="تاريخ إنهاء العمل" value=""
                                        autocomplete="off">
                                </div>
                            </div>
                            <?php if($workers->updatecancal_user != ''){ ?>
                                <div class=" col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
                                    <?php } else{?>
                                        <div class=" col-12 col-lg-9 col-md-12 col-sm-12 mb-5">
                                            <?php } ?>

                                <label for="cancalfile" class="form-label  fs-6 fw-bold text-dark mb-3">تحميل
                                    إرفاق مخالصة :</label>
                                <div class="input-group mb-3">
                                    @if ($workers->cancalfile)
                                        <a class="btn btn-lg   btn-success  "
                                            style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
                                            href=" {{ $workers->cancalfile }}">
                                            <span>
                                                <i class="la  la-cloud-download" style="color:#fff"></i>
                                            </span>
                                        </a>
                                        <a class="btn btn-lg   btn-danger remove"
                                            style="padding: 0.7rem 1rem !important;border-radius: 0;"
                                            onclick="del_file('{{ $workers->worker_id }}','{{ $workers->cancalfile }}','cancalfile')">
                                            <span>
                                                <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
                                            </span>
                                        </a>
                                    @endif
                                    <input class="form-control custom-file-input" type="file" name='cancalfile'
                                        id='cancalfile'>
                                </div>
                            </div>
                            <div class=" col-12 col-lg-12 col-md-12 col-sm-12  mb-5">
                                <label for="note_cancal" class="  form-label fs-6 fw-bold text-dark mb-3">سبب إنهاء العمل
                                </label>
                                <textarea name="note_cancal" rows="1" class="form-control fw-bold" id="note_cancal" placeholder="سبب إنهاء العمل">{{ $workers->note_cancal }}</textarea>
                            </div>
                            </div>
                        </div>
                        <div class="text-center mb-0  ">
                            <button type="submit" id="kt_docs_submitsss"
                                class="btn btn-primary font-weight-bold mr-2" name="submitButton">حفظ
                                البيانات</button>
                            <div class="overlay-layer bg-dark bg-opacity-5" id='wait_block'
                                style="display: none !important">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@section('styles')
    <style>
        .select2-container[dir="rtl"] .select2-selection--single .select2-selection__rendered {
            padding-bottom: 2px;
        }
    </style>
    <script type="text/javascript" src="{{ asset('assets/module/woker_j.js') }}?t={{ config('global.ver.version_all') }}">
    </script>
    <script>
        $('.input_date_picker_').flatpickr({
            format: 'dd-mm-yyyy',
            "locale": "ar",
        });

        $(document).ready(function() {
            $(".form-select_u").select2({
                dropdownParent: $('#view_prim_const_m .modal-content')
            });
        });
        function del_file_multi(worker_id, cancalfile_url, type, i) {
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
                        url: "{{ route('dashboard.workers.delete_file') }}",
                        'type': 'POST',
                        'dataType': 'json',
                        'async': false,
                        'data': {
                            worker_id: worker_id,
                            cancalfile_url: cancalfile_url,
                            type: type,
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        'success': function(resp) {
                            if (resp.status == false) {
                                document.documentElement.scrollTop = 0;
                                swal.fire('خطأ', resp.message);
                            } else {
                                swal.fire('تم الحف بنجاح', resp.message);
                            }

                        }
                    });
                } else if (result.dismiss === 'cancel') {
                    swal.fire('الغاء الامر', 'خطأ');
                }
            });
        }
        function del_file(worker_id, cancalfile_url, type) {
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
                        url: "{{ route('dashboard.workers.delete_file') }}",
                        'type': 'POST',
                        'dataType': 'json',
                        'async': false,
                        'data': {
                            worker_id: worker_id,
                            cancalfile_url: cancalfile_url,
                            type: type
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        'success': function(resp) {
                            if (resp.status == false) {
                                document.documentElement.scrollTop = 0;
                                swal.fire('خطأ', resp.message);
                            } else {
                                swal.fire('تم الحف بنجاح', resp.message);
                            }
                        }
                    });
                } else if (result.dismiss === 'cancel') {
                    swal.fire('الغاء الامر', 'خطأ');
                }
            });
        }
    </script>
