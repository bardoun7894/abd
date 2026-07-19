@extends('layouts.app')
@section('module', 'استخراج الفواتير')
@section('sub', 'التقرير الإجمالي')
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

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-flush sn-stat h-100"><div class="card-body text-center py-4">
                <div class="sn-stat-ico"><i class="bi bi-calendar-check"></i></div>
                <div class="fs-2hx fw-bold text-gray-900 sn-num">{{ $stats['today'] }}</div>
                <div class="text-muted fs-8">فواتير اليوم</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush sn-stat h-100"><div class="card-body text-center py-4">
                <div class="sn-stat-ico"><i class="bi bi-calendar3"></i></div>
                <div class="fs-2hx fw-bold text-gray-900 sn-num">{{ $stats['thisMonth'] }}</div>
                <div class="text-muted fs-8">فواتير هذا الشهر</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush sn-stat h-100"><div class="card-body text-center py-4">
                <div class="sn-stat-ico"><i class="bi bi-cash-stack"></i></div>
                <div class="fs-2hx fw-bold text-success sn-num">{{ number_format($stats['totalPurchases'], 2) }}</div>
                <div class="text-muted fs-8">إجمالي المشتريات (ر.س)</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush sn-stat h-100"><div class="card-body text-center py-4">
                <div class="sn-stat-ico"><i class="bi bi-percent"></i></div>
                <div class="fs-2hx fw-bold text-info sn-num">{{ number_format($stats['totalVat'], 2) }}</div>
                <div class="text-muted fs-8">إجمالي الضريبة (ر.س)</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush sn-stat h-100"><div class="card-body text-center py-4">
                <div class="sn-stat-ico"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="fs-2hx fw-bold text-warning sn-num">{{ $stats['duplicates'] }}</div>
                <div class="text-muted fs-8">فواتير مكررة/مشتبهة</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush sn-stat h-100"><div class="card-body text-center py-4">
                <div class="sn-stat-ico"><i class="bi bi-x-circle"></i></div>
                <div class="fs-2hx fw-bold text-danger sn-num">{{ $stats['rejected'] }}</div>
                <div class="text-muted fs-8">فواتير مرفوضة</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush sn-stat h-100"><div class="card-body text-center py-4">
                <div class="sn-stat-ico"><i class="bi bi-hourglass-split"></i></div>
                <div class="fs-2hx fw-bold text-warning sn-num">{{ $stats['needsReview'] }}</div>
                <div class="text-muted fs-8">بانتظار المراجعة</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush sn-stat h-100"><div class="card-body text-center py-4">
                <div class="sn-stat-ico"><i class="bi bi-check-circle"></i></div>
                <div class="fs-2hx fw-bold text-success sn-num">{{ $stats['successRate'] }}%</div>
                <div class="text-muted fs-8">نسبة نجاح الاستخراج الآلي</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-flush sn-stat h-100"><div class="card-body text-center py-4">
                <div class="sn-stat-ico"><i class="bi bi-speedometer2"></i></div>
                <div class="fs-2hx fw-bold text-gray-900 sn-num">{{ number_format($stats['avgProcessingMs'] / 1000, 2) }}</div>
                <div class="text-muted fs-8">متوسط زمن المعالجة (ثانية)</div>
            </div></div>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-xl-6">
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-center flex-row gap-3 mb-0">
                        <span class="sn-chart-ico"><i class="bi bi-bar-chart-fill"></i></span>
                        <span class="card-label fw-bolder text-gray-900">أكثر الموردين تكرارًا</span>
                    </h3>
                </div>
                <div class="card-body pt-5 h-350px">
                    <canvas id="kt_chartjs_suppliers" class="mh-350px"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-center flex-row gap-3 mb-0">
                        <span class="sn-chart-ico"><i class="bi bi-pie-chart-fill"></i></span>
                        <span class="card-label fw-bolder text-gray-900">توزيع حالة الفواتير</span>
                    </h3>
                </div>
                <div class="card-body pt-5 h-350px">
                    <canvas id="kt_chartjs_status" class="mh-350px"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header"><h3 class="card-title">أكثر الأصناف تكرارًا</h3></div>
        <div class="table-responsive">
            <table class="table table-striped sn-thead gy-3 align-middle">
                <thead>
                    <tr class="fw-bold fs-7 text-uppercase">
                        <th>الصنف</th><th>عدد التكرارات</th><th>الإجمالي (ر.س)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stats['topItems'] as $it)
                        <tr><td>{{ $it->name }}</td><td>{{ $it->cnt }}</td><td>{{ number_format((float) $it->total, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">لا توجد بيانات بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var emeraldColor = KTUtil.getCssVariableValue('--sn-emerald');
        var emeraldSoft = KTUtil.getCssVariableValue('--sn-emerald-soft');
        var emeraldDeep = KTUtil.getCssVariableValue('--sn-emerald-deep');
        var amberColor = KTUtil.getCssVariableValue('--sn-amber');
        var rustColor = KTUtil.getCssVariableValue('--sn-rust');
        var skyColor = KTUtil.getCssVariableValue('--sn-sky');
        var fontFamily = KTUtil.getCssVariableValue('--bs-font-sans-serif');

        var supplierLabels = <?php echo json_encode($stats['topSuppliers']->pluck('supplier_name')); ?>;
        var supplierCounts = <?php echo json_encode($stats['topSuppliers']->pluck('cnt')); ?>;
        var supplierPalette = [emeraldColor, emeraldSoft, emeraldDeep, skyColor];
        var supplierColors = supplierCounts.map(function (_, i) { return supplierPalette[i % supplierPalette.length]; });

        new Chart(document.getElementById('kt_chartjs_suppliers'), {
            type: 'bar',
            data: {
                labels: supplierLabels,
                datasets: [{ label: 'عدد الفواتير', data: supplierCounts, backgroundColor: supplierColors }]
            },
            options: {
                responsive: true,
                plugins: { title: { display: false } },
                indexAxis: 'y',
            },
            defaults: { global: { defaultFont: fontFamily } }
        });

        new Chart(document.getElementById('kt_chartjs_status'), {
            type: 'doughnut',
            data: {
                labels: ['بانتظار المراجعة', 'مرفوضة', 'مكررة/مشتبهة'],
                datasets: [{
                    data: [{{ $stats['needsReview'] }}, {{ $stats['rejected'] }}, {{ $stats['duplicates'] }}],
                    backgroundColor: [amberColor, rustColor, skyColor],
                }]
            },
            options: {
                responsive: true,
                plugins: { title: { display: false } },
            },
            defaults: { global: { defaultFont: fontFamily } }
        });
    </script>
@endsection
