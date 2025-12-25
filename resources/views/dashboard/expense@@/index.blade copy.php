@extends('layouts.app')
@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري ')
@section('title', "$page_title")
@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif


    <div id="user_reg" class="alert alert-danger d-none"></div>
    <form id="save_shop" name="save_shop" class="form" action="{{ route('dashboard.shop.store') }}"
        enctype="multipart/form-data" autocomplete="off" method="POST">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
                <div class="card">
                    <div class="card-body px-1">
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
                        <div class="mb-0">
                            <div class="row gx-5 mb-5">

                                <div class=" col-12 col-lg-4 col-md-12 col-sm-12 mb-5"><label for="shop_name"
                                        class="form-label required fs-6 fw-bold text-dark mb-3">اسم المحل</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-store-alt fa-fw text-dark"></i></span></div><input
                                            type="text" name="shop_name" id="shop_name"
                                            class="form-control fw-bold  text-dark" placeholder="اسم المحل"
                                            value="" autocomplete="off">
                                    </div>
                                </div>

                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="calculate_month_val" class="form-label required fs-6 fw-bold text-dark mb-3">المبلغ
                                        المطلوب</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                            type="text" name="calculate_month_val" id="calculate_month_val"
                                            class="form-control fw-bold text-dark text-info "
                                            data-inputmask="'alias' : 'decimal'" minlenght="1" maxlength="20"
                                            placeholder="المبلغ المطلوب">
                                    </div>
                                </div>


                                <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                    <label for="manager_id" class="form-label required  fs-6 fw-bold text-dark mb-3">قائد المحل</label>
                                    <div>
                                        <select class="form-select fw-bold  " data-control="select2" id="manager_id"
                                            name="manager_id" dir="rtl" data-placeholder="قائد المحل">
                                            <option value="">اختر ..</option>
                                            @foreach ($manager as $x)
                                                <option value="{{ $x->manager_id }} ">{{ $x->manager_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class=" col-12 col-lg-3 col-md-12 col-sm-12 mb-5"><label for="shop_respon"
                                    class="form-label required fs-6 fw-bold text-dark mb-3">اسم المسؤول</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-user-tie fa-fw text-dark"></i></span></div><input
                                        type="text" name="shop_respon" id="shop_respon"
                                        class="form-control fw-bold  text-dark" placeholder="اسم المسؤول"
                                        value="" autocomplete="off">
                                </div>
                            </div>
                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="shop_mobile" class="form-label  fs-6 fw-bold text-dark mb-3">رقم جوال المسؤول</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-phone-volume fa-fw text-dark"></i></span></div><input
                                            type="text" name="shop_mobile" id="shop_mobile"
                                            class="form-control fw-bold text-dark text-info" minlenght="1"
                                            maxlength="20" placeholder="رقم جوال المسؤول">
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                    <label for="city_id" class="form-label  fs-6 fw-bold text-dark mb-3">المدينة</label>
                                    <div>
                                        <select class="form-select fw-bold  " data-control="select2" id="city_id"
                                            name="city_id" dir="rtl" data-placeholder="المدينة">
                                            <option value="">اختر ..</option>
                                            @foreach ($city as $x)
                                                <option value="{{ $x->city_id }} ">{{ $x->city_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                    <label for="shop_location" class="form-label  fs-6 fw-bold text-dark mb-3">موقع المحل</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-map-marker-alt fa-fw text-dark"></i></span></div><input
                                            type="text" name="shop_location" id="shop_location"
                                            class="form-control fw-bold text-dark text-dark" minlenght="1"
                                            placeholder="موقع المحل">
                                    </div>
                                </div>

                                <div class=" col-12 col-lg-4 col-md-12 col-sm-12  mb-5">
                                    <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">الملاحظة
                                    </label>
                                    <textarea name="note" rows="1" class="form-control fw-bold" id="note" placeholder="الملاحظة"></textarea>
                                </div>

                            </div>
                            <div class=" mb-2 d-flex justify-content ">
                                <button type="submit" id="kt_docs_formvalidation_text_submit"
                                    class="btn btn-primary font-weight-bold mr-2" name="submitButton">حفظ
                                    البيانات</button>
                                &nbsp;&nbsp;
                                <button type="reset" class="btn btn-light font-weight-bold mr-2">تفريغ البيانات</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/module/shop_j.js') }}?t={{ config('global.ver.version_all') }}">
    </script>
    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
@endsection
