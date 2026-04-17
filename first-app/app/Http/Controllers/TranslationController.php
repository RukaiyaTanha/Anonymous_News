<?php

namespace App\Http\Controllers;

use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranslationController extends Controller
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Translate report content to Bangla
     */
    public function translateReport(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        try {
            $translations = $this->translationService->translateContent(
                $request->title,
                $request->content
            );

            return response()->json([
                'success' => true,
                'data' => $translations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Translation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Translate a single text string to Bangla
     */
    public function translateText(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        try {
            $translatedText = $this->translationService->toBangla($request->text);

            return response()->json([
                'success' => true,
                'data' => [
                    'original' => $request->text,
                    'translated' => $translatedText,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Translation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
