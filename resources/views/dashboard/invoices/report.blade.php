@extends('layouts.app')
@section('module', 'استخراج الفواتير')
@section('sub', 'التقرير الإجمالي')
@section('title', "$page_title")
@section('content')
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-flush h-100"><div class="card-body text-center py-4">
                <div class="fs-2hx fw-bold text-gray-900">{{ $stats['today'] }}</div>
                <div class="text-muted fs-8">فواتير اليوم</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush h-100"><div class="card-body text-center py-4">
                <div class="fs-2hx fw-bold text-gray-900">{{ $stats['thisMonth'] }}</div>
                <div class="text-muted fs-8">فواتير هذا الشهر</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush h-100"><div class="card-body text-center py-4">
                <div class="fs-2hx fw-bold text-success">{{ number_format($stats['totalPurchases'], 2) }}</div>
                <div class="text-muted fs-8">إجمالي المشتريات (ر.س)</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush h-100"><div class="card-body text-center py-4">
                <div class="fs-2hx fw-bold text-info">{{ number_format($stats['totalVat'], 2) }}</div>
                <div class="text-muted fs-8">إجمالي الضريبة (ر.س)</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush h-100"><div class="card-body text-center py-4">
                <div class="fs-2hx fw-bold text-warning">{{ $stats['duplicates'] }}</div>
                <div class="text-muted fs-8">فواتير مكررة/مشتبهة</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush h-100"><div class="card-body text-center py-4">
                <div class="fs-2hx fw-bold text-danger">{{ $stats['rejected'] }}</div>
                <div class="text-muted fs-8">فواتير مرفوضة</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush h-100"><div class="card-body text-center py-4">
                <div class="fs-2hx fw-bold text-warning">{{ $stats['needsReview'] }}</div>
                <div class="text-muted fs-8">بانتظار المراجعة</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-flush h-100"><div class="card-body text-center py-4">
                <div class="fs-2hx fw-bold text-success">{{ $stats['successRate'] }}%</div>
                <div class="text-muted fs-8">نسبة نجاح الاستخراج الآلي</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-flush h-100"><div class="card-body text-center py-4">
                <div class="fs-2hx fw-bold text-gray-900">{{ number_format($stats['avgProcessingMs'] / 1000, 2) }}</div>
                <div class="text-muted fs-8">متوسط زمن المعالجة (ثانية)</div>
            </div></div>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-xl-6">
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column"><span class="card-label fw-bolder text-info">أكثر الموردين تكرارًا</span></h3>
                </div>
                <div class="card-body pt-5 h-350px">
                    <canvas id="kt_chartjs_suppliers" class="mh-350px"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column"><span class="card-label fw-bolder text-info">توزيع حالة الفواتير</span></h3>
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
            <table class="table table-striped gy-3 align-middle">
                <thead>
                    <tr class="fw-bold fs-7 text-gray-800 border-bottom-2 border-gray-800" style="background-color:#ffb822 !important;">
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
        var primaryColor = KTUtil.getCssVariableValue('--bs-primary');
        var dangerColor = KTUtil.getCssVariableValue('--bs-danger');
        var successColor = KTUtil.getCssVariableValue('--bs-success');
        var warningColor = KTUtil.getCssVariableValue('--bs-warning');
        var infoColor = KTUtil.getCssVariableValue('--bs-info');
        var fontFamily = KTUtil.getCssVariableValue('--bs-font-sans-serif');

        var supplierLabels = <?php echo json_encode($stats['topSuppliers']->pluck('supplier_name')); ?>;
        var supplierCounts = <?php echo json_encode($stats['topSuppliers']->pluck('cnt')); ?>;

        new Chart(document.getElementById('kt_chartjs_suppliers'), {
            type: 'bar',
            data: {
                labels: supplierLabels,
                datasets: [{ label: 'عدد الفواتير', data: supplierCounts, backgroundColor: primaryColor }]
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
                    backgroundColor: [warningColor, dangerColor, infoColor],
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
