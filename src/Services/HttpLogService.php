<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Services;

use Agenciafmd\HttpLogs\Models\HttpLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class HttpLogService
{
    public static function make(): static
    {
        return app(self::class);
    }

    public function urls(): Collection
    {
        return cache()->flexible('http-logs-services-urls', [now()->addMinutes(5), now()->addMinutes(10)], function () {
            return $this->queryBuilder()
                ->pluck('url')
                ->mapWithKeys(function ($url) {
                    $scheme = parse_url($url, PHP_URL_SCHEME);
                    $host = parse_url($url, PHP_URL_HOST);

                    return [
                        "{$scheme}://{$host}" => "{$scheme}://{$host}",
                    ];
                })
                ->filter()
                ->unique()
                ->sort();
        });
    }

    private function queryBuilder(): Builder
    {
        return HttpLog::query();
    }
}
