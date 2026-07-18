@extends('layouts.app')
@section('module', 'الإعدادات')
@section('sub', 'الذكاء الاصطناعي')
@section('title', "$page_title")
@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">{{ $page_title }}</h3>
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="fs-8 text-muted fw-bold">آخر</label>
            <select name="days" class="form-select form-select-sm form-select-solid w-auto" onchange="this.form.submit()">
                @foreach ([7, 30, 90, 365] as $d)
                    <option value="{{ $d }}" @selected($days === $d)>{{ $d }} يوم</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="row g-5 mb-5">
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body text-center py-6">
                <div class="fs-2hx fw-bolder text-dark">{{ number_format($stats['total_calls']) }}</div>
                <div class="fs-8 fw-bold text-muted mt-1">إجمالي الطلبات</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body text-center py-6">
                <div class="fs-2hx fw-bolder text-success">{{ $stats['hit_rate'] }}%</div>
                <div class="fs-8 fw-bold text-muted mt-1">نسبة الاستفادة من الذاكرة (توفير)</div>
                <div class="fs-9 text-muted">{{ number_format($stats['hits']) }} من الذاكرة / {{ number_format($stats['misses']) }} استدعاء فعلي</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body text-center py-6">
                <div class="fs-2 fw-bolder text-dark">${{ number_format($stats['cost_usd'], 4) }}</div>
                <div class="fs-8 text-muted">≈ {{ number_format($stats['cost_sar'], 2) }} ريال</div>
                <div class="fs-9 fw-bold text-muted mt-1">التكلفة التقديرية</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body text-center py-6">
                <div class="fs-3 fw-bolder text-dark">{{ number_format($stats['input_tokens'] + $stats['output_tokens']) }}</div>
                <div class="fs-9 text-muted">إدخال {{ number_format($stats['input_tokens']) }} / إخراج {{ number_format($stats['output_tokens']) }}</div>
                <div class="fs-9 fw-bold text-muted mt-1">التوكنز · مخزّن: {{ number_format($stats['cache_rows']) }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title fw-bold">حسب الوحدة</h3></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered gy-3 align-middle">
                            <thead><tr class="fw-bold fs-8 text-muted text-uppercase">
                                <th>الوحدة</th><th class="text-center">طلبات</th>
                                <th class="text-center">من الذاكرة</th><th class="text-end">التكلفة $</th>
                            </tr></thead>
                            <tbody>
                                @forelse ($byModule as $m)
                                    <tr>
                                        <td class="fw-bold text-gray-800">{{ $m->module ?: '—' }}</td>
                                        <td class="text-center">{{ number_format($m->calls) }}</td>
                                        <td class="text-center">{{ number_format($m->hits) }}</td>
                                        <td class="text-end">{{ number_format((float) $m->cost, 4) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-6">لا توجد بيانات بعد.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title fw-bold">حسب اليوم</h3></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered gy-3 align-middle">
                            <thead><tr class="fw-bold fs-8 text-muted text-uppercase">
                                <th>اليوم</th><th class="text-center">طلبات</th>
                                <th class="text-center">من الذاكرة</th><th class="text-end">التكلفة $</th>
                            </tr></thead>
                            <tbody>
                                @forelse ($byDay as $r)
                                    <tr>
                                        <td class="fw-bold text-gray-800">{{ $r->d }}</td>
                                        <td class="text-center">{{ number_format($r->calls) }}</td>
                                        <td class="text-center">{{ number_format($r->hits) }}</td>
                                        <td class="text-end">{{ number_format((float) $r->cost, 4) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-6">لا توجد بيانات بعد.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
