@extends('layouts.app')

@section('content')
<div class="container submit-page">
    <section class="submit-header glass-card">
        <h1>Submit News</h1>
        <p>Submit news anonymously. Please provide as much detail as possible to help our AI and moderators verify your report.</p>
    </section>

    @if ($errors->any())
        <section class="glass-card submit-errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="submit-layout">
        <form class="glass-card submit-form" action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="submit-field">
                <label for="title">Title</label>
                <input id="title" type="text" name="title" placeholder="Title" value="{{ old('title') }}" required>
            </div>

            <div class="submit-field">
                <label for="content">Content <span>(Required)</span></label>
                <textarea id="content" name="content" placeholder="Write your report details here..." required>{{ old('content') }}</textarea>
            </div>

            <div class="submit-field">
                <label for="excerpt">Summary / Excerpt</label>
                <textarea id="excerpt" name="excerpt" placeholder="Short summary for listing pages..." required>{{ old('excerpt') }}</textarea>
            </div>

            <div class="submit-field">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">Category</option>
                    @foreach(($categories ?? collect()) as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id') === (int) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="submit-field">
                <label for="location">Optional Geo Location</label>
                <input id="location" type="text" name="location" placeholder="Enter location keyword (city, region)" value="{{ old('location') }}">
            </div>

            <div class="submit-field submit-file-field">
                <label for="evidence">Optional Evidence Upload</label>
                <input id="evidence" type="file" name="evidence[]" class="submit-file-input" accept="image/*,video/*" multiple>
                <small class="submit-file-help">Attach images or videos to strengthen your report (optional).</small>
            </div>

            <div class="submit-note glass-card">
                <p>Submit accurate information with supporting evidence.</p>
                <p>Your report will undergo AI verification and moderator review.</p>
                <p>Reports that pass verification will be published anonymously.</p>
            </div>

            <button type="submit" class="submit-report-btn">Submit Report</button>
        </form>

        <aside class="glass-card submit-guidelines">
            <h3>Submission Guidelines</h3>
            <ul>
                <li>Submit accurate information with any supporting evidence.</li>
                <li>Your report will undergo AI verification and moderator review.</li>
                <li>Reports that pass verification will be published anonymously.</li>
            </ul>
        </aside>
    </section>
</div>
@endsection