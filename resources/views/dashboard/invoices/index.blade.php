@extends('layouts.app')
@section('module', 'استخراج الفواتير')
@section('sub', 'السجل')
@section('title', "$page_title")
@section('content')
    @php use App\Support\AuditLabels; @endphp

    {{-- toolbar: new upload + filters --}}
    <div class="card mb-5">
        <div class="card-body py-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label fw-bold fs-8 text-muted mb-1">بحث بالاسم</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                           class="form-control form-control-solid" placeholder="اسم الملف…">
                </div>
                <div class="col-sm-3">
                    <label class="form-label fw-bold fs-8 text-muted mb-1">الحالة</label>
                    <select name="status" class="form-select form-select-solid">
                        <option value="">كل الحالات</option>
                        @foreach (AuditLabels::statuses() as $val => $label)
                            <option value="{{ $val }}" @selected(($filters['status'] ?? '') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <label class="form-label fw-bold fs-8 text-muted mb-1">من تاريخ</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                           class="form-control form-control-solid">
                </div>
                <div class="col-sm-2">
                    <label class="form-label fw-bold fs-8 text-muted mb-1">إلى تاريخ</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                           class="form-control form-control-solid">
                </div>
                <div class="col-sm-2">
                    <label class="form-label fw-bold fs-8 text-muted mb-1">أدنى عدد فواتير</label>
                    <input type="number" name="min_count" min="0" step="1" value="{{ $filters['min_count'] ?? '' }}"
                           class="form-control form-control-solid" placeholder="مثال: 5">
                </div>
                <div class="col-sm-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>تصفية
                    </button>
                    <a href="{{ route('dashboard.invoices.create') }}" class="btn btn-light-primary fw-bold" title="رفع فاتورة جديدة">
                        <i class="bi bi-plus-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold mb-0">سجل عمليات الاستخراج</h3>
            </div>
            <div class="card-toolbar gap-2">
                <a href="{{ route('dashboard.invoices.export', request()->only('q', 'status', 'date_from', 'date_to', 'min_count')) }}"
                   class="btn btn-sm btn-light-success fw-bold">
                    <i class="bi bi-file-earmark-excel me-1"></i>تصدير Excel
                </a>
                <span class="text-muted fs-7 sn-num">{{ $batches->total() }} دفعة</span>
            </div>
        </div>
        <div class="card-body pt-0">
            {{-- Spec 012 bundle B — bulk-action bar, hidden until ≥1 batch is checked. --}}
            <div id="bulkBar" class="alert alert-primary d-none align-items-center justify-content-between flex-wrap gap-2 mb-4">
                <span class="fw-bold"><span id="bulkCount">0</span> دفعة محددة</span>
                <div class="d-flex gap-2">
                    <button type="button" id="bulkPushOpenBtn" class="btn btn-sm btn-success fw-bold">
                        <i class="bi bi-send-check me-1"></i>ترحيل المحدد
                    </button>
                    <button type="button" id="bulkExportBtn" class="btn btn-sm btn-light-success fw-bold">
                        <i class="bi bi-file-earmark-excel me-1"></i>تصدير المحدد
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-row-dashed sn-thead align-middle gy-4">
                    <thead>
                        <tr class="fw-bold fs-7 text-uppercase">
                            <th class="ps-4" style="width:36px">
                                <input type="checkbox" class="form-check-input" id="selAll" title="تحديد الكل">
                            </th>
                            <th>#</th>
                            <th class="min-w-250px">الملف</th>
                            <th class="text-center">عدد الفواتير</th>
                            <th class="text-end">الإجمالي العام</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center">الترحيل</th>
                            <th>التاريخ</th>
                            <th class="text-end pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $b)
                            <tr class="sn-row-hover">
                                <td class="ps-4">
                                    <input type="checkbox" class="form-check-input js-batch-chk" value="{{ $b->id }}">
                                </td>
                                <td class="sn-num text-muted">{{ $b->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge badge-light-primary"><i class="bi bi-file-earmark-text"></i></span>
                                        <span class="fw-bold text-gray-800 text-truncate d-inline-block" style="max-width:280px">{{ $b->original_filename }}</span>
                                    </div>
                                </td>
                                <td class="text-center sn-num">{{ (int) $b->processed_pages }}</td>
                                <td class="text-end fw-bold text-success sn-num">{{ number_format((float) $b->grand_total, 2) }}</td>
                                <td class="text-center">
                                    @if ($b->status === 'processing')
                                        <span class="badge badge-light-{{ AuditLabels::statusColor($b->status) }}">
                                            <span class="spinner-border spinner-border-sm align-middle ms-1" style="width:.7rem;height:.7rem"></span>
                                            {{ AuditLabels::statusLabel($b->status) }}
                                        </span>
                                    @else
                                        <span class="badge badge-light-{{ AuditLabels::statusColor($b->status) }}">{{ AuditLabels::statusLabel($b->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $posted = (int) ($b->posted_count ?? 0);
                                        $totalInv = (int) ($b->invoices_count ?? 0);
                                    @endphp
                                    @if ($totalInv > 0 && $posted >= $totalInv)
                                        <span class="badge badge-light-success" title="كل الفواتير مُرحّلة إلى المشتريات">
                                            <i class="bi bi-check2-circle me-1"></i>مُرحّلة
                                        </span>
                                    @elseif ($posted > 0)
                                        <span class="badge badge-light-warning" title="{{ $posted }} من {{ $totalInv }} مُرحّلة">
                                            جزئياً {{ $posted }}/{{ $totalInv }}
                                        </span>
                                    @else
                                        <span class="badge badge-light-secondary">غير مُرحّلة</span>
                                    @endif
                                </td>
                                <td class="text-muted fs-7 sn-num">{{ $b->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('dashboard.invoices.show', $b->id) }}" class="btn btn-sm btn-light-primary fw-bold">
                                        عرض <i class="bi bi-arrow-left ms-1"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light-danger fw-bold js-del-batch"
                                            data-id="{{ $b->id }}" data-name="{{ $b->original_filename }}" title="حذف الدفعة">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="d-flex flex-column align-items-center text-center py-12">
                                        <span class="mb-4" style="width:72px;height:72px;border-radius:var(--sn-r-lg);font-size:30px;display:inline-flex;align-items:center;justify-content:center;background:var(--sn-emerald-tint);color:var(--sn-emerald-deep)">
                                            <i class="bi bi-inboxes"></i>
                                        </span>
                                        <div class="fs-5 fw-bold text-gray-800 mb-1">
                                            @if (collect($filters ?? [])->filter(fn ($v) => (string) $v !== '')->isNotEmpty())
                                                لا توجد نتائج مطابقة
                                            @else
                                                لا توجد عمليات بعد
                                            @endif
                                        </div>
                                        <div class="text-muted mb-5">
                                            @if (collect($filters ?? [])->filter(fn ($v) => (string) $v !== '')->isNotEmpty())
                                                جرّب تعديل كلمة البحث أو تغيير الفلتر.
                                            @else
                                                ابدأ برفع فاتورة PDF ليستخرجها النظام تلقائياً.
                                            @endif
                                        </div>
                                        <a href="{{ route('dashboard.invoices.create') }}" class="btn btn-primary fw-bold">
                                            <i class="bi bi-plus-lg me-1"></i> رفع فاتورة جديدة
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($batches->hasPages())
                <div class="d-flex justify-content-center mt-6">
                    {{ $batches->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Spec 012 bundle B — bulk "ترحيل المحدد" modal: shop XOR manager picker,
         mirroring show.blade.php's single-batch push panel (same $shops/$managers,
         same field names, same XOR enforcement). Submits to bulkPush() (bundle C). --}}
    <div class="modal fade" id="bulkPushModal" tabindex="-1" aria-labelledby="bulkPushModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header py-4">
                    <h3 class="modal-title fs-5" id="bulkPushModalLabel">ترحيل الدفعات المحددة إلى المشتريات</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted fs-7">سيتم ترحيل الفواتير المؤهلة من كل دفعة محددة (<span id="bulkPushCount">0</span>). اختر <strong>المحل</strong> أو <strong>قائد مجموعة</strong> — وليس كليهما.</p>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fs-7 fw-bold">المحل <span class="text-muted fw-normal">(مصاريف شراء محلات)</span></label>
                            <select id="bulkShopId" class="form-select form-select-sm">
                                <option value="">— اختر محلاً —</option>
                                @foreach ($shops as $x)
                                    <option value="{{ $x->shop_id }}">{{ $x->shop_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 text-center text-muted fs-7">أو</div>
                        <div class="col-md-6">
                            <label class="form-label fs-7">قائد المجموعة</label>
                            <select id="bulkManagerId" class="form-select form-select-sm">
                                <option value="">— اختر قائد مجموعة —</option>
                                @foreach ($managers as $x)
                                    <option value="{{ $x->manager_id }}">{{ $x->manager_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="bulkPushResult" class="mt-3 fs-7"></div>
                    <div class="d-flex align-items-center gap-3 mt-4">
                        <button type="button" id="bulkPushSubmitBtn" class="btn btn-sm btn-success">ترحيل الدفعات المحددة</button>
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">إلغاء</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });
        var invIndexUrl = "{{ url('dashboard/invoices') }}";
        $(document).on('click', '.js-del-batch', function () {
            var id = $(this).data('id');
            var name = $(this).data('name') || ('#' + id);
            if (!confirm('سيتم حذف الدفعة "' + name + '" وكل فواتيرها.\nأي فاتورة مُرحّلة إلى المشتريات سيتم عكس ترحيلها أيضاً.\nهل أنت متأكد؟')) return;
            var $row = $(this).closest('tr');
            $.ajax({
                url: invIndexUrl + '/' + id,
                method: 'DELETE',
                success: function (res) {
                    if (res && res.status) {
                        $row.fadeOut(200, function () { $(this).remove(); });
                    } else {
                        alert((res && res.message_out) || 'تعذّر الحذف');
                    }
                },
                error: function (xhr) {
                    alert((xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الحذف');
                }
            });
        });

        // Spec 012 bundle B — multi-select + bulk-action bar (checkbox column,
        // select-all, "ترحيل المحدد" modal, "تصدير المحدد"). Backend (bulkPush(),
        // exportBatches() batch_ids[]) is bundle C, already wired below.
        var bulkPushUrl = "{{ route('dashboard.invoices.bulk-push') }}";
        var bulkExportUrl = "{{ route('dashboard.invoices.export') }}";
        var bulkFilterParams = {!! json_encode(request()->only(['q', 'status', 'date_from', 'date_to', 'min_count'])) !!};

        function selectedBatchIds() {
            return $('.js-batch-chk:checked').map(function () { return $(this).val(); }).get();
        }

        function syncBulkBar() {
            var ids = selectedBatchIds();
            $('#bulkCount, #bulkPushCount').text(ids.length);
            $('#bulkBar').toggleClass('d-none', ids.length === 0).toggleClass('d-flex', ids.length > 0);
            $('#selAll').prop('checked', ids.length > 0 && ids.length === $('.js-batch-chk').length);
        }

        $(document).on('change', '#selAll', function () {
            $('.js-batch-chk').prop('checked', $(this).is(':checked'));
            syncBulkBar();
        });
        $(document).on('change', '.js-batch-chk', function () { syncBulkBar(); });

        // "تصدير المحدد" — a plain GET download carrying the selected ids alongside
        // the active filters (same params exportBatches() already reads).
        $('#bulkExportBtn').on('click', function () {
            var ids = selectedBatchIds();
            if (!ids.length) { return; }
            var params = $.extend({}, bulkFilterParams);
            var qs = $.param(params, true);
            ids.forEach(function (id) { qs += (qs ? '&' : '') + 'batch_ids[]=' + encodeURIComponent(id); });
            window.location = bulkExportUrl + (qs ? '?' + qs : '');
        });

        // select2 to match the single-batch push panel's dropdowns.
        if ($.fn.select2) { $('#bulkShopId, #bulkManagerId').select2({ dir: 'rtl', width: '100%', dropdownParent: $('#bulkPushModal') }); }
        $('#bulkManagerId').on('change', function () { if ($(this).val()) $('#bulkShopId').val('').trigger('change.select2'); });
        $('#bulkShopId').on('change', function () { if ($(this).val()) $('#bulkManagerId').val('').trigger('change.select2'); });

        $('#bulkPushOpenBtn').on('click', function () {
            if (!selectedBatchIds().length) { return; }
            $('#bulkPushResult').text('');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('bulkPushModal')).show();
        });

        $('#bulkPushSubmitBtn').on('click', function () {
            var ids = selectedBatchIds();
            var shopId = $('#bulkShopId').val(), managerId = $('#bulkManagerId').val();
            if (!ids.length) { return; }
            if (!shopId && !managerId) {
                $('#bulkPushResult').html('<span class="text-danger">الرجاء اختيار قائد مجموعة أو محل.</span>');
                return;
            }
            var $btn = $(this).prop('disabled', true).text('جارٍ الترحيل…');
            $('#bulkPushResult').html('<span class="text-muted">جارٍ الترحيل…</span>');
            $.post(bulkPushUrl, { batch_ids: ids, shop_id: shopId, manager_id: managerId })
                .done(function (r) {
                    var cls = r.status ? 'text-success' : 'text-danger';
                    $('#bulkPushResult').html('<span class="' + cls + '">' + $('<div>').text(r.message_out).html() + '</span>');
                    if (r.status) {
                        $('.js-batch-chk').prop('checked', false);
                        syncBulkBar();
                        // Refresh so the "الترحيل" badges reflect the newly-posted batches
                        // (backend already skips already-posted invoices — no duplicates).
                        setTimeout(function () { location.reload(); }, 2500);
                    }
                })
                .fail(function (xhr) {
                    var m = (xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الترحيل';
                    $('#bulkPushResult').html('<span class="text-danger">' + $('<div>').text(m).html() + '</span>');
                })
                .always(function () { $btn.prop('disabled', false).text('ترحيل الدفعات المحددة'); });
        });

        syncBulkBar();
    </script>
@endsection
