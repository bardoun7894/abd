<!DOCTYPE html>
<html lang="ar" dir="rtl">
	<head><base href="../../../">
		<title>{{ config('brand.name_ar') }} — تسجيل الدخول</title>
		<meta name="description" content="النظام المالي ل{{ config('brand.name_ar') }}" />
		<meta name="keywords" content="{{ config('brand.name_ar') }}" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta charset="utf-8" />
		<meta property="og:locale" content="ar_SA" />
		<meta property="og:type" content="website" />
		<meta property="og:title" content="{{ config('brand.name_ar') }}" />
		<meta property="og:site_name" content="{{ config('brand.name_ar') }}" />
		<link rel="shortcut icon" href="{{asset('assets/media/logos/logo.png')}}" />
		{{-- RTL bundles (previously loaded the LTR bundle under an RTL font — the login layout was visibly flipped). --}}
		<link href="{{asset('assets/plugins/global/plugins.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{asset('assets/css/style.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{asset('assets/fonts/dinnext/styles.rtl.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{asset('css/app-ui.css')}}?v={{ config('global.ver.version_css') }}" rel="stylesheet" type="text/css" />
		{{-- Per-instance brand theme override — see layouts/app.blade.php. --}}
		@if (config('brand.theme') && config('brand.theme') !== 'noor-blue')
		<link href="{{asset('css/brand/'.config('brand.theme').'.css')}}?v={{ config('global.ver.version_css') }}" rel="stylesheet" type="text/css" />
		@endif
		<link href="{{asset('css/auth.css')}}?v={{ config('global.ver.version_css') }}" rel="stylesheet" type="text/css" />
	</head>
	<body id="kt_body">

	{{-- Reusable brand mark, referenced as <use href="#sn-logo-mark"/> by every auth page.
	     Defined per theme so the small mark matches the company that owns the instance:
	       sabah-emerald → wheat + sunrise (agricultural mark)
	       noor-blue     → rayed sun + green growth arc (traced from the نور الصباح logo) --}}
	<svg width="0" height="0" style="position:absolute" aria-hidden="true">
		@if (config('brand.theme') === 'sabah-emerald')
		<symbol id="sn-logo-mark" viewBox="0 0 64 64">
			<circle cx="32" cy="30" r="13" fill="#F0AA3C"/>
			@for ($i = 0; $i < 12; $i++)
				<rect x="31" y="2" width="2" height="8" rx="1" fill="#F0AA3C"
				      transform="rotate({{ $i * 30 }} 32 30)"/>
			@endfor
			<path d="M14 60 Q32 46 50 60" stroke="var(--sn-emerald)" stroke-width="4" fill="none" stroke-linecap="round"/>
			<path d="M32 58 V40 M32 44 l-6 -5 M32 44 l6 -5 M32 50 l-6 -5 M32 50 l6 -5"
			      stroke="#C19A45" stroke-width="2.4" fill="none" stroke-linecap="round"/>
		</symbol>
		@else
		{{-- The REAL company logo file, not a drawn stand-in. Wrapped in the same
		     <symbol> id so all six auth pages keep working unchanged — they each
		     reference <use href="#sn-logo-mark"/>. preserveAspectRatio keeps the
		     wordmark undistorted whatever box it is dropped into. --}}
		<symbol id="sn-logo-mark" viewBox="0 0 64 64">
			<image href="{{ asset(config('brand.logo')) }}" x="0" y="0" width="64" height="64"
			       preserveAspectRatio="xMidYMid meet"/>
		</symbol>
		@endif
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

		{{-- BRAND / ART COLUMN (end / visual left in RTL).
		     Two scenes, chosen by the active brand theme. CSS alone can't do this:
		     the shapes themselves differ, not just their colour.
		       sabah-emerald → dawn over WHEAT FIELDS (صباح النور's agricultural mark)
		       noor-blue     → dawn over WATER (نور الصباح's sun-rays + growth-arc mark) --}}
		@if (config('brand.theme') === 'sabah-emerald')
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
		@else
		<aside class="sn-auth__art sn-auth__art--noor" aria-hidden="true">
			{{-- the sun: disc + 12 radiating rays, mirroring the logo mark --}}
			<div class="sn-dawn">
				<svg class="sn-rays" viewBox="0 0 200 200">
					@for ($i = 0; $i < 12; $i++)
						<rect x="98" y="10" width="4" height="26" rx="2"
						      transform="rotate({{ $i * 30 }} 100 100)"/>
					@endfor
				</svg>
				<div class="sn-disc"></div>
			</div>

			{{-- the growth arc — the green leaf/arrow that sweeps up through the logo --}}
			<svg class="sn-growth" viewBox="0 0 200 200" fill="none">
				<path d="M18 176 C42 96 96 44 176 30" stroke="currentColor" stroke-width="11"
				      stroke-linecap="round"/>
				<path d="M176 30 L142 30 M176 30 L176 64" stroke="currentColor" stroke-width="11"
				      stroke-linecap="round" stroke-linejoin="round"/>
			</svg>

			{{-- layered water --}}
			<svg class="sn-wave sn-wave--back" viewBox="0 0 500 150" preserveAspectRatio="none">
				<path d="M0 62 Q125 22 250 62 T500 62 V150 H0 Z" fill="currentColor"/>
			</svg>
			<svg class="sn-wave sn-wave--mid" viewBox="0 0 500 120" preserveAspectRatio="none">
				<path d="M0 54 Q125 96 250 54 T500 54 V120 H0 Z" fill="currentColor"/>
			</svg>
			<svg class="sn-wave sn-wave--front" viewBox="0 0 500 90" preserveAspectRatio="none">
				<path d="M0 40 Q125 8 250 40 T500 40 V90 H0 Z" fill="currentColor"/>
			</svg>

			<div class="sn-auth__art-content">
				<h2>نظام إدارة أعمالك<br>في مكان واحد</h2>
				<p>تابع أعمالك المالية والإدارية بسهولة وأمان — من الفواتير إلى العمّال والمصاريف والتقارير.</p>
			</div>
		</aside>
		@endif
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
