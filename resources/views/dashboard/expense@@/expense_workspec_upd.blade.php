<?php
if($corr){

?>

<div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
    <label for="worker_id" class="form-label required fs-6 fw-bold text-dark mb-3">اسم العامل</label>
    <div>
        <select class="form-select fw-bolder  worker_id" id="worker_id" onchange='get_manager_name()'
            name="worker_id" dir="rtl" data-placeholder="اسم العامل">
            <option value="">اختر ..</option>
            <option @selected($expense->worker_id==$worker->worker_id) value="{{ $worker->worker_id }}">{{ $worker->worker_name}}</option>

        </select>
    </div>
</div>
<?php }  else{?>
    <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
        <label for="worker_id" class="form-label required fs-6 fw-bold text-dark mb-3">اسم العامل</label>
        <div>
            <select class="form-select fw-bolder  worker_id" id="worker_id" onchange='get_manager_name()'
                name="worker_id" dir="rtl" data-placeholder="اسم العامل">
                <option value="">اختر ..</option>

            </select>
        </div>
    </div>

    <?php }?>
    <div class=" col-12 col-lg-3 col-md-12 col-sm-12 mb-5"><label for="expense_respon"
        class="form-label  fs-6 fw-bold text-dark mb-3">قائد المجموعة</label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="fas fa-users fa-fw text-dark"></i></span></div><input type="text" name="manager_name" readonly disabled
            id="manager_name" class="form-control fw-bold  text-dark form-control-solid" placeholder="قائد المجموعة " autocomplete="off">
    </div>
    </div>

<div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
    <label for="expense_categoty_id " class="form-label required  fs-6 fw-bold text-dark mb-3">التصنيف </label>
    <div>
        <select class="form-select fw-bold  form-select_u " data-control="select2" id="expense_categoty_id" name="expense_categoty_id"
            dir="rtl">
            <option value="">اختر ..</option>
            @foreach ($expense_categoty as $x)
                <option @selected($expense->expense_categoty_id==$x->expense_categoty_id) value="{{ $x->expense_categoty_id }} ">{{ $x->expense_categoty_name }}</option>
            @endforeach
        </select>
    </div>
</div>


<div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
    <label for="expense_price" class="form-label  fs-6 fw-bold text-dark mb-3">المبلغ
    </label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-id-card fa-fw text-dark"></i></span></div><input type="text" name="expense_price"  value="{{ $expense->expense_price }}"
            id="expense_price" class="form-control fw-bold text-dark text-info " data-inputmask="'alias' : 'decimal'"
            minlenght="1" maxlength="20" placeholder="المبلغ">
    </div>
</div>

<div class=" col-12 col-lg-7 col-md-12 col-sm-12 mb-5"><label for="expense_respon"
    class="form-label  fs-6 fw-bold text-dark mb-3">تفصيل الصرف</label>
<div class="input-group">
    <div class="input-group-prepend"><span class="input-group-text"><i
                class="far fa-clone fa-fw text-dark"></i></span></div><input type="text" name="expense_respon"  value="{{ $expense->expense_respon }}"
        id="expense_respon" class="form-control fw-bold  text-dark" placeholder="تفصيل الصرف " autocomplete="off">
</div>
</div>


















<input name="expensefile_db" id="expensefile_db" value="{{ $expense->expensefile }}"
im-insert="true" type="text" style="display:none"
class="form-control kt-font-dark kt-font-bolder " readonly
placeholder="expensefile_db" aria-describedby="basic-addon1">
<div class=" col-12 col-lg-5 col-md-12 col-sm-12 mb-5">
    <label for="doe" class="form-label  fs-6 fw-bold text-dark mb-3">إرفاق صورة
    للفاتورة</label>
<div class="input-group mb-3">
    @if ($expense->expensefile)
        <a class="btn btn-lg   btn-success  "
            style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
            href=" {{ $expense->expensefile }}">
            <span>
                <i class="la  la-cloud-download" style="color:#fff"></i>
            </span>
        </a>
        <a class="btn btn-lg   btn-danger remove"
            style="padding: 0.7rem 1rem !important;border-radius: 0;"
            onclick="del_file('{{ $expense->expense_id }}','{{ $expense->expensefile }}','expensefile')">
            <span>
                <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
            </span>
        </a>
    @endif
    <input class="form-control custom-file-input" type="file" name='expensefile'
        id='expensefile'>

</div>
</div>











<div class=" col-12 col-lg-12 col-md-12 col-sm-12  mb-5">
    <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">ملاحظات
    </label>
    <textarea name="note" rows="1" class="form-control fw-bold" id="note" placeholder="ملاحظات">{{ $expense->note }}</textarea>
</div>







<script>
    $(document).ready(function() {
              $(".form-select_u").select2({
                  dropdownParent: $('#view_prim_const_m .modal-content')
              });
            });



  function get_manager_name() {
    var  worker_id  = $('#worker_id').val();
if(worker_id!=''){
$.ajax({
url: " {{ route('dashboard.general.sel_worker_manager') }}",
dataType: 'json',
type: 'POST',
async: false,
'data': {
    worker_id: worker_id,
},
headers: {
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
},
beforeSend: function () {
load_message();
},
complete: function () {
unload_message();
},
success: function (resp) {
if (resp.status == 'false') {
$("#errorBox_worker").hide();
DisplayToastrMessage_General("error", resp.message, 'خطأ');
$('#manager_name').val('');
$("#errorBox_expense").show();
        $("#displayErrors_expense").html('');
        $("#errorBox_expense").removeClass("bg-success");
        $("#errorBox_expense").addClass( "bg-danger" );
        $('#displayErrors_expense').append('<p>'+resp.message+'</p');
}
else{
$('#manager_name').val(resp.manager_name);
$("#errorBox_expense").hide();
$("#displayErrors_expense").html('');
    $("#errorBox_expense").removeClass("bg-danger");
    $("#errorBox_expense").addClass("bg-success");
    $('#displayErrors_expense').append('<p>'+resp.message+'</p');
				}
            }
    });
}
else{
$('#manager_name').val('');
}
}

get_manager_name();


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

