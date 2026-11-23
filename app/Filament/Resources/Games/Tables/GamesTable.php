<?php

namespace App\Filament\Resources\Games\Tables;

use App\Models\Game;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GamesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->height(56)
                    ->width(56),
                TextColumn::make('name')
                    ->label('اسم اللعبة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Game $r) => $r->meta),
                TextColumn::make('prices_count')
                    ->label('المدد')
                    ->counts('prices')
                    ->badge(),
                TextColumn::make('min_price')
                    ->label('يبدأ من')
                    ->state(fn (Game $r) => $r->minPrice())
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->placeholder('—'),
                TextColumn::make('play_times_count')
                    ->label('أوقات اللعب')
                    ->counts('playTimes')
                    ->badge()
                    ->color('info'),
                TextColumn::make('rating')
                    ->label('التقييم')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('badge')
                    ->label('الشارة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Game::BADGES[$state] ?? $state)
                    ->placeholder('—'),
                IconColumn::make('is_active')
                    ->label('مُفعّلة')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('مُفعّلة')
                    ->falseLabel('معطّلة'),
                SelectFilter::make('badge')
                    ->label('الشارة')
                    ->options(Game::BADGES),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
