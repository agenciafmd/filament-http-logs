<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Resources\HttpLogs;

use Agenciafmd\HttpLogs\Models\HttpLog;
use Agenciafmd\HttpLogs\Resources\HttpLogs\Pages\ListHttpLogs;
use Agenciafmd\HttpLogs\Resources\HttpLogs\Tables\HttpLogsTable;
use BackedEnum;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Arr;

final class HttpLogResource extends Resource
{
    protected static ?string $model = HttpLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?int $navigationSort = 1001;

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

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns(6)
                    ->schema([
                        TextEntry::make('method')
                            ->translateLabel()
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'GET' => 'info',
                                'POST' => 'success',
                                'PATCH' => 'gray',
                                'PUT' => 'warning',
                                default => 'danger',
                            }),
                        TextEntry::make('url')
                            ->translateLabel()
                            ->columnSpan(4),
                        KeyValueEntry::make('request_headers')
                            ->translateLabel()
                            ->state(fn ($record) => collect(Arr::dot($record->request_headers))
                                ->map(fn ($value) => is_array($value) ? implode(', ', $value) : $value)
                                ->toArray())
                            ->columnSpanFull()
                            ->hidden(fn ($record): bool => ! config('filament-http-logs.show_request_headers')),
                        KeyValueEntry::make('request_body')
                            ->translateLabel()
                            ->state(fn ($record) => collect(Arr::dot($record->request_body ?? []))
                                ->map(fn ($value) => is_array($value) ? implode(', ', $value) : $value)
                                ->toArray())
                            ->columnSpanFull(),
                        KeyValueEntry::make('response_headers')
                            ->translateLabel()
                            ->state(fn ($record) => collect(Arr::dot($record->response_headers ?? []))
                                ->map(fn ($value) => is_array($value) ? implode(', ', $value) : $value)
                                ->toArray())
                            ->columnSpanFull()
                            ->hidden(fn ($record): bool => ! config('filament-http-logs.show_response_headers')),
                        KeyValueEntry::make('response_body')
                            ->translateLabel()
                            ->state(fn ($record) => collect(Arr::dot($record->response_body ?? []))
                                ->map(fn ($value) => is_array($value) ? implode(', ', $value) : $value)
                                ->toArray())
                            ->columnSpanFull(),
                        TextEntry::make('status')
                            ->translateLabel()
                            ->badge()
                            ->color(fn (string $state): string => match (true) {
                                str_starts_with($state, '1') => 'gray',
                                str_starts_with($state, '2') => 'success',
                                str_starts_with($state, '3') => 'info',
                                str_starts_with($state, '4') => 'warning',
                                default => 'danger',
                            }),
                        TextEntry::make('created_at')
                            ->translateLabel()
                            ->dateTime(config('filament-admix.timestamp.format'))
                            ->columnSpan(4),
                    ])
                    ->columnSpanFull(),
            ]);
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
