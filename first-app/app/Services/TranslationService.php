<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class TranslationService
{
    private $geminiApiKey;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    /**
     * Translate text to Bangla using Gemini API
     */
    public function toBangla(string $text): string
    {
        if (!$this->geminiApiKey) {
            throw new Exception('Gemini API key not configured');
        }

        try {
            $response = Http::post(
                'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent',
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => "Translate the following text to Bangla. Return ONLY the translated text, nothing else:\n\n{$text}"
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'key' => $this->geminiApiKey
                ]
            );

            if ($response->failed()) {
                throw new Exception('Translation failed: ' . $response->body());
            }

            $result = $response->json();
            
            return $result['candidates'][0]['content']['parts'][0]['text'] ?? $text;
        } catch (Exception $e) {
            \Log::error('Translation error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Translate title and content together
     */
    public function translateContent(string $title, string $content): array
    {
        $titleBn = $this->toBangla($title);
        $contentBn = $this->toBangla($content);

        return [
            'title_bn' => $titleBn,
            'content_bn' => $contentBn,
        ];
    }
}
