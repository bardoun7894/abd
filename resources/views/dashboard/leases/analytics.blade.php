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

    {{-- top / late tenants --}}
    <div class="col-xl-6">
        <div class="card card-flush">
            <div class="card-header"><h3 class="card-title">أعلى المستأجرين (قيمة)</h3></div>
            <div class="card-body pt-0">
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
    <div class="col-xl-6">
        <div class="card card-flush">
            <div class="card-header"><h3 class="card-title">أكثر المستأجرين تأخراً</h3></div>
            <div class="card-body pt-0">
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
                backgroundColor: ['#7239ea', '#50cd89', '#009ef7'] }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
})();
</script>
@endsection
