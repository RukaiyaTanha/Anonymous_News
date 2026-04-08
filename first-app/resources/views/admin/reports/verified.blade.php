@extends('layouts.app')

@section('content')
<section class="admin-pending-page">
	<div class="page-shell">
		<header class="admin-hero card-glass">
			<div class="admin-hero-top">
				<span class="admin-kicker">Admin Panel</span>
			</div>

			<div class="admin-hero-main">
				<h1>Verified Reports</h1>
				<p>Browse and audit reports that were already approved by moderators.</p>
			</div>
		</header>

		<section class="admin-panel card-glass">
			<div class="admin-panel-head">
				<h2>Verified Reports</h2>
				<small>{{ number_format($reports->total()) }} verified reports</small>
			</div>

			<div class="pending-toolbar">
				<form method="GET" action="{{ route('admin.reports.verified') }}" class="pending-toolbar-form">
					<input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Search report title or content">

					<select name="category_id">
						<option value="0">All Categories</option>
						@foreach ($categories as $category)
							<option value="{{ $category->id }}" @selected(($selectedCategory ?? 0) === $category->id)>
								{{ $category->name }}
							</option>
						@endforeach
					</select>

					<select name="sort">
						<option value="newest" @selected(($selectedSort ?? 'newest') === 'newest')>Newest</option>
						<option value="oldest" @selected(($selectedSort ?? 'newest') === 'oldest')>Oldest</option>
					</select>

					<button type="submit" class="btn btn-outline">Apply</button>
				</form>
			</div>

			@forelse($reports as $report)
				<article class="verified-report-row">
					<div class="verified-report-main">
						<h3>{{ $report->title }}</h3>
						<p>{{ $report->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($report->content), 180) }}</p>
						<div class="pending-review-meta">
							<span class="pending-meta-chip">By {{ $report->user?->username ?? 'Unknown user' }}</span>
							<span class="pending-meta-chip">{{ $report->category?->name ?? 'Uncategorized' }}</span>
							<span class="pending-meta-chip">Verified {{ ($report->reviewed_at ?? $report->published_at ?? $report->created_at)?->diffForHumans() }}</span>
						</div>
					</div>

					<div class="verified-report-scores">
						<span>AI {{ number_format((float) ($report->ai_confidence_score ?? 0), 0) }}%</span>
						<span>Credibility {{ number_format((float) ($report->credibility_score ?? 0), 0) }}%</span>
					</div>
				</article>
			@empty
				<div class="notifications-empty">
					<h3>No verified reports</h3>
					<p>Once a pending report is approved, it will appear here.</p>
				</div>
			@endforelse

			<div class="pagination-wrap">{{ $reports->links() }}</div>
		</section>
	</div>
</section>
@endsection
