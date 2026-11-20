<?php

namespace App\Filament\Resources\TournamentAnnouncements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TournamentAnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->height(48)
                    ->width(64),
                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('badge')
                    ->label('الشارة')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('tournament.name')
                    ->label('البطولة')
                    ->placeholder('—'),
                TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('حالة النشر'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
    }
}
