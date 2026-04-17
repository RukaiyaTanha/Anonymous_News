<?php

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';
    public string $language = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->language = Auth::user()->language ?? 'en';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Update the language preference for the currently authenticated user.
     */
    public function updateLanguagePreference(): void
    {
        $this->validate([
            'language' => 'required|in:en,bn',
        ]);

        $user = Auth::user();
        $user->update(['language' => $this->language]);

        Session::flash('status', 'language-updated');
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile Settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif

        <!-- Language Preference Section - AFTER DELETE ACCOUNT -->
        <div class="mt-8 pt-8 border-t">
            <form wire:submit="updateLanguagePreference" class="my-6 w-full space-y-6">
                <div>
                    <flux:heading size="md" class="text-lg font-semibold">{{ __('🌐 Language Preference') }}</flux:heading>
                    <flux:subheading class="mt-1">{{ __('Choose your preferred language') }}</flux:subheading>
                </div>

                <flux:radio.group wire:model="language" variant="segmented">
                    <flux:radio value="en" icon="language">{{ __('English') }}</flux:radio>
                    <flux:radio value="bn" icon="language">{{ __('Bangla (বাংলা)') }}</flux:radio>
                </flux:radio.group>

                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit">
                        {{ __('Save Language') }}
                    </flux:button>
                </div>

                @if (session('status') === 'language-updated')
                    <flux:alert icon="check-circle" variant="success" class="mt-4">
                        {{ __('Language preference updated successfully!') }}
                    </flux:alert>
                @endif
            </form>
        </div>
    </x-pages::settings.layout>
</section>
