@extends('layouts.app')
@section('module', 'عقود الإيجار')
@section('sub', 'السجل')
@section('title', "$page_title")
@section('content')
    <div class="mb-4">
        <a href="{{ route('dashboard.leases.create') }}" class="btn btn-primary fw-bold">+ رفع عقد إيجار جديد</a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped gy-5 gs-5 align-middle">
            <thead>
                <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-800" style="background-color:#ffb822 !important;">
                    <th>#</th>
                    <th class="min-w-250px">الملف</th>
                    <th>عدد الصفحات</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($batches as $b)
                    <tr>
                        <td>{{ $b->id }}</td>
                        <td>{{ $b->original_filename }}</td>
                        <td>{{ $b->processed_pages }} / {{ $b->total_pages }}</td>
                        <td><span class="badge badge-light-primary">{{ $b->status }}</span></td>
                        <td>{{ $b->created_at?->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route('dashboard.leases.show', $b->id) }}" class="btn btn-sm btn-light-primary">عرض</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">لا توجد عمليات بعد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
