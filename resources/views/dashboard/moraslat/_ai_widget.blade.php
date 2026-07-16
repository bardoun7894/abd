{{-- Spec 004 C1 — AI widget for the moraslat (correspondence) add form. Shared across
     moraslat_shop / moraslat_workall / moraslat_workspec (they share field ids:
     moraslat_respon, note, moraslat_categoty_id). Mirrors the expense AI widget pattern.
     NOTE: this add form has no موراسلة status select (moraslat_status_id is only set later
     via open_moraslat/updopenstore), so the status suggestion is informational only here.
     The type suggestion is also informational only — auto-switching #moraslat_type_id would
     trigger load_moraslat_form() and reload this very partial via AJAX, wiping the widget. --}}
<div class="col-12 mb-4">
    <div class="card bg-light-primary border border-primary border-dashed">
        <div class="card-body py-4">
            <label class="fw-bold text-primary mb-2"><i class="fa fa-robot me-1"></i> تحليل بالذكاء الاصطناعي — ارفع صورة أو PDF لخطاب المراسلة</label>
            <div class="d-flex gap-2 align-items-center">
                <input type="file" id="ai_letter" accept=".pdf,.jpg,.jpeg,.png,.webp" class="form-control form-control-sm">
                <button type="button" id="ai_analyze_btn" class="btn btn-sm btn-primary text-nowrap">تحليل</button>
            </div>
            <div id="ai_analyze_status" class="fs-8 text-muted mt-2"></div>
        </div>
    </div>
</div>

<div class="col-12 mb-4">
    <div class="card bg-light-info border border-info border-dashed">
        <div class="card-body py-4">
            <label class="fw-bold text-info mb-2"><i class="fa fa-reply me-1"></i> صياغة رد بالذكاء الاصطناعي</label>
            <div class="d-flex gap-2 align-items-center mb-2">
                <button type="button" id="ai_draft_btn" class="btn btn-sm btn-info text-nowrap">صياغة رد</button>
                <span id="ai_draft_status" class="fs-8 text-muted"></span>
            </div>
            <textarea id="ai_reply_draft" rows="4" class="form-control fw-bold" placeholder="سيظهر هنا مسودة الرد المقترحة — للمراجعة والاستخدام كمرجع"></textarea>
        </div>
    </div>
</div>

<script>
(function(){
    var abtn = document.getElementById('ai_analyze_btn');
    if (abtn && !abtn.dataset.bound) {
        abtn.dataset.bound = 1;
        abtn.addEventListener('click', function(){
            var f = document.getElementById('ai_letter');
            var st = document.getElementById('ai_analyze_status');
            if (!f.files.length) { st.innerHTML = '<span class="text-danger">اختر ملف الخطاب أولاً</span>'; return; }
            var fd = new FormData();
            fd.append('letter', f.files[0]);
            fd.append('_token', '{{ csrf_token() }}');
            st.textContent = 'جارٍ التحليل بالذكاء الاصطناعي...';
            abtn.disabled = true;
            fetch('{{ route('dashboard.moraslat.ai_analyze') }}', {method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .then(function(r){ return r.json(); })
                .then(function(res){
                    abtn.disabled = false;
                    if (!res.status) { st.innerHTML = '<span class="text-danger">' + (res.message_out || 'فشل التحليل') + '</span>'; return; }
                    var d = res.data;
                    function setv(id, v) {
                        var el = document.getElementById(id);
                        if (el && v != null && v !== '') { el.value = v; el.dispatchEvent(new Event('change')); }
                    }
                    setv('moraslat_respon', d.summary);
                    var noteParts = [];
                    if (d.subject) { noteParts.push('الموضوع: ' + d.subject); }
                    if (d.sender) { noteParts.push('المرسل: ' + d.sender); }
                    if (d.date) { noteParts.push('التاريخ: ' + d.date); }
                    if (d.key_points && d.key_points.length) { noteParts.push('أهم النقاط: ' + d.key_points.join('، ')); }
                    setv('note', noteParts.join(' — '));
                    if (d.moraslat_categoty_id) {
                        var s = document.getElementById('moraslat_categoty_id');
                        if (s) { s.value = String(d.moraslat_categoty_id); if (window.jQuery) { jQuery(s).trigger('change'); } }
                    }
                    var msg = '<span class="text-success">تم التحليل ✓ راجع الحقول ثم احفظ</span>';
                    if (d.category_name) { msg += ' — التصنيف المقترح: ' + d.category_name; }
                    if (d.type_name) { msg += ' — نوع المراسلة المقترح: ' + d.type_name + ' (غيّره أعلاه يدوياً إن اختلف عن النوع الحالي)'; }
                    st.innerHTML = msg;
                })
                .catch(function(){ abtn.disabled = false; st.innerHTML = '<span class="text-danger">خطأ في الاتصال</span>'; });
        });
    }

    var dbtn = document.getElementById('ai_draft_btn');
    if (dbtn && !dbtn.dataset.bound) {
        dbtn.dataset.bound = 1;
        dbtn.addEventListener('click', function(){
            var ctxEl = document.getElementById('moraslat_respon');
            var st = document.getElementById('ai_draft_status');
            var ctx = (ctxEl && ctxEl.value ? ctxEl.value : '').trim();
            if (!ctx) { st.innerHTML = '<span class="text-danger">لا يوجد نص للمعاملة لصياغة رد عليه</span>'; return; }
            st.textContent = 'جارٍ الصياغة...';
            dbtn.disabled = true;
            fetch('{{ route('dashboard.moraslat.ai_draft') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({context: ctx})
            })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    dbtn.disabled = false;
                    if (!res.status) { st.innerHTML = '<span class="text-danger">' + (res.message_out || 'فشلت الصياغة') + '</span>'; return; }
                    document.getElementById('ai_reply_draft').value = res.data.draft;
                    st.innerHTML = '<span class="text-success">تم ✓</span>';
                })
                .catch(function(){ dbtn.disabled = false; st.innerHTML = '<span class="text-danger">خطأ في الاتصال</span>'; });
        });
    }
})();
</script>
