<div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
    <label for="worker_id" class="form-label required fs-6 fw-bold text-dark mb-3">اسم
        العامل</label>
    <div>
        <select class="form-select fw-bolder  worker_id  " id="worker_id"
            name="worker_id" dir="rtl" data-placeholder="اسم العامل">
            <option value="">اختر ..</option>
        </select>
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



<div class=" col-12 col-lg-5 col-md-12 col-sm-12 mb-5"><label for="expense_respon"
        class="form-label  fs-6 fw-bold text-dark mb-3">تفصيل الصرف</label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-clone fa-fw text-dark"></i></span></div><input type="text" name="expense_respon"
            id="expense_respon" class="form-control fw-bold  text-dark" placeholder="تفصيل الصرف " autocomplete="off">
    </div>
</div>














<div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
    <label for="expense_price" class="form-label  fs-6 fw-bold text-dark mb-3">المبلغ
    </label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i
                    class="far fa-id-card fa-fw text-dark"></i></span></div><input type="text" name="expense_price"
            id="expense_price" class="form-control fw-bold text-dark text-info " data-inputmask="'alias' : 'decimal'"
            minlenght="1" maxlength="20" placeholder="المبلغ">
    </div>
</div>


<div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
    <label for="manager_id" class="form-label   fs-6 fw-bold text-dark mb-3">قائد
        المحل</label>
    <div>
        <select class="form-select fw-bold  " data-control="select2" id="manager_id" name="manager_id" dir="rtl"
            data-placeholder="قائد المحل">
            <option value="">اختر ..</option>
            @foreach ($manager as $x)
                <option value="{{ $x->manager_id }} ">{{ $x->manager_name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class=" col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
    <label for="expensefile" class="form-label  fs-6 fw-bold text-dark mb-3">إرفاق صورة
        للفاتورة</label>
    <input class="form-control custom-file-input" type="file" name='expensefile'>
</div>
<div class=" col-12 col-lg-4 col-md-12 col-sm-12  mb-5">
    <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">ملاحظات
    </label>
    <textarea name="note" rows="1" class="form-control fw-bold" id="note" placeholder="ملاحظات"></textarea>
</div>











<script>
    $(".form-select").select2({
        //placeholder: "Select a state",
        //  allowClear: true,
        //       width: 'resolve',

    });

    $(".worker_id").select2({
                placeholder: 'اختر',
                //  allowClear: true,
                //  dropdownParent: $(this).parent(),

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
</script>
