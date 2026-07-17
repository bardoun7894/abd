<x-guest-layout>
    <div class="sn-auth__brandmark sn-anim">
        <svg class="sn-mark"><use href="#sn-logo-mark"/></svg>
        <div>
            <b>شركة صباح النور</b>
            <span>Sabah Alnoor Co.</span>
        </div>
    </div>

    <h1 class="sn-auth__title sn-anim d1">إعادة تعيين كلمة المرور</h1>
    <p class="sn-auth__subtitle sn-anim d1">اختر كلمة مرور جديدة لحسابك.</p>

    @if ($errors->any())
        <div class="sn-auth__error sn-shake sn-anim d2">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" data-sn-indicator class="w-100">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="sn-field sn-anim d2">
            <label for="email">البريد الإلكتروني</label>
            <div class="sn-input">
                <span class="sn-input__icon" aria-hidden="true"><i class="fs-4 bi bi-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                       placeholder="أدخل بريدك الإلكتروني" required autofocus autocomplete="username" dir="ltr" />
            </div>
        </div>

        <div class="sn-field sn-anim d3">
            <label for="password">كلمة المرور الجديدة</label>
            <div class="sn-input">
                <span class="sn-input__icon" aria-hidden="true"><i class="fs-4 bi bi-lock"></i></span>
                <input id="password" type="password" name="password" placeholder="كلمة المرور الجديدة"
                       required autocomplete="new-password" />
                <button type="button" class="sn-input__reveal" aria-label="إظهار كلمة المرور" data-sn-reveal="password">
                    <i class="fs-4 bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="sn-field sn-anim d4">
            <label for="password_confirmation">تأكيد كلمة المرور</label>
            <div class="sn-input">
                <span class="sn-input__icon" aria-hidden="true"><i class="fs-4 bi bi-lock"></i></span>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       placeholder="أعد إدخال كلمة المرور" required autocomplete="new-password" />
                <button type="button" class="sn-input__reveal" aria-label="إظهار كلمة المرور" data-sn-reveal="password_confirmation">
                    <i class="fs-4 bi bi-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="sn-btn sn-anim d5">
            <span class="indicator-label">حفظ كلمة المرور</span>
            <span class="indicator-progress">انتظر... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </form>
</x-guest-layout>
