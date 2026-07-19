@extends('layouts.app')
@section('module', 'عقود الإيجار')
@section('sub', 'عقود غير معالَجة')
@section('title', "$page_title")
@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6 d-flex justify-content-between align-items-center">
            <h3 class="card-title fw-bolder text-info">عقود تحتاج مراجعة</h3>
            <span class="badge badge-light-primary fw-bold">{{ $extractions->count() }} عقد</span>
        </div>
        <div class="card-body pt-4">
            <p class="text-muted fs-7 mb-5">العقود التي فشلت قراءتها أو تحتاج مراجعة قبل الاعتماد.</p>
            <div class="table-responsive">
                <table class="table sn-thead align-middle sn-lease-review">
                    <colgroup>
                        <col class="sn-col-id">
                        <col class="sn-col-batch">
                        <col class="sn-col-contract">
                        <col class="sn-col-tenant">
                        <col class="sn-col-status">
                        <col class="sn-col-notes">
                        <col class="sn-col-actions">
                    </colgroup>
                    <thead>
                        <tr class="fw-bold fs-7 text-uppercase">
                            <th>#</th>
                            <th>الدفعة</th>
                            <th>رقم العقد</th>
                            <th>المستأجر</th>
                            <th>الحالة</th>
                            <th>ملاحظات التحقق / سبب الفشل</th>
                            <th class="text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($extractions as $e)
                            <tr class="sn-row-hover">
                                <td class="text-muted fw-bold">{{ $e->id }}</td>
                                <td>
                                    <a href="{{ route('dashboard.leases.show', $e->batch_id) }}" class="fw-bolder text-info">#{{ $e->batch_id }}</a>
                                </td>
                                <td class="fw-bold text-gray-800">{{ $e->contract_no ?: '—' }}</td>
                                <td class="fw-bold text-gray-800">{{ $e->tenant_name ?: '—' }}</td>
                                <td>
                                    <span class="badge badge-light-{{ $e->status === 'failed' ? 'danger' : 'secondary' }} fw-bold">
                                        {{ $e->status === 'failed' ? 'فشل' : 'تحتاج مراجعة' }}
                                    </span>
                                </td>
                                <td class="fs-8 text-muted sn-notes-cell">{{ $e->validation_notes ?? $e->error_message }}</td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end flex-nowrap">
                                        <button class="btn btn-sm btn-light-primary reprocessBtn" data-id="{{ $e->id }}">إعادة القراءة</button>
                                        <a href="{{ route('dashboard.leases.show', $e->batch_id) }}" class="btn btn-sm btn-light">تعديل</a>
                                        <button class="btn btn-sm btn-light-warning rejectBtn" data-id="{{ $e->id }}">رفض</button>
                                        <button class="btn btn-sm btn-icon btn-light-danger delBtn" data-id="{{ $e->id }}" title="حذف"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted fw-bold py-10">لا توجد عقود غير معالَجة 🎉</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });
        var lbase = "{{ url('dashboard/leases') }}";
        $(document).on('click', '.reprocessBtn', function () {
            var $btn = $(this).prop('disabled', true).text('جارٍ إعادة الجدولة…');
            $.post(lbase + "/" + $btn.data('id') + '/reprocess')
                .done(function (r) { if (r.status) { location.reload(); } })
                .fail(function () { $btn.prop('disabled', false).text('إعادة القراءة'); });
        });
        $(document).on('click', '.rejectBtn', function () {
            if (!confirm('سيتم رفض هذا العقد ولن يظهر هنا. متابعة؟')) return;
            var $btn = $(this).prop('disabled', true);
            $.post(lbase + "/" + $btn.data('id') + '/reject')
                .done(function (r) { if (r.status) { location.reload(); } else { alert(r.message_out || 'تعذّر الرفض'); $btn.prop('disabled', false); } })
                .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الرفض'); $btn.prop('disabled', false); });
        });
        $(document).on('click', '.delBtn', function () {
            if (!confirm('سيتم حذف هذا العقد المستخرَج نهائياً. متابعة؟')) return;
            var $btn = $(this).prop('disabled', true);
            $.ajax({ url: lbase + "/" + $btn.data('id'), method: 'DELETE' })
                .done(function (r) { if (r.status) { location.reload(); } else { alert(r.message_out || 'تعذّر الحذف'); $btn.prop('disabled', false); } })
                .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الحذف'); $btn.prop('disabled', false); });
        });
    </script>
@endsection
