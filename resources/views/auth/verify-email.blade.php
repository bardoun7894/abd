<x-guest-layout>
    <div class="sn-auth__brandmark sn-anim">
        <svg class="sn-mark"><use href="#sn-logo-mark"/></svg>
        <div>
            <b>شركة صباح النور</b>
            <span>Sabah Alnoor Co.</span>
        </div>
    </div>

    <h1 class="sn-auth__title sn-anim d1">تأكيد البريد الإلكتروني</h1>
    <p class="sn-auth__subtitle sn-anim d1">شكراً لتسجيلك! قبل البدء، الرجاء تأكيد بريدك الإلكتروني عبر الرابط الذي أرسلناه إليك. إن لم يصلك، يمكننا إرسال رابط جديد.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="sn-auth__status sn-anim d2">تم إرسال رابط تأكيد جديد إلى بريدك الإلكتروني.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" data-sn-indicator class="w-100">
        @csrf
        <button type="submit" class="sn-btn sn-anim d2">
            <span class="indicator-label">إعادة إرسال رابط التأكيد</span>
            <span class="indicator-progress">انتظر... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="sn-auth__support sn-anim d3">
        @csrf
        <button type="submit" class="btn btn-link p-0 text-decoration-none" style="color:var(--sn-emerald);font-weight:600">
            تسجيل الخروج
        </button>
    </form>
</x-guest-layout>
