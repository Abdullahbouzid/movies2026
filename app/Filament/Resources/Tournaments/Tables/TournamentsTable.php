<?php

namespace App\Filament\Resources\Tournaments\Tables;

use App\Models\Tournament;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TournamentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('البطولة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('game.name')
                    ->label('اللعبة')
                    ->badge()
                    ->sortable(),
                TextColumn::make('format')
                    ->label('الهيكلية')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Tournament::FORMATS[$state] ?? $state),
                TextColumn::make('participants_count')
                    ->label('المشاركون')
                    ->suffix(' لاعب')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Tournament::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'open' => 'info',
                        'drawn' => 'warning',
                        'running' => 'success',
                        'finished' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('prize')
                    ->label('الجائزة')
                    ->money('LYD'),
                TextColumn::make('starts_on')
                    ->label('تبدأ في')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('game_id')
                    ->label('اللعبة')
                    ->relationship('game', 'name'),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(Tournament::STATUSES),
                SelectFilter::make('format')
                    ->label('الهيكلية')
                    ->options(Tournament::FORMATS),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
