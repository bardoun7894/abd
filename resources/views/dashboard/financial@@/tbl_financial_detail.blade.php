<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
   <div class="py-5">
        <table id="det_tbl" class="table table-row-bordered gy-5">
        	<thead>
        		<tr class="fw-semibold fs-6 text-muted">
                    <th >#</th>
                    <th >اسم المحل</th>
                    <th >شهر الدفع</th>
                    <th >المبلغ المطلوب</th>
                    <th >المبلغ المدفوع </th>
                    <th >المبلغ المتبقي</th>
                    <th >الملاحظة</th>
                    <th >المدخل</th>
                    <th >تاريخ الادخال</th>
                    <?php if (Perm::get_function_access(24)) { ?>
                    <th >الاجراءات</th>
                    <?php } ?>

                </tr>
        	</thead>
        	<tbody>
        	</tbody>
        </table>
    </div>
    <script type="text/javascript">
        $(function () {
            var url = "{{ route('dashboard.financial.ajax_search_financial_detail') }}";
        var save_method;
        var table;
        var financial_id = $('#financial_id_db').val();
        table = $('#det_tbl').DataTable({
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
                 d.financial_id =financial_id;
                },


            },

        });






          });
      </script>




