
<?php

//dd($data);
?>

<h2>{{ $dt_from }}</h2>



<table class="table table-striped- table-bordered table-hover table-checkable " id="cust_tbl">
    <thead style="color:#eff2f7;background-color:#1A5276 ;text-align:center">
        <tr>
            <th >بيانات العميل</th>
            <th >بيانات الاتصال</th>
            <th >بيانات الاتصال</th>
            <th >بيانات الاتصال</th>
            <th >بيانات الاتصال</th>
            <th >بيانات الاتصال</th>

            <th >الاجراءات</th>
            <th >الاجراءات</th>
            <th >الاجراءات</th>

        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
<script language="javascript">
    $(document).ready(function () {

        var url = "{{ route('dashboard.workers.ajax_search_workers') }}";
        var save_method;
        var table;
        var dt_from = $('#dt_from').val();
    var dt_to = $('#dt_to').val();
    var worker_name = $('#worker_name_v').val();
    var sex = $('#sex_v').val();
    var phone = $('#phone_v').val();
    var email = $('#email_v').val();
        table = $('#cust_tbl').DataTable({
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
             // 'url': 'ajax_search_cust',
              url: url,

                "type": "POST",
                "beforeSend": function () {
                    load_message_table();
                },
                "complete": function () {
                    unload_message();
                },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},

                data: function (d) {
                 //   d.dt_from = dt_from,
                 d.dt_from =dt_from;
                 d.dt_to = dt_to;
                 d.worker_name =worker_name;
                 d.sex = sex;
                 d.phone = phone;
                 d.email = email;

                 /*   d.cust_id = cust_id,
                d.id_no = id_no,
                d.id_no_sso = id_no_sso,
                d.mobile = mobile,
                d.phone2 = phone2,
                d.dt_from = dt_from,
                d.dt_to = dt_to,
                d.doc_no = doc_no,
                d.state = state,
                d.csrf_test_name = Cookies.get('csrf_cookie_name')*/
                },


            },

        });














    });
</script>







