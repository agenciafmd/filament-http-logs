<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Tests\Feature\Services;

use Agenciafmd\HttpLogs\Models\HttpLog;
use Agenciafmd\HttpLogs\Services\HttpLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('lists each logged origin once and in alphabetical order', function (): void {
    HttpLog::factory()->create(['url' => 'https://b.test/api/users']);
    HttpLog::factory()->create(['url' => 'https://a.test/api?page=2']);
    HttpLog::factory()->create(['url' => 'https://b.test/api/orders']);

    expect(HttpLogService::make()->urls()->all())->toBe([
        'https://a.test' => 'https://a.test',
        'https://b.test' => 'https://b.test',
    ]);
});
