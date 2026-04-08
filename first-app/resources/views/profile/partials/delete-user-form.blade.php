<section>
    <header class="profile-block-header">
        <h2>
            {{ __('Delete Account') }}
        </h2>

        <p>
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}" class="profile-form-grid delete-account-form">
        @csrf
        @method('delete')

        <div class="profile-field">
            <x-input-label for="delete_password" :value="__('Current Password')" class="auth-label" />
            <x-text-input
                id="delete_password"
                name="password"
                type="password"
                class="auth-input"
                placeholder="{{ __('Enter current password to confirm') }}"
            />
            <x-input-error :messages="$errors->userDeletion->get('password')" class="auth-error" />
        </div>

        <div class="profile-actions-row">
            <button type="submit" class="profile-delete-btn">{{ __('Delete Account') }}</button>
            <p class="profile-note">{{ __('This action is permanent and cannot be undone.') }}</p>
        </div>
    </form>
</section>
