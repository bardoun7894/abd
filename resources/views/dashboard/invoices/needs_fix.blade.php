@extends('layouts.app')
@section('module', 'استخراج الفواتير')
@section('sub', 'مركز التصحيح')
@section('title', "$page_title")
@section('content')
    @php use App\Services\InvoicePurchaseMapper; @endphp

    <div class="inv-fix">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div class="fs-6 text-muted">
            @if (!empty($batchId))
                فواتير الدفعة #{{ $batchId }} التي تحتاج تصحيحاً —
                <a href="{{ route('dashboard.invoices.needs-fix') }}">عرض كل الفواتير التي تحتاج تصحيحاً</a>
            @else
                كل الفواتير غير المُرحّلة التي يمنعها خطأ أو نقص من الترحيل التلقائي. صحّحها ثم رحّلها.
            @endif
        </div>
        <a href="{{ route('dashboard.invoices.index') }}" class="btn btn-sm btn-light-primary">→ سجل الدفعات</a>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold mb-0">مركز تصحيح الفواتير</h3>
            </div>
            <div class="card-toolbar gap-2">
                @if (\Perm::get_function_access(55) && !empty($affectedBatchIds))
                    <button type="button" id="pushFixedBtn" class="btn btn-sm btn-success fw-bold"
                            title="ترحيل الفواتير التي صُحّحت في الدفعات المعروضة — يتخطّى المُرحّلة مسبقاً وما زال ناقصاً بلا تكرار">
                        <i class="bi bi-send-check me-1"></i>ترحيل المُصحّحة
                    </button>
                @endif
                <span class="text-muted fs-7 sn-num">{{ $invoices->total() }} فاتورة</span>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-row-dashed sn-thead align-middle gy-4" id="fixTable">
                    <thead>
                        <tr class="fw-bold fs-7 text-uppercase">
                            <th class="ps-4">الدفعة / الملف</th>
                            <th>رقم الفاتورة</th>
                            <th>التاريخ</th>
                            <th class="text-end">الإجمالي</th>
                            <th>السبب</th>
                            <th class="text-end pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $inv)
                            @php
                                $reason = InvoicePurchaseMapper::ineligibilityReason($inv->getAttributes());
                                $date = $inv->invoice_date?->format('Y-m-d');
                            @endphp
                            <tr class="sn-row" data-inv="{{ $inv->id }}">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge badge-light-primary">#{{ $inv->batch_id }}</span>
                                        <span class="fw-bold text-gray-800 text-truncate d-inline-block" style="max-width:240px"
                                              title="{{ $inv->batch?->original_filename }}">{{ $inv->batch?->original_filename ?: '—' }}</span>
                                        <span class="text-muted fs-8">ص {{ $inv->page_number }}</span>
                                    </div>
                                </td>
                                <td class="fw-bold text-gray-800 js-inv-number">{{ $inv->invoice_number ?: '—' }}</td>
                                <td class="text-muted sn-num js-inv-date">{{ $date ?: '—' }}</td>
                                <td class="text-end fw-bold sn-num js-inv-total">{{ $inv->total_incl_vat !== null ? number_format((float) $inv->total_incl_vat, 2) : '—' }}</td>
                                <td><span class="badge badge-light-warning">{{ $reason ?: 'بحاجة مراجعة' }}</span></td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-light-primary fw-bold js-edit-inv"
                                            data-id="{{ $inv->id }}"
                                            data-supplier_name="{{ $inv->supplier_name }}"
                                            data-supplier_tax_number="{{ $inv->supplier_tax_number }}"
                                            data-invoice_number="{{ $inv->invoice_number }}"
                                            data-invoice_date="{{ $date }}"
                                            data-amount_before_vat="{{ $inv->amount_before_vat }}"
                                            data-vat_amount="{{ $inv->vat_amount }}"
                                            data-total_incl_vat="{{ $inv->total_incl_vat }}"
                                            title="تعديل / إدخال يدوي">
                                        <i class="bi bi-pencil-square me-1"></i>تعديل
                                    </button>
                                    <a href="{{ route('dashboard.invoices.show', $inv->batch_id) }}" class="btn btn-sm btn-light fw-bold" title="فتح الدفعة">
                                        <i class="bi bi-box-arrow-up-left"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr id="fixEmptyRow">
                                <td colspan="6">
                                    <div class="d-flex flex-column align-items-center text-center py-12">
                                        <span class="mb-4" style="width:72px;height:72px;border-radius:var(--sn-r-lg);font-size:30px;display:inline-flex;align-items:center;justify-content:center;background:var(--sn-emerald-tint,#e6f4ee);color:var(--sn-emerald-deep,#116149)">
                                            <i class="bi bi-check2-all"></i>
                                        </span>
                                        <div class="fs-5 fw-bold text-gray-800 mb-1">لا شيء للتصحيح</div>
                                        <div class="text-muted mb-5">
                                            لا توجد فواتير محجوبة هنا — استخدم <strong>ترحيل الكل المؤهل</strong> من سجل الدفعات لترحيل الجاهزة، أو <strong>عرض</strong> الدفعة لمراجعتها.
                                        </div>
                                        <a href="{{ route('dashboard.invoices.index') }}" class="btn btn-primary fw-bold">
                                            <i class="bi bi-arrow-left me-1"></i> سجل الدفعات
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($invoices->hasPages())
                <div class="d-flex justify-content-center mt-6">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Shared manual-edit modal (posts changed fields to /{id}/correct → clears needs_review). --}}
    @include('dashboard.invoices._edit_modal')

    {{-- "ترحيل المُصحّحة" — shop XOR manager picker, mirroring index.blade's bulk-push modal.
         Posts the STABLE $affectedBatchIds set to bulkPush(); it skips already-posted and
         still-blocked invoices, so only the newly-corrected ones are pushed (no duplicates). --}}
    <div class="modal fade" id="pushFixedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header py-4">
                    <h3 class="modal-title fs-5">ترحيل الفواتير المُصحّحة</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted fs-7">سيتم ترحيل الفواتير المؤهلة من الدفعات المعروضة. اختر <strong>المحل</strong> أو <strong>قائد مجموعة</strong> — وليس كليهما.</p>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fs-7 fw-bold">المحل <span class="text-muted fw-normal">(مصاريف شراء محلات)</span></label>
                            <select id="fixShopId" class="form-select form-select-sm">
                                <option value="">— اختر محلاً —</option>
                                @foreach ($shops as $x)
                                    <option value="{{ $x->shop_id }}">{{ $x->shop_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 text-center text-muted fs-7">أو</div>
                        <div class="col-md-6">
                            <label class="form-label fs-7">قائد المجموعة</label>
                            <select id="fixManagerId" class="form-select form-select-sm">
                                <option value="">— اختر قائد مجموعة —</option>
                                @foreach ($managers as $x)
                                    <option value="{{ $x->manager_id }}">{{ $x->manager_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="pushFixedResult" class="mt-3 fs-7"></div>
                    <div class="d-flex align-items-center gap-3 mt-4">
                        <button type="button" id="pushFixedSubmitBtn" class="btn btn-sm btn-success">ترحيل المُصحّحة</button>
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">إلغاء</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>{{-- /.inv-fix --}}
@endsection
@section('scripts')
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });
        var correctBase = "{{ url('dashboard/invoices') }}";
        var bulkPushUrl = "{{ route('dashboard.invoices.bulk-push') }}";
        // Captured ONCE at page load: the batches that had a blocked invoice when the user
        // arrived. Stays stable as rows are fixed & removed from the DOM, so ترحيل still
        // targets a batch even after its LAST blocked invoice is corrected.
        var affectedBatchIds = {!! json_encode($affectedBatchIds) !!};

        var invEditFields = ['supplier_name', 'supplier_tax_number', 'invoice_number', 'invoice_date', 'amount_before_vat', 'vat_amount', 'total_incl_vat'];

        // Open the shared edit modal, populated from the row button's data-* attributes.
        $(document).on('click', '.js-edit-inv', function () {
            var $btn = $(this), id = String($btn.data('id'));
            var vals = {};
            invEditFields.forEach(function (f) {
                var v = $btn.attr('data-' + f);
                vals[f] = (v == null) ? '' : String(v);
            });
            var $form = $('#invEditForm');
            $form.find('[name="__id"]').val(id);
            invEditFields.forEach(function (f) { $form.find('[name="' + f + '"]').val(vals[f]); });
            $form.data('orig', vals);
            $('#invEditResult').text('').removeClass('text-success text-danger');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('invEditModal')).show();
        });

        // POST changed fields SEQUENTIALLY (isolated sqlite invoices conn recomputes the
        // batch total on each correct() — concurrent writes risk SQLITE_BUSY on prod).
        function invEditPostSeq(id, changed, onDone, onFail) {
            var i = 0;
            (function next() {
                if (i >= changed.length) { onDone(); return; }
                var c = changed[i++];
                $.post(correctBase + '/' + id + '/correct', { field: c.field, value: c.value }).done(next).fail(onFail);
            })();
        }

        $(document).on('click', '#invEditSave', function () {
            var $form = $('#invEditForm'), id = $form.find('[name="__id"]').val(), orig = $form.data('orig') || {};
            var $btn = $(this).prop('disabled', true);
            var changed = [];
            invEditFields.forEach(function (f) {
                var nv = ($form.find('[name="' + f + '"]').val() || '').trim();
                if (nv !== (orig[f] != null ? String(orig[f]) : '')) { changed.push({ field: f, value: nv }); }
            });
            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('invEditModal'));
            if (!changed.length) { modal.hide(); $btn.prop('disabled', false); return; }
            $('#invEditResult').text('جارٍ الحفظ…').removeClass('text-success text-danger');
            invEditPostSeq(id, changed,
                function () {
                    $('#invEditResult').text('تم الحفظ').addClass('text-success');
                    modal.hide();
                    $btn.prop('disabled', false);
                    // correct() cleared needs_review — the row is no longer blocked. Drop it
                    // from the DOM (do NOT re-query: affectedBatchIds must stay stable so the
                    // just-emptied batch is still posted by ترحيل المُصحّحة).
                    dropRow(id);
                },
                function (xhr) {
                    var m = (xhr && xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الحفظ';
                    $('#invEditResult').text(m).addClass('text-danger');
                    $btn.prop('disabled', false);
                }
            );
        });

        function dropRow(id) {
            var $row = $('tr[data-inv="' + id + '"]');
            $row.fadeOut(200, function () {
                $(this).remove();
                if (!$('#fixTable tbody tr[data-inv]').length) {
                    $('#fixTable tbody').html('<tr><td colspan="6"><div class="text-center text-muted py-10">تم تصحيح كل الفواتير المعروضة. اضغط <strong>ترحيل المُصحّحة</strong> لترحيلها.</div></td></tr>');
                }
            });
        }

        // select2 to match the rest of the app's pickers.
        if ($.fn.select2) { $('#fixShopId, #fixManagerId').select2({ dir: 'rtl', width: '100%', dropdownParent: $('#pushFixedModal') }); }
        $('#fixManagerId').on('change', function () { if ($(this).val()) $('#fixShopId').val('').trigger('change.select2'); });
        $('#fixShopId').on('change', function () { if ($(this).val()) $('#fixManagerId').val('').trigger('change.select2'); });

        $('#pushFixedBtn').on('click', function () {
            $('#pushFixedResult').text('');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('pushFixedModal')).show();
        });

        $('#pushFixedSubmitBtn').on('click', function () {
            var shopId = $('#fixShopId').val(), managerId = $('#fixManagerId').val();
            if (!affectedBatchIds.length) {
                $('#pushFixedResult').html('<span class="text-danger">لا توجد دفعات للترحيل.</span>');
                return;
            }
            if (!shopId && !managerId) {
                $('#pushFixedResult').html('<span class="text-danger">الرجاء اختيار قائد مجموعة أو محل.</span>');
                return;
            }
            var $btn = $(this).prop('disabled', true).text('جارٍ الترحيل…');
            $('#pushFixedResult').html('<span class="text-muted">جارٍ الترحيل…</span>');
            $.post(bulkPushUrl, { batch_ids: affectedBatchIds, shop_id: shopId, manager_id: managerId })
                .done(function (r) {
                    var cls = r.status ? 'text-success' : 'text-danger';
                    $('#pushFixedResult').html('<span class="' + cls + '">' + $('<div>').text(r.message_out).html() + '</span>');
                    if (r.status) { setTimeout(function () { location.reload(); }, 2500); }
                })
                .fail(function (xhr) {
                    var m = (xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الترحيل';
                    $('#pushFixedResult').html('<span class="text-danger">' + $('<div>').text(m).html() + '</span>');
                })
                .always(function () { $btn.prop('disabled', false).text('ترحيل المُصحّحة'); });
        });
    </script>
@endsection
