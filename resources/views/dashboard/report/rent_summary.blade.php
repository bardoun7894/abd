@extends('layouts.app')
@section('module', 'إدارة المالية')
@section('sub', 'عقود الإيجار')
@section('title', "$page_title")
@section('content')
    <div class="row g-5 mb-5">
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body text-center py-6">
                    <div class="fs-2hx fw-bolder text-dark">{{ number_format($stats['contracts_total']) }}</div>
                    <div class="fs-7 fw-bold text-muted mt-1">إجمالي العقود</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body text-center py-6">
                    <div class="fs-2hx fw-bolder text-success">{{ number_format($stats['contracts_paid']) }}</div>
                    <div class="fs-7 fw-bold text-muted mt-1">عقود مدفوعة بالكامل</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body text-center py-6">
                    <div class="fs-2hx fw-bolder text-danger">{{ number_format($stats['contracts_outstanding']) }}</div>
                    <div class="fs-7 fw-bold text-muted mt-1">عقود غير مكتملة الدفع</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body text-center py-6">
                    <div class="fs-3 fw-bolder text-dark">{{ number_format($stats['paid_amt'], 2) }}</div>
                    <div class="fs-8 text-muted">مدفوع من أصل {{ number_format($stats['total_amt'], 2) }}</div>
                    <div class="fs-8 fw-bold text-danger mt-1">متبقٍّ: {{ number_format($stats['outstanding_amt'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title fw-bold">تفصيل العقود حسب المحل</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered gy-4 align-middle">
                    <thead>
                        <tr class="fw-bold fs-7 text-muted text-uppercase">
                            <th>المحل</th>
                            <th class="text-center">عدد الدفعات</th>
                            <th class="text-center">المدفوعة</th>
                            <th class="text-end">إجمالي المبلغ</th>
                            <th class="text-end">المدفوع</th>
                            <th class="text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $r)
                            @php
                                $fullyPaid = (int) $r->paid_cnt >= (int) $r->total_cnt && (int) $r->total_cnt > 0;
                            @endphp
                            <tr>
                                <td class="fw-bold text-gray-800">{{ $r->shop_name }}</td>
                                <td class="text-center">{{ (int) $r->total_cnt }}</td>
                                <td class="text-center">{{ (int) $r->paid_cnt }}</td>
                                <td class="text-end">{{ number_format((float) $r->total_amt, 2) }}</td>
                                <td class="text-end text-success fw-bold">{{ number_format((float) $r->paid_amt, 2) }}</td>
                                <td class="text-center">
                                    @if ($fullyPaid)
                                        <span class="badge badge-light-success">مدفوع بالكامل</span>
                                    @else
                                        <span class="badge badge-light-danger">غير مكتمل</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-8">لا توجد دفعات إيجار مسجّلة بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
