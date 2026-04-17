@extends('layouts.app')

@section('content')
<div class="container stack-md">
    <h2>{{ __('Verified Reports') }}</h2>

    @forelse($reports as $report)
        <div class="glass-card">
            <h4>{{ $report->title }}</h4>
            <p>{{ $report->excerpt }}</p>

            @if($report->status === 'verified')
                <form method="POST" action="{{ route('reports.vote', $report->id) }}">
                    @csrf
                    <button type="submit">
                        👍 {{ __('Upvote') }} ({{ $report->votes_count }})
                    </button>
                </form>

                <form method="POST" action="{{ route('reports.flag', $report->id) }}">
                    @csrf
                    <input type="text" name="reason" placeholder="{{ __('Flag reason') }}" required>
                    <button type="submit">⚠ {{ __('Flag') }}</button>
                </form>
            @endif
        </div>
    @empty
        <p>{{ __('No verified reports found.') }}</p>
    @endforelse

    {{ $reports->links() }}
</div>
@endsection
