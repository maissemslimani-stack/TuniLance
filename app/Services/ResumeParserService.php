<?php

namespace App\Services;

use App\Models\Skill;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser as PdfParser;

class ResumeParserService
{
    public function parse(string $pdfPath): array
    {
        $text = $this->extractText($pdfPath);

        return [
            'email' => $this->extractEmail($text),
            'phone' => $this->extractPhone($text),
            'skill_ids' => $this->matchSkills($text),
            'bio' => $this->summarize($text),
        ];
    }

    private function extractText(string $path): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);

        return $pdf->getText();
    }

    /** Reliable — plain regex, no AI needed for something this structured. */
    private function extractEmail(string $text): ?string
    {
        preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches);

        return $matches[0] ?? null;
    }

    private function extractPhone(string $text): ?string
    {
        preg_match('/(\+?\d{1,3}[\s.-]?)?\(?\d{2,4}\)?[\s.-]?\d{3}[\s.-]?\d{3,4}/', $text, $matches);

        return $matches[0] ?? null;
    }

    /**
     * Matches the CV text against your actual predefined Skill list —
     * deliberately not using a generic AI model here, since matching
     * against a known, controlled vocabulary is more reliable than
     * asking a model to freely extract skill names.
     */
    private function matchSkills(string $text): array
    {
        $lowerText = strtolower($text);

        return Skill::all()
            ->filter(fn ($skill) => str_contains($lowerText, strtolower($skill->name)))
            ->pluck('id')
            ->toArray();
    }

    /**
     * The one genuine AI step — summarizes the full CV text into a short
     * professional bio using Hugging Face's hosted Inference API.
     * Fails gracefully (returns null) if the API is unreachable, rate-limited,
     * or the model is still "warming up" — never blocks registration.
     */
    private function summarize(string $text): ?string
    {
        $apiKey = config('services.huggingface.key');
        $model = config('services.huggingface.summary_model');

        if (! $apiKey || trim($text) === '') {
            return null;
        }

        // HF's free inference models have input limits — truncate to a safe length.
        $truncated = mb_substr(trim($text), 0, 3000);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->post("https://api-inference.huggingface.co/models/{$model}", [
                    'inputs' => $truncated,
                    'parameters' => ['max_length' => 120, 'min_length' => 30],
                ]);

            if ($response->successful()) {
                return $response->json('0.summary_text');
            }
        } catch (\Throwable $e) {
            // Silently fall back — a failed AI call should never break signup.
        }

        return null;
    }
}