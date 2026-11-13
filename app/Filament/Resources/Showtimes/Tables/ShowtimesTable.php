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
            ])
            ->defaultGroup(
                Group::make('day_of_week')
                    ->label('اليوم')
                    ->getTitleFromRecordUsing(fn ($record) => Showtime::$dayNames[$record->day_of_week] ?? ''),
            )
            ->filters([
                SelectFilter::make('movie_id')
                    ->label('الفيلم')
                    ->relationship('movie', 'name'),
                SelectFilter::make('day_of_week')
                    ->label('اليوم')
                    ->options(Showtime::$dayNames),
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
            ->defaultSort('start_time');
    }
}
