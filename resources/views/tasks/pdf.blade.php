<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $schedule->title }}</title>
    <style>
        body {
            font-family: 'DejaVuSans', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 14px;
            padding: 20px;
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

        .info-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-section p {
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            text-align: center;
        }

        th {
            background-color: #083da6;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
        }

        .badge {
            padding: 5px 10px;
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

        @font-face {
            font-family: 'DejaVuSans';
            font-style: normal;
            font-weight: normal;
            src: url({{ storage_path('fonts/DejaVuSans.ttf') }}) format("truetype");
        }
    </style>
</head>
<body>
    <div class="print-header">
        <img src="{{ public_path('assets/media/logos/logo.jpg') }}" alt="شعار الشركة" class="print-logo">
        <h1 class="print-title">شركة عبدالله سعيد ال هنيدي للمقاولات</h1>
        <h2>{{ $schedule->title }}</h2>
    </div>

    <div class="info-section">
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

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 20%">العامل</th>
                <th style="width: 20%">المنشأة</th>
                <th style="width: 20%">الخدمة</th>
                <th style="width: 25%">الملاحظات</th>
                <th style="width: 10%">يحتاج متابعة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedule->tasks as $task)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $task->worker->worker_name }}</td>
                <td>{{ $task->shop->shop_name }}</td>
                <td>{{ $task->service->title }}</td>
                <td>{{ $task->note }}</td>
                <td>
                    <span class="badge {{ $task->needs == '1' ? 'badge-warning' : 'badge-success' }}">
                        {{ $task->needs == '1' ? 'نعم' : 'لا' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>تم إنشاء هذا التقرير بتاريخ: {{ now()->format('Y-m-d H:i') }}</p>
        <p>شركة عبدالله سعيد ال هنيدي للمقاولات - جميع الحقوق محفوظة &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
