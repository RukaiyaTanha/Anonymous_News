@extends('layouts.app')

@section('content')
<div class="container stack-md">
    <h2>Pending Reports</h2>

    @forelse($reports as $report)
        <div class="glass-card">
            <h4>{{ $report->title }}</h4>
            <p>{{ $report->excerpt }}</p>

            <form method="POST" action="{{ route('admin.reports.verify', $report->id) }}">
                @csrf
                <button type="submit">Verify</button>
            </form>

            <form method="POST" action="{{ route('admin.reports.reject', $report->id) }}">
                @csrf
                <input type="text" name="note" placeholder="Reason for rejection">
                <button type="submit">Reject</button>
            </form>
        </div>
    @empty
        <p>No pending reports.</p>
    @endforelse
</div>
@endsection