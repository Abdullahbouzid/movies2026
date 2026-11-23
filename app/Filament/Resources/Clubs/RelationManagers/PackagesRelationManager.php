<?php

namespace App\Filament\Resources\Clubs\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PackagesRelationManager extends RelationManager
{
    protected static string $relationship = 'allPackages';

    protected static ?string $title = 'باقات النادي';

    protected static ?string $modelLabel = 'باقة';

    protected static ?string $pluralModelLabel = 'الباقات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الباقة')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('includes')
                    ->label('ما تشمله الباقة')
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('people_count')
                    ->label('عدد الأشخاص')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),
                            TextInput::make('capacity')
                                ->label('السعة (0 = بلا حد)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->helperText('أقصى عدد أشخاص في الفترة الواحدة'),
                TextInput::make('price')
                    ->label(\App\Support\Money::label('السعر'))
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),
                TimePicker::make('start_time')
                    ->label('من الساعة')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('end_time')
                    ->label('إلى الساعة')
                    ->seconds(false)
                    ->required()
                    ->after('start_time'),
                TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_default')
                    ->label('الباقة الافتراضية'),
                Toggle::make('is_active')
                    ->label('مُفعّلة')
                    ->default(true),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الباقة')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('people_count')
                    ->label('الأشخاص')
                    ->badge(),
                TextColumn::make('start_time')
                    ->label('من')
                    ->time('H:i'),
                TextColumn::make('end_time')
                    ->label('إلى')
                    ->time('H:i'),
                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label('افتراضية')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('مُفعّلة')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة باقة'),
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
