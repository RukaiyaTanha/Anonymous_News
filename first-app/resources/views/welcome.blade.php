<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="app-shell min-h-screen antialiased">
        @include('layouts.nav.public')

        <main class="page-wrap mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <header class="glass-card mb-6">
                <div>
                    <h1>Anonymous Verified News Portal</h1>
                    <p class="mt-2 text-slate-600">Modern, transparent, and focused on verified reporting.</p>
                </div>
            </header>

            <section class="grid gap-4 md:grid-cols-3">
                <article class="glass-card">
                    <h3>Submit Reports</h3>
                    <p class="mt-2 text-slate-600">Share credible reports securely with a clean submission flow.</p>
                </article>
                <article class="glass-card">
                    <h3>Community Voting</h3>
                    <p class="mt-2 text-slate-600">Readers vote and flag content to improve trust and quality.</p>
                </article>
                <article class="glass-card">
                    <h3>Admin Moderation</h3>
                    <p class="mt-2 text-slate-600">Moderation tools keep verified reports accurate and transparent.</p>
                </article>
            </section>
        </main>

        <footer class="site-footer mt-16 border-t border-slate-200 bg-slate-50">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid gap-8 md:grid-cols-4">
                    <div>
                        <h4 class="font-bold text-slate-900 mb-3">Anonymous News Portal</h4>
                        <p class="text-sm text-slate-600">Community-driven platform with AI-assisted verification and human moderation for transparent, credible reporting.</p>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-3">Product</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-slate-600 hover:text-slate-900">Submit Report</a></li>
                            <li><a href="#" class="text-slate-600 hover:text-slate-900">Browse News</a></li>
                            <li><a href="#" class="text-slate-600 hover:text-slate-900">Verified Reports</a></li>
                            <li><a href="#" class="text-slate-600 hover:text-slate-900">Categories</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-3">Company</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-slate-600 hover:text-slate-900">About Us</a></li>
                            <li><a href="#" class="text-slate-600 hover:text-slate-900">How It Works</a></li>
                            <li><a href="#" class="text-slate-600 hover:text-slate-900">Our Mission</a></li>
                            <li><a href="#" class="text-slate-600 hover:text-slate-900">Blog</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-3">Legal</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-slate-600 hover:text-slate-900">Privacy Policy</a></li>
                            <li><a href="#" class="text-slate-600 hover:text-slate-900">Terms of Service</a></li>
                            <li><a href="#" class="text-slate-600 hover:text-slate-900">Contact</a></li>
                            <li><a href="#" class="text-slate-600 hover:text-slate-900">Support</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center">
                    <p class="text-sm text-slate-600">&copy; 2026 Anonymous News Portal. All rights reserved.</p>
                    <p class="text-sm text-slate-500 mt-4 sm:mt-0">Powered by AI-assisted moderation and community verification</p>
                </div>
            </div>
        </footer>
    </body>
</html>
