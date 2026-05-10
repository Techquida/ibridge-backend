<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$gemini = app(\App\Services\GeminiService::class);
$reply = $gemini->chat(
    [['role' => 'user', 'content' => 'Explain this question to me:\n\n"Choose the correct sentence:"\n\nThe correct answer is: "Neither John nor his brothers are present."\n\nExplanation given: With "neither … nor", the verb agrees with the subject closest to it: "brothers" (plural) → "are".']],
    'English Language',
    'Grammar',
    'WAEC'
);
echo "Reply:\n";
var_dump($reply);
