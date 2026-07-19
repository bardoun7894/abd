@extends('layouts.app')
@section('module', 'استخراج الفواتير')
@section('sub', 'النتائج')
@section('title', "$page_title")
@section('content')
    @if (!empty($aiSummary))
        <div class="card mb-4 border-primary">
            <div class="card-header">
                <h3 class="card-title">🧠 ملخص الذكاء الاصطناعي للدفعة</h3>
            </div>
            <div class="card-body">
                @if (!empty($aiSummary['narrative']))
                    <p class="fs-6 mb-4">{{ $aiSummary['narrative'] }}</p>
                @else
                    <p class="text-muted fs-7 mb-4">تعذّر توليد الملخص النصي حالياً — الأرقام أدناه محسوبة مباشرة من بيانات الدفعة.</p>
                @endif
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="text-muted fs-8">عدد الفواتير</div>
                        <div class="fs-4 fw-bold">{{ $aiSummary['invoice_count'] }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted fs-8">عدد الموردين</div>
                        <div class="fs-4 fw-bold">{{ $aiSummary['supplier_count'] }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted fs-8">الإجمالي شامل الضريبة</div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($aiSummary['total_incl_vat'], 2) }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted fs-8">قيمة الضريبة</div>
                        <div class="fs-4 fw-bold">{{ number_format($aiSummary['vat_amount'], 2) }}</div>
                    </div>
                </div>
                @if ($aiSummary['top_supplier'])
                    <div class="mt-3 fs-7 text-muted">
                        أكثر مورد تكراراً: <strong class="text-gray-800">{{ $aiSummary['top_supplier']['name'] }}</strong> ({{ $aiSummary['top_supplier']['count'] }} فاتورة)
                        @if ($aiSummary['needs_review_count'] > 0)
                            — <span class="text-warning fw-bold">{{ $aiSummary['needs_review_count'] }} تحتاج مراجعة</span>
                        @endif
                    </div>
                @endif
                @if (!empty($aiSummary['top_suppliers_by_amount']))
                    <div class="mt-2 fs-7">
                        <span class="text-muted">أعلى 3 موردين بالمبلغ: </span>
                        @foreach ($aiSummary['top_suppliers_by_amount'] as $s)
                            <span class="badge badge-light-primary me-1">{{ $s['name'] }} — {{ number_format($s['total'], 2) }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div class="fs-5">الحالة: <span id="st" class="badge badge-light-primary">...</span></div>
                <div class="fs-4">الإجمالي العام: <strong id="grand" class="text-success">0.00</strong></div>
            </div>
            <div class="progress h-20px">
                <div id="bar" class="progress-bar bg-primary fw-bold" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
            </div>
            <div id="meta" class="text-muted mt-2 fs-7"></div>
            <div id="cost" class="mt-2 fs-7 fw-bold text-gray-700"></div>
            <div class="mt-3 d-print-none">
                <button type="button" id="rescanBtn" class="btn btn-sm btn-light-danger"
                    title="يستخدم نموذجاً أقوى (وأبطأ) لإعادة قراءة الدفعة بالكامل — مفيد للصفحات غير الواضحة">
                    🔍 إعادة الفحص بدقة أعلى
                </button>
                <span id="rescanResult" class="fs-7 ms-2"></span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md"><div class="card card-flush h-100"><div class="card-body text-center py-4"><div class="fs-2hx fw-bold text-gray-900" id="s-count">0</div><div class="text-muted fs-8">عدد الفواتير</div></div></div></div>
        <div class="col-6 col-md"><div class="card card-flush h-100"><div class="card-body text-center py-4"><div class="fs-2hx fw-bold text-success" id="s-total">0.00</div><div class="text-muted fs-8">الإجمالي (ر.س)</div></div></div></div>
        <div class="col-6 col-md"><div class="card card-flush h-100"><div class="card-body text-center py-4"><div class="fs-2hx fw-bold text-success" id="s-clear">0</div><div class="text-muted fs-8">واضحة</div></div></div></div>
        <div class="col-6 col-md"><div class="card card-flush h-100"><div class="card-body text-center py-4"><div class="fs-2hx fw-bold text-warning" id="s-medium">0</div><div class="text-muted fs-8">متوسطة</div></div></div></div>
        <div class="col-6 col-md"><div class="card card-flush h-100"><div class="card-body text-center py-4"><div class="fs-2hx fw-bold text-danger" id="s-unclear">0</div><div class="text-muted fs-8">غير واضحة</div></div></div></div>
        <div class="col-6 col-md"><div class="card card-flush h-100"><div class="card-body text-center py-4"><div class="fs-2hx fw-bold text-warning" id="s-review">0</div><div class="text-muted fs-8">تحتاج مراجعة</div></div></div></div>
    </div>

    @if (!empty($canPush))
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">ترحيل إلى المشتريات</h3>
            </div>
            <div class="card-body">
                <p class="text-muted fs-7">تُرحَّل الفواتير المكتملة فقط (غير المعلَّمة للمراجعة وغير المُرحّلة مسبقاً). اختر <strong>المحل</strong> ليظهر الترحيل ضمن «مصاريف شراء محلات»، أو قائد مجموعة.</p>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-bold">المحل <span class="text-muted fw-normal">(مصاريف شراء محلات)</span></label>
                        <select id="shop_id" class="form-select form-select-sm">
                            <option value="">— اختر محلاً —</option>
                            @foreach ($shops as $x)
                                <option value="{{ $x->shop_id }}">{{ $x->shop_name }}</option>
                            @endforeach
                        </select>
                        @if (count($shops) === 0)
                            <div class="form-text text-warning">لا توجد محلات بعد — أضِف المحلات أولاً لتظهر هنا.</div>
                        @endif
                    </div>
                    <div class="col-md-1 text-center text-muted fs-7">أو</div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">قائد المجموعة</label>
                        <select id="manager_id" class="form-select form-select-sm">
                            <option value="">— اختر قائد مجموعة —</option>
                            @foreach ($managers as $x)
                                <option value="{{ $x->manager_id }}">{{ $x->manager_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button id="pushBtn" class="btn btn-success btn-sm w-100">ترحيل كل الفواتير المؤهلة</button>
                    </div>
                </div>
                <div id="pushResult" class="mt-3 fs-7"></div>
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-end mb-2 d-print-none">
        <button type="button" class="btn btn-sm btn-light-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> طباعة / حفظ PDF (مع رمز ZATCA)
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped gy-5 gs-5 align-middle">
            <thead>
                <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-800" style="background-color:#ffb822 !important;">
                    <th>#</th>
                    <th class="min-w-150px">المورد</th>
                    <th class="min-w-150px">الرقم الضريبي</th>
                    <th class="min-w-120px">رقم الفاتورة</th>
                    <th class="min-w-110px">التاريخ</th>
                    <th>قبل الضريبة</th>
                    <th>الضريبة</th>
                    <th>الإجمالي</th>
                    <th>جودة الصورة</th>
                    <th>حالة</th>
                    <th>المرفق</th>
                    <th class="d-print-table-cell">رمز ZATCA</th>
                    <th class="d-print-none text-center">إجراء</th>
                </tr>
            </thead>
            <tbody id="rows"></tbody>
        </table>
    </div>
    <div class="text-muted fs-7 d-print-none">تلميح: انقر على أي خلية لتعديل قيمتها، ثم انقر خارجها للحفظ. اضغط صورة المرفق لتكبيرها. الصفوف الصفراء تحتاج مراجعة.</div>

    <style>
        /* Print/PDF output (browser "Print to PDF") keeps the ZATCA Phase-1 QR
           column and hides interactive-only chrome — no server PDF template
           exists yet for this AI-extraction results screen (see InvoiceController). */
        @media print {
            .d-print-none { display: none !important; }
            table.table { font-size: 10px; }
        }
    </style>

    <style>
        /* B3 — batch progress clarity (additive; toggled purely via CSS classes that a
           SEPARATE observer script below adds/removes on the existing .progress/#bar
           elements — render()/poll() above are untouched). */
        .ai-progress-tall { height: 28px !important; border-radius: .65rem; }
        .ai-progress-tall #bar { font-size: .95rem; display: flex; align-items: center; justify-content: center; }
        .ai-progress-processing #bar {
            background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
            background-size: 1rem 1rem;
            animation: ai-progress-stripes 1s linear infinite;
        }
        .ai-progress-done #bar { background-color: #17c653 !important; }
        #aiProgressBanner.ai-progress-banner-done { color: #17c653; font-weight: 800; }
        @keyframes ai-progress-stripes { from { background-position: 1rem 0; } to { background-position: 0 0; } }
        @media (prefers-reduced-motion: reduce) {
            .ai-progress-processing #bar { animation: none; }
        }
    </style>

    <div id="invLb" style="position:fixed;inset:0;z-index:1090;display:none;place-items:center;background:rgba(0,0,0,.85);padding:30px" onclick="this.style.display='none'">
        <img id="invLbImg" src="" style="max-width:92vw;max-height:92vh;border-radius:8px;box-shadow:0 30px 80px -20px #000">
    </div>
@endsection
@section('scripts')
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });
        var statusUrl = "{{ route('dashboard.invoices.status', $batch->id) }}";
        var correctBase = "{{ url('dashboard/invoices') }}";
        var assetBase = "{{ asset('') }}";
        var timer = null;

        function esc(s) { return (s == null ? '' : String(s)).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

        function qualityBadge(q) {
            if (q == 'clear') return '<span class="badge badge-light-success">واضحة</span>';
            if (q == 'medium') return '<span class="badge badge-light-warning">متوسطة</span>';
            if (q == 'unclear') return '<span class="badge badge-light-danger">غير واضحة</span>';
            return '<span class="text-muted">—</span>';
        }

        function attachment(v) {
            if (v.image_url) {
                return '<img src="' + v.image_url + '" loading="lazy" class="inv-thumb" data-full="' + v.image_url + '" title="اضغط للتكبير" style="height:46px;width:auto;border:1px solid #eee;border-radius:6px;cursor:zoom-in;transition:transform .15s" onmouseover="this.style.transform=\'scale(1.08)\'" onmouseout="this.style.transform=\'\'">';
            }
            return '<span class="text-muted">—</span>';
        }

        function zatcaQr(v) {
            if (v.zatca_qr_image) {
                return '<img src="' + v.zatca_qr_image + '" title="' + esc(v.zatca_qr) + '" alt="ZATCA QR" style="height:60px;width:60px">';
            }
            if (v.zatca_qr) { // fallback: raw TLV base64 text (image render unavailable)
                return '<span class="text-muted fs-9" style="word-break:break-all;max-width:140px;display:inline-block" title="' + esc(v.zatca_qr) + '">' + esc(v.zatca_qr) + '</span>';
            }
            return '<span class="text-muted">—</span>';
        }

        function render(d) {
            $('#st').text(d.status);
            $('#bar').css('width', d.percent + '%').text(d.percent + '%');
            $('#meta').text((d.processed_pages || 0) + ' / ' + (d.total_pages || 0) + ' صفحة' + (d.error_message ? (' — ' + d.error_message) : ''));
            $('#grand').text(Number(d.grand_total || 0).toFixed(2));
            $('#cost').text('التوكنز: ' + (d.input_tokens || 0) + ' إدخال / ' + (d.output_tokens || 0) + ' إخراج  —  التكلفة ≈ $' + Number(d.est_cost_usd || 0).toFixed(4) + '  (' + Number(d.est_cost_sar || 0).toFixed(3) + ' ريال)  —  ' + (d.model_used || ''));
            var inv = d.invoices || [];
            $('#s-count').text(inv.length);
            $('#s-total').text(Number(d.grand_total || 0).toFixed(2));
            $('#s-clear').text(inv.filter(function(x){return x.image_quality=='clear';}).length);
            $('#s-medium').text(inv.filter(function(x){return x.image_quality=='medium';}).length);
            $('#s-unclear').text(inv.filter(function(x){return x.image_quality=='unclear';}).length);
            $('#s-review').text(inv.filter(function(x){return x.needs_review;}).length);
            var html = '';
            (d.invoices || []).forEach(function (v) {
                var warn = v.needs_review ? ' style="background:#fff4d6"' : '';
                function cell(f) { return '<td contenteditable="true" data-id="' + v.id + '" data-field="' + f + '" class="edit" title="' + esc(v.validation_notes) + '">' + esc(v[f]) + '</td>'; }
                var flag = v.status == 'failed' ? '✗' : (v.needs_review ? '⚠' : '✓');
                if (v.purchase_id) { flag += ' <span class="badge badge-light-success" title="رقم المشترى ' + esc(v.purchase_id) + '">مُرحّلة</span>'; }
                else if (v.duplicate_in_purchase) { flag += ' <span class="badge badge-light-danger" title="رقم الفاتورة موجود مسبقاً في المشتريات — سيتم تخطيها">مكرّرة</span>'; }
                html += '<tr' + warn + '><td>' + esc(v.page_number) + '</td>'
                    + cell('supplier_name') + cell('supplier_tax_number') + cell('invoice_number') + cell('invoice_date')
                    + cell('amount_before_vat') + cell('vat_amount') + cell('total_incl_vat')
                    + '<td>' + qualityBadge(v.image_quality) + '</td>'
                    + '<td>' + flag + '</td><td>' + attachment(v) + '</td>'
                    + '<td>' + zatcaQr(v) + '</td>'
                    + '<td class="d-print-none text-center"><button type="button" class="btn btn-sm btn-icon btn-light-danger js-del-inv" data-id="' + v.id + '" data-posted="' + (v.purchase_id ? 1 : 0) + '" title="حذف الفاتورة"><i class="bi bi-trash"></i></button></td>'
                    + '</tr>';
            });
            $('#rows').html(html || '<tr><td colspan="13" class="text-center text-muted">لا توجد بيانات بعد…</td></tr>');
        }

        function poll() {
            $.getJSON(statusUrl).done(function (d) {
                render(d);
                if (d.status == 'done' || d.status == 'failed') { clearInterval(timer); }
            });
        }

        $(document).on('click', '.js-del-inv', function () {
            var id = $(this).data('id');
            var posted = String($(this).data('posted')) === '1';
            var msg = posted
                ? 'هذه الفاتورة مُرحّلة إلى المشتريات. سيتم حذفها وعكس ترحيلها (حذف قيد المشتريات).\nهل أنت متأكد؟'
                : 'سيتم حذف هذه الفاتورة.\nهل أنت متأكد؟';
            if (!confirm(msg)) return;
            $.ajax({
                url: correctBase + '/{{ $batch->id }}/invoice/' + id,
                method: 'DELETE',
                success: function (r) {
                    if (r && r.status) { poll(); }
                    else { alert((r && r.message_out) || 'تعذّر الحذف'); }
                },
                error: function (xhr) {
                    alert((xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الحذف');
                }
            });
        });

        $(document).on('blur', '.edit', function () {
            var $c = $(this), id = $c.data('id'), field = $c.data('field'), value = $c.text().trim();
            $.post(correctBase + '/' + id + '/correct', { field: field, value: value }).done(function (r) {
                if (r.status) { $('#grand').text(Number(r.grand_total || 0).toFixed(2)); $c.closest('tr').css('background', ''); }
            });
        });

        // select2 to match the purchase form's dropdowns.
        if ($.fn.select2) { $('#manager_id, #shop_id').select2({ dir: 'rtl', width: '100%' }); }
        // Shop XOR manager — selecting one clears the other (without re-firing the handler).
        $('#manager_id').on('change', function () { if ($(this).val()) $('#shop_id').val('').trigger('change.select2'); });
        $('#shop_id').on('change', function () { if ($(this).val()) $('#manager_id').val('').trigger('change.select2'); });

        $('#pushBtn').on('click', function () {
            var managerId = $('#manager_id').val(), shopId = $('#shop_id').val();
            if (!managerId && !shopId) { $('#pushResult').html('<span class="text-danger">الرجاء اختيار قائد مجموعة أو محل.</span>'); return; }
            var $btn = $(this).prop('disabled', true).text('جارٍ الترحيل…');
            $('#pushResult').html('<span class="text-muted">جارٍ الترحيل…</span>');
            $.post("{{ route('dashboard.invoices.push', $batch->id) }}", { manager_id: managerId, shop_id: shopId })
                .done(function (r) {
                    var cls = r.status ? 'text-success' : 'text-danger';
                    var extra = '';
                    var s = r.summary || {};
                    if (s.duplicates && s.duplicates.length) {
                        extra += '<div class="alert alert-warning py-2 px-3 mt-2 mb-0 fs-8">'
                            + '<i class="bi bi-exclamation-triangle me-1"></i>'
                            + '<b>فواتير مكرّرة لم تُرحّل (' + s.duplicates.length + '):</b> '
                            + s.duplicates.map(esc).join('، ')
                            + '<div class="text-muted mt-1">هذه الأرقام موجودة مسبقاً في المشتريات — مُنع تكرارها.</div></div>';
                    }
                    if (s.fuzzy_duplicates && s.fuzzy_duplicates.length) {
                        var fz = s.fuzzy_duplicates.map(function (f) { return esc(f.invoice_number) + ' (تشابه ' + Math.round((f.score || 0) * 100) + '%)'; });
                        extra += '<div class="alert alert-warning py-2 px-3 mt-2 mb-0 fs-8">'
                            + '<i class="bi bi-search me-1"></i>'
                            + '<b>مشتبه بتكرارها — تحتاج مراجعة (' + s.fuzzy_duplicates.length + '):</b> '
                            + fz.join('، ') + '</div>';
                    }
                    $('#pushResult').html('<span class="' + cls + '">' + esc(r.message_out) + '</span>' + extra);
                    poll();
                })
                .fail(function (xhr) {
                    var m = (xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الترحيل';
                    $('#pushResult').html('<span class="text-danger">' + esc(m) + '</span>');
                })
                .always(function () { $btn.prop('disabled', false).text('ترحيل كل الفواتير المؤهلة'); });
        });

        // Feature B — smart re-scan (model escalation): re-run the whole batch with a
        // stronger Gemini model, then resume polling so progress/results refresh live.
        $('#rescanBtn').on('click', function () {
            var $btn = $(this).prop('disabled', true);
            $('#rescanResult').removeClass('text-danger text-success').html('<span class="text-muted">جارٍ الجدولة…</span>');
            $.post("{{ route('dashboard.invoices.rescan', $batch->id) }}", {})
                .done(function (r) {
                    $('#rescanResult').html('<span class="text-success">' + esc(r.message_out || 'تمت الجدولة') + '</span>');
                    if (timer) { clearInterval(timer); }
                    poll();
                    timer = setInterval(poll, 3000);
                })
                .fail(function (xhr) {
                    var m = (xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر جدولة إعادة الفحص';
                    $('#rescanResult').html('<span class="text-danger">' + esc(m) + '</span>');
                })
                .always(function () { $btn.prop('disabled', false); });
        });

        // Image lightbox (rows render dynamically → delegated handler)
        $(document).on('click', '.inv-thumb', function () {
            document.getElementById('invLbImg').src = this.dataset.full;
            document.getElementById('invLb').style.display = 'grid';
        });

        poll();
        timer = setInterval(poll, 3000);
    </script>

    {{-- B3: batch progress clarity — a NEW, SEPARATE script that only *observes* the
         existing #st/#bar/#meta elements (which render()/poll() above already update)
         and toggles additive CSS classes + a prominent "processing X/N pages" banner.
         Does not modify render()/poll() or the polling interval. --}}
    <script>
        (function () {
            var bar = document.getElementById('bar');
            var st = document.getElementById('st');
            var meta = document.getElementById('meta');
            var wrap = bar ? bar.closest('.progress') : null;
            if (!bar || !wrap || bar.dataset.b3Watch) { return; }
            bar.dataset.b3Watch = '1';
            wrap.classList.add('ai-progress-tall');

            var banner = document.createElement('div');
            banner.id = 'aiProgressBanner';
            banner.className = 'fs-6 fw-bold mt-2 d-print-none';
            if (meta && meta.parentNode) { meta.parentNode.insertBefore(banner, meta); }

            function sync() {
                var pct = parseInt(bar.style.width, 10) || 0;
                bar.setAttribute('aria-valuenow', String(pct));
                var statusText = ((st && st.textContent) || '').trim();
                var isDone = statusText === 'done';
                var isProcessing = statusText !== '' && statusText !== '...' && !isDone && statusText !== 'failed';
                wrap.classList.toggle('ai-progress-processing', isProcessing);
                wrap.classList.toggle('ai-progress-done', isDone);
                if (isDone) {
                    banner.classList.add('ai-progress-banner-done');
                    banner.textContent = 'اكتملت المعالجة ✓';
                } else if (isProcessing) {
                    banner.classList.remove('ai-progress-banner-done');
                    var metaText = (meta && meta.textContent) || '';
                    var m = metaText.match(/(\d+)\s*\/\s*(\d+)/);
                    banner.textContent = m ? ('جارٍ المعالجة: ' + m[1] + ' / ' + m[2] + ' صفحة') : 'جارٍ المعالجة…';
                } else {
                    banner.classList.remove('ai-progress-banner-done');
                    banner.textContent = '';
                }
            }

            var moBar = new MutationObserver(sync);
            moBar.observe(bar, { attributes: true, attributeFilter: ['style'] });
            if (st) {
                var moSt = new MutationObserver(sync);
                moSt.observe(st, { childList: true, characterData: true, subtree: true });
            }
            if (meta) {
                var moMeta = new MutationObserver(sync);
                moMeta.observe(meta, { childList: true, characterData: true, subtree: true });
            }
            sync();
        })();
    </script>
@endsection
