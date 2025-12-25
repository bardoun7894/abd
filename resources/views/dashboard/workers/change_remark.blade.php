<input name="worker_note_id" id="worker_note_id" value="{{ $worker_note->worker_note_id }}" im-insert="true"
data-inputmask="'alias' : 'integer' " type="text" style="display:none"
class="form-control kt-font-dark kt-font-bolder " readonly placeholder="worker_note_id"
aria-describedby="basic-addon1">
<div class="row gx-5 mb-5">
    <div class="separator separator-content border-dark my-10 mb-8"><span
            class="w-150px fw-bold text-danger">بيانات الملاحظات</span></div>
            <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
                <label for="note_type_id"
                    class="form-label  fs-6 fw-bold text-dark mb-3">نوع الملاحظة</label>
                <div>
                    <select class="form-select fw-bold form-select_u " data-control="select2"
                        id="note_type_id" name="note_type_id" dir="rtl" >
                        <option value="">اختر ..</option>
                        @foreach ($note_type as $x)
                            <option @selected($worker_note->note_type_id==$x->note_type_id) value="{{ $x->note_type_id }} ">{{ $x->note_type_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        <div class=" col-12 col-lg-8 col-md-12 col-sm-12 mb-5" id="container_file"
        name="container_file">
        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
            <a type="button" id="add_file"
                class="btn btn-secondary kt-font-info kt-font-bolder"
                style='border-color:#232b51;'><i class="la la-chain"></i>تحميل أوراق اخرى</a>
        </div>
        <br />
        <?php $z_attac = count($noteworker_attach);
        if ($z_attac == 0) {
$note_attach_id  = "";
$note_attach_name= "";
$note_attach_extension= "";
$note_attach_url= "";
        }

        if ($z_attac != 0) {
            $i_att = 1;
            foreach ($noteworker_attach as $x) {
$note_attach_id = $x->note_attach_id;
$note_attach_name= $x->note_attach_name;
$note_attach_extension= $x->note_attach_extension;
$note_attach_url= $x->note_attach_url;
?>
        <div class="form-group row repeat_emp_<?php echo $i_att; ?> ">
            <input type="text" name="image_url_emp[]"
                id="image_url_emp_<?php echo $i_att; ?>" value="<?php echo $note_attach_url; ?>"
                class="form-control kt-font-dark kt-font-bolder" style="display:none"
                placeholder="ملف مرفق">


            <input type="text" name="emp_att_id[]" id="emp_att_id_<?php echo $i_att; ?>"
                value="<?php echo $note_attach_id; ?>" class="form-control kt-font-dark kt-font-bolder"
                style="display:none" placeholder="emp_att_id">

            <?php if ($note_attach_id != "") { ?>
            <?php } ?>
            <div class="input-group">

                        <div class="form-control ">
                            <input type="file" class="form-control custom-file-input" id="files_<?php echo $i_att; ?>" value="{{ $note_attach_url }}"
                                placeholder="ملف مرفق" name="files[]" multiple>
                        </div>
                <div class="input-group-append">
                    <a class="btn btn-lg btn-danger remove" style="padding: 0.7rem 1rem;"
                        onclick="del_file_multi('{{ $note_attach_id }}','{{ $note_attach_url }}','note_attach','{{ $i_att }}')"

                        >
                        <span>
                            <i class="la la-minus" style="color:#fff"></i>
                        </span>
                    </a>
                    <a class="btn btn-lg btn-success btnborder" style="padding: 0.7rem 1rem;"
                         href=" {{ $note_attach_url }}" target="_new">
                        <span>
                            <i class="la  la-cloud-download" style="color:#fff"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
        <?php $i_att++;
} }
else{ ?>
        <div class="form-group row">
            <div class="input-group ">
                <div class="form-control ">
                    <input type="file" class="form-control custom-file-input"
                        placeholder="ملف مرفق" name="files[]" multiple>
                </div>
                <div class="input-group-append" style="padding: 0.7rem 1rem;">
                    <a class="btn btn-lg btn-danger remove" style="padding: 0.7rem 1rem;">
                        <span>
                            <i class="la la-minus" style="color:#fff"></i>
                        </span>
                    </a>
                </div>

            </div>
        </div>

        <?php } ?>

    </div>
        <div class=" col-12 col-lg-12 col-md-12 col-sm-12  mb-5">
            <label for="remark" class="  form-label fs-6 fw-bold text-dark mb-3">الملاحظة
            </label>
            <textarea name="remark" rows="1" class="form-control fw-bold" id="remark" placeholder="الملاحظة">{{ $worker_note->remark }}</textarea>
        </div>
</div>
<div class="text-center mb-0  ">
    <button type="submit" id="kt_docs_submitsss"
        class="btn btn-primary font-weight-bold mr-2" name="submitButton">حفظ
        البيانات</button>
    <div class="overlay-layer bg-dark bg-opacity-5" id='wait_block'
        style="display: none !important">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
<script>
            $('#add_file').on('click', function() {
                var newfield =
                    '<div class="form-group row repeat"><div class="input-group "><div class="form-control custom-file"><input type="file" class="form-control custom-file-input" name="files[]" ></div><div class="input-group-append" style="padding: 0.7rem 1rem;"><a class="btn btn-lg btn-danger remove"  ><span><i class="la la-minus" style="color:#fff"></i></span></a></div></div></div>';
                $('#container_file').append(newfield);
            });
            $(document).on('click', '.remove', function() {
                $(this).parent().parent().parent('div').remove();
            });
            $(document).ready(function() {
                $(".form-select_u").select2({
                    dropdownParent: $('#view_prim_const_m .modal-content')
                });
            });

            function del_file_multi(shop_id, ssnfile_url, type, i) {
                swal.fire({
                    text: 'هل انت متأكد من الحذف',
                    icon: 'warning',
                    buttonsStyling: false,
                    confirmButtonText: 'تأكيد الحذف',
                    showCancelButton: true,
                    cancelButtonText: 'الغاء الامر',
                    customClass: {
                        confirmButton: "btn btn-primary",
                        cancelButton: 'btn btn-danger'
                    }
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('dashboard.shop.delete_file') }}",
                            'type': 'POST',
                            'dataType': 'json',
                            'async': false,
                            'data': {
                                shop_id: shop_id,
                                ssnfile_url: ssnfile_url,
                                type: type,

                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            'success': function(resp) {
                                if (resp.status == false) {
                                    document.documentElement.scrollTop = 0;
                                    swal.fire('خطأ', resp.message);
                                } else {
                                    swal.fire('تم الحف بنجاح', resp.message);
                                }

                            }
                        });
                    } else if (result.dismiss === 'cancel') {
                        swal.fire('الغاء الامر', 'خطأ');
                    }
                });
            }

            function del_file(shop_id, ssnfile_url, type) {
                swal.fire({
                    text: 'هل انت متأكد من الحذف',
                    icon: 'warning',
                    buttonsStyling: false,
                    confirmButtonText: 'تأكيد الحذف',
                    showCancelButton: true,
                    cancelButtonText: 'الغاء الامر',
                    customClass: {
                        confirmButton: "btn btn-primary",
                        cancelButton: 'btn btn-danger'
                    }
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('dashboard.shop.delete_file') }}",
                            'type': 'POST',
                            'dataType': 'json',
                            'async': false,
                            'data': {
                                shop_id: shop_id,
                                ssnfile_url: ssnfile_url,
                                type: type,

                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            'success': function(resp) {
                                if (resp.status == false) {
                                    document.documentElement.scrollTop = 0;
                                    swal.fire('خطأ', resp.message);
                                } else {
                                    swal.fire('تم الحف بنجاح', resp.message);
                                }

                            }
                        });
                    } else if (result.dismiss === 'cancel') {
                        swal.fire('الغاء الامر', 'خطأ');
                    }
                });
            }
        </script>
