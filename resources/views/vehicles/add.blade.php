
@extends('layouts.app')

@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري ')
@section('title', isset($vehicle) ? "تعديل بيانات المركبة" : "إضافة مركبة جديدة")

@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif

    <!-- Print all error messages -->
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- تنسيقات CSS إضافية -->
    <style>
        .section-title {
            color: #083da6;
        }
        .form-group{
            text-align: right;
        }
        .section-fieldset {
            padding: 1.4em;
            margin: 0.5em 0;
            border: 1px solid #ddd;
            border-radius: 0.5em;
        }
        .section-legend {
            width: auto;
            padding: 0 10px;
            border-bottom: none;
            font-size: 1.2em;
        }
        .image-preview {
            width: 100px;
            height: auto;
        }
    </style>

    <div class="container mt-5">
        <form action="{{ isset($vehicle) ? route('update_vehicle', $vehicle->id) : route('store_vehicle') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <h2 class="mb-4 section-title">{{ isset($vehicle) ? "تعديل بيانات المركبة" : "إضافة مركبة جديدة" }}</h2>

                <!-- استخراج بالذكاء الاصطناعي -->
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
                <div class="card ai-card mb-4">
                    <div class="card-body py-4">
                        <div class="ai-card-head">
                            <span class="ai-icon-badge"><i class="fa fa-robot"></i></span>
                            <h3 class="ai-card-title fs-6 text-primary">استخراج بالذكاء الاصطناعي</h3>
                            <span class="ai-pill"><i class="fa fa-magic"></i> ذكاء اصطناعي</span>
                        </div>
                        <p class="text-muted fs-8 mb-3">ارفع صورة أو PDF لاستمارة/رخصة السير أو التأمين أو كرت التشغيل ليتم استخراج بياناتها تلقائياً</p>
                        <div class="ai-dropzone" id="ai_vehicle_document_dropzone">
                            <label for="ai_vehicle_document" class="ai-dropzone__label">
                                <i class="fa fa-cloud-upload-alt ai-dropzone__icon"></i>
                                <span class="ai-dropzone__text">
                                    <span class="ai-dropzone__hint">اسحب الملف أو اضغط للاختيار</span>
                                    <span class="ai-dropzone__filename" id="ai_vehicle_document_filename"></span>
                                </span>
                            </label>
                            <input type="file" id="ai_vehicle_document" accept=".pdf,.jpg,.jpeg,.png,.webp" class="form-control form-control-sm ai-dropzone__input">
                            <button type="button" id="ai_vehicle_extract_btn" class="btn btn-sm btn-primary text-nowrap"><i class="fa fa-magic me-1"></i>استخراج</button>
                        </div>
                        <div id="ai_vehicle_extract_status" class="fs-8 text-muted mt-2"></div>
                    </div>
                </div>
                <script>
                (function(){
                    var btn=document.getElementById('ai_vehicle_extract_btn'); if(!btn||btn.dataset.bound) return; btn.dataset.bound=1;
                    btn.addEventListener('click', function(){
                        var f=document.getElementById('ai_vehicle_document'); var st=document.getElementById('ai_vehicle_extract_status');
                        if(!f.files.length){ st.innerHTML='<span class="text-danger">اختر ملف الوثيقة أولاً</span>'; return; }
                        var fd=new FormData(); fd.append('document', f.files[0]); fd.append('_token','{{ csrf_token() }}');
                        st.textContent='جارٍ الاستخراج بالذكاء الاصطناعي...'; btn.disabled=true;
                        fetch('{{ route("vehicles.ai_extract") }}',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
                        .then(function(r){return r.json();}).then(function(res){
                            btn.disabled=false;
                            if(!res.status){ st.innerHTML='<span class="text-danger">'+(res.message_out||'فشل الاستخراج')+'</span>'; return; }
                            var d=res.data;
                            function setv(id,v){ var el=document.getElementById(id); if(el&&v!=null&&v!==''){ el.value=v; } }
                            setv('plate_number', d.plate_number); setv('owner_name', d.owner_name); setv('model', d.model);
                            setv('license_expiry', d.license_expiry); setv('insurance_expiry', d.insurance_expiry); setv('operation_card_expiry', d.operation_card_expiry);
                            st.innerHTML='<span class="text-success">تم الاستخراج ✓ راجع الحقول ثم احفظ</span>';
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
                    enhance({inputId: 'ai_vehicle_document', zoneId: 'ai_vehicle_document_dropzone', nameId: 'ai_vehicle_document_filename', statusIds: ['ai_vehicle_extract_status']});
                })();
                </script>

                <!-- بيانات أساسية -->
                <fieldset class="section-fieldset">
                    <legend class="section-legend section-title">بيانات أساسية</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="owner_name">اسم مالك المركبة:</label>
                            <input type="text" class="form-control" id="owner_name" name="owner_name" value="{{ isset($vehicle) ? $vehicle->owner_name : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="owner_id">رقم الهوية لصاحب المركبة:</label>
                            <input type="text" class="form-control" id="owner_id" name="owner_id" value="{{ isset($vehicle) ? $vehicle->owner_id : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="vehicle_type">نوع المركبة:</label>
                            <input type="text" class="form-control" id="vehicle_type" name="vehicle_type" value="{{ isset($vehicle) ? $vehicle->vehicle_type : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="plate_number">رقم لوحة:</label>
                            <input type="text" class="form-control" id="plate_number" name="plate_number" value="{{ isset($vehicle) ? $vehicle->plate_number : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="serial_number">الرقم التسلسلي:</label>
                            <input type="text" class="form-control" id="serial_number" name="serial_number" value="{{ isset($vehicle) ? $vehicle->serial_number : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="model">الموديل:</label>
                            <input type="text" class="form-control" id="model" name="model" value="{{ isset($vehicle) ? $vehicle->model : '' }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="color">اللون:</label>
                            <input type="text" class="form-control" id="color" name="color" value="{{ isset($vehicle) ? $vehicle->color : '' }}" >
                        </div>
                                        </div>
                                        <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                            <label for="manager_id_v" class="form-label   fs-6 fw-bold text-dark mb-3">قائد المجموعة </label>
                                            <div>
                                                <select class="form-select fw-bold  " data-control="select2" id="manager_id_v"
                                                    name="manager_id" dir="rtl" >
                                                    <option value="">اختر</option>
                                                    @foreach ($managers as $x)
                                                    <option value="{{ $x->manager_id }} "  @if( isset($vehicle) and $vehicle->manager_id == $x->manager_id) selected @endif>{{ $x->manager_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>




                </fieldset>


                <!-- رخصة السير -->
                <fieldset class="section-fieldset">
                    <legend class="section-legend section-title">رخصة السير</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="license_id">رقم الهوية برخصة السير:</label>
                            <input type="text" class="form-control" id="license_id" name="license_id" value="{{ isset($vehicle) ? $vehicle->license_id : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="license_serial">رقم التسلسلي لرخصة السير:</label>
                            <input type="text" class="form-control" id="license_serial" name="license_serial" value="{{ isset($vehicle) ? $vehicle->license_serial : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="license_image">صورة لرخصة السير:</label>
                            @if (isset($vehicle->license_image))
                            <img src="{{ asset('storage/' . str_replace("public","",$vehicle->license_image)) }}" class="image-preview" alt="صورة رخصة السير">

                        @endif
                            <input type="file" class="form-control-file" id="license_image" name="license_image" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="license_expiry">تاريخ انتهاء رخصة السير:</label>
                            <input type="date" class="form-control" id="license_expiry" name="license_expiry" value="{{ isset($vehicle) ? $vehicle->license_expiry : '' }}" >
                        </div>                    </div>
                </fieldset>

                <!-- المركبة في عهدة الموظف -->
                <fieldset class="section-fieldset">
                    <legend class="section-legend section-title">المركبة في عهدة الموظف</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="custodian_name">الاسم:</label>
                            <input type="text" class="form-control" id="custodian_name" name="custodian_name" value="{{ isset($vehicle) ? $vehicle->custodian_name : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="custodian_name">رقم الهوية :</label>
                            <input type="text" class="form-control" id="custodian_id" name="custodian_id" value="{{ isset($vehicle) ? $vehicle->custodian_id : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="custodian_phone">رقم الجوال:</label>
                            <input type="tel" class="form-control" id="custodian_phone" value="{{ isset($vehicle) ? $vehicle->custodian_phone : '' }}" name="custodian_phone" >
                        </div>                    </div>
                </fieldset>

                <!-- تأمين المركبة -->
                <fieldset class="section-fieldset">
                    <legend class="section-legend section-title">تأمين المركبة</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="insurance_company">اسم شركة التأمين:</label>
                            <input type="text" class="form-control" id="insurance_company" name="insurance_company" value="{{ isset($vehicle) ? $vehicle->insurance_company : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="policy_number">رقم بوليصة التأمين:</label>
                            <input type="text" class="form-control" id="policy_number" name="policy_number" value="{{ isset($vehicle) ? $vehicle->policy_number : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="insurance_issue">تاريخ إصدار التأمين:</label>
                            <input type="date" class="form-control" id="insurance_issue" name="insurance_issue" value="{{ isset($vehicle) ? $vehicle->insurance_issue : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="insurance_expiry">تاريخ إنتهاء التأمين:</label>
                            <input type="date" class="form-control" id="insurance_expiry" name="insurance_expiry" value="{{ isset($vehicle) ? $vehicle->insurance_expiry : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="insurance_image"> صورة وثيقة التأمين:</label>
                                                    @if (isset($vehicle->insurance_image))
                            <img src="{{ asset('storage/' . str_replace("public","",$vehicle->insurance_image)) }}" class="image-preview" alt="صورة وثيقة التأمين">

                        @endif
                            <input type="file" class="form-control-file" id="insurance_image" name="insurance_image" >
                        </div>                    </div>
                </fieldset>


                <fieldset class="section-fieldset">
                    <legend class="section-legend section-title"> بطاقة السائق </legend>
                    <div class="form-row">

    <div class="form-group">
        <label for="driver_card_number">رقم بطاقة السائق:</label>
        <input type="text" class="form-control" id="driver_card_number" name="driver_card_number" value="{{ isset($vehicle) ? $vehicle->driver_card_number : '' }}" >
    </div>
    <div class="form-group">
        <label for="driver_name">اسم السائق:</label>
        <input type="text" class="form-control" id="driver_name" name="driver_name" value="{{ isset($vehicle) ? $vehicle->driver_name : '' }}" >
    </div>
    <div class="form-group">
        <label for="driver_id">رقم هوية السائق:</label>
        <input type="text" class="form-control" id="driver_id" name="driver_id" value="{{ isset($vehicle) ? $vehicle->driver_id : '' }}" >
    </div>
    <div class="form-group">
        <label for="driver_license_category">تصنيف بطاقة السائق:</label>
        <select  class="form-control" id="driver_license_category" name="driver_license_category"  >
            @if ( isset($vehicle) and isset($vehicle->driver_license_category)  )
                <option selected value="{{$vehicle->driver_license_category}}">{{$vehicle->driver_license_category}}</option>
            @endif
            <option value="">اختر تصنيف بطاقة السائق</option>
            <option value="بطاقة السائق السنوية">بطاقة السائق السنوية</option>
            <option value="بطاقة السائق الموسمية">بطاقة السائق الموسمية</option>
        <option value="بطاقة السائق المقيّدة">بطاقة السائق المقيّدة</option>
        <option value="بطاقة السائق المؤقتة">بطاقة السائق المؤقتة</option>
    </select>
    </div>
    <div class="form-group">
        <label for="driver_license_image">صورة بطاقة السائق:</label>
        @if (isset($vehicle->driver_license_image))
            <img src="{{ asset('storage/' . str_replace("public", "", $vehicle->driver_license_image)) }}" class="image-preview" alt="صورة بطاقة السائق">
        @endif
        <input type="file" class="form-control-file" id="driver_license_image" name="driver_license_image">
    </div>
    <div class="form-group">
        <label for="driver_license_expiry">تاريخ انتهاء بطاقة السائق:</label>
        <input type="date" class="form-control" id="driver_license_expiry" name="driver_license_expiry" value="{{ isset($vehicle) ? $vehicle->driver_license_expiry : '' }}" >

    </div>
                    </div>
</fieldset>
                <!-- كرت التشغيل -->
                <fieldset class="section-fieldset">
                    <legend class="section-legend section-title">كرت التشغيل</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="operation_card_number">رقم الوثيقة كرت التشغيل:</label>
                            <input type="text" class="form-control" id="operation_card_number" name="operation_card_number" value="{{ isset($vehicle) ? $vehicle->operation_card_number : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="operation_card_issue">تاريخ الإصدار كرت التشغيل:</label>
                            <input type="date" class="form-control" id="operation_card_issue" name="operation_card_issue" value="{{ isset($vehicle) ? $vehicle->operation_card_issue : '' }}"  >
                        </div>



                        <div class="form-group col-md-6">
                            <label for="operation_card_expiry">تاريخ الانتهاء كرت التشغيل:</label>
                            <input type="date" class="form-control" id="operation_card_expiry" name="operation_card_expiry" value="{{ isset($vehicle) ? $vehicle->operation_card_expiry : '' }}"  >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="operation_card_image"> صورة من كرت التشغيل:</label>
                            @if (isset($vehicle->operation_card_image))
                            <img src="{{ asset('storage/' . str_replace("public","",$vehicle->operation_card_image)) }}" width="100%" alt="صورة كرت التشغيل ">

                        @endif
                            <input type="file" class="form-control-file" id="operation_card_image" name="operation_card_image"  >
                        </div>                    </div>
                </fieldset>



                <button type="submit" class="btn btn-primary">{{ isset($vehicle) ? "تحديث" : "إرسال" }}</button>
            </div>
        </form>
    </div>

    <!-- إضافة Bootstrap JS و Popper.js و jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@endsection
