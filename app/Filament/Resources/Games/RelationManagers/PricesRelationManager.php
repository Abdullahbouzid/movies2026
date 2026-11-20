<?php

namespace App\Filament\Resources\Games\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    protected static ?string $title = 'الأسعار والمدد';

    protected static ?string $modelLabel = 'مدة وسعر';

    protected static ?string $pluralModelLabel = 'الأسعار والمدد';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('المدة')
                    ->placeholder('مثال: ساعة')
                    ->required()
                    ->maxLength(255),
                TextInput::make('minutes')
                    ->label('عدد الدقائق')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                TextInput::make('price')
                    ->label('السعر (﷼)')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),
                TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_default')
                    ->label('المدة الافتراضية'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('المدة')
                    ->weight('bold'),
                TextColumn::make('minutes')
                    ->label('الدقائق')
                    ->suffix(' د'),
                TextColumn::make('price')
                    ->label('السعر')
                    ->money('LYD'),
                IconColumn::make('is_default')
                    ->label('افتراضية')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('الترتيب'),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة مدة وسعر'),
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
