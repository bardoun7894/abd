<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }

    .tox-notifications-container {
        display: none !important;
    }
</style>





<form id="upd_purchase_data" name="upd_purchase_data" class="form" action="{{ route('dashboard.purchase.updstore') }}"
    method="post" enctype="multipart/form-data" autocomplete="off">
    @csrf


    <input {{$view =="true"? "disabled" : ''}}  name="purchase_id_db" id="purchase_id_db" value="{{ $purchase->purchase_id }}" im-insert="true"
        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="purchase_id_db"
        aria-describedby="basic-addon1">











    <!--begin::Layout-->
    <div class="d-flex flex-column flex-lg-row">

        <!--begin::Content-->

        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">


            <div class="card">
                <div class="card-body ">
                    <div class="alert alert-dismissible   d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                        id="errorBox_purchase" style="display: none !important">
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
                            <span id="displayErrors_purchase" class="mb-2  fw-bolder text-light"></span>
                        </div>
                        <button type="button"
                            class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                            data-bs-dismiss="alert">
                            <span class="svg-icon svg-icon-2x svg-icon-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                        transform="rotate(-45 6 17.3137)" fill="black"></rect>
                                    <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                        transform="rotate(45 7.41422 6)" fill="black"></rect>
                                </svg>
                            </span>
                        </button>
                    </div>


                    <div class="alert alert-dismissible bg-success d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                        id="successBox_purchase" style="display: none !important">
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
                            <span id="displaysuccess_purchase"></span>
                        </div>
                        <button type="button"
                            class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                            data-bs-dismiss="alert">
                            <span class="svg-icon svg-icon-2x svg-icon-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                        transform="rotate(-45 6 17.3137)" fill="black"></rect>
                                    <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                        transform="rotate(45 7.41422 6)" fill="black"></rect>
                                </svg>
                            </span>
                        </button>
                    </div>

                    <div class="mb-0">
                        <div class="row gx-5 mb-5">




                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="purchase_no" class="form-label required fs-6 fw-bold text-dark mb-3">رقم
                                    الفاتورة</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="far fa-id-card fa-fw text-dark"></i></span></div><input {{$view =="true"? "disabled" : ''}}
                                        type="text" name="purchase_no" id="purchase_no"
                                        value="{{ $purchase->purchase_no }}" required
                                        class="form-control fw-bold text-dark text-info " placeholder="رقم الفاتورة">
                                </div>
                            </div>


                            <div class=" col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="purchase_dt" class="form-label required fs-6 fw-bold text-dark mb-3">تاريخ
                                    الغاتورة :</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-calendar-alt fa-fw text-dark"></i></span></div>
                                    <input {{$view =="true"? "disabled" : ''}}  type="text" name="purchase_dt" id="purchase_dt" required
                                        value="{{ $purchase->purchase_dt }}"
                                        class="form-control fw-bold  text-dark input_date_"
                                        placeholder="تاريخ الغاتورة" value="" autocomplete="off">
                                </div>
                            </div>




                            <div class=" col-12 col-lg-3 col-md-12 col-sm-12 mb-5"><label for="purchase_respon"
                                    class="form-label  fs-6 fw-bold text-dark mb-3">اسم المورد</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-user-tie fa-fw text-dark"></i></span></div><input {{$view =="true"? "disabled" : ''}}
                                        required type="text" name="purchase_respon" id="purchase_respon"
                                        value="{{ $purchase->purchase_respon }}"
                                        class="form-control fw-bold  text-dark" placeholder="اسم المورد "
                                        autocomplete="off">
                                </div>
                            </div>



                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="purchase_price" class="form-label  fs-6 fw-bold text-dark mb-3">المبلغ
                                    شامل الضريبة
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="far fa-id-card fa-fw text-dark"></i></span></div><input {{$view =="true"? "disabled" : ''}}  required
                                        type="text" name="purchase_price" id="purchase_price"
                                        value="{{ $purchase->purchase_price }}"
                                        class="form-control fw-bold text-dark text-info "
                                        data-inputmask="'alias' : 'decimal'" minlenght="1" maxlength="20"
                                        placeholder="المبلغ">
                                </div>
                            </div>

                            @if (isset($purchase->shop_id))
                                <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                    <label for="manager_id" class="form-label   fs-6 fw-bold text-dark mb-3">
                                        المحل</label>
                                    <div>
                                        <select {{$view =="true"? "disabled" : ''}}  class="form-select fw-bold  form-select_u "id="shop_id" name="shop_id"  data-control="select2"
                                            dir="rtl" required onchange='$("#manager_id").html(0)'
                                            data-placeholder=" المحل">
                                            <option value="">اختر ..</option>
                                            @foreach ($shops as $x)
                                                <option value="{{ $x->shop_id }} " @selected($purchase->shop_id == $x->shop_id)>
                                                    {{ $x->shop_name . ($x->municip->municip_no ?? '') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                            <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label for="manager_id" class="form-label   fs-6 fw-bold text-dark mb-3">قائد
                                    المحل</label>
                                <div>
                                    <select {{$view =="true"? "disabled" : ''}}  class="form-select fw-bold form-select_u " data-control="select2"
                                        id="manager_id" name="manager_id" dir="rtl"
                                        data-placeholder="قائد المحل">
                                        <option @if (isset($purchase->shop_id)) disabled @endif value="">اختر
                                            ..</option>
                                        @foreach ($manager as $x)
                                            <option @if (isset($purchase->shop_id)) disabled @endif
                                                @selected($purchase->manager_id == $x->manager_id) value="{{ $x->manager_id }} ">
                                                {{ $x->manager_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="purchase_price" class="form-label  fs-6 fw-bold text-dark mb-3"> الرقم
                                    الضريبي
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="far fa-id-card fa-fw text-dark"></i></span></div><input {{$view =="true"? "disabled" : ''}}
                                        type="text" name="tax_number" id="tax_number" value=" {{$purchase->tax_number ??'' }}"
                                        class="form-control fw-bold text-dark text-info " placeholder="الرقم الضريبي">
                                </div>
                            </div>












                            <input {{$view =="true"? "disabled" : ''}}  name="purchasefile_db" id="purchasefile_db" value="{{ $purchase->purchasefile }}"
                                im-insert="true" type="text" style="display:none"
                                class="form-control kt-font-dark kt-font-bolder " readonly
                                placeholder="purchasefile_db" aria-describedby="basic-addon1">
                                @if ($view !="true")

                            <div class=" col-12 col-lg-5 col-md-12 col-sm-12 mb-5">

                                <label for="doe" class="form-label  fs-6 fw-bold text-dark mb-3">إرفاق صورة
                                    للفاتورة</label>
                                <div class="input-group mb-3">
                                    @if ($purchase->purchasefile)
                                        <a class="btn btn-lg   btn-success  "
                                            style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
                                            href=" {{ $purchase->purchasefile }}">
                                            <span>
                                                <i class="la  la-cloud-download" style="color:#fff"></i>
                                            </span>
                                        </a>
                                        <a class="btn btn-lg   btn-danger remove"
                                            style="padding: 0.7rem 1rem !important;border-radius: 0;"
                                            onclick="del_file('{{ $purchase->purchase_id }}','{{ $purchase->purchasefile }}','purchasefile')">
                                            <span>
                                                <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
                                            </span>
                                        </a>
                                    @endif
                                    <input {{$view =="true"? "disabled" : ''}}  class="form-control custom-file-input" type="file" name='purchasefile'
                                        id='purchasefile'>

                                </div>

                                @else

                                </div>

                                @if($view == "true")
                                <a href="{{ $purchase->purchasefile }}" target="_blank">

                                <div class="col">
                                    <label >
                                        الفاتورة</label>
                                            <iframe  style="width: 100%; height:700px"  src="https://noor-alsabah.com/{{ $purchase->purchasefile }}" alt="الفاتورة"></iframe>

                                </div>
                            </a>

                                @endif
                            @endif














                            <div class=" col-12 col-lg-7 col-md-12 col-sm-12 mb-5">
                                <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">بيان الفاتورة
                                </label>
                                <textarea  {{$view =="true"? "disabled" : ''}} name="note" rows="1" class="form-control fw-bold" id="note" placeholder="بيان الفاتورة">{{ $purchase->note }}</textarea>
                            </div>






                        </div>
                        @if ($view !="true")

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
                        @endif

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
        src="{{ asset('assets/module/purchase_j.js') }}?t={{ config('global.ver.version_all') }}"></script>
    <script>
        $('.input_date_').flatpickr({
            format: 'dd-mm-yyyy',
            "locale": "ar",
        });
        $('#add_file').on('click', function() {
            var newfield =
                '<div class="form-group row repeat"><div class="input-group "><div class="form-control custom-file"><input {{$view =="true"? "disabled" : ''}}  type="file" class="form-control custom-file-input" name="files[]" ></div><div class="input-group-append" style="padding: 0.7rem 1rem;"><a class="btn btn-lg btn-danger remove"  ><span><i class="la la-minus" style="color:#fff"></i></span></a></div></div></div>';
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

        function del_file_multi(purchase_id, ssnfile_url, type, i) {
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
                        url: "{{ route('dashboard.purchase.delete_file') }}",
                        'type': 'POST',
                        'dataType': 'json',
                        'async': false,
                        'data': {
                            purchase_id: purchase_id,
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

        function del_file(purchase_id, ssnfile_url, type) {
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
                        url: "{{ route('dashboard.purchase.delete_file') }}",
                        'type': 'POST',
                        'dataType': 'json',
                        'async': false,
                        'data': {
                            purchase_id: purchase_id,
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
    @if (!$can_edit and $view_page!="true")
    <script>

    swal.fire({
        title: 'لا يمكنك تعديل هذه الفاتورة ! ' ,
        text: 'التعديل غير مسموح لهذا البند يرجى مراجعة الموظف المختص وشكراً .',
        icon: 'warning',

    })
</script>

    @endif
