@extends('layouts.app')

@section('content')
<div class="container overview-page">
    <section class="overview-hero glass-card">
        <div class="overview-hero-content">
            <p class="overview-kicker">{{ __('Public Interest Reporting') }}</p>
            <h1>{{ __('Anonymous. Verified. Professionally Moderated.') }}</h1>
            <p>{{ __('Share evidence-based reports and read stories that pass AI-assisted checks plus human moderation.') }}</p>
            <div class="overview-hero-actions">
                <a href="{{ route('reports.create') }}">{{ __('Submit News') }}</a>
                <a href="{{ route('news.index') }}">{{ __('Browse Reports') }}</a>
            </div>
        </div>
    </section>

    @if($featuredReport)
        <section class="overview-section">
            <div class="overview-section-head">
                <h3>{{ __('Featured Verified Report') }}</h3>
            </div>

            <article class="featured-report glass-card">
                <div class="featured-media{{ $featuredReport->primary_image_url ? '' : ' no-media' }}" @if($featuredReport->primary_image_url) style="background-image: url('{{ $featuredReport->primary_image_url }}');" @endif></div>
                <div class="featured-overlay">
                    <h2>
                        <a href="{{ route('news.show', $featuredReport->slug) }}">{{ $featuredReport->title }}</a>
                    </h2>
                    <p>{{ \Illuminate\Support\Str::limit($featuredReport->excerpt ?? $featuredReport->content, 170) }}</p>
                    <div class="featured-meta">
                        <span class="pill">{{ __('Verified') }}</span>
                        <span class="pill">{{ __('Credibility') }} {{ number_format((float) ($featuredReport->credibility_score ?? 0), 0) }}%</span>
                        <a class="read-more" href="{{ route('news.show', $featuredReport->slug) }}">{{ __('Read More') }}</a>
                    </div>
                </div>
            </article>
        </section>
    @endif

    <section class="overview-section">
        <div class="overview-section-head">
            <h3>{{ __('Latest Verified Reports') }}</h3>
            <a href="{{ route('news.index') }}">{{ __('View all') }}</a>
        </div>

        @if($latestReports->isNotEmpty())
            <div class="report-grid">
                @foreach($latestReports as $report)
                    <article class="report-card glass-card">
                        <div class="report-thumb{{ $report->primary_image_url ? '' : ' no-media' }}" @if($report->primary_image_url) style="background-image: url('{{ $report->primary_image_url }}');" @endif></div>
                        <div class="report-body">
                            <h4>
                                <a href="{{ route('news.show', $report->slug) }}">{{ \Illuminate\Support\Str::limit($report->title, 62) }}</a>
                            </h4>
                            <p>{{ \Illuminate\Support\Str::limit($report->excerpt ?? $report->content, 110) }}</p>
                            <div class="report-meta">
                                <span>{{ number_format((float) ($report->credibility_score ?? 0), 0) }}% {{ __('credible') }}</span>
                                <span>{{ $report->created_at?->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="glass-card">
                <p>{{ __('No verified reports yet.') }}</p>
            </div>
        @endif
    </section>
</div>

@include('partials.footer')

@endsection