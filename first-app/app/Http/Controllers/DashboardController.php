<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $userReportsQuery = Report::with([
            'category',
            'media' => fn ($query) => $query->where('media_type', 'image')->orderBy('id'),
        ])
            ->where('user_id', $userId)
            ->latest();

        $userReports = (clone $userReportsQuery)->paginate(5)->withQueryString();

        $totalSubmissions = Report::where('user_id', $userId)->count();
        $pendingReports = Report::where('user_id', $userId)
            ->whereIn('status', ['pending', 'under_review'])
            ->count();
        $verifiedReports = Report::where('user_id', $userId)
            ->where('status', 'verified')
            ->count();
        $rejectedReports = Report::where('user_id', $userId)
            ->where('status', 'rejected')
            ->count();

        $reputationScore = min(
            100,
            max(
                0,
                (int) round(($verifiedReports * 12) + ($pendingReports * 3) - ($rejectedReports * 6))
            )
        );

        return view('dashboard.index', compact(
            'userReports',
            'totalSubmissions',
            'pendingReports',
            'verifiedReports',
            'rejectedReports',
            'reputationScore'
        ));
    }
}
