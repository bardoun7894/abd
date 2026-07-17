@extends('layouts.app')
@section('module', 'عقود الإيجار')
@section('sub', 'التحليلات')
@section('title', "$page_title")
@section('content')
<div class="row g-5 g-xl-8">
    {{-- KPI tiles --}}
    <div class="col-12">
        <div class="row g-3">
            @php $tiles = [
                ['العقود النشطة', $stats['active'], 'success'],
                ['المنتهية', $stats['ended'], 'secondary'],
                ['قابلة للتجديد (30 يوم)', $stats['renewable'], 'warning'],
                ['المتعثرة', $stats['troubled'], 'danger'],
                ['نسبة التحصيل %', $stats['collection_rate'], 'primary'],
                ['دفعات متأخرة', $stats['overdue'], 'danger'],
                ['مستحقة خلال 30 يوم', $stats['upcoming'], 'info'],
                ['إيراد الشهر', number_format($stats['monthly_revenue'], 2), 'success'],
            ]; @endphp
            @foreach ($tiles as [$label, $val, $color])
            <div class="col-6 col-md-3">
                <div class="card card-flush h-100">
                    <div class="card-body text-center py-5">
                        <div class="fs-2hx fw-bold text-{{ $color }}">{{ $val }}</div>
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
            <div class="card-header"><h3 class="card-title">توزيع حالة العقود</h3></div>
            <div class="card-body"><canvas id="statusChart" height="160"></canvas></div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card card-flush h-100">
            <div class="card-header"><h3 class="card-title">التحصيل مقابل المستحق</h3></div>
            <div class="card-body"><canvas id="collectionChart" height="160"></canvas></div>
        </div>
    </div>

    {{-- Spec 006 T6-3: future revenue forecast + AI collection-trend analysis --}}
    <div class="col-xl-6">
        <div class="card card-flush h-100">
            <div class="card-header"><h3 class="card-title">التوقعات المستقبلية للإيرادات</h3></div>
            <div class="card-body"><canvas id="forecastChart" height="160"></canvas></div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card card-flush h-100">
            <div class="card-header">
                <h3 class="card-title">تحليل الذكاء الاصطناعي للتحصيل</h3>
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
                <table class="table table-row-dashed align-middle fs-7">
                    <thead><tr class="fw-bold text-muted">
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
            <div class="card-header"><h3 class="card-title">أعلى المستأجرين (قيمة)</h3></div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                <table class="table table-row-dashed align-middle">
                    <thead><tr class="fw-bold text-muted"><th>المستأجر</th><th class="text-end">الإجمالي</th></tr></thead>
                    <tbody>
                    @forelse ($top_tenants as $t)
                        <tr><td>{{ $t->tenant_name }}</td><td class="text-end">{{ number_format((float) $t->total, 2) }}</td></tr>
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
            <div class="card-header"><h3 class="card-title">أكثر المستأجرين تأخراً</h3></div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                <table class="table table-row-dashed align-middle">
                    <thead><tr class="fw-bold text-muted"><th>المستأجر</th><th class="text-end">دفعات متأخرة</th></tr></thead>
                    <tbody>
                    @forelse ($late_tenants as $t)
                        <tr><td>{{ $t->tenant_name }}</td><td class="text-end text-danger fw-bold">{{ $t->late_count }}</td></tr>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['نشطة', 'منتهية', 'قابلة للتجديد', 'متعثرة'],
            datasets: [{ data: [{{ $stats['active'] }}, {{ $stats['ended'] }}, {{ $stats['renewable'] }}, {{ $stats['troubled'] }}],
                backgroundColor: ['#50cd89', '#a1a5b7', '#ffc700', '#f1416c'] }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
    new Chart(document.getElementById('collectionChart'), {
        type: 'bar',
        data: {
            labels: ['المستحق', 'المحصّل', 'إيراد سنوي'],
            datasets: [{ label: 'ريال', data: [{{ (float) $stats['due_total'] }}, {{ (float) $stats['paid_total'] }}, {{ (float) $stats['annual_revenue'] }}],
                backgroundColor: ['#0A4F3A', '#50cd89', '#0E6B4F'] }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
    new Chart(document.getElementById('forecastChart'), {
        type: 'line',
        data: {
            labels: [@foreach ($forecast as $f)'{{ $f['month'] }}',@endforeach],
            datasets: [
                { label: 'المستحق المجدوَل', data: [@foreach ($forecast as $f){{ (float) $f['scheduled'] }},@endforeach], borderColor: '#0A4F3A', backgroundColor: 'rgba(10,79,58,0.1)', tension: 0.3 },
                { label: 'الإيراد المتوقَّع (مرجّح بمعدل التحصيل)', data: [@foreach ($forecast as $f){{ (float) $f['projected'] }},@endforeach], borderColor: '#50cd89', backgroundColor: 'rgba(80,205,137,0.1)', tension: 0.3 }
            ]
        },
        options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
    });
})();
</script>
@endsection
