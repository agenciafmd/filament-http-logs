<?php

declare(strict_types=1);

return [
    'name' => 'Logs de integração',

    /*
     * Enable or disable log recording.
     */
    'enabled' => env('FILAMENT_HTTP_LOGS_ENABLED', true),

    /*
     * URLs to exclude from logging.
     */
    'deny_hosts' => [
        //        'https://httpbin.org',
    ],

    /*
     * Request fields to hide in logs.
     */
    'hide_fields' => [
        'password',
        'token',
    ],

    /*
     * Number of days to retain logs before cleaning.
     */
    'keep_days' => (int) env('FILAMENT_HTTP_LOGS_KEEP_DAYS', 120),

    /*
     * Content types to exclude from request body logging.
     */
    'request_body_exclude_content_types' => [
        'video/',
        'audio/',
        'application/pdf',
        'application/zip',
        'application/x-zip-compressed',
        'application/octet-stream',
    ],

    /*
     * Content types to exclude from response body logging.
     */
    'response_body_exclude_content_types' => [
        'video/',
        'audio/',
        'application/pdf',
        'application/zip',
        'application/x-zip-compressed',
        'application/octet-stream',
    ],

    /*
     * Show request headers on infolist
     */
    'show_request_headers' => false,

    /*
     * Show response headers on infolist
     */
    'show_response_headers' => false,
];
