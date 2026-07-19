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
    <form id="save_workers" name="save_workers" class="form" action="{{ route('dashboard.workers.store') }}"
        enctype="multipart/form-data" autocomplete="off" method="POST">
        @csrf
        <div class="d-flex flex-column flex-lg-row">
            <div class="mb-10 flex-lg-row-fluid mb-lg-0">
                <div class="card">
                    <div class="px-1 card-body">
                        <div class="p-5 mb-6 alert alert-dismissible d-flex flex-column flex-sm-row w-100"
                            id="errorBox_worker" style="display: none !important">
                            <span class="mb-5 svg-icon svg-icon-2hx svg-icon-light me-4 mb-sm-0">
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
                                <span id="displayErrors_worker" class="mb-2 fw-bolder text-light"></span>
                            </div>
                            <button type="button"
                                class="top-0 m-2 position-absolute position-sm-relative m-sm-0 end-0 btn btn-icon ms-sm-auto"
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
                            <div class="mb-5 row gx-5">
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
                                /* T6-motion: analyzing / result-reveal / low-conf pulse / dropzone affordance / card entrance (additive) */
                                @keyframes ai-card-in{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
                                .ai-card{animation:ai-card-in .4s cubic-bezier(0.22,1,0.36,1) both;}
                                @keyframes ai-icon-breathe{0%,100%{transform:scale(1);}50%{transform:scale(1.08);}}
                                .ai-card.ai-is-analyzing .ai-icon-badge{animation:ai-icon-breathe 1.1s ease-in-out infinite;}
                                @keyframes ai-status-dots{0%,100%{content:"";}25%{content:".";}50%{content:"..";}75%{content:"...";}}
                                .ai-status.is-loading::after{content:"";display:inline-block;min-width:1.2em;text-align:start;animation:ai-status-dots 1.2s steps(1) infinite;}
                                @keyframes ai-scan-sweep{0%{transform:translateY(-100%);opacity:0;}10%{opacity:1;}90%{opacity:1;}100%{transform:translateY(220%);opacity:0;}}
                                .ai-card.ai-is-analyzing .ai-dropzone{overflow:hidden;}
                                .ai-card.ai-is-analyzing .ai-dropzone::after{content:"";position:absolute;inset-inline:0;top:0;height:40%;background:linear-gradient(180deg,rgba(14,107,79,0) 0%,rgba(14,107,79,.35) 50%,rgba(14,107,79,0) 100%);pointer-events:none;animation:ai-scan-sweep 1.6s ease-in-out infinite;}
                                .ai-dropzone{transition:border-color .15s ease,background-color .15s ease,transform .2s cubic-bezier(0.22,1,0.36,1),box-shadow .2s ease;}
                                .ai-dropzone:hover,.ai-dropzone.is-dragover{transform:translateY(-2px);box-shadow:0 .4rem 1rem rgba(14,107,79,.18);}
                                @keyframes ai-preview-in{from{opacity:0;transform:scale(.96);}to{opacity:1;transform:scale(1);}}
                                [id^="ai_preview_"] img,[id^="ai_preview_"] .badge{animation:ai-preview-in .3s cubic-bezier(0.22,1,0.36,1) both;}
                                @keyframes ai-field-flash{0%{background-color:rgba(14,107,79,.22);}100%{background-color:transparent;}}
                                .ai-field-flash{animation:ai-field-flash .6s ease-out both;}
                                @keyframes ai-check-pop{0%{opacity:0;transform:scale(.4);}60%{opacity:1;transform:scale(1.15);}100%{opacity:1;transform:scale(1);}}
                                .ai-check-pop{display:inline-flex;align-items:center;justify-content:center;width:1.05rem;height:1.05rem;border-radius:50%;background:#17c653;color:#fff;font-size:.65rem;margin-inline-end:.3rem;animation:ai-check-pop .35s cubic-bezier(0.22,1,0.36,1) both;}
                                @keyframes ai-low-conf-pulse{0%,100%{box-shadow:0 0 0 0 rgba(241,180,76,.55);}50%{box-shadow:0 0 0 6px rgba(241,180,76,0);}}
                                .ai-low-conf{animation:ai-low-conf-pulse 1s ease-out 2;}
                                @keyframes ai-chip-in{from{opacity:0;transform:translateY(4px);}to{opacity:1;transform:translateY(0);}}
                                .ai-fields-chip{display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;font-weight:600;color:#0E6B4F;background:rgba(14,107,79,.1);border-radius:50rem;padding:.15rem .55rem;margin-inline-start:.4rem;animation:ai-chip-in .25s cubic-bezier(0.22,1,0.36,1) both;}
                                @keyframes ai-btn-in{from{opacity:0;transform:scale(.85);}to{opacity:1;transform:scale(1);}}
                                .ai-reanalyze-btn{animation:ai-btn-in .25s cubic-bezier(0.22,1,0.36,1) both;margin-inline-start:.4rem;}
                                @media (prefers-reduced-motion: reduce){
                                .ai-status.is-loading::before{animation:none;}
                                .ai-card,.ai-card.ai-is-analyzing .ai-icon-badge,.ai-status.is-loading::after,.ai-card.ai-is-analyzing .ai-dropzone::after,.ai-dropzone,.ai-dropzone:hover,.ai-dropzone.is-dragover,[id^="ai_preview_"] img,[id^="ai_preview_"] .badge,.ai-field-flash,.ai-check-pop,.ai-low-conf,.ai-fields-chip,.ai-reanalyze-btn{animation:none !important;transition:none !important;transform:none !important;}
                                }
                                </style>
                                <div class="col-12 mb-4">
                                    <div class="card ai-card">
                                        <div class="card-body py-4">
                                            <div class="ai-card-head">
                                                <span class="ai-icon-badge"><i class="fa fa-robot"></i></span>
                                                <h3 class="ai-card-title fs-6 text-primary">استخراج بالذكاء الاصطناعي</h3>
                                                <span class="ai-pill"><i class="fa fa-magic"></i> ذكاء اصطناعي</span>
                                            </div>
                                            <p class="text-muted fs-8 mb-3">ارفع صورة أو PDF للإقامة / الجواز / الهوية ليتم استخراج بياناتها تلقائياً</p>
                                            <div class="ai-dropzone" id="ai_worker_document_dropzone" role="button" tabindex="0" aria-label="منطقة رفع مستند العامل — اسحب الملف هنا أو اضغط للاختيار">
                                                <label for="ai_worker_document" class="ai-dropzone__label">
                                                    <i class="fa fa-cloud-upload-alt ai-dropzone__icon"></i>
                                                    <span class="ai-dropzone__text">
                                                        <span class="ai-dropzone__hint">اسحب الملف أو اضغط للاختيار</span>
                                                        <span class="ai-dropzone__filename" id="ai_worker_document_filename"></span>
                                                    </span>
                                                </label>
                                                <input type="file" id="ai_worker_document" accept=".pdf,.jpg,.jpeg,.png,.webp" class="form-control form-control-sm ai-dropzone__input">
                                                <button type="button" id="ai_worker_extract_btn" class="btn btn-sm btn-primary text-nowrap"><i class="fa fa-magic me-1"></i>استخراج</button>
                                            </div>
                                            <div id="ai_preview_workers" class="mt-2"></div>
                                            <div id="ai_worker_extract_status" class="fs-8 text-muted mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                (function(){
                                    var btn=document.getElementById('ai_worker_extract_btn'); if(!btn||btn.dataset.bound) return; btn.dataset.bound=1;
                                    btn.addEventListener('click', function(){
                                        var f=document.getElementById('ai_worker_document'); var st=document.getElementById('ai_worker_extract_status');
                                        if(!f.files.length){ st.innerHTML='<span class="text-danger">اختر ملف الوثيقة أولاً</span>'; return; }
                                        var fd=new FormData(); fd.append('document', f.files[0]); fd.append('_token','{{ csrf_token() }}');
                                        st.textContent='جارٍ الاستخراج بالذكاء الاصطناعي...'; btn.disabled=true;
                                        fetch('{{ route('dashboard.workers.ai_extract') }}',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
                                        .then(function(r){return r.json();}).then(function(res){
                                            btn.disabled=false;
                                            if(!res.status){ st.innerHTML='<span class="text-danger">'+(res.message_out||'فشل الاستخراج')+'</span>'; return; }
                                            var d=res.data;
                                            function setv(id,v){ var el=document.getElementById(id); if(el&&v!=null&&v!==''){ el.value=v; el.dispatchEvent(new Event('change')); } }
                                            setv('worker_name', d.worker_name); setv('ssn', d.ssn); setv('passport_no', d.passport_no);
                                            setv('dob', d.dob); setv('doe', d.doe); setv('dop', d.dop);
                                            if(d.nation_id){ var s=document.getElementById('nation_id'); if(s){ s.value=String(d.nation_id); if(window.jQuery){ jQuery(s).trigger('change'); } } }
                                            // --- T6-1: confidence highlighting, appended after the existing prefill lines ---
                                            try {
                                                var _conf = (res.data && res.data.confidence) || {};
                                                var _map = { worker_name:'worker_name', ssn:'ssn', passport_no:'passport_no', dob:'dob', doe:'doe', dop:'dop' };
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
                                            st.innerHTML='<span class="text-success">تم الاستخراج ✓ راجع الحقول ثم احفظ</span>'+(d.nationality_name?(' — الجنسية المقترحة: '+d.nationality_name):'');
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
                                    enhance({inputId: 'ai_worker_document', zoneId: 'ai_worker_document_dropzone', nameId: 'ai_worker_document_filename', statusIds: ['ai_worker_extract_status']});
                                })();
                                </script>
                                {{-- T6-2: document preview thumbnail (additive, does not touch analyze/prefill logic) --}}
                                <script>
                                (function(){
                                    var inp = document.getElementById('ai_worker_document');
                                    var box = document.getElementById('ai_preview_workers');
                                    if (!inp || !box || inp.dataset.prevBound) return; inp.dataset.prevBound='1';
                                    inp.addEventListener('change', function(){
                                        box.innerHTML=''; var f=inp.files && inp.files[0]; if(!f) return;
                                        if (/^image\//.test(f.type)) {
                                            var img=document.createElement('img'); img.src=URL.createObjectURL(f);
                                            img.style.cssText='max-height:120px;border:1px solid #eee;border-radius:8px'; box.appendChild(img);
                                        } else {
                                            box.innerHTML='<span class="badge badge-light-primary"><i class="fa fa-file-pdf me-1"></i>'+f.name+'</span>';
                                        }
                                    });
                                })();
                                </script>
                                {{-- T6-motion: analyzing state class + result-reveal (field flash / check pop / fields chip) + optional
                                     re-analyze button. Additive — watches the same status element with a NEW, separate
                                     MutationObserver; does not touch the existing fetch/prefill/observer above. --}}
                                <script>
                                (function(){
                                    function aiMotion(opts){
                                        var el = document.getElementById(opts.statusId);
                                        if (!el || el.dataset.motionWatch) { return; }
                                        el.dataset.motionWatch = '1';
                                        var card = el.closest('.ai-card');
                                        var btn = opts.analyzeBtnId ? document.getElementById(opts.analyzeBtnId) : null;
                                        var mo = new MutationObserver(function(){
                                            var text = (el.textContent || '').trim();
                                            var loading = /^جارٍ/.test(text);
                                            if (card) { card.classList.toggle('ai-is-analyzing', loading); }
                                            if (loading) { return; }
                                            var success = el.querySelector('.text-success');
                                            if (!success) { return; }
                                            var n = 0, delay = 0;
                                            (opts.fieldIds || []).forEach(function(fid){
                                                var fEl = document.getElementById(fid);
                                                if (!fEl || !fEl.value) { return; }
                                                n++;
                                                fEl.classList.remove('ai-field-flash'); void fEl.offsetWidth;
                                                fEl.style.animationDelay = delay + 'ms';
                                                fEl.classList.add('ai-field-flash');
                                                delay += 40;
                                            });
                                            if (!success.querySelector('.ai-check-pop')) {
                                                var chk = document.createElement('span'); chk.className = 'ai-check-pop'; chk.textContent = '✓';
                                                success.insertBefore(chk, success.firstChild);
                                            }
                                            // Idempotent: this MutationObserver watches el's subtree — blindly removing/
                                            // re-adding the chip re-triggers the observer forever and FREEZES the page.
                                            var chipText = 'تم استخراج ' + n + ' حقول — راجع المميّزة';
                                            var oldChip = el.querySelector('.ai-fields-chip');
                                            if (oldChip && (n <= 0 || oldChip.textContent !== chipText)) { oldChip.remove(); oldChip = null; }
                                            if (!oldChip && n > 0) {
                                                var chip = document.createElement('span'); chip.className = 'ai-fields-chip';
                                                chip.textContent = chipText;
                                                el.appendChild(chip);
                                            }
                                            if (btn && !btn.dataset.reanalyzeAdded) {
                                                btn.dataset.reanalyzeAdded = '1';
                                                var rbtn = document.createElement('button');
                                                rbtn.type = 'button';
                                                rbtn.className = 'btn btn-sm btn-light-primary text-nowrap ai-reanalyze-btn';
                                                rbtn.innerHTML = '<i class="fa fa-rotate-right me-1"></i>إعادة التحليل';
                                                rbtn.addEventListener('click', function(){ btn.click(); });
                                                btn.insertAdjacentElement('afterend', rbtn);
                                            }
                                        });
                                        mo.observe(el, {childList: true, characterData: true, subtree: true});
                                    }
                                    aiMotion({statusId: 'ai_worker_extract_status', fieldIds: ['worker_name', 'ssn', 'passport_no', 'dob', 'doe', 'dop', 'nation_id'], analyzeBtnId: 'ai_worker_extract_btn'});
                                })();
                                </script>

                                {{-- B2: keyboard accessibility for the drop-zone container — Enter/Space opens the file
                                     picker via the existing label; a NEW, separate script that does not touch the
                                     analyze/prefill/motion logic above. --}}
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
                                <div class="mb-6 row">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 col-form-label fw-bold fs-6 text-dark">صورة الشخصية
                                        للعامل</label>
                                    <div class="col-lg-8">
                                        <div class="image-input image-input-outline" data-kt-image-input="true"
                                            style="background-image: url(/assets/media/avatars/blank.png)">
                                            <div class="image-input-wrapper w-125px h-125px"
                                                style="background-image: url(/assets/media/avatars/150-2.jpg)"></div>
                                            <label
                                                class="bg-white shadow btn btn-icon btn-circle btn-active-color-primary w-25px h-25px"
                                                data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                                data-bs-dismiss="click" title="Change avatar">
                                                <i class="bi bi-pencil-fill fs-7"></i>

                                                <input type="file" name="avatar" accept="image/*" />
                                                <input type="hidden" name="avatar_remove" />
                                            </label>

                                            <span
                                                class="bg-white shadow btn btn-icon btn-circle btn-active-color-primary w-25px h-25px"
                                                data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                                data-bs-dismiss="click" title="Cancel avatar">
                                                <i class="bi bi-x fs-2"></i>
                                            </span>
                                            <span
                                                class="bg-white shadow btn btn-icon btn-circle btn-active-color-primary w-25px h-25px"
                                                data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                                data-bs-dismiss="click" title="Remove avatar">
                                                <i class="bi bi-x fs-2"></i>
                                            </span>
                                        </div>
                                        <div class="form-text">نوع المسموح: png, jpg, jpeg.</div>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12"><label for="worker_name"
                                        class="mb-3 form-label required fs-6 fw-bold text-dark">اسم
                                        العامل</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-tools fa-fw text-dark"></i></span></div>
                                        <input type="text" name="worker_name" id="worker_name"
                                            class="form-control fw-bold text-dark" placeholder="اسم العامل"
                                            value="" autocomplete="off">
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12"><label for="registration_number   "
                                    class="mb-3 form-label fs-6 fw-bold text-dark">رقم الاشتراك</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-tools fa-fw text-dark"></i></span></div>
                                    <input type="text" name="registration_number" id="registration_number"
                                        class="form-control fw-bold text-dark" placeholder="رقم الاشتراك"
                                        value="" autocomplete="off">
                                </div>
                            </div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="dob" class="mb-3 form-label required fs-6 fw-bold text-dark"> تاريخ
                                        الميلاد :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-calendar-alt fa-fw text-dark"></i></span></div>
                                        <input type="text" name="dob" id="dob"
                                            class="form-control fw-bold text-dark input_date_"
                                            placeholder="تاريخ الميلاد" value="" autocomplete="off">
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="mobile" class="mb-3 form-label fs-6 fw-bold text-dark">رقم هاتف
                                        الموظف</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-phone-volume fa-fw text-dark"></i></span></div>
                                        <input type="text" name="mobile" id="mobile"
                                            class="form-control fw-bold text-dark text-info" minlenght="1"
                                            maxlength="20" placeholder="رقم هاتف الموظف ">
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="ssn" class="mb-3 form-label fs-6 fw-bold text-dark">رقم صاحب
                                        العمل</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-mobile-alt fa-fw text-dark"></i></span></div>
                                        <input type="text" name="phone" id="phone"
                                            class="form-control fw-bold text-dark text-info" minlenght="1"
                                            maxlength="20" placeholder="رقم صاحب العمل">
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="nation_id" class="mb-3 form-label fs-6 fw-bold text-dark">الجنسية</label>
                                    <div>
                                        <select class="form-select fw-bold" data-control="select2" id="nation_id"
                                            name="nation_id" dir="rtl" data-placeholder="الجنسية">
                                            <option value="">اختر ..</option>
                                            @foreach ($nation as $x)
                                                <option value="{{ $x->nation_id }} ">{{ $x->nation_name_ar }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="passport_no" class="mb-3 form-label fs-6 fw-bold text-dark">رقم
                                        الجواز</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-passport fa-fw text-dark"></i></span></div>
                                        <input type="text" name="passport_no" id="passport_no"
                                            class="form-control fw-bold text-dark text-info" minlenght="1"
                                            maxlength="50" placeholder="رقم الجواز">
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="dop" class="mb-3 form-label fs-6 fw-bold text-dark">تاريخ
                                        انتهاء الجواز :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-calendar-minus fa-fw text-dark"></i></span></div>
                                        <input type="text" name="dop" id="dop"
                                            class="form-control fw-bold text-dark input_date_"
                                            placeholder="تاريخ انتهاء الجواز" autocomplete="off">
                                    </div>
                                </div>
                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="manager_id" class="mb-3 form-label fs-6 fw-bold text-dark">قائد المجموعة</label>
                                    <div>
                                        <select class="form-select fw-bold" data-control="select2" id="manager_id"
                                            name="manager_id" dir="rtl" data-placeholder="قائد المجموعة">
                                            <option value="">اختر ..</option>
                                            @foreach ($manager as $x)
                                                <option value="{{ $x->manager_id }} ">{{ $x->manager_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12">
                                    <label for="doe" class="mb-3 form-label fs-6 fw-bold text-dark">تحميل
                                        الجواز :</label>
                                    <input class="form-control custom-file-input" type="file" name='passportfile'>
                                </div>

                                <div class="mb-5 border col-12 col-lg-2 col-md-12 col-sm-12 border-success">
                                    <label class="mb-3 form-label fs-6 fw-bold text-danger">حالة التواجد :</label>
                                    <div class="fv-row fv-plugins-icon-container fv-plugins-bootstrap5-row-invalid">
                                        <div class="mt-3 d-flex align-items-center">
                                            <label class="form-check form-check-inline form-check-solid me-5 is-invalid">
                                                <input class="form-check-input" name="inside" id="inside"
                                                    type="checkbox" checked value="1">
                                                <span class="fw-bold ps-2 fs-6 text-dark">نعم داخل المملكة</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>


                                <div class="my-10 mb-8 separator separator-content border-dark"><span
                                        class="w-150px fw-bold text-danger">بيانات الإقامة</span></div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="ssn" class="mb-3 form-label required fs-6 fw-bold text-dark">رقم
                                        الإقامة</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-id-card fa-fw text-dark"></i></span></div>
                                        <input type="text" name="ssn" id="ssn"
                                            class="form-control fw-bold text-dark text-info"
                                            data-inputmask="'alias' : 'decimal'" minlenght="1" maxlength="50"
                                            placeholder="رقم الإقامة">
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-5 col-md-12 col-sm-12">
                                    <label for="ssnfile" class="mb-3 form-label fs-6 fw-bold text-dark">تحميل
                                        الإقامة :</label>
                                    <input class="form-control custom-file-input" type="file" name='ssnfile'>
                                </div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="dos" class="mb-3 form-label required fs-6 fw-bold text-dark">تاريخ
                                        اصدار الاقامة :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-calendar-alt fa-fw text-dark"></i></span></div>
                                        <input type="text" name="dos" id="dos"
                                            class="form-control fw-bold text-dark input_date_"
                                            placeholder="تاريخ اصدار الاقامة" value="" autocomplete="off">
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="doe" class="mb-3 form-label required fs-6 fw-bold text-dark">تاريخ
                                        إنتهاء الإقامة :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-calendar-alt fa-fw text-dark"></i></span></div>
                                        <input type="text" name="doe" id="doe"
                                            class="form-control fw-bold text-dark input_date_"
                                            placeholder="تاريخ إنتهاء الإقامة" value="" autocomplete="off">
                                    </div>
                                </div>

                                <div class="my-10 mb-8 separator separator-content border-dark"><span
                                        class="w-150px fw-bold text-danger">بيانات العمل</span></div>

                                <div class="mb-5 col-12 col-lg-2 col-md-12 col-sm-12">
                                    <label for="dow" class="mb-3 form-label fs-6 fw-bold text-dark"> تاريخ
                                        التعيين :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="far fa-calendar-alt fa-fw text-dark"></i></span></div>
                                        <input type="text" name="dow" id="dow"
                                            class="form-control fw-bold text-dark input_date_"
                                            placeholder=" تاريخ التعيين" value="" autocomplete="off">
                                    </div>
                                </div>

                                <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12">
                                    <label for="work_place_id" class="mb-3 form-label fs-6 fw-bold text-dark">مكان
                                        العمل</label>
                                    <div>
                                        <select class="form-select fw-bold" data-control="select2" id="work_place_id"
                                            name="work_place_id" dir="rtl" data-placeholder="مكان العمل">
                                            <option value="">اختر ..</option>
                                            @foreach ($work_place as $x)
                                                <option value="{{ $x->work_place_id }} ">{{ $x->work_place_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-3 col-md-12 col-sm-12">
                                    <label for="job_id" class="mb-3 form-label fs-6 fw-bold text-dark">المهنة</label>
                                    <div>
                                        <select class="form-select fw-bold" data-control="select2" id="job_id"
                                            name="job_id" dir="rtl" data-placeholder="المهنة">
                                            <option value="">اختر ..</option>
                                            @foreach ($job as $x)
                                                <option value="{{ $x->job_id }} ">{{ $x->job_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="mb-5 col-12 col-lg-4 col-md-12 col-sm-12" id="container_file"
                                    name="container_file">
                                    <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                        <a type="button" id="add_file"
                                            class="btn btn-secondary kt-font-info kt-font-bolder"
                                            style='border-color:#232b51;'><i class="la la-chain"></i>تحميل أوراق اخرى</a>
                                    </div>
                                    <br />
                                    <div class="form-group row">
                                        <div class="input-group">
                                            <div class="form-control">
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
                                <div class="mb-5 col-12 col-lg-12 col-md-12 col-sm-12">
                                    <label for="note" class="mb-3 form-label fs-6 fw-bold text-dark">الملاحظة
                                    </label>
                                    <textarea name="note" rows="1" class="form-control fw-bold" id="note" placeholder="الملاحظة"></textarea>
                                </div>

                            </div>
                            <div class="mb-2 d-flex justify-content">
                                <button type="submit" id="kt_docs_formvalidation_text_submit"
                                    class="mr-2 btn btn-primary font-weight-bold" name="submitButton">حفظ
                                    البيانات
                                </button>
                                &nbsp;&nbsp;
                                <button type="reset" class="mr-2 btn btn-light font-weight-bold">تفريغ البيانات</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('styles')
    <style>
    </style>
@endsection
@section('scripts')
    <script>
        $('#add_file').on('click', function() {
            var newfield =
                '<div class="form-group row repeat"><div class="input-group"><div class="form-control custom-file"><input type="file" class="form-control custom-file-input" name="files[]" ></div><div class="input-group-append" style="padding: 0.7rem 1rem;"><a class="btn btn-lg btn-danger remove"  ><span><i class="la la-minus" style="color:#fff"></i></span></a></div></div></div>';
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
    </script>
    <script type="text/javascript" src="{{ asset('assets/module/woker_j.js') }}?t={{ config('global.ver.version_all') }}">
    </script>
    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
@endsection
