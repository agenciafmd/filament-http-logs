<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Resources\HttpLogs\Tables;

use Agenciafmd\HttpLogs\Services\HttpLogService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class HttpLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('method')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'GET' => 'info',
                        'POST' => 'success',
                        'PATCH' => 'gray',
                        'PUT' => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('url')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        $scheme = parse_url($state, PHP_URL_SCHEME);
                        $host = parse_url($state, PHP_URL_HOST);

                        return "{$scheme}://{$host}/";
                    }),
                TextColumn::make('request_body')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->state(function ($record) {
                        return str(collect($record->request_body)
                            ->map(function ($value, $key) {
                                return "{$key}: {$value}";
                            })
                            ->values()
                            ->implode(' | '))->limit(50);
                    }),
                TextColumn::make('response_body')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->state(function ($record) {
                        return str(collect($record->response_body)
                            ->map(function ($value, $key) {
                                return "{$key}: {$value}";
                            })
                            ->values()
                            ->implode(' | '))->limit(50);
                    }),
                TextColumn::make('status')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, '1') => 'gray',
                        str_starts_with($state, '2') => 'success',
                        str_starts_with($state, '3') => 'info',
                        str_starts_with($state, '4') => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->translateLabel()
                    ->dateTime(config('filament-admix.timestamp.format'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('method')
                    ->translateLabel()
                    ->options([
                        'GET' => 'GET',
                        'POST' => 'POST',
                        'PATCH' => 'PATCH',
                        'PUT' => 'PUT',
                        'DELETE' => 'DELETE',
                    ]),
                SelectFilter::make('url')
                    ->translateLabel()
                    ->options(fn (): array => HttpLogService::make()
                        ->urls()
                        ->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], fn (Builder $query, $value): Builder => $query->where('url', 'like', $value . '%'));
                    }),
                SelectFilter::make('status')
                    ->translateLabel()
                    ->options([
                        '1' => '1xx',
                        '2' => '2xx',
                        '3' => '3xx',
                        '4' => '4xx',
                        '5' => '5xx',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], fn (Builder $query, $value): Builder => $query->where('status', 'like', $value . '%'));
                    }),
                Filter::make('created_at')
                    ->schema([
                        DateTimePicker::make('created_from')
                            ->translateLabel(),
                        DateTimePicker::make('created_until')
                            ->translateLabel(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(function (Builder $query): Builder {
                return $query->orderBy('created_at', 'desc');
            });
    }
}
