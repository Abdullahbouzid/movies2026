<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Models\Branch;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('is_main')
                    ->label('رئيسي')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'المركز الرئيسي' : '—')
                    ->color(fn (bool $state) => $state ? 'warning' : 'gray'),
                TextColumn::make('services')
                    ->label('الخدمات')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Branch::SERVICES[$state] ?? $state)
                    ->color('success')
                    ->separator(','),
                TextColumn::make('location')
                    ->label('العنوان')
                    ->limit(35),
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('مُفعّل')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('مُفعّل'),
                TernaryFilter::make('is_main')->label('المركز الرئيسي'),
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
