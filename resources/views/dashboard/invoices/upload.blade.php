@extends('layouts.app')
@section('module', 'استخراج الفواتير')
@section('sub', 'الذكاء الاصطناعي')
@section('title', "$page_title")
@section('content')
    <style>
        .inv-drop{border:2px dashed var(--sn-line);border-radius:var(--sn-r-lg);background:var(--sn-paper-2);
            padding:48px 24px;text-align:center;cursor:pointer;transition:border-color var(--sn-dur-base) var(--sn-ease-out),
            background-color var(--sn-dur-base) var(--sn-ease-out),transform var(--sn-dur-base) var(--sn-ease-out);position:relative}
        .inv-drop:hover,.inv-drop.drag{border-color:var(--sn-emerald);background:var(--sn-emerald-tint);transform:translateY(-1px)}
        .inv-drop .ico{width:64px;height:64px;border-radius:var(--sn-r-md);background:var(--sn-card);border:1px solid var(--sn-line);
            display:grid;place-items:center;margin:0 auto 14px;font-size:28px;color:var(--sn-emerald);
            box-shadow:var(--sn-shadow-sm);transition:border-color var(--sn-dur-base) var(--sn-ease-out),transform var(--sn-dur-base) var(--sn-ease-out)}
        .inv-drop:hover .ico,.inv-drop.drag .ico{border-color:var(--sn-emerald);transform:translateY(-2px)}
        .inv-drop .fname{margin-top:10px;font-weight:600;color:var(--sn-emerald-deep);word-break:break-all}
        .inv-drop input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer}
        .inv-ov{position:fixed;inset:0;z-index:1090;display:none;place-items:center;background:rgba(10,20,16,.6);backdrop-filter:blur(3px)}
        .inv-ov.on{display:grid}
        .inv-scan{width:150px;height:190px;background:var(--sn-card);border-radius:var(--sn-r-md);position:relative;overflow:hidden;padding:20px 16px;
            box-shadow:var(--sn-shadow-lg)}
        .inv-scan .ln{height:6px;border-radius:3px;background:var(--sn-line);margin-bottom:11px}
        .inv-scan .ln:nth-child(2){width:72%}.inv-scan .ln:nth-child(4){width:86%}.inv-scan .ln:nth-child(5){width:55%}
        .inv-scan .beam{position:absolute;left:0;right:0;height:34px;top:-34px;
            background:linear-gradient(180deg,transparent,rgba(14,107,79,.55),transparent);
            box-shadow:0 0 18px 4px rgba(14,107,79,.45);animation:invscan 1.4s ease-in-out infinite}
        @keyframes invscan{0%{top:-12%}100%{top:104%}}
        .inv-ov .lbl{color:#fff;text-align:center;margin-top:22px;font-weight:700;font-size:17px}
        .inv-ov .lbl small{display:block;opacity:.8;font-weight:400;font-size:13px;margin-top:5px}
    </style>

    <div class="ai-page">
        @include('dashboard.partials.ai_subscription_banner')

    <div id="err" class="alert alert-danger d-none"></div>
    <form id="up_form" enctype="multipart/form-data" method="POST" action="{{ route('dashboard.invoices.store') }}">
        @csrf
        <div class="card">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold">رفع فاتورة واستخراجها بالذكاء الاصطناعي 🤖</h3>
            </div>
            <div class="card-body">
                <p class="text-muted fs-7 mb-4">اسحب ملف PDF وأفلته هنا، أو انقر للاختيار. تُقرأ كل فاتورة على حدة وتُستخرج بياناتها وصورتها — ثم رحّلها إلى المشتريات.</p>
                <label class="inv-drop" id="drop">
                    <div class="ico"><i class="bi bi-file-earmark-arrow-up"></i></div>
                    <div class="fs-5 fw-bold text-gray-800">أفلت ملف الفاتورة (PDF) هنا</div>
                    <div class="text-muted fs-7 mt-1">أو انقر للاختيار — فاتورة واحدة أو عدة فواتير (حتى أكثر من 100)</div>
                    <div class="fname" id="fname"></div>
                    <input type="file" name="pdf" id="pdf" accept="application/pdf" required>
                </label>
                <div class="mt-6 d-flex gap-3">
                    <button type="submit" id="btn" class="btn btn-primary fw-bold">استخراج الآن ←</button>
                    <a href="{{ route('dashboard.invoices.index') }}" class="btn btn-light fw-bold">سجل العمليات</a>
                </div>
            </div>
        </div>
    </form>

    <div class="inv-ov" id="ov">
        <div>
            <div class="inv-scan"><div class="beam"></div><div class="ln"></div><div class="ln"></div><div class="ln"></div><div class="ln"></div><div class="ln"></div></div>
            <div class="lbl">جارٍ قراءة الفاتورة…<small>الذكاء الاصطناعي يستخرج البيانات، انتظر قليلاً</small></div>
        </div>
    </div>
    </div>
@endsection
@section('styles')
    @include('dashboard.partials.ai-page-styles')
@endsection
@section('scripts')
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });
        var drop = document.getElementById('drop'), pdf = document.getElementById('pdf'), fname = document.getElementById('fname');
        pdf.addEventListener('change', function () { fname.textContent = pdf.files[0] ? '📄 ' + pdf.files[0].name : ''; });
        ['dragenter','dragover'].forEach(function(e){ drop.addEventListener(e, function(ev){ ev.preventDefault(); drop.classList.add('drag'); }); });
        ['dragleave','drop'].forEach(function(e){ drop.addEventListener(e, function(ev){ ev.preventDefault(); drop.classList.remove('drag'); }); });
        drop.addEventListener('drop', function (ev) { if (ev.dataTransfer.files.length) { pdf.files = ev.dataTransfer.files; fname.textContent = '📄 ' + ev.dataTransfer.files[0].name; } });

        $('#up_form').on('submit', function (e) {
            e.preventDefault();
            if (!pdf.files.length) { showErr('اختر ملف PDF أولاً'); return; }
            var fd = new FormData(this);
            $('#btn').prop('disabled', true).text('جارٍ الرفع...');
            $('#err').addClass('d-none');
            document.getElementById('ov').classList.add('on');
            $.ajax({ url: $(this).attr('action'), method: 'POST', data: fd, processData: false, contentType: false })
                .done(function (r) {
                    if (r.status) { window.location = r.redirect; }
                    else { document.getElementById('ov').classList.remove('on'); showErr(r.message_out || 'حدث خطأ'); }
                })
                .fail(function (x) { document.getElementById('ov').classList.remove('on'); showErr((x.responseJSON && x.responseJSON.message_out) || 'فشل الرفع، تأكد أن الملف PDF'); })
                .always(function () { $('#btn').prop('disabled', false).text('استخراج الآن ←'); });
        });
        function showErr(m) { $('#err').removeClass('d-none').text(m); }
    </script>
@endsection
