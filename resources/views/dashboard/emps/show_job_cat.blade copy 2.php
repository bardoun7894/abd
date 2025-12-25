




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

                    <div id="kt_docs_jstree_checkable"></div>
                </form>
            </div>
        </div>
    </div>
</div>



<link href="{{asset('assets/plugins/custom/jstree/jstree.bundle.css')}}" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="{{ asset('assets/module/emp_j.js') }}?t={{ config('global.ver.version_all') }}">    </script>
<script src="{{asset('assets/plugins/custom/jstree/jstree.bundle.js')}}"></script>

<script>


$('#kt_docs_jstree_checkable').jstree({
    'plugins': ["wholerow", "checkbox", "types"],
    'core': {
        "themes" : {
            "responsive": false
        },
        'data': [{
                "text": "Same but with checkboxes",
                "children": [{
                    "text": "initially selected",
                    "state": {
                        "selected": true
                    }
                }, {
                    "text": "custom icon",
                    "icon": "fa fa-warning text-danger"
                }, {
                    "text": "initially open",
                    "icon" : "fa fa-folder text-default",
                    "state": {
                        "opened": true
                    },
                    "children": ["Another node"]
                }, {
                    "text": "custom icon",
                    "icon": "fa fa-warning text-waring"
                }, {
                    "text": "disabled node",
                    "icon": "fa fa-check text-success",
                    "state": {
                        "disabled": true
                    }
                }]
            },
            "And wholerow selection"
        ]
    },
    "types" : {
        "default" : {
            "icon" : "fa fa-folder text-warning"
        },
        "file" : {
            "icon" : "fa fa-file  text-warning"
        }
    },
});


    </script>




