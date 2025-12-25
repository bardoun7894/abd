<input name="rentpay_id" id="rentpay_id" value="{{$shop_rentpay->rentpay_id}}" im-insert="true"
data-inputmask="'alias' : 'integer' " type="text" style="display:none"
class="form-control kt-font-dark kt-font-bolder " readonly placeholder="shop_rentpay_id"
aria-describedby="basic-addon1">
<div class="row gx-5 mb-5">
    <div class="separator separator-content border-dark my-10 mb-8"><span
            class="w-150px fw-bold text-danger">بيانات الملاحظات</span></div>


            {{-- <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                <label for="rentpay_id" class="form-label  fs-6 fw-bold text-dark mb-3">rentpay_id </label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i
                                class="fas fa-passport fa-fw text-dark"></i></span></div><input
                        type="text" name="rentpay_id" id="rentpay_id"
                        class="form-control fw-bold text-dark text-info" minlenght="1" value="{{$shop_rentpay->rentpay_id}}"
                        maxlength="50" placeholder="رقم العقد ">
                </div>
            </div> --}}


            <div class=" col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                <label for="rentpay_dt" class="form-label  fs-6 fw-bold text-dark mb-3">تاريخ الدفعة :</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i
                                class="far fa-calendar-alt fa-fw text-dark"></i></span></div><input
                        type="text" name="rentpay_dt" id="rentpay_dt" value="{{ $shop_rentpay->rentpay_dt }}"
                        class="form-control fw-bold  text-dark input_date_"
                        placeholder="تاريخ الانتهاء" value="" autocomplete="off">
                </div>
            </div>

            <div class="col-12 col-lg-2 col-md-12 col-sm-12   mb-5">
                <label for="rentpay_price" class="form-label fs-6 fw-bold text-dark mb-3">مبلغ الايجار</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i
                                class="fas fa-dollar-sign fa-fw text-dark"></i></span></div>

                    <input type="number" name="rentpay_price" هي="rentpay_price" class="form-control " value="{{ $shop_rentpay->rentpay_price }}"
                        placeholder="مبلغ الايجار" />
                </div>
            </div>

            <div class="col-12 col-lg-8 col-md-12 col-sm-12   mb-5">
                <label for="rentpay_note" class="form-label fs-6 fw-bold text-dark mb-3">ملاحظة</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i
                                class="fas fa-flag-checkered fa-fw text-dark"></i></span></div>

                    <input type="text" name="rentpay_note" class="form-control " value="{{ $shop_rentpay->rentpay_note }}"
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
<script>

            $(document).ready(function() {
                $(".form-select_u").select2({
                    dropdownParent: $('#view_prim_const_m .modal-content')
                });
            });

            $('.input_date_').flatpickr({
            format: 'dd-mm-yyyy',
            "locale": "ar",
        });


        </script>
