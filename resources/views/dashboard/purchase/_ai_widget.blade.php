{{-- T-B4 — AI widget for the purchase (المشتريات) add form. Reuses the existing
     invoice-extraction pipeline via PurchaseController::aiExtract(). Prefills the
     exact input ids of resources/views/dashboard/purchase/index.blade.php:
     purchase_no, purchase_dt, purchase_respon, purchase_price, tax_number, note.
     Nothing is saved by this widget — the user reviews the prefilled fields and
     submits the normal add form (#save_purchase), which writes to `purchase` via
     PurchaseController::store(). Mirrors the expense/shop AI widget pattern. --}}
@once
<style>
.ai-card{position:relative;border:1px solid rgba(14,107,79,.18);border-radius:.95rem;background:linear-gradient(180deg,rgba(14,107,79,.06) 0%,rgba(255,255,255,0) 65%);overflow:hidden;transition:box-shadow .2s ease;}
.ai-card::before{content:"";position:absolute;inset-inline-start:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,#0E6B4F,#0A4F3A);}
.ai-card:hover{box-shadow:0 .5rem 1.5rem rgba(14,107,79,.12);}
.ai-card-head{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.75rem;}
.ai-icon-badge{display:inline-flex;align-items:center;justify-content:center;width:2.35rem;height:2.35rem;border-radius:.65rem;background:linear-gradient(135deg,#0E6B4F,#0A4F3A);color:#fff;font-size:1rem;flex:0 0 auto;box-shadow:0 .35rem .85rem rgba(14,107,79,.35);}
.ai-card-title{font-weight:700;margin:0;}
.ai-pill{display:inline-flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:700;line-height:1;padding:.35rem .6rem;border-radius:50rem;color:#fff;background:linear-gradient(135deg,#0E6B4F,#0A4F3A);letter-spacing:.02em;white-space:nowrap;}
.ai-dropzone{position:relative;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;border:1.5px dashed rgba(14,107,79,.45);border-radius:.75rem;padding:.85rem 1rem;background:rgba(14,107,79,.04);transition:border-color .15s ease,background-color .15s ease;}
.ai-dropzone.is-dragover{border-color:#0E6B4F;background:rgba(14,107,79,.1);}
/* B2 — a11y: visible focus ring when the drop-zone itself is keyboard-focused (additive). */
.ai-dropzone:focus-visible{outline:3px solid #0E6B4F;outline-offset:2px;}
.ai-dropzone__label{display:flex;align-items:center;gap:.6rem;flex:1 1 220px;min-width:0;cursor:pointer;margin:0;}
.ai-dropzone__icon{font-size:1.3rem;color:#0E6B4F;flex:0 0 auto;}
.ai-dropzone__text{display:flex;flex-direction:column;gap:.1rem;min-width:0;}
.ai-dropzone__hint{font-weight:600;font-size:.85rem;}
.ai-dropzone__filename{font-size:.76rem;color:#7e8299;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.ai-dropzone__filename.has-file{color:#0E6B4F;font-weight:600;}
.ai-dropzone__input{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;}
.ai-dropzone .btn{flex:0 0 auto;}
.ai-status{font-size:.8rem;min-height:1.1rem;}
.ai-status.is-loading{display:inline-flex;align-items:center;gap:.45rem;color:#0E6B4F;font-weight:600;}
.ai-status.is-loading::before{content:"";width:.8rem;height:.8rem;border-radius:50%;border:2px solid currentColor;border-inline-end-color:transparent;animation:ai-spin .7s linear infinite;flex:0 0 auto;}
.ai-status .text-success{font-weight:700;}
.ai-status .text-danger{font-weight:700;}
.ai-status .text-warning{font-weight:700;}
@keyframes ai-spin{to{transform:rotate(360deg);}}
.ai-low-conf { outline:2px solid #f1b44c !important; outline-offset:1px; background:#fff8ec !important; }
.ai-conf-hint { color:#e0a800; font-size:.72rem; margin-top:2px; display:block; }
/* T6-motion: analyzing / result-reveal / low-conf pulse / dropzone affordance / card entrance (additive) */
@keyframes ai-card-in{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.ai-card{animation:ai-card-in .4s cubic-bezier(0.22,1,0.36,1) both;}
@keyframes ai-icon-breathe{0%,100%{transform:scale(1);}50%{transform:scale(1.08);}}
.ai-card.ai-is-analyzing .ai-icon-badge{animation:ai-icon-breathe 1.1s ease-in-out infinite;}
@keyframes ai-status-dots{0%,100%{content:"";}25%{content:".";}50%{content:"..";}75%{content:"...";}}
.ai-status.is-loading::after{content:"";display:inline-block;min-width:1.2em;text-align:start;animation:ai-status-dots 1.2s steps(1) infinite;}
@keyframes ai-scan-sweep{0%{transform:translateY(-100%);opacity:0;}10%{opacity:1;}90%{opacity:1;}100%{transform:translateY(220%);opacity:0;}}
.ai-card.ai-is-analyzing .ai-dropzone{overflow:hidden;}
.ai-card.ai-is-analyzing .ai-dropzone::after{content:"";position:absolute;inset-inline:0;top:0;height:40%;background:linear-gradient(180deg,rgba(14,107,79,0) 0%,rgba(14,107,79,.35) 50%,rgba(14,107,79,0) 100%);pointer-events:none;animation:ai-scan-sweep 1.6s ease-in-out infinite;}
.ai-dropzone{transition:border-color .15s ease,background-color .15s ease,transform .2s cubic-bezier(0.22,1,0.36,1),box-shadow .2s ease;}
.ai-dropzone:hover,.ai-dropzone.is-dragover{transform:translateY(-2px);box-shadow:0 .4rem 1rem rgba(14,107,79,.18);}
@keyframes ai-preview-in{from{opacity:0;transform:scale(.96);}to{opacity:1;transform:scale(1);}}
[id^="ai_preview_"] img,[id^="ai_preview_"] .badge{animation:ai-preview-in .3s cubic-bezier(0.22,1,0.36,1) both;}
@keyframes ai-field-flash{0%{background-color:rgba(14,107,79,.22);}100%{background-color:transparent;}}
.ai-field-flash{animation:ai-field-flash .6s ease-out both;}
@keyframes ai-check-pop{0%{opacity:0;transform:scale(.4);}60%{opacity:1;transform:scale(1.15);}100%{opacity:1;transform:scale(1);}}
.ai-check-pop{display:inline-flex;align-items:center;justify-content:center;width:1.05rem;height:1.05rem;border-radius:50%;background:#17c653;color:#fff;font-size:.65rem;margin-inline-end:.3rem;animation:ai-check-pop .35s cubic-bezier(0.22,1,0.36,1) both;}
@keyframes ai-low-conf-pulse{0%,100%{box-shadow:0 0 0 0 rgba(241,180,76,.55);}50%{box-shadow:0 0 0 6px rgba(241,180,76,0);}}
.ai-low-conf{animation:ai-low-conf-pulse 1s ease-out 2;}
@keyframes ai-chip-in{from{opacity:0;transform:translateY(4px);}to{opacity:1;transform:translateY(0);}}
.ai-fields-chip{display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;font-weight:600;color:#0E6B4F;background:rgba(14,107,79,.1);border-radius:50rem;padding:.15rem .55rem;margin-inline-start:.4rem;animation:ai-chip-in .25s cubic-bezier(0.22,1,0.36,1) both;}
@keyframes ai-btn-in{from{opacity:0;transform:scale(.85);}to{opacity:1;transform:scale(1);}}
.ai-reanalyze-btn{animation:ai-btn-in .25s cubic-bezier(0.22,1,0.36,1) both;margin-inline-start:.4rem;}
@media (prefers-reduced-motion: reduce){
.ai-status.is-loading::before{animation:none;}
.ai-card,.ai-card.ai-is-analyzing .ai-icon-badge,.ai-status.is-loading::after,.ai-card.ai-is-analyzing .ai-dropzone::after,.ai-dropzone,.ai-dropzone:hover,.ai-dropzone.is-dragover,[id^="ai_preview_"] img,[id^="ai_preview_"] .badge,.ai-field-flash,.ai-check-pop,.ai-low-conf,.ai-fields-chip,.ai-reanalyze-btn{animation:none !important;transition:none !important;transform:none !important;}
}
</style>
@endonce
<div class="col-12 mb-4">
    <div class="card ai-card">
        <div class="card-body py-4">
            <div class="ai-card-head">
                <span class="ai-icon-badge"><i class="fa fa-robot"></i></span>
                <h3 class="ai-card-title fs-6 text-primary">استخراج بالذكاء الاصطناعي</h3>
                <span class="ai-pill"><i class="fa fa-magic"></i> ذكاء اصطناعي</span>
            </div>
            <p class="text-muted fs-8 mb-3">ارفع صورة أو PDF للفاتورة ليتم استخراج بياناتها تلقائياً</p>
            <div class="ai-dropzone" id="ai_invoice_dropzone" role="button" tabindex="0" aria-label="منطقة رفع ملف الفاتورة — اسحب الملف هنا أو اضغط للاختيار">
                <label for="ai_invoice" class="ai-dropzone__label">
                    <i class="fa fa-cloud-upload-alt ai-dropzone__icon"></i>
                    <span class="ai-dropzone__text">
                        <span class="ai-dropzone__hint">اسحب الملف أو اضغط للاختيار</span>
                        <span class="ai-dropzone__filename" id="ai_invoice_filename"></span>
                    </span>
                </label>
                <input type="file" id="ai_invoice" accept=".pdf,.jpg,.jpeg,.png,.webp" class="form-control form-control-sm ai-dropzone__input">
                <button type="button" id="ai_purchase_extract_btn" class="btn btn-sm btn-primary text-nowrap"><i class="fa fa-magic me-1"></i>استخراج</button>
            </div>
            <div id="ai_preview_purchase" class="mt-2"></div>
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
                // --- T6-1: confidence highlighting, appended after the existing prefill lines ---
                try {
                    var _conf = (res.data && res.data.confidence) || {};
                    var _map = { invoice_number:'purchase_no', invoice_date:'purchase_dt', supplier_name:'purchase_respon', total_incl_vat:'purchase_price', supplier_tax_number:'tax_number' };
                    Object.keys(_map).forEach(function(k){
                        var el = document.getElementById(_map[k]);
                        if (!el) return;
                        var c = _conf[k];
                        var old = document.getElementById('conf_hint_'+_map[k]); if (old) old.remove();
                        el.classList.remove('ai-low-conf');
                        if (typeof c === 'number' && c < 0.7) {
                            el.classList.add('ai-low-conf');
                            var h = document.createElement('small'); h.className='ai-conf-hint'; h.id='conf_hint_'+_map[k];
                            h.textContent = '⚠ ثقة منخفضة ('+Math.round(c*100)+'%) — راجع الحقل';
                            el.parentNode.insertBefore(h, el.nextSibling);
                        }
                    });
                } catch(e) {}
                var msg = '<span class="text-success">تم الاستخراج ✓ راجع الحقول ثم اختر المحل/قائد المجموعة واحفظ</span>';
                if (d.needs_review) { msg += ' — <span class="text-warning">تحتاج مراجعة يدوية لبعض الحقول</span>'; }
                st.innerHTML = msg;
            })
            .catch(function(){ btn.disabled = false; st.innerHTML = '<span class="text-danger">خطأ في الاتصال</span>'; });
    });
})();
</script>

<script>
(function(){
    function enhance(opts){
        var input = opts.inputId ? document.getElementById(opts.inputId) : null;
        var zone = opts.zoneId ? document.getElementById(opts.zoneId) : null;
        var nameEl = opts.nameId ? document.getElementById(opts.nameId) : null;
        if (input && !input.dataset.aiUx) {
            input.dataset.aiUx = '1';
            input.addEventListener('change', function(){
                if (!nameEl) { return; }
                if (input.files && input.files.length) {
                    nameEl.textContent = input.files[0].name;
                    nameEl.classList.add('has-file');
                } else {
                    nameEl.textContent = '';
                    nameEl.classList.remove('has-file');
                }
            });
        }
        if (zone && input && !zone.dataset.aiUx) {
            zone.dataset.aiUx = '1';
            ['dragenter', 'dragover'].forEach(function(evt){
                zone.addEventListener(evt, function(e){ e.preventDefault(); zone.classList.add('is-dragover'); });
            });
            ['dragleave', 'drop'].forEach(function(evt){
                zone.addEventListener(evt, function(e){ e.preventDefault(); zone.classList.remove('is-dragover'); });
            });
            zone.addEventListener('drop', function(e){
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
        (opts.statusIds || []).forEach(function(id){
            var el = document.getElementById(id);
            if (!el || el.dataset.aiWatch) { return; }
            el.dataset.aiWatch = '1';
            el.classList.add('ai-status');
            var mo = new MutationObserver(function(){
                var loading = /^جارٍ/.test((el.textContent || '').trim());
                el.classList.toggle('is-loading', loading);
            });
            mo.observe(el, {childList: true, characterData: true, subtree: true});
        });
    }
    enhance({inputId: 'ai_invoice', zoneId: 'ai_invoice_dropzone', nameId: 'ai_invoice_filename', statusIds: ['ai_purchase_extract_status']});
})();
</script>

{{-- T6-2: document preview thumbnail (additive, does not touch analyze/prefill logic) --}}
<script>
(function(){
    var inp = document.getElementById('ai_invoice');
    var box = document.getElementById('ai_preview_purchase');
    if (!inp || !box || inp.dataset.prevBound) return; inp.dataset.prevBound='1';
    inp.addEventListener('change', function(){
        box.innerHTML=''; var f=inp.files && inp.files[0]; if(!f) return;
        if (/^image\//.test(f.type)) {
            var img=document.createElement('img'); img.src=URL.createObjectURL(f);
            img.style.cssText='max-height:120px;border:1px solid #eee;border-radius:8px'; box.appendChild(img);
        } else {
            box.innerHTML='<span class="badge badge-light-primary"><i class="fa fa-file-pdf me-1"></i>'+f.name+'</span>';
        }
    });
})();
</script>

{{-- T6-motion: analyzing state class + result-reveal (field flash / check pop / fields chip) + optional
     re-analyze button. Additive — watches the same status element with a NEW, separate
     MutationObserver; does not touch the existing fetch/prefill/observer above. --}}
<script>
(function(){
    function aiMotion(opts){
        var el = document.getElementById(opts.statusId);
        if (!el || el.dataset.motionWatch) { return; }
        el.dataset.motionWatch = '1';
        var card = el.closest('.ai-card');
        var btn = opts.analyzeBtnId ? document.getElementById(opts.analyzeBtnId) : null;
        var mo = new MutationObserver(function(){
            var text = (el.textContent || '').trim();
            var loading = /^جارٍ/.test(text);
            if (card) { card.classList.toggle('ai-is-analyzing', loading); }
            if (loading) { return; }
            var success = el.querySelector('.text-success');
            if (!success) { return; }
            var n = 0, delay = 0;
            (opts.fieldIds || []).forEach(function(fid){
                var fEl = document.getElementById(fid);
                if (!fEl || !fEl.value) { return; }
                n++;
                fEl.classList.remove('ai-field-flash'); void fEl.offsetWidth;
                fEl.style.animationDelay = delay + 'ms';
                fEl.classList.add('ai-field-flash');
                delay += 40;
            });
            if (!success.querySelector('.ai-check-pop')) {
                var chk = document.createElement('span'); chk.className = 'ai-check-pop'; chk.textContent = '✓';
                success.insertBefore(chk, success.firstChild);
            }
            var oldChip = el.querySelector('.ai-fields-chip'); if (oldChip) { oldChip.remove(); }
            if (n > 0) {
                var chip = document.createElement('span'); chip.className = 'ai-fields-chip';
                chip.textContent = 'تم استخراج ' + n + ' حقول — راجع المميّزة';
                el.appendChild(chip);
            }
            if (btn && !btn.dataset.reanalyzeAdded) {
                btn.dataset.reanalyzeAdded = '1';
                var rbtn = document.createElement('button');
                rbtn.type = 'button';
                rbtn.className = 'btn btn-sm btn-light-primary text-nowrap ai-reanalyze-btn';
                rbtn.innerHTML = '<i class="fa fa-rotate-right me-1"></i>إعادة التحليل';
                rbtn.addEventListener('click', function(){ btn.click(); });
                btn.insertAdjacentElement('afterend', rbtn);
            }
        });
        mo.observe(el, {childList: true, characterData: true, subtree: true});
    }
    aiMotion({statusId: 'ai_purchase_extract_status', fieldIds: ['purchase_no', 'purchase_dt', 'purchase_respon', 'purchase_price', 'tax_number', 'note'], analyzeBtnId: 'ai_purchase_extract_btn'});
})();
</script>

{{-- B2: keyboard accessibility for the drop-zone container — Enter/Space opens the file
     picker via the existing label; a NEW, separate script that does not touch the
     analyze/prefill/motion logic above. --}}
<script>
(function aiDropzoneKeyboardActivate(){
    document.querySelectorAll('.ai-dropzone[role="button"]').forEach(function(zone){
        if (zone.dataset.kbBound) { return; }
        zone.dataset.kbBound = '1';
        zone.addEventListener('keydown', function(e){
            if (e.target !== zone) { return; }
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                e.preventDefault();
                var label = zone.querySelector('label');
                if (label) { label.click(); }
            }
        });
    });
})();
</script>
