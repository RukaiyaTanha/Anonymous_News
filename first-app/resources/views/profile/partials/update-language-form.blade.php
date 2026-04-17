<section>
    <header class="profile-block-header">
        <h2>
            {{ __('Language Preference') }}
        </h2>

        <p>
            {{ __('Choose the language you want to use in your account.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.language.update') }}" class="profile-form-grid">
        @csrf
        @method('patch')

        <div class="profile-field">
            <x-input-label for="language" :value="__('Language')" class="auth-label" />
            <select id="language" name="language" class="auth-input" required>
                <option value="en" @selected(old('language', $user->language ?? 'en') === 'en')>{{ __('English') }}</option>
                <option value="bn" @selected(old('language', $user->language ?? 'en') === 'bn')>{{ __('Bangla (বাংলা)') }}</option>
            </select>
            <x-input-error :messages="$errors->get('language')" class="auth-error" />
        </div>

        <div class="profile-actions-row">
            <button type="submit" class="profile-save-btn">{{ __('Save Language') }}</button>

            @if (session('status') === 'language-updated')
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
