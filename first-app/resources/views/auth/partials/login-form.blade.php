@php
    $isModal = $isModal ?? false;
@endphp

<section class="auth-card {{ $isModal ? 'auth-card--overlay' : '' }}">
    <header class="auth-header">
        <h1>Welcome back</h1>
        <p>Sign in to manage reports, track moderation, and follow verified updates.</p>
    </header>

    <x-auth-session-status class="auth-status" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf
        <input type="hidden" name="auth_form" value="login">

        <div class="auth-field">
            <x-input-label for="login_email" :value="__('Email')" class="auth-label" />
            <x-text-input id="login_email" class="auth-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>

        <div class="auth-field">
            <x-input-label for="login_password" :value="__('Password')" class="auth-label" />

            <x-text-input id="login_password" class="auth-input"
                          type="password"
                          name="password"
                          required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

        <div class="auth-row remember-row">
            <label for="remember_me" class="remember-label">
                <input id="remember_me" type="checkbox" class="remember-checkbox" name="remember">
                <span>{{ __('Remember me') }}</span>
            </label>

            @if($isModal)
                <button type="button" class="auth-link-btn" @click="authModal = 'register'">
                    {{ __('Need an account? Register') }}
                </button>
            @endif
        </div>

        <div class="auth-row actions-row">
            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <button type="submit" class="auth-submit auth-submit--login">
                {{ __('Log in') }}
            </button>
        </div>

        @if(! $isModal && Route::has('register'))
            <p class="auth-note auth-foot-note">
                {{ __('New here?') }}
                <a class="auth-link" href="{{ route('register') }}">{{ __('Create an account') }}</a>
            </p>
        @endif
    </form>
</section>
