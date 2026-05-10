<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Chat Grace Period
    |--------------------------------------------------------------------------
    |
    | Number of days after subscription expiry that a user may still READ
    | their AI chat history (read-only, no new messages). After this period
    | all access is revoked until they re-subscribe.
    |
    */
    'chat_grace_days' => (int) env('AI_CHAT_GRACE_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Gemini API
    |--------------------------------------------------------------------------
    */
    'gemini_api_key' => env('GEMINI_API_KEY', ''),
    'gemini_model'   => env('GEMINI_MODEL', 'gemini-1.5-flash'),
];
