{{-- Spec 005 T-B1 — AI assistant widget for the violation add form. Vanilla JS
     fetch() to dashboard.violation.ai_classify / dashboard.violation.ai_draft,
     prefills the real form fields (violation_side_id, violation_cause) by id. --}}
<div class="col-12 mb-5" id="violation_ai_widget">
    <div class="card bg-light-primary border border-primary border-dashed">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="fw-bold text-dark">
                    <i class="fas fa-robot me-2"></i>
                    مساعد الذكاء الاصطناعي للمخالفات
                </div>
                <div class="d-flex" style="gap:.5rem">
                    <button type="button" id="violation_ai_classify_btn" class="btn btn-sm btn-primary">
                        <i class="fas fa-magic me-1"></i> تصنيف المخالفة
                    </button>
                    <button type="button" id="violation_ai_draft_btn" class="btn btn-sm btn-secondary">
                        <i class="fas fa-file-alt me-1"></i> صياغة إنذار
                    </button>
                </div>
            </div>
            <div id="violation_ai_result" class="mt-3 d-none">
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
