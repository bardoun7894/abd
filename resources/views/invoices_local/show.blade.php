<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الدفعة #{{ $batch->id }} · النتائج</title>
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
            font-family:'IBM Plex Sans Arabic',sans-serif; color:var(--ink); background:var(--paper);
            background-image:linear-gradient(var(--line) 1px,transparent 1px),linear-gradient(90deg,var(--line) 1px,transparent 1px);
            background-size:38px 38px; min-height:100vh; padding:40px 20px 80px; position:relative;
        }
        body::before{content:""; position:fixed; inset:0; pointer-events:none; z-index:0;
            background:radial-gradient(120% 80% at 50% -10%,rgba(255,255,255,.55),transparent 60%)}
        .wrap{max-width:1080px; margin:0 auto; position:relative; z-index:1}
        @keyframes rise{from{opacity:0; transform:translateY(16px)}to{opacity:1; transform:none}}
        .rise{animation:rise .6s cubic-bezier(.2,.7,.2,1) both}

        /* header */
        .top{display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:22px; flex-wrap:wrap}
        .top .lhs{display:flex; align-items:center; gap:14px}
        .back{width:42px; height:42px; border-radius:12px; border:1px solid var(--line); background:var(--card);
            display:grid; place-items:center; text-decoration:none; color:var(--ink); font-size:20px; box-shadow:var(--shadow)}
        .back:hover{background:var(--paper-2)}
        .top h1{font-family:'Reem Kufi'; font-size:24px; margin:0}
        .top .meta{color:var(--ink-soft); font-size:12px; margin-top:2px}

        /* summary strip */
        .summary{display:grid; grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); gap:12px; margin-bottom:26px}
        .stat{background:var(--card); border:1px solid var(--line); border-radius:14px; padding:14px 16px; box-shadow:var(--shadow)}
        .stat b{display:block; font-family:'Reem Kufi'; font-size:28px; line-height:1; font-variant-numeric:tabular-nums}
        .stat span{font-size:11px; color:var(--ink-soft)}
        .stat.total b{color:var(--emerald-deep)}
        .stat.dot-c b{color:var(--emerald)} .stat.dot-m b{color:var(--amber)} .stat.dot-u b{color:var(--rust)}
        .stat.rev b{color:var(--amber)}

        /* invoice cards */
        .cards{display:flex; flex-direction:column; gap:18px}
        .inv{display:grid; grid-template-columns:200px 1fr; gap:0; background:var(--card); border:1px solid var(--line);
            border-radius:var(--radius); overflow:hidden; box-shadow:var(--shadow); position:relative;
            border-right:5px solid var(--emerald)}
        .inv.review{border-right-color:var(--amber)}
        .inv.failed{border-right-color:var(--rust)}
        /* scan image */
        .scan{position:relative; background:#efeadf; overflow:hidden; min-height:240px; display:block}
        .scan img{width:100%; height:100%; object-fit:cover; display:block; min-height:240px; max-height:340px}
        .scan .noimg{display:grid; place-items:center; height:100%; color:var(--ink-soft); font-size:13px; padding:20px; text-align:center}
        .scan::after{content:""; position:absolute; left:0; right:0; height:46px; top:-46px;
            background:linear-gradient(180deg,transparent,rgba(14,107,79,.4),transparent);
            box-shadow:0 0 22px 6px rgba(14,107,79,.35); animation:sweep 1.4s ease-out 1 forwards}
        @keyframes sweep{0%{top:-12%}100%{top:112%; opacity:0}}
        .scan .pageno{position:absolute; top:10px; right:10px; background:rgba(35,32,26,.78); color:#fff;
            font-family:'Reem Kufi'; font-size:12px; padding:3px 10px; border-radius:8px}

        /* body */
        .body{padding:18px 20px}
        .hd{display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:14px}
        .badge{font-size:11px; font-weight:600; padding:4px 11px; border-radius:999px; display:inline-flex; gap:5px; align-items:center}
        .b-clear{background:var(--emerald-tint); color:var(--emerald-deep)}
        .b-medium{background:var(--amber-tint); color:var(--amber)}
        .b-unclear{background:var(--rust-tint); color:var(--rust)}
        .b-na{background:#EFEADD; color:var(--ink-soft)}
        .b-ok{background:var(--emerald-tint); color:var(--emerald-deep)}
        .b-rev{background:var(--amber-tint); color:var(--amber)}
        .b-fail{background:var(--rust-tint); color:var(--rust)}
        .stamp{margin-inline-start:auto; transform:rotate(-9deg); border:2px solid var(--emerald); color:var(--emerald);
            font-family:'Reem Kufi'; font-size:12px; padding:3px 12px; border-radius:8px; opacity:.9;
            animation:stamp .5s cubic-bezier(.2,1.5,.4,1) both}
        @keyframes stamp{from{opacity:0; transform:rotate(-9deg) scale(2)}to{opacity:.9; transform:rotate(-9deg) scale(1)}}

        .grid{display:grid; grid-template-columns:repeat(2,1fr); gap:12px 22px}
        .f{min-width:0}
        .f label{display:block; font-size:11px; color:var(--ink-soft); margin-bottom:2px}
        .f .v{font-size:15px; font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-variant-numeric:tabular-nums}
        .f.amount .v{color:var(--emerald-deep)}
        .f.grand{grid-column:1/-1; border-top:1px dashed var(--line); padding-top:10px; margin-top:2px}
        .f.grand .v{font-family:'Reem Kufi'; font-size:22px; color:var(--emerald-deep)}

        /* AI analysis */
        .ai{margin-top:14px; padding-top:14px; border-top:1px solid var(--line)}
        .ai .row1{display:flex; align-items:center; gap:10px; font-size:12px; color:var(--ink-soft)}
        .conf{flex:1; height:7px; background:#ECE6D8; border-radius:99px; overflow:hidden; max-width:200px}
        .conf > i{display:block; height:100%; background:linear-gradient(90deg,var(--emerald),var(--emerald-deep)); border-radius:99px;
            width:0; transition:width 1s ease .3s}
        .notes{margin-top:10px; display:flex; flex-wrap:wrap; gap:6px}
        .note{font-size:11px; background:var(--amber-tint); color:var(--amber); border:1px solid #E9D9AE;
            padding:4px 9px; border-radius:8px}
        .note.dup{background:var(--rust-tint); color:var(--rust); border-color:#E6C3BA}
        .clean{font-size:12px; color:var(--emerald-deep)}

        /* lightbox */
        .lb{position:fixed; inset:0; background:rgba(35,32,26,.85); display:none; place-items:center; z-index:60; padding:30px}
        .lb.on{display:grid}
        .lb img{max-width:92vw; max-height:92vh; border-radius:8px; box-shadow:0 30px 80px -20px #000}
        @media(max-width:640px){.inv{grid-template-columns:1fr}.scan img{max-height:260px}}
    </style>
</head>
<body>
@php
    $clear   = $invoices->where('image_quality','clear')->count();
    $medium  = $invoices->where('image_quality','medium')->count();
    $unclear = $invoices->where('image_quality','unclear')->count();
    $flagged = $invoices->where('needs_review',true)->count();
    $pushed  = $invoices->whereNotNull('purchase_id')->count();
@endphp
<div class="wrap">
    <header class="top rise">
        <div class="lhs">
            <a class="back" href="/local-invoices" title="رجوع">→</a>
            <div>
                <h1>الدفعة #{{ $batch->id }}</h1>
                <div class="meta">
                    {{ $batch->original_filename }} · {{ $batch->model_used }} ·
                    {{ $batch->input_tokens }}+{{ $batch->output_tokens }} توكن ·
                    ≈ ${{ number_format((float)$batch->est_cost_usd,4) }} ({{ number_format($sar,3) }} ر.س)
                </div>
            </div>
        </div>
    </header>

    <div class="summary rise">
        <div class="stat total"><b class="count" data-to="{{ $invoices->count() }}">0</b><span>عدد الفواتير</span></div>
        <div class="stat"><b class="count money" data-to="{{ (float)$batch->grand_total }}">0</b><span>الإجمالي العام (ر.س)</span></div>
        <div class="stat dot-c"><b class="count" data-to="{{ $clear }}">0</b><span>واضحة</span></div>
        <div class="stat dot-m"><b class="count" data-to="{{ $medium }}">0</b><span>متوسطة</span></div>
        <div class="stat dot-u"><b class="count" data-to="{{ $unclear }}">0</b><span>غير واضحة</span></div>
        <div class="stat rev"><b class="count" data-to="{{ $flagged }}">0</b><span>تحتاج مراجعة</span></div>
        @if ($pushed)<div class="stat"><b class="count" data-to="{{ $pushed }}">0</b><span>مُرحّلة للمشتريات</span></div>@endif
    </div>

    <div class="cards">
        @forelse ($invoices as $v)
            @php
                $cls = $v->status==='failed' ? 'failed' : ($v->needs_review ? 'review' : '');
                $q = $v->image_quality;
                $href = $v->image_path ? '/'.ltrim($v->image_path,'/') : null;
                $isImg = $href && preg_match('/\.(png|jpe?g|webp|gif)(\?|#|$)/i',$v->image_path);
                $notes = array_filter(array_map('trim', explode('|', (string)$v->validation_notes)));
                $conf = $v->confidence !== null ? round(((float)$v->confidence)*100) : null;
            @endphp
            <article class="inv {{ $cls }} rise" style="animation-delay:{{ 0.1 + $loop->index*0.07 }}s">
                <div class="scan">
                    @if ($isImg)
                        <img src="{{ $href }}" loading="lazy" data-full="{{ $href }}" class="lbtrig" alt="فاتورة {{ $v->page_number }}">
                    @elseif ($href)
                        <div class="noimg"><a href="{{ $href }}" target="_blank" style="color:var(--emerald);font-weight:600">عرض المرفق ←</a></div>
                    @else
                        <div class="noimg">لا توجد صورة</div>
                    @endif
                    <span class="pageno">صفحة {{ $v->page_number }}</span>
                </div>
                <div class="body">
                    <div class="hd">
                        @if ($v->status==='failed')
                            <span class="badge b-fail">✗ فشل</span>
                        @elseif ($v->needs_review)
                            <span class="badge b-rev">⚠ تحتاج مراجعة</span>
                        @else
                            <span class="badge b-ok">✓ مكتملة</span>
                        @endif
                        <span class="badge {{ $q=='clear'?'b-clear':($q=='medium'?'b-medium':($q=='unclear'?'b-unclear':'b-na')) }}">
                            {{ $q=='clear'?'صورة واضحة':($q=='medium'?'صورة متوسطة':($q=='unclear'?'صورة غير واضحة':'—')) }}
                        </span>
                        @if ($v->purchase_id)<span class="stamp">مُرحّلة #{{ $v->purchase_id }}</span>@endif
                    </div>

                    <div class="grid">
                        <div class="f"><label>المورد</label><div class="v" title="{{ $v->supplier_name }}">{{ $v->supplier_name ?: '—' }}</div></div>
                        <div class="f"><label>الرقم الضريبي</label><div class="v">{{ $v->supplier_tax_number ?: '—' }}</div></div>
                        <div class="f"><label>رقم الفاتورة</label><div class="v">{{ $v->invoice_number ?: '—' }}</div></div>
                        <div class="f"><label>التاريخ</label><div class="v">{{ optional($v->invoice_date)->format('Y-m-d') ?: '—' }}</div></div>
                        <div class="f amount"><label>قبل الضريبة</label><div class="v">{{ $v->amount_before_vat ?? '—' }}</div></div>
                        <div class="f amount"><label>الضريبة</label><div class="v">{{ $v->vat_amount ?? '—' }}</div></div>
                        <div class="f grand"><label>الإجمالي شامل الضريبة</label><div class="v">{{ $v->total_incl_vat ?? '—' }}</div></div>
                    </div>

                    <div class="ai">
                        <div class="row1">
                            <span>تحليل الذكاء الاصطناعي</span>
                            @if ($conf !== null)
                                <span class="conf"><i style="--w:{{ $conf }}%"></i></span><span>{{ $conf }}% ثقة</span>
                            @endif
                        </div>
                        @if (count($notes))
                            <div class="notes">
                                @foreach ($notes as $n)
                                    <span class="note {{ str_contains($n,'مكرر') ? 'dup' : '' }}">{{ $n }}</span>
                                @endforeach
                            </div>
                        @else
                            <div class="notes"><span class="clean">✓ لا ملاحظات — البيانات متّسقة</span></div>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="stat" style="text-align:center; padding:40px">لا توجد بيانات في هذه الدفعة.</div>
        @endforelse
    </div>
</div>

<div class="lb" id="lb"><img id="lbimg" src=""></div>

<script>
    // Count-ups (integers + money) — guaranteed to land on the final value even if
    // requestAnimationFrame is throttled (e.g. the tab is in the background).
    document.querySelectorAll('.count').forEach(function (el) {
        var to = parseFloat(el.dataset.to) || 0, money = el.classList.contains('money'), t0 = null, dur = 1000, done = false;
        var fmt = function (v) { return money ? v.toLocaleString('en', {minimumFractionDigits:2, maximumFractionDigits:2}) : Math.round(v); };
        function finish(){ if (!done) { done = true; el.textContent = fmt(to); } }
        if (!to) { el.textContent = fmt(0); return; }
        requestAnimationFrame(function step(ts){ if (done) return; t0 = t0 || ts; var p = Math.min((ts-t0)/dur,1);
            el.textContent = fmt((1-Math.pow(1-p,3))*to); if (p<1) requestAnimationFrame(step); else finish(); });
        setTimeout(finish, 1500); // fallback when rAF is paused
    });
    // Confidence bars (setTimeout so it fires even when backgrounded)
    setTimeout(function(){ document.querySelectorAll('.conf > i').forEach(function(i){ i.style.width = getComputedStyle(i).getPropertyValue('--w'); }); }, 60);
    // Lightbox
    var lb = document.getElementById('lb'), lbimg = document.getElementById('lbimg');
    document.querySelectorAll('.lbtrig').forEach(im=>im.addEventListener('click',()=>{ lbimg.src=im.dataset.full; lb.classList.add('on'); }));
    lb.addEventListener('click',()=>lb.classList.remove('on'));
</script>
</body>
</html>
