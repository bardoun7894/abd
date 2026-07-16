
<div class="col-12 mb-4">
    <div class="card bg-light-primary border border-primary border-dashed">
        <div class="card-body py-4">
            <label class="fw-bold text-primary mb-2"><i class="fa fa-robot me-1"></i> استخراج بالذكاء الاصطناعي — ارفع صورة أو PDF للإيصال</label>
            <div class="d-flex gap-2 align-items-center">
                <input type="file" id="ai_receipt" accept=".pdf,.jpg,.jpeg,.png,.webp" class="form-control form-control-sm">
                <button type="button" id="ai_extract_btn" class="btn btn-sm btn-primary text-nowrap">استخراج</button>
            </div>
            <div id="ai_extract_status" class="fs-8 text-muted mt-2"></div>
        </div>
    </div>
</div>
<script>
(function(){
    var btn=document.getElementById('ai_extract_btn'); if(!btn||btn.dataset.bound) return; btn.dataset.bound=1;
    btn.addEventListener('click', function(){
        var f=document.getElementById('ai_receipt'); var st=document.getElementById('ai_extract_status');
        if(!f.files.length){ st.innerHTML='<span class="text-danger">اختر ملف الإيصال أولاً</span>'; return; }
        var fd=new FormData(); fd.append('receipt', f.files[0]); fd.append('_token','{{ csrf_token() }}');
        st.textContent='جارٍ الاستخراج بالذكاء الاصطناعي...'; btn.disabled=true;
        fetch('{{ route('dashboard.expense.ai_extract') }}',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){return r.json();}).then(function(res){
            btn.disabled=false;
            if(!res.status){ st.innerHTML='<span class="text-danger">'+(res.message_out||'فشل الاستخراج')+'</span>'; return; }
            var d=res.data;
            function setv(id,v){ var el=document.getElementById(id); if(el&&v!=null&&v!==''){ el.value=v; el.dispatchEvent(new Event('change')); } }
            setv('expense_price', d.expense_price); setv('expense_respon', d.expense_respon); setv('note', d.note); setv('expense_month_desc', d.date);
            if(d.expense_categoty_id){ var s=document.getElementById('expense_categoty_id'); if(s){ s.value=String(d.expense_categoty_id); if(window.jQuery){ jQuery(s).trigger('change'); } } }
            st.innerHTML='<span class="text-success">تم الاستخراج ✓ راجع الحقول ثم احفظ</span>'+(d.category_name?(' — التصنيف المقترح: '+d.category_name):'');
        }).catch(function(){ btn.disabled=false; st.innerHTML='<span class="text-danger">خطأ في الاتصال</span>'; });
    });
})();
</script>

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

<div class=" col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
    <label for="expense_month_desc" class="form-label required fs-6 fw-bold text-dark mb-3">شهر الدفع
        :</label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-calendar-alt fa-fw text-dark"></i></span></div><input
            type="text" name="expense_month_desc" id="expense_month_desc"
            class="form-control fw-bold  text-info input_date_full_"
            placeholder="شهر الدفع " value="" autocomplete="off">
    </div>
</div>


<div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
    <label for="expense_price" class="form-label required fs-6 fw-bold text-dark mb-3">المبلغ المطلوب
    </label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-id-card fa-fw text-dark"></i></span></div><input type="text" name="expense_price"
            id="expense_price" class="form-control fw-bold text-dark text-info " data-inputmask="'alias' : 'decimal'"
            minlenght="1" maxlength="20" placeholder="المبلغ المطلوب" oninput="calc_expense_price()">
    </div>
</div>
<div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
    <label for="expense_month_pay" class="form-label required fs-6 fw-bold text-dark mb-3">المبلغ المدفوع
    </label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-id-card fa-fw text-dark"></i></span></div><input type="text" name="expense_month_pay"
            id="expense_month_pay" class="form-control fw-bold text-dark text-info " data-inputmask="'alias' : 'decimal'"
            minlenght="1" maxlength="20" placeholder="المبلغ المدفوع" oninput="calc_expense_price()">
    </div>
</div>
<div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
    <label for="expense_month_remain" class="form-label required fs-6 fw-bold text-dark mb-3">المبلغ المتبقي
    </label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-id-card fa-fw text-dark"></i></span></div><input type="text" name="expense_month_remain"
            id="expense_month_remain" class="form-control fw-bold text-dark text-info form-control-solid" data-inputmask="'alias' : 'decimal'"
            minlenght="1" maxlength="20" placeholder="المبلغ المتبقي" readonly>
    </div>
</div>

<div class=" col-12 col-lg-5 col-md-12 col-sm-12 mb-5"><label for="expense_respon"
        class="form-label  fs-6 fw-bold text-dark mb-3">تفصيل الصرف</label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-clone fa-fw text-dark"></i></span></div><input type="text" name="expense_respon"
            id="expense_respon" class="form-control fw-bold  text-dark" placeholder="تفصيل الصرف " autocomplete="off">
    </div>
</div>















<div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
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
<div class=" col-12 col-lg-5 col-md-12 col-sm-12 mb-5">
    <label for="expensefile" class="form-label  fs-6 fw-bold text-dark mb-3">إرفاق صورة
        للفاتورة</label>
    <input class="form-control custom-file-input" type="file" name='expensefile'>
</div>
<div class=" col-12 col-lg-5 col-md-12 col-sm-12  mb-5">
    <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">ملاحظات
    </label>
    <textarea name="note" rows="1" class="form-control fw-bold" id="note" placeholder="ملاحظات"></textarea>
</div>











<script>
    function calc_expense_price() {
        var expense_price_v = parseFloat($('#expense_price').val());
        var expense_month_pay_v = parseFloat($('#expense_month_pay').val());
        var expense_month_remain_v = parseFloat($('#expense_month_remain').val());
        if (isNaN(expense_price_v)) {
            expense_price_v = 0;
            $('#expense_price').val(0);
        }
        if (isNaN(expense_month_pay_v)) {
            expense_month_pay_v = 0;
            $('#expense_month_pay').val(0);
        }
        expense_month_remain_v = expense_price_v - expense_month_pay_v;
        if (!isNaN(expense_month_remain_v)) {
            $('#expense_month_remain').val(expense_month_remain_v);
        }
    }

    $(".form-select").select2({

    });

    $('.input_date_full_').flatpickr({
    "locale": "ar" ,
    plugins: [
        new monthSelectPlugin({
          shorthand: false,
          dateFormat: "m-Y",
          altFormat: "F Y",
          theme: "light",

        }),
    ],
    onChange: function(selectedDates, dateStr, instance) {
      },
    onClose: function(selectedDates, dateStr, instance){},
});
</script>
