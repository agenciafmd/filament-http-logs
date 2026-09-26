<?php

declare(strict_types=1);

namespace Agenciafmd\HttpLogs\Resources\HttpLogs\Tables;

use Agenciafmd\HttpLogs\Models\HttpLog;
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
                    ->formatStateUsing(function (string $state): string {
                        $scheme = parse_url($state, PHP_URL_SCHEME);
                        $host = parse_url($state, PHP_URL_HOST);

                        return "{$scheme}://{$host}/";
                    }),
                TextColumn::make('request_body')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->state(fn (HttpLog $record): string => self::bodyPreview($record->request_body)),
                TextColumn::make('response_body')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->state(fn (HttpLog $record): string => self::bodyPreview($record->response_body)),
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
                    ->dateTime(config()->string('filament-admix.timestamp.format', 'd/m/Y H:i:s'))
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
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(self::filterValue($data, 'value'), fn (Builder $query, string $value): Builder => $query->where('url', 'like', $value . '%'))),
                SelectFilter::make('status')
                    ->translateLabel()
                    ->options([
                        '1' => '1xx',
                        '2' => '2xx',
                        '3' => '3xx',
                        '4' => '4xx',
                        '5' => '5xx',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(self::filterValue($data, 'value'), fn (Builder $query, string $value): Builder => $query->where('status', 'like', $value . '%'))),
                Filter::make('created_at')
                    ->schema([
                        DateTimePicker::make('created_from')
                            ->translateLabel(),
                        DateTimePicker::make('created_until')
                            ->translateLabel(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            self::filterValue($data, 'created_from'),
                            fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            self::filterValue($data, 'created_until'),
                            fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date),
                        )),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query->latest());
    }

    private static function bodyPreview(mixed $body): string
    {
        if (! $body) {
            return '';
        }

        return str(json_encode($body, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '')
            ->limit(50)
            ->toString();
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    private static function filterValue(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
