@extends('layouts.app')

@section('content')
<div class="container submissions-page" id="submissions-page">
    <section class="submit-header glass-card">
        <h1>My Submissions</h1>
        <p>Track the status of your news reports you've submitted.</p>
    </section>

    <section class="submissions-layout">
        <article class="glass-card submissions-main">
            <div class="dashboard-block-head">
                <h3>My Submissions</h3>
            </div>

            <form method="GET" action="{{ route('reports.my') }}" class="submission-filters">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by title or summary">

                <select name="category">
                    <option value="">Categories</option>
                    @foreach(($categories ?? collect()) as $categoryOption)
                        <option value="{{ $categoryOption->slug }}" @selected(($category ?? '') === $categoryOption->slug)>
                            {{ $categoryOption->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status">
                    <option value="">Status</option>
                    <option value="pending" @selected(($status ?? '') === 'pending')>Pending</option>
                    <option value="under_review" @selected(($status ?? '') === 'under_review')>Under Review</option>
                    <option value="verified" @selected(($status ?? '') === 'verified')>Verified</option>
                    <option value="rejected" @selected(($status ?? '') === 'rejected')>Rejected</option>
                </select>

                <button type="submit">Apply</button>
            </form>

            @if($reports->count())
                <div class="submission-table-wrap">
                    <table class="submission-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr>
                                    <td>{{ \Illuminate\Support\Str::limit($report->title, 42) }}</td>
                                    <td>{{ $report->category?->name ?? 'General' }}</td>
                                    <td>
                                        <span class="status-pill status-{{ str_replace('_', '-', $report->status) }}">{{ ucfirst(str_replace('_', ' ', $report->status)) }}</span>
                                    </td>
                                    <td>{{ $report->created_at?->format('M j, Y') }}</td>
                                    <td>
                                        @php
                                            $reportPayload = [
                                                'title' => $report->title,
                                                'category' => $report->category?->name ?? 'General',
                                                'statusLabel' => ucfirst(str_replace('_', ' ', $report->status)),
                                                'statusClass' => 'status-' . str_replace('_', '-', $report->status),
                                                'submittedAt' => $report->created_at?->format('M j, Y h:i A') ?? 'N/A',
                                                'summary' => $report->excerpt ?? '',
                                                'content' => $report->content ?? '',
                                                'publishedUrl' => $report->status === 'verified' ? route('news.show', $report->slug) : null,
                                            ];
                                        @endphp
                                        <button
                                            type="button"
                                            class="submission-view-btn js-submission-view-btn"
                                            data-report='@json($reportPayload)'
                                        >
                                            View
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="dashboard-pagination">
                    <a href="{{ route('reports.my') }}">View all</a>
                    <div>{{ $reports->links() }}</div>
                </div>
            @else
                <p>No submissions found.</p>
            @endif
        </article>
    </section>

    <div class="submission-modal-backdrop" id="submission-modal-backdrop">
        <section class="submission-modal glass-card" id="submission-modal" role="dialog" aria-modal="true" aria-labelledby="submission-modal-title">
            <header class="submission-modal-head">
                <div>
                    <h3 id="submission-modal-title"></h3>
                    <p>Submission details</p>
                </div>
                <button type="button" class="submission-modal-close" id="submission-modal-close">Close</button>
            </header>

            <div class="submission-modal-grid">
                <div class="submission-modal-item">
                    <span>Category</span>
                    <strong id="submission-modal-category"></strong>
                </div>
                <div class="submission-modal-item">
                    <span>Status</span>
                    <strong>
                        <span class="status-pill" id="submission-modal-status"></span>
                    </strong>
                </div>
                <div class="submission-modal-item">
                    <span>Submitted</span>
                    <strong id="submission-modal-submitted"></strong>
                </div>
            </div>

            <div class="submission-modal-section">
                <h4>Summary</h4>
                <p id="submission-modal-summary"></p>
            </div>

            <div class="submission-modal-section">
                <h4>Full Content</h4>
                <p class="submission-modal-content" id="submission-modal-content"></p>
            </div>

            <div class="submission-modal-actions">
                <a href="#" class="submission-modal-link is-hidden" id="submission-modal-published-link">Open Published News</a>
            </div>
        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const page = document.getElementById('submissions-page');
        const backdrop = document.getElementById('submission-modal-backdrop');
        const closeButton = document.getElementById('submission-modal-close');
        const publishedLink = document.getElementById('submission-modal-published-link');

        if (!page || !backdrop || !closeButton || !publishedLink) {
            return;
        }

        const titleField = document.getElementById('submission-modal-title');
        const categoryField = document.getElementById('submission-modal-category');
        const statusField = document.getElementById('submission-modal-status');
        const submittedField = document.getElementById('submission-modal-submitted');
        const summaryField = document.getElementById('submission-modal-summary');
        const contentField = document.getElementById('submission-modal-content');

        const closeModal = function () {
            backdrop.classList.remove('is-open');
            page.classList.remove('is-modal-open');
            document.body.style.overflow = '';
        };

        const openModal = function (report) {
            titleField.textContent = report.title || 'Submission details';
            categoryField.textContent = report.category || 'General';
            submittedField.textContent = report.submittedAt || 'N/A';
            summaryField.textContent = report.summary || 'No summary available.';
            contentField.textContent = report.content || 'No content available.';

            statusField.className = 'status-pill';
            if (report.statusClass) {
                statusField.classList.add(report.statusClass);
            }
            statusField.textContent = report.statusLabel || 'Unknown';

            if (report.publishedUrl) {
                publishedLink.classList.remove('is-hidden');
                publishedLink.setAttribute('href', report.publishedUrl);
            } else {
                publishedLink.classList.add('is-hidden');
                publishedLink.setAttribute('href', '#');
            }

            backdrop.classList.add('is-open');
            page.classList.add('is-modal-open');
            document.body.style.overflow = 'hidden';
        };

        page.querySelectorAll('.js-submission-view-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                try {
                    const report = JSON.parse(button.dataset.report || '{}');
                    openModal(report);
                } catch (error) {
                    console.error(error);
                }
            });
        });

        closeButton.addEventListener('click', closeModal);

        backdrop.addEventListener('click', function (event) {
            if (event.target === backdrop) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && backdrop.classList.contains('is-open')) {
                closeModal();
            }
        });
    });
</script>
@endsection