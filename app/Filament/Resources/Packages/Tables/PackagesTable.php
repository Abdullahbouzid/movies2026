<?php

namespace App\Filament\Resources\Packages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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
                    ->sortable()
                    ->summarize(Sum::make()->label('إجمالي المقاعد')),
                TextColumn::make('includes')
                    ->label('ماذا تشمل')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('movies_count')
                    ->label('عدد الأفلام')
                    ->counts('movies')
                    ->badge()
                    ->summarize(Sum::make()->label('الإجمالي')),
            ])
            ->filters([
                Filter::make('seats')
                    ->label('عدد المقاعد')
                    ->form([
                        TextInput::make('min')->label('من')->numeric(),
                        TextInput::make('max')->label('إلى')->numeric(),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['min'] ?? null, fn ($q, $v) => $q->where('seats_count', '>=', $v))
                        ->when($data['max'] ?? null, fn ($q, $v) => $q->where('seats_count', '<=', $v))),
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
