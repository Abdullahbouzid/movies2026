<?php

namespace App\Filament\Resources\Tournaments\RelationManagers;

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

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    protected static ?string $title = 'المشاركون';

    protected static ?string $modelLabel = 'مشارك';

    protected static ?string $pluralModelLabel = 'المشاركون';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم المشارك')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->maxLength(30),
                Toggle::make('is_bye')
                    ->label('مقعد فارغ (Bye)')
                    ->helperText('يُستخدم لملء الهيكلية عند عدم اكتمال العدد.'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('seed')
                    ->label('#')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('name')
                    ->label('المشارك')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->placeholder('—'),
                IconColumn::make('is_bye')
                    ->label('Bye')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة مشارك'),
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
            ->defaultSort('seed');
    }
}
