<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
   <div class="py-5">
    <table id="tbl_expense_categoty_data" name="tbl_expense_categoty_data"
    class="table table-row-bordered gy-5">
    <thead style="color:#eff2f7;background-color:#232b51;text-align:center">
        <tr class="fw-semibold fs-6 text-muted">
            <th>#</th>
         <th>التصنيف</th>
         <th>الاجراءات</th>
     </tr>
 </thead>
 <tbody>
     <?php
     if (isset($expense_categoty)) {
         $z = count($expense_categoty);
         if ($z == 0) {
             $expense_categoty_id = "";
             $expense_categoty_name = "";

         }
         if ($z != 0) {
             $i = 1;
             foreach ($expense_categoty as $x) {
                 $expense_categoty_id = $x->expense_categoty_id;
                 $expense_categoty_name = $x->expense_categoty_name;



                 ?>
                 <tr>
                     <td> <?php echo $i ?></td>
                     <td  style="text-align: center;"> <?php echo $expense_categoty_name ?></td>
                     <td><a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_expensecategoty"  style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"
                        data-url= "<?php echo route('dashboard.constant.updexpensecategoty') ?>"  onclick="upd_expensecategoty('<?php echo $expense_categoty_id ?>')">
                             <i class="fa  fa-edit"></i></a>
                             <a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"
                            onclick="del_expensecategoty('<?php echo $expense_categoty_id ?>')"> <i class="fas fa-trash-alt fa-fw"></i></a>

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
    create_table("#tbl_expense_categoty_data");
});

      </script>







