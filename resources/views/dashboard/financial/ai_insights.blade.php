@extends('layouts.app')
@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري')
@section('title', "$page_title")
@section('content')
{{-- Spec 005-remaining-work — Financial AI analytics: forecast + anomaly + narrative,
     driven by FinancialAiService (financial + financial_detail tables). --}}
<div class="row g-5 g-xl-8">
    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header">
                <h3 class="card-title">🧠 تحليلات الذكاء الاصطناعي المالي</h3>
            </div>
            <div class="card-body">
                @if ($narrative['source'] === 'ai' && $narrative['narrative'])
                    <div class="d-flex align-items-start mb-4">
                        <i class="ki-duotone ki-abstract-26 fs-2 text-primary me-2 mt-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <p class="mb-0 fs-6 fw-semibold">{{ $narrative['narrative'] }}</p>
                    </div>
                @else
                    <div class="text-muted fs-8 mb-3">
                        تعذّر توليد التحليل النصي بالذكاء الاصطناعي حالياً؛ إليك أرقام اتجاه التحصيل الخام أدناه.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card card-flush h-100">
            <div class="card-header"><h3 class="card-title">التوقعات المستقبلية للمستحقات والتحصيل</h3></div>
            <div class="card-body"><canvas id="financialForecastChart" height="160"></canvas></div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card card-flush h-100">
            <div class="card-header"><h3 class="card-title">المستحق مقابل المحصّل (آخر 6 أشهر)</h3></div>
            <div class="card-body"><canvas id="financialHistoryChart" height="160"></canvas></div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card card-flush h-100">
            <div class="card-header"><h3 class="card-title">سجل التحصيل الشهري</h3></div>
            <div class="card-body pt-0">
                <table class="table table-row-dashed align-middle fs-7">
                    <thead><tr class="fw-bold text-muted">
                        <th>الشهر</th><th class="text-end">المستحق</th><th class="text-end">المحصّل</th><th class="text-end">المتبقي</th><th class="text-end">النسبة %</th>
                    </tr></thead>
                    <tbody>
                    @forelse ($history as $h)
                        <tr>
                            <td>{{ $h['month'] }}</td>
                            <td class="text-end">{{ number_format($h['due'], 2) }}</td>
                            <td class="text-end">{{ number_format($h['paid'], 2) }}</td>
                            <td class="text-end {{ $h['remaining'] < 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($h['remaining'], 2) }}</td>
                            <td class="text-end fw-bold {{ $h['rate'] >= 80 ? 'text-success' : ($h['rate'] >= 50 ? 'text-warning' : 'text-danger') }}">{{ $h['rate'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted text-center">لا توجد بيانات كافية</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card card-flush h-100">
            <div class="card-header"><h3 class="card-title">التوقّعات القادمة</h3></div>
            <div class="card-body pt-0">
                <table class="table table-row-dashed align-middle fs-7">
                    <thead><tr class="fw-bold text-muted">
                        <th>الشهر</th><th class="text-end">المستحق المتوقَّع</th><th class="text-end">التحصيل المتوقَّع</th>
                    </tr></thead>
                    <tbody>
                    @forelse ($forecast as $f)
                        <tr>
                            <td>{{ $f['month'] }}</td>
                            <td class="text-end">{{ number_format($f['due'], 2) }}</td>
                            <td class="text-end">{{ number_format($f['expected'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center">لا توجد بيانات كافية</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header">
                <h3 class="card-title">تنبيهات الشذوذ</h3>
            </div>
            <div class="card-body pt-0">
                @forelse ($anomalies as $note)
                    <div class="alert alert-warning d-flex align-items-center py-3 mb-3">
                        <i class="fas fa-exclamation-triangle fa-fw text-warning me-3"></i>
                        <span>{{ $note }}</span>
                    </div>
                @empty
                    <div class="d-flex align-items-center text-success fs-7">
                        <i class="fas fa-check-circle fa-fw me-2"></i>
                        لا توجد أي حالات شذوذ خلال الفترة المعروضة
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    new Chart(document.getElementById('financialHistoryChart'), {
        type: 'bar',
        data: {
            labels: [@foreach ($history as $h)'{{ $h['month'] }}',@endforeach],
            datasets: [
                { label: 'المستحق', data: [@foreach ($history as $h){{ (float) $h['due'] }},@endforeach], backgroundColor: '#7239ea' },
                { label: 'المحصّل', data: [@foreach ($history as $h){{ (float) $h['paid'] }},@endforeach], backgroundColor: '#50cd89' }
            ]
        },
        options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
    });
    new Chart(document.getElementById('financialForecastChart'), {
        type: 'line',
        data: {
            labels: [@foreach ($forecast as $f)'{{ $f['month'] }}',@endforeach],
            datasets: [
                { label: 'المستحق المتوقَّع', data: [@foreach ($forecast as $f){{ (float) $f['due'] }},@endforeach], borderColor: '#7239ea', backgroundColor: 'rgba(114,57,234,0.1)', tension: 0.3 },
                { label: 'التحصيل المتوقَّع (مرجّح بمعدل التحصيل التاريخي)', data: [@foreach ($forecast as $f){{ (float) $f['expected'] }},@endforeach], borderColor: '#50cd89', backgroundColor: 'rgba(80,205,137,0.1)', tension: 0.3 }
            ]
        },
        options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
    });
})();
</script>
@endsection
