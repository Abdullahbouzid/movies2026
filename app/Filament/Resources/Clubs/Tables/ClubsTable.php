<?php

namespace App\Filament\Resources\Clubs\Tables;

use App\Models\Club;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ClubsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('النادي')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('location')
                    ->label('الموقع')
                    ->limit(40),
                TextColumn::make('packages_count')
                    ->label('الباقات')
                    ->counts('packages')
                    ->badge(),
                TextColumn::make('bookings_count')
                    ->label('الحجوزات')
                    ->counts('bookings')
                    ->badge()
                    ->color('success'),
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('مُفعّل')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('مُفعّل'),
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
            ->defaultSort('sort_order');
    }
}
