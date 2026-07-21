@extends('layouts.app')
@section('module', 'استخراج الفواتير')
@section('sub', 'المراجعة والاعتماد')
@section('title', "$page_title")
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="{{ route('dashboard.invoices.show', $batch->id) }}" class="btn btn-sm btn-light-primary">→ عرض نتائج الدفعة</a>
            <button type="button" id="reprocessMissingBtn" class="btn btn-sm btn-light-warning" data-batch="{{ $batch->id }}"
                title="يعيد قراءة الصفحات الناقصة/الفاشلة فقط — لا يمسّ المُرحّلة أو المُصحّحة يدوياً">
                ♻️ إعادة قراءة الناقصة/الفاشلة
            </button>
            <span id="reprocessMissingResult" class="fs-7 ms-2"></span>
        </div>
        <div class="fs-6 text-muted">دفعة #{{ $batch->id }} — {{ $batch->original_filename }} — {{ $invoices->count() }} فاتورة</div>
    </div>

    @if ($invoices->isEmpty())
        <div class="card"><div class="card-body text-center text-muted py-10">لا توجد فواتير في هذه الدفعة بعد.</div></div>
    @endif

    @php
        $fieldLabels = [
            'supplier_name' => 'اسم المورد',
            'supplier_tax_number' => 'الرقم الضريبي للمورد',
            'invoice_number' => 'رقم الفاتورة',
            'invoice_date' => 'تاريخ الفاتورة',
            'invoice_type' => 'نوع الفاتورة',
            'currency' => 'العملة',
            'amount_before_vat' => 'المبلغ قبل الضريبة',
            'discount_total' => 'إجمالي الخصم',
            'vat_rate' => 'نسبة الضريبة',
            'vat_amount' => 'قيمة الضريبة',
            'total_incl_vat' => 'الإجمالي شامل الضريبة',
            'commercial_registration' => 'السجل التجاري',
            'payment_method' => 'طريقة الدفع',
            'due_date' => 'تاريخ الاستحقاق',
        ];
        $editableFields = ['supplier_name', 'supplier_tax_number', 'invoice_number', 'invoice_date', 'amount_before_vat', 'vat_amount', 'total_incl_vat'];
    @endphp

    @foreach ($invoices as $invoice)
        @php
            $fc = $invoice->field_confidence ?? [];
            $needsReview = (bool) $invoice->needs_review;
            $statusBadge = match ($invoice->status) {
                'rejected' => '<span class="badge badge-light-danger">مرفوضة</span>',
                'draft' => '<span class="badge badge-light-secondary">مسودة</span>',
                'failed' => '<span class="badge badge-light-danger">فشل الاستخراج</span>',
                'done' => $needsReview ? '<span class="badge badge-light-warning">بانتظار المراجعة</span>' : '<span class="badge badge-light-success">معتمدة</span>',
                default => '<span class="badge badge-light-primary">'.$invoice->status.'</span>',
            };
        @endphp
        <div class="card mb-5{{ $needsReview ? ' border-warning' : '' }}" id="inv-{{ $invoice->id }}">
            <div class="card-header flex-wrap">
                <h3 class="card-title">صفحة {{ $invoice->page_number }} — {{ $invoice->supplier_name ?: 'بدون اسم مورد' }}</h3>
                <div class="card-toolbar">{!! $statusBadge !!}</div>
            </div>
            <div class="card-body">
                <div class="row g-5">
                    <div class="col-lg-4">
                        @if ($invoice->image_url)
                            <img src="{{ $invoice->image_url }}" loading="lazy" class="inv-thumb w-100 rounded border" data-full="{{ $invoice->image_url }}" style="cursor:zoom-in" title="اضغط للتكبير">
                        @else
                            <div class="text-muted text-center border rounded py-10">لا توجد صورة للصفحة</div>
                        @endif
                    </div>
                    <div class="col-lg-8">
                        <div class="table-responsive">
                        <table class="table table-row-dashed table-sm align-middle">
                            <tbody>
                                @foreach ($fieldLabels as $field => $label)
                                    @php
                                        $conf = $fc[$field] ?? null;
                                        $confClass = $conf === null ? 'secondary' : ($conf < 0.6 ? 'danger' : ($conf < 0.85 ? 'warning' : 'success'));
                                        $confText = $conf === null ? '—' : number_format($conf * 100, 0).'%';
                                        $isEditable = in_array($field, $editableFields, true);
                                    @endphp
                                    <tr class="{{ $conf !== null && $conf < 0.6 ? 'bg-light-danger' : '' }}">
                                        <td class="text-muted fs-7 w-200px">{{ $label }}</td>
                                        <td>
                                            @if ($isEditable)
                                                <span class="edit" contenteditable="true" data-id="{{ $invoice->id }}" data-field="{{ $field }}">{{ $invoice->{$field} }}</span>
                                            @else
                                                {{ $invoice->{$field} }}
                                            @endif
                                        </td>
                                        <td class="text-end w-100px"><span class="badge badge-light-{{ $confClass }}" title="مستوى الثقة">{{ $confText }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>

                        @if ($invoice->validation_notes)
                            @if (str_contains($invoice->validation_notes, 'شذوذ'))
                                <span class="badge badge-light-danger mb-2">⚠ شذوذ</span>
                            @endif
                            <div class="alert alert-warning py-2 px-3 fs-7">{{ $invoice->validation_notes }}</div>
                        @endif
                        @if ($invoice->error_message)
                            <div class="alert alert-danger py-2 px-3 fs-7">{{ $invoice->error_message }}</div>
                        @endif

                        @if ($invoice->items->isNotEmpty())
                            <div class="fw-bold fs-7 text-gray-700 mb-2 mt-4">بنود الفاتورة</div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm gy-2">
                                    <thead>
                                        <tr class="fs-8 text-muted">
                                            <th>#</th><th>الصنف</th><th>الكمية</th><th>الوحدة</th><th>سعر الوحدة</th><th>الإجمالي</th><th>الضريبة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoice->items as $item)
                                            <tr>
                                                <td>{{ $item->line_no }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ $item->unit }}</td>
                                                <td>{{ $item->unit_price }}</td>
                                                <td>{{ $item->line_total }}</td>
                                                <td>{{ $item->vat_amount }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex gap-2 flex-wrap">
                <button class="btn btn-sm btn-light-primary js-edit-inv" data-id="{{ $invoice->id }}" title="تعديل / إدخال يدوي"><i class="bi bi-pencil-square me-1"></i>تعديل / إدخال يدوي</button>
                <button class="btn btn-sm btn-success act-approve" data-id="{{ $invoice->id }}">✓ اعتماد</button>
                <button class="btn btn-sm btn-danger act-reject" data-id="{{ $invoice->id }}">✗ رفض</button>
                <button class="btn btn-sm btn-light-warning act-reprocess" data-id="{{ $invoice->id }}">↻ إعادة معالجة الدفعة</button>
                <button class="btn btn-sm btn-light act-draft" data-id="{{ $invoice->id }}">حفظ كمسودة</button>
                <span class="act-result fs-7 ms-2 align-self-center"></span>
            </div>
        </div>
    @endforeach

    <div id="invLb" style="position:fixed;inset:0;z-index:1090;display:none;place-items:center;background:rgba(0,0,0,.85);padding:30px" onclick="this.style.display='none'">
        <img id="invLbImg" src="" style="max-width:92vw;max-height:92vh;border-radius:8px;box-shadow:0 30px 80px -20px #000">
    </div>

    @include('dashboard.invoices._edit_modal')
@endsection
@section('scripts')
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });
        var correctBase = "{{ url('dashboard/invoices') }}";

        $(document).on('blur', '.edit', function () {
            var $c = $(this), id = $c.data('id'), field = $c.data('field'), value = $c.text().trim();
            $.post(correctBase + '/' + id + '/correct', { field: field, value: value });
        });

        function runAction(action, id, $btn) {
            $btn.prop('disabled', true);
            var $result = $btn.closest('.card-footer').find('.act-result');
            $result.text('جارٍ التنفيذ…').removeClass('text-success text-danger');
            $.post(correctBase + '/' + id + '/' + action, {})
                .done(function (r) {
                    $result.text(r.message_out || 'تم').addClass('text-success');
                    if (action === 'approve' || action === 'reject') {
                        $('#inv-' + id).fadeTo(300, 0.5);
                    }
                })
                .fail(function (xhr) {
                    var m = (xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر التنفيذ';
                    $result.text(m).addClass('text-danger');
                })
                .always(function () { $btn.prop('disabled', false); });
        }

        $(document).on('click', '.act-approve', function () { runAction('approve', $(this).data('id'), $(this)); });
        $(document).on('click', '.act-reject', function () { runAction('reject', $(this).data('id'), $(this)); });
        $(document).on('click', '.act-reprocess', function () { runAction('reprocess', $(this).data('id'), $(this)); });
        $(document).on('click', '.act-draft', function () { runAction('draft', $(this).data('id'), $(this)); });

        $('#reprocessMissingBtn').on('click', function () {
            var $btn = $(this).prop('disabled', true);
            $('#reprocessMissingResult').removeClass('text-success text-danger').text('جارٍ الجدولة…');
            $.post(correctBase + '/' + $btn.data('batch') + '/reprocess-missing', {})
                .done(function (r) { $('#reprocessMissingResult').addClass('text-success').text(r.message_out || 'تمت الجدولة'); })
                .fail(function (xhr) { $('#reprocessMissingResult').addClass('text-danger').text((xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الجدولة'); })
                .always(function () { $btn.prop('disabled', false); });
        });

        $(document).on('click', '.inv-thumb', function () {
            document.getElementById('invLbImg').src = this.dataset.full;
            document.getElementById('invLb').style.display = 'grid';
        });

        // Bundle A — visible manual edit. Opens the shared modal, populated from the
        // card's own .edit[data-field] spans; on save it POSTs only the changed fields
        // to /{id}/correct (which clears needs_review), then reloads to refresh badges.
        var invEditFields = ['supplier_name', 'supplier_tax_number', 'invoice_number', 'invoice_date', 'amount_before_vat', 'vat_amount', 'total_incl_vat'];
        function invEditRead(id) {
            var vals = {};
            $('.edit[data-id="' + id + '"][data-field]').each(function () {
                var f = $(this).data('field'), v = $(this).text().trim();
                if (f === 'invoice_date' && v) { v = v.slice(0, 10); }
                vals[f] = v;
            });
            return vals;
        }
        $(document).on('click', '.js-edit-inv', function () {
            var id = String($(this).data('id'));
            var vals = invEditRead(id);
            var $form = $('#invEditForm');
            $form.find('[name="__id"]').val(id);
            invEditFields.forEach(function (f) { $form.find('[name="' + f + '"]').val(vals[f] != null ? vals[f] : ''); });
            $form.data('orig', vals);
            $('#invEditResult').text('').removeClass('text-success text-danger');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('invEditModal')).show();
        });
        // Post changed fields SEQUENTIALLY, not in parallel: correct() writes to the
        // isolated SQLite invoices connection and recomputes the batch total on each
        // call — concurrent writes risk SQLITE_BUSY on multi-worker prod. Chaining
        // mirrors the one-field-at-a-time contenteditable blur behaviour.
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
                var nv = $form.find('[name="' + f + '"]').val().trim();
                if (nv !== (orig[f] != null ? String(orig[f]) : '')) { changed.push({ field: f, value: nv }); }
            });
            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('invEditModal'));
            if (!changed.length) { modal.hide(); $btn.prop('disabled', false); return; }
            $('#invEditResult').text('جارٍ الحفظ…').removeClass('text-success text-danger');
            invEditPostSeq(id, changed,
                function () {
                    $('#invEditResult').text('تم الحفظ').addClass('text-success');
                    window.location.reload();
                },
                function (xhr) {
                    var m = (xhr && xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الحفظ';
                    $('#invEditResult').text(m).addClass('text-danger');
                    $btn.prop('disabled', false);
                });
        });
    </script>
@endsection
