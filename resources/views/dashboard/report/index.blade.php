@extends('layouts.app')
@section('module', 'نظام الحوسبة')
@section('sub', 'التقارير')
@section('title', "$page_title")
@section('content')

<style>
.ai-card{position:relative;border:1px solid rgba(14,107,79,.18);border-radius:.95rem;background:linear-gradient(180deg,rgba(14,107,79,.06) 0%,rgba(255,255,255,0) 65%);overflow:hidden;transition:box-shadow .2s ease;}
.ai-card::before{content:"";position:absolute;inset-inline-start:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,#0E6B4F,#0A4F3A);}
.ai-card:hover{box-shadow:0 .5rem 1.5rem rgba(14,107,79,.12);}
.ai-card--info{border-color:rgba(10,79,58,.18);background:linear-gradient(180deg,rgba(10,79,58,.06) 0%,rgba(255,255,255,0) 65%);}
.ai-card--info::before{background:linear-gradient(180deg,#0A4F3A,#0E6B4F);}
.ai-card-head{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.85rem;}
.ai-icon-badge{display:inline-flex;align-items:center;justify-content:center;width:2.35rem;height:2.35rem;border-radius:.65rem;background:linear-gradient(135deg,#0E6B4F,#0A4F3A);color:#fff;font-size:1rem;flex:0 0 auto;box-shadow:0 .35rem .85rem rgba(14,107,79,.35);}
.ai-card--info .ai-icon-badge{background:linear-gradient(135deg,#0A4F3A,#0E6B4F);box-shadow:0 .35rem .85rem rgba(10,79,58,.35);}
.ai-card-title{font-weight:700;margin:0;}
.ai-pill{display:inline-flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:700;line-height:1;padding:.35rem .6rem;border-radius:50rem;color:#fff;background:linear-gradient(135deg,#0E6B4F,#0A4F3A);letter-spacing:.02em;white-space:nowrap;}
.ai-card--info .ai-pill{background:linear-gradient(135deg,#0A4F3A,#0E6B4F);}
.ai-status{font-size:.8rem;min-height:1.1rem;}
.ai-status.is-loading{display:inline-flex;align-items:center;gap:.45rem;color:#0E6B4F;font-weight:600;}
.ai-status.is-loading::before{content:"";width:.8rem;height:.8rem;border-radius:50%;border:2px solid currentColor;border-inline-end-color:transparent;animation:ai-spin .7s linear infinite;flex:0 0 auto;}
.ai-status .text-success{font-weight:700;}
.ai-status .text-danger{font-weight:700;}
@keyframes ai-spin{to{transform:rotate(360deg);}}
.ai-answer-box{border:1px dashed rgba(10,79,58,.35);border-radius:.65rem;padding:.85rem 1rem;background:rgba(10,79,58,.04);min-height:2.5rem;}
@media (prefers-reduced-motion: reduce){.ai-status.is-loading::before{animation:none;}}
</style>

<div class="d-flex flex-column">

    <div class="col-12 mb-4">
        <div class="card ai-card">
            <div class="card-body py-4">
                <div class="ai-card-head">
                    <span class="ai-icon-badge"><i class="fa fa-robot"></i></span>
                    <h3 class="ai-card-title fs-6 text-primary">ملخص ذكي للفترة</h3>
                    <span class="ai-pill"><i class="fa fa-magic"></i> ذكاء اصطناعي</span>
                </div>
                <div class="row gx-3 align-items-end mb-3">
                    <div class="col-6 col-md-3">
                        <label for="report_ai_date_from" class="form-label fs-7">من تاريخ</label>
                        <input type="date" id="report_ai_date_from" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="report_ai_date_to" class="form-label fs-7">إلى تاريخ</label>
                        <input type="date" id="report_ai_date_to" class="form-control form-control-sm">
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="button" id="report_ai_narrate_btn" class="btn btn-sm btn-primary text-nowrap"><i class="fa fa-magic me-1"></i>توليد الملخص</button>
                    </div>
                </div>
                <div id="report_ai_narrate_status" class="fs-8 text-muted mb-2"></div>
                <textarea id="report_ai_narrate_text" rows="3" class="form-control fw-bold" readonly placeholder="سيظهر هنا ملخص سردي للأرقام المالية للفترة المحددة"></textarea>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="card ai-card ai-card--info">
            <div class="card-body py-4">
                <div class="ai-card-head">
                    <span class="ai-icon-badge"><i class="fa fa-comments"></i></span>
                    <h3 class="ai-card-title fs-6 text-info">اسأل بياناتك</h3>
                    <span class="ai-pill"><i class="fa fa-magic"></i> ذكاء اصطناعي</span>
                </div>
                <div class="d-flex gap-2 align-items-center mb-2">
                    <input type="text" id="report_ai_question" class="form-control form-control-sm" placeholder="مثال: كم بلغ صافي الفرق هذه الفترة؟">
                    <button type="button" id="report_ai_ask_btn" class="btn btn-sm btn-info text-nowrap"><i class="fa fa-magic me-1"></i>اسأل</button>
                </div>
                <div id="report_ai_ask_status" class="fs-8 text-muted mb-2"></div>
                <div id="report_ai_answer" class="fw-bold ai-answer-box"></div>
            </div>
        </div>
    </div>

</div>

<script>
(function(){
    function escapeHtml(s) { return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }
    function setStatus(el, cls, text) { el.textContent = ''; var sp = document.createElement('span'); sp.className = cls; sp.textContent = text; el.appendChild(sp); }

    function fromToParams() {
        var f = document.getElementById('report_ai_date_from').value;
        var t = document.getElementById('report_ai_date_to').value;
        var p = {};
        if (f) { p.date_from = f; }
        if (t) { p.date_to = t; }
        return p;
    }

    var nbtn = document.getElementById('report_ai_narrate_btn');
    if (nbtn && !nbtn.dataset.bound) {
        nbtn.dataset.bound = 1;
        nbtn.addEventListener('click', function(){
            var st = document.getElementById('report_ai_narrate_status');
            var out = document.getElementById('report_ai_narrate_text');
            st.textContent = 'جارٍ توليد الملخص...';
            nbtn.disabled = true;
            fetch('{{ route('dashboard.report.ai_narrate') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify(fromToParams())
            })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    nbtn.disabled = false;
                    if (!res.status) { setStatus(st, 'text-danger', res.message_out || 'فشل توليد الملخص'); return; }
                    out.value = res.data.summary;
                    setStatus(st, 'text-success', 'تم ✓');
                })
                .catch(function(){ nbtn.disabled = false; setStatus(st, 'text-danger', 'خطأ في الاتصال'); });
        });
    }

    var abtn = document.getElementById('report_ai_ask_btn');
    if (abtn && !abtn.dataset.bound) {
        abtn.dataset.bound = 1;
        abtn.addEventListener('click', function(){
            var q = document.getElementById('report_ai_question').value.trim();
            var st = document.getElementById('report_ai_ask_status');
            var out = document.getElementById('report_ai_answer');
            if (!q) { setStatus(st, 'text-danger', 'اكتب سؤالاً أولاً'); return; }
            var params = fromToParams();
            params.question = q;
            st.textContent = 'جارٍ البحث عن إجابة...';
            abtn.disabled = true;
            fetch('{{ route('dashboard.report.ai_ask') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify(params)
            })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    abtn.disabled = false;
                    if (!res.status) { setStatus(st, 'text-danger', res.message_out || 'تعذّرت الإجابة'); return; }
                    out.textContent = res.data.answer;
                    setStatus(st, 'text-success', 'تم ✓');
                })
                .catch(function(){ abtn.disabled = false; setStatus(st, 'text-danger', 'خطأ في الاتصال'); });
        });
    }
})();
</script>

<script>
(function(){
    ['report_ai_narrate_status', 'report_ai_ask_status'].forEach(function(id){
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
})();
</script>

@endsection
