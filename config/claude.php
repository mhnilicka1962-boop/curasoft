<?php

return [
    'api_key'    => env('CLAUDE_API_KEY', ''),
    'api_url'    => 'https://api.anthropic.com/v1/messages',
    'version'    => '2023-06-01',
    'model'      => 'claude-haiku-4-5-20251001',
    'max_tokens' => 1024,
    'timeout'    => 30,
];
