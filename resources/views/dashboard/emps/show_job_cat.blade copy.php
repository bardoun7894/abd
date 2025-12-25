<style type="text/css">
.jstree-open>.jstree-anchor>.fa-folder::before {
    color: #EE2D41 !important;
}

.jstree-default .jstree-search {
    font-style: italic;
    color: #2a4eff !important;
    font-weight: 600 !important;
}

.jstree-default .jstree-anchor {
    color: #000;
}

.jstree-default.jstree-checkbox-no-clicked .jstree-clicked {
    background: transparent;
    box-shadow: none;
    color: green;
}

.jstree-icon {
    vertical-align: inherit !important;
}
</style>



<script type="text/javascript">

$(document).ready(function() {

    $('[data-inputmask]').inputmask();

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
            'plugins': ["types", "search", "changed"],
            "core": {
                "themes": {
                    "responsive": false
                },
            },

            "types": {
                "default": {
                    "icon": "fa fa-folder kt-font-warning"
                },
                "file": {
                    "icon": "fa fa-file  kt-font-warning"
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
</script>


<div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="kt-portlet">
                <div class="kt-portlet__head">
                    <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title">
                            <?php echo "سسسسسسسس"?> </h3>
                    </div>
                </div>
                <form autocomplete='off' class="kt-form kt-form--label-right" enctype="multipart/form-data"
                    accept-charset="utf-8">



                    <div class="kt-portlet__body">
                        <div class="form-group row" style="display:none">
                            <label for="example-text-input"
                                class="col-2 col-form-label kt-font-info kt-font-bolder">الوظيفة التى تم
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
                                    <label class="kt-font-info kt-font-bolder">المسميات الوظيفية</label>
                                    <div class="kt-searchbar">
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"
                                                    id="basic-addon1"><svg xmlns="http://www.w3.org/2000/svg"
                                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                                        height="24px" viewBox="0 0 24 24" version="1.1"
                                                        class="kt-svg-icon">
                                                        <g stroke="none" stroke-width="1" fill="none"
                                                            fill-rule="evenodd">
                                                            <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                                                            <path
                                                                d="M14.2928932,16.7071068 C13.9023689,16.3165825 13.9023689,15.6834175 14.2928932,15.2928932 C14.6834175,14.9023689 15.3165825,14.9023689 15.7071068,15.2928932 L19.7071068,19.2928932 C20.0976311,19.6834175 20.0976311,20.3165825 19.7071068,20.7071068 C19.3165825,21.0976311 18.6834175,21.0976311 18.2928932,20.7071068 L14.2928932,16.7071068 Z"
                                                                id="Path-2" fill="#000000" fill-rule="nonzero"
                                                                opacity="0.3"></path>
                                                            <path
                                                                d="M11,16 C13.7614237,16 16,13.7614237 16,11 C16,8.23857625 13.7614237,6 11,6 C8.23857625,6 6,8.23857625 6,11 C6,13.7614237 8.23857625,16 11,16 Z M11,18 C7.13400675,18 4,14.8659932 4,11 C4,7.13400675 7.13400675,4 11,4 C14.8659932,4 18,7.13400675 18,11 C18,14.8659932 14.8659932,18 11,18 Z"
                                                                id="Path" fill="#000000" fill-rule="nonzero"></path>
                                                        </g>
                                                    </svg></span></div>
                                            <input type="text" name="jstree_q" id="jstree_q" class="form-control"
                                                placeholder="بحث" aria-describedby="basic-addon1" data-maxzpsw="0">
                                        </div>
                                    </div>
                                   <div id="jstree">
                                        <ul>
                                            <?php

                                        $selected_tree='{"selected":true,"disabled":true}';
                                        $selected_tree2='{"selected":false,"disabled":true}';
                                        foreach ($get_all_job_dept as $s) { ?>
                                            <li data-jstree='{"opened":true, "disabled" : true}'> <span
                                                    class="kt-font-info"> <?php echo $s->name ?></span>

                                                <ul>
                                                    <?php

//$users = DB::select('select * from users');
$get_spec_supervisor_per = DB::select('SELECT j_c_id,j_c_name_en,j_c_name_ar as name, job_id FROM job_cat where job_dept_id = ?', [$s->job_dept_id]);




                                          //  $get_spec_supervisor_per = $this->emp_m->get_spc_job_cat_tree($s->job_dept_id);
                                            foreach ($get_spec_supervisor_per as $s) { ?>
                                                    <li id="<?php echo $s->j_c_id ?>"
                                                        data-value="<?php echo $s->j_c_id ?>"><?php echo $s->name ?>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </li>
                                            <?php } ?>

                                        </ul>
                                    </div>
                                    <input name="role_per" id="role_per" type="text" class="form-control"
                                        im-insert="true" style="display:none" placeholder="اسم المجموعة"
                                        aria-describedby="basic-addon1">

                                </div>
                            </div>







                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
