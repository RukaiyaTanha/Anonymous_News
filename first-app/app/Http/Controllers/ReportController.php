<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Report;
use App\Models\ReportMedia;
use App\Services\ReportScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    private function ensureDefaultCategory(): Category
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'general'],
            [
                'name' => 'General',
                'description' => 'General reports category',
            ]
        );
    }

    public function index()
    {
        $reports = Report::where('status', 'verified')
            ->whereNotNull('reviewed_by')
            ->withCount('votes')
            ->latest()
            ->paginate(10);

        return view('reports.index', compact('reports'));
    }
    // Show create form
    public function create()
    {
        $this->ensureDefaultCategory();

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('reports.create', compact('categories'));
    }

    // Store report
    public function store(Request $request, ReportScoringService $reportScoringService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'category_id' => 'nullable|integer|exists:categories,id',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|max:10240|mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime',
        ]);

        $defaultCategory = $this->ensureDefaultCategory();

        $report = new Report();
        $report->user_id = Auth::id();
        $report->category_id = $request->integer('category_id') ?: $defaultCategory->id;
        $report->title = $request->title;
        $report->excerpt = $request->excerpt;
        $report->content = $request->content;
        $report->slug = Str::slug($request->title) . '-' . time();
        $report->status = 'pending';
        $report->credibility_score = null;
        $report->ai_confidence_score = null;

        $report->save();

        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence', []) as $file) {
                $mimeType = (string) $file->getMimeType();
                $mediaType = str_starts_with($mimeType, 'image/')
                    ? 'image'
                    : (str_starts_with($mimeType, 'video/') ? 'video' : 'document');

                ReportMedia::create([
                    'report_id' => $report->id,
                    'file_path' => $file->store('report-media', 'public'),
                    'media_type' => $mediaType,
                    'created_at' => now(),
                ]);
            }
        }

        $reportScoringService->scoreAndPersist($report);

        return redirect()->route('reports.my')->with('success', 'Report submitted successfully!');
    }

    // User's own reports
    public function myReports(Request $request)
    {
        $this->ensureDefaultCategory();

        $search = trim((string) $request->get('search', ''));
        $category = trim((string) $request->get('category', ''));
        $status = trim((string) $request->get('status', ''));

        $categories = Category::query()
            ->orderBy('name')
            ->get(['name', 'slug']);

        $reports = Report::with('category')
            ->where('user_id', Auth::id())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            })
            ->when($category !== '', function ($query) use ($category) {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $category));
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10);

        return view('reports.my', compact('reports', 'search', 'category', 'status', 'categories'));
    }
}
