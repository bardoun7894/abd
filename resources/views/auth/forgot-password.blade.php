<x-guest-layout>
    <div class="sn-auth__brandmark sn-anim">
        <svg class="sn-mark"><use href="#sn-logo-mark"/></svg>
        <div>
            <b>شركة صباح النور</b>
            <span>Sabah Alnoor Co.</span>
        </div>
    </div>

    <h1 class="sn-auth__title sn-anim d1">نسيت كلمة المرور؟</h1>
    <p class="sn-auth__subtitle sn-anim d1">لا مشكلة. أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.</p>

    @if (session('status'))
        <div class="sn-auth__status sn-anim d2">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="sn-auth__error sn-shake sn-anim d2">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" data-sn-indicator class="w-100">
        @csrf

        <div class="sn-field sn-anim d3">
            <label for="email">البريد الإلكتروني</label>
            <div class="sn-input">
                <span class="sn-input__icon" aria-hidden="true"><i class="fs-4 bi bi-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       placeholder="أدخل بريدك الإلكتروني" required autofocus autocomplete="username" dir="ltr" />
            </div>
        </div>

        <button type="submit" class="sn-btn sn-anim d4">
            <span class="indicator-label">إرسال رابط إعادة التعيين</span>
            <span class="indicator-progress">انتظر... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </form>

    <div class="sn-auth__support sn-anim d5">
        <a href="{{ route('login') }}"><i class="bi bi-arrow-right ms-1"></i> العودة لتسجيل الدخول</a>
    </div>
</x-guest-layout>
