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
        return resolve(self::class);
    }

    /**
     * Origens (`scheme://host`) já registradas, para o filtro por URL.
     *
     * @return Collection<string, non-falsy-string>
     */
    public function urls(): Collection
    {
        return cache()->flexible('http-logs-services-urls', [now()->addMinutes(5), now()->addMinutes(10)], fn (): Collection => $this->queryBuilder()
            ->pluck('url')
            ->filter(static fn (mixed $url): bool => is_string($url))
            ->mapWithKeys(static function (string $url): array {
                $scheme = parse_url($url, PHP_URL_SCHEME);
                $host = parse_url($url, PHP_URL_HOST);

                return [
                    "{$scheme}://{$host}" => "{$scheme}://{$host}",
                ];
            })
            ->filter()
            ->unique()
            ->sort());
    }

    /**
     * @return Builder<HttpLog>
     */
    private function queryBuilder(): Builder
    {
        return HttpLog::query();
    }
}
