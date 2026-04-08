<section>
    <header class="profile-block-header">
        <h2>
            {{ __('Profile Information') }}
        </h2>

        <p>
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="profile-form-grid" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="profile-field">
            <x-input-label for="profile_photo" :value="__('Profile Photo')" class="auth-label" />

            <div class="profile-photo-row">
                <div
                    class="profile-photo-preview"
                    style="background-image: url('{{ $user->profile_photo_url ?: 'https://picsum.photos/seed/' . urlencode($user->username ?? $user->id) . '/220/220' }}');"
                ></div>

                <div class="profile-photo-input-wrap">
                    <input id="profile_photo" name="profile_photo" type="file" class="auth-input profile-file-input" accept="image/*" />
                    <p class="profile-note-text">{{ __('Upload JPG, PNG, or WEBP (max 2MB).') }}</p>
                </div>
            </div>

            <x-input-error class="auth-error" :messages="$errors->get('profile_photo')" />
        </div>

        <div class="profile-field">
            <x-input-label for="name" :value="__('Name')" class="auth-label" />
            <x-text-input id="name" name="name" type="text" class="auth-input" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="auth-error" :messages="$errors->get('name')" />
        </div>

        <div class="profile-field">
            <x-input-label for="email" :value="__('Email')" class="auth-label" />
            <x-text-input id="email" name="email" type="email" class="auth-input" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="auth-error" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="profile-note">
                    <p>
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="profile-link-btn">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="profile-success-msg">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="profile-actions-row">
            <button type="submit" class="profile-save-btn">{{ __('Save Changes') }}</button>

            @if (session('status') === 'profile-updated')
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
