
<div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
    <label for="expense_categoty_id" class="form-label required  fs-6 fw-bold text-dark mb-3">التصنيف</label>
    <div>
        <select class="form-select fw-bold  " data-control="select2" id="expense_categoty_id" name="expense_categoty_id"
            dir="rtl">
            <option value="">اختر ..</option>
            @foreach ($expense_categoty as $x)
                <option value="{{ $x->expense_categoty_id }} ">{{ $x->expense_categoty_name }}</option>
            @endforeach
        </select>
    </div>
</div>



<div class=" col-12 col-lg-4 col-md-12 col-sm-12 mb-5"><label for="expense_respon"
        class="form-label  fs-6 fw-bold text-dark mb-3">تفصيل الصرف</label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-clone fa-fw text-dark"></i></span></div><input type="text" name="expense_respon"
            id="expense_respon" class="form-control fw-bold  text-dark" placeholder="تفصيل الصرف " autocomplete="off">
    </div>
</div>














<div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
    <label for="expense_price" class="form-label  fs-6 fw-bold text-dark mb-3">المبلغ
    </label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-id-card fa-fw text-dark"></i></span></div><input type="text" name="expense_price"
            id="expense_price" class="form-control fw-bold text-dark text-info " data-inputmask="'alias' : 'decimal'"
            minlenght="1" maxlength="20" placeholder="المبلغ">
    </div>
</div>


<div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
    <label for="manager_id" class="form-label   fs-6 fw-bold text-dark mb-3">قائد
        المحل</label>
    <div>
        <select class="form-select fw-bold  " data-control="select2" id="manager_id" name="manager_id" dir="rtl"
            data-placeholder="قائد المحل">
            <option value="">اختر ..</option>
            @foreach ($manager as $x)
                <option value="{{ $x->manager_id }} ">{{ $x->manager_name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class=" col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
    <label for="expensefile" class="form-label  fs-6 fw-bold text-dark mb-3">إرفاق صورة
        للفاتورة</label>
    <input class="form-control custom-file-input" type="file" name='expensefile'>
</div>
<div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
    <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">ملاحظات
    </label>
    <textarea name="note" rows="1" class="form-control fw-bold" id="note" placeholder="ملاحظات"></textarea>
</div>











<script>
    $(".form-select").select2({

    });


</script>
