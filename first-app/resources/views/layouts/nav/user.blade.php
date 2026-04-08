<aside class="side-nav glass-nav">
    @php
        $user = auth()->user();
        $profilePhotoUrl = $user?->profile_photo_url;
        $userInitial = strtoupper(substr($user->name ?? 'U', 0, 1));
    @endphp

    <div class="side-nav-top">
        <a class="side-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/logo-spy.png') }}" alt="Anonymous News Portal logo" class="side-brand-logo">
            <span>Anonymous News Portal</span>
        </a>
        <p class="side-role-label">User Panel</p>
    </div>

    <nav class="side-nav-links" aria-label="User navigation">
        <a href="{{ route('dashboard') }}" class="side-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12.5L12 4l9 8.5" />
                    <path d="M5 10.8V20h14v-9.2" />
                </svg>
            </span>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('reports.create') }}" class="side-link {{ request()->routeIs('reports.create') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                </svg>
            </span>
            <span>Submit Report</span>
        </a>

        <a href="{{ route('reports.my') }}" class="side-link {{ request()->routeIs('reports.my') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7 4h10l4 4v12H7z" />
                    <path d="M17 4v4h4" />
                    <path d="M10 12h8" />
                    <path d="M10 16h8" />
                </svg>
            </span>
            <span>My Reports</span>
        </a>

        <a href="{{ route('notifications.index') }}" class="side-link {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" />
                    <path d="M9 17a3 3 0 0 0 6 0" />
                </svg>
            </span>
            <span>Notifications</span>
            <span class="side-link-badge">{{ auth()->user()->userNotifications()->where('is_read', false)->count() }}</span>
        </a>

        <a href="{{ route('profile.edit') }}" class="side-link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21a8 8 0 1 0-16 0" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </span>
            <span>Profile</span>
        </a>

        <a href="{{ route('news.index') }}" class="side-link {{ request()->routeIs('news.*') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18v12H3z" />
                    <path d="M8 10h8" />
                    <path d="M8 14h5" />
                </svg>
            </span>
            <span>Verified News</span>
        </a>
    </nav>

    <div class="side-nav-bottom">
        <div class="side-user">
            <span class="side-user-avatar" aria-hidden="true">
                @if ($profilePhotoUrl)
                    <img src="{{ $profilePhotoUrl }}" alt="{{ $user->name }}">
                @else
                    <span>{{ $userInitial }}</span>
                @endif
            </span>

            <div class="side-user-meta">
                <strong>{{ $user->name }}</strong>
                <small>{{ $user->email }}</small>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="side-logout-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <path d="M16 17l5-5-5-5" />
                    <path d="M21 12H9" />
                </svg>
                <span>Log Out</span>
            </button>
        </form>
    </div>
</aside>
