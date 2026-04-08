<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalUsers = User::count();
        $totalReports = Report::count();
        $verifiedReports = Report::where('status', 'verified')
            ->whereNotNull('reviewed_by')
            ->count();
        $pendingReports = Report::where('status', 'pending')->count();
        $underReviewReports = Report::where('status', 'under_review')->count();
        $rejectedReports = Report::where('status', 'rejected')->count();

        $reportsToday = Report::whereDate('created_at', now()->toDateString())->count();
        $verifiedToday = Report::where('status', 'verified')
            ->whereNotNull('reviewed_by')
            ->whereDate('updated_at', now()->toDateString())
            ->count();
        $rejectedToday = Report::where('status', 'rejected')
            ->whereDate('updated_at', now()->toDateString())
            ->count();

        $avgAiConfidence = round((float) Report::whereNotNull('ai_confidence_score')->avg('ai_confidence_score'), 1);
        $avgCredibility = round((float) Report::whereNotNull('credibility_score')->avg('credibility_score'), 1);
        $activeUsers = User::has('reports')->count();

        $trendRangeOptions = [
            7 => 'Last 7 Days',
            14 => 'Last 14 Days',
            30 => 'Last 30 Days',
            90 => 'Last 90 Days',
        ];

        $selectedTrendRange = (int) $request->query('trend_range', 7);
        if (! array_key_exists($selectedTrendRange, $trendRangeOptions)) {
            $selectedTrendRange = 7;
        }

        $trendRangeLabel = $trendRangeOptions[$selectedTrendRange];

        $startDate = Carbon::today()->subDays($selectedTrendRange - 1);
        $dateKeys = collect(range(0, $selectedTrendRange - 1))
            ->map(fn ($offset) => $startDate->copy()->addDays($offset)->toDateString());

        $trendRows = Report::selectRaw('DATE(created_at) as day, status, COUNT(*) as count')
            ->whereDate('created_at', '>=', $startDate)
            ->groupBy('day', 'status')
            ->orderBy('day')
            ->get();

        $trendByDay = $trendRows->groupBy('day');

        $chartLabels = $dateKeys->map(fn ($date) => Carbon::parse($date)->format('M d'))->values();

        $trendSeries = [
            'total' => [],
            'verified' => [],
            'pending' => [],
            'rejected' => [],
        ];

        foreach ($dateKeys as $date) {
            $rows = $trendByDay->get($date, collect());
            $byStatus = $rows->pluck('count', 'status');

            $total = (int) $rows->sum('count');
            $verified = (int) Report::query()
                ->whereDate('created_at', $date)
                ->where('status', 'verified')
                ->whereNotNull('reviewed_by')
                ->count();
            $pending = (int) (($byStatus['pending'] ?? 0) + ($byStatus['under_review'] ?? 0));
            $rejected = (int) ($byStatus['rejected'] ?? 0);

            $trendSeries['total'][] = $total;
            $trendSeries['verified'][] = $verified;
            $trendSeries['pending'][] = $pending;
            $trendSeries['rejected'][] = $rejected;
        }

        $categoryRows = Category::withCount('reports')
            ->orderByDesc('reports_count')
            ->take(6)
            ->get()
            ->filter(fn ($category) => $category->reports_count > 0)
            ->values();

        $categoryLabels = $categoryRows->pluck('name')->values();
        $categoryCounts = $categoryRows->pluck('reports_count')->map(fn ($count) => (int) $count)->values();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalReports',
            'verifiedReports',
            'pendingReports',
            'underReviewReports',
            'rejectedReports',
            'reportsToday',
            'verifiedToday',
            'rejectedToday',
            'avgAiConfidence',
            'avgCredibility',
            'activeUsers',
            'trendRangeOptions',
            'selectedTrendRange',
            'trendRangeLabel',
            'chartLabels',
            'trendSeries',
            'categoryLabels',
            'categoryCounts'
        ));
    }
}
