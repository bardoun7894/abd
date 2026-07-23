@extends('layouts.app')
@section('module', 'عقود الإيجار')
@section('sub', 'التحليلات')
@section('title', "$page_title")
@section('content')
    <style>
        .sn-stat{background:var(--sn-emerald-tint);border-color:var(--sn-emerald-tint)}
        .sn-stat .sn-stat-ico{width:40px;height:40px;border-radius:var(--sn-r-md);background:var(--sn-card);
            display:grid;place-items:center;font-size:18px;color:var(--sn-emerald-deep);box-shadow:var(--sn-shadow-sm);
            margin:0 auto 10px}
        .sn-chart-ico{width:36px;height:36px;min-width:36px;border-radius:var(--sn-r-md);background:var(--sn-emerald-tint);
            display:grid;place-items:center;font-size:16px;color:var(--sn-emerald-deep)}
    </style>
<div class="ai-page">
<div class="row g-5 g-xl-8">
    {{-- KPI tiles --}}
    <div class="col-12">
        <div class="row g-3">
            @php $tiles = [
                ['العقود النشطة', $stats['active'], 'success', 'bi-house-check'],
                ['المنتهية', $stats['ended'], 'secondary', 'bi-house-dash'],
                ['قابلة للتجديد (30 يوم)', $stats['renewable'], 'warning', 'bi-arrow-repeat'],
                ['المتعثرة', $stats['troubled'], 'danger', 'bi-exclamation-triangle'],
                ['نسبة التحصيل %', $stats['collection_rate'], 'primary', 'bi-percent'],
                ['دفعات متأخرة', $stats['overdue'], 'danger', 'bi-clock-history'],
                ['مستحقة خلال 30 يوم', $stats['upcoming'], 'info', 'bi-calendar-event'],
                ['إيراد الشهر', number_format($stats['monthly_revenue'], 2), 'success', 'bi-cash-stack'],
            ]; @endphp
            @foreach ($tiles as [$label, $val, $color, $icon])
            <div class="col-6 col-md-3">
                <div class="card card-flush sn-stat h-100">
                    <div class="card-body text-center py-5">
                        <div class="sn-stat-ico"><i class="bi {{ $icon }}"></i></div>
                        <div class="fs-2hx fw-bold text-{{ $color }} sn-num">{{ $val }}</div>
                        <div class="fs-7 text-gray-600 mt-2">{{ $label }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- charts --}}
    <div class="col-xl-6">
        <div class="card card-flush h-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-center flex-row gap-3 mb-0">
                    <span class="sn-chart-ico"><i class="bi bi-pie-chart-fill"></i></span>
                    <span class="card-label fw-bolder text-gray-900">توزيع حالة العقود</span>
                </h3>
            </div>
            <div class="card-body"><canvas id="statusChart" height="160"></canvas></div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card card-flush h-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-center flex-row gap-3 mb-0">
                    <span class="sn-chart-ico"><i class="bi bi-bar-chart-fill"></i></span>
                    <span class="card-label fw-bolder text-gray-900">التحصيل مقابل المستحق</span>
                </h3>
            </div>
            <div class="card-body"><canvas id="collectionChart" height="160"></canvas></div>
        </div>
    </div>

    {{-- Spec 006 T6-3: future revenue forecast + AI collection-trend analysis --}}
    <div class="col-xl-6">
        <div class="card card-flush h-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-center flex-row gap-3 mb-0">
                    <span class="sn-chart-ico"><i class="bi bi-graph-up-arrow"></i></span>
                    <span class="card-label fw-bolder text-gray-900">التوقعات المستقبلية للإيرادات</span>
                </h3>
            </div>
            <div class="card-body"><canvas id="forecastChart" height="160"></canvas></div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card card-flush h-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-center flex-row gap-3 mb-0">
                    <span class="sn-chart-ico"><i class="bi bi-stars"></i></span>
                    <span class="card-label fw-bolder text-gray-900">تحليل الذكاء الاصطناعي للتحصيل</span>
                </h3>
            </div>
            <div class="card-body">
                @if ($trend['source'] === 'ai' && $trend['narrative'])
                    <div class="d-flex align-items-start mb-4">
                        <i class="ki-duotone ki-abstract-26 fs-2 text-primary me-2 mt-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <p class="mb-0">{{ $trend['narrative'] }}</p>
                    </div>
                @else
                    <div class="text-muted fs-8 mb-3">
                        تعذّر توليد التحليل النصي بالذكاء الاصطناعي حالياً؛ إليك أرقام اتجاه التحصيل الخام:
                    </div>
                @endif
                <div class="table-responsive">
                <table class="table table-row-dashed sn-thead align-middle fs-7">
                    <thead><tr class="fw-bold text-uppercase">
                        <th>الشهر</th><th class="text-end">المستحق</th><th class="text-end">المحصّل</th><th class="text-end">النسبة %</th>
                    </tr></thead>
                    <tbody>
                    @forelse ($collection_history as $h)
                        <tr>
                            <td>{{ $h['month'] }}</td>
                            <td class="text-end">{{ number_format($h['due'], 2) }}</td>
                            <td class="text-end">{{ number_format($h['paid'], 2) }}</td>
                            <td class="text-end fw-bold {{ $h['rate'] >= 80 ? 'text-success' : ($h['rate'] >= 50 ? 'text-warning' : 'text-danger') }}">{{ $h['rate'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center">لا توجد بيانات كافية</td></tr>
                    @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    {{-- top / late tenants --}}
    <div class="col-xl-6">
        <div class="card card-flush">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-center flex-row gap-3 mb-0">
                    <span class="sn-chart-ico"><i class="bi bi-trophy-fill"></i></span>
                    <span class="card-label fw-bolder text-gray-900">أعلى المستأجرين (قيمة)</span>
                </h3>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                <table class="table table-row-dashed sn-thead align-middle">
                    <thead><tr class="fw-bold text-uppercase"><th>المستأجر</th><th class="text-end">الإجمالي</th></tr></thead>
                    <tbody>
                    @forelse ($top_tenants as $t)
                        <tr class="sn-row-hover"><td>{{ $t->tenant_name }}</td><td class="text-end sn-num">{{ number_format((float) $t->total, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="text-muted text-center">لا توجد بيانات</td></tr>
                    @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card card-flush">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-center flex-row gap-3 mb-0">
                    <span class="sn-chart-ico"><i class="bi bi-hourglass-split"></i></span>
                    <span class="card-label fw-bolder text-gray-900">أكثر المستأجرين تأخراً</span>
                </h3>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                <table class="table table-row-dashed sn-thead align-middle">
                    <thead><tr class="fw-bold text-uppercase"><th>المستأجر</th><th class="text-end">دفعات متأخرة</th></tr></thead>
                    <tbody>
                    @forelse ($late_tenants as $t)
                        <tr class="sn-row-hover"><td>{{ $t->tenant_name }}</td><td class="text-end text-danger fw-bold sn-num">{{ $t->late_count }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="text-muted text-center">لا يوجد تأخر</td></tr>
                    @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>{{-- /.ai-page --}}
@endsection
@section('styles')
    @include('dashboard.partials.ai-page-styles')
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    var emeraldColor = KTUtil.getCssVariableValue('--sn-emerald');
    var emeraldSoft = KTUtil.getCssVariableValue('--sn-emerald-soft');
    var emeraldDeep = KTUtil.getCssVariableValue('--sn-emerald-deep');
    var amberColor = KTUtil.getCssVariableValue('--sn-amber');
    var rustColor = KTUtil.getCssVariableValue('--sn-rust');
    var grayColor = KTUtil.getCssVariableValue('--bs-gray-400');

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['نشطة', 'منتهية', 'قابلة للتجديد', 'متعثرة'],
            datasets: [{ data: [{{ $stats['active'] }}, {{ $stats['ended'] }}, {{ $stats['renewable'] }}, {{ $stats['troubled'] }}],
                backgroundColor: [emeraldColor, grayColor, amberColor, rustColor] }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
    new Chart(document.getElementById('collectionChart'), {
        type: 'bar',
        data: {
            labels: ['المستحق', 'المحصّل', 'إيراد سنوي'],
            datasets: [{ label: 'ريال', data: [{{ (float) $stats['due_total'] }}, {{ (float) $stats['paid_total'] }}, {{ (float) $stats['annual_revenue'] }}],
                backgroundColor: [emeraldDeep, emeraldSoft, emeraldColor] }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
    new Chart(document.getElementById('forecastChart'), {
        type: 'line',
        data: {
            labels: [@foreach ($forecast as $f)'{{ $f['month'] }}',@endforeach],
            datasets: [
                { label: 'المستحق المجدوَل', data: [@foreach ($forecast as $f){{ (float) $f['scheduled'] }},@endforeach], borderColor: emeraldDeep, backgroundColor: 'rgba(10,79,58,0.1)', tension: 0.3 },
                { label: 'الإيراد المتوقَّع (مرجّح بمعدل التحصيل)', data: [@foreach ($forecast as $f){{ (float) $f['projected'] }},@endforeach], borderColor: emeraldColor, backgroundColor: 'rgba(14,107,79,0.12)', tension: 0.3 }
            ]
        },
        options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
    });
})();
</script>
@endsection
