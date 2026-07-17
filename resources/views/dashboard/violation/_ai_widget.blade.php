{{-- Spec 005 T-B1 — AI assistant widget for the violation add form. Vanilla JS
     fetch() to dashboard.violation.ai_classify / dashboard.violation.ai_draft,
     prefills the real form fields (violation_side_id, violation_cause) by id. --}}
@once
<style>
.ai-card{position:relative;border:1px solid rgba(14,107,79,.18);border-radius:.95rem;background:linear-gradient(180deg,rgba(14,107,79,.06) 0%,rgba(255,255,255,0) 65%);overflow:hidden;transition:box-shadow .2s ease;}
.ai-card::before{content:"";position:absolute;inset-inline-start:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,#0E6B4F,#0A4F3A);}
.ai-card:hover{box-shadow:0 .5rem 1.5rem rgba(14,107,79,.12);}
.ai-icon-badge{display:inline-flex;align-items:center;justify-content:center;width:2.35rem;height:2.35rem;border-radius:.65rem;background:linear-gradient(135deg,#0E6B4F,#0A4F3A);color:#fff;font-size:1rem;flex:0 0 auto;box-shadow:0 .35rem .85rem rgba(14,107,79,.35);}
.ai-card-title{font-weight:700;margin:0;}
.ai-pill{display:inline-flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:700;line-height:1;padding:.35rem .6rem;border-radius:50rem;color:#fff;background:linear-gradient(135deg,#0E6B4F,#0A4F3A);letter-spacing:.02em;white-space:nowrap;}
.ai-btn-group .btn{position:relative;}
.ai-btn-group .btn.is-loading{pointer-events:none;opacity:.75;}
.ai-btn-group .btn.is-loading i{visibility:hidden;}
.ai-btn-group .btn.is-loading::after{content:"";position:absolute;inset-inline-start:50%;top:50%;width:.9rem;height:.9rem;margin-inline-start:-.45rem;margin-top:-.45rem;border-radius:50%;border:2px solid currentColor;border-inline-end-color:transparent;animation:ai-spin .7s linear infinite;}
@keyframes ai-spin{to{transform:rotate(360deg);}}
.ai-result-box{border:1px dashed rgba(14,107,79,.35);border-radius:.75rem;padding:1rem 1.1rem;background:rgba(14,107,79,.04);}
.ai-result-box .alert{border-inline-start:4px solid #0A4F3A;border-radius:.6rem;}
@media (prefers-reduced-motion: reduce){.ai-btn-group .btn.is-loading::after{animation:none;}}
</style>
@endonce
<div class="col-12 mb-5" id="violation_ai_widget">
    <div class="card ai-card">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="ai-icon-badge"><i class="fas fa-robot"></i></span>
                    <div>
                        <div class="ai-card-title fw-bold text-dark">مساعد الذكاء الاصطناعي للمخالفات</div>
                        <span class="ai-pill mt-1"><i class="fas fa-magic"></i> ذكاء اصطناعي</span>
                    </div>
                </div>
                <div class="d-flex ai-btn-group" style="gap:.5rem">
                    <button type="button" id="violation_ai_classify_btn" class="btn btn-sm btn-primary">
                        <i class="fas fa-magic me-1"></i> تصنيف المخالفة
                    </button>
                    <button type="button" id="violation_ai_draft_btn" class="btn btn-sm btn-secondary">
                        <i class="fas fa-file-alt me-1"></i> صياغة إنذار
                    </button>
                </div>
            </div>
            <div id="violation_ai_result" class="mt-3 d-none ai-result-box">
                <div class="alert alert-light-info mb-2" id="violation_ai_classify_result" style="display:none"></div>
                <div class="mb-2" id="violation_ai_draft_wrap" style="display:none">
                    <label class="form-label fw-bold">مسودة الإنذار المقترحة</label>
                    <textarea id="violation_ai_draft_text" class="form-control" rows="6" dir="rtl"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var root = document.getElementById('violation_ai_widget');
        if (!root || root.dataset.bound === '1') {
            return;
        }
        root.dataset.bound = '1';

        function csrfToken() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        function fieldVal(id) {
            var el = document.getElementById(id);
            return el ? (el.value || '') : '';
        }

        function selectedOptionText(id) {
            var el = document.getElementById(id);
            if (!el || el.selectedIndex < 0) {
                return '';
            }
            var opt = el.options[el.selectedIndex];
            return opt ? opt.text : '';
        }

        function showResultBox() {
            var box = document.getElementById('violation_ai_result');
            if (box) {
                box.classList.remove('d-none');
            }
        }

        var classifyBtn = document.getElementById('violation_ai_classify_btn');
        if (classifyBtn) {
            classifyBtn.addEventListener('click', function () {
                var note = fieldVal('violation_cause');
                if (!note.trim()) {
                    alert('الرجاء إدخال سبب المخالفة أولاً');
                    return;
                }

                fetch("{{ route('dashboard.violation.ai_classify') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ note: note }),
                })
                    .then(function (r) { return r.json(); })
                    .then(function (resp) {
                        if (!resp.status) {
                            alert(resp.message_out || 'تعذّر التصنيف');
                            return;
                        }
                        var d = resp.data || {};
                        if (d.side_id) {
                            var sideSelect = document.getElementById('violation_side_id');
                            if (sideSelect) {
                                sideSelect.value = d.side_id;
                                if (window.jQuery) {
                                    window.jQuery(sideSelect).trigger('change');
                                }
                            }
                        }
                        var box = document.getElementById('violation_ai_classify_result');
                        if (box) {
                            box.style.display = 'block';
                            box.innerHTML = '<strong>الجهة المقترحة:</strong> ' + (d.side || '-') +
                                '<br><strong>درجة الخطورة:</strong> ' + (d.severity || '-') +
                                '<br><strong>الإجراء المقترح:</strong> ' + (d.suggested_action || '-');
                        }
                        showResultBox();
                    })
                    .catch(function () {
                        alert('حدث خطأ أثناء الاتصال بالذكاء الاصطناعي');
                    });
            });
        }

        var draftBtn = document.getElementById('violation_ai_draft_btn');
        if (draftBtn) {
            draftBtn.addEventListener('click', function () {
                var payload = {
                    name: selectedOptionText('shop_id'),
                    violation_type: selectedOptionText('violation_side_id'),
                    date: fieldVal('violation_dt'),
                    note: fieldVal('violation_cause'),
                };

                fetch("{{ route('dashboard.violation.ai_draft') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                })
                    .then(function (r) { return r.json(); })
                    .then(function (resp) {
                        if (!resp.status) {
                            alert(resp.message_out || 'تعذّرت الصياغة');
                            return;
                        }
                        var wrap = document.getElementById('violation_ai_draft_wrap');
                        var textarea = document.getElementById('violation_ai_draft_text');
                        if (wrap && textarea) {
                            textarea.value = (resp.data && resp.data.draft) || '';
                            wrap.style.display = 'block';
                        }
                        showResultBox();
                    })
                    .catch(function () {
                        alert('حدث خطأ أثناء الاتصال بالذكاء الاصطناعي');
                    });
            });
        }
    })();
</script>

<script>
(function(){
    // Purely additive visual affordance: shows a spinner on the AI button that was
    // clicked, and clears it once the original (unmodified) script above reveals the
    // corresponding result element. Never touches the original fetch/click logic.
    function watchLoading(btnId, resultId) {
        var btn = document.getElementById(btnId);
        var result = document.getElementById(resultId);
        if (!btn || !result || btn.dataset.aiLoadingWatch) { return; }
        btn.dataset.aiLoadingWatch = '1';
        var timer = null;
        btn.addEventListener('click', function(){
            btn.classList.add('is-loading');
            clearTimeout(timer);
            timer = setTimeout(function(){ btn.classList.remove('is-loading'); }, 15000);
        });
        var mo = new MutationObserver(function(){
            if (result.style.display === 'block') {
                btn.classList.remove('is-loading');
                clearTimeout(timer);
            }
        });
        mo.observe(result, {attributes: true, attributeFilter: ['style']});
    }
    watchLoading('violation_ai_classify_btn', 'violation_ai_classify_result');
    watchLoading('violation_ai_draft_btn', 'violation_ai_draft_wrap');
})();
</script>
