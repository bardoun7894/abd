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
                <div class="col-sm-5">
                    <label class="form-label fw-bold fs-8 text-muted mb-1">بحث بالاسم</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                           class="form-control form-control-solid" placeholder="اسم الملف…">
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-bold fs-8 text-muted mb-1">الحالة</label>
                    <select name="status" class="form-select form-select-solid">
                        <option value="">كل الحالات</option>
                        @foreach (AuditLabels::statuses() as $val => $label)
                            <option value="{{ $val }}" @selected(($filters['status'] ?? '') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
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
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-hover sn-thead align-middle gy-4">
                    <thead>
                        <tr class="fw-bold fs-7 text-uppercase">
                            <th class="ps-4">#</th>
                            <th class="min-w-250px">الملف</th>
                            <th class="text-center">عدد الفواتير</th>
                            <th class="text-end">الإجمالي العام</th>
                            <th class="text-center">الحالة</th>
                            <th>التاريخ</th>
                            <th class="text-end pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $b)
                            <tr>
                                <td class="ps-4 sn-num text-muted">{{ $b->id }}</td>
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
                                <td class="text-muted fs-7 sn-num">{{ $b->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('dashboard.invoices.show', $b->id) }}" class="btn btn-sm btn-light-primary fw-bold">
                                        عرض <i class="bi bi-arrow-left ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="d-flex flex-column align-items-center text-center py-10">
                                        <span class="badge badge-light-primary mb-4" style="width:64px;height:64px;border-radius:16px;font-size:28px;display:inline-flex;align-items:center;justify-content:center">
                                            <i class="bi bi-inboxes"></i>
                                        </span>
                                        <div class="fs-5 fw-bold text-gray-800 mb-1">
                                            @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? '') !== '')
                                                لا توجد نتائج مطابقة
                                            @else
                                                لا توجد عمليات بعد
                                            @endif
                                        </div>
                                        <div class="text-muted mb-5">ابدأ برفع فاتورة PDF ليستخرجها النظام تلقائياً.</div>
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
@endsection
