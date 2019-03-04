<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */

    'supportsCredentials' => false,
    // 'allowedOrigins' => [
    //     env('APP_URL'),
    //     env('CMS_URL'),
    //     env('SUPPORT_URL'),
    // ],
    'allowedOrigins' => ['*'],
    // 'allowedOriginsPatterns' => [],
    'allowedHeaders' => ['*'],
    'allowedMethods' => ['*'],
    'exposedHeaders' => [],
    'maxAge' => 604800,
];
