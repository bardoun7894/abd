@extends('layouts.app')
@section('module',"وزارة العمل ")
@section('sub',"أنظمة الوزارة ")
@section('title',"$page_title")
@section('content')
<style>
.mol_home_ a {font-size: 16px !important;}
</style>

  <?php
     if (isset($list)) {
         $z = count($list);
         if ($z == 0) {
             $worker_id = "";
             $worker_full_name = "";
             $worker_mobile_no = "";
             $worker_whatsapp_no = "";
             $op_name_ar = "";
             $worker_notes = "";
             $request_status_name = "";
             $insert_date = "";
             $seq_no = "";
             $request_status_id = "";
                          $license_type_id='';
             $license_type_name= '';

         }
         if ($z != 0) {
             foreach ($list as $x) {
             $worker_id = $x->worker_id;
             $license_type_id= $x->license_type_id;
             $license_type_name= $x->license_type_name;
             $worker_full_name= $x->worker_full_name;
             $worker_mobile_no= $x->worker_mobile_no;
             $worker_whatsapp_no= $x->worker_whatsapp_no;
             $op_name_ar= $x->op_name_ar;
             $worker_notes = $x->worker_notes;
             $request_status_name = $x->request_status_name;
             $insert_date= $x->insert_date;
                 $request_status_id=  $x->request_status_id;
      $seq_no=$x->seq_no;

}
}
}
if($worker_id!='' and $license_type_id==2){ ?>
<div class="notice d-flex bg-light-warning rounded border-danger border border-dashed p-9">
    <span class="svg-icon svg-icon-2tx svg-icon-warning me-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="black"></rect>
            <rect x="11" y="14" width="7" height="2" rx="1" transform="rotate(-90 11 14)" fill="black"></rect>
            <rect x="11" y="17" width="2" height="2" rx="1" transform="rotate(-90 11 17)" fill="black"></rect>
        </svg>
    </span>
    <div class="d-flex flex-stack flex-grow-1">
        <div class="fw-bold">
            <h4 class="text-danger fw-bolder">ملاحظة!</h4>
            <div class="fs-4 text-dark">لقد تم تقديم الطلب مسبقا - شكرا لك
            </div>
        </div>
    </div>
</div>
   <div class="py-5">
    <table id="tbl_job_data" name="tbl_job_data"
    class="table table-row-bordered gy-5">
    <thead style="color:#eff2f7;background-color:#232b51;text-align:center">
        <tr class="fw-semibold fs-6 text-muted">
<th>رقم الهوية</th>
<th>العامل</th>
<th>رقم الجوال</th>
<th>رقم الواتس</th>
<th>اسم الشركة</th>
<th>ملاحظات</th>
<th>الاعتماد</th>
<th>تاريخ التسجيل</th>
<th>الاجراءات</th>
     </tr>
 </thead>
 <tbody>
                 <tr>
                     <td  style="text-align: center;"> <?php echo $worker_id ?></td>
                     <td  style="text-align: center;"> <?php echo $worker_full_name ?></td>
                     <td  style="text-align: center;"> <?php echo $worker_mobile_no ?></td>
                     <td  style="text-align: center;"> <?php echo $worker_whatsapp_no ?></td>
                     <td  style="text-align: center;"> <?php echo $op_name_ar ?></td>
                     <td  style="text-align: center;"> <?php echo $worker_notes ?></td>
                     <td  style="text-align: center;"> <?php echo $request_status_name ?></td>
                     <td  style="text-align: center;"> <?php echo $insert_date ?></td>
                     <td>
                             <a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"
                            onclick="del_order('<?php echo $seq_no ?>','<?php echo $worker_id ?>','<?php echo $request_status_id ?>')"> <i class="fas fa-trash-alt fa-fw"></i></a>
                         </td>
                 </tr>
 </tbody>
</table>
    </div>
<?php } else {
//$txt_msg='لقد قمت مسبقاً باختيار الغاء الطلب لذلك لن يمكنك الدخول الى هذه الصفحة';
$txt_msg=' لقد قمت مسبقاً باختيار   ';
$txt_msg.=' '.$license_type_name;
$txt_msg.=' '.'لذلك لن يمكنك الدخول الى هذه الصفحة ';


?>

<div class="notice d-flex bg-light-warning rounded border-danger border border-dashed p-9">
    <span class="svg-icon svg-icon-2tx svg-icon-warning me-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="black"></rect>
            <rect x="11" y="14" width="7" height="2" rx="1" transform="rotate(-90 11 14)" fill="black"></rect>
            <rect x="11" y="17" width="2" height="2" rx="1" transform="rotate(-90 11 17)" fill="black"></rect>
        </svg>
    </span>
    <div class="d-flex flex-stack flex-grow-1">
        <div class="fw-bold">
            <h4 class="text-danger fw-bolder">ملاحظة!</h4>
            <div class="fs-4 text-dark"><?php echo $txt_msg?>
            </div>
        </div>
    </div>
</div>


<?php } ?>
@endsection
@section('scripts')
<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
function create_table(table_name) {
    $(table_name).DataTable({
        "ordering": true,
        "paging": true,
        bFilter: true,
        responsive: true,
        bInfo: false,
        "iDisplayLength": 25,
        "pageLength": 25,
        language: {
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
        }
    });
}
$(document).ready(function () {
    create_table("#tbl_job_data");
});
function del_order(id,worker_id,request_status_id) {
    swal.fire({
        text: 'هل انت متأكد من الحذف',
        icon: 'warning',
        buttonsStyling: false,
        confirmButtonText: 'تأكيد الحذف',
        showCancelButton: true,
        cancelButtonText: 'الغاء الامر',
        customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: 'btn btn-danger'
        }
    }).then(function(result) {
        if (result.value) {
            $.ajax({
                url: "{{ route('del_order') }}",
                'type': 'POST',
                'dataType': 'json',
                'async': false,
                'data': {
                    id: id,
                     worker_id: worker_id,
                    request_status_id: request_status_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                'success': function(resp) {
                    if (resp.status == false) {
                        document.documentElement.scrollTop = 0;
                        swal.fire('خطأ', resp.message);
                    } else {
                        window.location.href = "{{ route('order.home')}}";
                        swal.fire('تم الحذفبنجاح', resp.message);
                    }

                }
            });
        } else if (result.dismiss === 'cancel') {
            swal.fire('الغاء الامر', 'خطأ');
        }
    });
}
</script>
@endsection
