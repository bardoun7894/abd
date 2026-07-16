@extends('layouts.app')
@section('module', 'استخراج الفواتير')
@section('sub', 'النتائج')
@section('title', "$page_title")
@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div class="fs-5">الحالة: <span id="st" class="badge badge-light-primary">...</span></div>
                <div class="fs-4">الإجمالي العام: <strong id="grand" class="text-success">0.00</strong></div>
            </div>
            <div class="progress h-20px">
                <div id="bar" class="progress-bar bg-primary fw-bold" role="progressbar" style="width:0%">0%</div>
            </div>
            <div id="meta" class="text-muted mt-2 fs-7"></div>
            <div id="cost" class="mt-2 fs-7 fw-bold text-gray-700"></div>
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
                </tr>
            </thead>
            <tbody id="rows"></tbody>
        </table>
    </div>
    <div class="text-muted fs-7">تلميح: انقر على أي خلية لتعديل قيمتها، ثم انقر خارجها للحفظ. اضغط صورة المرفق لتكبيرها. الصفوف الصفراء تحتاج مراجعة.</div>

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
                html += '<tr' + warn + '><td>' + esc(v.page_number) + '</td>'
                    + cell('supplier_name') + cell('supplier_tax_number') + cell('invoice_number') + cell('invoice_date')
                    + cell('amount_before_vat') + cell('vat_amount') + cell('total_incl_vat')
                    + '<td>' + qualityBadge(v.image_quality) + '</td>'
                    + '<td>' + flag + '</td><td>' + attachment(v) + '</td></tr>';
            });
            $('#rows').html(html || '<tr><td colspan="11" class="text-center text-muted">لا توجد بيانات بعد…</td></tr>');
        }

        function poll() {
            $.getJSON(statusUrl).done(function (d) {
                render(d);
                if (d.status == 'done' || d.status == 'failed') { clearInterval(timer); }
            });
        }

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
                    if (r.summary && r.summary.duplicates && r.summary.duplicates.length) {
                        extra = '<div class="text-muted mt-1">مكررة: ' + r.summary.duplicates.map(esc).join(', ') + '</div>';
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

        // Image lightbox (rows render dynamically → delegated handler)
        $(document).on('click', '.inv-thumb', function () {
            document.getElementById('invLbImg').src = this.dataset.full;
            document.getElementById('invLb').style.display = 'grid';
        });

        poll();
        timer = setInterval(poll, 3000);
    </script>
@endsection
