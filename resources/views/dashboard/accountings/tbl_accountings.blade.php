<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
   <div class="py-5">
        <table id="kt_datatable_zero_configuration" class="table table-row-bordered gy-5">
        	<thead>
        		<tr class="fw-semibold fs-6 text-muted">
                    <th >#</th>
                    <th >اسم العامل</th>
                    <th >شهر الدفع</th>
                    <th >المبلغ المطلوب</th>
                    <th >المبلغ المدفوع </th>
                    <th >المبلغ المتبقي</th>
                    <th >الملاحظة</th>
                    <th >المدخل</th>
                    <th >تاريخ الادخال</th>
                    <th >الاجراءات</th>
                        </tr>
        	</thead>
        	<tbody>
        	</tbody>
        </table>
    </div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
    <script type="text/javascript">
        $(function () {
            var url = "{{ route('dashboard.accountings.ajax_search_accountings') }}";
        var save_method;
        var table;
        var payments_month_desc = $('#payments_month_desc_v').val();
    var worker_id = $('#worker_id_v').val();
        table = $('#kt_datatable_zero_configuration').DataTable({
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
                 d.payments_month_desc =payments_month_desc;
                 d.worker_id = worker_id;
                },


            },

        });






          });
      </script>




