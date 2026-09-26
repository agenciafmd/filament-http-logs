<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Resources\HttpLogs;

use Agenciafmd\HttpLogs\Models\HttpLog;
use Agenciafmd\HttpLogs\Resources\HttpLogs\Infolists\HttpLogInfolist;
use Agenciafmd\HttpLogs\Resources\HttpLogs\Pages\ListHttpLogs;
use Agenciafmd\HttpLogs\Resources\HttpLogs\Tables\HttpLogsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class HttpLogResource extends Resource
{
    protected static ?string $model = HttpLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static bool $isGloballySearchable = false;

    protected static ?string $recordTitleAttribute = 'url';

    public static function getModelLabel(): string
    {
        return __('HttpLog');
    }

    public static function getPluralModelLabel(): string
    {
        return __('HttpLogs');
    }

    public static function getNavigationSort(): ?int
    {
        $navigationSort = config('filament-http-logs.navigation_sort');

        return is_int($navigationSort) ? $navigationSort : null;
    }

    public static function getNavigationGroup(): ?string
    {
        $navigationGroup = config('filament-http-logs.navigation_group');

        return is_string($navigationGroup) ? $navigationGroup : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return HttpLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HttpLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHttpLogs::route('/'),
        ];
    }
}
