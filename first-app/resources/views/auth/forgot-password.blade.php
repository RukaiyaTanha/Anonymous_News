<x-guest-layout>
    <section class="auth-card">
        <header class="auth-header">
            <h1>Forgot password</h1>
            <p>Enter your account email and we will send you a secure reset link.</p>
        </header>

        <div class="auth-note">
            {{ __('For security, reset links expire automatically. If you do not see the email, check your spam folder.') }}
        </div>

        <x-auth-session-status class="auth-status" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
            @csrf

            <div class="auth-field">
                <x-input-label for="email" :value="__('Email')" class="auth-label" />
                <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div class="auth-row actions-row">
                <a class="auth-link" href="{{ route('login') }}">{{ __('Back to login') }}</a>
                <button type="submit" class="auth-submit">
                    {{ __('Send Reset Link') }}
                </button>
            </div>
        </form>
    </section>
</x-guest-layout>
