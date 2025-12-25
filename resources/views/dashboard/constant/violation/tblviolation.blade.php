<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
   <div class="py-5">
    <table id="tbl_violation_data" name="tbl_violation_data"
    class="table table-row-bordered gy-5">
    <thead style="color:#eff2f7;background-color:#232b51;text-align:center">
        <tr class="fw-semibold fs-6 text-muted">
            <th>#</th>
         <th>جهة المخالفة</th>
         <th>الاجراءات</th>
     </tr>
 </thead>
 <tbody>
     <?php
     if (isset($violation)) {
         $z = count($violation);
         if ($z == 0) {
             $violation_side_id = "";
             $violation_side_name = "";
         }
         if ($z != 0) {
             $i = 1;
             foreach ($violation as $x) {
                 $violation_side_id = $x->violation_side_id;
                 $violation_side_name = $x->violation_side_name;
                ?>
                 <tr>
                     <td> <?php echo $i ?></td>
                     <td  style="text-align: center;"> <?php echo $violation_side_name ?></td>
                     <td><a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_violation"  style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"
                        data-url= "<?php echo route('dashboard.constant.updviolation') ?>"  onclick="upd_violation('<?php echo $violation_side_id ?>')">
                             <i class="fa  fa-edit"></i></a>
                             <a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"
                            onclick="del_violation('<?php echo $violation_side_id ?>')"> <i class="fas fa-trash-alt fa-fw"></i></a>

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
    create_table("#tbl_violation_data");
});
</script>







