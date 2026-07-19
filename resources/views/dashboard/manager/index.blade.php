@extends('layouts.app')
@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري ')
@section('title', "$page_title")
@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif


    <div id="user_reg" class="alert alert-danger d-none"></div>
    <form id="save_manager" name="save_manager" class="form" action="{{ route('dashboard.manager.store') }}"
        enctype="multipart/form-data" autocomplete="off" method="POST">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
                <div class="card">
                    <div class="card-body px-1">
                        <div class="alert alert-dismissible   d-flex flex-column flex-sm-row w-100 p-5 mb-6"
                            id="errorBox_manager" style="display: none !important">
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
                                <span id="displayErrors_manager" class="mb-2  fw-bolder text-light"></span>
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
                        <div class="mb-0">
                            <div class="row gx-5 mb-5">
                                <style>
                                .ai-card{position:relative;border:1px solid rgba(14,107,79,.18);border-radius:.95rem;background:linear-gradient(180deg,rgba(14,107,79,.06) 0%,rgba(255,255,255,0) 65%);overflow:hidden;transition:box-shadow .2s ease;}
                                .ai-card::before{content:"";position:absolute;inset-inline-start:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,#0E6B4F,#0A4F3A);}
                                .ai-card:hover{box-shadow:0 .5rem 1.5rem rgba(14,107,79,.12);}
                                .ai-card-head{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.75rem;}
                                .ai-icon-badge{display:inline-flex;align-items:center;justify-content:center;width:2.35rem;height:2.35rem;border-radius:.65rem;background:linear-gradient(135deg,#0E6B4F,#0A4F3A);color:#fff;font-size:1rem;flex:0 0 auto;box-shadow:0 .35rem .85rem rgba(14,107,79,.35);}
                                .ai-card-title{font-weight:700;margin:0;}
                                .ai-pill{display:inline-flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:700;line-height:1;padding:.35rem .6rem;border-radius:50rem;color:#fff;background:linear-gradient(135deg,#0E6B4F,#0A4F3A);letter-spacing:.02em;white-space:nowrap;}
                                .ai-dropzone{position:relative;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;border:1.5px dashed rgba(14,107,79,.45);border-radius:.75rem;padding:.85rem 1rem;background:rgba(14,107,79,.04);transition:border-color .15s ease,background-color .15s ease;}
                                .ai-dropzone.is-dragover{border-color:#0E6B4F;background:rgba(14,107,79,.1);}
                                /* B2 — a11y: visible focus ring when the drop-zone itself is keyboard-focused (additive). */
                                .ai-dropzone:focus-visible{outline:3px solid #0E6B4F;outline-offset:2px;}
                                .ai-dropzone__label{display:flex;align-items:center;gap:.6rem;flex:1 1 220px;min-width:0;cursor:pointer;margin:0;}
                                .ai-dropzone__icon{font-size:1.3rem;color:#0E6B4F;flex:0 0 auto;}
                                .ai-dropzone__text{display:flex;flex-direction:column;gap:.1rem;min-width:0;}
                                .ai-dropzone__hint{font-weight:600;font-size:.85rem;}
                                .ai-dropzone__filename{font-size:.76rem;color:#7e8299;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
                                .ai-dropzone__filename.has-file{color:#0E6B4F;font-weight:600;}
                                .ai-dropzone__input{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;}
                                .ai-dropzone .btn{flex:0 0 auto;}
                                .ai-status{font-size:.8rem;min-height:1.1rem;}
                                .ai-status.is-loading{display:inline-flex;align-items:center;gap:.45rem;color:#0E6B4F;font-weight:600;}
                                .ai-status.is-loading::before{content:"";width:.8rem;height:.8rem;border-radius:50%;border:2px solid currentColor;border-inline-end-color:transparent;animation:ai-spin .7s linear infinite;flex:0 0 auto;}
                                .ai-status .text-success{font-weight:700;}
                                .ai-status .text-danger{font-weight:700;}
                                @keyframes ai-spin{to{transform:rotate(360deg);}}
                                .ai-low-conf { outline:2px solid #f1b44c !important; outline-offset:1px; background:#fff8ec !important; }
                                .ai-conf-hint { color:#e0a800; font-size:.72rem; margin-top:2px; display:block; }
                                </style>
                                <div class="col-12 mb-4">
                                    <div class="card ai-card">
                                        <div class="card-body py-4">
                                            <div class="ai-card-head">
                                                <span class="ai-icon-badge"><i class="fa fa-robot"></i></span>
                                                <h3 class="ai-card-title fs-6 text-primary">استخراج بالذكاء الاصطناعي</h3>
                                                <span class="ai-pill"><i class="fa fa-magic"></i> ذكاء اصطناعي</span>
                                            </div>
                                            <p class="text-muted fs-8 mb-3">ارفع صورة أو PDF لهوية / إقامة / جواز قائد المجموعة ليتم استخراج بياناته تلقائياً</p>
                                            <div class="ai-dropzone" id="ai_manager_document_dropzone" role="button" tabindex="0" aria-label="منطقة رفع مستند قائد المجموعة — اسحب الملف هنا أو اضغط للاختيار">
                                                <label for="ai_manager_document" class="ai-dropzone__label">
                                                    <i class="fa fa-cloud-upload-alt ai-dropzone__icon"></i>
                                                    <span class="ai-dropzone__text">
                                                        <span class="ai-dropzone__hint">اسحب الملف أو اضغط للاختيار</span>
                                                        <span class="ai-dropzone__filename" id="ai_manager_document_filename"></span>
                                                    </span>
                                                </label>
                                                <input type="file" id="ai_manager_document" accept=".pdf,.jpg,.jpeg,.png,.webp" class="form-control form-control-sm ai-dropzone__input">
                                                <button type="button" id="ai_manager_extract_btn" class="btn btn-sm btn-primary text-nowrap"><i class="fa fa-magic me-1"></i>استخراج</button>
                                            </div>
                                            <div id="ai_preview_manager" class="mt-2"></div>
                                            <div id="ai_manager_extract_status" class="fs-8 text-muted mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                (function(){
                                    var btn=document.getElementById('ai_manager_extract_btn'); if(!btn||btn.dataset.bound) return; btn.dataset.bound=1;
                                    var statusBase = "{{ url('dashboard/ai-extract/status') }}";
                                    function escapeHtml(s) { return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }
                                    function setStatus(el, cls, text) { el.textContent = ''; var sp = document.createElement('span'); sp.className = cls; sp.textContent = text; el.appendChild(sp); }

                                    function setv(id,v){ var el=document.getElementById(id); if(el&&v!=null&&v!==''){ el.value=v; el.dispatchEvent(new Event('change')); } }

                                    // Fill the form from an extraction result (shared by async result handling).
                                    function applyExtraction(d, st){
                                        setv('manager_name', d.manager_name); setv('manager_mobile', d.manager_mobile);
                                        // --- T6-1: confidence highlighting, appended after the existing prefill lines ---
                                        try {
                                            var _conf = d.confidence || {};
                                            var _map = { manager_name:'manager_name', manager_mobile:'manager_mobile' };
                                            Object.keys(_map).forEach(function(k){
                                                var el = document.getElementById(_map[k]);
                                                if (!el) return;
                                                var c = _conf[k];
                                                var old = document.getElementById('conf_hint_'+_map[k]); if (old) old.remove();
                                                el.classList.remove('ai-low-conf');
                                                if (typeof c === 'number' && c < 0.7) {
                                                    el.classList.add('ai-low-conf');
                                                    var h = document.createElement('small'); h.className='ai-conf-hint'; h.id='conf_hint_'+_map[k];
                                                    h.textContent = '⚠ ثقة منخفضة ('+Math.round(c*100)+'%) — راجع الحقل';
                                                    el.parentNode.insertBefore(h, el.nextSibling);
                                                }
                                            });
                                        } catch(e) {}
                                        st.innerHTML='<span class="text-success">تم الاستخراج ✓ راجع الحقول ثم احفظ</span>';
                                    }

                                    btn.addEventListener('click', function(){
                                        var f=document.getElementById('ai_manager_document'); var st=document.getElementById('ai_manager_extract_status');
                                        if(!f.files.length){ st.innerHTML='<span class="text-danger">اختر ملف الوثيقة أولاً</span>'; return; }
                                        var fd=new FormData(); fd.append('document', f.files[0]); fd.append('_token','{{ csrf_token() }}');
                                        st.textContent='جارٍ رفع المستند...'; btn.disabled=true;

                                        // 1) Queue the extraction (returns instantly with a job id).
                                        fetch('{{ route('dashboard.ai_extract.start', ['module' => 'manager']) }}',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
                                        .then(function(r){return r.json();}).then(function(res){
                                            if(!res.status || !res.job_id){ btn.disabled=false; setStatus(st, 'text-danger', res.message_out || 'فشل بدء الاستخراج'); return; }
                                            st.textContent='جارٍ الاستخراج بالذكاء الاصطناعي...';
                                            // 2) Poll for the result (works whether the job ran inline or on a worker).
                                            var tries=0, maxTries=80; // ~2 min at 1.5s
                                            var iv=setInterval(function(){
                                                tries++;
                                                if(tries>maxTries){ clearInterval(iv); btn.disabled=false; st.innerHTML='<span class="text-danger">استغرق الاستخراج وقتاً طويلاً — حاول مرة أخرى أو أدخل البيانات يدوياً</span>'; return; }
                                                fetch(statusBase+'/'+res.job_id,{headers:{'X-Requested-With':'XMLHttpRequest'}})
                                                .then(function(r){return r.json();}).then(function(s){
                                                    if(!s.status){ return; }
                                                    if(s.state==='done'){ clearInterval(iv); btn.disabled=false; applyExtraction(s.data||{}, st); }
                                                    else if(s.state==='failed'){ clearInterval(iv); btn.disabled=false; setStatus(st, 'text-danger', s.error || 'فشل الاستخراج'); }
                                                }).catch(function(){ /* transient — keep polling */ });
                                            }, 1500);
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
                                    enhance({inputId: 'ai_manager_document', zoneId: 'ai_manager_document_dropzone', nameId: 'ai_manager_document_filename', statusIds: ['ai_manager_extract_status']});
                                })();
                                </script>
                                {{-- document preview thumbnail (additive, does not touch analyze/prefill logic) --}}
                                <script>
                                (function(){
                                    var inp = document.getElementById('ai_manager_document');
                                    var box = document.getElementById('ai_preview_manager');
                                    if (!inp || !box || inp.dataset.prevBound) return; inp.dataset.prevBound='1';
                                    inp.addEventListener('change', function(){
                                        box.innerHTML=''; var f=inp.files && inp.files[0]; if(!f) return;
                                        if (/^image\//.test(f.type)) {
                                            var img=document.createElement('img'); img.src=URL.createObjectURL(f);
                                            img.style.cssText='max-height:120px;border:1px solid #eee;border-radius:8px'; box.appendChild(img);
                                        } else {
                                            var badge = document.createElement('span'); badge.className = 'badge badge-light-primary';
                                            var icon = document.createElement('i'); icon.className = 'fa fa-file-pdf me-1';
                                            badge.appendChild(icon);
                                            badge.appendChild(document.createTextNode(f.name));
                                            box.appendChild(badge);
                                        }
                                    });
                                })();
                                </script>

                                {{-- B2: keyboard accessibility for the drop-zone container — Enter/Space opens the file
                                     picker via the existing label; a NEW, separate script that does not touch the
                                     analyze/prefill logic above. --}}
                                <script>
                                (function aiDropzoneKeyboardActivate(){
                                    document.querySelectorAll('.ai-dropzone[role="button"]').forEach(function(zone){
                                        if (zone.dataset.kbBound) { return; }
                                        zone.dataset.kbBound = '1';
                                        zone.addEventListener('keydown', function(e){
                                            if (e.target !== zone) { return; }
                                            if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                                                e.preventDefault();
                                                var label = zone.querySelector('label');
                                                if (label) { label.click(); }
                                            }
                                        });
                                    });
                                })();
                                </script>

                                <div class=" col-12 col-lg-4 col-md-12 col-sm-12 mb-5"><label for="manager_name"
                                        class="form-label required fs-6 fw-bold text-dark mb-3">اسم القائد</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-user-tie fa-fw text-dark"></i></span></div><input
                                            type="text" name="manager_name" id="manager_name"
                                            class="form-control fw-bold  text-dark" placeholder="اسم القائد"
                                            value="" autocomplete="off">
                                    </div>
                                </div>



                                <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                    <label for="manager_mobile" class="form-label  fs-6 fw-bold text-dark mb-3">رقم جوال القائد</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-phone-volume fa-fw text-dark"></i></span></div><input
                                            type="text" name="manager_mobile" id="manager_mobile"
                                            class="form-control fw-bold text-dark text-info" minlenght="1"
                                            maxlength="20" placeholder="رقم جوال القائد">
                                    </div>
                                </div>

                                <div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
                                    <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">الملاحظة
                                    </label>
                                    <textarea name="note" rows="1" class="form-control fw-bold" id="note" placeholder="الملاحظة"></textarea>
                                </div>

                            </div>
                            <div class=" mb-2 d-flex justify-content ">
                                <button type="submit" id="kt_docs_formvalidation_text_submit"
                                    class="btn btn-primary font-weight-bold mr-2" name="submitButton">حفظ
                                    البيانات</button>
                                &nbsp;&nbsp;
                                <button type="reset" class="btn btn-light font-weight-bold mr-2">تفريغ البيانات</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/module/manager_j.js') }}?t={{ config('global.ver.version_all') }}">
    </script>
    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
@endsection
