<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ReportWritingAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportAIController extends Controller
{
    public function suggestImprovements(Request $request, ReportWritingAssistantService $assistant): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $categoryName = null;

        if (! empty($validated['category_id'])) {
            $categoryName = Category::query()->whereKey($validated['category_id'])->value('name');
        }

        $suggestion = $assistant->suggest(
            (string) $validated['title'],
            (string) $validated['content'],
            is_string($categoryName) ? $categoryName : null,
        );

        return response()->json($suggestion);
    }
}
