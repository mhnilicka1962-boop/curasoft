<?php

namespace App\Services;

use Exception;

class ClaudeService
{
    private string $apiKey;
    private string $apiUrl;
    private string $version;
    private string $model;
    private int    $maxTokens;
    private int    $timeout;

    public function __construct()
    {
        $this->apiKey    = config('claude.api_key');
        $this->apiUrl    = config('claude.api_url');
        $this->version   = config('claude.version');
        $this->model     = config('claude.model');
        $this->maxTokens = config('claude.max_tokens');
        $this->timeout   = config('claude.timeout');

        if (empty($this->apiKey)) {
            throw new Exception('Claude API Key nicht konfiguriert (CLAUDE_API_KEY in .env fehlt)');
        }
    }

    /**
     * Generiert einen professionellen Pflegebericht aus Stichworten.
     */
    public function rapportVorschlag(
        string $stichworte,
        string $klientName,
        string $rapportTyp,
        string $datum
    ): string {
        $typLabels = [
            'pflege'       => 'Pflegerapport',
            'verlauf'      => 'Verlaufsbericht',
            'information'  => 'Information',
            'zwischenfall' => 'Zwischenfall',
            'medikament'   => 'Medikamentendokumentation',
        ];
        $typLabel = $typLabels[$rapportTyp] ?? $rapportTyp;

        $systemPrompt = 'Du bist Pflegefachperson bei einer Schweizer Spitex-Organisation. '
            . 'Schreibe professionelle, sachliche Pflegeberichte auf Deutsch in der dritten Person oder Passivform. '
            . 'Keine Markdown-Formatierung. Nur Fliesstext. 3-5 Sätze. '
            . 'Fachlich korrekt, klar, präzise. Keine Floskeln.';

        $userMessage = "Schreibe einen {$typLabel} vom {$datum} für Klient/in: {$klientName}.\n\n"
            . "Beobachtungen/Stichworte:\n{$stichworte}\n\n"
            . 'Schreibe den vollständigen Bericht:';

        return $this->chat($systemPrompt, $userMessage);
    }

    /**
     * Generischer Chat-Call.
     */
    public function chat(string $systemPrompt, string $userMessage): string
    {
        $body = json_encode([
            'model'      => $this->model,
            'max_tokens' => $this->maxTokens,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json; charset=utf-8',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . $this->version,
            ],
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception('cURL-Fehler: ' . $curlError);
        }

        if ($httpCode !== 200) {
            $err = json_decode($response, true);
            throw new Exception('Claude API Fehler ' . $httpCode . ': ' . ($err['error']['message'] ?? $response));
        }

        $decoded = json_decode($response, true);
        $text    = '';
        foreach ($decoded['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= $block['text'];
            }
        }

        if (empty(trim($text))) {
            throw new Exception('Leere Antwort von Claude');
        }

        return trim($text);
    }
}
