<?php

namespace App\Filament\Resources\Movies\Tables;

use App\Models\Movie;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MoviesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->height(60)
                    ->width(45),
                TextColumn::make('name')
                    ->label('اسم الفيلم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('genre.name')
                    ->label('الفئة')
                    ->badge()
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('المدة')
                    ->suffix(' دقيقة')
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('التقييم')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('age_rating')
                    ->label('التصنيف العمري')
                    ->badge(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === Movie::STATUS_AVAILABLE ? 'متوفر' : 'قريبًا')
                    ->color(fn ($state) => $state === Movie::STATUS_AVAILABLE ? 'success' : 'gray'),
                TextColumn::make('packages_count')
                    ->label('الباقات')
                    ->counts('packages')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('genre_id')
                    ->label('الفئة')
                    ->relationship('genre', 'name'),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        Movie::STATUS_AVAILABLE => 'متوفر',
                        Movie::STATUS_COMING_SOON => 'قريبًا',
                    ]),
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
