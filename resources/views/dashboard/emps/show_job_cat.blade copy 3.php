<link href="{{ asset('assets/plugins/custom/jstree/jstree.bundle.css') }}" rel="stylesheet" type="text/css" />












<form autocomplete='off' class="kt-form kt-form--label-right" enctype="multipart/form-data" accept-charset="utf-8">




    <div class="d-flex flex-column flex-lg-row">


        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">



            <div class="card">
                <div class="card-body ">



                    <div class="mb-0">
                        <div class="row gx-5 mb-5">






            <div class="form-group row" style="display:none">
                <label for="example-text-input" class="col-2 col-form-label kt-font-info kt-font-bolder">الوظيفة التى تم
                    اختيارها</label>
                <div class="col-10">
                    <input disabled class="form-control kt-font-info kt-font-bolder" type="text"
                        value='لم يتم الاختيار' id="job_desc" name="job_desc" data-maxzpsw="0">

                    <input disabled class="form-control kt-font-info kt-font-bolder" type="text"
                        value='لم يتم الاختيار' id="id_val_desc" name="id_val_desc" data-maxzpsw="0">

                </div>
            </div>









            <div class="form-group row">


                <div class="col-lg-12">
                    <div class="form-group ">


                        <div class="kt-searchbar">






                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i
                                            class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                    type="text" name="jstree_q" id="jstree_q"
                                    class="form-control fw-bold  text-info" placeholder="بحث ">
                            </div>










                        </div>
                        <div id="jstree">
                            <ul>
                                <?php

                                        $selected_tree='{"selected":true,"disabled":true}';
                                        $selected_tree2='{"selected":false,"disabled":true}';
                                        foreach ($get_all_job_dept as $s) { ?>
                                <li data-jstree='{"opened":true, "disabled" : true}'> <span class="fw-bold text-dark">
                                        <?php echo $s->name; ?></span>

                                    <ul>
                                        <?php

$get_spec_supervisor_per = DB::select('SELECT j_c_id,j_c_name_en,j_c_name_ar as name, job_id FROM job_cat where job_dept_id = ?', [$s->job_dept_id]);




                                            foreach ($get_spec_supervisor_per as $s) { ?>
                                        <li id="<?php echo $s->j_c_id; ?>" data-value="<?php echo $s->j_c_id; ?>">
                                            <?php echo $s->name; ?>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                                <?php } ?>

                            </ul>
                        </div>
                        <input name="role_per" id="role_per" type="text" class="form-control" im-insert="true"
                            style="display:none" placeholder="اسم المجموعة" aria-describedby="basic-addon1">

                    </div>
                </div>




            </div>

        </div>


    </div>

</div>


            </div>

        </div>
</form>

</div>



</div>



<script type="text/javascript" src="{{ asset('assets/module/emp_j.js') }}?t={{ config('global.ver.version_all') }}">
</script>
<script src="{{ asset('assets/plugins/custom/jstree/jstree.bundle.js') }}"></script>

<script>
    $(document).ready(function() {


        $('#jstree').on("changed.jstree", function(e, data) {
            var i, j, id_val = [],
                r = [];


            for (i = 0, j = data.selected.length; i < j; i++) {
                r.push(data.instance.get_node(data.selected[i]).text);
                id_val.push(data.instance.get_node(data.selected[i]).id);

            }



            nodesOnSelectedPath = [...data.selected.reduce(function(acc, nodeId) {
                var node = data.instance.get_node(nodeId);
                return new Set([...acc, ...node.parents, node.id]);
            }, new Set)];
            $('#role_per').val(nodesOnSelectedPath).trigger('change');
            $('#job_desc').val(r.join(', '));
            $('#id_val_desc').val(id_val.join(', '));


            $("#view_role_m").modal('hide');

        });










        $('#jstree').on("changed.jstree", function(e, data) {
            var i, j, r = [];
            nodesOnSelectedPath = [...data.selected.reduce(function(acc, nodeId) {
                var node = data.instance.get_node(nodeId);
                return new Set([...acc, ...node.parents, node.id]);
            }, new Set)];
            $('#role_per').val(nodesOnSelectedPath).trigger('change');
        });
        $(function() {
            $('#jstree').jstree({
                'plugins': ["wholerow", "checkbox", "types"],
                "core": {
                    "themes": {
                        "responsive": false
                    },
                },

                "types": {
                    "default": {
                        "icon": "fa fa-folder text-warning"
                    },
                    "file": {
                        "icon": "fa fa-file  text-warning"
                    }
                },
            });
            var to = false;
            $('#jstree_q').keyup(function() {
                if (to) {
                    clearTimeout(to);
                }
                to = setTimeout(function() {
                    var v = $('#jstree_q').val();
                    $('#jstree').jstree(true).search(v);
                }, 250);
            });
        });
    });

    $(document).ready(function() {
        $(".form-select_u").select2({
            dropdownParent: $('#view_prim_const_m .modal-content')

        });
    });
</script>
