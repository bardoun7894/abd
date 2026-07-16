<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $schedule->title }}</title>
    <link href="{{asset('assets/plugins/global/plugins.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .page-break {
                page-break-after: always;
            }
            @page {
                margin: 20px;
            }
        }
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #083da6;
            padding-bottom: 20px;
        }
        .print-logo {
            max-width: 150px;
            margin-bottom: 15px;
        }
        .print-title {
            font-size: 24px;
            margin: 20px 0;
            color: #083da6;
        }
        .info-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            margin-bottom: 30px;
        }
        .info-card .card-body {
            padding: 20px;
        }
        .info-card p {
            margin: 8px 0;
            font-size: 15px;
        }
        .info-card strong {
            color: #083da6;
        }
        .table {
            margin-bottom: 30px;
        }
        .table th {
            background-color: #083da6;
            color: white;
            border: 1px solid #083da6;
        }
        .table td {
            padding: 12px 8px;
            vertical-align: middle;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        .badge-success {
            background-color: #28a745;
            color: #fff;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
        }
        .footer p {
            margin: 5px 0;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="mb-4 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> طباعة
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                <i class="bi bi-x"></i> إغلاق
            </button>
        </div>

        <div class="print-header">
            <img src="{{asset('assets/media/logos/logo.jpg')}}" alt="شعار الشركة" class="print-logo">
            <h1 class="print-title">شركة صباح النور</h1>
            <h2>{{ $schedule->title }}</h2>
        </div>

        <div class="info-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>تاريخ البداية:</strong> {{ $schedule->start_date->format('Y-m-d') }}</p>
                        @if($schedule->end_date)
                        <p><strong>تاريخ النهاية:</strong> {{ $schedule->end_date->format('Y-m-d') }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <p><strong>الحالة:</strong> {{ $schedule->status }}</p>
                        @if($schedule->description)
                        <p><strong>الوصف:</strong> {{ $schedule->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 15%">العامل</th>
                        <th style="width: 15%">رقم الاشتراك</th>
                        <th style="width: 15%">المنشأة</th>
                        <th style="width: 15%">رقم المنشأة</th>
                        <th style="width: 15%">الخدمة</th>
                        <th style="width: 10%">الملاحظات</th>
                        <th style="width: 10%">يحتاج متابعة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedule->tasks as $task)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $task->worker->worker_name }}</td>
                        <td>{{ $task->worker->registration_number }}</td>
                        <td>{{ $task->shop->shop_name }}</td>
                        <td>{{ $task->shop->establishment_number }}</td>
                        <td>{{ $task->service->title }}</td>
                        <td>{{ $task->note }}</td>
                        <td>
                            <span class="badge {{ $task->needs == '1' ? 'badge-success' : 'badge-warning' }}">
                                {{ $task->needs == '1' ? 'نعم' : 'لا' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>تم إنشاء هذا التقرير بتاريخ: {{ now()->format('Y-m-d H:i') }}</p>
            <p>شركة صباح النور - جميع الحقوق محفوظة &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>
