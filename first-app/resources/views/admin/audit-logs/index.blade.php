@extends('layouts.app')

@section('content')
<section class="admin-audits-page">
	<div class="page-shell">
		<header class="admin-hero card-glass">
			<div class="admin-hero-top">
				<span class="admin-kicker">Admin Panel</span>
			</div>

			<div class="admin-hero-main">
				<h1>Audit Logs</h1>
				<p>Track all moderator actions and changes to verified reports.</p>
			</div>
		</header>

		<section class="admin-panel card-glass">
			<div class="admin-panel-head">
				<h2>Audit Logs</h2>
				<small>{{ number_format($auditLogs->total()) }} entries</small>
			</div>

			<form method="GET" action="{{ route('admin.audits.index') }}" class="audit-toolbar-form">
				<input
					type="text"
					name="q"
					value="{{ $search }}"
					placeholder="Search users, report, action..."
				>

				<select name="moderator_id">
					<option value="0">All Moderators</option>
					@foreach ($moderators as $moderator)
						<option value="{{ $moderator->id }}" @selected($moderatorId === $moderator->id)>
							{{ $moderator->username }}
						</option>
					@endforeach
				</select>

				<select name="action">
					<option value="">All Actions</option>
					@foreach ($actions as $actionOption)
						<option value="{{ $actionOption }}" @selected($action === $actionOption)>
							{{ ucfirst(str_replace('_', ' ', $actionOption)) }}
						</option>
					@endforeach
				</select>

				<input
					type="date"
					name="date_from"
					value="{{ $dateFrom }}"
					title="From date"
				>

				<input
					type="date"
					name="date_to"
					value="{{ $dateTo }}"
					title="To date"
				>

				<button type="submit" class="audit-search-btn">Search</button>
			</form>

			<div class="audit-table-wrap">
				<table class="audit-table">
					<thead>
						<tr>
							<th>Action</th>
							<th>Moderator</th>
							<th>Report ID</th>
							<th>Timestamp</th>
							<th>IP Hash</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						@forelse ($auditLogs as $log)
							@php
								$actionType = strtolower((string) $log->action_type);
								$badgeClass = match (true) {
									str_contains($actionType, 'approved') => 'is-approved',
									str_contains($actionType, 'rejected') => 'is-rejected',
									str_contains($actionType, 'removed') => 'is-removed',
									str_contains($actionType, 'locked') => 'is-locked',
									str_contains($actionType, 'banned') => 'is-banned',
									default => 'is-default',
								};
							@endphp
							<tr>
								<td>
									<span class="audit-action-pill {{ $badgeClass }}">
										{{ ucfirst(str_replace('_', ' ', $log->action_type)) }}
									</span>
								</td>
								<td>
									<div class="audit-user-cell">
										<img
											src="https://ui-avatars.com/api/?name={{ urlencode($log->admin?->username ?? 'Admin') }}&background=1d4ed8&color=ffffff"
											alt="{{ $log->admin?->username ?? 'Admin' }}"
										>
										<div>
											<strong>{{ $log->admin?->username ?? 'Unknown' }}</strong>
											<small>{{ $log->admin?->email ?? 'No email' }}</small>
										</div>
									</div>
								</td>
								<td>
									{{ $log->report_id ?? '—' }}
								</td>
								<td>
									{{ $log->created_at ? \Illuminate\Support\Carbon::parse($log->created_at)->format('M d, Y - h:i A') : '—' }}
								</td>
								<td>
									{{ $log->ip_hash ?: '—' }}
								</td>
								<td>
									@if ($log->admin)
										<a href="{{ route('admin.users.index', ['q' => $log->admin->username]) }}" class="audit-row-action-btn">Manage</a>
									@else
										<span class="audit-row-action-disabled">N/A</span>
									@endif
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="6" class="audit-empty">No audit logs found for the selected filters.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			<div class="audit-pagination">{{ $auditLogs->links() }}</div>
		</section>
	</div>
</section>
@endsection
