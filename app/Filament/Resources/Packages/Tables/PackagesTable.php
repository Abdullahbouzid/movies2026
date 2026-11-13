<?php

namespace App\Filament\Resources\Packages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الباقة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('seats_count')
                    ->label('عدد المقاعد')
                    ->badge()
                    ->sortable(),
                TextColumn::make('includes')
                    ->label('ماذا تشمل')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('movies_count')
                    ->label('عدد الأفلام')
                    ->counts('movies')
                    ->badge(),
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
