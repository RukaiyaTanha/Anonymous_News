<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\Http;

class ReportScoringService
{
    public function scoresForDisplay(Report $report): array
    {
        $hasPersistedScores = $report->ai_confidence_score !== null
            && $report->credibility_score !== null
            && $report->duplicate_similarity_score !== null;

        if (! $hasPersistedScores) {
            return $this->scoreAndPersist($report);
        }

        return [
            'ai_confidence_score' => round((float) $report->ai_confidence_score, 2),
            'credibility_score' => round((float) $report->credibility_score, 2),
            'duplicate_similarity_score' => round((float) $report->duplicate_similarity_score, 2),
            'reporter_reputation_score' => $this->normalizeReputation($this->getUserReputationScore($report)),
            'ai_realism_assessment' => (string) ($report->ai_realism_assessment ?: 'uncertain'),
            'ai_suspicious_indicators' => is_array($report->ai_suspicious_indicators) ? $report->ai_suspicious_indicators : [],
            'ai_entities' => is_array($report->ai_entities) ? $report->ai_entities : [],
            'ai_model' => $report->ai_model,
        ];
    }

    public function scoreAndPersist(Report $report, ?float $duplicateSimilarity = null): array
    {
        $duplicateMeta = $duplicateSimilarity === null
            ? $this->calculateDuplicateSimilarityMeta($report)
            : ['score' => $duplicateSimilarity, 'candidate' => null];

        $duplicateSimilarity = $this->clampPercentage((float) ($duplicateMeta['score'] ?? 0));
        $bestCandidate = $duplicateMeta['candidate'] ?? null;

        if ($bestCandidate instanceof Report) {
            $duplicateSimilarity = $this->refineDuplicateSimilarityWithGemini($report, $bestCandidate, $duplicateSimilarity);
        }

        $analysis = $this->analyzeWithGemini($report, $duplicateSimilarity);

        $aiConfidence = is_numeric($analysis['ai_confidence_score'] ?? null)
            ? $this->clampPercentage((float) $analysis['ai_confidence_score'])
            : $this->estimateAiConfidence($report, $duplicateSimilarity);

        $reporterReputation = $this->normalizeReputation($this->getUserReputationScore($report));
        $credibility = $this->calculateFinalCredibility($aiConfidence, $reporterReputation, $duplicateSimilarity);

        $indicators = $this->sanitizeList($analysis['suspicious_indicators'] ?? []);
        if ($indicators === []) {
            $indicators = $this->fallbackIndicators($report, $duplicateSimilarity, $aiConfidence);
        }
        $entities = $this->sanitizeList($analysis['key_entities'] ?? []);
        $realism = $this->sanitizeRealism($analysis['realism_assessment'] ?? null, $aiConfidence);
        $modelUsed = $analysis !== null ? $this->geminiModel() : 'heuristic-fallback';

        $report->ai_confidence_score = round($aiConfidence, 2);
        $report->duplicate_similarity_score = round($duplicateSimilarity, 2);
        $report->credibility_score = round($credibility, 2);
        $report->ai_realism_assessment = $realism;
        $report->ai_suspicious_indicators = $indicators;
        $report->ai_entities = $entities;
        $report->ai_model = $modelUsed;
        $report->save();

        return [
            'ai_confidence_score' => (float) $report->ai_confidence_score,
            'credibility_score' => (float) $report->credibility_score,
            'duplicate_similarity_score' => (float) $report->duplicate_similarity_score,
            'reporter_reputation_score' => (float) $reporterReputation,
            'ai_realism_assessment' => (string) $report->ai_realism_assessment,
            'ai_suspicious_indicators' => $indicators,
            'ai_entities' => $entities,
            'ai_model' => $modelUsed,
        ];
    }

    public function calculateDuplicateSimilarity(Report $report): float
    {
        $meta = $this->calculateDuplicateSimilarityMeta($report);

        return round((float) $meta['score'], 2);
    }

    private function calculateDuplicateSimilarityMeta(Report $report): array
    {
        $baseTokens = $this->tokenize($this->combinedReportText($report));

        if ($baseTokens === []) {
            return ['score' => 0.0, 'candidate' => null];
        }

        $baseMap = array_fill_keys($baseTokens, true);
        $maxSimilarity = 0.0;
        $bestCandidate = null;

        $candidates = Report::query()
            ->where('id', '!=', $report->id)
            ->whereIn('status', ['pending', 'under_review', 'verified'])
            ->latest()
            ->limit(120)
            ->get(['id', 'title', 'excerpt', 'content']);

        foreach ($candidates as $candidate) {
            $candidateTokens = $this->tokenize($this->combinedReportText($candidate));

            if ($candidateTokens === []) {
                continue;
            }

            $candidateMap = array_fill_keys($candidateTokens, true);
            $intersection = count(array_intersect_key($baseMap, $candidateMap));
            $union = count($baseMap) + count($candidateMap) - $intersection;

            if ($union <= 0) {
                continue;
            }

            $similarity = ($intersection / $union) * 100;

            if ($similarity > $maxSimilarity) {
                $maxSimilarity = $similarity;
                $bestCandidate = $candidate;
            }

            if ($maxSimilarity >= 99.0) {
                break;
            }
        }

        return [
            'score' => round($maxSimilarity, 2),
            'candidate' => $bestCandidate,
        ];
    }

    private function analyzeWithGemini(Report $report, float $duplicateSimilarity): ?array
    {
        $apiKey = $this->geminiApiKey();

        if ($apiKey === '') {
            return null;
        }

        $model = $this->geminiModel();

        $prompt = "You are a newsroom fact-check assistant. Analyze the report and return strict JSON only. "
            . "Required keys: ai_confidence_score (0-100), suspicious_indicators (array of short strings), "
            . "realism_assessment (realistic|uncertain|unlikely), key_entities (array of people/place/event entities). "
            . "Use duplicate_similarity_score as a negative signal.\n\n"
            . "Title: {$report->title}\n"
            . "Excerpt: " . ($report->excerpt ?? 'N/A') . "\n"
            . "Content: {$report->content}\n"
            . "duplicate_similarity_score: {$duplicateSimilarity}";

        try {
            $response = Http::timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

            if (! $response->successful()) {
                return null;
            }

            $responseText = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');

            if ($responseText === '') {
                return null;
            }

            $decoded = $this->decodeJsonObject($responseText);

            if (! is_array($decoded)) {
                return null;
            }

            $ai = $decoded['ai_confidence_score'] ?? null;

            if (! is_numeric($ai)) {
                return null;
            }

            return [
                'ai_confidence_score' => $this->clampPercentage((float) $ai),
                'suspicious_indicators' => $this->sanitizeList($decoded['suspicious_indicators'] ?? []),
                'realism_assessment' => (string) ($decoded['realism_assessment'] ?? ''),
                'key_entities' => $this->sanitizeList($decoded['key_entities'] ?? []),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    private function refineDuplicateSimilarityWithGemini(Report $report, Report $candidate, float $baseSimilarity): float
    {
        $apiKey = $this->geminiApiKey();

        if ($apiKey === '') {
            return round($baseSimilarity, 2);
        }

        $model = $this->geminiModel();

        $prompt = "Compare these two news reports and return strict JSON only with key similarity_percentage (0-100)."
            . "\n\nReport A:\n"
            . $this->combinedReportText($report)
            . "\n\nReport B:\n"
            . $this->combinedReportText($candidate)
            . "\n\nBaseline similarity from lexical algorithm: {$baseSimilarity}";

        try {
            $response = Http::timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

            if (! $response->successful()) {
                return round($baseSimilarity, 2);
            }

            $responseText = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');

            if ($responseText === '') {
                return round($baseSimilarity, 2);
            }

            $decoded = $this->decodeJsonObject($responseText);
            $similarity = $decoded['similarity_percentage'] ?? null;

            if (! is_numeric($similarity)) {
                return round($baseSimilarity, 2);
            }

            return round($this->clampPercentage((float) $similarity), 2);
        } catch (\Throwable) {
            return round($baseSimilarity, 2);
        }
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

    private function estimateAiConfidence(Report $report, float $duplicateSimilarity): float
    {
        $content = trim(strip_tags((string) $report->content));
        $title = trim((string) $report->title);
        $excerpt = trim((string) ($report->excerpt ?? ''));

        $contentLength = mb_strlen($content);
        $titleLength = mb_strlen($title);

        $hasNumbers = preg_match('/\d/', $content) === 1;
        $hasPlaceTerms = preg_match('/\b(city|district|road|street|hospital|school|market|station|village|area)\b/i', $content) === 1;

        $score = 30
            + min(30, $contentLength / 60)
            + min(10, $titleLength / 8)
            + ($excerpt !== '' ? 8 : 0)
            + ($hasNumbers ? 8 : 0)
            + ($hasPlaceTerms ? 8 : 0)
            - ($duplicateSimilarity * 0.35);

        return $score;
    }

    private function calculateFinalCredibility(float $aiConfidence, float $reporterReputation, float $duplicateSimilarity): float
    {
        $score = ($aiConfidence * 0.6)
            + ($reporterReputation * 0.3)
            - ($duplicateSimilarity * 0.1);

        return $this->clampPercentage($score);
    }

    private function normalizeReputation(float|int $rawReputation): float
    {
        return round($this->clampPercentage((float) $rawReputation), 2);
    }

    private function getUserReputationScore(Report $report): float
    {
        if ($report->relationLoaded('user')) {
            return (float) ($report->user?->reputation_score ?? 0);
        }

        return (float) ($report->user()->value('reputation_score') ?? 0);
    }

    private function sanitizeList(mixed $raw): array
    {
        if (is_string($raw)) {
            $raw = array_map('trim', explode(',', $raw));
        }

        if (! is_array($raw)) {
            return [];
        }

        $items = collect($raw)
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->take(8)
            ->values()
            ->all();

        return $items;
    }

    private function sanitizeRealism(mixed $rawRealism, float $aiConfidence): string
    {
        $allowed = ['realistic', 'uncertain', 'unlikely'];
        $value = mb_strtolower(trim((string) $rawRealism));

        if (in_array($value, $allowed, true)) {
            return $value;
        }

        return $this->inferRealismFromScore($aiConfidence);
    }

    private function fallbackIndicators(Report $report, float $duplicateSimilarity, float $aiConfidence): array
    {
        $indicators = [];
        $content = trim(strip_tags((string) $report->content));

        if ($duplicateSimilarity >= 80) {
            $indicators[] = 'High duplicate similarity with existing report';
        }

        if (mb_strlen($content) < 180) {
            $indicators[] = 'Low detail content length';
        }

        if (preg_match('/\d/', $content) !== 1) {
            $indicators[] = 'No numeric evidence found';
        }

        if ($aiConfidence < 40) {
            $indicators[] = 'Low AI confidence';
        }

        return array_slice($indicators, 0, 5);
    }

    private function inferRealismFromScore(float $aiConfidence): string
    {
        if ($aiConfidence >= 70) {
            return 'realistic';
        }

        if ($aiConfidence >= 40) {
            return 'uncertain';
        }

        return 'unlikely';
    }

    private function geminiApiKey(): string
    {
        return trim((string) config('services.gemini.api_key'));
    }

    private function geminiModel(): string
    {
        $model = trim((string) config('services.gemini.model', 'gemini-2.5-flash-lite'));

        return $model !== '' ? $model : 'gemini-2.5-flash-lite';
    }

    private function tokenize(string $text): array
    {
        $normalized = mb_strtolower($text);
        $normalized = preg_replace('/[^\pL\pN\s]+/u', ' ', $normalized) ?? '';

        $parts = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $stopWords = [
            'the', 'and', 'for', 'that', 'with', 'this', 'from', 'have', 'were', 'been', 'will',
            'into', 'about', 'your', 'their', 'there', 'they', 'them', 'then', 'than', 'when',
            'where', 'which', 'what', 'after', 'before', 'under', 'over', 'within', 'without',
            'into', 'onto', 'also', 'just', 'news', 'report', 'reports', 'said', 'says', 'say',
        ];

        $stopWordMap = array_fill_keys($stopWords, true);
        $tokens = [];

        foreach ($parts as $part) {
            if (mb_strlen($part) < 3) {
                continue;
            }

            if (isset($stopWordMap[$part])) {
                continue;
            }

            $tokens[] = $part;
        }

        $tokens = array_values(array_unique($tokens));

        if (count($tokens) > 350) {
            $tokens = array_slice($tokens, 0, 350);
        }

        return $tokens;
    }

    private function combinedReportText(Report $report): string
    {
        return trim(implode(' ', [
            (string) $report->title,
            (string) ($report->excerpt ?? ''),
            strip_tags((string) $report->content),
        ]));
    }

    private function clampPercentage(float $value): float
    {
        return max(0.0, min(100.0, $value));
    }
}
