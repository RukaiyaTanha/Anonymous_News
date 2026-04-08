<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Report;
use App\Services\ReportScoringService;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function pending(Request $request, ReportScoringService $reportScoringService)
    {
        $selectedCategory = (int) $request->integer('category_id');
        $selectedSort = $request->string('sort')->toString();
        $search = trim((string) $request->string('q'));

        if (! in_array($selectedSort, ['newest', 'oldest'], true)) {
            $selectedSort = 'newest';
        }

        $reportsQuery = Report::with([
            'user:id,username,email,reputation_score',
            'category:id,name',
            'media' => fn ($query) => $query->where('media_type', 'image')->orderBy('id'),
        ])
            ->where(function ($query) {
                $query->whereIn('status', ['pending', 'under_review'])
                    ->orWhere(function ($fallbackQuery) {
                        $fallbackQuery->where('status', 'verified')
                            ->whereNull('reviewed_by');
                    });
            });

        if ($selectedCategory > 0) {
            $reportsQuery->where('category_id', $selectedCategory);
        }

        if ($search !== '') {
            $reportsQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($selectedSort === 'oldest') {
            $reportsQuery->oldest();
        } else {
            $reportsQuery->latest();
        }

        $reports = $reportsQuery->paginate(8)->withQueryString();
        $reports->getCollection()->transform(function (Report $report) use ($reportScoringService) {
            $scores = $reportScoringService->scoresForDisplay($report);

            $report->ai_confidence_score = $scores['ai_confidence_score'];
            $report->credibility_score = $scores['credibility_score'];
            $report->duplicate_similarity_score = $scores['duplicate_similarity_score'];
            $report->reporter_reputation_score = $scores['reporter_reputation_score'] ?? (float) ($report->user?->reputation_score ?? 0);
            $report->ai_realism_assessment = $scores['ai_realism_assessment'] ?? 'uncertain';
            $report->ai_suspicious_indicators = $scores['ai_suspicious_indicators'] ?? [];
            $report->ai_entities = $scores['ai_entities'] ?? [];

            return $report;
        });

        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.reports.pending', compact('reports', 'categories', 'selectedCategory', 'selectedSort', 'search'));
    }

    public function verified(Request $request)
    {
        $selectedCategory = (int) $request->integer('category_id');
        $selectedSort = $request->string('sort')->toString();
        $search = trim((string) $request->string('q'));

        if (! in_array($selectedSort, ['newest', 'oldest'], true)) {
            $selectedSort = 'newest';
        }

        $reportsQuery = Report::with(['user:id,username,email', 'category:id,name'])
            ->where('status', 'verified')
            ->whereNotNull('reviewed_by');

        if ($selectedCategory > 0) {
            $reportsQuery->where('category_id', $selectedCategory);
        }

        if ($search !== '') {
            $reportsQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($selectedSort === 'oldest') {
            $reportsQuery->oldest();
        } else {
            $reportsQuery->latest();
        }

        $reports = $reportsQuery->paginate(8)->withQueryString();
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.reports.verified', compact('reports', 'categories', 'selectedCategory', 'selectedSort', 'search'));
    }

    public function approve(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $wasVerified = $report->status === 'verified';

        $report->status = 'verified';
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
            'ip_hash' => substr(hash('sha256', (string) $request->ip()), 0, 16),
            'created_at' => now(),
        ]);

        // User notification can be added here

        return redirect()->back()->with('success', 'Report approved.');
    }

    public function reject(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $wasRejected = $report->status === 'rejected';

        $report->status = 'rejected';
        $report->moderator_note = $request->moderator_note ?? null;
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
            'ip_hash' => substr(hash('sha256', (string) $request->ip()), 0, 16),
            'created_at' => now(),
        ]);

        // User notification can be added here

        return redirect()->back()->with('success', 'Report rejected.');
    }
}
