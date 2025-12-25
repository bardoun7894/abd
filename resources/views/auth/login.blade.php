



<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class='form w-100 ' >
        @csrf

        <div>
            <label class="form-label fs-2 fw-bolder text-dark">الايميل</label>
            <x-text-input id="email" class="form-control form-control-lg form-control-solid" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="fv-plugins-message-container invalid-feedback" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label class="form-label fw-bolder text-dark fs-2 mb-0">كلمة المرور</label>

            <x-text-input id="password" class="form-control form-control-lg form-control-solid"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="fv-plugins-message-container invalid-feedback" />
        </div>

 <!--         <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="form-check-input" name="toc" value="1">
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>


      <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="link-primary fs-6 fw-bolder" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif


        </div>
    -->


    <div class="block mt-4">
        <label for="remember_me" class="inline-flex items-center">
            <input id="remember_me" type="checkbox" class="form-check-input" name="toc" value="1">
            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
        </label>
    </div>
    <div class="flex items-center justify-end mt-4">
        @if (Route::has('password.request'))
            <a class="link-primary fs-6 fw-bolder" href="{{ route('password.request') }}">
                {{ __('Forgot your password?') }}
            </a>
        @endif


    </div>


        <div class="text-center mt-4">
            <!--begin::Submit button-->
            <button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5">
                <span class="indicator-label">تسجيل الدخول</span>
                <span class="indicator-progress">انتظر...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        <!--    <div class="text-center text-muted text-uppercase fw-bolder mb-5">او</div>
            <a href="#" class="btn btn-flex flex-center btn-light btn-lg w-100 mb-5">
            <img alt="Logo" src="assets/media/svg/brand-logos/google-icon.svg" class="h-20px me-3">تسجيل قوقل</a>
            <a href="#" class="btn btn-flex flex-center btn-light btn-lg w-100 mb-5">
            <img alt="Logo" src="assets/media/svg/brand-logos/facebook-4.svg" class="h-20px me-3">Continue with Facebook</a>
            <a href="#" class="btn btn-flex flex-center btn-light btn-lg w-100">
            <img alt="Logo" src="assets/media/svg/brand-logos/apple-black.svg" class="h-20px me-3">Continue with Apple</a>-->
        </div>






    </form>
</x-guest-layout>
