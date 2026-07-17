@extends('layouts.app')
@section('module', 'عقود الإيجار')
@section('sub', 'عقود غير معالَجة')
@section('title', "$page_title")
@section('content')
    <p class="text-muted fs-7 mb-4">العقود التي فشلت قراءتها أو تحتاج مراجعة قبل الاعتماد.</p>
    <div class="table-responsive">
        <table class="table table-striped gy-5 gs-5 align-middle">
            <thead>
                <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-800 sn-thead">
                    <th>#</th>
                    <th>الدفعة</th>
                    <th>رقم العقد</th>
                    <th>المستأجر</th>
                    <th>الحالة</th>
                    <th>ملاحظات التحقق / سبب الفشل</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($extractions as $e)
                    <tr class="{{ $e->needs_review ? 'table-warning' : '' }}">
                        <td>{{ $e->id }}</td>
                        <td><a href="{{ route('dashboard.leases.show', $e->batch_id) }}">#{{ $e->batch_id }}</a></td>
                        <td>{{ $e->contract_no }}</td>
                        <td>{{ $e->tenant_name }}</td>
                        <td><span class="badge badge-light-{{ $e->status === 'failed' ? 'danger' : 'warning' }}">{{ $e->status === 'failed' ? 'فشل' : 'تحتاج مراجعة' }}</span></td>
                        <td class="fs-8 text-muted">{{ $e->validation_notes ?? $e->error_message }}</td>
                        <td class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-light-primary reprocessBtn" data-id="{{ $e->id }}">إعادة القراءة</button>
                            <a href="{{ route('dashboard.leases.show', $e->batch_id) }}" class="btn btn-sm btn-light-warning">تعديل</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">لا توجد عقود غير معالَجة 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
@section('scripts')
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });
        $(document).on('click', '.reprocessBtn', function () {
            var $btn = $(this).prop('disabled', true).text('جارٍ إعادة الجدولة…');
            $.post("{{ url('dashboard/leases') }}/" + $btn.data('id') + '/reprocess')
                .done(function (r) { if (r.status) { location.reload(); } })
                .fail(function () { $btn.prop('disabled', false).text('إعادة القراءة'); });
        });
    </script>
@endsection
