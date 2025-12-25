<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }

    .tox-notifications-container {
        display: none !important;
    }
</style>





<form id="upd_shop_data" name="upd_shop_data" class="form" action="{{ route('dashboard.shop.updstore') }}"
    method="post" enctype="multipart/form-data" autocomplete="off">
    @csrf


    <input name="shop_id_db" id="shop_id_db" value="{{ $shop->shop_id }}" im-insert="true"
        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
        class="form-control kt-font-dark kt-font-bolder" readonly placeholder="shop_id_db"
        aria-describedby="basic-addon1">











    <div class="d-flex flex-column flex-lg-row">


        <div class="mb-10 flex-lg-row-fluid mb-lg-0">


            <div class="card">
                <div class="card-body">
                    <div class="p-5 mb-6 alert alert-dismissible d-flex flex-column flex-sm-row w-100"
                        id="errorBox_shop" style="display: none !important">
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
                            <span id="displayErrors_shop" class="mb-2 fw-bolder text-light"></span>
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


                    <div class="p-5 mb-6 alert alert-dismissible bg-success d-flex flex-column flex-sm-row w-100"
                        id="successBox_shop" style="display: none !important">
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
                            <h4 class="mb-2 text-light">نجاح</h4>
                            <span id="displaysuccess_shop"></span>
                        </div>
                        <button type="button"
                            class="top-0 m-2 position-absolute position-sm-relative m-sm-0 end-0 btn btn-icon ms-sm-auto"
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
                        <div class="mb-5 row gx-5">

                            <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12"><label for="shop_name"
                                class="mb-3 form-label required fs-6 fw-bold text-dark">اسم المحل</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i
                                            class="fas fa-store-alt fa-fw text-dark"></i></span></div><input
                                    type="text" name="shop_name" id="shop_name"
                                    class="form-control fw-bold text-dark" placeholder="اسم المحل" value="{{$shop->shop_name}}"
                                    value="" autocomplete="off">
                            </div>


                        </div>

                                <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12"><label for="establishment_number"
                            class="mb-3 form-label fs-6 fw-bold text-dark">رقم المنشأة</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-store-alt fa-fw text-dark"></i></span></div><input
                                type="text" name="establishment_number" id="establishment_number"
                                class="form-control fw-bold text-dark" placeholder="رقم المنشأة" value="{{$shop->establishment_number}}"
                                 autocomplete="off">
                        </div>
                    </div>

                        <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                            <label for="calculate_month_val" class="mb-3 form-label required fs-6 fw-bold text-dark">المبلغ
                                المطلوب</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i
                                            class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                    type="text" name="calculate_month_val" id="calculate_month_val"  value="{{$shop->calculate_month_val}}"
                                    class="form-control fw-bold text-dark text-info"
                                    data-inputmask="'alias' : 'decimal'" minlenght="1" maxlength="20"
                                    placeholder="المبلغ المطلوب">
                            </div>
                        </div>

                    <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12">
                                <label for="manager_id" class="mb-3 form-label required fs-6 fw-bold text-dark">قائد المحل</label>
                                <div>
                                    <select class="form-select fw-bold form-select_u" data-control="select2" id="manager_id"
                                        name="manager_id" dir="rtl" data-placeholder="قائد المحل">
                                        <option value="">اختر ..</option>
                                        @foreach ($manager as $x)
                                            <option @selected($shop->manager_id == $x->manager_id) value="{{ $x->manager_id }} ">{{ $x->manager_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12"><label for="shop_respon"
                                class="mb-3 form-label required fs-6 fw-bold text-dark">اسم المسؤول</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i
                                            class="fas fa-user-tie fa-fw text-dark"></i></span></div><input
                                    type="text" name="shop_respon" id="shop_respon" value="{{$shop->shop_respon}}"
                                    class="form-control fw-bold text-dark" placeholder="اسم المسؤول"
                                    value="" autocomplete="off">
                            </div>
                        </div>
                            <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                <label for="shop_mobile" class="mb-3 form-label fs-6 fw-bold text-dark">رقم جوال المسؤول</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-phone-volume fa-fw text-dark"></i></span></div><input
                                        type="text" name="shop_mobile" id="shop_mobile" value="{{$shop->shop_mobile}}"
                                        class="form-control fw-bold text-dark text-info" minlenght="1"
                                        maxlength="20" placeholder="رقم جوال المسؤول">
                                </div>
                            </div>
                            <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12">
                                <label for="city_id" class="mb-3 form-label fs-6 fw-bold text-dark">المدينة</label>
                                <div>
                                    <select class="form-select fw-bold form-select_u" data-control="select2" id="city_id"
                                        name="city_id" dir="rtl" data-placeholder="المدينة">
                                        <option value="">اختر ..</option>
                                        @foreach ($city as $x)
                                            <option @selected($shop->city_id == $x->city_id) value="{{ $x->city_id }} ">{{ $x->city_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12">
                                <label for="shop_location" class="mb-3 form-label fs-6 fw-bold text-dark">موقع المحل</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-map-marker-alt fa-fw text-dark"></i></span></div><input
                                        type="text" name="shop_location" id="shop_location"  value="{{$shop->shop_location}}"
                                        class="form-control fw-bold text-dark" minlenght="1"
                                        placeholder="موقع المحل">
                                </div>
                            </div>
                            <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12">
                                <label for="note" class="mb-3 form-label fs-6 fw-bold text-dark">الملاحظة
                                </label>
                                <textarea name="note" rows="1" class="form-control fw-bold" id="note" placeholder="الملاحظة">{{$shop->note}}</textarea>
                            </div>






                        </div>
                        <div class="mb-0 text-center">
                            <button type="submit" id="kt_docs_submitsss"
                                class="mr-2 btn btn-primary font-weight-bold" name="submitButton">حفظ
                                البيانات</button>
                            <div class="bg-opacity-5 overlay-layer bg-dark" id='wait_block'
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
    <script type="text/javascript" src="{{ asset('assets/module/shop_j.js') }}?t={{ config('global.ver.version_all') }}">
    </script>
    <script>
        $('.input_date_').flatpickr({
            format: 'dd-mm-yyyy',
            "locale": "ar",
        });
        $('#add_file').on('click', function() {
            var newfield =
                '<div class="form-group row repeat"><div class="input-group"><div class="form-control custom-file"><input type="file" class="form-control custom-file-input" name="files[]" ></div><div class="input-group-append" style="padding: 0.7rem 1rem;"><a class="btn btn-lg btn-danger remove"  ><span><i class="la la-minus" style="color:#fff"></i></span></a></div></div></div>';
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

        function del_file_multi(shop_id,ssnfile_url,type,i) {
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
                        url: "{{ route('dashboard.shop.delete_file') }}",
                        'type': 'POST',
                        'dataType': 'json',
                        'async': false,
                        'data': {
                            shop_id: shop_id,
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

        function del_file(shop_id, ssnfile_url, type) {
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
                        url: "{{ route('dashboard.shop.delete_file') }}",
                        'type': 'POST',
                        'dataType': 'json',
                        'async': false,
                        'data': {
                            shop_id: shop_id,
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
