<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }

    .tox-notifications-container {
        display: none !important;
    }
</style>





<form id="upd_moraslat_data" name="upd_moraslat_data" class="form" action="{{ route('dashboard.moraslat.updopenstore') }}"
    method="post" enctype="multipart/form-data" autocomplete="off">
    @csrf


    <input name="moraslat_id_db" id="moraslat_id_db" value="{{ $moraslat->moraslat_id }}" im-insert="true"
        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="moraslat_id_db"
        aria-describedby="basic-addon1">











    <!--begin::Layout-->
    <div class="d-flex flex-column flex-lg-row">

        <!--begin::Content-->

        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">


            <div class="card">





                <div class="card-body pt-3">
                    <div class="inv_detail">
                        <h5 class="mb-4"> #رقم الفاتورة: 1/2023</h5>
                        <div class="d-flex flex-wrap py-5">
                            <div class="flex-equal me-5">
                                <table class="table fs-6 fw-bold gs-0 gy-2 gx-2 m-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-gray-400 min-w-150px w-150px">نوع المستند:</td>
                                            <td class="text-gray-800 min-w-100px">
                                                وارد
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-400">الـــمــــــــــــــــورد:</td>
                                            <td class="text-gray-800">شركة بدكس</td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-400">#فاتورة المورد:</td>
                                            <td class="text-gray-800">58524</td>
                                        </tr>
                                   </tbody>
                                </table>
                            </div>
                            <div class="flex-equal">
                                <table class="table fs-6 fw-bold gs-0 gy-2 gx-2 m-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-gray-400 min-w-100px w-100px">الــعمــلـــــــــــــــــة:
                                            </td>
                                            <td class="text-gray-800 min-w-200px">
                                                شيكل
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-400">تاريخ المستند:</td>
                                            <td class="text-gray-800">07-08-2023</td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-400 w-125px">المبلغ الإجمالي :</td>
                                            <td class="text-gray-800">1850 شيكل</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex-equal">
                                <table class="table fs-6 fw-bold gs-0 gy-2 gx-2 m-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-gray-400 min-w-150px w-150px">سعر الصرف:</td>
                                            <td class="text-gray-800">1</td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-400 min-w-150px w-150px">تاريخ فاتورة المورد:</td>
                                            <td class="text-gray-800">07-08-2023</td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-400 min-w-150px w-150px">ملاحظات:</td>
                                            <td class="text-gray-800">تجريبي حاسوب</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="card-header p-0 d-flex">
                            <div class="card-title">
                                <h5>قائمة الأصناف:</h5>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-4 mb-0 inv_itms">
                                <thead>
                                    <tr
                                        class="border-bottom border-gray-200 text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th style="padding-right: 0.75rem;" class="min-w-90px">رقم الصنف</th>
                                        <th class="min-w-125px">اسم الصنف</th>
                                        <th class="min-w-70px">السعر</th>
                                        <th class="min-w-70px">الكمية</th>
                                        <th class="min-w-80px">الوحدة</th>
                                        <th class="min-w-125px">المبلغ الإجمالي</th>
                                        <th class="min-w-100px">الحالة</th>
                                        <th class="min-w-100px">الموظف</th>
                                        <th class="min-w-100px">ملاحظات الموظف</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-bold text-gray-800">
                                    <tr>
                                        <td style="padding-right: 0.75rem;">
                                            <span class="badge badge-light-danger">71531001</span>
                                        </td>
                                        <td colspan="2" class="items_details_rows"
                                            style="width: 500px;padding-top: 0 !important;padding-bottom: 0 !important;">
                                            <table>
                                                <tbody>
                                                    <tr
                                                        style="border:0 !important;border-bottom: 1px solid #e6e7e8 !important;">
                                                        <td
                                                            style="border:0 !important;width: 430px;padding-left: 15px;">
                                                            طابعة حاسوب <span
                                                                style="color:#d3224c;float:left;font-weight:400 !important">hp
                                                                3*1 سيريال (FDDJ545454) </span>
                                                        </td>
                                                        <td
                                                            style="padding-right:.55rem;border:0 !important;border-right:1px solid #e6e7e8   !important;width: 99px;">
                                                            200
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <table>
                                                <tbody>
                                                    <tr
                                                        style="border:0 !important;border-bottom: 1px solid #e6e7e8 !important;">
                                                        <td
                                                            style="border:0 !important;width: 430px;padding-left: 15px;">
                                                            طابعة حاسوب <span
                                                                style="color:#d3224c;float:left;font-weight:400 !important">hp
                                                                4*1 سيريال (SAFDG254Df) </span>
                                                        </td>
                                                        <td
                                                            style="padding-right:.55rem;border:0 !important;border-right:1px solid #e6e7e8   !important;width: 99px;">
                                                            250
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <table>
                                                <tbody>
                                                    <tr
                                                        style="border:0 !important;border-bottom: 1px solid #e6e7e8 !important;">
                                                        <td
                                                            style="border:0 !important;width: 430px;padding-left: 15px;">
                                                            طابعة حاسوب <span
                                                                style="color:#d3224c;float:left;font-weight:400 !important">hp
                                                                laser 4*1 سيريال (FGHGH214p) </span>
                                                        </td>
                                                        <td
                                                            style="padding-right:.55rem;border:0 !important;border-right:1px solid #e6e7e8   !important;width: 99px;">
                                                            300
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td>3</td>
                                        <td>عدد</td>
                                        <td>750</td>
                                        <td> <span class="badge badge-light-info fw-bold">مرسل لجنة</span> </td>
                                        <td></td>
                                        <td></td>
                                    </tr>



                                    <tr>
                                        <td style="padding-right: 0.75rem;">
                                            <span class="badge badge-light-danger">71431004</span>
                                        </td>
                                        <td colspan="2" class="items_details_rows"
                                            style="width: 500px;padding-top: 0 !important;padding-bottom: 0 !important;">
                                            <table>
                                                <tbody>
                                                    <tr
                                                        style="border:0 !important;border-bottom: 1px solid #e6e7e8 !important;">
                                                        <td
                                                            style="border:0 !important;width: 430px;padding-left: 15px;">
                                                            جهاز حاسوب محمول لابتوب i5 <span
                                                                style="color:#d3224c;float:left;font-weight:400 !important">hp
                                                                probook سيريال (RDFG46884) </span>
                                                        </td>
                                                        <td
                                                            style="padding-right:.55rem;border:0 !important;border-right:1px solid #e6e7e8   !important;width: 99px;">
                                                            600
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <table>
                                                <tbody>
                                                    <tr
                                                        style="border:0 !important;border-bottom: 1px solid #e6e7e8 !important;">
                                                        <td
                                                            style="border:0 !important;width: 430px;padding-left: 15px;">
                                                            جهاز حاسوب محمول لابتوب i5 <span
                                                                style="color:#d3224c;float:left;font-weight:400 !important">lenovo
                                                                سيريال (TYFGG4458) </span>
                                                        </td>
                                                        <td
                                                            style="padding-right:.55rem;border:0 !important;border-right:1px solid #e6e7e8   !important;width: 99px;">
                                                            500
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td>2</td>
                                        <td>عدد</td>
                                        <td>1100</td>
                                        <td> <span class="badge badge-light-info fw-bold">مرسل لجنة</span> </td>
                                        <td></td>
                                        <td></td>
                                    </tr>



                                </tbody>
                                <!--end::Table body-->
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Product table-->
                    </div>
                    <!--end::Section-->
                </div>



















                <div class="card-body ">
                    <div class="alert alert-dismissible   d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                        id="errorBox_moraslat" style="display: none !important">
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


                    <div class="alert alert-dismissible bg-success d-flex flex-column flex-sm-row w-100 p-5 mb-6"
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

                    <div class="mb-0">





                        <div class="row gx-5 mb-5">
                            <div class="col-12 col-lg-5 col-md-12 col-sm-12 mb-5">
                                <label for="moraslat_type_id" class="form-label   fs-6 fw-bolder text-danger mb-3">نوع
                                    المصروف</label>
                                <div>
                                    <select class="form-select fw-bolder text-danger moraslat_type_id form-select_u "
                                        data-control="select2" id="moraslat_type_id" name="moraslat_type_id"
                                        dir="rtl" onchange='load_moraslat_form(this.value,2)'
                                        data-url="{{ route('dashboard.moraslat.load_moraslat_form') }}">
                                        <option value="">اختر ..</option>

                                        @foreach ($moraslat_type as $x)
                                            <option @selected($moraslat->moraslat_type_id == $x->moraslat_type_id) value="{{ $x->moraslat_type_id }} ">
                                                {{ $x->moraslat_type_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>










                        <div class="row gx-5 mb-5" id='load_moraslat_form'>













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
        load_moraslat_form({{ $x->moraslat_type_id }}, 2)







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
        /*   $('.custom-file-input').on('change', function() {
               var fileName = $(this).val();
               if (fileName.length > 23) {
                   fileName = fileName.substr(0, 11) + "..." + fileName.substr(-10);
               }
               $(this).next('.custom-file-label').addClass("selected").html(fileName);
           });*/
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
