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

    {{-- Approving a lease writes lease_contracts/lease_payments, which the
         «ادارة دفعات الايجار» screen never reads. Naming the shop here mirrors the
         same schedule into shop_rentpay so the دفعات actually appear. Optional:
         leaving it empty keeps the previous behaviour. --}}
    <div class="modal fade" id="lseApproveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-4">
                    <h5 class="modal-title fw-bold">اعتماد العقد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="lseApproveId">
                    <input type="hidden" id="lseApproveForce" value="0">
                    <label class="form-label fw-semibold">المحل التابع له العقد</label>
                    <select id="lseApproveShop" class="form-select"></select>
                    <div class="text-muted fs-8 mt-2">
                        اختر المحل لإضافة دفعات الإيجار إليه مباشرة في «ادارة دفعات الايجار».
                        إن تركته فارغاً سيُنشأ العقد بدون إضافة دفعات للمحل.
                    </div>
                </div>
                <div class="modal-footer py-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-success" id="lseApproveGo">اعتماد</button>
                </div>
            </div>
        </div>
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
                var delBtn = ' <button class="btn btn-sm btn-icon btn-light-danger delBtn" data-id="' + v.id + '" data-approved="' + (v.contract_id ? 1 : 0) + '" title="حذف"><i class="fas fa-trash-alt"></i></button>';
                var action = v.contract_id
                    ? '<span class="badge badge-light-success" title="رقم العقد ' + esc(v.contract_id) + '">مُعتمد</span>' + delBtn
                    : '<button class="btn btn-sm btn-success approveBtn" data-id="' + v.id + '" data-needs-review="' + (v.needs_review ? 1 : 0) + '">موافقة</button>'
                      + ' <button class="btn btn-sm btn-light-warning rejectBtn" data-id="' + v.id + '">رفض</button>' + delBtn;
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

        /* Approving only ever wrote to lease_contracts/lease_payments, which the
           «ادارة دفعات الايجار» screen does not read. Ask which shop the lease
           belongs to so the same schedule is also written as real دفعات. The
           shop is optional — approving without one behaves exactly as before. */
        $(document).on('click', '.approveBtn', function () {
            var $btn = $(this);
            var needsReview = String($btn.data('needs-review')) === '1';
            if (needsReview && !confirm('هذا العقد محدد للمراجعة. هل تريد الموافقة عليه بالقوة؟')) {
                return;
            }
            $('#lseApproveId').val($btn.data('id'));
            $('#lseApproveForce').val(needsReview ? 1 : 0);
            $('#lseApproveShop').val(null).trigger('change');
            $('#lseApproveModal').modal('show');
        });

        $(document).on('click', '#lseApproveGo', function () {
            var $go = $(this).prop('disabled', true).text('جارٍ الموافقة…');
            var id = $('#lseApproveId').val();
            var postData = { shop_id: $('#lseApproveShop').val() || '' };
            if ($('#lseApproveForce').val() === '1') { postData.force = 1; }

            $.post(correctBase + '/' + id + '/approve', postData).done(function (r) {
                $('#lseApproveModal').modal('hide');
                if (r.status) { if (r.message_out) { alert(r.message_out); } poll(); }
                else { alert(r.message_out || 'تعذّرت الموافقة'); }
            }).fail(function (xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّرت الموافقة');
            }).always(function () {
                $go.prop('disabled', false).text('اعتماد');
            });
        });

        $('#lseApproveShop').select2({
            dir: 'rtl',
            width: '100%',
            dropdownParent: $('#lseApproveModal'),
            placeholder: 'اختر المحل (اختياري)',
            allowClear: true,
            ajax: {
                url: "{{ route('dashboard.general.sel_shop_list') }}",
                dataType: 'json',
                type: 'POST',
                delay: 250,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: function (params) { return { q: params.term, page: params.page || 1 }; },
                processResults: function (data, params) {
                    var page = params.page || 1;
                    return {
                        results: $.map(data, function (item) {
                            return { text: item.ItemName + ' - ' + item.item_code, id: item.id };
                        }),
                        pagination: { more: data.length ? (page * 50) <= data[0].total_count : false }
                    };
                },
                cache: true
            }
        });

        $(document).on('click', '.rejectBtn', function () {
            if (!confirm('سيتم رفض هذا العقد المستخرَج ولن يظهر للاعتماد. متابعة؟')) return;
            var $btn = $(this).prop('disabled', true).text('…');
            var id = $btn.data('id');
            $.post(correctBase + '/' + id + '/reject').done(function (r) {
                if (r.status) { poll(); } else { alert(r.message_out || 'تعذّر الرفض'); $btn.prop('disabled', false).text('رفض'); }
            }).fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الرفض'); $btn.prop('disabled', false).text('رفض'); });
        });

        $(document).on('click', '.delBtn', function () {
            var approved = String($(this).data('approved')) === '1';
            var msg = approved
                ? 'هذا العقد معتمد. سيتم حذفه وحذف جدول الدفعات المرتبط به. متابعة؟'
                : 'سيتم حذف هذا العقد المستخرَج. متابعة؟';
            if (!confirm(msg)) return;
            var id = $(this).data('id');
            $.ajax({ url: correctBase + '/' + id, method: 'DELETE' })
                .done(function (r) { if (r.status) { poll(); } else { alert(r.message_out || 'تعذّر الحذف'); } })
                .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الحذف'); });
        });

        /* Image lightbox (rows render dynamically → delegated handler) */
        $(document).on('click', '.lse-thumb', function () {
            document.getElementById('lseLbImg').src = this.dataset.full;
            document.getElementById('lseLb').style.display = 'grid';
        });

        poll();
        timer = setInterval(poll, 3000);
    </script>
@endsection
