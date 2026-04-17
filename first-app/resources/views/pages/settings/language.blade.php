<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public string $language = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->language = Auth::user()->language ?? 'en';
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

        $this->dispatch('language-updated', language: $this->language);
        session()->flash('status', 'language-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Language Settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Language')" :subheading="__('Choose your preferred language for reports and content')">
        <form wire:submit="updateLanguagePreference" class="my-6 w-full space-y-6">
            <flux:radio.group wire:model.live="language" variant="segmented">
                <flux:radio value="en" icon="language">{{ __('English') }}</flux:radio>
                <flux:radio value="bn" icon="language">{{ __('Bangla (বাংলা)') }}</flux:radio>
            </flux:radio.group>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">
                    {{ __('Save Language Preference') }}
                </flux:button>
            </div>

            @if (session('status') === 'language-updated')
                <flux:alert icon="check-circle" variant="success" class="mt-4">
                    {{ __('Language preference updated successfully!') }}
                </flux:alert>
            @endif
        </form>

        <div class="mt-6 border-t pt-6">
            <flux:text size="sm" class="text-gray-600 dark:text-gray-400">
                <strong>{{ __('Info:') }}</strong> 
                {{ __('Your language preference will be used across the platform. Reports can be translated to Bangla using our AI-powered translation feature.') }}
            </flux:text>
        </div>
    </x-pages::settings.layout>
</section>
