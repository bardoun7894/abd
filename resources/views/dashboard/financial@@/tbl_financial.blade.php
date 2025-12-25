<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>




<div class="row p-0 mb-1 px-9">
    <div class="col">
        <div class="border border-dashed border-gray-800 text-center min-w-125px rounded ">
            <span class="fs-4 fw-bold text-success d-block">اجمالي المبلغ المطلوب</span>
            <span class="fs-2hx fw-bolder text-gray-900 counted" data-kt-countup="true"
            id='sum_c1' name='sum_c1'
             style="font-size: 1.5rem !important;"></span>
        </div>
    </div>
    <div class="col">
        <div class="border border-dashed border-gray-800 text-center min-w-125px rounded ">
            <span class="fs-4 fw-bold text-primary d-block">اجمالي المدفوع</span>
            <span class="fs-2hx fw-bolder text-gray-900 counted" data-kt-countup="true"    id='sum_sum_det_financial_month_pay_All' name='sum_sum_det_financial_month_pay_All' style="font-size: 1.5rem !important;"></span>
        </div>
    </div>
    <div class="col">
        <div class="border border-dashed border-gray-800 text-center min-w-125px rounded ">
            <span class="fs-4 fw-bold text-danger d-block">اجمالي المتبقي</span>
            <span class="fs-2hx fw-bolder text-gray-900 counted" data-kt-countup="true"  id='sum_xx' name='sum_xx' style="font-size: 1.5rem !important;"></span>
        </div>
    </div>
</div>
   <div class="py-5">
        <table id="financial_tbl" class="table table-row-bordered gy-5">
        	<thead>
        		<tr class="fw-semibold fs-6 text-muted">
                    <th >#</th>
                    <th >اسم العامل</th>
                    <th >المجموعة</th>
                    <th >شهر الدفع</th>
                    <th >حالة</th>
                    <th >المبلغ المطلوب</th>
                    <th >اجمالي المدفوع </th>
                    <th >اجمالي المتبقي</th>
                    <th >عدد الاقساط</th>
                    <th >الملاحظة</th>
                    <th >المدخل</th>
                    <th >تاريخ الادخال</th>
<?php if (Perm::get_function_access(22)||Perm::get_function_access(23)||Perm::get_function_access(24)||Perm::get_function_access(25)) { ?>
                   <th >الاجراءات</th>
                    <?php } ?>
                        </tr>
        	</thead>
        	<tbody>
        	</tbody>
            <tfoot>
                <tr style="color:#4a0ce7 !important  ;background: #B5B5C3;">
                    <th style="text-align:center">الاجمالي:</th>
                    <th></th>
                    <th></th>

                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <?php if (Perm::get_function_access(22)||Perm::get_function_access(23)||Perm::get_function_access(24)||Perm::get_function_access(25)) { ?>
                    <th></th>
                    <?php } ?>
                </tr>
            </tfoot>
        </table>
    </div>
    <script >
        $(function () {
            var url = "{{ route('dashboard.financial.ajax_search_financial') }}";
        var save_method;
        var table;
        var financial_month_desc = $('#financial_month_desc_v').val();
        var manager_id = $('#manager_id_v').val();
    var worker_id = $('#worker_id_v').val();
        table = $('#financial_tbl').DataTable({
            "searching": false,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12 col-md-6'i><'col-sm-12 col-md-6'p>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

            pageLength: 50,
        "lengthMenu": [
            [20, 30, 50, 100, 150, 200],
            [20, 30, 50, 100, 150, 200]
        ],
            responsive: true,
            "ordering": false,
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
            "processing": true,
            "serverSide": true,
            "ajax": {
              url: url,

                "type": "POST",
                "beforeSend": function () {
              load_message();
                },
                "complete": function () {
                 unload_message();
                $('#sum_c1').html(sum_c1);
                $('#sum_sum_det_financial_month_pay_All').html(sum_sum_det_financial_month_pay_All);
                $('#sum_xx').html(sum_xx);

                },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},

                data: function (d) {
                 d.financial_month_desc =financial_month_desc;
                 d.worker_id = worker_id;
                 d.manager_id = manager_id;

                },

                "dataSrc": function(json) {
                    sum_c1 = json.sum_c1;
                    sum_count_statement = json.sum_count_statement;
                    sum_sum_det_financial_month_pay_All = json.sum_sum_det_financial_month_pay_All;
                    sum_xx = json.sum_xx;
                return json.data;
            },

            },







            "columnDefs": [
                {
                    render: function (data, type, full, meta) {
                        return "<div class='text-wrap width-200'>" + data + "</div>";
                    },
                    targets: 1
                }
            ],
            "footerCallback": function(row, data, start, end, display) {
            var api = this.api(),
                data;
            var intVal = function(i) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '') * 1 :
                    typeof i === 'number' ?
                    i : 0;
            };





            var x = api
            .column(5).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
            x = api
             .column(5, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            $(api.column(5).footer()).html('<span class="results">'+  x.toFixed(2) +' ر.س ' +'</span> ');




            var x = api
            .column(6).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
            x = api
             .column(6, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            $(api.column(6).footer()).html('<span class="results4">'+  x.toFixed(2) +' ر.س ' +'</span> ');


            var x = api
            .column(7).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
            x = api
             .column(7, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            $(api.column(7).footer()).html('<span class="results1">'+  x.toFixed(2) +' ر.س ' +'</span> ');



            var x = api
            .column(8).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
            x = api
             .column(8, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            $(api.column(8).footer()).html('<span class="results1">'+  x.toFixed(2) +' عدد.الاقساط ' +'</span> ');






        },










        });






          });
      </script>




