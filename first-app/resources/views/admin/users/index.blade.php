@extends('layouts.app')

@section('content')
<section class="admin-users-page">
	<div class="page-shell">
		<header class="admin-hero card-glass">
			<div class="admin-hero-top">
				<span class="admin-kicker">Admin Panel</span>
			</div>

			<div class="admin-hero-main">
				<h1>User Management</h1>
				<p>Manage and moderate user accounts.</p>
			</div>
		</header>

		<section class="admin-panel card-glass">
			<div class="admin-panel-head">
				<h2>User Management</h2>
				<span class="admin-users-count">Total: {{ number_format($users->total()) }}</span>
			</div>

			@if (session('success'))
				<div class="admin-users-alert admin-users-alert--success">{{ session('success') }}</div>
			@endif

			@if (session('error'))
				<div class="admin-users-alert admin-users-alert--error">{{ session('error') }}</div>
			@endif

			<form method="GET" action="{{ route('admin.users.index') }}" class="admin-users-search-form">
				<input
					type="text"
					name="q"
					value="{{ $search }}"
					placeholder="Search users..."
					aria-label="Search users"
				>
				<button type="submit" class="admin-users-search-btn">Search</button>
			</form>

			<div class="admin-users-table-wrap">
				<table class="admin-users-table">
					<thead>
						<tr>
							<th>Name</th>
							<th>Email</th>
							<th>Reputation</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						@forelse ($users as $user)
							@php
								$reputationPercent = max(0, min(100, (int) $user->reputation_score));
								$starCount = (int) ceil($reputationPercent / 20);
								$isBanned = (bool) $user->is_banned;
								$qualityLabel = match (true) {
									$reputationPercent >= 90 => 'Likely Accurate',
									$reputationPercent >= 75 => 'Reliable',
									$reputationPercent >= 60 => 'Fair',
									default => 'Needs Review',
								};
							@endphp
							<tr>
								<td>
									<div class="admin-user-cell">
										@if ($user->profile_photo_url)
											<img
												src="{{ $user->profile_photo_url }}"
												alt="{{ $user->username }} profile photo"
												class="admin-user-avatar"
											>
										@else
											<span class="admin-user-avatar admin-user-avatar-fallback" aria-hidden="true">
												{{ strtoupper(substr((string) $user->username, 0, 2)) }}
											</span>
										@endif
										<div>
											<strong>{{ $user->username }}</strong>
											<small>@if($user->role === 'admin') Admin @else Member @endif</small>
										</div>
									</div>
								</td>
								<td>{{ $user->email ?: 'No email provided' }}</td>
								<td>
									<div class="admin-user-reputation">
										<span class="admin-user-stars">{{ str_repeat('★', max(1, $starCount)) }}</span>
										<strong>{{ $reputationPercent }}%</strong>
										<small>{{ $qualityLabel }}</small>
									</div>
								</td>
								<td>
									<span class="admin-user-status {{ $isBanned ? 'is-banned' : 'is-active' }}">
										{{ $isBanned ? 'Banned' : 'Active' }}
									</span>
								</td>
								<td>
									@if ($user->role === 'admin')
										<span class="admin-user-action-disabled">Protected</span>
									@else
										<form method="POST" action="{{ route('admin.users.toggle-ban', $user) }}">
											@csrf
											<button
												type="submit"
												class="admin-user-action-btn {{ $isBanned ? 'is-unban' : 'is-ban' }}"
											>
												{{ $isBanned ? 'Unban' : 'Ban' }}
											</button>
										</form>
									@endif
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="5" class="admin-users-empty">No users found for this filter.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			<div class="admin-users-pagination">
				{{ $users->links() }}
			</div>
		</section>
	</div>
</section>
@endsection
