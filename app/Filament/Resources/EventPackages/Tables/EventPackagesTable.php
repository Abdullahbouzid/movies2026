<?php

namespace App\Filament\Resources\EventPackages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EventPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الباقة')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('people_count')
                    ->label('الأشخاص')
                    ->badge(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->sortable(),
                TextColumn::make('bookings_count')
                    ->label('الحجوزات')
                    ->counts('bookings')
                    ->badge()
                    ->color('success'),
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('مُفعّلة')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('مُفعّلة'),
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
