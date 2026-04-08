@extends('layouts.app')

@section('content')
<section class="admin-categories-page">
	<div class="page-shell">
		<header class="admin-hero card-glass">
			<div class="admin-hero-top">
				<span class="admin-kicker">Admin Panel</span>
			</div>

			<div class="admin-hero-main">
				<h1>Category Management</h1>
				<p>Manage and organize news report categories.</p>
			</div>
		</header>

		<section class="admin-panel card-glass">
			<div class="admin-panel-head">
				<h2>Category Management</h2>
				<a href="{{ route('admin.categories.index', ['create' => 1] + request()->except('page')) }}" class="category-create-toggle">+ Add Category</a>
			</div>

			@if (session('success'))
				<div class="category-flash category-flash--success">{{ session('success') }}</div>
			@endif

			@if ($errors->any())
				<div class="category-flash category-flash--error">{{ $errors->first() }}</div>
			@endif

			@if ($showCreate)
				<form method="POST" action="{{ route('admin.categories.store') }}" class="category-editor-form">
					@csrf
					<div>
						<label for="create-name">Category Name</label>
						<input id="create-name" type="text" name="name" value="{{ old('name') }}" required maxlength="120">
					</div>

					<div>
						<label for="create-description">Description</label>
						<textarea id="create-description" name="description" rows="3" maxlength="800">{{ old('description') }}</textarea>
					</div>

					<div class="category-editor-actions">
						<button type="submit" class="category-primary-btn">Create Category</button>
						<a href="{{ route('admin.categories.index', request()->except(['create', 'edit', 'page'])) }}" class="category-cancel-btn">Cancel</a>
					</div>
				</form>
			@endif

			@if ($editingCategory)
				<form method="POST" action="{{ route('admin.categories.update', $editingCategory) }}" class="category-editor-form">
					@csrf
					@method('PATCH')
					<div>
						<label for="edit-name">Edit Category Name</label>
						<input id="edit-name" type="text" name="name" value="{{ old('name', $editingCategory->name) }}" required maxlength="120">
					</div>

					<div>
						<label for="edit-description">Description</label>
						<textarea id="edit-description" name="description" rows="3" maxlength="800">{{ old('description', $editingCategory->description) }}</textarea>
					</div>

					<div class="category-editor-actions">
						<button type="submit" class="category-primary-btn">Save Changes</button>
						<a href="{{ route('admin.categories.index', request()->except(['create', 'edit', 'page'])) }}" class="category-cancel-btn">Cancel</a>
					</div>
				</form>
			@endif

			<form method="GET" action="{{ route('admin.categories.index') }}" class="category-search-form">
				<input
					type="text"
					name="q"
					value="{{ $search }}"
					placeholder="Search categories..."
				>
				<button type="submit" class="category-primary-btn">Search</button>
			</form>

			<div class="category-table-wrap">
				<table class="category-table">
					<thead>
						<tr>
							<th>Category Name</th>
							<th>Report ID</th>
							<th>Timestamp</th>
							<th>Slug</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						@forelse ($categories as $category)
							@php
								$icon = match (strtolower($category->name)) {
									'environment' => '🍃',
									'politics' => '🏛️',
									'technology' => '💻',
									'health' => '❤️',
									'world' => '🌐',
									default => '📁',
								};
							@endphp
							<tr>
								<td>
									<div class="category-name-cell">
										<span>{{ $icon }}</span>
										<strong>{{ $category->name }}</strong>
									</div>
								</td>
								<td>{{ $category->reports_max_id ?? '—' }}</td>
								<td>{{ $category->updated_at?->format('M d, Y - h:i A') ?? '—' }}</td>
								<td>{{ $category->slug }}</td>
								<td>
									<a
										href="{{ route('admin.categories.index', ['edit' => $category->id] + request()->except('page')) }}"
										class="category-edit-btn"
									>
										Edit
									</a>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="5" class="category-empty">No categories found.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			<div class="category-pagination">{{ $categories->links() }}</div>
		</section>
	</div>
</section>
@endsection
