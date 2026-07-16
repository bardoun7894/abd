@extends('layouts.app')
@section('module', 'نظام الحوسبة')
@section('sub', 'التقارير')
@section('title', "$page_title")
@section('content')

<div class="d-flex flex-column">

    <div class="col-12 mb-4">
        <div class="card bg-light-primary border border-primary border-dashed">
            <div class="card-body py-4">
                <label class="fw-bold text-primary mb-2"><i class="fa fa-robot me-1"></i> ملخص ذكي للفترة</label>
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
                        <button type="button" id="report_ai_narrate_btn" class="btn btn-sm btn-primary text-nowrap">توليد الملخص</button>
                    </div>
                </div>
                <div id="report_ai_narrate_status" class="fs-8 text-muted mb-2"></div>
                <textarea id="report_ai_narrate_text" rows="3" class="form-control fw-bold" readonly placeholder="سيظهر هنا ملخص سردي للأرقام المالية للفترة المحددة"></textarea>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="card bg-light-info border border-info border-dashed">
            <div class="card-body py-4">
                <label class="fw-bold text-info mb-2"><i class="fa fa-comments me-1"></i> اسأل بياناتك</label>
                <div class="d-flex gap-2 align-items-center mb-2">
                    <input type="text" id="report_ai_question" class="form-control form-control-sm" placeholder="مثال: كم بلغ صافي الفرق هذه الفترة؟">
                    <button type="button" id="report_ai_ask_btn" class="btn btn-sm btn-info text-nowrap">اسأل</button>
                </div>
                <div id="report_ai_ask_status" class="fs-8 text-muted mb-2"></div>
                <div id="report_ai_answer" class="fw-bold"></div>
            </div>
        </div>
    </div>

</div>

<script>
(function(){
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
                    if (!res.status) { st.innerHTML = '<span class="text-danger">' + (res.message_out || 'فشل توليد الملخص') + '</span>'; return; }
                    out.value = res.data.summary;
                    st.innerHTML = '<span class="text-success">تم ✓</span>';
                })
                .catch(function(){ nbtn.disabled = false; st.innerHTML = '<span class="text-danger">خطأ في الاتصال</span>'; });
        });
    }

    var abtn = document.getElementById('report_ai_ask_btn');
    if (abtn && !abtn.dataset.bound) {
        abtn.dataset.bound = 1;
        abtn.addEventListener('click', function(){
            var q = document.getElementById('report_ai_question').value.trim();
            var st = document.getElementById('report_ai_ask_status');
            var out = document.getElementById('report_ai_answer');
            if (!q) { st.innerHTML = '<span class="text-danger">اكتب سؤالاً أولاً</span>'; return; }
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
                    if (!res.status) { st.innerHTML = '<span class="text-danger">' + (res.message_out || 'تعذّرت الإجابة') + '</span>'; return; }
                    out.textContent = res.data.answer;
                    st.innerHTML = '<span class="text-success">تم ✓</span>';
                })
                .catch(function(){ abtn.disabled = false; st.innerHTML = '<span class="text-danger">خطأ في الاتصال</span>'; });
        });
    }
})();
</script>

@endsection
