<x-guest-layout>
    <div class="sn-auth__brandmark sn-anim">
        <svg class="sn-mark"><use href="#sn-logo-mark"/></svg>
        <div>
            <b>شركة صباح النور</b>
            <span>Sabah Alnoor Co.</span>
        </div>
    </div>

    <h1 class="sn-auth__title sn-anim d1">تأكيد كلمة المرور</h1>
    <p class="sn-auth__subtitle sn-anim d1">هذه منطقة محمية. الرجاء تأكيد كلمة المرور للمتابعة.</p>

    @if ($errors->any())
        <div class="sn-auth__error sn-shake sn-anim d2">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" data-sn-indicator class="w-100">
        @csrf
        <div class="sn-field sn-anim d2">
            <label for="password">كلمة المرور</label>
            <div class="sn-input">
                <span class="sn-input__icon" aria-hidden="true"><i class="fs-4 bi bi-lock"></i></span>
                <input id="password" type="password" name="password" placeholder="أدخل كلمة المرور"
                       required autocomplete="current-password" />
                <button type="button" class="sn-input__reveal" aria-label="إظهار كلمة المرور" data-sn-reveal="password">
                    <i class="fs-4 bi bi-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="sn-btn sn-anim d3">
            <span class="indicator-label">تأكيد</span>
            <span class="indicator-progress">انتظر... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </form>
</x-guest-layout>
