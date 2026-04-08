@php
    $isModal = $isModal ?? false;
@endphp

<section class="auth-card {{ $isModal ? 'auth-card--overlay' : '' }}">
    <header class="auth-header">
        <h1>Create account</h1>
        <p>Create your account to submit evidence-based reports and monitor review progress.</p>
    </header>

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <input type="hidden" name="auth_form" value="register">

        <div class="auth-field">
            <x-input-label for="register_name" :value="__('Name')" class="auth-label" />
            <x-text-input id="register_name" class="auth-input" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="auth-error" />
        </div>

        <div class="auth-field">
            <x-input-label for="register_email" :value="__('Email')" class="auth-label" />
            <x-text-input id="register_email" class="auth-input" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>

        <div class="auth-field">
            <x-input-label for="register_password" :value="__('Password')" class="auth-label" />

            <x-text-input id="register_password" class="auth-input"
                          type="password"
                          name="password"
                          required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

        <div class="auth-field">
            <x-input-label for="register_password_confirmation" :value="__('Confirm Password')" class="auth-label" />

            <x-text-input id="register_password_confirmation" class="auth-input"
                          type="password"
                          name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
        </div>

        <div class="auth-row actions-row">
            @if($isModal)
                <button type="button" class="auth-link-btn" @click="authModal = 'login'">
                    {{ __('Already registered? Log in') }}
                </button>
            @else
                <a class="auth-link" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>
            @endif

            <button type="submit" class="auth-submit auth-submit--register">
                {{ __('Register') }}
            </button>
        </div>
    </form>
</section>
