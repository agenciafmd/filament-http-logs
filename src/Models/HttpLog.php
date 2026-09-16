<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Models;

use Agenciafmd\HttpLogs\Database\Factories\HttpLogFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Override;

#[UseFactory(HttpLogFactory::class)]
final class HttpLog extends Model
{
    use HasFactory;
    use Prunable;

    public function prunable(): Builder
    {
        return self::query()
            ->where('created_at', '<=', now()->subDays(config('filament-http-logs.keep_days')));
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'request_headers' => 'json',
            'request_body' => 'json',
            'response_headers' => 'json',
            'response_body' => 'json',
        ];
    }
}
