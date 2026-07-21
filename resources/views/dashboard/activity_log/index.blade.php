@extends('layouts.app')
@section('module', 'سجل النشاط')
@section('sub', 'سجل نشاط الموظفين')
@section('title', "$page_title")
@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">{{ $page_title }}</h3>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="fs-8 fw-bold text-muted mb-1">الموظف</label>
                    <select name="user_id" class="form-select form-select-sm form-select-solid">
                        <option value="">الكل</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(($filters['user_id'] ?? null) == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="fs-8 fw-bold text-muted mb-1">الإجراء</label>
                    <select name="action" class="form-select form-select-sm form-select-solid">
                        <option value="">الكل</option>
                        @foreach ($actions as $a)
                            <option value="{{ $a }}" @selected(($filters['action'] ?? null) === $a)>{{ $actionLabels[$a] ?? $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="fs-8 fw-bold text-muted mb-1">الجدول</label>
                    <select name="entity_type" class="form-select form-select-sm form-select-solid">
                        <option value="">الكل</option>
                        @foreach ($entities as $e)
                            <option value="{{ $e }}" @selected(($filters['entity_type'] ?? null) === $e)>{{ $e }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="fs-8 fw-bold text-muted mb-1">من تاريخ</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control form-control-sm form-control-solid">
                </div>
                <div class="col-6 col-md-2">
                    <label class="fs-8 fw-bold text-muted mb-1">إلى تاريخ</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control form-control-sm form-control-solid">
                </div>
                <div class="col-6 col-md-2">
                    <label class="fs-8 fw-bold text-muted mb-1">بحث</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث في الملخص" class="form-control form-control-sm form-control-solid">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">تصفية</button>
                    <a href="{{ route('dashboard.activity_log.index') }}" class="btn btn-sm btn-light">إعادة تعيين</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered gy-3 align-middle">
                    <thead>
                        <tr class="fw-bold fs-8 text-muted text-uppercase">
                            <th>التاريخ</th>
                            <th>الموظف</th>
                            <th>الإجراء</th>
                            <th>الجدول</th>
                            <th>ملخص</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td class="fs-8">{{ $row->created_at }}</td>
                                <td class="fs-8">{{ $row->user_name ?? '—' }}</td>
                                <td>
                                    @php($badge = match($row->action) {
                                        'create' => 'success',
                                        'update' => 'primary',
                                        'delete' => 'danger',
                                        'login' => 'info',
                                        'logout' => 'secondary',
                                        default => 'warning',
                                    })
                                    <span class="badge badge-light-{{ $badge }}">{{ $actionLabels[$row->action] ?? $row->action }}</span>
                                </td>
                                <td class="fs-8">{{ $row->entity_type ?? '—' }}</td>
                                <td class="fs-8">{{ $row->summary }}</td>
                                <td class="fs-8 text-muted">{{ $row->ip ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-8">لا توجد سجلات مطابقة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">
                {{ $rows->links() }}
            </div>
        </div>
    </div>

@endsection
