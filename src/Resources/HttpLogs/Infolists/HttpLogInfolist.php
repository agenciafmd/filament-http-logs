<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Resources\HttpLogs\Infolists;

use Agenciafmd\Admix\Resources\Infolists\Components\DateTimeEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;

final class HttpLogInfolist
{
    public static function configure(Schema $schema): Schema
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
                        self::keyValueEntry('request_headers')
                            ->hidden(fn ($record): bool => ! config('filament-http-logs.show_request_headers')),
                        self::keyValueEntry('request_body')
                            ->hidden(fn ($record): bool => ! $record->request_body),
                        self::keyValueEntry('response_headers')
                            ->hidden(fn ($record): bool => ! config('filament-http-logs.show_response_headers')),
                        self::keyValueEntry('response_body')
                            ->hidden(fn ($record): bool => ! $record->response_body),
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
                        DateTimeEntry::make('created_at')
                            ->columnSpan(4),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function keyValueEntry(string $name): KeyValueEntry
    {
        return KeyValueEntry::make($name)
            ->translateLabel()
            ->state(fn ($record) => collect(Arr::dot(Arr::wrap($record->{$name} ?? [])))
                ->map(fn ($value) => is_array($value) ? implode(', ', $value) : $value)
                ->toArray())
            ->columnSpanFull();
    }
}
