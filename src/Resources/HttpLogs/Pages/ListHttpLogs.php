<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Resources\HttpLogs\Pages;

use Agenciafmd\HttpLogs\Resources\HttpLogs\HttpLogResource;
use Filament\Resources\Pages\ListRecords;

final class ListHttpLogs extends ListRecords
{
    protected static string $resource = HttpLogResource::class;
}
