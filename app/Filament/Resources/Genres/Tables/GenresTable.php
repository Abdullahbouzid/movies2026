<?php

namespace App\Filament\Resources\Genres\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GenresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الفئة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('movies_count')
                    ->label('عدد الأفلام')
                    ->counts('movies')
                    ->badge()
                    ->summarize(Sum::make()->label('إجمالي الأفلام')),
                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('has_movies')
                    ->label('تحتوي أفلام')
                    ->placeholder('الكل')
                    ->trueLabel('بها أفلام')
                    ->falseLabel('فارغة')
                    ->queries(
                        true: fn ($query) => $query->has('movies'),
                        false: fn ($query) => $query->doesntHave('movies'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
