<aside class="side-nav glass-nav">
    @php
        $user = auth()->user();
        $profilePhotoUrl = $user?->profile_photo_url;
        $userInitial = strtoupper(substr($user->name ?? 'U', 0, 1));
    @endphp

    <div class="side-nav-top">
        <a class="side-brand" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('images/logo-spy.png') }}" alt="Anonymous News Portal logo" class="side-brand-logo">
            <span>{{ __('Anonymous News Portal') }}</span>
        </a>
        <p class="side-role-label">{{ __('Admin Panel') }}</p>
    </div>

    <nav class="side-nav-links" aria-label="{{ __('Admin navigation') }}">
        <a href="{{ route('admin.dashboard') }}" class="side-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="8" height="8" rx="1.5" />
                    <rect x="13" y="3" width="8" height="5" rx="1.5" />
                    <rect x="13" y="10" width="8" height="11" rx="1.5" />
                    <rect x="3" y="13" width="8" height="8" rx="1.5" />
                </svg>
            </span>
            <span>{{ __('Overview') }}</span>
        </a>

        <a href="{{ route('admin.reports.pending') }}" class="side-link {{ request()->routeIs('admin.reports.pending') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 7v5l3 2" />
                </svg>
            </span>
            <span>{{ __('Pending Reports') }}</span>
        </a>

        <a href="{{ route('admin.reports.verified') }}" class="side-link {{ request()->routeIs('admin.reports.verified') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 12l2 2 4-4" />
                    <circle cx="12" cy="12" r="9" />
                </svg>
            </span>
            <span>{{ __('Verified Reports') }}</span>
        </a>

        <a href="{{ route('admin.users.index') }}" class="side-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 19a4 4 0 0 0-8 0" />
                    <circle cx="12" cy="10" r="3" />
                    <path d="M4 19a4 4 0 0 1 4-4" />
                    <path d="M20 19a4 4 0 0 0-4-4" />
                </svg>
            </span>
            <span>{{ __('Users') }}</span>
        </a>

        <a href="{{ route('admin.categories.index') }}" class="side-link {{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 8h14" />
                    <path d="M5 12h14" />
                    <path d="M5 16h14" />
                    <rect x="3" y="5" width="18" height="14" rx="2" />
                </svg>
            </span>
            <span>{{ __('Categories') }}</span>
        </a>

        <a href="{{ route('admin.audits.index') }}" class="side-link {{ request()->routeIs('admin.audits.*') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="6" y="3" width="12" height="18" rx="2" />
                    <path d="M9 8h6" />
                    <path d="M9 12h6" />
                    <path d="M9 16h4" />
                </svg>
            </span>
            <span>{{ __('Audit Logs') }}</span>
        </a>

        <a href="{{ route('news.index') }}" class="side-link {{ request()->routeIs('news.*') ? 'is-active' : '' }}">
            <span class="side-link-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18v12H3z" />
                    <path d="M8 10h8" />
                    <path d="M8 14h5" />
                </svg>
            </span>
            <span>{{ __('Public News') }}</span>
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
                <span>{{ __('Log Out') }}</span>
            </button>
        </form>
    </div>
</aside>
