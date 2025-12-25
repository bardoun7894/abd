<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }
    </style>
<script type="text/javascript">
    $(document).ready(function() {
        $('[data-inputmask]').inputmask();
        $(".sex,.city,.region,.nation").select2({
            width: 'resolve',
            dropdownParent: $('#view_prim_const_m .modal-content')

        });
    });
    </script>
<style type="text/css">
    .kt-font-info {color: #072a9d !important;}
    .select2-container--default .select2-selection--multiple,.select2-container--default .select2-selection--single {    border: 1px solid #232b51;}
    .form-control {border: 1px solid #232b51;}
    .input-group>.input-group-prepend>.btn,
    .input-group>.input-group-prepend>.input-group-text,
    .input-group>.input-group-append:not(:last-child)>.btn,
    .input-group>.input-group-append:not(:last-child)>.input-group-text,
    .input-group>.input-group-append:last-child>.btn:not(:last-child):not(.dropdown-toggle),
    .input-group>.input-group-append:last-child>.input-group-text:not(:last-child) {    border-color: #232b51;}
    .kt-form.kt-form--label-right .form-group label:not(.kt-checkbox):not(.kt-radio):not(.kt-option) {    text-align: right;    color: #072a9d !important;}
    #customFile .custom-file-input:lang(en)::after {content: "Select file...";}
    #customFile .custom-file-input:lang(en)::before {content: "Click me";}
    .custom-file-input.selected:lang(en)::after {content: "" !important;}
    .custom-file {overflow: hidden;}
    .custom-file-input {white-space: nowrap;}
    .kt-form.kt-form--label-right .form-group label:not(.kt-checkbox):not(.kt-radio):not(.kt-option) {    text-align: right;    color: #072a9d !important;}
    .kt-form.kt-form--label-right .form-group label:not(.kt-checkbox):not(.kt-radio):not(.kt-option) {    text-align: right;}
    .input-group>.custom-file:not(:last-child) .custom-file-label,.input-group>.custom-file:not(:last-child) .custom-file-label::after {    border-top-left-radius: 0;    border-bottom-left-radius: 0;}
    /*.form-group label {font-weight: inherit;color: #050541;}
    .form-group label {font-size: 1rem;font-weight: 400;}*/
    .btn-danger {color: #fff;background-color: #fd397a;border-color: #232b51;color: #ffffff;}
    .custom-control-label::before,.custom-file-label,.custom-select {transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;}
    </style>



    <script type="text/javascript">
    $(document).ready(function() {
        $('#add_file').on('click', function() {
    var newfield ='<div class="form-group row repeat"><div class="input-group mb-3"><div class="custom-file"><input type="file" class="custom-file-input" name="files[]" ><label class="custom-file-label kt-font-primary kt-font-bolder" for="customFile" data-browse="upload"></label></div><div class="input-group-append"><a class="btn btn-sm btn-danger remove"  style="padding: 0.7rem 1rem;"><span><i class="la la-minus" style="color:#fff"></i></span></a></div></div></div>';
            $('#container_file').append(newfield);
        });
        $(document).on('click', '.remove', function() {
            $(this).parent().parent().parent('div').remove();
        });
        $(document).on('change', '.custom-file-input', function() {
            var i = $(this).prev('label').clone();
            var file = this.files[0].name;
            $(this).prev('label').text(file);
            $(this).next('.custom-file-label').addClass("selected").html(file);
        });




    });
    </script>

<script type="text/javascript" src="<?php echo asset('assets/woker_j.js') ?>"></script>

    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                <?php echo "dddddddddddd"?> </h3>
                        </div>
                    </div>


                    <form autocomplete='off' class="kt-form kt-form--label-right"
                    action="{{ route('dashboard.workers.updstore')}}" method="post" id="upd_worker_data"
                        name="upd_worker_data" enctype="multipart/form-data" accept-charset="utf-8">
                        @csrf

                        <div class="alert alert-outline-danger fade show" role="alert" id="errorBox_worker"
                            style="display: none">
                            <div class="alert-icon"><i class="flaticon-questions-circular-button"></i></div>
                            <div class="alert-text" id="displayErrors_worker"></div>
                            <div class="alert-close">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="la la-close"></i></span>
                                </button>
                            </div>
                        </div>



                        <input name="id_val" id="id_val" value="{{$workers->worker_id}}" im-insert="true"
                        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
                        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="id_val"
                        aria-describedby="basic-addon1">


                        <div class="kt-portlet__body">

                        <div class="form-group row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>woker_name</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fa  fa-user kt-font-dark kt-font-bolder"></i></span></div>
                                        <input name="worker_name" type="text" class="form-control kt-font-dark kt-font-bolder" value="{{$workers->worker_name}}"
                                            placeholder="worker_name"
                                            aria-describedby="basic-addon1">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="form-group">
                                <label class="kt-font-info kt-font-bolder">sex</label>
                                    <select class="sex select2" id="sex" name="sex" style="width:100%">
                                        <option value="">اختر</option>
                                            @foreach ( $sexs as  $sex)

                                        <option value="{{ $sex->sex_id}} @selected($workers->sex) ">{{ $sex->sex_name}}</option>
@endforeach
                                    </select>
                                </div>
                            </div>




                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label>mobile</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="la  la-phone kt-font-dark kt-font-bolder"></i></span></div>
                                        <input name="phone" id="phone" type="text"
                                            class="form-control kt-font-dark kt-font-bolder rtlchange" value="{{$workers->phone}}"
                                            placeholder="mobile"
                                            aria-describedby="basic-addon1" data-inputmask="'alias' : 'integer'"
                                            maxlength="9" minlenght="9">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>email</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span
                                                class="input-group-text kt-font-info kt-font-bold">@</span></div>
                                        <input name="email" id="email" type="text"
                                            class="form-control kt-font-dark kt-font-bolder"  value="{{$workers->email}}"
                                            placeholder="email"
                                            aria-describedby="basic-addon1">
                                    </div>
                                </div>
                            </div>







                            <div class="col-lg-12 row" id='change_job' name='change_job'>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>remarks</label>
                                    <textarea class="form-control kt-font-dark kt-font-bolder"
                                        placeholder="remarks" id="remarks"
                                        name="remarks" rows="1">{{$workers->remarks}}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="kt-portlet__foot">
                        <div class="kt-form__actions">
                            <div class="row">
                                <div class="col-lg-9 ml-lg-auto">
                                    <button type="submit" class="btn btn-success"><i
                                            class="fa fa-check"></i>save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>






