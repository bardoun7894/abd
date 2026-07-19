@extends('layouts.app')
@section('module', 'عقود الإيجار')
@section('sub', 'السجل')
@section('title', "$page_title")
@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold mb-0">سجل عمليات استخراج عقود الإيجار</h3>
            </div>
            <div class="card-toolbar d-flex align-items-center gap-3">
                <span class="text-muted fs-7 sn-num">{{ $batches->count() }} دفعة</span>
                <a href="{{ route('dashboard.leases.create') }}" class="btn btn-primary fw-bold">
                    <i class="bi bi-plus-lg me-1"></i> رفع عقد إيجار جديد
                </a>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-row-dashed sn-thead align-middle gy-4">
                    <thead>
                        <tr class="fw-bold fs-7 text-uppercase">
                            <th class="ps-4">#</th>
                            <th class="min-w-250px">الملف</th>
                            <th class="text-center">عدد الصفحات</th>
                            <th class="text-center">الحالة</th>
                            <th>التاريخ</th>
                            <th class="text-end pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $b)
                            <tr class="sn-row-hover">
                                <td class="ps-4 sn-num text-muted">{{ $b->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge badge-light-primary"><i class="bi bi-file-earmark-text"></i></span>
                                        <span class="fw-bold text-gray-800 text-truncate d-inline-block" style="max-width:280px">{{ $b->original_filename }}</span>
                                    </div>
                                </td>
                                <td class="text-center sn-num">{{ $b->processed_pages }} / {{ $b->total_pages }}</td>
                                <td class="text-center"><span class="badge badge-light-primary">{{ $b->status }}</span></td>
                                <td class="text-muted fs-7 sn-num">{{ $b->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('dashboard.leases.show', $b->id) }}" class="btn btn-sm btn-light-primary fw-bold">
                                        عرض <i class="bi bi-arrow-left ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="d-flex flex-column align-items-center text-center py-12">
                                        <span class="mb-4" style="width:72px;height:72px;border-radius:var(--sn-r-lg);font-size:30px;display:inline-flex;align-items:center;justify-content:center;background:var(--sn-emerald-tint);color:var(--sn-emerald-deep)">
                                            <i class="bi bi-inboxes"></i>
                                        </span>
                                        <div class="fs-5 fw-bold text-gray-800 mb-1">لا توجد عمليات بعد</div>
                                        <div class="text-muted mb-5">ابدأ برفع عقد إيجار PDF ليستخرج النظام بياناته تلقائياً.</div>
                                        <a href="{{ route('dashboard.leases.create') }}" class="btn btn-primary fw-bold">
                                            <i class="bi bi-plus-lg me-1"></i> رفع عقد إيجار جديد
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
