@php
            $emp_job = Auth()->user()->emp_job;

@endphp
<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }

    .tox-notifications-container {
        display: none !important;
    }
</style>
<form id="upd_file_data" name="upd_file_data" class="form" action="{{ route('dashboard.shop.updfile') }}" method="post"
    enctype="multipart/form-data" autocomplete="off">
    @csrf
    <input name="shop_id" id="shop_id" value="{{ $shop->shop_id }}" im-insert="true"
        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="shop_id"
        aria-describedby="basic-addon1">
    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
            <div class="card">
                <div class="card-body ">
                    <div class="row p-0 mb-0 px-1">
                        <div class="col">
                            <div
                                class="border border-dashed border-dark text-center min-w-125px rounded pt-4 pb-2 my-3">
                                <span class="fs-6 fw-bold text-info ">اسم المحل :</span> <span
                                    class=" fw-bold text-success d-block">{{ $shop->shop_name }}</span>
                            </div>
                        </div>
                        <div class="col">
                            <div
                                class="border border-dashed border-dark text-center min-w-125px  rounded pt-4 pb-2 my-3">
                                <span class="fs-6 fw-bold text-info ">قائد المحل:</span> <span
                                    class=" fw-bold text-success d-block">{{ $shop->manager_name }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-dismissible   d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                        id="errorBox_shop" style="display: none !important">
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
                            <span id="displayErrors_shop" class="mb-2  fw-bolder text-light"></span>
                        </div>
                        <button type="button"
                            class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                            data-bs-dismiss="alert">
                            <span class="svg-icon svg-icon-2x svg-icon-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2"
                                        rx="1" transform="rotate(-45 6 17.3137)" fill="black"></rect>
                                    <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                        transform="rotate(45 7.41422 6)" fill="black"></rect>
                                </svg>
                            </span>
                        </button>
                    </div>
                    <div class="alert alert-dismissible bg-success d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                        id="successBox_shop" style="display: none !important">
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
                            <span id="displaysuccess_shop"></span>
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
                            <style>
                            .ai-card{position:relative;border:1px solid rgba(0,158,247,.18);border-radius:.95rem;background:linear-gradient(180deg,rgba(0,158,247,.06) 0%,rgba(255,255,255,0) 65%);overflow:hidden;transition:box-shadow .2s ease;}
                            .ai-card::before{content:"";position:absolute;inset-inline-start:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,#009ef7,#7239ea);}
                            .ai-card:hover{box-shadow:0 .5rem 1.5rem rgba(0,158,247,.12);}
                            .ai-card-head{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.75rem;}
                            .ai-icon-badge{display:inline-flex;align-items:center;justify-content:center;width:2.35rem;height:2.35rem;border-radius:.65rem;background:linear-gradient(135deg,#009ef7,#7239ea);color:#fff;font-size:1rem;flex:0 0 auto;box-shadow:0 .35rem .85rem rgba(0,158,247,.35);}
                            .ai-card-title{font-weight:700;margin:0;}
                            .ai-pill{display:inline-flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:700;line-height:1;padding:.35rem .6rem;border-radius:50rem;color:#fff;background:linear-gradient(135deg,#009ef7,#7239ea);letter-spacing:.02em;white-space:nowrap;}
                            .ai-dropzone{position:relative;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;border:1.5px dashed rgba(0,158,247,.45);border-radius:.75rem;padding:.85rem 1rem;background:rgba(0,158,247,.04);transition:border-color .15s ease,background-color .15s ease;}
                            .ai-dropzone.is-dragover{border-color:#009ef7;background:rgba(0,158,247,.1);}
                            .ai-dropzone__label{display:flex;align-items:center;gap:.6rem;flex:1 1 220px;min-width:0;cursor:pointer;margin:0;}
                            .ai-dropzone__icon{font-size:1.3rem;color:#009ef7;flex:0 0 auto;}
                            .ai-dropzone__text{display:flex;flex-direction:column;gap:.1rem;min-width:0;}
                            .ai-dropzone__hint{font-weight:600;font-size:.85rem;}
                            .ai-dropzone__filename{font-size:.76rem;color:#7e8299;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
                            .ai-dropzone__filename.has-file{color:#009ef7;font-weight:600;}
                            .ai-dropzone__input{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;}
                            .ai-dropzone .btn{flex:0 0 auto;}
                            .ai-status{font-size:.8rem;min-height:1.1rem;}
                            .ai-status.is-loading{display:inline-flex;align-items:center;gap:.45rem;color:#009ef7;font-weight:600;}
                            .ai-status.is-loading::before{content:"";width:.8rem;height:.8rem;border-radius:50%;border:2px solid currentColor;border-inline-end-color:transparent;animation:ai-spin .7s linear infinite;flex:0 0 auto;}
                            .ai-status .text-success{font-weight:700;}
                            .ai-status .text-danger{font-weight:700;}
                            @keyframes ai-spin{to{transform:rotate(360deg);}}
                            @media (prefers-reduced-motion: reduce){.ai-status.is-loading::before{animation:none;}}
                            </style>
                            <div class="col-12 mb-4">
                                <div class="card ai-card">
                                    <div class="card-body py-4">
                                        <div class="ai-card-head">
                                            <span class="ai-icon-badge"><i class="fa fa-robot"></i></span>
                                            <h3 class="ai-card-title fs-6 text-primary">استخراج بالذكاء الاصطناعي</h3>
                                            <span class="ai-pill"><i class="fa fa-magic"></i> ذكاء اصطناعي</span>
                                        </div>
                                        <p class="text-muted fs-8 mb-3">ارفع صورة أو PDF من السجل التجاري / رخصة البلدية / عقد الإيجار ليتم استخراج بياناته تلقائياً</p>
                                        <div class="ai-dropzone" id="ai_shop_document_dropzone">
                                            <label for="ai_shop_document" class="ai-dropzone__label">
                                                <i class="fa fa-cloud-upload-alt ai-dropzone__icon"></i>
                                                <span class="ai-dropzone__text">
                                                    <span class="ai-dropzone__hint">اسحب الملف أو اضغط للاختيار</span>
                                                    <span class="ai-dropzone__filename" id="ai_shop_document_filename"></span>
                                                </span>
                                            </label>
                                            <input type="file" id="ai_shop_document" accept=".pdf,.jpg,.jpeg,.png,.webp" class="form-control form-control-sm ai-dropzone__input">
                                            <button type="button" id="ai_shop_extract_btn" class="btn btn-sm btn-primary text-nowrap"><i class="fa fa-magic me-1"></i>استخراج</button>
                                        </div>
                                        <div id="ai_shop_extract_status" class="fs-8 text-muted mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <script>
                            (function(){
                                var btn=document.getElementById('ai_shop_extract_btn'); if(!btn||btn.dataset.bound) return; btn.dataset.bound=1;
                                btn.addEventListener('click', function(){
                                    var f=document.getElementById('ai_shop_document'); var st=document.getElementById('ai_shop_extract_status');
                                    if(!f.files.length){ st.innerHTML='<span class="text-danger">اختر ملف المستند أولاً</span>'; return; }
                                    var fd=new FormData(); fd.append('document', f.files[0]); fd.append('_token','{{ csrf_token() }}');
                                    st.textContent='جارٍ الاستخراج بالذكاء الاصطناعي...'; btn.disabled=true;
                                    fetch('{{ route('dashboard.shop.ai_extract') }}',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
                                    .then(function(r){return r.json();}).then(function(res){
                                        btn.disabled=false;
                                        if(!res.status){ st.innerHTML='<span class="text-danger">'+(res.message_out||'فشل الاستخراج')+'</span>'; return; }
                                        var d=res.data;
                                        function setv(id,v){ var el=document.getElementById(id); if(el&&v!=null&&v!==''){ el.value=v; el.dispatchEvent(new Event('change')); } }
                                        var typeLabel='';
                                        if(d.document_type==='commercial_registration'){
                                            typeLabel='السجل التجاري';
                                            setv('comme_no', d.document_number);
                                            setv('comme_sdt', d.issue_date);
                                            setv('comme_edt', d.expiry_date);
                                        } else if(d.document_type==='municipal_license'){
                                            typeLabel='رخصة البلدية';
                                            setv('municip_no', d.document_number);
                                            setv('municip_sdt', d.issue_date);
                                            setv('municip_edt', d.expiry_date);
                                        } else if(d.document_type==='lease'){
                                            typeLabel='عقد الإيجار';
                                            setv('rent_no', d.document_number);
                                            setv('rent_sdt', d.issue_date);
                                            setv('rent_edt', d.expiry_date);
                                            setv('rent_name', d.owner_name);
                                        }
                                        var extra='';
                                        if(d.owner_name && d.document_type!=='lease'){ extra+=' — الاسم: '+d.owner_name; }
                                        if(d.rent_amount!=null && d.rent_amount!==''){ extra+=' — قيمة الإيجار المقترحة: '+d.rent_amount; }
                                        st.innerHTML='<span class="text-success">تم الاستخراج ✓ راجع الحقول ثم احفظ</span>'+(typeLabel?(' — نوع المستند: '+typeLabel):'')+extra;
                                    }).catch(function(){ btn.disabled=false; st.innerHTML='<span class="text-danger">خطأ في الاتصال</span>'; });
                                });
                            })();
                            </script>
                            <script>
                            (function(){
                                function enhance(opts){
                                    var input = opts.inputId ? document.getElementById(opts.inputId) : null;
                                    var zone = opts.zoneId ? document.getElementById(opts.zoneId) : null;
                                    var nameEl = opts.nameId ? document.getElementById(opts.nameId) : null;
                                    if (input && !input.dataset.aiUx) {
                                        input.dataset.aiUx = '1';
                                        input.addEventListener('change', function(){
                                            if (!nameEl) { return; }
                                            if (input.files && input.files.length) {
                                                nameEl.textContent = input.files[0].name;
                                                nameEl.classList.add('has-file');
                                            } else {
                                                nameEl.textContent = '';
                                                nameEl.classList.remove('has-file');
                                            }
                                        });
                                    }
                                    if (zone && input && !zone.dataset.aiUx) {
                                        zone.dataset.aiUx = '1';
                                        ['dragenter', 'dragover'].forEach(function(evt){
                                            zone.addEventListener(evt, function(e){ e.preventDefault(); zone.classList.add('is-dragover'); });
                                        });
                                        ['dragleave', 'drop'].forEach(function(evt){
                                            zone.addEventListener(evt, function(e){ e.preventDefault(); zone.classList.remove('is-dragover'); });
                                        });
                                        zone.addEventListener('drop', function(e){
                                            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
                                                input.files = e.dataTransfer.files;
                                                input.dispatchEvent(new Event('change'));
                                            }
                                        });
                                    }
                                    (opts.statusIds || []).forEach(function(id){
                                        var el = document.getElementById(id);
                                        if (!el || el.dataset.aiWatch) { return; }
                                        el.dataset.aiWatch = '1';
                                        el.classList.add('ai-status');
                                        var mo = new MutationObserver(function(){
                                            var loading = /^جارٍ/.test((el.textContent || '').trim());
                                            el.classList.toggle('is-loading', loading);
                                        });
                                        mo.observe(el, {childList: true, characterData: true, subtree: true});
                                    });
                                }
                                enhance({inputId: 'ai_shop_document', zoneId: 'ai_shop_document_dropzone', nameId: 'ai_shop_document_filename', statusIds: ['ai_shop_extract_status']});
                            })();
                            </script>
                            <div class="separator separator-content border-dark my-10 mb-8"><span
                                    class="w-150px fw-bold text-danger">معلومات السجل التجاري</span></div>
                                    <input name="shop_comme_id" id="shop_comme_id" value="{{ $shop->shop_comme_id }}" im-insert="true"
                                    data-inputmask="'alias' : 'integer' " type="text" style="display:none"
                                    class="form-control kt-font-dark kt-font-bolder " readonly placeholder="shop_comme_id"
                                    aria-describedby="basic-addon1">
                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="comme_sso" class="form-label  fs-6 fw-bold text-dark mb-3">رقم
                                    الموحد</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-passport fa-fw text-dark"></i></span></div><input
                                        type="text" name="comme_sso" id="comme_sso"  value="{{ $shop->comme_sso }}"
                                        class="form-control fw-bold text-dark text-info" minlenght="1"
                                        maxlength="50" placeholder="رقم الموحد">
                                </div>
                            </div>
                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="comme_no" class="form-label  fs-6 fw-bold text-dark mb-3">رقم السجل
                                    التجاري</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-passport fa-fw text-dark"></i></span></div><input
                                        type="text" name="comme_no" id="comme_no" value="{{ $shop->comme_no }}"
                                        class="form-control fw-bold text-dark text-info" minlenght="1"
                                        maxlength="50" placeholder="رقم السجل التجاري">
                                </div>
                            </div>
                            <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-6 fv-row border border-gray-500 ">
                                <label
                                class=" form-label  fs-6 fw-bold text-dark mb-3">تاريخ إصدار السجل</label>
                            <div class="row fv-row fv-plugins-icon-container ">
                                <div class="col-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class=" far far fa-calendar-plus fa-fw text-primary"></i></span></div><input
                                            type="text" name="comme_sdt" id="comme_sdt"  value="{{ $shop->comme_sdt }}"
                                            class="form-control fw-bold  text-dark "
                                            placeholder="ميلادي" value="" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-star-and-crescent fa-fw text-success"></i></span></div><input
                                            type="text" name="comme_sdt_h" id="comme_sdt_h" value="{{ $shop->comme_sdt_h }}"
                                            class="form-control fw-bold  text-dark "
                                            placeholder="هجري" value="" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="fw-small fs-7 text-danger">هجري او ميلادي</div>
                        </div>
                        <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-6 fv-row border border-gray-500 ">
                            <label
                            class=" form-label  fs-6 fw-bold text-dark mb-3">تاريخ انتهاء السجل</label>
                        <div class="row fv-row fv-plugins-icon-container ">
                            <div class="col-6">
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class=" far fa-calendar-plus fa-fw text-primary"></i></span></div><input
                                        type="text" name="comme_edt" id="comme_edt"  value="{{ $shop->comme_edt }}"
                                        class="form-control fw-bold  text-dark "
                                        placeholder="ميلادي" value="" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-star-and-crescent fa-fw text-success"></i></span></div><input
                                        type="text" name="comme_edt_h" id="comme_edt_h" value="{{ $shop->comme_edt_h }}"
                                        class="form-control fw-bold  text-dark "
                                        placeholder="هجري" value="" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="fw-small fs-7 text-danger">هجري او ميلادي</div>
                    </div>
                            <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                                <label for="comme_city"
                                    class="form-label  fs-6 fw-bold text-dark mb-3">المدينة</label>
                                <div>
                                    <select class="form-select fw-bold form-select_u " data-control="select2"
                                        id="comme_city" name="comme_city" dir="rtl" data-placeholder="المدينة">
                                        <option value="">اختر ..</option>
                                        @foreach ($city as $x)
                                            <option @selected($shop->comme_city == $x->city_id) value="{{ $x->city_id }} ">{{ $x->city_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <input name="commefile_db" id="commefile_db" value="{{ $shop->comme_attach_url }}"
                                im-insert="true" type="text" style="display:none"
                                class="form-control kt-font-dark kt-font-bolder " readonly placeholder="commefile_db"
                                aria-describedby="basic-addon1">
                            <div class=" col-12 col-lg-5 col-md-12 col-sm-12 mb-5">
                                <label for="doe" class="form-label  fs-6 fw-bold text-dark mb-3">تحميل
                                    صورة من السجل التجاري :</label>
                                <div class="input-group mb-3">
                                    @if ($shop->comme_attach_url)
                                        <a class="btn btn-lg   btn-success  "
                                            style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
                                            href=" {{ $shop->comme_attach_url }}">
                                            <span>
                                                <i class="la  la-cloud-download" style="color:#fff"></i>
                                            </span>
                                        </a>
                                        @php
                                                     $emp_job = Auth()->user()->emp_job;

if($emp_job==1){
                                        @endphp
                                        <a class="btn btn-lg   btn-danger "
                                            style="padding: 0.7rem 1rem !important;border-radius: 0;"
                                            onclick="del_file('{{ $shop->shop_comme_id }}','{{ $shop->comme_attach_url }}','commefile')">
                                            <span>
                                                <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
                                            </span>
                                        </a>
                                        @php
                                    }
                                        @endphp



                                    @endif
                                    <input class="form-control custom-file-input" type="file" name='commefile'
                                        id='commefile'>

                                </div>
                            </div>

                            <div class=" col-12 col-lg-4 col-md-12 col-sm-12  mb-5">
                                <label for="comme_note" class="  form-label fs-6 fw-bold text-dark mb-3">الملاحظة
                                </label>
                                <textarea name="comme_note" rows="1" class="form-control fw-bold" id="comme_note" placeholder="الملاحظة">{{ $shop->comme_note }}</textarea>
                            </div>
                            <div class="separator separator-content border-dark my-10 mb-8"><span
                                class="w-150px fw-bold text-danger">معلومات رخصة البلدية</span></div>
                                <input name="shop_municip_id" id="shop_municip_id" value="{{ $shop->shop_municip_id }}" im-insert="true"
                                data-inputmask="'alias' : 'integer' " type="text" style="display:none"
                                class="form-control kt-font-dark kt-font-bolder " readonly placeholder="shop_municip_id"
                                aria-describedby="basic-addon1">
                        <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                            <label for="municip_no" class="form-label  fs-6 fw-bold text-dark mb-3">رقم الرخصة</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i
                                            class="fas fa-passport fa-fw text-dark"></i></span></div><input
                                    type="text" name="municip_no" id="municip_no" value="{{ $shop->municip_no }}"
                                    class="form-control fw-bold text-dark text-info" minlenght="1"
                                    maxlength="50" placeholder="رقم الرخصة">
                            </div>
                        </div>
                        <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                            <label for="municip_width" class="form-label  fs-6 fw-bold text-dark mb-3">مساحة المحل</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i
                                            class="fas fa-square-root-alt fa-fw text-dark"></i></span></div><input
                                    type="text" name="municip_width" id="municip_width" value="{{ $shop->municip_width }}"
                                    class="form-control fw-bold text-dark text-info" minlenght="1"
                                    maxlength="50" placeholder="مساحة المحل">
                            </div>
                        </div>
                        <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-6 fv-row border border-gray-500 ">
                            <label
                            class=" form-label  fs-6 fw-bold text-dark mb-3">تاريخ إصدار الرخصة</label>
                        <div class="row fv-row fv-plugins-icon-container ">
                            <div class="col-6">
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class=" far fa-calendar-plus fa-fw text-primary"></i></span></div><input
                                        type="text" name="municip_sdt" id="municip_sdt"  value="{{ $shop->municip_sdt }}"
                                        class="form-control fw-bold  text-dark "
                                        placeholder="ميلادي" value="" autocomplete="off">
                                </div>

                            </div>
                            <div class="col-6">
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-star-and-crescent fa-fw text-success"></i></span></div><input
                                        type="text" name="municip_sdt_h" id="municip_sdt_h" value="{{ $shop->municip_sdt_h }}"
                                        class="form-control fw-bold  text-dark "
                                        placeholder="هجري" value="" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="fw-small fs-7 text-danger">هجري او ميلادي</div>
                    </div>
                    <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-6 fv-row border border-gray-500 ">
                        <label
                        class=" form-label  fs-6 fw-bold text-dark mb-3">تاريخ انتهاء الرخصة</label>
                    <div class="row fv-row fv-plugins-icon-container ">
                        <div class="col-6">
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i
                                            class=" far fa-calendar-plus fa-fw text-primary"></i></span></div><input
                                    type="text" name="municip_edt" id="municip_edt"  value="{{ $shop->municip_edt }}"
                                    class="form-control fw-bold  text-dark "
                                    placeholder="ميلادي" value="" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i
                                            class="fas fa-star-and-crescent fa-fw text-success"></i></span></div><input
                                    type="text" name="municip_edt_h" id="municip_edt_h" value="{{ $shop->municip_edt_h }}"
                                    class="form-control fw-bold  text-dark "
                                    placeholder="هجري" value="" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="fw-small fs-7 text-danger">هجري او ميلادي</div>
                </div>
                        <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                            <label for="municip_city"
                                class="form-label  fs-6 fw-bold text-dark mb-3">المدينة</label>
                            <div>
                                <select class="form-select fw-bold form-select_u " data-control="select2"
                                    id="municip_city" name="municip_city" dir="rtl" data-placeholder="المدينة">
                                    <option value="">اختر ..</option>
                                    @foreach ($city as $x)
                                        <option  @selected($shop->municip_city == $x->city_id) value="{{ $x->city_id }} ">{{ $x->city_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class=" col-12 col-lg-3 col-md-12 col-sm-12 mb-5"><label for="municip_name"
                            class="form-label  fs-6 fw-bold text-dark mb-3">البلدية</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-warehouse fa-fw text-dark"></i></span></div><input
                                type="text" name="municip_name" id="municip_name" value="{{ $shop->municip_name }}"
                                class="form-control fw-bold  text-dark"
                                placeholder="البلدية" autocomplete="off">
                        </div>
                    </div>
                        <div class=" col-12 col-lg-4 col-md-12 col-sm-12 mb-5"><label for="municip_active"
                            class="form-label  fs-6 fw-bold text-dark mb-3">النشاط</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fab fa-creative-commons-sampling fa-fw text-dark"></i></span></div><input
                                type="text" name="municip_active" id="municip_active" value="{{ $shop->municip_active }}"
                                class="form-control fw-bold  text-dark"
                                placeholder="النشاط" autocomplete="off">
                        </div>
                    </div>
                <div class=" col-12 col-lg-3 col-md-12 col-sm-12 mb-5"><label for="municip_region"
                    class="form-label  fs-6 fw-bold text-dark mb-3">الحي</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i
                                class="fas fa-map-pin fa-fw text-dark"></i></span></div><input
                        type="text" name="municip_region" id="municip_region" value="{{ $shop->municip_region }}"
                        class="form-control fw-bold  text-dark"
                        placeholder="الحي" autocomplete="off">
                </div>
            </div>
                        <input name="municipfile_db" id="municipfile_db" value="{{ $shop->municip_attach_url }}"
                            im-insert="true" type="text" style="display:none"
                            class="form-control kt-font-dark kt-font-bolder " readonly placeholder="municipfile_db"
                            aria-describedby="basic-addon1">
                        <div class=" col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
                            <label for="doe" class="form-label  fs-6 fw-bold text-dark mb-3">تحميل
                                صورة من رخصة البلدية :</label>
                            <div class="input-group mb-3">
                                @if ($shop->municip_attach_url)
                                    <a class="btn btn-lg   btn-success  "
                                        style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
                                        href=" {{ $shop->municip_attach_url }}">
                                        <span>
                                            <i class="la  la-cloud-download" style="color:#fff"></i>
                                        </span>
                                    </a>
                                    @php
                                    $emp_job = Auth()->user()->emp_job;

if($emp_job==1){
                       @endphp
                                    <a class="btn btn-lg   btn-danger "
                                        style="padding: 0.7rem 1rem !important;border-radius: 0;"
                                        onclick="del_file('{{ $shop->shop_municip_id }}','{{ $shop->municip_attach_url }}','municipfile')">
                                        <span>
                                            <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
                                        </span>
                                    </a>
                                    @php
                                    }
                                        @endphp

                                @endif
                                <input class="form-control custom-file-input" type="file" name='municipfile'
                                    id='municipfile'>

                            </div>
                        </div>

                        <div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
                            <label for="municip_note" class="  form-label fs-6 fw-bold text-dark mb-3">الملاحظة
                            </label>
                            <textarea name="municip_note" rows="1" class="form-control fw-bold" id="municip_note" placeholder="الملاحظة">{{ $shop->municip_note }}</textarea>
                        </div>



                        <div class="separator separator-content border-dark my-10 mb-8"><span
                            class="w-150px fw-bold text-danger">معلومات الإيجار</span></div>
                            <input name="shop_rent_id" id="shop_rent_id" value="{{ $shop->shop_rent_id }}" im-insert="true"
                            data-inputmask="'alias' : 'integer' " type="text" style="display:none"
                            class="form-control kt-font-dark kt-font-bolder " readonly placeholder="shop_rent_id"
                            aria-describedby="basic-addon1">

                    <div class="col-12 col-lg-3 col-md-12 col-sm-12 mb-5">
                        <label for="rent_no" class="form-label  fs-6 fw-bold text-dark mb-3">رقم العقد </label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-passport fa-fw text-dark"></i></span></div><input
                                type="text" name="rent_no" id="rent_no"
                                class="form-control fw-bold text-dark text-info" minlenght="1" value="{{ $shop->rent_no }}"
                                maxlength="50" placeholder="رقم العقد ">
                        </div>
                    </div>





                <div class="col-12 col-lg-4 col-md-12 col-sm-12 me-7 mb-5  fv-row border border-gray-500 ">
                    <label
                    class=" form-label  fs-6 fw-bold text-dark mb-3">تاريخ بداية العقد</label>
                <div class="row fv-row fv-plugins-icon-container ">


                    <div class="col-6">
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class=" far fa-calendar-plus fa-fw text-primary"></i></span></div><input
                                type="text" name="rent_sdt" id="rent_sdt"  value="{{ $shop->rent_sdt }}"
                                class="form-control fw-bold  text-dark "
                                placeholder="ميلادي" value="" autocomplete="off">
                        </div>

                    </div>


                    <div class="col-6">
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-star-and-crescent fa-fw text-success"></i></span></div><input
                                type="text" name="rent_sdt_h" id="rent_sdt_h" value="{{ $shop->rent_sdt_h }}"
                                class="form-control fw-bold  text-dark "
                                placeholder="هجري" value="" autocomplete="off">
                        </div>
                    </div>



                </div>
                <div class="fw-small fs-7 text-danger">هجري او ميلادي</div>
            </div>












            <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-5 fv-row border border-gray-500 ">
                <label
                class=" form-label  fs-6 fw-bold text-dark mb-3">تاريخ نهاية العقد</label>
            <div class="row fv-row fv-plugins-icon-container ">


                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class=" far fa-calendar-plus fa-fw text-primary"></i></span></div><input
                            type="text" name="rent_edt" id="rent_edt"  value="{{ $shop->rent_edt }}"
                            class="form-control fw-bold  text-dark "
                            placeholder="ميلادي" value="" autocomplete="off">
                    </div>

                </div>


                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-star-and-crescent fa-fw text-success"></i></span></div><input
                            type="text" name="rent_edt_h" id="rent_edt_h" value="{{ $shop->rent_edt_h }}"
                            class="form-control fw-bold  text-dark "
                            placeholder="هجري" value="" autocomplete="off">
                    </div>
                </div>



            </div>
            <div class="fw-small fs-7 text-danger">هجري او ميلادي</div>
        </div>


        <div class=" col-12 col-lg-3 col-md-12 col-sm-12 mb-5"><label for="rent_name"
            class="form-label  fs-6 fw-bold text-dark mb-3">اسم المؤجر</label>
        <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text"><i
                        class="fas fa-tools fa-fw text-dark"></i></span></div><input
                type="text" name="rent_name" id="rent_name" value="{{ $shop->rent_name }}"
                class="form-control fw-bold  text-dark"
                placeholder="اسم المؤجر" autocomplete="off">
        </div>
    </div>


    <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
        <label for="rent_mobile" class="form-label  fs-6 fw-bold text-dark mb-3">رقم هاتف
            </label>
        <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text"><i
                        class="fas fa-phone-volume fa-fw text-dark"></i></span></div><input
                type="text" name="rent_mobile" id="rent_mobile" value="{{ $shop->rent_mobile }}"
                class="form-control fw-bold text-dark text-info" minlenght="1"
                maxlength="20" placeholder="رقم هاتف">
        </div>
    </div>


                    <input name="rentfile_db" id="rentfile_db" value="{{ $shop->rent_attach_url }}"
                        im-insert="true" type="text" style="display:none"
                        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="rentfile_db"
                        aria-describedby="basic-addon1">
                    <div class=" col-12 col-lg-4 col-md-12 col-sm-12 mb-5">
                        <label for="doe" class="form-label  fs-6 fw-bold text-dark mb-3">تحميل
                            صورة العقد :</label>
                        <div class="input-group mb-3">
                            @if ($shop->rent_attach_url)
                                <a class="btn btn-lg   btn-success  "
                                    style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
                                    href=" {{ $shop->rent_attach_url }}">
                                    <span>
                                        <i class="la  la-cloud-download" style="color:#fff"></i>
                                    </span>
                                </a>
                                @php
                                $emp_job = Auth()->user()->emp_job;

if($emp_job==1){
                   @endphp
                                <a class="btn btn-lg   btn-danger "
                                    style="padding: 0.7rem 1rem !important;border-radius: 0;"
                                    onclick="del_file('{{ $shop->shop_rent_id }}','{{ $shop->rent_attach_url }}','rentfile')">
                                    <span>
                                        <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
                                    </span>
                                </a>
                                @php
                            }
                                @endphp

                            @endif
                            <input class="form-control custom-file-input" type="file" name='rentfile'
                                id='rentfile'>

                        </div>
                    </div>

                    <div class=" col-12 col-lg-3 col-md-12 col-sm-12  mb-5">
                        <label for="rent_note" class="  form-label fs-6 fw-bold text-dark mb-3">الملاحظة
                        </label>
                        <textarea name="rent_note" rows="1" class="form-control fw-bold" id="rent_note" placeholder="الملاحظة">{{ $shop->rent_note }}</textarea>
                    </div>











                    <div class="separator separator-content border-dark my-10 mb-8"><span
                        class="w-150px fw-bold text-danger">رخصة الدفاع المدني</span></div>
                        <input name="shop_defence_id" id="shop_defence_id" value="{{ $shop->shop_defence_id }}" im-insert="true"
                        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
                        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="shop_defence_id"
                        aria-describedby="basic-addon1">


                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                    <label for="defence_no" class="form-label  fs-6 fw-bold text-dark mb-3">رقم الرخصة</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-passport fa-fw text-dark"></i></span></div><input
                            type="text" name="defence_no" id="defence_no" value="{{ $shop->defence_no }}"
                            class="form-control fw-bold text-dark text-info" minlenght="1"
                            maxlength="50" placeholder="رقم الرخصة">
                    </div>
                </div>



                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                    <label for="defence_city"
                        class="form-label  fs-6 fw-bold text-dark mb-3">المدينة</label>
                    <div>
                        <select class="form-select fw-bold form-select_u " data-control="select2"
                            id="defence_city" name="defence_city" dir="rtl" data-placeholder="المدينة">
                            <option value="">اختر ..</option>
                            @foreach ($city as $x)
                                <option  @selected($shop->defence_city == $x->city_id) value="{{ $x->city_id }} ">{{ $x->city_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>








                <div class="col-12 col-lg-4 col-md-12 col-sm-12  mb-6 fv-row border border-gray-500 ">
                    <label
                    class=" form-label  fs-6 fw-bold text-dark mb-3">تاريخ إصدار الرخصة</label>
                <div class="row fv-row fv-plugins-icon-container ">


                    <div class="col-6">
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class=" far fa-calendar-plus fa-fw text-primary"></i></span></div><input
                                type="text" name="defence_sdt" id="defence_sdt"  value="{{ $shop->defence_sdt }}"
                                class="form-control fw-bold  text-dark "
                                placeholder="ميلادي" value="" autocomplete="off">
                        </div>

                    </div>


                    <div class="col-6">
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-star-and-crescent fa-fw text-success"></i></span></div><input
                                type="text" name="defence_sdt_h" id="defence_sdt_h" value="{{ $shop->defence_sdt_h }}"
                                class="form-control fw-bold  text-dark "
                                placeholder="هجري" value="" autocomplete="off">
                        </div>
                    </div>



                </div>
                <div class="fw-small fs-7 text-danger">هجري او ميلادي</div>
            </div>





            <div class="col-12 col-lg-4 col-md-12 col-sm-12 mb-6 fv-row border border-gray-500 ">
                <label
                class=" form-label  fs-6 fw-bold text-dark mb-3">تاريخ انتهاء الرخصة</label>
            <div class="row fv-row fv-plugins-icon-container ">


                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class=" far fa-calendar-plus fa-fw text-primary"></i></span></div><input
                            type="text" name="defence_edt" id="defence_edt"  value="{{ $shop->defence_edt }}"
                            class="form-control fw-bold  text-dark "
                            placeholder="ميلادي" value="" autocomplete="off">
                    </div>

                </div>


                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-star-and-crescent fa-fw text-success"></i></span></div><input
                            type="text" name="defence_edt_h" id="defence_edt_h" value="{{ $shop->defence_edt_h }}"
                            class="form-control fw-bold  text-dark "
                            placeholder="هجري" value="" autocomplete="off">
                    </div>
                </div>



            </div>
            <div class="fw-small fs-7 text-danger">هجري او ميلادي</div>
        </div>
























                <input name="defencefile_db" id="defencefile_db" value="{{ $shop->defence_attach_url }}"
                    im-insert="true" type="text" style="display:none"
                    class="form-control kt-font-dark kt-font-bolder " readonly placeholder="defencefile_db"
                    aria-describedby="basic-addon1">
                <div class=" col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
                    <label for="doe" class="form-label  fs-6 fw-bold text-dark mb-3">تحميل
                        صورة من رخصة الدفاع المدني : </label>
                    <div class="input-group mb-3">
                        @if ($shop->defence_attach_url)

                            <a class="btn btn-lg   btn-success  "
                                style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
                                href=" {{ $shop->defence_attach_url }}">
                                <span>
                                    <i class="la  la-cloud-download" style="color:#fff"></i>
                                </span>
                            </a>
                            @php
                            $emp_job = Auth()->user()->emp_job;

if($emp_job==1){
               @endphp
                            <a class="btn btn-lg   btn-danger "
                                style="padding: 0.7rem 1rem !important;border-radius: 0;"
                                onclick="del_file('{{ $shop->shop_defence_id }}','{{ $shop->defence_attach_url }}','defencefile')">
                                <span>
                                    <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
                                </span>
                            </a>

										     @php
                                    }
                                        @endphp

                        @endif
                        <input class="form-control custom-file-input" type="file" name='defencefile'
                            id='defencefile'>

                    </div>
                </div>



                <input name="defencefile_db1" id="defencefile_db1" value="{{ $shop->defence_attach_url1 }}"
                im-insert="true" type="text" style="display:none"
                class="form-control kt-font-dark kt-font-bolder " readonly placeholder="defencefile_db1"
                aria-describedby="basic-addon1">
            <div class=" col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
                <label for="doe" class="form-label  fs-6 fw-bold text-dark mb-3">تحميل
                    صورة من واجهة المحل :</label>
                <div class="input-group mb-3">
                    @if ($shop->defence_attach_url1)
                        <a class="btn btn-lg   btn-success  "
                            style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
                            href=" {{ $shop->defence_attach_url1 }}">
                            <span>
                                <i class="la  la-cloud-download" style="color:#fff"></i>
                            </span>
                        </a>
                        @php
                        $emp_job = Auth()->user()->emp_job;

if($emp_job==1){
           @endphp
                        <a class="btn btn-lg   btn-danger "
                            style="padding: 0.7rem 1rem !important;border-radius: 0;"
                            onclick="del_file('{{ $shop->shop_defence_id }}','{{ $shop->defence_attach_url1 }}','defencefile1')">
                            <span>
                                <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
                            </span>
                        </a>

										     @php
                                            }
                                                @endphp
                    @endif
                    <input class="form-control custom-file-input" type="file" name='defencefile1'
                        id='defencefile1'>

                </div>
            </div>





            <input name="defencefile_db2" id="defencefile_db2" value="{{ $shop->defence_attach_url2 }}"
            im-insert="true" type="text" style="display:none"
            class="form-control kt-font-dark kt-font-bolder " readonly placeholder="defencefile_db2"
            aria-describedby="basic-addon1">
        <div class=" col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
            <label for="doe" class="form-label  fs-6 fw-bold text-dark mb-3">تحميل
                صورة من رخصة البناء :</label>
            <div class="input-group mb-3">
                @if ($shop->defence_attach_url2)
                    <a class="btn btn-lg   btn-success  "
                        style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
                        href=" {{ $shop->defence_attach_url2 }}">
                        <span>
                            <i class="la  la-cloud-download" style="color:#fff"></i>
                        </span>
                    </a>
                    @php
                    $emp_job = Auth()->user()->emp_job;

if($emp_job==1){
       @endphp
                    <a class="btn btn-lg   btn-danger "
                        style="padding: 0.7rem 1rem !important;border-radius: 0;"
                        onclick="del_file('{{ $shop->shop_defence_id }}','{{ $shop->defence_attach_url2 }}','defencefile2')">
                        <span>
                            <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
                        </span>
                    </a>
                    @php
                                    }
                                        @endphp
                @endif
                <input class="form-control custom-file-input" type="file" name='defencefile2'
                    id='defencefile2'>

            </div>
        </div>




        <input name="defencefile_db3" id="defencefile_db3" value="{{ $shop->defence_attach_url3 }}"
        im-insert="true" type="text" style="display:none"
        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="defencefile_db3"
        aria-describedby="basic-addon1">
    <div class=" col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
        <label for="doe" class="form-label  fs-6 fw-bold text-dark mb-3">تحميل
            صورة من موقع المحل كروكي :</label>
        <div class="input-group mb-3">
            @if ($shop->defence_attach_url3)
                <a class="btn btn-lg   btn-success  "
                    style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
                    href=" {{ $shop->defence_attach_url3 }}">
                    <span>
                        <i class="la  la-cloud-download" style="color:#fff"></i>
                    </span>
                </a>
                @php
                $emp_job = Auth()->user()->emp_job;

if($emp_job==1){
   @endphp
                <a class="btn btn-lg   btn-danger "
                    style="padding: 0.7rem 1rem !important;border-radius: 0;"
                    onclick="del_file('{{ $shop->shop_defence_id }}','{{ $shop->defence_attach_url3 }}','defencefile3')">
                    <span>
                        <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
                    </span>
                </a>
                @php
            }
                @endphp
            @endif
            <input class="form-control custom-file-input" type="file" name='defencefile3'
                id='defencefile3'>

        </div>
    </div>
    <input name="defencefile_db4" id="defencefile_db4" value="{{ $shop->defence_attach_url4 }}"
    im-insert="true" type="text" style="display:none"
    class="form-control kt-font-dark kt-font-bolder " readonly placeholder="defencefile_db4"
    aria-describedby="basic-addon1">
<div class=" col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
    <label for="doe" class="form-label  fs-6 fw-bold text-dark mb-3">تحميل صورة من شهادة كفاءة  :</label>
    <div class="input-group mb-3">
        @if ($shop->defence_attach_url4)
            <a class="btn btn-lg   btn-success  "
                style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
                href=" {{ $shop->defence_attach_url4 }}">
                <span>
                    <i class="la  la-cloud-download" style="color:#fff"></i>
                </span>
            </a>
            @php
            $emp_job = Auth()->user()->emp_job;

if($emp_job==1){
@endphp
            <a class="btn btn-lg   btn-danger "
                style="padding: 0.7rem 1rem !important;border-radius: 0;"
                onclick="del_file('{{ $shop->shop_defence_id }}','{{ $shop->defence_attach_url4 }}','defencefile4')">
                <span>
                    <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
                </span>
            </a>

            @php
                                    }
                                        @endphp
        @endif
        <input class="form-control custom-file-input" type="file" name='defencefile4'
            id='defencefile4'>

    </div>
</div>
<input name="defencefile_db5" id="defencefile_db5" value="{{ $shop->defence_attach_url5 }}"
im-insert="true" type="text" style="display:none"
class="form-control kt-font-dark kt-font-bolder " readonly placeholder="defencefile_db5"
aria-describedby="basic-addon1">
<div class=" col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
<label for="doe" class="form-label  fs-6 fw-bold text-dark mb-3">تحميل
    صورة من شاشة الكاميرات :</label>
<div class="input-group mb-3">
    @if ($shop->defence_attach_url5)
        <a class="btn btn-lg   btn-success  "
            style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
            href=" {{ $shop->defence_attach_url5 }}">
            <span>
                <i class="la  la-cloud-download" style="color:#fff"></i>
            </span>
        </a>
        @php
        $emp_job = Auth()->user()->emp_job;

if($emp_job==1){
@endphp
        <a class="btn btn-lg   btn-danger "
            style="padding: 0.7rem 1rem !important;border-radius: 0;"
            onclick="del_file('{{ $shop->shop_defence_id }}','{{ $shop->defence_attach_url5 }}','defencefile5')">
            <span>
                <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
            </span>
        </a>

        @php
    }
        @endphp
    @endif
    <input class="form-control custom-file-input" type="file" name='defencefile5'
        id='defencefile5'>

</div>
</div>
<input name="defencefile_db6" id="defencefile_db6" value="{{ $shop->defence_attach_url6 }}"
im-insert="true" type="text" style="display:none"
class="form-control kt-font-dark kt-font-bolder " readonly placeholder="defencefile_db6"
aria-describedby="basic-addon1">
<div class=" col-12 col-lg-6 col-md-12 col-sm-12 mb-5">
<label for="doe" class="form-label  fs-6 fw-bold text-dark mb-3">تحميل
    صورة من داخل المحل :</label>
<div class="input-group mb-3">
    @if ($shop->defence_attach_url6)
        <a class="btn btn-lg   btn-success  "
            style="padding: 0.7rem 1rem !important;border-radius: 0;" target='_new'
            href=" {{ $shop->defence_attach_url6 }}">
            <span>
                <i class="la  la-cloud-download" style="color:#fff"></i>
            </span>
        </a>
        @php
        $emp_job = Auth()->user()->emp_job;

if($emp_job==1){
@endphp
        <a class="btn btn-lg   btn-danger "
            style="padding: 0.7rem 1rem !important;border-radius: 0;"
            onclick="del_file('{{ $shop->shop_defence_id }}','{{ $shop->defence_attach_url6 }}','defencefile6')">
            <span>
                <i class="fas fa-trash-alt fa-fw " style="color:#fff"></i>
            </span>
        </a>

        @php
                                    }
                                        @endphp
    @endif
    <input class="form-control custom-file-input" type="file" name='defencefile6'
        id='defencefile6'>

</div>
</div>
<div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
    <label for="defence_note" class="  form-label fs-6 fw-bold text-dark mb-3">الملاحظة
    </label>
    <textarea name="defence_note" rows="1" class="form-control fw-bold" id="defence_note" placeholder="الملاحظة">{{ $shop->defence_note }}</textarea>
</div>
                <div class="separator separator-content border-dark my-10 mb-8"><span
                    class="w-150px fw-bold text-danger">ملفات مرفقة اخرى </span></div>
                    <div class=" col-12 col-lg-12 col-md-12 col-sm-12 mb-5" id="container_file"
                            name="container_file">
                            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                <a type="button" id="add_file"
                                    class="btn btn-secondary kt-font-info kt-font-bolder"
                                    style='border-color:#232b51;'><i class="la la-chain"></i>تحميل أوراق اخرى</a>
                            </div>
                            <br />
                            <?php $z_attac = count($shop_attach);
                            if ($z_attac == 0) {
$shop_attach_id  = "";
$shop_id  = "";
$shop_attach_name= "";
$shop_attach_extension= "";
$shop_attach_url= "";
                            }

                            if ($z_attac != 0) {
                                $i_att = 1;
                                foreach ($shop_attach as $x) {
$shop_attach_id = $x->shop_attach_id;
$shop_id = $x->shop_id;
$shop_attach_name= $x->shop_attach_name;
$shop_attach_extension= $x->shop_attach_extension;
$shop_attach_url= $x->shop_attach_url;


?>
                            <div class="form-group row repeat_emp_<?php echo $i_att; ?> ">
                                <input type="text" name="image_url_emp[]"
                                    id="image_url_emp_<?php echo $i_att; ?>" value="<?php echo $shop_attach_url; ?>"
                                    class="form-control kt-font-dark kt-font-bolder" style="display:none"
                                    placeholder="ملف مرفق">


                                <input type="text" name="emp_att_id[]" id="emp_att_id_<?php echo $i_att; ?>"
                                    value="<?php echo $shop_attach_id; ?>" class="form-control kt-font-dark kt-font-bolder"
                                    style="display:none" placeholder="emp_att_id">


                                    <a href="{{ $shop_attach_url }}" target="new" class=" fw-bold mb-1 text-info text-hover-primary">{{ $shop_attach_name }}</a>



                                <?php if ($shop_attach_id != "") { ?>
                                <?php } ?>
                                <div class="input-group">

                                            <div class="form-control ">
                                                <input type="file" class="form-control custom-file-input" id="files_<?php echo $i_att; ?>" value="{{ $shop_attach_url }}"
                                                    placeholder="ملف مرفق" name="files[]" multiple>
                                            </div>
                                    <div class="input-group-append">
                                         @php

if($emp_job==1){
                                    @endphp
                                        <a class="btn btn-lg btn-danger remove" style="padding: 0.7rem 1rem;"
                                            onclick="del_file_multi('{{ $shop_attach_id }}','{{ $shop_attach_url }}','shop_attach','{{ $i_att }}')"

                                            >
                                            <span>
                                                <i class="la la-minus" style="color:#fff"></i>
                                            </span>
                                        </a>


                                         @php
                                        }
                                            @endphp


                                        <a class="btn btn-lg btn-success btnborder" style="padding: 0.7rem 1rem;"
                                        href=" {{ $shop_attach_url }}" target="_new">
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
        .calendars-popup {
    z-index: 100004 !important;
}

.ui-datepicker {
    width: 17em;
    padding: .2em .2em 0;
    display: none;
    z-index: 2000 !important;
}

#ui-datepicker-div {
    z-index: 9999 !important;
}

    </style>
    <script type="text/javascript" src="{{ asset('assets/module/shop_j.js') }}?t={{ config('global.ver.version_all') }}">
    </script>
<script src="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.js') }}"></script>
<script src="{{ asset('assets/js/custom/documentation/forms/formrepeater.bundle.js') }}"></script>
<script src="{{ asset('assets/js/custom/documentation/documentation.js') }}"></script>
<script src="{{ asset('assets/js/custom/documentation/search.js') }}"></script>
<link href="{{asset('assets/um/css/ummalqura.calendars.picker.css')}}" rel="stylesheet" type="text/css" />
<script src="{{asset('assets/um/js/jquery.calendars.js')}}"></script>
<script src="{{asset('assets/um/js/jquery.plugin.js')}}"></script>
<script src="{{asset('assets/um/js/jquery.calendars.plus.js')}}"></script>
<script src="{{asset('assets/um/js/jquery.calendars.picker.js')}}"></script>
<script src="{{asset('assets/um/js/jquery.calendars.picker-ar.js')}}"></script>
<script src="{{asset('assets/um/js/jquery.calendars.ummalqura.js')}}"></script>
<script src="{{asset('assets/um/js/jquery.calendars.ummalqura-ar.js')}}"></script>
<script src="{{asset('assets/um/js/calendar-convert.js')}}"></script>
<script>
        function dt_um(desc_name_g,desc_name_h){
        var calGer = $.calendars.instance('gregorian', 'ar');
    var calHj = $.calendars.instance('ummalqura', 'ar');
    $('#'+desc_name_g).calendarsPicker($.extend({
            calendar: calGer,
            onSelect: function(date) {
                convertDtFromGerToHijri(date, desc_name_h);
                $(this).blur();
            },
            defaultDate: '-1d',
            defaultDate: '-1m',
            dateFormat: 'yyyy-mm-dd',
            localNumbers: true,
        },
        $.calendarsPicker.regionalOptions['ar-EG']));


    $('#'+desc_name_h).calendarsPicker($.extend({
            calendar: calHj,
            onSelect: function(date) {
                convertDtFromHijriToGer(date,desc_name_g);
                $(this).blur();
            },
            onClose: function(date) {
                if ($('#'+desc_name_h).val() == '') {
                    $('#'+desc_name_g).val('');
                }
                $(this).blur();
            },
            altFormat: 'D',
            selectDefaultDate: true,
            altField: '#rtlAlternate_to_v',
            showSpeed: 'normal',
            dateFormat: 'yyyy-mm-dd',
        },
        $.calendarsPicker.regionalOptions['ar']));
    }
    dt_um('comme_sdt','comme_sdt_h');
    dt_um('comme_edt','comme_edt_h');
    dt_um('municip_sdt','municip_sdt_h');
    dt_um('municip_edt','municip_edt_h');
    dt_um('rent_sdt','rent_sdt_h');
    dt_um('rent_edt','rent_edt_h');
    dt_um('defence_sdt','defence_sdt_h');
    dt_um('defence_edt','defence_edt_h');

$("#repeater-button_edu").click(function() {
    setTimeout(function() {

        $(".form-select_u").select2({
        });
        $('.flatpickr_u').flatpickr({
            format: 'dd-mm-yyyy',
            "locale": "ar",
        });

    }, 100);
});
$("#repeater-button").click(function() {
    setTimeout(function() {

        $(".flatpickr_u").select2({
        });

        $('.input_date_u_').flatpickr({
            format: 'dd-mm-yyyy',
            "locale": "ar",
        });

    }, 100);
});


        $('.input_date_').flatpickr({
            format: 'dd-mm-yyyy',
            "locale": "ar",
        });
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
                                swal.fire('تم الحذفبنجاح', resp.message);
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
                                swal.fire('تم الحذفبنجاح', resp.message);
                            }
                        }
                    });
                } else if (result.dismiss === 'cancel') {
                    swal.fire('الغاء الامر', 'خطأ');
                }
            });
        }
    </script>
