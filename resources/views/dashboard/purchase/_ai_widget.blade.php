{{-- T-B4 — AI widget for the purchase (المشتريات) add form. Reuses the existing
     invoice-extraction pipeline via PurchaseController::aiExtract(). Prefills the
     exact input ids of resources/views/dashboard/purchase/index.blade.php:
     purchase_no, purchase_dt, purchase_respon, purchase_price, tax_number, note.
     Nothing is saved by this widget — the user reviews the prefilled fields and
     submits the normal add form (#save_purchase), which writes to `purchase` via
     PurchaseController::store(). Mirrors the expense/shop AI widget pattern. --}}
<div class="col-12 mb-4">
    <div class="card bg-light-primary border border-primary border-dashed">
        <div class="card-body py-4">
            <label class="fw-bold text-primary mb-2"><i class="fa fa-robot me-1"></i> استخراج بالذكاء الاصطناعي — ارفع صورة أو PDF للفاتورة</label>
            <div class="d-flex gap-2 align-items-center">
                <input type="file" id="ai_invoice" accept=".pdf,.jpg,.jpeg,.png,.webp" class="form-control form-control-sm">
                <button type="button" id="ai_purchase_extract_btn" class="btn btn-sm btn-primary text-nowrap">استخراج</button>
            </div>
            <div id="ai_purchase_extract_status" class="fs-8 text-muted mt-2"></div>
        </div>
    </div>
</div>

<script>
(function(){
    var btn = document.getElementById('ai_purchase_extract_btn');
    if (!btn || btn.dataset.bound) { return; }
    btn.dataset.bound = 1;
    btn.addEventListener('click', function(){
        var f = document.getElementById('ai_invoice');
        var st = document.getElementById('ai_purchase_extract_status');
        if (!f.files.length) { st.innerHTML = '<span class="text-danger">اختر ملف الفاتورة أولاً</span>'; return; }
        var fd = new FormData();
        fd.append('invoice', f.files[0]);
        fd.append('_token', '{{ csrf_token() }}');
        st.textContent = 'جارٍ الاستخراج بالذكاء الاصطناعي...';
        btn.disabled = true;
        fetch('{{ route('dashboard.purchase.ai_extract') }}', {method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .then(function(r){ return r.json(); })
            .then(function(res){
                btn.disabled = false;
                if (!res.status) { st.innerHTML = '<span class="text-danger">' + (res.message_out || 'فشل الاستخراج') + '</span>'; return; }
                var d = res.data;
                function setv(id, v) {
                    var el = document.getElementById(id);
                    if (el && v != null && v !== '') { el.value = v; el.dispatchEvent(new Event('change')); }
                }
                setv('purchase_no', d.purchase_no);
                setv('purchase_dt', d.purchase_dt);
                setv('purchase_respon', d.purchase_respon);
                setv('purchase_price', d.purchase_price);
                setv('tax_number', d.tax_number);
                setv('note', d.note);
                var msg = '<span class="text-success">تم الاستخراج ✓ راجع الحقول ثم اختر المحل/قائد المجموعة واحفظ</span>';
                if (d.needs_review) { msg += ' — <span class="text-warning">تحتاج مراجعة يدوية لبعض الحقول</span>'; }
                st.innerHTML = msg;
            })
            .catch(function(){ btn.disabled = false; st.innerHTML = '<span class="text-danger">خطأ في الاتصال</span>'; });
    });
})();
</script>
