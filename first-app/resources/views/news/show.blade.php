@extends('layouts.app')

@section('content')
<div class="container report-detail-page">
    @php
        $primaryImageUrl = $report->primary_image_url;
        $inlineGalleryMedia = ($report->media ?? collect())
            ->where('media_type', 'image')
            ->skip(1)
            ->take(2)
            ->values();
    @endphp

    <section class="overview-section-headline">
        <h1>{{ \Illuminate\Support\Str::limit($report->title, 52) }}</h1>
    </section>

    <section class="report-hero glass-card" style="background-image: linear-gradient(120deg, rgba(15, 23, 42, 0.72), rgba(30, 64, 175, 0.58)){{ $primaryImageUrl ? ", url('{$primaryImageUrl}')" : '' }};">
        <div class="report-hero-inner">
            <h2>{{ $report->title }}</h2>
            <p>{{ \Illuminate\Support\Str::limit($report->excerpt ?? $report->content, 165) }}</p>
        </div>
    </section>

    <section class="report-meta-bar glass-card">
        <span class="meta-item meta-category">
            <span class="meta-icon-badge" aria-hidden="true">
                <svg class="meta-icon" viewBox="0 0 24 24" focusable="false">
                    <path d="M3 7a2 2 0 0 1 2-2h4.3a2 2 0 0 1 1.4.58L12 7h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" />
                </svg>
            </span>
            <span>{{ $report->category?->name ?? 'General' }}</span>
        </span>
        <span class="meta-item">
            <span class="meta-icon-badge" aria-hidden="true">
                <svg class="meta-icon" viewBox="0 0 24 24" focusable="false">
                    <path d="M7 2h2v2h6V2h2v2h2a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2V2Zm12 8H5v8h14v-8Z" />
                </svg>
            </span>
            <span>{{ $report->created_at?->format('M j, Y') }}</span>
        </span>
        <span class="meta-item">
            <span class="meta-icon-badge" aria-hidden="true">
                <svg class="meta-icon" viewBox="0 0 24 24" focusable="false">
                    <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z" />
                </svg>
            </span>
            <span>Anonymous Reporter</span>
        </span>
        <span class="meta-item">
            <span class="meta-icon-badge" aria-hidden="true">
                <svg class="meta-icon" viewBox="0 0 24 24" focusable="false">
                    <path d="M12 5c5.5 0 9.5 5.5 9.5 7s-4 7-9.5 7S2.5 14.5 2.5 12 6.5 5 12 5Zm0 2C8.1 7 5.1 10.7 4.6 12c.5 1.3 3.5 5 7.4 5s6.9-3.7 7.4-5c-.5-1.3-3.5-5-7.4-5Zm0 2.5A2.5 2.5 0 1 1 12 14a2.5 2.5 0 0 1 0-5Z" />
                </svg>
            </span>
            <span>
                {{ number_format((int) $report->view_count) }}
                {{ \Illuminate\Support\Str::plural('View', (int) $report->view_count) }}
            </span>
        </span>
    </section>

    <section class="report-content-layout">
        <article class="report-main glass-card">
            <h3>{{ $report->title }}</h3>

            <button
                type="button"
                class="report-main-media report-image-trigger{{ $primaryImageUrl ? '' : ' no-media' }}"
                @if($primaryImageUrl)
                    style="background-image: url('{{ $primaryImageUrl }}');"
                    @click="openImageModal(@js($primaryImageUrl), @js($report->title))"
                    aria-label="Open main image preview"
                @else
                    disabled
                    aria-label="No main image available"
                @endif
            ></button>

            <div class="report-score-row">
                <div class="score-item">
                    <strong>{{ number_format((float) ($report->ai_confidence_score ?? 0), 0) }}%</strong>
                    <span>Likely Accurate</span>
                </div>
                <div class="score-item">
                    <strong>{{ number_format((float) ($report->credibility_score ?? 0), 0) }}%</strong>
                    <span>Verified</span>
                </div>
                <div class="score-item">
                    <strong>{{ number_format((int) $report->view_count) }}</strong>
                    <span>Total Views</span>
                </div>
            </div>

            <div class="report-prose">
                @foreach(collect(preg_split('/\n\s*\n/', trim((string) $report->content)))->filter() as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>

            @if($inlineGalleryMedia->isNotEmpty())
                <div class="report-inline-gallery">
                    @foreach($inlineGalleryMedia as $media)
                        @php
                            $galleryImageUrl = asset('storage/' . $media->file_path);
                        @endphp
                        <button
                            type="button"
                            class="gallery-image gallery-image--button"
                            style="background-image: url('{{ $galleryImageUrl }}');"
                            @click="openImageModal(@js($galleryImageUrl), @js($report->title))"
                            aria-label="Open image preview"
                        ></button>
                    @endforeach
                </div>
            @endif

            <div class="report-action-stats">
                <span>
                    {{ number_format((int) ($report->votes_count ?? 0)) }}
                    {{ \Illuminate\Support\Str::plural('Upvote', (int) ($report->votes_count ?? 0)) }}
                </span>
                <span>
                    {{ number_format((int) ($report->flags_count ?? 0)) }}
                    {{ \Illuminate\Support\Str::plural('Flag', (int) ($report->flags_count ?? 0)) }}
                </span>
            </div>

            <div class="report-actions">
                @auth
                    <form method="POST" action="{{ route('reports.vote', $report->id) }}" class="vote-form">
                        @csrf
                        <button type="submit" class="action-pill action-upvote">
                            <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M14 9V4a2 2 0 0 0-2-2l-1 7v11h8a2 2 0 0 0 1.95-1.55l1.2-6A2 2 0 0 0 20.2 10H14Zm-9 2h4v9H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2Z" />
                            </svg>
                            <span>Upvote Report</span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('reports.flag', $report->id) }}" class="flag-form">
                        @csrf
                        <input type="text" name="reason" placeholder="Flag reason" required>
                        <button type="submit" class="action-pill action-flag">
                            <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M4 3h2v18H4V3Zm4 1h10l-2 4 2 4H8V4Z" />
                            </svg>
                            <span>Flag Report</span>
                        </button>
                    </form>
                @else
                    <a href="{{ request()->fullUrlWithQuery(['auth' => 'login']) }}" class="action-pill action-upvote">
                        <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M14 9V4a2 2 0 0 0-2-2l-1 7v11h8a2 2 0 0 0 1.95-1.55l1.2-6A2 2 0 0 0 20.2 10H14Zm-9 2h4v9H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2Z" />
                        </svg>
                        <span>Login to Upvote</span>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['auth' => 'login']) }}" class="action-pill action-flag">
                        <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M4 3h2v18H4V3Zm4 1h10l-2 4 2 4H8V4Z" />
                        </svg>
                        <span>Login to Flag</span>
                    </a>
                @endauth
            </div>
        </article>

        <aside class="report-sidebar">
            <section class="glass-card sidebar-block">
                <h4>Related Reports</h4>
                @forelse($relatedReports as $item)
                    <a class="sidebar-item" href="{{ route('news.show', $item->slug) }}">
                        <span class="mini-thumb{{ $item->primary_image_url ? '' : ' no-media' }}" @if($item->primary_image_url) style="background-image: url('{{ $item->primary_image_url }}');" @endif></span>
                        <span>
                            <strong>{{ \Illuminate\Support\Str::limit($item->title, 52) }}</strong>
                            <small>{{ $item->created_at?->format('M j, Y') }}</small>
                        </span>
                    </a>
                @empty
                    <p>No related reports yet.</p>
                @endforelse
            </section>

            <section class="glass-card sidebar-block">
                <h4>Trending Reports</h4>
                @forelse($trendingReports as $item)
                    <a class="sidebar-item" href="{{ route('news.show', $item->slug) }}">
                        <span class="mini-thumb{{ $item->primary_image_url ? '' : ' no-media' }}" @if($item->primary_image_url) style="background-image: url('{{ $item->primary_image_url }}');" @endif></span>
                        <span>
                            <strong>{{ \Illuminate\Support\Str::limit($item->title, 52) }}</strong>
                            <small>
                                {{ number_format((int) $item->view_count) }}
                                {{ \Illuminate\Support\Str::plural('view', (int) $item->view_count) }}
                                @if(isset($item->votes_count))
                                    · {{ number_format((int) $item->votes_count) }} upvotes
                                @endif
                            </small>
                        </span>
                    </a>
                @empty
                    <p>No trending reports yet.</p>
                @endforelse
            </section>

            <section class="glass-card sidebar-block">
                <h4>Highest Credibility</h4>
                @forelse($highCredibilityReports as $item)
                    <a class="sidebar-item" href="{{ route('news.show', $item->slug) }}">
                        <span class="mini-thumb{{ $item->primary_image_url ? '' : ' no-media' }}" @if($item->primary_image_url) style="background-image: url('{{ $item->primary_image_url }}');" @endif></span>
                        <span>
                            <strong>{{ \Illuminate\Support\Str::limit($item->title, 52) }}</strong>
                            <small>{{ number_format((float) ($item->credibility_score ?? 0), 0) }}% credible</small>
                        </span>
                    </a>
                @empty
                    <p>No high-credibility reports yet.</p>
                @endforelse
            </section>
        </aside>
    </section>

    <section class="detail-pagination glass-card">
        <div>
            @if($previousReport)
                <a href="{{ route('news.show', $previousReport->slug) }}">Previous</a>
            @endif
        </div>
        <div>
            @if($nextReport)
                <a href="{{ route('news.show', $nextReport->slug) }}">Next</a>
            @endif
        </div>
    </section>
</div>
@endsection