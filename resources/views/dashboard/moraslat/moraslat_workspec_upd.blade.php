<?php
if($corr){

?>

<div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
    <label for="worker_id" class="form-label required fs-6 fw-bold text-dark mb-3">اسم العامل</label>
    <div>
        <select class="form-select fw-bolder  worker_id" id="worker_id"
            name="worker_id" dir="rtl" data-placeholder="اسم العامل">
            <option value="">اختر ..</option>
            <option @selected($moraslat->worker_id==$worker->worker_id) value="{{ $worker->worker_id }}">{{ $worker->worker_name}}</option>

        </select>
    </div>
</div>
<?php }  else{?>
    <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
        <label for="worker_id" class="form-label required fs-6 fw-bold text-dark mb-3">اسم العامل</label>
        <div>
            <select class="form-select fw-bolder  worker_id" id="worker_id"
                name="worker_id" dir="rtl" data-placeholder="اسم العامل">
                <option value="">اختر ..</option>

            </select>
        </div>
    </div>

    <?php }?>


    <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
        <label for="moraslat_categoty_id " class="form-label required  fs-6 fw-bold text-dark mb-3">التصنيف </label>
        <div>
            <select class="form-select fw-bold  form-select_u " data-control="select2" id="moraslat_categoty_id" name="moraslat_categoty_id"
                dir="rtl">
                <option value="">اختر ..</option>
                @foreach ($moraslat_categoty as $x)
                    <option @selected($moraslat->moraslat_categoty_id==$x->moraslat_categoty_id) value="{{ $x->moraslat_categoty_id }} ">{{ $x->moraslat_categoty_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
        <label for="user_id" class="form-label required  fs-6 fw-bold text-dark mb-3">توجية الى</label>
        <div>
            <select class="form-select fw-bold form-select_u " data-control="select2" id="user_id" name="user_id" dir="rtl"
                data-placeholder="قائد المحل">
                <option value="">اختر ..</option>
                @foreach ($users as $x)
                    <option @selected($moraslat->user_id==$x->id) value="{{ $x->id }} ">{{ $x->name }}</option>
                @endforeach
            </select>
        </div>
    </div>



<div class=" col-12 col-lg-12 col-md-12 col-sm-12 mb-5"><label for="moraslat_respon"
    class="form-label  fs-6 fw-bold text-dark mb-3">نص المعاملة</label>
<div class="input-group">
    <div class="input-group-prepend"><span class="input-group-text"><i
                class="far fa-clone fa-fw text-dark"></i></span></div><input type="text" name="moraslat_respon"  value="{{$moraslat->moraslat_respon}}"
        id="moraslat_respon" class="form-control fw-bold  text-dark" placeholder="نص المعاملة " autocomplete="off">
</div>
</div>












<div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
    <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">ملاحظات
    </label>
    <textarea name="note" rows="1" class="form-control fw-bold" id="note" placeholder="ملاحظات">{{ $moraslat->note }}</textarea>
</div>






<div class=" col-12 col-lg-6 col-md-12 col-sm-12 mb-5" id="container_file"
name="container_file">
<div class="btn-group" role="group" aria-label="Button group with nested dropdown">
    <a type="button" id="add_file"
        class="btn btn-secondary kt-font-info kt-font-bolder"
        style='border-color:#232b51;'><i class="la la-chain"></i>تحميل أوراق اخرى</a>
</div>
<br />
<?php


$z_attac = count($moraslat_attach);
if ($z_attac == 0) {
$moraslat_attach_id  = "";
$moraslat_id  = "";
$moraslat_attach_name= "";
$moraslat_attach_extension= "";
$moraslat_attach_url= "";
}

if ($z_attac != 0) {
    $i_att = 1;
    foreach ($moraslat_attach as $x) {
$moraslat_attach_id = $x->moraslat_attach_id;
$moraslat_id = $x->moraslat_id;
$moraslat_attach_name= $x->moraslat_attach_name;
$moraslat_attach_extension= $x->moraslat_attach_extension;
$moraslat_attach_url= $x->moraslat_attach_url;


?>
<div class="form-group row repeat_emp_<?php echo $i_att; ?> ">
    <input type="text" name="image_url_emp[]"
        id="image_url_emp_<?php echo $i_att; ?>" value="<?php echo $moraslat_attach_url; ?>"
        class="form-control kt-font-dark kt-font-bolder" style="display:none"
        placeholder="ملف مرفق">


    <input type="text" name="emp_att_id[]" id="emp_att_id_<?php echo $i_att; ?>"
        value="<?php echo $moraslat_attach_id; ?>" class="form-control kt-font-dark kt-font-bolder"
        style="display:none" placeholder="emp_att_id">



    <?php if ($moraslat_attach_id != "") { ?>
    <?php } ?>
    <div class="input-group">

                <div class="form-control ">
                    <input type="file" class="form-control custom-file-input" id="files_<?php echo $i_att; ?>" value="{{ $moraslat_attach_url }}"
                        placeholder="ملف مرفق" name="files[]" multiple>
                </div>


        <div class="input-group-append">
            <a class="btn btn-lg btn-danger remove" style="padding: 0.7rem 1rem;"
                onclick="del_file_multi('{{ $moraslat_attach_id }}','{{ $moraslat_attach_url }}','moraslat_attach','{{ $i_att }}')"

                >
                <span>
                    <i class="la la-minus" style="color:#fff"></i>
                </span>
            </a>
            <a class="btn btn-lg btn-success btnborder" style="padding: 0.7rem 1rem;"
                 href=" {{ $moraslat_attach_url }}" target="_new">
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








<script>
    $(document).ready(function() {
              $(".form-select_u").select2({
                  dropdownParent: $('#view_prim_const_m .modal-content')
              });
  });






  $(".worker_id").select2({
              placeholder: 'اختر',

              language: {
                  searching: function() {
                      return 'بحث';
                  },
                  loadingMore: function() {
                      return "تحميل المزيد.."
                  },
                  errorLoading: function() {
                      return "The results could not be loaded."
                  },
                  inputTooLong: function(e) {
                      var t = e.input.length - e.maximum,
                          n = "Please delete" + t + " character";
                      return t != 1 && (n += "s"), n
                  },
                  inputTooShort: function(e) {
                      var t = e.minimum - e.input.length,
                          n = "Please enter" + t + " or more characters";
                      return n
                  },
                  maximumSelected: function(e) {
                      var t = "You can only select" + e.maximum + " item";
                      return e.maximum != 1 && (t += "s"), t
                  },
                  noResults: function() {
                      return "No results found"
                  },
                  removeAllItems: function() {
                      return "Remove all items"
                  }
              },
              ajax: {
                url: " {{ route('dashboard.general.sel_worker_list') }}",
                  dataType: "json",
                  type: "POST",
                  delay: 250,
                  async: false,
                  casesensitive: false,
                  beforeSend: function() {

                  },
                  complete: function() {

                  },
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  },

                  data: function(params) {
                      return {
                          q: params.term,
                          page: params.page || 1,
                          job: 1,
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
                                  text: item.ItemName+ ' - '+item.item_code,
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
  </script>

