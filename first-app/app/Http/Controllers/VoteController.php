<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Report;
use App\Models\Vote;

class VoteController extends Controller
{
    public function store(Report $report)
    {
        // Only verified report can be voted
        if ($report->status !== 'verified') {
            abort(403);
        }

        // Check duplicate vote
        $existingVote = Vote::where('user_id', auth()->id())
            ->where('report_id', $report->id)
            ->first();

        if (!$existingVote) {
            Vote::create([
                'user_id' => auth()->id(),
                'report_id' => $report->id,
                'vote_type' => 'upvote',
                'created_at' => now()
            ]);

            if ($report->user) {
                $report->user->increment('reputation_score');

                if ($report->user_id !== auth()->id()) {
                    Notification::create([
                        'user_id' => $report->user_id,
                        'type' => 'upvote',
                        'message' => 'Your report "'.$report->title.'" received an upvote.',
                        'is_read' => false,
                        'created_at' => now(),
                    ]);
                }
            }
        }

        return back();
    }
}
