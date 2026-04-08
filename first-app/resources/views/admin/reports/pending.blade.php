@extends('layouts.app')

@section('content')
<section class="admin-pending-page">
    <div class="page-shell">
        <header class="admin-hero card-glass">
            <div class="admin-hero-top">
                <span class="admin-kicker">Admin Panel</span>
            </div>

            <div class="admin-hero-main">
                <h1>Pending Reports Review</h1>
                <p>Review and moderate pending news reports submitted by users.</p>
            </div>
        </header>

        @if (session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif

        <section class="admin-panel card-glass">
            <div class="admin-panel-head">
                <h2>Pending Reports Review</h2>
                <small>{{ number_format($reports->total()) }} pending reports</small>
            </div>

            <div class="pending-toolbar">
                <form method="GET" action="{{ route('admin.reports.pending') }}" class="pending-toolbar-form">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search ?? '' }}"
                        placeholder="Search report title or content"
                    >

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
                @php
                    $aiScore = (float) ($report->ai_confidence_score ?? 0);
                    $credibilityScore = (float) ($report->credibility_score ?? 0);
                    $duplicateSimilarity = (float) ($report->duplicate_similarity_score ?? 0);
                    $reporterReputation = (float) ($report->reporter_reputation_score ?? ($report->user?->reputation_score ?? 0));
                    $indicators = is_array($report->ai_suspicious_indicators ?? null) ? $report->ai_suspicious_indicators : [];
                    $entities = is_array($report->ai_entities ?? null) ? $report->ai_entities : [];
                    $primaryImageUrl = $report->primary_image_url;

                    if (! $primaryImageUrl && $report->relationLoaded('media')) {
                        $fallbackImagePath = $report->media->first()?->file_path;
                        $primaryImageUrl = $fallbackImagePath ? asset('storage/'.$fallbackImagePath) : null;
                    }
                @endphp
                <article class="pending-review-card">
                    <div class="pending-review-main">
                        <div class="pending-review-head">
                            <h3>{{ $report->title }}</h3>
                            <span class="category-pill">{{ $report->category?->name ?? 'Uncategorized' }}</span>
                        </div>

                        <div class="pending-review-meta">
                            <span class="pending-meta-chip">By {{ $report->user?->username ?? 'Unknown user' }}</span>
                            <span class="pending-meta-chip">{{ $report->created_at?->format('M j, Y') }}</span>
                            <span class="pending-meta-chip">AI {{ number_format((float) ($report->ai_confidence_score ?? 0), 0) }}%</span>
                            <span class="pending-meta-chip">Credibility {{ number_format((float) ($report->credibility_score ?? 0), 0) }}%</span>
                        </div>

                        <div class="pending-review-media{{ $primaryImageUrl ? '' : ' no-media' }}">
                            @if ($primaryImageUrl)
                                <img src="{{ $primaryImageUrl }}" alt="Submitted image for {{ $report->title }}" loading="lazy">
                            @else
                                <span class="pending-media-empty-label">No submitted image</span>
                            @endif
                        </div>

                        @if (!empty($report->excerpt))
                            <p class="pending-review-excerpt">{{ $report->excerpt }}</p>
                        @endif
                        <p class="pending-review-content">{{ strip_tags($report->content) }}</p>
                    </div>

                    <aside class="pending-review-controls">
                        <h4>Moderator controls:</h4>

                        <div class="pending-score-grid">
                            <div class="pending-score-item">
                                <span>AI Confidence</span>
                                <strong>{{ number_format($aiScore, 0) }}%</strong>
                            </div>
                            <div class="pending-score-item">
                                <span>Duplicate Similarity</span>
                                <strong>{{ number_format($duplicateSimilarity, 0) }}%</strong>
                            </div>
                            <div class="pending-score-item">
                                <span>Reporter Reputation</span>
                                <strong>{{ number_format($reporterReputation, 0) }}%</strong>
                            </div>
                        </div>

                        @if (!empty($indicators))
                            <div class="pending-score-item">
                                <span>Suspicious Indicators</span>
                                <strong>{{ implode(', ', $indicators) }}</strong>
                            </div>
                        @endif

                        @if (!empty($entities))
                            <div class="pending-score-item">
                                <span>Detected Entities</span>
                                <strong>{{ implode(', ', $entities) }}</strong>
                            </div>
                        @endif

                        <form action="{{ route('admin.reports.verify', $report->id) }}" method="POST" class="pending-control-form">
                            @csrf
                            <button type="submit" class="btn btn-success pending-action-btn">Approve</button>
                        </form>

                        <form action="{{ route('admin.reports.reject', $report->id) }}" method="POST" class="pending-control-form stack-md">
                            @csrf
                            <textarea name="note" rows="4" placeholder="Add moderator note (optional)"></textarea>
                            <button type="submit" class="btn btn-danger pending-action-btn">Reject</button>
                        </form>

                        <form action="{{ route('admin.reports.revision', $report->id) }}" method="POST" class="pending-control-form stack-md">
                            @csrf
                            <textarea name="note" rows="3" placeholder="Request revision note (optional)"></textarea>
                            <button type="submit" class="btn btn-outline pending-action-btn">Request Revision</button>
                        </form>

                        <p class="pending-note-hint">By rejecting this report, the user may not receive reputation points and this submission will be archived.</p>
                    </aside>
                </article>
            @empty
                <div class="notifications-empty">
                    <h3>No pending reports</h3>
                    <p>All submissions are already reviewed. New pending reports will appear here.</p>
                </div>
            @endforelse

            <div class="pagination-wrap">{{ $reports->links() }}</div>
        </section>
    </div>
</section>
@endsection