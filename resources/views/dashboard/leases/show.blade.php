@extends('layouts.app')
@section('module', 'عقود الإيجار')
@section('sub', 'النتائج')
@section('title', "$page_title")
@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div class="fs-5">الحالة: <span id="st" class="badge badge-light-primary">...</span></div>
            </div>
            <div class="progress h-20px">
                <div id="bar" class="progress-bar bg-primary fw-bold" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
            </div>
            <div id="meta" class="text-muted mt-2 fs-7"></div>
            <div id="cost" class="mt-2 fs-7 fw-bold text-gray-700"></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md"><div class="card card-flush h-100"><div class="card-body text-center py-4"><div class="fs-2hx fw-bold text-gray-900" id="s-count">0</div><div class="text-muted fs-8">عدد العقود</div></div></div></div>
        <div class="col-6 col-md"><div class="card card-flush h-100"><div class="card-body text-center py-4"><div class="fs-2hx fw-bold text-warning" id="s-review">0</div><div class="text-muted fs-8">تحتاج مراجعة</div></div></div></div>
        <div class="col-6 col-md"><div class="card card-flush h-100"><div class="card-body text-center py-4"><div class="fs-2hx fw-bold text-success" id="s-approved">0</div><div class="text-muted fs-8">مُعتمدة</div></div></div></div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped gy-5 gs-5 align-middle">
            <thead>
                <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-800" style="background-color:#ffb822 !important;">
                    <th>#</th>
                    <th class="min-w-120px">رقم العقد</th>
                    <th class="min-w-150px">المستأجر</th>
                    <th class="min-w-150px">المؤجر</th>
                    <th>الوحدة</th>
                    <th class="min-w-110px">البداية</th>
                    <th class="min-w-110px">النهاية</th>
                    <th>قيمة الإيجار</th>
                    <th>عدد الدفعات</th>
                    <th>حالة</th>
                    <th>المرفق</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody id="rows"></tbody>
        </table>
    </div>
    <div class="text-muted fs-7">تلميح: انقر على أي خلية لتعديل قيمتها، ثم انقر خارجها للحفظ. اضغط «موافقة» لإنشاء العقد وجدول الدفعات. الصفوف الصفراء تحتاج مراجعة.</div>

    <div id="lseLb" style="position:fixed;inset:0;z-index:1090;display:none;place-items:center;background:rgba(0,0,0,.85);padding:30px" onclick="this.style.display='none'">
        <img id="lseLbImg" src="" style="max-width:92vw;max-height:92vh;border-radius:8px;box-shadow:0 30px 80px -20px #000">
    </div>
@endsection
@section('scripts')
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });
        var statusUrl = "{{ route('dashboard.leases.status', $batch->id) }}";
        var correctBase = "{{ url('dashboard/leases') }}";
        var timer = null;

        function esc(s) { return (s == null ? '' : String(s)).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

        function attachment(v) {
            if (v.image_url) {
                return '<img src="' + v.image_url + '" loading="lazy" class="lse-thumb" data-full="' + v.image_url + '" title="اضغط للتكبير" style="height:46px;width:auto;border:1px solid #eee;border-radius:6px;cursor:zoom-in;transition:transform .15s" onmouseover="this.style.transform=\'scale(1.08)\'" onmouseout="this.style.transform=\'\'">';
            }
            return '<span class="text-muted">—</span>';
        }

        function render(d) {
            $('#st').text(d.status);
            $('#bar').css('width', d.percent + '%').text(d.percent + '%');
            $('#meta').text((d.processed_pages || 0) + ' / ' + (d.total_pages || 0) + ' صفحة' + (d.error_message ? (' — ' + d.error_message) : ''));
            $('#cost').text('التوكنز: ' + (d.input_tokens || 0) + ' إدخال / ' + (d.output_tokens || 0) + ' إخراج  —  التكلفة ≈ $' + Number(d.est_cost_usd || 0).toFixed(4) + '  (' + Number(d.est_cost_sar || 0).toFixed(3) + ' ريال)  —  ' + (d.model_used || ''));
            var rows = d.extractions || [];
            $('#s-count').text(rows.length);
            $('#s-review').text(rows.filter(function(x){return x.needs_review;}).length);
            $('#s-approved').text(rows.filter(function(x){return x.contract_id;}).length);
            var html = '';
            rows.forEach(function (v) {
                var warn = v.needs_review ? ' style="background:#fff4d6"' : '';
                function cell(f) { return '<td contenteditable="true" data-id="' + v.id + '" data-field="' + f + '" class="edit" title="' + esc(v.validation_notes) + '">' + esc(v[f]) + '</td>'; }
                var flag = v.status == 'failed' ? '✗' : (v.needs_review ? '⚠' : '✓');
                var action = v.contract_id
                    ? '<span class="badge badge-light-success" title="رقم العقد ' + esc(v.contract_id) + '">مُعتمد</span>'
                    : '<button class="btn btn-sm btn-success approveBtn" data-id="' + v.id + '">موافقة</button>';
                html += '<tr' + warn + '><td>' + esc(v.page_number) + '</td>'
                    + cell('contract_no') + cell('tenant_name') + cell('landlord_name') + cell('unit')
                    + cell('start_date') + cell('end_date') + cell('rent_value') + cell('num_payments')
                    + '<td>' + flag + '</td><td>' + attachment(v) + '</td><td>' + action + '</td></tr>';
            });
            $('#rows').html(html || '<tr><td colspan="12" class="text-center text-muted">لا توجد بيانات بعد…</td></tr>');
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
                if (r.status) { $c.closest('tr').css('background', ''); }
            });
        });

        $(document).on('click', '.approveBtn', function () {
            var $btn = $(this).prop('disabled', true).text('جارٍ الموافقة…');
            var id = $btn.data('id');
            $.post(correctBase + '/' + id + '/approve').done(function (r) {
                if (r.status) { poll(); } else { alert(r.message_out || 'تعذّرت الموافقة'); $btn.prop('disabled', false).text('موافقة'); }
            }).fail(function (xhr) {
                var m = (xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّرت الموافقة';
                alert(m);
                $btn.prop('disabled', false).text('موافقة');
            });
        });

        // Image lightbox (rows render dynamically → delegated handler)
        $(document).on('click', '.lse-thumb', function () {
            document.getElementById('lseLbImg').src = this.dataset.full;
            document.getElementById('lseLb').style.display = 'grid';
        });

        poll();
        timer = setInterval(poll, 3000);
    </script>
@endsection
