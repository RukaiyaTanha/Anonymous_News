@extends('layouts.app')

@section('content')
<section class="admin-dashboard-page">
    <div class="page-shell">
        <header class="admin-hero card-glass">
            <div class="admin-hero-top">
                <span class="admin-kicker">Admin Panel</span>
            </div>

            <div class="admin-hero-main">
                <h1>Admin Dashboard</h1>
                <p>Welcome to the Admin Panel. Monitor and manage verified news reports.</p>
            </div>
        </header>

        <section class="admin-panel card-glass">
            <div class="admin-panel-head">
                <h2>Statistics</h2>
                <a href="{{ route('admin.reports') }}" class="btn btn-outline">Manage Reports</a>
            </div>

            <div class="admin-stat-grid admin-stat-grid-primary">
                <article class="admin-stat-card tone-blue">
                    <p>Total Submissions</p>
                    <strong>{{ number_format($totalReports) }}</strong>
                    <small>{{ number_format($reportsToday) }} today</small>
                </article>

                <article class="admin-stat-card tone-orange">
                    <p>Pending / Review</p>
                    <strong>{{ number_format($pendingReports + $underReviewReports) }}</strong>
                    <small>{{ number_format($pendingReports) }} pending, {{ number_format($underReviewReports) }} reviewing</small>
                </article>

                <article class="admin-stat-card tone-green">
                    <p>Verified Reports</p>
                    <strong>{{ number_format($verifiedReports) }}</strong>
                    <small>{{ number_format($verifiedToday) }} updated today</small>
                </article>

                <article class="admin-stat-card tone-red">
                    <p>Rejected Reports</p>
                    <strong>{{ number_format($rejectedReports) }}</strong>
                    <small>{{ number_format($rejectedToday) }} updated today</small>
                </article>
            </div>

            <div class="admin-stat-grid admin-stat-grid-secondary">
                <article class="admin-mini-card">
                    <p>AI Avg. Confidence</p>
                    <strong>{{ number_format($avgAiConfidence, 1) }}%</strong>
                </article>

                <article class="admin-mini-card">
                    <p>Avg. Credibility</p>
                    <strong>{{ number_format($avgCredibility, 1) }}%</strong>
                </article>

                <article class="admin-mini-card">
                    <p>Active Users</p>
                    <strong>{{ number_format($activeUsers) }}</strong>
                </article>

                <article class="admin-mini-card">
                    <p>Total Users</p>
                    <strong>{{ number_format($totalUsers) }}</strong>
                </article>
            </div>
        </section>

        @php
            $hasTrendData = collect($trendSeries)->flatten()->sum() > 0;
            $hasCategoryData = collect($categoryCounts)->sum() > 0;
        @endphp

        <section class="admin-charts-grid">
            <article class="admin-chart-card card-glass">
                <div class="admin-chart-head">
                    <h3>Reports Over Time</h3>
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="admin-chart-range-form">
                        <select
                            name="trend_range"
                            class="admin-chart-range-select"
                            aria-label="Select reports over time range"
                            onchange="this.form.submit()"
                        >
                            @foreach($trendRangeOptions as $rangeValue => $rangeLabel)
                                <option value="{{ $rangeValue }}" @selected((int) $selectedTrendRange === (int) $rangeValue)>
                                    {{ $rangeLabel }}
                                </option>
                            @endforeach
                        </select>
                        <noscript>
                            <button type="submit" class="btn btn-outline">Apply</button>
                        </noscript>
                    </form>
                </div>
                <div class="admin-chart-area">
                    @if ($hasTrendData)
                        <canvas id="adminTrendChart"></canvas>
                    @else
                        <div class="admin-chart-empty">No report activity in the selected period yet.</div>
                    @endif
                </div>
            </article>

            <article class="admin-chart-card card-glass">
                <div class="admin-chart-head">
                    <h3>Category Distribution</h3>
                    <span>All Reports</span>
                </div>
                <div class="admin-chart-area">
                    @if ($hasCategoryData)
                        <canvas id="adminCategoryChart"></canvas>
                    @else
                        <div class="admin-chart-empty">No categorized reports available yet.</div>
                    @endif
                </div>
            </article>
        </section>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (() => {
        const labels = @json($chartLabels);
        const trend = @json($trendSeries);
        const categoryLabels = @json($categoryLabels);
        const categoryCounts = @json($categoryCounts);

        const trendCanvas = document.getElementById('adminTrendChart');
        if (trendCanvas && labels.length) {
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Total',
                            data: trend.total,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.16)',
                            tension: 0.35,
                            fill: true,
                            borderWidth: 2
                        },
                        {
                            label: 'Verified',
                            data: trend.verified,
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22, 163, 74, 0.12)',
                            tension: 0.35,
                            fill: false,
                            borderWidth: 2
                        },
                        {
                            label: 'Pending',
                            data: trend.pending,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.35,
                            fill: false,
                            borderWidth: 2
                        },
                        {
                            label: 'Rejected',
                            data: trend.rejected,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.35,
                            fill: false,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        const categoryCanvas = document.getElementById('adminCategoryChart');
        if (categoryCanvas && categoryLabels.length) {
            new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [
                        {
                            data: categoryCounts,
                            backgroundColor: [
                                '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6'
                            ],
                            borderWidth: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }
    })();
</script>
@endsection