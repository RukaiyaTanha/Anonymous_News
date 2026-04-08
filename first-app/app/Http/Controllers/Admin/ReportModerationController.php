<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\Report;
use App\Services\ReportScoringService;

class ReportModerationController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.reports.pending');
    }

    public function verify(Report $report, ReportScoringService $reportScoringService)
    {
        $wasVerified = $report->status === 'verified';

        $reportScoringService->scoreAndPersist($report);

        $report->status = 'verified';
        $report->published_at = $report->published_at ?: now();
        $report->reviewed_by = auth()->id();
        $report->reviewed_at = now();
        $report->save();

        if (! $wasVerified && $report->user) {
            $report->user->increment('reputation_score', 10);
        }

        Notification::create([
            'user_id' => $report->user_id,
            'type' => 'report_verified',
            'message' => 'Your report "'.$report->title.'" was verified by an admin.',
            'is_read' => false,
            'created_at' => now(),
        ]);

        AuditLog::create([
            'admin_id' => auth()->id(),
            'report_id' => $report->id,
            'action_type' => 'approved',
            'ip_hash' => substr(hash('sha256', (string) request()->ip()), 0, 16),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Report approved and published.');
    }

    public function requestRevision(Request $request, Report $report, ReportScoringService $reportScoringService)
    {
        $reportScoringService->scoreAndPersist($report);

        $report->status = 'under_review';
        $report->moderator_note = $request->note;
        $report->reviewed_by = auth()->id();
        $report->reviewed_at = now();
        $report->save();

        Notification::create([
            'user_id' => $report->user_id,
            'type' => 'report_revision_requested',
            'message' => 'Your report "'.$report->title.'" requires revision. Please review moderator feedback.',
            'is_read' => false,
            'created_at' => now(),
        ]);

        AuditLog::create([
            'admin_id' => auth()->id(),
            'report_id' => $report->id,
            'action_type' => 'revision_requested',
            'ip_hash' => substr(hash('sha256', (string) request()->ip()), 0, 16),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Revision requested from reporter.');
    }

    public function reject(Request $request, Report $report)
    {
        $wasRejected = $report->status === 'rejected';

        $report->status = 'rejected';
        $report->moderator_note = $request->note;
        $report->reviewed_by = auth()->id();
        $report->reviewed_at = now();
        $report->save();

        if (! $wasRejected && $report->user) {
            $report->user->reputation_score = max(0, (int) $report->user->reputation_score - 5);
            $report->user->save();
        }

        Notification::create([
            'user_id' => $report->user_id,
            'type' => 'report_rejected',
            'message' => 'Your report "'.$report->title.'" was rejected by an admin.',
            'is_read' => false,
            'created_at' => now(),
        ]);

        AuditLog::create([
            'admin_id' => auth()->id(),
            'report_id' => $report->id,
            'action_type' => 'rejected',
            'ip_hash' => substr(hash('sha256', (string) request()->ip()), 0, 16),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Report rejected.');
    }

}
