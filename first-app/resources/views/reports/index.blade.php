@extends('layouts.app')

@section('content')
<div class="container stack-md">
    <h2>Verified Reports</h2>

    @forelse($reports as $report)
        <div class="glass-card">
            <h4>{{ $report->title }}</h4>
            <p>{{ $report->excerpt }}</p>

            @if($report->status === 'verified')
                <form method="POST" action="{{ route('reports.vote', $report->id) }}">
                    @csrf
                    <button type="submit">
                        👍 Upvote ({{ $report->votes_count }})
                    </button>
                </form>

                <form method="POST" action="{{ route('reports.flag', $report->id) }}">
                    @csrf
                    <input type="text" name="reason" placeholder="Flag reason" required>
                    <button type="submit">⚠ Flag</button>
                </form>
            @endif
        </div>
    @empty
        <p>No verified reports found.</p>
    @endforelse

    {{ $reports->links() }}
</div>
@endsection
