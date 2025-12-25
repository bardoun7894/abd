<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
   <div class="py-5">
        <table id="purchase_tbl" class="table table-row-bordered gy-5">
        	<thead>
        		<tr class="fw-semibold fs-6 text-muted">
                    <th>#</th>
                    <th>رقم الفاتورة</th>
                    <th>تاريخ الفاتورة</th>
                    <th> المبلغ شامل الضريبة</th>
                    <th>   الضريبة</th>
                    <th> المبلغ غير شامل الضريبة</th>
                    <th>   الرقم الضريبي </th>
                    @if($request->shops == "on")
                    <th>المحل </th>
                    @else
                    <th>قائد المحل</th>
                    @endif
                    <th>اسم المورد</th>
                    <th> مُدخل الفاتورة</th>
                    <th>تاريح الادخال</th>
                    <th>الملاحظة</th>
  <?php  if ( Perm::get_function_access(57) || Perm::get_function_access(58)) { ?>
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
                    <th id="tex"></th>
                    <th id="without_tex"></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <?php  if ( Perm::get_function_access(57) || Perm::get_function_access(58)) { ?>
                        <th></th>
                    <?php } ?>
                </tr>
            </tfoot>

        </table>
    </div>
    <script type="text/javascript">
        $(function () {
            var url = "{{ route('dashboard.purchase.ajax_search_purchase') }}";
        var save_method;
        var table;
        var purchase_no = $('#purchase_no_v').val();
    var purchase_dt_from = $('#purchase_dt_from').val();
    var purchase_dt_to = $('#purchase_dt_to').val();
    var purchase_respon = $('#purchase_respon_v').val();
    var manager_id = $('#manager_id_v').val();
    var shop_id = $('#shop_id').val();
    var create_users = $('#create_users').val();
    var shops = "{{$request->shops }}";

        table = $('#purchase_tbl').DataTable({
            "searching": false,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12 col-md-6'i><'col-sm-12 col-md-6'p>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            pageLength: 3000,
        "lengthMenu": [
            [20, 30, 3000, 100, 13000, 200],
            [20, 30, 3000, 100, 13000, 200]
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
                 d.purchase_no =purchase_no;
                 d.purchase_dt_from = purchase_dt_from;
                 d.purchase_dt_to =purchase_dt_to;
                 d.purchase_respon = purchase_respon;
                 d.manager_id = manager_id;
                 d.shop_id = shop_id;
                 d.shops = shops;
                 d.create_users = create_users;
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
            .column(3).data().reduce( function (a, b) {
return intVal(a) + intVal(b);
}, 0 );
            x = api
             .column(3, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            $(api.column(3).footer()).html('<span class="results">'+  x.toFixed(2) +' ر.س ' +'</span> ');

            y = api
             .column(4, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            $(api.column(4).footer()).html('<span class="results">'+  y.toFixed(2) +' ر.س ' +'</span> ');

            z = api
             .column(5, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            $(api.column(5).footer()).html('<span class="results">'+  z.toFixed(2) +' ر.س ' +'</span> ');



        },

        });
         });
      </script>




