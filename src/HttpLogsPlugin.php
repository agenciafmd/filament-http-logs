<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs;

use Agenciafmd\HttpLogs\Resources\HttpLogs\HttpLogResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

final class HttpLogsPlugin implements Plugin
{
    public static function make(): static
    {
        return app(self::class);
    }

    public function getId(): string
    {
        return 'http-logs';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                HttpLogResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
