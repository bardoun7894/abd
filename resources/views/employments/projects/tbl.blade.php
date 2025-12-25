<style type="text/css">
.dataTables_wrapper .dataTable .selected th, .dataTables_wrapper .dataTable .selected td {box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.954) !important;color: white !important;}
#project_tbl tbody tr.even:hover {background-color: #ebf2f0;cursor: pointer;}
#project_tbl tr.even:hover td.sorting_1 {background-color: #ebf2f0;cursor: pointer;}
#project_tbl tbody tr.odd:hover {background-color: #ebf2f0;cursor: pointer;}
#project_tbl tr.odd:hover td.sorting_1 {background-color: #ebf2f0;cursor: pointer;}
</style>
<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"          type="text/css" />
<!--      <div class="py-5">-->
<table id="project_tbl" class="table table-row-bordered gy-5">
<thead>
<tr class="fw-semibold fs-6 text-muted">
<th >#</th>
<th >رقم المشروع </th>
<th >اسم المشروع </th>
<th > الهدف العام </th>
<th > فكرة البرنامج </th>
<th > تاريخ التسجيل </th>
<th > تنفيذ المشروع </th>
<th > عدد ايام المشروع </th>
<th >الممول</th>
<th > قيمة المشروع </th>
<th >العملة</th>
<th > الجهة الشريكة </th>
<th >حالة الاغلاق</th>
<th > المحافظة المستهدفة </th>
    <?php if (Perm::get_function_access(3) || Perm::get_function_access(4) ||Perm::get_function_access(5)) {?>
    <th  >الاجراءات</th>
    <?php } ?>
</tr>
</thead>
<tbody>
</tbody>
</table>
   <!-- </div>-->

<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
    <script type="text/javascript">
        $(function () {
            var url = "{{ route('projects.ajax_search_project') }}";
        var save_method;
        var table;
var PROJECT_NAME_IN = $('#PROJECT_NAME_IN_V').val();
var FINANCIER_IN = $('#FINANCIER_IN_V').val();
var SIDE_ID_IN = $('#SIDE_ID_IN').val();
var START_DATE_IN = $('#START_DATE_IN_V').val();
var END_DATE_IN = $('#END_DATE_IN_V').val();
        table = $('#project_tbl').DataTable({
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


                 d.PROJECT_NAME_IN =PROJECT_NAME_IN;
                 d.FINANCIER_IN = FINANCIER_IN;
                 d.SIDE_ID_IN =SIDE_ID_IN;
                 d.END_DATE_IN = END_DATE_IN;

                },


            },

        });

$('#project_tbl tbody').on('click', 'tr', function () {
if ($(this).hasClass('selected')) {
$(this).removeClass('selected');
} else {
table.$('tr.selected').removeClass('selected');
$(this).addClass('selected');
}
});




          });
      </script>




