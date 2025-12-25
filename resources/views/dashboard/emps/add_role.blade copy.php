@extends('layouts.app')
@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري ')
@section('title', "$page_title")
@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif


    <div id="user_reg" class="alert alert-danger d-none"></div>
    <form id="save_workers" name="save_workers" class="form" action="{{ route('dashboard.workers.store') }}"
        enctype="multipart/form-data" autocomplete="off" method="POST">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
                <div class="card">
                    <div class="card-body px-1">
                        <div class="alert alert-dismissible   d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                            id="errorBox_worker" style="display: none !important">
                            <span class="svg-icon svg-icon-2hx svg-icon-light me-4 mb-5 mb-sm-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <path opacity="0.3"
                                        d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z"
                                        fill="black"></path>
                                    <path
                                        d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z"
                                        fill="black"></path>
                                </svg>
                            </span>
                            <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                                <span id="displayErrors_worker" class="mb-2  fw-bolder text-light"></span>
                            </div>
                            <button type="button"
                                class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                                data-bs-dismiss="alert">
                                <span class="svg-icon svg-icon-2x svg-icon-light">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none">
                                        <rect opacity="0.5" x="6" y="17.3137" width="16" height="2"
                                            rx="1" transform="rotate(-45 6 17.3137)" fill="black"></rect>
                                        <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                            transform="rotate(45 7.41422 6)" fill="black"></rect>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <div class="mb-0">
                            <div class="row gx-5 mb-5">












                                <div class="separator separator-content border-dark my-10 mb-8"><span
                                        class="w-150px fw-bold text-danger"> شجرة الصلاحيات </span></div>




                                        <div class="kt-searchbar">
                                        <div class=" col-12 col-lg-4 col-md-12 col-sm-12 mb-5"><label for="jstree_q"
                                            class="form-label required fs-6 fw-bold text-dark mb-3">بحث</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-tools fa-fw text-dark"></i></span></div><input
                                                type="text" name="jstree_q" id="jstree_q"
                                                class="form-control fw-bold  text-dark" placeholder="بحث"
                                                value="" autocomplete="off">
                                        </div>
                                    </div>
                                </div>







                                <div id="jstree" >
                                    <ul>
                                        <?php foreach ($get_all_per_controller as $s) { ?>
                                            <li data-jstree='{"opened":true}' >  <?php echo $s->name ?>

                                                <ul>
                                                    <?php



$get_spec_supervisor_per = DB::select('SELECT  id, parent_id, name , is_delete FROM per_function where  parent_id = ? and  is_delete=0 order by order_p asc ', [$s->id]);

//$get_spec_supervisor_per = DB::select('SELECT j_c_id,j_c_name_en,j_c_name_ar as name, job_id FROM job_cat where job_dept_id = ?', [$s->id]);

                                                 //   $get_spec_supervisor_per = $this->General_m->get_spc_per_function($s->id);
                                                    foreach ($get_spec_supervisor_per as $s) {
                                                        ?>
                                                        <li id="<?php echo $s->id ?>" data-value="<?php echo $s->id ?>"><?php echo $s->name ?></li>
    <?php } ?>
                                                </ul>
                                            </li>
<?php } ?>
                                    </ul>
                                </div>

                                <input name="role_per" id="role_per" type="text" class=" fw-bold  text-info"
                                im-insert="true" style="display:none" placeholder="اسم المجموعة"
                                aria-describedby="basic-addon1">













                            </div>
                            <div class=" mb-2 d-flex justify-content ">
                                <button type="submit" id="kt_docs_formvalidation_text_submit"
                                    class="btn btn-primary font-weight-bold mr-2" name="submitButton">حفظ
                                    البيانات</button>
                                &nbsp;&nbsp;
                                <button type="reset" class="btn btn-light font-weight-bold mr-2">تفريغ البيانات</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('styles')
<link href="{{ asset('assets/plugins/custom/jstree/jstree.bundle.css') }}" rel="stylesheet" type="text/css" />
<style type="text/css">
    .jstree-open>.jstree-anchor>.fa-folder::before {
        color: #EE2D41 !important;
    }
    .jstree-default .jstree-search {
        font-style: italic;
        color: #2a4eff !important;
        /* font-weight: bold;*/
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

@endsection
@section('scripts')

<script type="text/javascript" src="{{ asset('assets/module/emp_j.js') }}?t={{ config('global.ver.version_all') }}">
</script>
<script src="{{ asset('assets/plugins/custom/jstree/jstree.bundle.js') }}"></script>

<script>

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
                     'plugins': ["wholerow", "checkbox", "types","search"],
               // 'plugins': ["types", "search", "changed"],

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
        </script>
@endsection
