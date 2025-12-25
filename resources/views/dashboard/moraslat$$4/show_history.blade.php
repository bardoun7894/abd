<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }

    .tox-notifications-container {
        display: none !important;
    }
</style>





<form
    enctype="multipart/form-data" autocomplete="off">
    @csrf




    <!--begin::Layout-->
    <div class="d-flex flex-column flex-lg-row">

        <!--begin::Content-->

        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">


            <div class="card">
                <div class="card-body ">
                           <div class="mb-0">
                            <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
                            <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
                               <div class="py-5">
                                <table id="tbl_job_data" name="tbl_job_data"
                                class="table table-row-bordered gy-5">
                                <thead style="color:#eff2f7;background-color:#232b51;text-align:center">
                                    <tr class="fw-semibold fs-6 text-muted">
                                        <th>#</th>
                                     <th>رقم المعاملة</th>
                                     <th>الموظف</th>
                                     <th>الحالة</th>
                                     <th>السبب</th>
                                     <th>تاريخ</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php
                                 if (isset($moraslat_history)) {
                                     $z = count($moraslat_history);
                                     if ($z == 0) {
                                         $moraslat_history_id = "";
                                         $moraslat_id = "";
                                         $emp_name = "";
                                         $moraslat_status_name = "";
                                         $status_note = "";
                                         $status_dt = "";
                                     }
                                     if ($z != 0) {
                                         $i = 1;
                                         foreach ($moraslat_history as $x) {
                                             $moraslat_history_id = $x->moraslat_history_id;
                                             $moraslat_id = $x->moraslat_id;
                                             $emp_name = $x->emp_name;
                                             $moraslat_status_name = $x->moraslat_status_name;
                                             $status_note = $x->status_note;
                                             $status_dt = $x->status_dt;
                                             ?>
                                             <tr>
                                                 <td> <?php echo $i ?></td>
                                                 <td  style="text-align: center;"> <?php echo $moraslat_id ?></td>
                                                 <td  style="text-align: center;"> <?php echo $emp_name ?></td>
                                                 <td  style="text-align: center;"> <?php echo $moraslat_status_name ?></td>
                                                 <td  style="text-align: center;"> <?php echo $status_note ?></td>
                                                 <td  style="text-align: center;"> <?php echo $status_dt ?></td>

                                             </tr>
                                     <?php $i++;
                                 }
                             }
                            } ?>
                             </tbody>
                            </table>



                                </div>















                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript"
src="{{ asset('assets/module/moraslat_j.js') }}?t={{ config('global.ver.version_all') }}"></script>


<script type="text/javascript">

    $(document).ready(function () {
        create_table("#tbl_job_data");
    });

          </script>
