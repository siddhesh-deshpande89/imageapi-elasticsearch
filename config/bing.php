<?php
declare(strict_types=1);

return [
    'endpoint_url' => 'https://api.bing.microsoft.com/v7.0/images/search',
    'subscription_key' => env('BING_SUBSCRIPTION_KEY'),
    'max_words' => env('BING_IMAGE_PER_WORD')
];