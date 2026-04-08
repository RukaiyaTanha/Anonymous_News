<section>
    <header class="profile-block-header">
        <h2>
            {{ __('Update Password') }}
        </h2>

        <p>
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="profile-form-grid">
        @csrf
        @method('put')

        <div class="profile-field">
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="auth-label" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="auth-input" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="auth-error" />
        </div>

        <div class="profile-field">
            <x-input-label for="update_password_password" :value="__('New Password')" class="auth-label" />
            <x-text-input id="update_password_password" name="password" type="password" class="auth-input" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="auth-error" />
        </div>

        <div class="profile-field">
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="auth-label" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="auth-input" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="auth-error" />
        </div>

        <div class="profile-actions-row">
            <button type="submit" class="profile-save-btn">{{ __('Save Changes') }}</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="profile-success-msg"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
