<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<style type="text/css">
    .text-wrap {white-space: normal;}
    .width-200 {width: 200px;text-align: right !important;}
    .dataTables_wrapper .dataTable .selected th, .dataTables_wrapper .dataTable .selected td {
       background-color: #e3faf3;
       color: #000 !important;

}

#worker_tbl tbody tr.even:hover {
       background-color: #ebf2f0;
       cursor: pointer;
   }

   #worker_tbl tr.even:hover td.sorting_1 {
       background-color: #ebf2f0;
       cursor: pointer;
   }
   #worker_tbl tbody tr.odd:hover {
       background-color: #ebf2f0;
       cursor: pointer;
   }

   #worker_tbl tr.odd:hover td.sorting_1 {
       background-color: #ebf2f0;
       cursor: pointer;
   }


    /* .results {background-color: #5867dd !important;color: #ffffff;}
    .results_disc {background-color: #ddd458 !important;color: #ffffff;}
    .results1 {background-color: #0abb87 !important;color: #ffffff;}
    .results2 {background-color: #191a25 !important;color: #ffffff;}
    .results3 {background-color: #e6a015 !important;color: #ffffff;}
    .results4 {background-color: #b527b8 !important;color: #ffffff;}
    .results5 {background-color: #27b8a0 !important;color: #ffffff;}
    .dataTables_wrapper .dataTable th {    color: white;}
    .table-bordered {border: 1px solid #232b51;}
    .dataTables_wrapper .dataTable td {color: #595d6e;}
    .dataTables_wrapper .dataTable th, .dataTables_wrapper .dataTable td {
        color: #282a3c !important;
    }
    .table.dataTable {
        font-size: 12px !important;
    }
    .dataTables_wrapper .dataTable th{
        color: #eff2f7 !important;
    } */
    </style>
<div class="p-0 px-9 mb-1 row">



    <div class="col">
        <div class="text-center rounded border border-gray-800 border-dashed min-w-150px">
            <span class="fs-4 fw-bold text-primary d-block"> ليس لديهم مجموعة</span>
            <span class="text-gray-900 fs-2hx fw-bolder counted" data-kt-countup="true"    id='not_have_manger' name='not_have_manger' style="font-size: 1.5rem !important;"></span>
        </div>
    </div>



    <div class="col">
        <div class="text-center rounded border border-gray-800 border-dashed min-w-150px">
            <span class="fs-4 fw-bold text-warning d-block">خارج المملكة</span>
            <span class="text-gray-900 fs-2hx fw-bolder counted" data-kt-countup="true"    id='out_ksa' name='out_ksa' style="font-size: 1.5rem !important;"></span>
        </div>
    </div>



    <div class="col">
        <div class="text-center rounded border border-gray-800 border-dashed min-w-150px">
            <span class="fs-4 fw-bold text-danger d-block">داخل المملكة</span>
            <span class="text-gray-900 fs-2hx fw-bolder counted" data-kt-countup="true"    id='in_ksa' name='in_ksa' style="font-size: 1.5rem !important;"></span>
        </div>
    </div>


    <div class="col">
        <div class="text-center rounded border border-gray-800 border-dashed min-w-150px">
            <span class="fs-4 fw-bold text-info d-block">اجمالي المستوردين</span>
            <span class="text-gray-900 fs-2hx fw-bolder counted" data-kt-countup="true"
            id='all_imp' name='all_imp'
             style="font-size: 1.5rem !important;"></span>
        </div>
    </div>
    <div class="col">
        <div class="text-center rounded border border-gray-800 border-dashed min-w-150px">
            <span class="fs-4 fw-bold text-success d-block">المستوردين المستمرين</span>
            <span class="text-gray-900 fs-2hx fw-bolder counted" data-kt-countup="true"    id='all_imp_not_cancal' name='all_imp_not_cancal' style="font-size: 1.5rem !important;"></span>
        </div>
    </div>
    <div class="col">
        <div class="text-center rounded border border-gray-800 border-dashed min-w-150px">
            <span class="fs-4 fw-bold text-orange d-block">المنهي خدماته</span>
            <span class="text-gray-900 fs-2hx fw-bolder counted" data-kt-countup="true"  id='all_cancal' name='all_cancal' style="font-size: 1.5rem !important;"></span>
        </div>
    </div>
</div>
   <div class="py-5">
        <table id="worker_tbl" class="table table-row-bordered gy-5 sn-thead">
        	<thead>
        		<tr class="fw-bold fs-7 text-uppercase">
                    <th >#</th>
                    <th >اسم العامل</th>
                    <th >رقم الإقامة</th>
                    <th >رقم الاشتراك</th>

                    <th>المجموعة</th>
                    <th >عدد الملاحظات</th>
                    <th >تاريخ اصدار الاقامة</th>
                    <th >تاريخ إنتهاء الإقامة</th>
                    <th >تاريخ انتهاء الجواز </th>
                    <th >تاريخ الميلاد</th>

                    <th >الجنسية</th>
                    <th >تاريخ التعيين </th>
                    <th >مكان العمل</th>
                    <th >المهنة</th>
                    <th >التواجد</th>
                    <th >الملاحظة</th>
                    <th >حالة العمل</th>
                    <th >تاريخ الادخال</th>
                    <th >نوع الادخال</th>

<?php
    if (
    Perm::get_function_access(12)||Perm::get_function_access(13)||Perm::get_function_access(15)||Perm::get_function_access(14)||
Perm::get_function_access(16)||Perm::get_function_access(17)||Perm::get_function_access(18)||Perm::get_function_access(19)) {?>

                   <th >الاجراءات</th>

                   <?php }?>

                        </tr>
        	</thead>
        	<tbody>
        	</tbody>
        </table>
    </div>
    <script type="text/javascript">
        $(function () {
            var url = "{{ route('dashboard.workers.ajax_search_workers') }}";
        var save_method;
        var table;
        var worker_name = $('#worker_name_v').val();
    var ssn = $('#ssn_v').val();
    var work_place_id = $('#work_place_id_v').val();
    var doe = $('#doe_v').val();
    var updatedcancal_at = $('#updatedcancal_at_v').val();
    var job_id = $('#job_id_v').val();
    var end_dt = $('#end_dt_v').val();
    var end_p_dt = $('#end_p_dt_v').val();
    var manager_id = $('#manager_id_v').val();
    var inside = $('#inside_v').val();
    var is_imp = $('#is_imp_v').val();
    var nation = $('#nation_v').val();
    var order_date = $('#order_date').val();
    var residence_month = $('#residence_month').val();
    var residence_year = $('#residence_year').val();
    var passport_month = $('#passport_month').val();
    var passport_year = $('#passport_year').val();

        table = $('#worker_tbl').DataTable({
            "searching": false,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12 col-md-6'i><'col-sm-12 col-md-6'p>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

            pageLength: 200,
        "lengthMenu": [
            [20, 30, 50, 100, 150, 200, 500, 1000],
            [20, 30, 50, 100, 150, 200, 500, 1000]
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
                  $('#all_imp').html(all_imp);
                $('#all_imp_not_cancal').html(all_imp_not_cancal);
                $('#all_imp_cancal').html(all_imp_cancal);
                $('#all_not_cancal').html(all_not_cancal);
                $('#all_cancal').html(all_cancal);
                $('#not_have_manger').html(not_have_manger);
                $('#out_ksa').html(out_ksa);
                $('#in_ksa').html(in_ksa);

                },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},

                data: function (d) {
                 d.worker_name =worker_name;
                 d.ssn = ssn;
                 d.work_place_id =work_place_id;
                 d.doe = doe;
                  d.updatedcancal_at = updatedcancal_at;
                  d.job_id = job_id;
                  d.end_dt = end_dt;
                  d.end_p_dt = end_p_dt;
                  d.manager_id = manager_id;
                  d.inside = inside;
                  d.is_imp = is_imp;
                  d.nation = nation;
                  d.order_date = order_date;
                  d.residence_month = residence_month;
                  d.residence_year = residence_year;
                  d.passport_month = passport_month;
                  d.passport_year = passport_year;
                },
                "dataSrc": function(json) {
    all_imp = json.all_imp;
    all_imp_not_cancal = json.all_imp_not_cancal;
    all_imp_cancal = json.all_imp_cancal;
    all_not_cancal = json.all_not_cancal;
    all_cancal = json.all_cancal;
    not_have_manger = json.not_have_manger;
    out_ksa = json.out_ksa;
    in_ksa = json.in_ksa;

                return json.data;
            },

            },
            "columnDefs": [
                {"className": "dt-center", "targets": "_all"},
                {
                    render: function (data, type, full, meta) {
                        return "<div class='text-wrap width-200'>" + data + "</div>";
                    },
                    targets: [1]
                },

              {
                   responsivePriority: 1,
                   targets: 0
               },
               {
                   responsivePriority: 2,
                   targets: -3
               },
               {
                   responsivePriority: 3,
                   targets: -2
               },
               {
                   responsivePriority: 4,
                   targets: -1
               }
           ]
        });


        $('#worker_tbl tbody').on('click', 'tr', function () {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });



          });
      </script>




