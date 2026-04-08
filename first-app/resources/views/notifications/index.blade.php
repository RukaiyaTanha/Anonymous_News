<x-app-layout>
    <section class="notifications-page">
        <div class="page-shell">
            <header class="notifications-hero card-glass">
                <div>
                    <h1>Notifications</h1>
                    <p class="section-subtitle">Keep track of important updates to your reports and account.</p>
                </div>
            </header>

            @if (session('success'))
                <div class="flash-success">{{ session('success') }}</div>
            @endif

            <section class="notifications-panel card-glass">
                <div class="notifications-panel__head">
                    <h2>Notifications</h2>
                </div>

                @if ($notifications->isEmpty())
                    <div class="notifications-empty">
                        <h3>No notifications yet</h3>
                        <p>You will see updates here when there is activity related to your reports and account.</p>
                    </div>
                @else
                    <div class="notifications-list">
                        @foreach ($notifications as $notification)
                            <article class="notification-row {{ $notification->is_read ? 'is-read' : 'is-unread' }}">
                                <div class="notification-row__icon {{ $notification->is_read ? 'icon-read' : 'icon-unread' }}" aria-hidden="true"></div>

                                <div class="notification-row__content">
                                    @if (filled($notification->title))
                                        <h3 class="notification-row__title">{{ $notification->title }}</h3>
                                    @endif
                                    <p>{{ $notification->message }}</p>
                                </div>

                                <time class="notification-row__time" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>

                            </article>
                        @endforeach
                    </div>

                    <div class="pagination-wrap">{{ $notifications->links() }}</div>
                @endif
            </section>
        </div>
    </section>
</x-app-layout>
