<?php

namespace App\Http\Controllers;

use App\Models\Report; 

class HomeController extends Controller
{
    public function index()
    {
        try {
            $verifiedReports = Report::with(['media' => fn ($query) => $query->where('media_type', 'image')->orderBy('id')])
                ->where('status', 'verified')
                ->whereNotNull('reviewed_by')
                ->orderByDesc('is_featured')
                ->latest()
                ->take(8)
                ->get();
        } catch (\Throwable $exception) {
            $verifiedReports = collect();
        }

        $featuredReport = $verifiedReports->firstWhere('is_featured', true) ?? $verifiedReports->first();

        $latestReports = $verifiedReports
            ->when($featuredReport, fn ($collection) => $collection->where('id', '!=', $featuredReport->id))
            ->take(6);

        return view('home', compact('featuredReport', 'latestReports'));
    }
}
