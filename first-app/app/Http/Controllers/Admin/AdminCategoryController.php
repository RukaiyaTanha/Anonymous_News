<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $editingId = (int) $request->integer('edit');
        $showCreate = $request->boolean('create');

        $categories = Category::query()
            ->withCount('reports')
            ->withMax('reports', 'id')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $editingCategory = $editingId > 0
            ? Category::query()->find($editingId)
            : null;

        return view('admin.categories.index', compact('categories', 'search', 'editingCategory', 'showCreate'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:800'],
        ]);

        Category::query()->create([
            'name' => $validated['name'],
            'slug' => $this->buildUniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:800'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => $this->buildUniqueSlug($validated['name'], $category->id),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    private function buildUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : 'category';
        $counter = 1;

        while (
            Category::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }

        return $slug;
    }
}