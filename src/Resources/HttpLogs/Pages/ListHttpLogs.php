<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Resources\HttpLogs\Pages;

use Agenciafmd\HttpLogs\Resources\HttpLogs\HttpLogResource;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListHttpLogs extends ListRecords
{
    #[Override]
    protected static string $resource = HttpLogResource::class;
}
