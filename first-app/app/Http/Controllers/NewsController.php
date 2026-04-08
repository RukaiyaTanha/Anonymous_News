<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Report;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    // All verified reports
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $categoryId = $request->filled('category') ? (int) $request->get('category') : null;
        $sort = (string) $request->get('sort', 'latest');
        $range = (string) $request->get('range', 'all');

        $reportsQuery = Report::with([
            'category',
            'media' => fn ($query) => $query->where('media_type', 'image')->orderBy('id'),
        ])
            ->withCount(['votes', 'flags'])
            ->where('status', 'verified')
            ->whereNotNull('reviewed_by')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId));

        if ($range === '7d') {
            $reportsQuery->where('created_at', '>=', now()->subDays(7));
        } elseif ($range === '30d') {
            $reportsQuery->where('created_at', '>=', now()->subDays(30));
        } elseif ($range === '365d') {
            $reportsQuery->where('created_at', '>=', now()->subDays(365));
        }

        if ($sort === 'oldest') {
            $reportsQuery->oldest();
        } elseif ($sort === 'credibility') {
            $reportsQuery->orderByDesc('credibility_score')->latest();
        } else {
            $reportsQuery->latest();
        }

        $reports = $reportsQuery->paginate(9)->withQueryString();

        $votedReportIds = [];
        $flaggedReportIds = [];

        if (auth()->check() && $reports->isNotEmpty()) {
            $reportIds = $reports->getCollection()->pluck('id');

            $votedReportIds = auth()->user()
                ->votes()
                ->whereIn('report_id', $reportIds)
                ->pluck('report_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $flaggedReportIds = auth()->user()
                ->flags()
                ->whereIn('report_id', $reportIds)
                ->pluck('report_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('news.index', compact(
            'reports',
            'categories',
            'search',
            'categoryId',
            'sort',
            'range',
            'votedReportIds',
            'flaggedReportIds'
        ));
    }

    // Single report by slug
    public function show($slug)
    {
        $report = Report::with([
            'category',
            'user',
            'media' => fn ($query) => $query->where('media_type', 'image')->orderBy('id'),
        ])
            ->withCount(['votes', 'flags'])
            ->where('slug', $slug)
            ->where('status', 'verified')
            ->whereNotNull('reviewed_by')
            ->firstOrFail();

        $report->increment('view_count');
        $report->refresh()->load([
            'category',
            'user',
            'media' => fn ($query) => $query->where('media_type', 'image')->orderBy('id'),
        ])->loadCount(['votes', 'flags']);

        $baseSidebarQuery = Report::query()
            ->with([
                'category',
                'media' => fn ($query) => $query->where('media_type', 'image')->orderBy('id'),
            ])
            ->withCount(['votes', 'flags'])
            ->where('status', 'verified')
            ->whereNotNull('reviewed_by')
            ->where('id', '!=', $report->id);

        $relatedReports = (clone $baseSidebarQuery)
            ->when($report->category_id, fn ($query) => $query->where('category_id', $report->category_id))
            ->latest()
            ->take(4)
            ->get();

        if ($relatedReports->count() < 4) {
            $relatedReports = $relatedReports
                ->concat(
                    (clone $baseSidebarQuery)
                        ->whereNotIn('id', $relatedReports->pluck('id'))
                        ->latest()
                        ->take(4 - $relatedReports->count())
                        ->get()
                )
                ->values();
        }

        $trendingReports = (clone $baseSidebarQuery)
            ->withCount(['votes', 'flags'])
            ->orderByDesc('view_count')
            ->orderByDesc('votes_count')
            ->orderBy('flags_count')
            ->latest()
            ->take(4)
            ->get();

        $highCredibilityReports = (clone $baseSidebarQuery)
            ->whereNotNull('credibility_score')
            ->orderByDesc('credibility_score')
            ->orderByDesc('ai_confidence_score')
            ->latest()
            ->take(4)
            ->get();

        if ($highCredibilityReports->count() < 4) {
            $highCredibilityReports = $highCredibilityReports
                ->concat(
                    (clone $baseSidebarQuery)
                        ->whereNotIn('id', $highCredibilityReports->pluck('id'))
                        ->latest()
                        ->take(4 - $highCredibilityReports->count())
                        ->get()
                )
                ->values();
        }

        $previousReport = Report::where('status', 'verified')
            ->whereNotNull('reviewed_by')
            ->where('id', '<', $report->id)
            ->orderByDesc('id')
            ->first();

        $nextReport = Report::where('status', 'verified')
            ->whereNotNull('reviewed_by')
            ->where('id', '>', $report->id)
            ->orderBy('id')
            ->first();

        return view('news.show', compact(
            'report',
            'relatedReports',
            'trendingReports',
            'highCredibilityReports',
            'previousReport',
            'nextReport'
        ));
    }
}
