<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
   <div class="py-5">
        <table id="moraslat_tbl" class="table table-row-bordered gy-5">
        	<thead>
        		<tr class="fw-semibold fs-6 text-muted">
                    <th>#</th>
                    <th>نوع المراسلة</th>
                    <th>درجة الأهمية</th>
                    <th>نص المعاملة</th>
                    <th>مرسلة الى</th>
                    <th>المحل</th>
                    <th>العامل</th>
                    <th>الملاحظة</th>
                    <th>القراءة</th>
                    <th>الحالة</th>
                    <th>المرسل</th>
                    <th>تاريح الارسال</th>
<?php  if (Perm::get_function_access(51) || Perm::get_function_access(52) || Perm::get_function_access(53) || Perm::get_function_access(54)) {
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
            var url = "{{ route('dashboard.moraslat.ajax_search_moraslat') }}";
        var save_method;
        var table;
        var moraslat_type_id = $('#moraslat_type_id_v').val();
    var moraslat_categoty_id = $('#moraslat_categoty_id_v').val();
    var moraslat_dt_from = $('#moraslat_dt_from').val();
    var moraslat_dt_to = $('#moraslat_dt_to').val();
    var user_id = $('#user_id_v').val();
    var worker_id = $('#worker_id_v').val();
    var shop_id = $('#shop_id_v').val();
    var moraslat_id = $('#moraslat_id_v').val();


        table = $('#moraslat_tbl').DataTable({
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
                 d.moraslat_type_id =moraslat_type_id;
                 d.moraslat_categoty_id = moraslat_categoty_id;
                 d.moraslat_dt_from =moraslat_dt_from;
                 d.moraslat_dt_to = moraslat_dt_to;
                 d.user_id = user_id;
                 d.worker_id = worker_id;
                 d.shop_id = shop_id;
                 d.moraslat_id = moraslat_id;


                },
            },
            "columnDefs": [
                {"className": "dt-center", "targets": "_all"},
              {
                   responsivePriority: 1,
                   targets: 0
               },
               {
                   responsivePriority: 2,
                   targets: 1
               },
               {
                   responsivePriority: 3,
                   targets: 2
               },
               {
                   responsivePriority: 4,
                   targets: -1
               }
           ]
        });
         });
      </script>




