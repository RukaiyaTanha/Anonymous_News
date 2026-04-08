<nav class="site-nav glass-nav">
    <div class="site-nav-inner container container--wide">
        <a class="site-brand" href="{{ route('home') }}">
            <img class="site-brand-logo" src="{{ asset('images/logo-spy.png') }}" alt="Anonymous News Portal logo">
            <span class="site-brand-copy">
                <strong>Anonymous News Portal</strong>
                <small>Verified Community Reporting</small>
            </span>
        </a>

        <div class="site-links">
            <a class="site-link-pill" href="{{ route('home') }}">Home</a>

            @auth
                @if (strtolower(trim((string) auth()->user()->role)) === 'admin')
                    <a class="site-link-pill" href="{{ route('admin.dashboard') }}">Admin</a>
                @else
                    <a class="site-link-pill" href="{{ route('dashboard') }}">Dashboard</a>
                @endif
            @else
                <a class="site-link-pill" href="{{ request()->fullUrlWithQuery(['auth' => 'login']) }}">Log in</a>
                @if (Route::has('register'))
                    <a class="site-link-pill nav-cta" href="{{ request()->fullUrlWithQuery(['auth' => 'register']) }}">Register</a>
                @endif
            @endauth
        </div>
    </div>
</nav>
