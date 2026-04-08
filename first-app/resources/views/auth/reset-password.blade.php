<x-guest-layout>
    <section class="auth-card">
        <header class="auth-header">
            <h1>Reset password</h1>
            <p>Create a new password for your account.</p>
        </header>

        <div class="auth-note">
            {{ __('Use at least 8 characters with a strong combination of letters, numbers, and symbols.') }}
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="auth-form">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="auth-field">
                <x-input-label for="email" :value="__('Email')" class="auth-label" />
                <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div class="auth-field">
                <x-input-label for="password" :value="__('New Password')" class="auth-label" />
                <x-text-input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="auth-error" />
            </div>

            <div class="auth-field">
                <x-input-label for="password_confirmation" :value="__('Confirm New Password')" class="auth-label" />
                <x-text-input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
            </div>

            <div class="auth-row actions-row">
                <a class="auth-link" href="{{ route('login') }}">{{ __('Back to login') }}</a>
                <button type="submit" class="auth-submit">
                    {{ __('Reset Password') }}
                </button>
            </div>
        </form>
    </section>
</x-guest-layout>
