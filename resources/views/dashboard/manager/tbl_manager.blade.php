<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
   <div class="py-5">
        <table id="kt_datatable_zero_configuration" class="table table-row-bordered gy-5">
        	<thead>
        		<tr class="fw-semibold fs-6 text-muted">
                    <th>#</th>
                    <th>اسم القائد</th>
                    <th>رقم جوال القائد</th>
                    <th>الملاحظة</th>
                    <th>تاريخ الادخال</th>
                    <?php                 if( Perm::get_function_access(47) || Perm::get_function_access(48)) {
 ?>
                    <th>الاجراءات</th>
                    <?php } ?>
                     </tr>
        	</thead>
        	<tbody>
        	</tbody>
        </table>
    </div>
    <script type="text/javascript">
        $(function () {
            var url = "{{ route('dashboard.manager.ajax_search_manager') }}";
        var save_method;
        var table;
        var manager_name = $('#manager_name_v').val();
    var manager_mobile = $('#manager_mobile_v').val();
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
                 d.manager_name =manager_name;
                 d.manager_mobile = manager_mobile;

                },
            },

        });
         });
      </script>




