<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
   <div class="py-5">
        <table id="expense_tbl" class="table table-row-bordered gy-5">
        	<thead>
        		<tr class="fw-semibold fs-6 text-muted">
                    <th>#</th>
                    <th>نوع المصروف</th>
                    <th>التصنيف</th>
                    <th>تفصيل الصرف</th>
                    <th>المبلغ</th>
                    <th>قائد المحل</th>
                    <th>المحل</th>
                    <th>العامل</th>
                    <th>الملاحظة</th>
                    <th>المدخل</th>
                    <th>تاريح الادخال</th>
                    <?php   if ( Perm::get_function_access(61) || Perm::get_function_access(62)) { ?>
                    <th>الاجراءات</th>
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
                    <?php   if ( Perm::get_function_access(61) || Perm::get_function_access(62)) { ?>
                        <th></th>
                    <?php } ?>
                </tr>
            </tfoot>

        </table>
    </div>
    <script type="text/javascript">
        $(function () {
            var url = "{{ route('dashboard.expense.ajax_search_expense') }}";
        var save_method;
        var table;
        var expense_type_id = $('#expense_type_id_v').val();
    var expense_categoty_id = $('#expense_categoty_id_v').val();
    var expense_dt_from = $('#expense_dt_from').val();
    var expense_dt_to = $('#expense_dt_to').val();
    var manager_id = $('#manager_id_v').val();
    var worker_id = $('#worker_id_v').val();
    var shop_id = $('#shop_id_v').val();

        table = $('#expense_tbl').DataTable({
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
                },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: function (d) {
                 d.expense_type_id =expense_type_id;
                 d.expense_categoty_id = expense_categoty_id;
                 d.expense_dt_from =expense_dt_from;
                 d.expense_dt_to = expense_dt_to;
                 d.manager_id = manager_id;
                 d.worker_id = worker_id;
                 d.shop_id = shop_id;
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
            .column(4).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
            x = api
             .column(4, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            $(api.column(4).footer()).html('<span class="results">'+  x.toFixed(2) +' ر.س ' +'</span> ');









        },

        });
         });
      </script>




