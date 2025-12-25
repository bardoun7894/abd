<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<style type="text/css">
    .text-wrap {white-space: normal;}
    .width-200 {width: 100px;}
    .dataTables_wrapper .dataTable .selected th, .dataTables_wrapper .dataTable .selected td {
       background-color: #e3faf3;
       color: #000 !important;

}

#violation_tbl tbody tr.even:hover {
       background-color: #ebf2f0;
       cursor: pointer;
   }

   #violation_tbl tr.even:hover td.sorting_1 {
       background-color: #ebf2f0;
       cursor: pointer;
   }
   #violation_tbl tbody tr.odd:hover {
       background-color: #ebf2f0;
       cursor: pointer;
   }

   #violation_tbl tr.odd:hover td.sorting_1 {
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
<div class="row p-0 mb-1 px-9">
    <!--begin::Col-->
    <div class="col">
        <div class="border border-dashed border-gray-800 text-center min-w-125px rounded ">
            <span class="fs-4 fw-bold text-success d-block">اجمالي المبلغ المطلوب</span>
            <span class="fs-2hx fw-bolder text-gray-900 counted" data-kt-countup="true"
                  id='violation_val_all_pay' name='violation_val_all_pay'
                  style="font-size: 1.5rem !important;">0</span>
        </div>
    </div>
    <!--end::Col-->
    <!--begin::Col-->
    <div class="col">
        <div class="border border-dashed border-gray-800 text-center min-w-125px rounded ">
            <span class="fs-4 fw-bold text-primary d-block">اجمالي المدفوع</span>
            <span class="fs-2hx fw-bolder text-gray-900 counted" data-kt-countup="true"    id='violation_val_pay' name='violation_val_pay' style="font-size: 1.5rem !important;">0</span>
        </div>
    </div>
    <!--end::Col-->
    <!--begin::Col-->
    <div class="col">
        <div class="border border-dashed border-gray-800 text-center min-w-125px rounded ">
            <span class="fs-4 fw-bold text-danger d-block">اجمالي غير مدفوع</span>
            <span class="fs-2hx fw-bolder text-gray-900 counted" data-kt-countup="true"  id='violation_val_not_pay' name='violation_val_not_pay' style="font-size: 1.5rem !important;">0</span>
        </div>
    </div>
    <!--end::Col-->
</div>

   <div class="py-5">
        <table id="violation_tbl" class="table table-row-bordered gy-5">
        	<thead>
        		<tr class="fw-semibold fs-6 text-muted">
                    <th>#</th>
                    <th>رقم المخالفة </th>
                    <th>اسم المحل</th>
                    <th>المجموعة</th>
                    <th>تاريخ المخالفة </th>
                    <th>قيمة المخالفة </th>
                    <th>حالة دفع</th>
                    <th>جهة المخالفة</th>
                    <th>السبب</th>
                    <th>اسم المسؤول</th>
                    <th>رقم السجل التجاري</th>
                    <th>رقم الرخصة</th>
                    <th>بيانات الادخال</th>
                    <?php  if (Perm::get_function_access(78) || Perm::get_function_access(79))  { ?>
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
                <th></th>
                <?php  if (Perm::get_function_access(78) || Perm::get_function_access(79))  { ?>
                <th></th>
                <?php } ?>

            </tr>
            </tfoot>

        </table>
    </div>
    <script type="text/javascript">
        $(function () {
            var url = "{{ route('dashboard.violation.ajax_search_violation') }}";
        var save_method;
        var table;
        var violation_month_desc = $('#violation_month_desc_v').val();
    var shop_id = $('#shop_id_v').val();
    var manager_id = $('#manager_id_v').val();

    var violation_no = $('#violation_no_v').val();
    var violation_ispay = $('#violation_ispay_v').val();
    var comme_no = $('#municip_no_v').val();
    var municip_no = $('#municip_no_v').val();
    var shop_respon = $('#shop_respon_v').val();


        table = $('#violation_tbl').DataTable({
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
                    $('#violation_val_all_pay').html(violation_val_all_pay);
                    $('#violation_val_pay').html(violation_val_pay);
                    $('#violation_val_not_pay').html(violation_val_not_pay);

                },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},

                data: function (d) {
                 d.violation_month_desc =violation_month_desc;
                 d.shop_id = shop_id;
                 d.manager_id = manager_id;
                 d.violation_no = violation_no;
                 d.violation_ispay = violation_ispay;
                 d.comme_no = comme_no;
                 d.municip_no = municip_no;
                 d.shop_respon = shop_respon;





                },
                "dataSrc": function(json) {
                    violation_val_all_pay = json.violation_val_all_pay;
                    sum_count_statement = json.sum_count_statement;
                    violation_val_pay = json.violation_val_pay;
                    violation_val_not_pay = json.violation_val_not_pay;
                    return json.data;
                },

            },


            "columnDefs": [
                                {"className": "dt-center", "targets": "_all"},

                {
                    render: function (data, type, full, meta) {
                        return "<div class='text-wrap width-200'>" + data + "</div>";
                    },
                    targets: [2,3]
                },
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












            },



        });



        $('#violation_tbl tbody').on('click', 'tr', function () {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });


          });
      </script>




