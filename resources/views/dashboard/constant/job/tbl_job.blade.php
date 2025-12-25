<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
   <div class="py-5">
    <table id="tbl_job_data" name="tbl_job_data"
    class="table table-row-bordered gy-5">
    <thead style="color:#eff2f7;background-color:#232b51;text-align:center">
        <tr class="fw-semibold fs-6 text-muted">
            <th>#</th>
         <th>المهنة</th>
         <th>الاجراءات</th>
     </tr>
 </thead>
 <tbody>
     <?php
     if (isset($job)) {
         $z = count($job);
         if ($z == 0) {
             $job_id = "";
             $job_name = "";

         }
         if ($z != 0) {
             $i = 1;
             foreach ($job as $x) {
                 $job_id = $x->job_id;
                 $job_name = $x->job_name;



                 ?>
                 <tr>
                     <td> <?php echo $i ?></td>
                     <td  style="text-align: center;"> <?php echo $job_name ?></td>
                     <td><a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_job"  style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"
                        data-url= "<?php echo route('dashboard.constant.updjob') ?>"  onclick="upd_job('<?php echo $job_id ?>')">
                             <i class="fa  fa-edit"></i></a>
                             <a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"
                            onclick="del_job('<?php echo $job_id ?>')"> <i class="fas fa-trash-alt fa-fw"></i></a>

                         </td>
                 </tr>
         <?php $i++;
     }
 }
} ?>
 </tbody>
</table>



    </div>



    <script type="text/javascript">

$(document).ready(function () {
    create_table("#tbl_job_data");
});

      </script>







