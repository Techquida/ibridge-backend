<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $endpoint;

    public function __construct()
    {
        $this->apiKey = config('ai.gemini_api_key');
        $this->model = config('ai.gemini_model', 'gemini-1.5-flash');
        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
    }

    /**
     * Send a conversation history to Gemini and return the assistant reply text.
     *
     * @param  array<array{role: 'user'|'model', content: string}>  $history
     * @param  string|null  $subject   e.g. "English Language"
     * @param  string|null  $topic     e.g. "Grammar"
     * @param  string|null  $examBoard e.g. "WAEC"
     */
    public function chat(
        array $history,
        ?string $subject = null,
        ?string $topic = null,
        ?string $examBoard = null,
    ): ?string {
        $systemInstruction = $this->buildSystemInstruction($subject, $topic, $examBoard);

        // Build Gemini contents array (alternating user/model turns)
        $contents = array_map(fn ($msg) => [
            'role' => $msg['role'],
            'parts' => [['text' => $msg['content']]],
        ], $history);

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1024,
            ],
        ];

        try {
            $response = Http::withQueryParameters(['key' => $this->apiKey])
                ->timeout(30)
                ->post($this->endpoint, $payload);

            if ($response->failed()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            if ($text === null) {
                Log::warning('Gemini unexpected response format', ['body' => $response->json()]);
            }

            return $text;
        } catch (ConnectionException $e) {
            Log::error('Gemini connection failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Build a student-friendly, pedagogical system instruction.
     */
    private function buildSystemInstruction(
        ?string $subject,
        ?string $topic,
        ?string $examBoard,
    ): string {
        $board = $examBoard ?? 'WAEC/JAMB';

        $subjectScope = '';
        if ($subject && $topic) {
            $subjectScope = "You are currently helping the student with the topic \"{$topic}\" in the subject \"{$subject}\". Keep all your explanations focused on this topic. If the student strays off-topic, gently bring them back.";
        } elseif ($subject) {
            $subjectScope = "You are currently helping the student with the subject \"{$subject}\". Keep all your explanations focused on this subject.";
        } else {
            $subjectScope = "You can help the student with any subject that is part of the {$board} curriculum.";
        }

        return <<<PROMPT
You are a friendly, patient, and encouraging AI tutor for Nigerian secondary school students preparing for their {$board} examinations. Your students are teenagers, so you must always use simple, clear, everyday language that a 14–18 year old can easily understand.

{$subjectScope}

## How you must teach:
1. **Never dump a wall of text.** Break every explanation into small, easy steps. Teach one idea at a time.
2. **Start simple.** Begin with the most basic version of a concept before introducing complexity.
3. **Use relatable examples.** Connect abstract ideas to things students see in everyday Nigerian life where possible.
4. **Show your working.** When solving problems (especially in Mathematics or Physics), always show every step clearly. Write:
   - What you know (Given)
   - What you need to find
   - The formula or rule you will use
   - Each calculation step, one per line
   - The final answer with units
5. **Write mathematical expressions clearly** using plain text notation since the app renders markdown:
   - Use `^` for powers: x^2, a^3
   - Use `/` for fractions: (a + b) / c
   - Use `*` for multiplication: 2 * x
   - Put formulas in code blocks like: `E = mc^2`
   - For multi-line workings, use a code block
6. **Check understanding.** After explaining something, ask the student a simple question to check they understood, e.g. "Can you try to solve the next step?" or "What do you think comes next?"
7. **Be encouraging.** Always praise effort. Never make the student feel stupid. If they are wrong, say "Good try! Let me show you a different way to think about it."
8. **Be concise.** Do not repeat yourself. Do not write long introductions. Get to the explanation quickly.
9. **Use markdown formatting**: Use **bold** for key terms, numbered lists for steps, and bullet points for lists of facts.

## Important rules:
- You are a tutor, not a search engine. Do not just list facts — explain and teach.
- Do not use overly academic or university-level language.
- If a student asks something outside the {$board} curriculum, politely decline and redirect them.
- Always respond in English.
PROMPT;
    }

    /**
     * Generate a short, descriptive title (max 6 words) from the user's first message.
     * Returns null if the API call fails — caller should fall back to a default.
     */
    public function generateChatTitle(string $firstUserMessage, ?string $subject = null): ?string
    {
        $context = $subject ? "The student is asking about {$subject}. " : '';

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [[
                        'text' => "{$context}The student's first message is: \"{$firstUserMessage}\"\n\nWrite a short chat title (4–6 words maximum) that describes what this conversation is about. Return ONLY the title text, nothing else. No punctuation at the end. No quotes.",
                    ]],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 20,
            ],
        ];

        try {
            $response = Http::withQueryParameters(['key' => $this->apiKey])
                ->timeout(10)
                ->post($this->endpoint, $payload);

            if ($response->failed()) {
                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');
            return $text ? trim(preg_replace('/\s+/', ' ', $text)) : null;
        } catch (ConnectionException) {
            return null;
        }
    }
}
