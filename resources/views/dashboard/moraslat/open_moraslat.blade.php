<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }

    .tox-notifications-container {
        display: none !important;
    }
</style>





<form id="open_moraslat_data" name="open_moraslat_data" class="form"
    action="{{ route('dashboard.moraslat.updopenstore') }}" method="post" enctype="multipart/form-data" autocomplete="off">
    @csrf


    <input name="moraslat_id" id="moraslat_id" value="{{ $moraslat->moraslat_id }}" im-insert="true"
        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="moraslat_id"
        aria-describedby="basic-addon1">



    <input name="user_id_db" id="user_id_db" value="{{ $moraslat->user_id }}" im-insert="true"
        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="user_id_db"
        aria-describedby="basic-addon1">








    <div class="d-flex flex-column flex-lg-row">


        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">


            <div class="card">

























                <div class="card-body ">


                    <div id="kt_create_new_payment_method_1"
                        class="collapse show fs-6 ps-10 notice bg-light-primary rounded border-primary border border-dashed ">
                        <h5 class="mb-4 text-danger">بيانات المعاملة:</h5>
                        <div class="d-flex flex-wrap py-5">
                            <div class="flex-equal me-5">
                                <table class="table table-flush fw-bold gy-1">
                                    <tbody>
                                        <tr>
                                            <td class="text-info min-w-125px w-125px">نوع المعاملة</td>
                                            <td class="text-black">{{ $moraslat->moraslat_type_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-info min-w-125px w-125px">تصنيف المعاملة</td>
                                            <td class="text-black">{{ $moraslat->moraslat_categoty_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-info min-w-125px w-125px">رقم المعاملة</td>
                                            <td class="text-black">{{ $moraslat->moraslat_id }}</td>
                                        </tr>


                                        <tr style="border: 0 !important;">
                                            <td class="text-info min-w-125px w-125px" style="border: 0 !important;">نص
                                                المعاملة</td>
                                            <td class="text-black" style="border: 0 !important;">
                                                {{ $moraslat->moraslat_respon }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex-equal">
                                <table class="table table-flush fw-bold gy-1">
                                    <tbody>
                                        <tr>
                                            <td class="text-info min-w-125px w-125px">المرسل اليه</td>
                                            <td class="text-black">{{ $moraslat->emp_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-info min-w-125px w-125px">تاريخ الارسال</td>
                                            <td class="text-black">{{ $moraslat->created_at }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-info min-w-125px w-125px">تاريخ الفتح</td>
                                            <td class="text-black">
                                                <a href="#"
                                                    class="text-gray-900 text-hover-primary">{{ $moraslat->created_at }}</a>
                                            </td>
                                        </tr>

                                        <tr style="border: 0 !important;">
                                            <td class="text-info min-w-125px w-125px" style="border: 0 !important;">
                                                ملاحظات</td>
                                            <td class="text-black" style="border: 0 !important;">{{ $moraslat->note }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>


                        <div class="overflow-auto pb-5">

                            <div
                                class="d-flex align-items-center border border-dashed border-gray-300 rounded min-w-700px p-5">
                                <?php
                                $z_attac = count($moraslat_attach);
                                if ($z_attac != 0) {
                                    foreach ($moraslat_attach as $x) {
                                        $moraslat_attach_id = $x->moraslat_attach_id;
                                $moraslat_id = $x->moraslat_id;
                                $moraslat_attach_name= $x->moraslat_attach_name;
                                $moraslat_attach_extension= $x->moraslat_attach_extension;
                                $moraslat_attach_url= $x->moraslat_attach_url;


                                                        ?>
                                <div class="d-flex flex-aligns-center pe-10 pe-lg-20">
                                    <img alt="" class="w-30px me-3" src="assets/media/svg/files/doc.svg">
                                    <div class="ms-1 fw-bold">
                                        <a href="{{ $moraslat_attach_url }}" target="_new"
                                            class="fs-6 text-hover-primary fw-bolder">{{ $moraslat_attach_name }}</a>
                                    </div>
                                </div>

                                <?php }  } ?>
                            </div>


                        </div>
                    </div>



















                    <div class="alert alert-dismissible   d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                        id="errorBox_moraslat" style="display: none !important">
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
                            <span id="displayErrors_moraslat" class="mb-2  fw-bolder text-light"></span>
                        </div>
                        <button type="button"
                            class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                            data-bs-dismiss="alert">
                            <span class="svg-icon svg-icon-2x svg-icon-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16"
                                        height="2" rx="1" transform="rotate(-45 6 17.3137)"
                                        fill="black"></rect>
                                    <rect x="7.41422" y="6" width="16" height="2"
                                        rx="1" transform="rotate(45 7.41422 6)" fill="black"></rect>
                                </svg>
                            </span>
                        </button>
                    </div>


                    <div class="alert alert-dismissible bg-success d-flex flex-column flex-sm-row w-100 p-5 mb-0"
                        id="successBox_moraslat" style="display: none !important">
                        <span class="svg-icon svg-icon-2hx svg-icon-light me-4 mb-5 mb-sm-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none">
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
                            <span id="displaysuccess_moraslat"></span>
                        </div>
                        <button type="button"
                            class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                            data-bs-dismiss="alert">
                            <span class="svg-icon svg-icon-2x svg-icon-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16"
                                        height="2" rx="1" transform="rotate(-45 6 17.3137)"
                                        fill="black"></rect>
                                    <rect x="7.41422" y="6" width="16" height="2"
                                        rx="1" transform="rotate(45 7.41422 6)" fill="black"></rect>
                                </svg>
                            </span>
                        </button>
                    </div>

                    <div class="mb-0 py-5">





                        <div class="row gx-5 mb-5">
                            <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label for="moraslat_status_id"
                                    class="form-label   fs-6 fw-bolder text-danger mb-3">نوع الاجراء</label>
                                <div>
                                    <select class="form-select fw-bolder text-danger moraslat_status_id form-select_u "
                                        data-control="select2" id="moraslat_status_id" name="moraslat_status_id"
                                        dir="rtl" onchange='show_info(this.value,2)'>
                                        <option value="">اختر ..</option>

                                        @foreach ($moraslat_status as $x)
                                            <option @selected($moraslat->moraslat_status_id == $x->moraslat_status_id)
                                                value="{{ $x->moraslat_status_id }} ">
                                                {{ $x->moraslat_status_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class=" col-12 col-lg-4 col-md-12 col-sm-12  mb-5">
                                <label for="status_note" class="  form-label fs-6 fw-bold text-dark mb-3">ملاحظات
                                </label>
                                <textarea name="status_note" rows="1" class="form-control fw-bold" id="status_note" placeholder="ملاحظات">{{ $moraslat->status_note }}</textarea>
                            </div>








                            <div class=" col-12 col-lg-5 col-md-12 col-sm-12 mb-5" id="container_file"
                                name="container_file">
                                <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                    <a type="button" id="add_file"
                                        class="btn btn-secondary kt-font-info kt-font-bolder"
                                        style='border-color:#232b51;'><i class="la la-chain"></i>تحميل أوراق اخرى</a>
                                </div>
                                <br />
                                <div class="form-group row">
                                    <div class="input-group ">
                                        <div class="form-control ">
                                            <input type="file" class="form-control custom-file-input"
                                                placeholder="ملف مرفق" name="files[]" multiple>
                                        </div>
                                        <div class="input-group-append" style="padding: 0.7rem 1rem;">
                                            <a class="btn btn-lg btn-danger remove" style="padding: 0.7rem 1rem;">
                                                <span>
                                                    <i class="la la-minus" style="color:#fff"></i>
                                                </span>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </div>












                            <div class=" col-12 col-lg-12 col-md-12 col-sm-12  mb-5 row" id='return_desc_div'
                                name='return_desc_div' style="display:none">
                                <div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
                                    <label for="return_desc" class="  form-label fs-6 fw-bold text-dark mb-3">سبب
                                        الارجاع
                                    </label>
                                    <textarea name="return_desc" rows="1" class="form-control fw-bold" id="return_desc"
                                        placeholder="سبب الارجاع">
{{ $moraslat->status_note }}</textarea>
                                </div>

                                <div class="col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
                                    <label for="user_id"
                                        class="form-label required  fs-6 fw-bold text-dark mb-3">توجية الى</label>
                                    <div>
                                        <select class="form-select fw-bold form-select_u  " data-control="select2"
                                            id="user_id" name="user_id" dir="rtl"
                                            data-placeholder="توجية الى">
                                            @foreach ($users as $x)
                                                <option value="{{ $x->id }} ">{{ $x->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
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
    <script type="text/javascript"
        src="{{ asset('assets/module/moraslat_j.js') }}?t={{ config('global.ver.version_all') }}"></script>
    <script>
        show_info('', '');

        function show_info(moraslat_status_id, desc) {

            var moraslat_status_id = $('#moraslat_status_id').val();

            load_message();

            if (moraslat_status_id == 3) {
                $('#return_desc_div').css('display', '');


                unload_message();


            } else {
                $('#return_desc_div').css('display', 'none');
                unload_message();

            }

        }










        $('.input_date_').flatpickr({
            format: 'dd-mm-yyyy',
            "locale": "ar",
        });
        $('#add_file').on('click', function() {
            var newfield =
                '<div class="form-group row repeat"><div class="input-group "><div class="form-control custom-file"><input type="file" class="form-control custom-file-input" name="files[]" ></div><div class="input-group-append" style="padding: 0.7rem 1rem;"><a class="btn btn-lg btn-danger remove"  ><span><i class="la la-minus" style="color:#fff"></i></span></a></div></div></div>';
            $('#container_file').append(newfield);
        });
        $(document).on('click', '.remove', function() {
            $(this).parent().parent().parent('div').remove();
        });
        $(document).ready(function() {
            $(".form-select_u").select2({
                dropdownParent: $('#view_prim_const_m .modal-content')
            });
        });

        function del_file_multi(moraslat_id, ssnfile_url, type, i) {
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
                        url: "{{ route('dashboard.moraslat.delete_file') }}",
                        'type': 'POST',
                        'dataType': 'json',
                        'async': false,
                        'data': {
                            moraslat_id: moraslat_id,
                            ssnfile_url: ssnfile_url,
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
                                swal.fire('تم الحذفبنجاح', resp.message);
                            }

                        }
                    });
                } else if (result.dismiss === 'cancel') {
                    swal.fire('الغاء الامر', 'خطأ');
                }
            });
        }

        function del_file(moraslat_id, ssnfile_url, type) {
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
                        url: "{{ route('dashboard.moraslat.delete_file') }}",
                        'type': 'POST',
                        'dataType': 'json',
                        'async': false,
                        'data': {
                            moraslat_id: moraslat_id,
                            ssnfile_url: ssnfile_url,
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
