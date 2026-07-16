<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>مكتب الفواتير · استخراج</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Reem+Kufi:wght@500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --paper:#F4EEE1; --paper-2:#FBF7EE; --card:#FFFDF8;
            --ink:#23201A; --ink-soft:#6E6557; --line:#E4DBC8;
            --emerald:#0E6B4F; --emerald-deep:#0A4F3A; --emerald-tint:#E4EFE9;
            --amber:#B5780A; --amber-tint:#F6ECD4;
            --rust:#A93B2C; --rust-tint:#F4E2DC;
            --gold:#C19A45;
            --shadow:0 1px 2px rgba(35,32,26,.04),0 12px 30px -12px rgba(35,32,26,.18);
            --radius:18px;
        }
        *{box-sizing:border-box}
        html,body{margin:0}
        body{
            font-family:'IBM Plex Sans Arabic',sans-serif; color:var(--ink);
            background:var(--paper);
            background-image:
                linear-gradient(var(--line) 1px,transparent 1px),
                linear-gradient(90deg,var(--line) 1px,transparent 1px);
            background-size:38px 38px; background-position:center;
            min-height:100vh; padding:48px 20px 80px; position:relative;
        }
        body::before{ /* paper grain + vignette */
            content:""; position:fixed; inset:0; pointer-events:none; z-index:0;
            background:
                radial-gradient(120% 80% at 50% -10%, rgba(255,255,255,.55), transparent 60%),
                radial-gradient(100% 60% at 50% 120%, rgba(35,32,26,.06), transparent 60%);
        }
        .wrap{max-width:980px; margin:0 auto; position:relative; z-index:1}

        /* ---- header ---- */
        .top{display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:34px}
        .brand{display:flex; align-items:center; gap:14px}
        .seal{
            width:52px; height:52px; border-radius:50%; flex:none; display:grid; place-items:center;
            color:var(--emerald); border:2px solid var(--emerald);
            font-family:'Reem Kufi'; font-weight:700; font-size:22px;
            box-shadow:inset 0 0 0 3px var(--paper),0 0 0 1px var(--emerald);
            transform:rotate(-6deg);
        }
        .brand h1{font-family:'Reem Kufi'; font-weight:700; font-size:26px; margin:0; letter-spacing:.5px}
        .brand p{margin:2px 0 0; color:var(--ink-soft); font-size:13px}
        .stats{display:flex; gap:12px}
        .chip{
            background:var(--card); border:1px solid var(--line); border-radius:14px;
            padding:10px 18px; text-align:center; box-shadow:var(--shadow); min-width:96px;
        }
        .chip b{display:block; font-family:'Reem Kufi'; font-size:26px; line-height:1; color:var(--emerald-deep)}
        .chip span{font-size:11px; color:var(--ink-soft); letter-spacing:.3px}

        /* ---- dropzone card ---- */
        .panel{
            background:var(--card); border:1px solid var(--line); border-radius:var(--radius);
            box-shadow:var(--shadow); padding:28px; margin-bottom:30px; position:relative; overflow:hidden;
        }
        .panel::after{ /* hanging stamp corner */
            content:"فاتورة"; position:absolute; top:14px; left:-34px; transform:rotate(-45deg);
            background:var(--emerald); color:#fff; font-size:11px; letter-spacing:2px;
            padding:4px 40px; font-family:'Reem Kufi'; opacity:.9;
        }
        .panel h2{font-family:'Reem Kufi'; font-weight:600; font-size:18px; margin:0 0 4px}
        .panel .sub{color:var(--ink-soft); font-size:13px; margin:0 0 20px}

        .drop{
            border:2px dashed var(--line); border-radius:14px; background:var(--paper-2);
            padding:34px 20px; text-align:center; cursor:pointer; transition:.25s ease;
            position:relative;
        }
        .drop:hover{border-color:var(--emerald); background:var(--emerald-tint)}
        .drop.drag{border-color:var(--emerald); background:var(--emerald-tint); transform:scale(1.01)}
        .drop .doc{
            width:54px; height:66px; margin:0 auto 14px; border-radius:6px; position:relative;
            background:#fff; border:1.5px solid var(--ink-soft);
            box-shadow:0 6px 16px -8px rgba(35,32,26,.4);
        }
        .drop .doc::before{content:""; position:absolute; top:9px; left:9px; right:9px; height:2px; background:var(--line);
            box-shadow:0 7px 0 var(--line),0 14px 0 var(--line),0 21px 0 var(--line),0 28px 0 var(--emerald)}
        .drop .doc::after{content:""; position:absolute; top:-1px; right:-1px; border-width:0 14px 14px 0;
            border-style:solid; border-color:var(--paper-2) var(--paper-2) var(--ink-soft) var(--ink-soft)}
        .drop .big{font-family:'Reem Kufi'; font-size:16px; color:var(--ink)}
        .drop .hint{font-size:12px; color:var(--ink-soft); margin-top:4px}
        .drop .fname{margin-top:10px; font-size:13px; color:var(--emerald-deep); font-weight:600; word-break:break-all}
        .drop input[type=file]{position:absolute; inset:0; opacity:0; cursor:pointer}

        .controls{display:flex; gap:14px; align-items:flex-end; margin-top:18px; flex-wrap:wrap}
        .field{flex:1; min-width:180px}
        .field label{display:block; font-size:12px; color:var(--ink-soft); margin-bottom:6px}
        .field select{
            width:100%; padding:12px 14px; border:1px solid var(--line); border-radius:11px;
            background:var(--paper-2); font-family:inherit; font-size:14px; color:var(--ink);
        }
        .go{
            border:none; cursor:pointer; padding:13px 30px; border-radius:11px; color:#fff;
            background:var(--emerald); font-family:'Reem Kufi'; font-weight:600; font-size:16px;
            box-shadow:0 10px 22px -10px var(--emerald-deep); transition:.2s; white-space:nowrap;
        }
        .go:hover{background:var(--emerald-deep); transform:translateY(-1px)}
        .alert{background:var(--rust-tint); color:var(--rust); border:1px solid var(--rust);
            border-radius:11px; padding:12px 16px; margin-bottom:18px; font-size:14px}

        /* ---- recent ---- */
        .section-title{font-family:'Reem Kufi'; font-size:16px; margin:0 0 14px; color:var(--ink)}
        .ledger{background:var(--card); border:1px solid var(--line); border-radius:var(--radius);
            overflow:hidden; box-shadow:var(--shadow)}
        .row{display:grid; grid-template-columns:48px 1fr auto auto auto 70px; gap:14px; align-items:center;
            padding:14px 20px; border-bottom:1px solid var(--line); transition:background .15s}
        .row:last-child{border-bottom:none}
        .row:hover{background:var(--paper-2)}
        .row .id{font-family:'Reem Kufi'; color:var(--ink-soft)}
        .row .name{font-weight:500; overflow:hidden; text-overflow:ellipsis; white-space:nowrap}
        .pill{font-size:11px; padding:4px 10px; border-radius:999px; font-weight:600; white-space:nowrap}
        .pill.done{background:var(--emerald-tint); color:var(--emerald-deep)}
        .pill.failed{background:var(--rust-tint); color:var(--rust)}
        .pill.proc{background:var(--amber-tint); color:var(--amber)}
        .pill.count{background:#EFEADD; color:var(--ink-soft)}
        .row .total{font-weight:700; color:var(--emerald-deep); font-variant-numeric:tabular-nums}
        .row .view{color:var(--emerald); text-decoration:none; font-size:13px; font-weight:600; text-align:left}
        .row .view:hover{text-decoration:underline}
        .empty{padding:30px; text-align:center; color:var(--ink-soft)}

        /* ---- entrance motion ---- */
        @keyframes rise{from{opacity:0; transform:translateY(14px)}to{opacity:1; transform:none}}
        .rise{animation:rise .6s cubic-bezier(.2,.7,.2,1) both}
        .d1{animation-delay:.05s}.d2{animation-delay:.13s}.d3{animation-delay:.22s}
        .row{animation:rise .5s both} /* stagger set in JS via style */

        /* ---- scanning overlay ---- */
        .overlay{position:fixed; inset:0; z-index:50; display:none; place-items:center;
            background:rgba(35,32,26,.55); backdrop-filter:blur(4px)}
        .overlay.on{display:grid}
        .scanner{width:160px; height:200px; background:#fff; border-radius:10px; position:relative;
            box-shadow:0 30px 60px -20px rgba(0,0,0,.5); overflow:hidden; padding:22px 18px}
        .scanner .ln{height:6px; border-radius:3px; background:var(--line); margin-bottom:12px}
        .scanner .ln:nth-child(2){width:70%}.scanner .ln:nth-child(4){width:85%}.scanner .ln:nth-child(5){width:60%}
        .scanner .beam{position:absolute; left:0; right:0; height:38px; top:-38px;
            background:linear-gradient(180deg,transparent,rgba(14,107,79,.35),transparent);
            box-shadow:0 0 18px 4px rgba(14,107,79,.4); animation:scan 1.5s ease-in-out infinite}
        @keyframes scan{0%{top:-10%}100%{top:100%}}
        .overlay .label{color:#fff; font-family:'Reem Kufi'; font-size:18px; margin-top:24px; text-align:center}
        .overlay .label small{display:block; opacity:.8; font-size:13px; font-family:'IBM Plex Sans Arabic'; margin-top:6px}
    </style>
</head>
<body>
<div class="wrap">
    <header class="top rise d1">
        <div class="brand">
            <div class="seal">ف</div>
            <div>
                <h1>مكتب الفواتير</h1>
                <p>استخراج بيانات الفواتير الضريبية عبر الذكاء الاصطناعي</p>
            </div>
        </div>
        <div class="stats">
            <div class="chip"><b class="count" data-to="{{ $totalInvoices }}">0</b><span>فاتورة مستخرجة</span></div>
            <div class="chip"><b class="count" data-to="{{ $totalBatches }}">0</b><span>دفعة</span></div>
        </div>
    </header>

    @if (session('err'))<div class="alert rise d2">{{ session('err') }}</div>@endif

    <form id="up" class="panel rise d2" method="post" action="/local-invoices" enctype="multipart/form-data">
        @csrf
        <h2>رفع فاتورة</h2>
        <p class="sub">اسحب ملف PDF وأفلته هنا، أو انقر للاختيار. تُقرأ كل فاتورة على حدة وتظهر صورتها مرفقة.</p>

        <label class="drop" id="drop">
            <div class="doc"></div>
            <div class="big">أفلت ملف الفاتورة (PDF) هنا</div>
            <div class="hint">أو انقر للاختيار — حتى 50 ميغابايت</div>
            <div class="fname" id="fname"></div>
            <input type="file" name="pdf" id="pdf" accept="application/pdf" required>
        </label>

        <div class="controls">
            <div class="field">
                <label>وضع الاستخراج</label>
                <select name="mode">
                    <option value="split" selected>صفحة = فاتورة (الأفضل)</option>
                    <option value="grouped">صفحة بصفحة + تجميع</option>
                    <option value="whole">المستند كامل</option>
                </select>
            </div>
            <button type="submit" class="go">استخرج الآن ←</button>
        </div>
    </form>

    <h3 class="section-title rise d3">آخر الدفعات</h3>
    <div class="ledger rise d3">
        @forelse ($batches as $b)
            <a class="row" href="/local-invoices/{{ $b->id }}" style="text-decoration:none;color:inherit">
                <span class="id">#{{ $b->id }}</span>
                <span class="name">{{ $b->original_filename }}</span>
                <span class="pill count">{{ $b->invoices_count }} فاتورة</span>
                <span class="pill {{ $b->status == 'done' ? 'done' : ($b->status == 'failed' ? 'failed' : 'proc') }}">{{ $b->status }}</span>
                <span class="total">{{ number_format((float) $b->grand_total, 2) }}</span>
                <span class="view">عرض ←</span>
            </a>
        @empty
            <div class="empty">لا توجد دفعات بعد — ارفع أول فاتورة بالأعلى.</div>
        @endforelse
    </div>
</div>

<div class="overlay" id="ov">
    <div>
        <div class="scanner">
            <div class="beam"></div>
            <div class="ln"></div><div class="ln"></div><div class="ln"></div><div class="ln"></div><div class="ln"></div>
        </div>
        <div class="label">جارٍ قراءة الفاتورة…<small>الذكاء الاصطناعي يستخرج البيانات</small></div>
    </div>
</div>

<script>
    // Count-up stats (guaranteed final value even if rAF is throttled in a bg tab)
    document.querySelectorAll('.count').forEach(function (el) {
        var to = +el.dataset.to || 0, t0 = null, dur = 900, done = false;
        function finish(){ if (!done) { done = true; el.textContent = to; } }
        if (!to) { el.textContent = '0'; return; }
        requestAnimationFrame(function step(ts) {
            if (done) return; t0 = t0 || ts; var p = Math.min((ts - t0) / dur, 1);
            el.textContent = Math.round((1 - Math.pow(1 - p, 3)) * to);
            if (p < 1) requestAnimationFrame(step); else finish();
        });
        setTimeout(finish, 1400);
    });
    // Stagger recent rows
    document.querySelectorAll('.ledger .row').forEach(function (r, i) { r.style.animationDelay = (0.28 + i * 0.05) + 's'; });

    // Dropzone interactions
    var drop = document.getElementById('drop'), pdf = document.getElementById('pdf'), fname = document.getElementById('fname');
    pdf.addEventListener('change', function () { fname.textContent = pdf.files[0] ? '📄 ' + pdf.files[0].name : ''; });
    ['dragenter', 'dragover'].forEach(e => drop.addEventListener(e, ev => { ev.preventDefault(); drop.classList.add('drag'); }));
    ['dragleave', 'drop'].forEach(e => drop.addEventListener(e, ev => { ev.preventDefault(); drop.classList.remove('drag'); }));
    drop.addEventListener('drop', function (ev) { if (ev.dataTransfer.files.length) { pdf.files = ev.dataTransfer.files; fname.textContent = '📄 ' + ev.dataTransfer.files[0].name; } });

    // Scanning overlay while the (synchronous) extraction runs
    document.getElementById('up').addEventListener('submit', function (e) {
        if (!pdf.files.length) return;
        document.getElementById('ov').classList.add('on');
    });
</script>
</body>
</html>
