<?php

namespace App\Filament\Resources\PlayTimes\Tables;

use App\Models\PlayTime;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PlayTimesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('game.name')
                    ->label('اللعبة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('day_of_week')
                    ->label('اليوم')
                    ->badge()
                    ->formatStateUsing(fn ($state) => PlayTime::$dayNames[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('من')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('إلى')
                    ->time('H:i'),
                IconColumn::make('is_active')
                    ->label('مُفعّل')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('game_id')
                    ->label('اللعبة')
                    ->relationship('game', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('day_of_week')
                    ->label('اليوم')
                    ->options(PlayTime::$dayNames),
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
            ->defaultSort('day_of_week');
    }
}
