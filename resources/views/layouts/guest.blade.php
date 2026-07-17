<!DOCTYPE html>
<html lang="ar" dir="rtl">
	<head><base href="../../../">
		<title>شركة صباح النور — تسجيل الدخول</title>
		<meta name="description" content="النظام المالي لشركة صباح النور" />
		<meta name="keywords" content="شركة صباح النور" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta charset="utf-8" />
		<meta property="og:locale" content="ar_SA" />
		<meta property="og:type" content="website" />
		<meta property="og:title" content="شركة صباح النور" />
		<meta property="og:site_name" content="شركة صباح النور" />
		<link rel="shortcut icon" href="{{asset('assets/media/logos/logo.png')}}" />
		{{-- RTL bundles (previously loaded the LTR bundle under an RTL font — the login layout was visibly flipped). --}}
		<link href="{{asset('assets/plugins/global/plugins.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{asset('assets/css/style.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{asset('assets/fonts/dinnext/styles.rtl.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{asset('css/app-ui.css')}}?v={{ config('global.ver.version_css') }}" rel="stylesheet" type="text/css" />
		<link href="{{asset('css/auth.css')}}?v={{ config('global.ver.version_css') }}" rel="stylesheet" type="text/css" />
	</head>
	<body id="kt_body">

	{{-- Reusable brand mark: an animated wheat-sunrise placeholder in brand emerald/gold.
	     When the real Sabah Alnoor logo asset lands, swap the two <symbol>s below (or drop
	     an <img>) — nothing else in the layout depends on this markup. --}}
	<svg width="0" height="0" style="position:absolute" aria-hidden="true">
		<symbol id="sn-logo-mark" viewBox="0 0 64 64">
			<circle cx="32" cy="30" r="13" fill="#F0AA3C"/>
			@for ($i = 0; $i < 12; $i++)
				<rect x="31" y="2" width="2" height="8" rx="1" fill="#F0AA3C"
				      transform="rotate({{ $i * 30 }} 32 30)"/>
			@endfor
			<path d="M14 60 Q32 46 50 60" stroke="#0E6B4F" stroke-width="4" fill="none" stroke-linecap="round"/>
			<path d="M32 58 V40 M32 44 l-6 -5 M32 44 l6 -5 M32 50 l-6 -5 M32 50 l6 -5"
			      stroke="#C19A45" stroke-width="2.4" fill="none" stroke-linecap="round"/>
		</symbol>
		<symbol id="sn-wheat" viewBox="0 0 40 120">
			<path d="M20 120 V30" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
			@for ($i = 0; $i < 7; $i++)
				<g transform="translate(0 {{ 34 + $i * 11 }})">
					<path d="M20 0 q-11 4 -13 13 q11 -1 13 -8" fill="currentColor"/>
					<path d="M20 0 q11 4 13 13 q-11 -1 -13 -8" fill="currentColor"/>
				</g>
			@endfor
			<path d="M20 26 q-2 -12 -6 -18 q7 3 6 14" fill="currentColor"/>
		</symbol>
	</svg>

	<div class="sn-auth">
		{{-- FORM COLUMN (start / visual right in RTL) --}}
		<main class="sn-auth__form">
			<div class="sn-auth__inner">
				{{ $slot }}
			</div>
		</main>

		{{-- BRAND / ART COLUMN (end / visual left in RTL) — an animated dawn over the fields --}}
		<aside class="sn-auth__art" aria-hidden="true">
			<div class="sn-sun"></div>

			{{-- layered rolling fields --}}
			<svg class="sn-hills sn-hills--back" viewBox="0 0 500 190" preserveAspectRatio="none">
				<path d="M0 90 Q120 40 250 78 T500 60 V190 H0 Z" fill="currentColor"/>
			</svg>
			<svg class="sn-hills sn-hills--front" viewBox="0 0 500 130" preserveAspectRatio="none">
				<path d="M0 74 Q150 30 300 66 T500 74 V130 H0 Z" fill="currentColor"/>
			</svg>

			{{-- wheat standing in the field --}}
			<svg class="sn-wheat sn-wheat--2" viewBox="0 0 40 120"><use href="#sn-wheat"/></svg>
			<svg class="sn-wheat sn-wheat--1" viewBox="0 0 40 120"><use href="#sn-wheat"/></svg>
			<svg class="sn-wheat sn-wheat--3" viewBox="0 0 40 120"><use href="#sn-wheat"/></svg>

			<div class="sn-auth__art-content">
				<div class="sn-auth__art-logo">
					<svg class="sn-mark-lg" viewBox="0 0 64 64"><use href="#sn-logo-mark"/></svg>
				</div>
				<h2>نظام إدارة أعمالك<br>في مكان واحد</h2>
				<p>تابع أعمالك المالية والإدارية بسهولة وأمان — من الفواتير إلى العمّال والمصاريف والتقارير.</p>
			</div>
		</aside>
	</div>

	{{-- The auth pages are self-contained and deliberately do NOT load the full Metronic
	     JS bundle (scripts.bundle.js auto-inits drawers/menus against body scaffold that
	     this minimal layout doesn't have — it threw a console error and shipped ~500KB for
	     nothing). Native HTML5 validation + this tiny handler cover everything the forms need. --}}
	{{-- NOTE: an HTML minifier collapses this block onto ONE line, so `//` line comments
	     would comment out the whole script. Use /* */ block comments only. --}}
	<script>
		/* submit loading indicator (pure-CSS indicator-label/progress toggled by data-kt-indicator) */
		document.querySelectorAll('form[data-sn-indicator]').forEach(function (f) {
			f.addEventListener('submit', function () {
				var b = f.querySelector('[type="submit"]');
				if (b && f.checkValidity()) { b.setAttribute('data-kt-indicator', 'on'); b.disabled = true; }
			});
		});
		/* password reveal toggle (progressive enhancement) */
		document.querySelectorAll('[data-sn-reveal]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var input = document.getElementById(btn.getAttribute('data-sn-reveal'));
				if (!input) return;
				var icon = btn.querySelector('i');
				var show = input.type === 'password';
				input.type = show ? 'text' : 'password';
				if (icon) { icon.classList.toggle('bi-eye', !show); icon.classList.toggle('bi-eye-slash', show); }
			});
		});
	</script>
</body>
</html>
