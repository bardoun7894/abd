<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
   <div class="py-5">
        <table id="shop_table" class="table table-row-bordered gy-5 sn-thead">
        	<thead>
        		<tr class="fw-bold fs-7 text-uppercase">
                    <th>#</th>
                    <th>اسم المحل</th>
                    <th>رقم المنشأة</th>
                    <th>المجموعة</th>
                    <th>اسم المسؤول</th>
                    <th>المدينة</th>
                    <th>رقم جوال المسؤول</th>
                    <th>موقع المحل </th>
                    <th>معلومات البلدية</th>
                    <th>معلومات السجل التجاري</th>
                    <th>معلومات الإيجار</th>
                    <th>معلومات الدفاع المدني</th>
                    <th>دفعة الايجار</th>
                    <th>الملاحظة</th>
                    <th>عدد الملاحظات</th>
                    <th>تاريخ الادخال</th>
                    <?php
                    if (
                    Perm::get_function_access(32) || Perm::get_function_access(33)
                    || Perm::get_function_access(34) || Perm::get_function_access(35)
                    || Perm::get_function_access(36) || Perm::get_function_access(37)
                    || Perm::get_function_access(38)) {?>
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
            var url = "{{ route('dashboard.shop.ajax_search_shop') }}";
        var save_method;
        var table;
        var shop_name = $('#shop_name_v').val();
    var manager_id = $('#manager_id_v').val();
    var shop_respon = $('#shop_respon_v').val();
    var shop_mobile = $('#shop_mobile_v').val();
    var city_id = $('#city_id_v').val();
    var comme_no = $('#comme_no_v').val();
    var municip_no = $('#municip_no_v').val();
    var rentpay_price = $('#rentpay_price_v').val();

    var order_date = $('#order_date').val();
    var rentpay_year = $('#rentpay_year').val();
    var rentpay_month = $('#rentpay_month').val();
    var municip_year = $('#municip_year').val();
    var municip_month = $('#municip_month').val();
    var comme_year = $('#comme_year').val();
    var comme_month = $('#comme_month').val();

        table = $('#shop_table').DataTable({
            "searching": false,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12 col-md-6'i><'col-sm-12 col-md-6'p>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            pageLength: 200,
        "lengthMenu": [
            [20, 30, 50, 100, 150, 200, 500, 700,1000],
            [20, 30, 50, 100, 150, 200, 500, 700,1000]
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
                 d.shop_name =shop_name;
                 d.manager_id = manager_id;
                 d.shop_respon =shop_respon;
                 d.shop_mobile = shop_mobile;
                 d.city_id = city_id;
                 d.comme_no=comme_no;
                 d.municip_no=municip_no;
                 d.rentpay_price=rentpay_price;

                 d.order_date = order_date;
                  d.rentpay_year = rentpay_year;
                  d.rentpay_month = rentpay_month;
                  d.municip_year = municip_year;
                  d.municip_month = municip_month;
                  d.comme_year = comme_year;
                  d.comme_month = comme_month;
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
