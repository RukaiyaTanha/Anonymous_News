@extends('layouts.app')

@section('content')
<div class="container user-dashboard-page">
    <section class="dashboard-head glass-card">
        <div>
            <h1>{{ __('Welcome back, :name!', ['name' => auth()->user()->username ?? __('User')]) }}</h1>
            <p>{{ __('Here is a quick overview of your reporting activity and platform status.') }}</p>
        </div>
        <a href="{{ route('reports.create') }}" class="dashboard-submit-btn">{{ __('Submit News') }}</a>
    </section>

    <section class="dashboard-stats-grid">
        <article class="dashboard-stat-card glass-card">
            <small>{{ __('Total Submissions') }}</small>
            <strong>{{ $totalSubmissions }}</strong>
        </article>
        <article class="dashboard-stat-card glass-card">
            <small>{{ __('Pending Reports') }}</small>
            <strong>{{ $pendingReports }}</strong>
        </article>
        <article class="dashboard-stat-card glass-card">
            <small>{{ __('Verified Reports') }}</small>
            <strong>{{ $verifiedReports }}</strong>
        </article>
        <article class="dashboard-stat-card glass-card">
            <small>{{ __('Rejected Reports') }}</small>
            <strong>{{ $rejectedReports }}</strong>
        </article>
    </section>

    <section class="dashboard-main-layout">
        <article class="dashboard-recent glass-card">
            <div class="dashboard-block-head">
                <h3>{{ __('Recent Submissions') }}</h3>
            </div>

            @if($userReports->count())
                <div class="submission-table-wrap">
                    <table class="submission-table">
                        <thead>
                            <tr>
                                <th>{{ __('Photo') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userReports as $report)
                                <tr>
                                    <td>
                                        <span class="dashboard-table-thumb{{ $report->primary_image_url ? '' : ' no-media' }}" @if($report->primary_image_url) style="background-image: url('{{ $report->primary_image_url }}');" @endif></span>
                                    </td>
                                    <td>
                                        <span class="dashboard-table-title">{{ \Illuminate\Support\Str::limit($report->title, 56) }}</span>
                                    </td>
                                    <td>{{ $report->category?->name ?? __('General') }}</td>
                                    <td>{{ $report->created_at?->format('M j, Y') }}</td>
                                    <td>
                                        <span class="status-pill status-{{ str_replace('_', '-', $report->status) }}">{{ ucfirst(str_replace('_', ' ', $report->status)) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="dashboard-pagination">
                    <a href="{{ route('reports.my') }}">{{ __('View all') }}</a>
                    <div>{{ $userReports->links() }}</div>
                </div>
            @else
                <p>{{ __('You have not submitted any reports yet.') }}</p>
            @endif
        </article>

        <aside class="dashboard-side">
            <section class="glass-card dashboard-side-card reputation-card">
                <h4>{{ __('Reputation Score') }}</h4>
                <div class="reputation-score">{{ $reputationScore }}</div>
                <div class="reputation-bar">
                    <span style="width: {{ $reputationScore }}%"></span>
                </div>
                <small>{{ __('Based on verified, pending, and rejected report outcomes.') }}</small>
            </section>
        </aside>
    </section>
</div>
@endsection