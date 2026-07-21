@extends('layouts.app')
@section('module', 'الصندوق')
@section('sub', 'سندات القبض')
@section('title', 'الصندوق وسندات القبض')

@section('content')
<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>

<div class="d-flex flex-column flex-column-fluid">
    <div class="post d-flex flex-column-fluid">
        <div class="container-xxl">

            <div class="card mb-5">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa fa-cash-register text-primary me-2"></i>
                        <h2 class="fw-bold">رصيد الصندوق الحالي: {{ number_format($balance, 2) }}</h2>
                    </div>
                </div>
                <div class="card-body">
                    <form id="cashbox_filters" class="row g-5 align-items-end mb-6">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">من تاريخ</label>
                            <input type="date" id="f_from" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">إلى تاريخ</label>
                            <input type="date" id="f_to" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">نوع المصدر</label>
                            <select id="f_source_type" class="form-select">
                                <option value="">الكل</option>
                                <option value="shop_rentpay">إيجار المحلات</option>
                                <option value="expense">مصروفات</option>
                                <option value="purchase">مشتريات</option>
                                <option value="accountings">محاسبة</option>
                                <option value="financial">مالية</option>
                                <option value="lease_payment">دفعات عقود الإيجار</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btn_filter" class="btn btn-primary fw-bold">تصفية</button>
                        </div>
                    </form>

                    <table id="cashbox_tbl" class="table table-row-bordered gy-5">
                        <thead>
                            <tr class="fw-semibold fs-6 text-muted">
                                <th>#</th>
                                <th>التاريخ</th>
                                <th>المصدر</th>
                                <th>الاتجاه</th>
                                <th>المبلغ</th>
                                <th>الرصيد بعد الحركة</th>
                                <th>الدافع</th>
                                <th>الموظف</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@include('dashboard.cashbox.void_modal')

<script type="text/javascript">
$(function () {
    var url = "{{ route('dashboard.cashbox.ajax_search') }}";
    var table = $('#cashbox_tbl').DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12 col-md-6'i><'col-sm-12 col-md-6'p>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        pageLength: 50,
        lengthMenu: [[20, 30, 50, 100, 150, 200], [20, 30, 50, 100, 150, 200]],
        responsive: true,
        ordering: false,
        searching: false,
        language: {
            "sEmptyTable": "لا يوجد بيانات",
            "sProcessing": "جارٍ التحميل...",
            "sLengthMenu": "أظهر _MENU_ سجلات",
            "sZeroRecords": "لم يعثر على أية سجلات",
            "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ سجل",
            "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
            "sInfoFiltered": "(منتقاة من مجموع _MAX_ سجل)",
            "sInfoPostFix": "",
            "sSearch": "ابحث:",
            "sUrl": "",
            "oPaginate": {
                "sFirst": "الأول",
                "sPrevious": "السابق",
                "sNext": "التالي",
                "sLast": "الأخير"
            }
        },
        processing: true,
        serverSide: true,
        ajax: {
            url: url,
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: function (d) {
                d.from = $('#f_from').val();
                d.to = $('#f_to').val();
                d.source_type = $('#f_source_type').val();
            },
        },
    });

    $('#btn_filter').on('click', function () {
        table.ajax.reload();
    });

    $(document).on('click', '.cashbox_void', function () {
        openCashboxVoidModal($(this).data('id'), function () {
            table.ajax.reload(null, false);
        });
    });
});
</script>
@endsection
