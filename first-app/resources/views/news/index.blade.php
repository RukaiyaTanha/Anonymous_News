@extends('layouts.app')

@section('content')
<div class="container overview-page">
    

    <section class="guest-news-hero glass-card">
        <div class="guest-news-hero-content">
            <h2>Public News</h2>
            <p>Explore the most recent and credible news reports submitted anonymously by the community.</p>
        </div>
    </section>

    <section class="overview-section guest-filters-wrap">
        <div class="overview-section-head">
            <h3>Latest Public News</h3>
        </div>

        <form method="GET" action="{{ route('news.index') }}" class="guest-filters glass-card">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search reports...">

            <select name="category">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <select name="sort">
                <option value="latest" @selected($sort === 'latest')>Latest</option>
                <option value="oldest" @selected($sort === 'oldest')>Oldest</option>
                <option value="credibility" @selected($sort === 'credibility')>Top Credibility</option>
            </select>

            <select name="range">
                <option value="all" @selected($range === 'all')>Date Range</option>
                <option value="7d" @selected($range === '7d')>Last 7 days</option>
                <option value="30d" @selected($range === '30d')>Last 30 days</option>
                <option value="365d" @selected($range === '365d')>Last 12 months</option>
            </select>

            <button type="submit">Apply Filters</button>
        </form>
    </section>

    <section class="overview-section">
        <div class="overview-section-head">
            <h3>View Public News</h3>
        </div>

        @if($reports->count())
        <div class="report-grid">
            @foreach($reports as $report)
                @php
                    $hasVoted = in_array((int) $report->id, $votedReportIds ?? [], true);
                    $hasFlagged = in_array((int) $report->id, $flaggedReportIds ?? [], true);
                @endphp
                <article class="report-card glass-card">
                    <div class="report-thumb{{ $report->primary_image_url ? '' : ' no-media' }}" @if($report->primary_image_url) style="background-image: url('{{ $report->primary_image_url }}');" @endif></div>
                    <div class="report-body">
                        <div class="report-tags">
                            <span class="category-pill">{{ $report->category?->name ?? 'General' }}</span>
                        </div>
                        <h4>
                            <a href="{{ route('news.show', $report->slug) }}">{{ \Illuminate\Support\Str::limit($report->title, 64) }}</a>
                        </h4>
                        <p>{{ \Illuminate\Support\Str::limit($report->excerpt ?? $report->content, 100) }}</p>
                        <div class="report-meta">
                            <span>{{ number_format((float) ($report->credibility_score ?? 0), 0) }}% credible</span>
                            <span>{{ $report->created_at?->format('M j, Y') }}</span>
                        </div>
                        <div class="report-meta report-meta-engagement">
                            <span>{{ number_format((int) ($report->votes_count ?? 0)) }} upvotes</span>
                            <span>{{ number_format((int) ($report->flags_count ?? 0)) }} flags</span>
                        </div>

                        <div class="report-actions report-actions-compact">
                            @auth
                                @if($hasVoted)
                                    <button type="button" class="action-pill action-upvote" disabled>Already Upvoted</button>
                                @else
                                    <form method="POST" action="{{ route('reports.vote', $report->id) }}">
                                        @csrf
                                        <button type="submit" class="action-pill action-upvote">Upvote</button>
                                    </form>
                                @endif

                                @if($hasFlagged)
                                    <button type="button" class="action-pill action-flag" disabled>Already Flagged</button>
                                @else
                                    <form method="POST" action="{{ route('reports.flag', $report->id) }}" class="index-flag-form">
                                        @csrf
                                        <input type="text" name="reason" placeholder="Flag reason" required maxlength="500">
                                        <button type="submit" class="action-pill action-flag">Flag</button>
                                    </form>
                                @endif
                            @else
                                <a href="{{ request()->fullUrlWithQuery(['auth' => 'login']) }}" class="action-pill action-upvote">Login to Upvote</a>
                                <a href="{{ request()->fullUrlWithQuery(['auth' => 'login']) }}" class="action-pill action-flag">Login to Flag</a>
                            @endauth
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="pagination-wrap">
            {{ $reports->links() }}
        </div>
        @else
            <div class="glass-card">
                <p>No public news found for this filter.</p>
            </div>
        @endif
    </section>
</div>
@endsection