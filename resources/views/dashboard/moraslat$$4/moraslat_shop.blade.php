<div class="separator separator-content border-dark my-10 mb-8"><span
    class="w-150px fw-bold text-danger">تفاصيل المراسلة</span></div>



<div class="col-12 col-lg-5 col-md-12 col-sm-12 mb-5">
    <label for="shop_id" class="form-label required fs-6 fw-bold text-dark mb-3">اسم المحل</label>
    <div>
        <select class="form-select fw-bolder  shop_id  " id="shop_id" name="shop_id" dir="rtl"
            data-placeholder="اسم المحل">
            <option value="">اختر ..</option>
        </select>
    </div>
</div>

<div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
    <label for="moraslat_categoty_id" class="form-label required  fs-6 fw-bold text-dark mb-3">درجة الأهمية </label>
    <div>
        <select class="form-select fw-bold  " data-control="select2" id="moraslat_categoty_id" name="moraslat_categoty_id"
            dir="rtl">
            <option value="">اختر ..</option>
            @foreach ($moraslat_categoty as $x)
                <option value="{{ $x->moraslat_categoty_id }} ">{{ $x->moraslat_categoty_name }}</option>
            @endforeach
        </select>
    </div>
</div>


<div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
    <label for="user_id" class="form-label required  fs-6 fw-bold text-dark mb-3">توجية الى</label>
    <div>
        <select class="form-select fw-bold  " data-control="select2" id="user_id" name="user_id" dir="rtl"
            data-placeholder="توجية الى">
            <option value="">اختر ..</option>
            @foreach ($users as $x)
                <option value="{{ $x->id }} ">{{ $x->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class=" col-12 col-lg-12 col-md-12 col-sm-12  mb-5">
    <label for="note" class="  form-label required fs-6 fw-bold text-dark mb-3">نص المعاملة
    </label>
    <textarea name="moraslat_respon"  id="moraslat_respon" placeholder="نص المعاملة"></textarea>
</div>








<div class=" col-12 col-lg-5 col-md-12 col-sm-12  mb-5">
    <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">ملاحظات
    </label>
    <textarea name="note" rows="1" class="form-control fw-bold" id="note" placeholder="ملاحظات"></textarea>
</div>





<div class=" col-12 col-lg-7 col-md-12 col-sm-12 mb-5" id="container_file"
name="container_file">
<div class="btn-group" role="group" aria-label="Button group with nested dropdown">
    <a type="button" id="add_file"
        class="btn btn-secondary kt-font-info kt-font-bolder"
        style='border-color:#232b51;'><i class="la la-chain"></i>تحميل أوراق اخرى</a>
</div>
<br />
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
</div>






<script src="{{ asset('assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>


<script>









    $(".form-select").select2({
    });
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



tinymce.init({
    selector: '#moraslat_respon',
    language: 'ar',

    directionality: 'rtl',

    setup: function(editor) {
        editor.on('change', function() {
            tinymce.triggerSave();
        });
    },


    plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
    ],
    toolbar: 'undo redo | formatselect | ' +
        'bold italic backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | help',
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',


});
</script>
