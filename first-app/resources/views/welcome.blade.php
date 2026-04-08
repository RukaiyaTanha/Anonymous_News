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
    </body>
</html>
