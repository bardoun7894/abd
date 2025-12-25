<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<style media="print">
    @page {
        size: landscape;
        margin: 10mm;
    }

    /* إخفاء كل شيء ما عدا الجدول */
    body * {
        visibility: hidden;
    }

    #expense_tbl,
    #expense_tbl *,
    .dtrg-group,
    .dtrg-start,
    .dtrg-level-0 {
        visibility: visible !important;
    }

    #expense_tbl {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    /* إخفاء عمود الإجراءات */
    .actions,
    th:last-child,
    td:last-child {
        display: none;
    }

    /* تنسيق الجدول للطباعة */
    #expense_tbl {
        border-collapse: collapse;
        width: 100%;
        direction: rtl;
    }

    #expense_tbl th,
    #expense_tbl td {
        border: 1px solid #000;
        padding: 8px;
        text-align: right;
    }

    /* تنسيق صفوف المجموعات */
    .dtrg-group {
        background-color: #f5f5f5 !important;
        font-weight: bold;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    tr.dtrg-group td {
        background-color: #f5f5f5 !important;
        font-weight: bold;
        padding: 10px 8px !important;
        border-bottom: 2px solid #000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    #expense_tbl thead th {
        background-color: #e4e6ef !important;
        color: #000;
        font-weight: bold;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    #expense_tbl tfoot tr {
        background-color: #e4e6ef !important;
        color: #4a0ce7;
        font-weight: bold;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* تنسيق الأرقام */
    .number-cell {
        text-align: center;
    }

    /* تأكيد ظهور جميع الصفوف */
    #expense_tbl tr {
        display: table-row !important;
        page-break-inside: avoid;
    }

    /* تنسيق خاص للمجموعات المتداخلة */
    .dtrg-level-0 {
        font-size: 14px;
        background-color: #f0f0f0 !important;
    }

     td[colspan="17"]{
        display: table-cell !important;
    visibility: visible !important;
    text-align: center !important;
    }

    .dtrg-level-1 {
        font-size: 13px;
        background-color: #f8f8f8 !important;
    }
</style>
   <div class="py-5">
        <div class="mb-3 d-flex justify-content-end">
            <button type="button" class="btn btn-danger me-2" onclick="
    // إنشاء عنوان الطباعة
    let printTitle = document.createElement('div');
    printTitle.innerHTML = '<h2 style=\'text-align: center; margin-bottom: 20px; font-family: Arial;\'>تقرير المصروفات</h2>';
    printTitle.style.cssText = 'visibility: visible; position: fixed; top: 20px; right: 0; width: 100%; background: white; padding: 10px 0;';
    document.body.insertBefore(printTitle, document.body.firstChild);

    // إظهار جميع الصفوف المجمعة
    let groupRows = document.querySelectorAll('.dtrg-group');
    groupRows.forEach(row => {
        row.style.cssText = 'display: table-row !important; visibility: visible !important;';
    });

    window.print();

    // إزالة العنوان
    document.body.removeChild(printTitle);
">
                <i class="fas fa-print"></i> طباعة PDF
            </button>
        </div>
        <table id="expense_tbl" class="table table-row-bordered gy-5">
        	<thead>
        		<tr class="fw-semibold fs-6 text-muted">
                    <th>#</th>
                    <th>نوع الرئيسي</th>
                    <th>نوع المصروف</th>
                    <th>التصنيف</th>
                    <th>المحل</th>
                    <th>العامل</th>

                    <th>المجموعة</th>
                    <th>الشهر</th>
                    <th >المبلغ شامل الضريبة</th>
                    <th>الضريبة</th>
                    <th >المبلغ دون الضريبة</th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                    <th>الحالة</th>
                    <th>ملاحظة</th>

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
    <style type="text/css">
.dtrg-group {
    background: #B5B5C3;
    text-align: right !important;

}


        </style>
        <!-- DataTables -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
        $(function () {
var expense_type_id = $('#expense_type_id_v').val();
if(expense_type_id==1){
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
    var expense_month_desc = $('#expense_month_desc_v').val();
    var type = $('#type_v').val();
    var det_calculate_month_remain = $('#det_calculate_month_remain_v').val();

        table = $('#expense_tbl').DataTable({
            "searching": false,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12 col-md-6'i><'col-sm-12 col-md-6'p>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            pageLength: 5000,
        "lengthMenu": [
            [ 5000],
            [ 5000],
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
            rowGroup: {
                dataSrc: [4]
        },
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
                 d.expense_month_desc = expense_month_desc;
                 d.type = type;
                 d.det_calculate_month_remain = det_calculate_month_remain;

                },
            },
            "columnDefs": [
                {
                    render: function (data, type, full, meta) {
                        return "<div class='text-wrap width-200'>" + data + "</div>";
                    },
                    targets: 1
                },
                {
            targets: [ 4 ],
            visible: false
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
            .column(8).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
            x = api
             .column(8, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            $(api.column(8).footer()).html('<span class="results">'+  x.toFixed(2) +' ر.س ' +'</span> ');

            var x = api
            .column(9).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
            x = api
             .column(9, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            $(api.column(9).footer()).html('<span class="results">'+  x.toFixed(2) +' ر.س ' +'</span> ');


            var x = api
            .column(10).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
            x = api
             .column(10, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            $(api.column(10).footer()).html('<span class="results">'+  x.toFixed(2) +' ر.س ' +'</span> ');






        },

        });

    }



  else  if(expense_type_id==3){


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
    var expense_month_desc = $('#expense_month_desc_v').val();
    var type = $('#type_v').val();
    var det_calculate_month_remain = $('#det_calculate_month_remain_v').val();

table = $('#expense_tbl').DataTable({
"searching": false,
dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
        "<'row'<'col-sm-12 col-md-6'i><'col-sm-12 col-md-6'p>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        pageLength: 5000,
        "lengthMenu": [
            [ 5000],
            [ 5000],
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
rowGroup: {
    dataSrc: [  5 ]
},
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
                 d.expense_month_desc = expense_month_desc;
                 d.type = type;
                 d.det_calculate_month_remain = det_calculate_month_remain;

    },
},
"columnDefs": [
    {
        render: function (data, type, full, meta) {
            return "<div class='text-wrap width-200'>" + data + "</div>";
        },
        targets: 1
    },
    {
targets: [ 5 ],
visible: false
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
.column(8).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
x = api
 .column(8, { page: 'current'} )
    .data()
    .reduce( function (a, b) {
        return intVal(a) + intVal(b);
    }, 0 );
$(api.column(8).footer()).html('<span class="results">'+  x.toFixed(2) +' ر.س ' +'</span> ');

var x = api
.column(9).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
x = api
 .column(9, { page: 'current'} )
    .data()
    .reduce( function (a, b) {
        return intVal(a) + intVal(b);
    }, 0 );
$(api.column(9).footer()).html('<span class="results">'+  x.toFixed(2) +' ر.س ' +'</span> ');


var x = api
.column(10).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
x = api
 .column(10, { page: 'current'} )
    .data()
    .reduce( function (a, b) {
        return intVal(a) + intVal(b);
    }, 0 );
$(api.column(10).footer()).html('<span class="results">'+  x.toFixed(2) +' ر.س ' +'</span> ');






},

});

}



else  {
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
    var expense_month_desc = $('#expense_month_desc_v').val();
    var type = $('#type_v').val();
    var det_calculate_month_remain = $('#det_calculate_month_remain_v').val();

table = $('#expense_tbl').DataTable({
"searching": false,
dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
        "<'row'<'col-sm-12 col-md-6'i><'col-sm-12 col-md-6'p>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        pageLength: 5000,
        "lengthMenu": [
            [ 5000],
            [ 5000],
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
                 d.expense_month_desc = expense_month_desc;
                 d.type = type;
                 d.det_calculate_month_remain = det_calculate_month_remain;
    },
},
"columnDefs": [
    {
        render: function (data, type, full, meta) {
            return "<div class='text-wrap width-200'>" + data + "</div>";
        },
        targets: 1
    },

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
.column(8).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
x = api
 .column(8, { page: 'current'} )
    .data()
    .reduce( function (a, b) {
        return intVal(a) + intVal(b);
    }, 0 );
$(api.column(8).footer()).html('<span class="results">'+  x.toFixed(2) +' ر.س ' +'</span> ');

var x = api
.column(9).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
x = api
 .column(9, { page: 'current'} )
    .data()
    .reduce( function (a, b) {
        return intVal(a) + intVal(b);
    }, 0 );
$(api.column(9).footer()).html('<span class="results">'+  x.toFixed(2) +' ر.س ' +'</span> ');


var x = api
.column(10).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
x = api
 .column(10, { page: 'current'} )
    .data()
    .reduce( function (a, b) {
        return intVal(a) + intVal(b);
    }, 0 );
$(api.column(10).footer()).html('<span class="results">'+  x.toFixed(2) +' ر.س ' +'</span> ');






},

});

}







         });



      </script>

<script>

function printTable() {

}

// إضافة class للخلايا الرقمية
document.addEventListener('DOMContentLoaded', function() {
    const numericColumns = [8, 9, 10, 11, 12];
    const table = document.getElementById('expense_tbl');
    const rows = table.getElementsByTagName('tr');

    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        numericColumns.forEach(colIndex => {
            if (cells[colIndex]) {
                cells[colIndex].classList.add('number-cell');
            }
        });
    }
});
</script>




