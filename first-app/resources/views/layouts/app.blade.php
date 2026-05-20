<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php
        $requestedAuthModal = request()->query('auth');
        $oldAuthModal = old('auth_form');
        $initialAuthModal = in_array($oldAuthModal, ['login', 'register'], true)
            ? $oldAuthModal
            : (in_array($requestedAuthModal, ['login', 'register'], true) ? $requestedAuthModal : null);
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="app-shell font-sans antialiased"
          x-data="{
              authModal: @js($initialAuthModal),
              imageModal: null,
              syncAuthModalUrl() {
                  const currentUrl = new URL(window.location.href);

                  if (this.authModal) {
                      currentUrl.searchParams.set('auth', this.authModal);
                  } else {
                      currentUrl.searchParams.delete('auth');
                  }

                  window.history.replaceState({}, '', currentUrl.toString());
              },
              openImageModal(src, title) {
                  this.imageModal = { src, title };
              },
              closeImageModal() {
                  this.imageModal = null;
              }
          }"
          x-init="$watch('authModal', () => syncAuthModalUrl()); if (authModal) { syncAuthModalUrl(); }"
          @keydown.escape.window="authModal = null; imageModal = null"
          :class="{ 'auth-overlay-open': Boolean(authModal || imageModal) }">
        <div class="min-h-screen app-content-layer">
            @auth
                <div class="auth-app-layout">
                    @if (strtolower(trim((string) auth()->user()->role)) === 'admin')
                        @include('layouts.nav.admin')
                    @else
                        @include('layouts.nav.user')
                    @endif

                    <div class="auth-main-area">
                        @isset($header)
                            <header class="auth-page-header glass-header container">
                                <div class="py-4">
                                    {{ $header }}
                                </div>
                            </header>
                        @endisset

                        <main class="page-wrap auth-page-wrap">
                            @isset($slot)
                                {{ $slot }}
                            @else
                                @yield('content')
                            @endisset
                        </main>
                    </div>
                </div>
            @else
                @include('layouts.nav.public')

                @isset($header)
                    <header class="glass-header mx-auto mt-6 max-w-7xl rounded-2xl">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="page-wrap">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </main>
            @endauth
        </div>

        @guest
            <div class="auth-overlay" x-show="authModal" x-cloak x-transition.opacity.duration.200ms>
                <button class="auth-overlay-backdrop" type="button" aria-label="Close authentication modal" @click="authModal = null"></button>

                <section class="auth-overlay-panel glass-panel" role="dialog" aria-modal="true" aria-label="Authentication modal" @click.stop>
                    <div class="auth-overlay-head">
                        <div class="auth-overlay-title">
                            <p class="auth-overlay-kicker">Account Access</p>
                            <h2 x-text="authModal === 'register' ? 'Create your account' : 'Sign in to continue'"></h2>
                        </div>

                        <button class="auth-overlay-close" type="button" @click="authModal = null">Close</button>
                    </div>

                    <div x-show="authModal === 'login'" x-transition.opacity.duration.160ms>
                        @include('auth.partials.login-form', ['isModal' => true])
                    </div>

                    <div x-show="authModal === 'register'" x-transition.opacity.duration.160ms>
                        @include('auth.partials.register-form', ['isModal' => true])
                    </div>
                </section>
            </div>
        @endguest

        <div class="image-overlay" x-show="imageModal" x-cloak x-transition.opacity.duration.200ms>
            <button class="image-overlay-backdrop" type="button" aria-label="Close image preview" @click="closeImageModal()"></button>

            <section class="image-overlay-panel glass-panel" role="dialog" aria-modal="true" aria-label="Image preview" @click.stop>
                <div class="image-overlay-head">
                    <div class="image-overlay-title">
                        <p class="image-overlay-kicker">Image Preview</p>
                        <h2 x-text="imageModal?.title ?? 'Preview'"></h2>
                    </div>

                    <button class="image-overlay-close" type="button" @click="closeImageModal()">Close</button>
                </div>

                <div class="image-overlay-frame">
                    <img x-show="imageModal?.src" :src="imageModal?.src" :alt="imageModal?.title ?? 'Image preview'">
                </div>
            </section>
        </div>
    </body>
</html>
