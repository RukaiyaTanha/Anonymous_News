@extends('layouts.app')

@section('content')
<div class="container submit-page">
    <section class="submit-header glass-card">
        <h1>{{ __('Submit News') }}</h1>
        <p>{{ __('Submit news anonymously. Please provide as much detail as possible to help our AI and moderators verify your report.') }}</p>
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
                <label for="title">{{ __('Title') }}</label>
                <input id="title" type="text" name="title" placeholder="{{ __('Title') }}" value="{{ old('title') }}" required>
            </div>

            <div class="submit-field">
                <label for="content">{{ __('Content') }} <span>({{ __('Required') }})</span></label>
                <textarea id="content" name="content" placeholder="{{ __('Write your report details here...') }}" required>{{ old('content') }}</textarea>
            </div>

            <div class="submit-field">
                <label for="excerpt">{{ __('Summary / Excerpt') }}</label>
                <textarea id="excerpt" name="excerpt" placeholder="{{ __('Short summary for listing pages...') }}" required>{{ old('excerpt') }}</textarea>
            </div>

            <div class="submit-field">
                <label for="category_id">{{ __('Category') }}</label>
                <select id="category_id" name="category_id">
                    <option value="">{{ __('Category') }}</option>
                    @foreach(($categories ?? collect()) as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id') === (int) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="submit-field">
                <label for="location">{{ __('Optional Geo Location') }}</label>
                <input id="location" type="text" name="location" placeholder="{{ __('Enter location keyword (city, region)') }}" value="{{ old('location') }}">
            </div>

            <div class="submit-field submit-file-field">
                <label for="evidence">{{ __('Optional Evidence Upload') }}</label>
                <input id="evidence" type="file" name="evidence[]" class="submit-file-input" accept="image/*,video/*" multiple>
                <small class="submit-file-help">{{ __('Attach images or videos to strengthen your report (optional).') }}</small>
            </div>

            <div class="submit-note glass-card">
                <p>{{ __('Submit accurate information with supporting evidence.') }}</p>
                <p>{{ __('Your report will undergo AI verification and moderator review.') }}</p>
                <p>{{ __('Reports that pass verification will be published anonymously.') }}</p>
            </div>

            <button type="submit" class="submit-report-btn">{{ __('Submit Report') }}</button>
        </form>

        <aside class="submit-aside-stack">
            <section class="glass-card smart-suggestion-panel" id="smartSuggestionPanel">
                <div class="smart-suggestion-head">
                    <h3>{{ __('Smart Suggestion') }}</h3>
                    <p>{{ __('Improve your draft before submission. AI suggestions never auto-replace your text.') }}</p>
                </div>

                <button type="button" class="smart-improve-btn" id="improveWithAiBtn">{{ __('Improve with AI') }}</button>
                <p class="smart-suggestion-status" id="aiSuggestionStatus" aria-live="polite"></p>
                <p class="smart-suggestion-source" id="aiSuggestionSource"></p>
                <p class="smart-suggestion-error" id="aiSuggestionError"></p>

                <div class="smart-suggestion-body" id="aiSuggestionBody" hidden>
                    <div class="smart-block">
                        <div class="smart-block-head">
                            <strong>{{ __('Suggested Title') }}</strong>
                            <button type="button" class="smart-use-btn" data-apply-target="title">{{ __('Use Suggestion') }}</button>
                        </div>
                        <p id="aiSuggestedTitle" class="smart-suggested-text"></p>
                    </div>

                    <div class="smart-block">
                        <div class="smart-block-head">
                            <strong>{{ __('Suggested Summary') }}</strong>
                            <button type="button" class="smart-use-btn" data-apply-target="excerpt">{{ __('Use Suggestion') }}</button>
                        </div>
                        <p id="aiSuggestedSummary" class="smart-suggested-text"></p>
                    </div>

                    <div class="smart-block">
                        <div class="smart-block-head">
                            <strong>{{ __('Improved Description') }}</strong>
                            <button type="button" class="smart-use-btn" data-apply-target="content">{{ __('Use Suggestion') }}</button>
                        </div>
                        <p id="aiSuggestedContent" class="smart-suggested-text smart-suggested-content"></p>
                    </div>

                    <div class="smart-metrics-grid">
                        <article class="smart-metric-card">
                            <small>{{ __('Readability') }}</small>
                            <strong id="aiReadabilityScore">-</strong>
                        </article>
                        <article class="smart-metric-card">
                            <small>{{ __('Clarity') }}</small>
                            <strong id="aiClarityScore">-</strong>
                        </article>
                        <article class="smart-metric-card">
                            <small>{{ __('Tone') }}</small>
                            <strong id="aiTone">-</strong>
                        </article>
                    </div>

                    <div class="smart-block">
                        <strong>{{ __('SEO Keywords') }}</strong>
                        <div class="smart-chip-row" id="aiKeywords"></div>
                    </div>

                    <div class="smart-block">
                        <strong>{{ __('SEO Feedback') }}</strong>
                        <ul id="aiSeoFeedback"></ul>
                    </div>

                    <div class="smart-decision-row">
                        <button type="button" class="smart-use-btn smart-use-all" id="useAllSuggestionsBtn">{{ __('Use All Suggestions') }}</button>
                        <button type="button" class="smart-keep-btn" id="keepOriginalBtn">{{ __('Keep Original') }}</button>
                    </div>
                </div>
            </section>

            <section class="glass-card submit-guidelines">
                <h3>{{ __('Submission Guidelines') }}</h3>
                <ul>
                    <li>{{ __('Submit accurate information with any supporting evidence.') }}</li>
                    <li>{{ __('Your report will undergo AI verification and moderator review.') }}</li>
                    <li>{{ __('Reports that pass verification will be published anonymously.') }}</li>
                </ul>
            </section>
        </aside>
    </section>
</div>

<script>
(() => {
    const titleInput = document.getElementById('title');
    const contentInput = document.getElementById('content');
    const excerptInput = document.getElementById('excerpt');
    const categoryInput = document.getElementById('category_id');

    const improveButton = document.getElementById('improveWithAiBtn');
    const keepOriginalButton = document.getElementById('keepOriginalBtn');
    const useAllButton = document.getElementById('useAllSuggestionsBtn');

    const statusText = document.getElementById('aiSuggestionStatus');
    const sourceText = document.getElementById('aiSuggestionSource');
    const errorText = document.getElementById('aiSuggestionError');
    const suggestionBody = document.getElementById('aiSuggestionBody');

    const suggestedTitle = document.getElementById('aiSuggestedTitle');
    const suggestedSummary = document.getElementById('aiSuggestedSummary');
    const suggestedContent = document.getElementById('aiSuggestedContent');
    const readabilityScore = document.getElementById('aiReadabilityScore');
    const clarityScore = document.getElementById('aiClarityScore');
    const toneValue = document.getElementById('aiTone');
    const keywordsWrap = document.getElementById('aiKeywords');
    const seoFeedbackList = document.getElementById('aiSeoFeedback');

    const applyButtons = document.querySelectorAll('[data-apply-target]');

    let latestSuggestion = null;

    const toTitleCase = (value) => value
        .replace(/_/g, ' ')
        .toLowerCase()
        .replace(/\b\w/g, (char) => char.toUpperCase());

    const setStatus = (message, type = 'info') => {
        statusText.textContent = message;
        statusText.dataset.state = type;
    };

    const renderSuggestions = (data) => {
        latestSuggestion = data;
        suggestionBody.hidden = false;
        sourceText.textContent = data.source ? @js(__('Source: ')) + toTitleCase(String(data.source)) : '';
        errorText.textContent = data.api_error ? String(data.api_error) : '';

        suggestedTitle.textContent = data.suggested_title || '-';
        suggestedSummary.textContent = data.suggested_summary || '-';
        suggestedContent.textContent = data.suggested_content || '-';

        const readability = Number.isFinite(Number(data.readability_score))
            ? `${data.readability_score}/100`
            : '-';
        const clarity = Number.isFinite(Number(data.clarity_score))
            ? `${data.clarity_score}/100`
            : '-';

        readabilityScore.textContent = readability;
        clarityScore.textContent = clarity;
        toneValue.textContent = data.tone ? toTitleCase(String(data.tone)) : '-';

        keywordsWrap.innerHTML = '';
        (Array.isArray(data.keywords) ? data.keywords : []).forEach((keyword) => {
            const chip = document.createElement('span');
            chip.className = 'smart-chip';
            chip.textContent = String(keyword);
            keywordsWrap.appendChild(chip);
        });

        seoFeedbackList.innerHTML = '';
        (Array.isArray(data.seo_feedback) ? data.seo_feedback : []).forEach((item) => {
            const li = document.createElement('li');
            li.textContent = String(item);
            seoFeedbackList.appendChild(li);
        });
    };

    const applySuggestion = (target) => {
        if (!latestSuggestion) {
            return;
        }

        if (target === 'title' && latestSuggestion.suggested_title) {
            titleInput.value = latestSuggestion.suggested_title;
        }

        if (target === 'excerpt' && latestSuggestion.suggested_summary) {
            excerptInput.value = latestSuggestion.suggested_summary;
        }

        if (target === 'content' && latestSuggestion.suggested_content) {
            contentInput.value = latestSuggestion.suggested_content;
        }

        setStatus(@js(__('Suggestion applied. You can still edit anything before submit.')), 'success');
    };

    improveButton?.addEventListener('click', async () => {
        const title = titleInput?.value?.trim() || '';
        const content = contentInput?.value?.trim() || '';

        if (!title || !content) {
            setStatus(@js(__('Write both title and content first, then click Improve with AI.')), 'error');
            suggestionBody.hidden = true;
            return;
        }

        setStatus(@js(__('Analyzing your draft and generating suggestions...')), 'loading');
        improveButton.disabled = true;

        try {
            const response = await fetch("{{ route('ai.suggest-report') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                },
                body: JSON.stringify({
                    title,
                    content,
                    category_id: categoryInput?.value || null,
                }),
            });

            if (!response.ok) {
                throw new Error(@js(__('Suggestion request failed.')));
            }

            const data = await response.json();
            renderSuggestions(data);
            setStatus(@js(__('Suggestions are ready. Review and choose what to use.')), 'success');
        } catch (error) {
            setStatus(@js(__('Could not generate suggestions right now. Please try again.')), 'error');
            suggestionBody.hidden = true;
        } finally {
            improveButton.disabled = false;
        }
    });

    applyButtons.forEach((button) => {
        button.addEventListener('click', () => {
            applySuggestion(button.dataset.applyTarget);
        });
    });

    useAllButton?.addEventListener('click', () => {
        applySuggestion('title');
        applySuggestion('excerpt');
        applySuggestion('content');
    });

    keepOriginalButton?.addEventListener('click', () => {
        setStatus(@js(__('Original text kept. Suggestions are optional.')), 'info');
    });
})();
</script>
@endsection