<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReportWritingAssistantService
{
    private ?string $lastGeminiError = null;

    public function suggest(string $title, string $content, ?string $categoryName = null): array
    {
        $this->lastGeminiError = null;

        $normalizedTitle = $this->normalizeText($title);
        $normalizedContent = $this->normalizeParagraphs($content);

        $aiSuggestion = $this->suggestWithGemini($normalizedTitle, $normalizedContent, $categoryName, false);

        if (is_array($aiSuggestion)) {
            $aiSuggestion = $this->enforceDistinctSuggestion($aiSuggestion, $normalizedTitle, $normalizedContent, $categoryName);

            if ($this->isStillTooSimilar($aiSuggestion, $normalizedTitle, $normalizedContent)) {
                $retrySuggestion = $this->suggestWithGemini($normalizedTitle, $normalizedContent, $categoryName, true);

                if (is_array($retrySuggestion)) {
                    $aiSuggestion = $this->enforceDistinctSuggestion($retrySuggestion, $normalizedTitle, $normalizedContent, $categoryName);
                }
            }

            return $aiSuggestion;
        }

        return $this->heuristicSuggestion($normalizedTitle, $normalizedContent, $categoryName);
    }

    private function suggestWithGemini(string $title, string $content, ?string $categoryName, bool $aggressiveRewrite = false): ?array
    {
        $apiKey = trim((string) config('services.gemini.api_key'));

        if ($apiKey === '') {
            $this->lastGeminiError = 'Gemini API key is missing from your environment.';

            return null;
        }

        $model = trim((string) config('services.gemini.model', 'gemini-2.5-flash-lite'));
        $model = $model !== '' ? $model : 'gemini-2.5-flash-lite';

        $categoryLabel = $categoryName !== null && trim($categoryName) !== ''
            ? trim($categoryName)
            : 'General';

        $rewriteDirection = $aggressiveRewrite
            ? 'Rewrite aggressively. Change sentence order, structure, and wording while keeping the facts unchanged.'
            : 'Rewrite clearly with a stronger editorial tone and make the result noticeably different from the source.';

        $prompt = "You are an editorial writing assistant for a citizen journalism platform.\n"
            . $rewriteDirection . "\n"
            . "Preserve meaning and facts exactly. Do not invent new facts, names, places, or numbers.\n"
            . "The suggested title must be meaningfully different from the original title, not a near-copy.\n"
            . "The suggested content must be cleaner, more readable, and reorganized into 2-4 short paragraphs.\n"
            . "Keep the same factual claim, but improve flow, specificity, and searchability.\n"
            . "Return strict JSON with keys only: suggested_title, suggested_content, suggested_summary, keywords, readability_score, clarity_score, tone, seo_feedback.\n"
            . "Rules:\n"
            . "- suggested_title: max 90 chars, specific and searchable, avoid reusing more than 40% of the original wording\n"
            . "- suggested_content: improved grammar/readability, factual meaning unchanged, do not echo the original sentence order or opening line\n"
            . "- suggested_summary: 1-2 concise sentences\n"
            . "- keywords: array of 3-6 lowercase keywords\n"
            . "- readability_score: integer 0-100\n"
            . "- clarity_score: integer 0-100\n"
            . "- tone: one of neutral|informative|urgent|cautious\n"
            . "- seo_feedback: array of 2-4 short tips\n\n"
            . "Category: {$categoryLabel}\n"
            . "Original title: {$title}\n"
            . "Original content: {$content}";

        try {
            $response = $this->geminiClient()
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.35,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

            if (! $response->successful()) {
                $this->lastGeminiError = $this->friendlyGeminiErrorMessage($response->status(), (string) $response->body());

                return null;
            }

            $rawText = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');

            if ($rawText === '') {
                $this->lastGeminiError = 'Gemini returned an empty response.';

                return null;
            }

            $decoded = $this->decodeJsonObject($rawText);

            if (! is_array($decoded)) {
                $this->lastGeminiError = 'Gemini response could not be parsed as JSON.';

                return null;
            }

            return $this->sanitizeSuggestion($decoded, $title, $content);
        } catch (\Throwable) {
            $this->lastGeminiError = 'Gemini request failed before a response was returned.';

            return null;
        }
    }

    private function heuristicSuggestion(string $title, string $content, ?string $categoryName): array
    {
        $improvedTitle = $this->optimizeTitle($title, $content, $categoryName);
        $improvedContent = $this->softImproveContent($content);
        $summary = $this->buildSummary($improvedContent);
        $keywords = $this->extractKeywords($improvedTitle . ' ' . $improvedContent);

        [$readabilityScore, $clarityScore] = $this->calculateScores($improvedContent);
        $tone = $this->detectTone($improvedContent);

        return [
            'suggested_title' => $improvedTitle,
            'suggested_content' => $improvedContent,
            'suggested_summary' => $summary,
            'keywords' => $keywords,
            'readability_score' => $readabilityScore,
            'clarity_score' => $clarityScore,
            'tone' => $tone,
            'seo_feedback' => $this->seoFeedback($improvedTitle, $improvedContent, $keywords),
            'source' => 'heuristic-fallback',
            'api_error' => $this->lastGeminiError,
        ];
    }

    private function enforceDistinctSuggestion(array $suggestion, string $originalTitle, string $originalContent, ?string $categoryName): array
    {
        $suggestedTitle = $this->normalizeText((string) ($suggestion['suggested_title'] ?? ''));
        $suggestedContent = $this->normalizeParagraphs((string) ($suggestion['suggested_content'] ?? ''));

        if ($suggestedTitle === '' || $this->similarityScore($suggestedTitle, $originalTitle) >= 70) {
            $suggestedTitle = $this->optimizeTitle($originalTitle, $originalContent, $categoryName);
        }

        if ($suggestedContent === '' || $this->similarityScore($this->stripWhitespace($suggestedContent), $this->stripWhitespace($originalContent)) >= 78) {
            $suggestedContent = $this->softImproveContent($originalContent);
        }

        $suggestion['suggested_title'] = $suggestedTitle;
        $suggestion['suggested_content'] = $suggestedContent;

        if (empty($suggestion['suggested_summary'])) {
            $suggestion['suggested_summary'] = $this->buildSummary($suggestedContent);
        }

        if (empty($suggestion['keywords']) || ! is_array($suggestion['keywords'])) {
            $suggestion['keywords'] = $this->extractKeywords($suggestedTitle . ' ' . $suggestedContent);
        }

        $suggestion['source'] = $suggestion['source'] ?? 'gemini';
        $suggestion['api_error'] = null;

        return $suggestion;
    }

    private function isStillTooSimilar(array $suggestion, string $originalTitle, string $originalContent): bool
    {
        $suggestedTitle = $this->normalizeText((string) ($suggestion['suggested_title'] ?? ''));
        $suggestedContent = $this->normalizeParagraphs((string) ($suggestion['suggested_content'] ?? ''));

        $titleSimilarity = $suggestedTitle !== ''
            ? $this->similarityScore($suggestedTitle, $originalTitle)
            : 100;

        $contentSimilarity = $suggestedContent !== ''
            ? $this->similarityScore($this->stripWhitespace($suggestedContent), $this->stripWhitespace($originalContent))
            : 100;

        return $titleSimilarity >= 58 || $contentSimilarity >= 68;
    }

    private function sanitizeSuggestion(array $decoded, string $fallbackTitle, string $fallbackContent): array
    {
        $title = $this->normalizeText((string) ($decoded['suggested_title'] ?? $fallbackTitle));
        $content = $this->normalizeParagraphs((string) ($decoded['suggested_content'] ?? $fallbackContent));
        $summary = $this->normalizeText((string) ($decoded['suggested_summary'] ?? $this->buildSummary($content)));

        $keywords = $decoded['keywords'] ?? [];
        if (is_string($keywords)) {
            $keywords = array_map('trim', explode(',', $keywords));
        }
        if (! is_array($keywords)) {
            $keywords = [];
        }

        $keywords = collect($keywords)
            ->map(fn ($keyword) => Str::lower(trim((string) $keyword)))
            ->filter(fn ($keyword) => $keyword !== '')
            ->unique()
            ->take(6)
            ->values()
            ->all();

        if ($keywords === []) {
            $keywords = $this->extractKeywords($title . ' ' . $content);
        }

        $readability = (int) ($decoded['readability_score'] ?? 0);
        $clarity = (int) ($decoded['clarity_score'] ?? 0);
        $tone = trim((string) ($decoded['tone'] ?? 'neutral'));

        $seoFeedback = $decoded['seo_feedback'] ?? [];
        if (is_string($seoFeedback)) {
            $seoFeedback = array_map('trim', explode("\n", $seoFeedback));
        }

        if (! is_array($seoFeedback)) {
            $seoFeedback = [];
        }

        $seoFeedback = collect($seoFeedback)
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->take(4)
            ->values()
            ->all();

        if ($seoFeedback === []) {
            $seoFeedback = $this->seoFeedback($title, $content, $keywords);
        }

        return [
            'suggested_title' => $title,
            'suggested_content' => $content,
            'suggested_summary' => $summary,
            'keywords' => $keywords,
            'readability_score' => $this->boundScore($readability),
            'clarity_score' => $this->boundScore($clarity),
            'tone' => $this->sanitizeTone($tone),
            'seo_feedback' => $seoFeedback,
            'source' => 'gemini',
            'api_error' => null,
        ];
    }

    private function optimizeTitle(string $title, string $content, ?string $categoryName): string
    {
        $title = $this->normalizeText($title);
        $content = $this->normalizeParagraphs($content);

        if ($title === '') {
            $title = 'Breaking Incident Report';
        }

        $title = preg_replace('/\b(big|very|really|some|something)\b/i', '', $title) ?? $title;
        $title = preg_replace('/\s+/', ' ', trim($title)) ?? $title;

        $location = $this->extractLocationHint($content);

        if ($location !== null && ! Str::contains(Str::lower($title), Str::lower($location))) {
            $title .= ' in ' . $location;
        }

        if ($categoryName !== null && trim($categoryName) !== '' && ! Str::contains(Str::lower($title), Str::lower($categoryName))) {
            if (mb_strlen($title) < 70) {
                $title .= ' | ' . trim($categoryName);
            }
        }

        if ($this->similarityScore($title, $this->normalizeText($title)) >= 70) {
            $title = $this->buildDistinctTitle($title, $content);
        }

        return Str::title(Str::limit($title, 90, ''));
    }

    private function buildDistinctTitle(string $originalTitle, string $content): string
    {
        $subject = $this->extractSubject($originalTitle) ?: 'Bangladesh';

        $topic = 'digital infrastructure';
        if (preg_match('/\bdigital infrastructure\b/i', $content) !== 1) {
            $keywords = $this->extractKeywords($content);
            if ($keywords !== []) {
                $topic = implode(' ', array_slice($keywords, 0, 2));
            }
        }

        $outcome = 'smart tech growth';
        if (preg_match('/\bsmart tech|smart technology|technology growth\b/i', $content) !== 1) {
            $outcome = 'digital growth';
        }

        $headline = trim("{$subject} boosts {$topic} to drive {$outcome}");

        if ($headline === '' || $headline === $originalTitle) {
            $headline = trim("{$subject} advances infrastructure and technology investment");
        }

        return $headline;
    }

    private function extractSubject(string $title): ?string
    {
        $words = preg_split('/\s+/u', trim($title), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return null;
        }

        return $words[0];
    }

    private function softImproveContent(string $content): string
    {
        $content = $this->normalizeParagraphs($content);

        $sentences = preg_split('/(?<=[.!?])\s+/u', $content, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $deduped = [];
        foreach ($sentences as $sentence) {
            $clean = trim($sentence);

            if ($clean === '') {
                continue;
            }

            if ($deduped !== [] && mb_strtolower(end($deduped)) === mb_strtolower($clean)) {
                continue;
            }

            $deduped[] = ucfirst($clean);
        }

        $rebuilt = implode(' ', $deduped);
        $rebuilt = preg_replace('/\s+,/', ',', $rebuilt) ?? $rebuilt;
        $rebuilt = preg_replace('/\s+\./', '.', $rebuilt) ?? $rebuilt;

        return trim($rebuilt);
    }

    private function buildSummary(string $content): string
    {
        $sentences = preg_split('/(?<=[.!?])\s+/u', $content, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $summary = trim(implode(' ', array_slice($sentences, 0, 2)));

        if ($summary === '') {
            return Str::limit($content, 180, '...');
        }

        return Str::limit($summary, 220, '...');
    }

    private function calculateScores(string $content): array
    {
        $length = mb_strlen($content);
        $sentences = preg_split('/(?<=[.!?])\s+/u', $content, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $sentenceCount = max(count($sentences), 1);
        $avgSentenceLength = $length / $sentenceCount;

        $readability = 78 - (($avgSentenceLength - 16) * 1.4);
        $clarity = 55 + min(30, $length / 80);

        if ($avgSentenceLength > 30) {
            $clarity -= 10;
        }

        return [$this->boundScore((int) round($readability)), $this->boundScore((int) round($clarity))];
    }

    private function detectTone(string $content): string
    {
        $lower = Str::lower($content);

        if (preg_match('/\b(urgent|immediately|danger|critical|emergency)\b/', $lower) === 1) {
            return 'urgent';
        }

        if (preg_match('/\b(alleged|unconfirmed|reportedly|possibly|might)\b/', $lower) === 1) {
            return 'cautious';
        }

        if (preg_match('/\b(according to|witness|stated|official|source)\b/', $lower) === 1) {
            return 'informative';
        }

        return 'neutral';
    }

    private function extractKeywords(string $text): array
    {
        $stopWords = [
            'the', 'and', 'for', 'that', 'with', 'this', 'from', 'were', 'have', 'has', 'had',
            'into', 'about', 'your', 'their', 'they', 'them', 'been', 'will', 'would', 'could',
            'after', 'before', 'there', 'where', 'what', 'when', 'which', 'while', 'report',
            'news', 'incident', 'just', 'very', 'really', 'some', 'said', 'says', 'is', 'are', 'was',
        ];

        $clean = mb_strtolower($text);
        $clean = preg_replace('/[^\pL\pN\s]+/u', ' ', $clean) ?? '';
        $parts = preg_split('/\s+/u', $clean, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $counts = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if (mb_strlen($part) < 4 || in_array($part, $stopWords, true)) {
                continue;
            }

            $counts[$part] = ($counts[$part] ?? 0) + 1;
        }

        arsort($counts);

        return array_slice(array_keys($counts), 0, 6);
    }

    private function seoFeedback(string $title, string $content, array $keywords): array
    {
        $feedback = [];

        if (mb_strlen($title) < 35) {
            $feedback[] = 'Title can be slightly longer with one specific keyword.';
        }

        if (mb_strlen($title) > 90) {
            $feedback[] = 'Title is a bit long; keep it under 90 characters.';
        }

        if (count($keywords) < 3) {
            $feedback[] = 'Add clearer terms like location, event type, and impact.';
        }

        if (preg_match('/\n\s*\n/', $content) !== 1) {
            $feedback[] = 'Break content into short paragraphs for better readability.';
        }

        $feedback[] = 'Keep the first sentence factual and specific to improve search relevance.';

        return array_slice(array_values(array_unique($feedback)), 0, 4);
    }

    private function sanitizeTone(string $tone): string
    {
        $tone = Str::lower(trim($tone));
        $allowed = ['neutral', 'informative', 'urgent', 'cautious'];

        return in_array($tone, $allowed, true) ? $tone : 'neutral';
    }

    private function normalizeText(string $text): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);

        return $text;
    }

    private function normalizeParagraphs(string $text): string
    {
        $text = strip_tags($text);
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[\t ]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", trim($text)) ?? trim($text);

        return $text;
    }

    private function extractLocationHint(string $content): ?string
    {
        if (preg_match('/\b(in|at|near)\s+([A-Z][a-zA-Z]+(?:\s+[A-Z][a-zA-Z]+){0,2})\b/', $content, $matches) === 1) {
            return trim((string) ($matches[2] ?? '')) ?: null;
        }

        return null;
    }

    private function boundScore(int $score): int
    {
        return max(0, min(100, $score));
    }

    private function similarityScore(string $left, string $right): int
    {
        similar_text(mb_strtolower($left), mb_strtolower($right), $percent);

        return (int) round($percent);
    }

    private function stripWhitespace(string $value): string
    {
        return preg_replace('/\s+/u', '', $value) ?? $value;
    }

    private function friendlyGeminiErrorMessage(int $status, string $body): string
    {
        if ($status === 403 && str_contains($body, 'reported as leaked')) {
            return 'Gemini rejected the current API key because it has been reported as leaked. Replace GEMINI_API_KEY with a new key.';
        }

        if ($status === 401) {
            return 'Gemini rejected the API key. Check GEMINI_API_KEY in your .env file.';
        }

        if ($status === 429) {
            return 'Gemini rate limit reached. Try again later.';
        }

        if ($status >= 500) {
            return 'Gemini had a server-side error. Try again later.';
        }

        return 'Gemini request failed. The service is using the fallback suggestion engine.';
    }

    private function decodeJsonObject(string $raw): ?array
    {
        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $firstBrace = strpos($raw, '{');
        $lastBrace = strrpos($raw, '}');

        if ($firstBrace === false || $lastBrace === false || $lastBrace <= $firstBrace) {
            return null;
        }

        $candidateJson = substr($raw, $firstBrace, $lastBrace - $firstBrace + 1);
        $decoded = json_decode($candidateJson, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function geminiClient()
    {
        $client = Http::timeout(30);

        if (! (bool) config('services.gemini.verify_ssl', true)) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }
}
