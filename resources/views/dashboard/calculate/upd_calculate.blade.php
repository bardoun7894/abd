<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }

    .tox-notifications-container {
        display: none !important;
    }
</style>
<form id="upd_calculate_data" name="upd_calculate_data" class="form" action="{{ route('dashboard.calculate.updstore') }}"
    method="post" enctype="multipart/form-data" autocomplete="off">
    @csrf
    <input name="calculate_id_db" id="calculate_id_db" value="{{ $calculate->calculate_id }}" im-insert="true"
        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="calculate_id_db"
        aria-describedby="basic-addon1">
    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
            <div class="card">
                <div class="card-body ">
                    <div class="alert alert-dismissible   d-flex flex-column flex-sm-row w-100 p-5 mb-6" id="errorBox_calculate"  style="display: none !important">
                        <span class="svg-icon svg-icon-2hx svg-icon-light me-4 mb-5 mb-sm-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path opacity="0.3" d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z" fill="black"></path>
                                <path d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z" fill="black"></path>
                            </svg>
                        </span>
                        <div class="d-flex flex-column text-light pe-0 pe-sm-10" >
                            <span id="displayErrors_calculate" class="mb-2  fw-bolder text-light"></span>
                        </div>
                        <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                            <span class="svg-icon svg-icon-2x svg-icon-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black"></rect>
                                    <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black"></rect>
                                </svg>
                            </span>
                        </button>
                    </div>
                    <div class="alert alert-dismissible bg-success d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                        id="successBox_worker" style="display: none !important">
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
                            <h4 class="mb-2 text-light">نجاح</h4>
                            <span id="displaysuccess_worker"></span>
                        </div>
                        <button type="button"
                            class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                            data-bs-dismiss="alert">
                            <span class="svg-icon svg-icon-2x svg-icon-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16"
                                        height="2" rx="1" transform="rotate(-45 6 17.3137)"
                                        fill="black"></rect>
                                    <rect x="7.41422" y="6" width="16" height="2"
                                        rx="1" transform="rotate(45 7.41422 6)" fill="black"></rect>
                                </svg>
                            </span>
                        </button>
                    </div>
                    <div class="mb-0">
                        <div class="row gx-5 mb-5">
                            <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
                                <label for="shop_id" class="form-label required fs-6 fw-bold text-dark mb-3">اسم المحل</label>
                                <div>
                                    <select class="form-select fw-bolder  shop_id  " id="shop_id" onchange='sel_shop_pay()'
                                        name="shop_id" dir="rtl" data-placeholder="اسم المحل">
                                        <option value="">اختر ..</option>
                                        <option @selected($calculate->shop_id==$shop->shop_id) value="{{ $shop->shop_id }}">{{ $shop->shop_name}}</option>

                                    </select>
                                </div>
                            </div>
                           <div class=" col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="calculate_month_desc" class="form-label required fs-6 fw-bold text-dark mb-3">شهر الدفع
                                    :</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="far fa-calendar-alt fa-fw text-dark"></i></span></div><input
                                        type="text" name="calculate_month_desc" id="calculate_month_desc" value="{{ $calculate->calculate_month_desc }}"
                                        class="form-control fw-bold  text-info input_date_full_"
                                        placeholder="شهر الدفع " value="" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="calculate_month_val" class="form-label required fs-6 fw-bold text-dark mb-3">المبلغ
                                    المطلوب</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                        type="text" name="calculate_month_val" id="calculate_month_val"
                                        class="form-control fw-bold text-dark text-info form-control-solid"
                                        data-inputmask="'alias' : 'decimal'" minlenght="1" maxlength="20" value="{{ $calculate->calculate_month_val }}"
                                        placeholder="المبلغ المطلوب ">
                                </div>
                            </div>
                            <div class=" col-12 col-lg-4 col-md-12 col-sm-12  mb-5">
                                <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">ملاحظة
                                </label>
                                <textarea name="note" class="form-control fw-bold" id="note" placeholder="ملاحظة">{{ $calculate->note }}</textarea>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@section('styles')
    <style>
        .select2-container[dir="rtl"] .select2-selection--single .select2-selection__rendered {
  padding-bottom: 2px;
}
    </style>
<script type="text/javascript"
src="{{ asset('assets/module/calculate_j.js') }}?t={{ config('global.ver.version_all') }}"></script>
<script>
function calc_all_price() {
var calculate_month_val_v= parseFloat($('#calculate_month_val').val());
var calculate_month_pay_v = parseFloat($('#calculate_month_pay').val());
var calculate_month_remain_v = parseFloat($('#calculate_month_remain').val());
if (isNaN(calculate_month_val_v)) {
    calculate_month_val_v = 0;
    $('#calculate_month_val').val(0);
}
if (isNaN(calculate_month_pay_v)) {
    calculate_month_pay_v = 0;
    $('#calculate_month_pay').val(0);
}
calculate_month_remain_v = calculate_month_val_v - calculate_month_pay_v;
if (!isNaN(calculate_month_remain_v)) {
    $('#calculate_month_remain').val(calculate_month_remain_v);
}
}
function sel_shop_pay() {
    let  shop_id  = $('#shop_id').val();
    if(shop_id!=''){
$('#calculate_month_val').val('');
$('#calculate_month_remain').val('');
$.ajax({
url: " {{ route('dashboard.general.sel_shop_pay') }}",
dataType: 'json',
type: 'POST',
async: false,
'data': {
shop_id: shop_id,
},
headers: {
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
},
beforeSend: function () {
chk_calculate('','');
},
complete: function () {
},
success: function (resp) {
if (resp.status == 'false') {
$("#errorBox_worker").hide();
DisplayToastrMessage_General("error", resp.message, 'خطأ');
$('#calculate_month_val').val('');
$('#calculate_month_pay').val('');
$('#calculate_month_remain').val('');
}
else{
$('#calculate_month_val').val(resp.calculate_month_val);
calc_all_price() ;
				}
            }
    });
}
else{
$('#calculate_month_val').val('');
$('#calculate_month_pay').val('');
$('#calculate_month_remain').val('');
}
}
$(document).ready(function() {
    $(".form-select_u").select2({
            dropdownParent: $('#view_prim_const_m .modal-content')

    });
    $('.input_date_full_').flatpickr({
    "locale": "ar" ,

    plugins: [
        new monthSelectPlugin({
          shorthand: false,
          dateFormat: "m-Y",
          altFormat: "F Y",
          theme: "dark"
        }),
    ],
    onChange: function(selectedDates, dateStr, instance) {
        chk_calculate(selectedDates[0].getMonth() + 1,selectedDates[0].getFullYear())
  },
  onClose: function(selectedDates, dateStr, instance){
    },
});
});
function chk_calculate(month,year) {
    let  shop_id  = $('#shop_id').val();
    let  calculate_month_desc  = $('#calculate_month_desc').val();
if(calculate_month_desc!=''){
    let arr = calculate_month_desc.split('-');
    let month_val =  arr[0];
    let year_val  =  arr[1];
var month=month_val;
var year=year_val;
}
if(shop_id!=''){
$.ajax({
url: " {{ route('dashboard.general.chk_calculate') }}",
dataType: 'json',
type: 'POST',
async: false,
'data': {
    month: month,
    year: year,
    shop_id: shop_id,
    calculate_month_desc: calculate_month_desc,
},
headers: {
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
},
beforeSend: function () {
},
complete: function () {
},
success: function (resp) {
if (resp.status == 'false') {
$("#errorBox_worker").hide();
DisplayToastrMessage_General("error", resp.message, 'خطأ');
$('#calculate_month_val').val(resp.calculate_month_val);
$('#payments_month_pay').val('');
$('#payments_month_remain').val('');

$("#errorBox_calculate").show();
        $("#displayErrors_calculate").html('');
        $("#errorBox_calculate").removeClass("bg-success");
        $("#errorBox_calculate").addClass( "bg-danger" );
        $('#displayErrors_calculate').append('<p>'+resp.message+'</p');
}
else{
$('#calculate_month_val').val(resp.calculate_month_val);
$("#errorBox_calculate").show();
$("#displayErrors_calculate").html('');
    $("#errorBox_calculate").removeClass("bg-danger");
    $("#errorBox_calculate").addClass("bg-success");
    $('#displayErrors_calculate').append('<p>'+resp.message+'</p');
				}
            }
    });

}
else{
$('#calculate_month_val').val('');
$('#payments_month_pay').val('');
$('#payments_month_remain').val('');
}
}
$('#add_file').on('click', function() {
            var newfield =
                '<div class="form-group row repeat"><div class="input-group "><div class="form-control custom-file"><input type="file" class="form-control custom-file-input" name="files[]" ></div><div class="input-group-append" style="padding: 0.7rem 1rem;"><a class="btn btn-lg btn-danger remove"  ><span><i class="la la-minus" style="color:#fff"></i></span></a></div></div></div>';
            $('#container_file').append(newfield);
        });
        $(document).on('click', '.remove', function() {
            $(this).parent().parent().parent('div').remove();
        });


        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val();
            if (fileName.length > 23) {
                fileName = fileName.substr(0, 11) + "..." + fileName.substr(-10);
            }
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        $(".shop_id").select2({
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
                url: " {{ route('dashboard.general.sel_shop_list') }}",
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
