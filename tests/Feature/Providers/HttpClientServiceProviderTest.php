<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Tests\Feature\Providers;

use Agenciafmd\HttpLogs\Models\HttpLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('logs the request and the response with the sensitive fields masked', function (): void {
    config()->set('filament-http-logs.enabled', true);
    Http::fake([
        'example.test/*' => Http::response(['ok' => true], 201, ['Content-Type' => 'application/json']),
    ]);

    Http::asJson()->post('https://example.test/api?token=abc&q=x', ['password' => 'secret', 'name' => 'Fulano']);

    $httpLog = HttpLog::query()->sole();
    expect($httpLog->url)->toBe('https://example.test/api?token=#######&q=x')
        ->and($httpLog->status)->toEqual(201)
        ->and($httpLog->request_body)->toEqual(['password' => '#######', 'name' => 'Fulano'])
        ->and($httpLog->response_body)->toBe(['ok' => true]);
});

it('does not log the requests to a denied host', function (): void {
    config()->set('filament-http-logs.enabled', true);
    config()->set('filament-http-logs.deny_hosts', ['https://denied.test']);
    Http::fake([
        'denied.test/*' => Http::response('ok'),
    ]);

    Http::get('https://denied.test/api');

    expect(HttpLog::query()->count())->toBe(0);
});

it('keeps an invalid xml body as text', function (): void {
    config()->set('filament-http-logs.enabled', true);
    Http::fake([
        'example.test/*' => Http::response('<invalid', 200, ['Content-Type' => 'application/xml']),
    ]);

    Http::get('https://example.test/feed');

    expect(HttpLog::query()->sole()->response_body)->toBe('<invalid');
});
