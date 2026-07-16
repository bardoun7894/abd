<div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
    <label for="worker_id" class="form-label required fs-6 fw-bold text-dark mb-3">اسم العامل</label>
    <div>
        <select class="form-select fw-bolder  worker_id  " id="worker_id"   onchange='get_manager_name(); get_worker_expense_totals();'
            name="worker_id" dir="rtl" data-placeholder="اسم العامل">
            <option value="">اختر ..</option>
        </select>
    </div>
</div>

<!-- Worker Expense Totals Summary -->
<div class="col-12 mb-5" id="worker_totals_section" style="display: none;">
    <div class="row">
        <div class="col-12 col-lg-4 col-md-4 col-sm-12 mb-3">
            <div class="border border-dashed border-primary text-center rounded p-3 bg-light-primary">
                <span class="fs-6 fw-bold text-dark d-block mb-2">المبلغ الإجمالي المطلوب</span>
                <span class="fs-3 fw-bolder text-primary" id="total_required_display">0.00</span>
                <span class="text-muted fs-7"> ر.س</span>
            </div>
        </div>
        <div class="col-12 col-lg-4 col-md-4 col-sm-12 mb-3">
            <div class="border border-dashed border-success text-center rounded p-3 bg-light-success">
                <span class="fs-6 fw-bold text-dark d-block mb-2">المبلغ المدفوع</span>
                <span class="fs-3 fw-bolder text-success" id="total_paid_display">0.00</span>
                <span class="text-muted fs-7"> ر.س</span>
            </div>
        </div>
        <div class="col-12 col-lg-4 col-md-4 col-sm-12 mb-3">
            <div class="border border-dashed border-danger text-center rounded p-3 bg-light-danger">
                <span class="fs-6 fw-bold text-dark d-block mb-2">المبلغ المتبقي</span>
                <span class="fs-3 fw-bolder text-danger" id="total_remaining_display">0.00</span>
                <span class="text-muted fs-7"> ر.س</span>
            </div>
        </div>
    </div>
</div>
<div class=" col-12 col-lg-3 col-md-12 col-sm-12 mb-5"><label for="expense_respon"
    class="form-label  fs-6 fw-bold text-dark mb-3">قائد المجموعة</label>
<div class="input-group">
    <div class="input-group-prepend"><span class="input-group-text"><i
                class="fas fa-users fa-fw text-dark"></i></span></div><input type="text" name="manager_name" readonly disabled
        id="manager_name" class="form-control fw-bold  text-dark form-control-solid" placeholder="قائد المجموعة " autocomplete="off">
</div>
</div>

<div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
    <label for="expense_categoty_id" class="form-label required  fs-6 fw-bold text-dark mb-3">التصنيف </label>
    <div>
        <select class="form-select fw-bold  " data-control="select2" id="expense_categoty_id" name="expense_categoty_id"
            dir="rtl">
            <option value="">اختر ..</option>
            @foreach ($expense_categoty as $x)
                <option value="{{ $x->expense_categoty_id }} ">{{ $x->expense_categoty_name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class=" col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
    <label for="expense_month_desc" class="form-label required fs-6 fw-bold text-dark mb-3">شهر الدفع
        :</label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-calendar-alt fa-fw text-dark"></i></span></div><input
            type="text" name="expense_month_desc" id="expense_month_desc"
            class="form-control fw-bold  text-info input_date_full_"
            placeholder="شهر الدفع " value="" autocomplete="off">
    </div>
</div>
<div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
    <label for="expense_price" class="form-label required fs-6 fw-bold text-dark mb-3">المبلغ المطلوب
    </label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-id-card fa-fw text-dark"></i></span></div><input type="text" name="expense_price"
            id="expense_price" class="form-control fw-bold text-dark text-info " data-inputmask="'alias' : 'decimal'"
            minlenght="1" maxlength="20" placeholder="المبلغ المطلوب" oninput="calc_expense_price()">
    </div>
</div>
<div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
    <label for="expense_month_pay" class="form-label required fs-6 fw-bold text-dark mb-3">المبلغ المدفوع
    </label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-id-card fa-fw text-dark"></i></span></div><input type="text" name="expense_month_pay"
            id="expense_month_pay" class="form-control fw-bold text-dark text-info " data-inputmask="'alias' : 'decimal'"
            minlenght="1" maxlength="20" placeholder="المبلغ المدفوع" oninput="calc_expense_price()">
    </div>
</div>
<div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
    <label for="expense_month_remain" class="form-label required fs-6 fw-bold text-dark mb-3">المبلغ المتبقي
    </label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-id-card fa-fw text-dark"></i></span></div><input type="text" name="expense_month_remain"
            id="expense_month_remain" class="form-control fw-bold text-dark text-info form-control-solid" data-inputmask="'alias' : 'decimal'"
            minlenght="1" maxlength="20" placeholder="المبلغ المتبقي" readonly>
    </div>
</div>

<div class=" col-12 col-lg-10 col-md-12 col-sm-12 mb-5"><label for="expense_respon"
        class="form-label  fs-6 fw-bold text-dark mb-3">تفصيل الصرف</label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-clone fa-fw text-dark"></i></span></div><input type="text" name="expense_respon"
            id="expense_respon" class="form-control fw-bold  text-dark" placeholder="تفصيل الصرف " autocomplete="off">
    </div>
</div>

















<div class=" col-12 col-lg-5 col-md-12 col-sm-12 mb-5">
    <label for="expensefile" class="form-label  fs-6 fw-bold text-dark mb-3">إرفاق صورة
        للفاتورة</label>
    <input class="form-control custom-file-input" type="file" name='expensefile'>
</div>
<div class=" col-12 col-lg-7 col-md-12 col-sm-12  mb-5">
    <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">ملاحظات
    </label>
    <textarea name="note" rows="1" class="form-control fw-bold" id="note" placeholder="ملاحظات"></textarea>
</div>











<script>
function calc_expense_price() {
    var expense_price_v = parseFloat($('#expense_price').val());
    var expense_month_pay_v = parseFloat($('#expense_month_pay').val());
    var expense_month_remain_v = parseFloat($('#expense_month_remain').val());
    if (isNaN(expense_price_v)) {
        expense_price_v = 0;
        $('#expense_price').val(0);
    }
    if (isNaN(expense_month_pay_v)) {
        expense_month_pay_v = 0;
        $('#expense_month_pay').val(0);
    }
    expense_month_remain_v = expense_price_v - expense_month_pay_v;
    if (!isNaN(expense_month_remain_v)) {
        $('#expense_month_remain').val(expense_month_remain_v);
    }
}

$('.input_date_full_').flatpickr({
    "locale": "ar" ,
    plugins: [
        new monthSelectPlugin({
          shorthand: false,
          dateFormat: "m-Y",
          altFormat: "F Y",
          theme: "light",

        }),
    ],
    onChange: function(selectedDates, dateStr, instance) {
      },
    onClose: function(selectedDates, dateStr, instance){},
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
$("#errorBox_expense").show();
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

// Fetch and display worker expense totals
function get_worker_expense_totals() {
    var worker_id = $('#worker_id').val();
    if(worker_id != '') {
        $.ajax({
            url: "{{ route('dashboard.general.get_worker_expense_totals') }}",
            dataType: 'json',
            type: 'POST',
            async: false,
            data: {
                worker_id: worker_id,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                $('#worker_totals_section').hide();
            },
            success: function (resp) {
                if (resp.status == 'true') {
                    $('#total_required_display').text(resp.total_required);
                    $('#total_paid_display').text(resp.total_paid);
                    $('#total_remaining_display').text(resp.total_remaining);
                    $('#worker_totals_section').fadeIn();
                } else {
                    $('#worker_totals_section').hide();
                }
            },
            error: function() {
                $('#worker_totals_section').hide();
            }
        });
    } else {
        $('#worker_totals_section').hide();
        $('#total_required_display').text('0.00');
        $('#total_paid_display').text('0.00');
        $('#total_remaining_display').text('0.00');
    }
}







    $(".form-select").select2({

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
                        load_message();
                    },
                    complete: function() {
                        unload_message();
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
                                    text: item.ItemName + ' - ' + item.item_code,
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
