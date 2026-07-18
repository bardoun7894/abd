<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }

    .tox-notifications-container {
        display: none !important;
    }
</style>





<form id="upd_rentpay_data" name="upd_rentpay_data" class="form" action="{{ route('dashboard.shop.updrentpay') }}" method="post"
    enctype="multipart/form-data" autocomplete="off">
    @csrf


    <input name="shop_id" id="shop_id" value="{{ $shop->shop_id }}" im-insert="true"
        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="shop_id"
        aria-describedby="basic-addon1">
    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
            <div class="card">
                <div class="card-body ">
                    <div class="row p-0 mb-0 px-1">
                        <div class="col">
                            <div
                                class="border border-dashed border-dark text-center min-w-125px rounded pt-4 pb-2 my-3">
                                <span class="fs-6 fw-bold text-info ">اسم المحل :</span> <span
                                    class=" fw-bold text-success d-block">{{ $shop->shop_name }}</span>
                            </div>
                        </div>
                        <div class="col">
                            <div
                                class="border border-dashed border-dark text-center min-w-125px  rounded pt-4 pb-2 my-3">
                                <span class="fs-6 fw-bold text-info ">قائد المحل:</span> <span
                                    class=" fw-bold text-success d-block">{{ $shop->manager_name }}</span>
                            </div>
                        </div>
                    </div>
                    @if (!empty($shop->rent_attach_url))
                        <div class="text-center mb-4">
                            <a href="{{ $shop->rent_attach_url }}" target="_blank" class="btn btn-sm btn-light-primary fw-bold">
                                <i class="fas fa-file-contract fa-fw"></i> عرض عقد الإيجار
                            </a>
                            @if (!empty($shop->rent_name))
                                <span class="text-muted fs-8 ms-2">المؤجر: {{ $shop->rent_name }}</span>
                            @endif
                        </div>
                    @endif
                    <div class="alert alert-dismissible   d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                        id="errorBox_shop" style="display: none !important">
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
                            <span id="displayErrors_shop" class="mb-2  fw-bolder text-light"></span>
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
                        id="successBox_shop" style="display: none !important">
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
                            <span id="displaysuccess_shop"></span>
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

                    <div class="mb-0" id=show_details>







                        <input name="rentpay_id" id="rentpay_id"  im-insert="true"
                        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
                        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="shop_rentpay_id"
                        aria-describedby="basic-addon1">





                        <div class="row gx-5 mb-5">






                        <div class=" col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                            <label for="rentpay_dt" class="form-label  fs-6 fw-bold text-dark mb-3">تاريخ الدفعة :</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i
                                            class="far fa-calendar-alt fa-fw text-dark"></i></span></div><input
                                    type="text" name="rentpay_dt" id="rentpay_dt"
                                    class="form-control fw-bold  text-dark input_date_"
                                    placeholder="تاريخ الانتهاء" value="" autocomplete="off">
                            </div>
                        </div>









                        <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                            <label for="rentpay_price" class="form-label fs-6 fw-bold text-dark mb-3">مبلغ الايجار</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i
                                            class="fas fa-dollar-sign fa-fw text-dark"></i></span></div>

                                <input type="number" name="rentpay_price" هي="rentpay_price" class="form-control "
                                    placeholder="مبلغ الايجار" />
                            </div>
                        </div>


                        <div class="col-12 col-lg-8 col-md-12 col-sm-12   mb-5">
                            <label for="rentpay_note" class="form-label fs-6 fw-bold text-dark mb-3">ملاحظة</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i
                                            class="fas fa-flag-checkered fa-fw text-dark"></i></span></div>

                                <input type="text" name="rentpay_note" class="form-control "
                                    placeholder="ملاحظة" />
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



                    <div class="separator separator-content border-dark my-10 mb-8"><span
                        class="w-150px fw-bold text-danger">عرض بيانات الدفع</span></div>
                    <div id="result_rentpay_tbl" name="result_rentpay_tbl"></div>












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
    <script type="text/javascript" src="{{ asset('assets/module/shop_j.js') }}?t={{ config('global.ver.version_all') }}"></script>
    <script>

view_all_rentpay("{{ route('dashboard.shop.tbl_rentpay') }}");
function del_rentpay(id) {
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
                        url: "{{ route('dashboard.shop.del_rentpay') }}",
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
                                $("#view_prim_const_m").modal('hide');
                                view_all_shop("{{ route('dashboard.shop.tbl') }}");

                                swal.fire('تم الحذفبنجاح', resp.message);
                            }

                        }
                    });
                } else if (result.dismiss === 'cancel') {
                    swal.fire('الغاء الامر', 'خطأ');
                }
            });
        }

















        $('.input_date_').flatpickr({
            format: 'dd-mm-yyyy',
            "locale": "ar",
        });

        $(document).ready(function() {
            $(".form-select_u").select2({
                dropdownParent: $('#view_prim_const_m .modal-content')
            });
        });



    </script>
