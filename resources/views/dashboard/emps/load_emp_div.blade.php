<style type="text/css">
    .datepicker.dropdown-menu {
        z-index: 100 !important;}
    .input-group > .form-control:not(:last-child), .input-group > .custom-select:not(:last-child) {
        text-align: center;}
    .bootstrap-touchspin .input-group-btn-vertical .btn i {
        color: blue;}
    .bootstrap-switch.bootstrap-switch-large .bootstrap-switch-handle-on, .bootstrap-switch.bootstrap-switch-large .bootstrap-switch-handle-off, .bootstrap-switch.bootstrap-switch-large .bootstrap-switch-label {
        padding: 1.15rem 1.65rem;
        font-size: 1rem;
        line-height: 1;}
    .btn-secondary {
        border: 1px solid black;}
    .input-group-text {
        color: black;}
</style>
<script type="text/javascript">
    $(document).ready(function() {
        $(function() {

            $(".job,.role_per").select2({
                width: 'resolve',
                <?php if($desc==2){?>
                dropdownParent: $('#view_prim_const_m .modal-content')
                <?php } ?>

            });
            $('select:not(.normal)').each(function () {
                var job = $('#job').val();

                $(".emp_supervisor").select2({
                    placeholder: 'اختر',
                    allowClear: true,
                    dropdownParent: $(this).parent(),

                    language: {
                        searching: function() {
                            return 'بحث';
                        },
                        loadingMore: function() {
                            return"تحميل المزيد.."
                        },
                        errorLoading: function() {
                            return"The results could not be loaded."
                        },
                        inputTooLong: function(e) {
                            var t = e.input.length - e.maximum,
                                n ="Please delete" + t +" character";
                            return t != 1 && (n +="s"), n
                        },
                        inputTooShort: function(e) {
                            var t = e.minimum - e.input.length,
                                n ="Please enter" + t +" or more characters";
                            return n
                        },
                        maximumSelected: function(e) {
                            var t ="You can only select" + e.maximum +" item";
                            return e.maximum != 1 && (t +="s"), t
                        },
                        noResults: function() {
                            return"No results found"
                        },
                        removeAllItems: function() {
                            return"Remove all items"
                        }
                    },
                    ajax: {
                     url: 'emps/sel_emp_supervisor',
                      //  url: "{{ route('dashboard.emps.sel_emp_supervisor') }}",
                        dataType:"json",
                        type:"POST",
                        delay: 250,
                        async: false,
                        casesensitive: false,
                        beforeSend: function() {
                            load_message();
                        },
                        complete: function() {
                            unload_message();
                        },
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},

                        data: function(params) {
                            return {
                                q: params.term,
                                page: params.page || 1,
                                job:job,
                            };
                        },
                        processResults: function(data, params) {
                            var resData = [];
                            data.forEach(function(value) {
                                resData.push(value);
                            });
                            var page = params.page || 1;

                            return {
                                results: $.map(resData, function(item) {
                                    return {
                                        text: item.ItemName,
                                        id: item.id
                                    };
                                }),
                                pagination: {
                                    more: (page * 50) <= data[0].total_count
                                }
                            };
                        },
                        cache: true,
                        escapeMarkup: function(m) {
                            return m;
                        }
                    },
                });
            });
        });

        $('#perm').multiselect({
            buttonWidth: '100%',
            filterPlaceholder:'بحث',
            nonSelectedText:'اختر',
            nSelectedText: 'اختير',
            allSelectedText:'اختار الكل',
            selectAllText: 'اختار الكل',
            selectAllValue:'الجميع',
            dropupAuto: false ,
            dropUp: false,
            enableClickableOptGroups: true,
            enableCollapsibleOptGroups: true,
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            includeSelectAllOption: true,
            maxHeight: 200,
            clearBtn: true,
        });


    });

</script>
<style type="text/css">
    .select2-container--default .select2-selection--single .select2-selection__arrow,
    .select2-container--default .select2-selection--multiple .select2-selection__arrow {
        display: none;}
    .multiselect-container>li.multiselect-group label {color: red;padding: 3px 8px;    }
    .multiselect-container {width: 100% !important;}
</style>
<?php if($job!=1   ){?>
    <div class="col-lg-6" >
        <div class="form-group">
            <label >مجموعة الصلاحية</label>
            <select class="role_per select2" id="role_per" name="role_per" style="width:100%" >
                <option value="">اختر</option>
                <?php foreach ($serach_role_data_all as $s) { ?>
                    <option  value="<?php echo $s->id ?>"> <?php echo $s->name ?></option>
                <?php } ?>
            </select>

        </div>
    </div>
    <?php if($job!=2  ){?>

        <div class="col-lg-6" >
            <div class="form-group">
                <label >المشرف</label>
                <select class="emp_supervisor  kt-font-info kt-font-bolder" id="emp_supervisor"
                        name="emp_supervisor"
                        style="width: 100%">
                </select>
            </div>
        </div>
    <?php } ?>


<?php } ?>


