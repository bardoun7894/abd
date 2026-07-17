<x-guest-layout>

    {{-- brand mark (shows on the form side; primary identity is the art panel) --}}
    <div class="sn-auth__brandmark sn-anim">
        <svg class="sn-mark"><use href="#sn-logo-mark"/></svg>
        <div>
            <b>شركة صباح النور</b>
            <span>Sabah Alnoor Co.</span>
        </div>
    </div>

    <h1 class="sn-auth__title sn-anim d1">مرحباً بك مجدداً</h1>
    <p class="sn-auth__subtitle sn-anim d1">سجّل دخولك للوصول إلى نظام إدارة الشركة ومتابعة أعمالك بسهولة وأمان.</p>

    @if (session('status'))
        <div class="sn-auth__status sn-anim d2">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="sn-auth__error sn-shake sn-anim d2">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" data-sn-indicator class="w-100">
        @csrf

        {{-- Email --}}
        <div class="sn-field sn-anim d3">
            <label for="email">البريد الإلكتروني</label>
            <div class="sn-input">
                <span class="sn-input__icon" aria-hidden="true">
                    <i class="fs-4 bi bi-envelope"></i>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       placeholder="أدخل بريدك الإلكتروني"
                       required autofocus autocomplete="username" dir="ltr" />
            </div>
        </div>

        {{-- Password --}}
        <div class="sn-field sn-anim d4">
            <label for="password">كلمة المرور</label>
            <div class="sn-input">
                <span class="sn-input__icon" aria-hidden="true">
                    <i class="fs-4 bi bi-lock"></i>
                </span>
                <input id="password" type="password" name="password"
                       placeholder="أدخل كلمة المرور"
                       required autocomplete="current-password" />
                <button type="button" class="sn-input__reveal" aria-label="إظهار كلمة المرور"
                        data-sn-reveal="password">
                    <i class="fs-4 bi bi-eye"></i>
                </button>
            </div>
        </div>

        {{-- Remember + forgot --}}
        <div class="sn-auth__row sn-anim d5">
            <label class="sn-check">
                {{-- was name="toc" — the LoginRequest reads boolean('remember'), so remember-me never worked --}}
                <input type="checkbox" name="remember" value="1" id="remember">
                <span>تذكّرني</span>
            </label>
            @if (Route::has('password.request'))
                <a class="sn-auth__forgot" href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
            @endif
        </div>

        {{-- Submit — general.js wires #kt_sign_in_submit to FormValidation + the loading indicator --}}
        <button type="submit" id="kt_sign_in_submit" class="sn-btn sn-anim d6">
            <span class="indicator-label d-inline-flex align-items-center gap-2">
                تسجيل الدخول
                <svg class="sn-btn__arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M14 7l-5 5 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="indicator-progress">
                انتظر... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
            </span>
        </button>
    </form>

    <div class="sn-auth__support sn-anim d7">
        بحاجة إلى مساعدة؟ <a href="{{ route('tasks.index') }}">تواصل مع الدعم الفني</a>
    </div>

    {{-- feature strip --}}
    <div class="sn-features sn-anim d7">
        <div class="sn-feature">
            <span class="sn-feature__ic"><i class="fs-5 bi bi-headset"></i></span>
            <div><b>دعم فني</b><span>على مدار الساعة</span></div>
        </div>
        <div class="sn-feature">
            <span class="sn-feature__ic"><i class="fs-5 bi bi-hand-index-thumb"></i></span>
            <div><b>سهولة الاستخدام</b><span>واجهة بسيطة وعصرية</span></div>
        </div>
        <div class="sn-feature">
            <span class="sn-feature__ic"><i class="fs-5 bi bi-shield-check"></i></span>
            <div><b>أمان عالي</b><span>حماية بياناتك</span></div>
        </div>
    </div>

    <div class="sn-auth__version">
        © {{ date('Y') }} شركة صباح النور — الإصدار {{ config('global.ver.version_all') }}
    </div>
</x-guest-layout>
