<?php

namespace App\Filament\Resources\Showtimes\Tables;

use App\Models\Showtime;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ShowtimesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('movie.name')
                    ->label('الفيلم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('show_date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('day_of_week')
                    ->label('اليوم')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Showtime::$dayNames[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('البداية')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('النهاية')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('seats_capacity')
                    ->label('السعة')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
            ])
            ->defaultGroup(
                Group::make('show_date')
                    ->label('التاريخ')
                    ->getTitleFromRecordUsing(fn ($record) => $record->show_date
                        ? $record->show_date->format('l d/m/Y')
                        : 'بدون تاريخ')
                    ->orderQueryUsing(fn ($query, $direction) => $query->orderBy('show_date', $direction)),
            )
            ->filters([
                SelectFilter::make('movie_id')
                    ->label('الفيلم')
                    ->relationship('movie', 'name'),
                SelectFilter::make('day_of_week')
                    ->label('اليوم')
                    ->options(Showtime::$dayNames),
                \Filament\Tables\Filters\Filter::make('show_date')
                    ->label('التاريخ')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('من'),
                        \Filament\Forms\Components\DatePicker::make('to')->label('إلى'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('show_date', '>=', $d))
                            ->when($data['to'] ?? null, fn ($q, $d) => $q->whereDate('show_date', '<=', $d));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('show_date', 'asc');
    }
}
