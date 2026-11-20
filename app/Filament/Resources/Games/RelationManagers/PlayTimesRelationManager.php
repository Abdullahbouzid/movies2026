<?php

namespace App\Filament\Resources\Games\RelationManagers;

use App\Models\PlayTime;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PlayTimesRelationManager extends RelationManager
{
    protected static string $relationship = 'playTimes';

    protected static ?string $title = 'أوقات اللعب';

    protected static ?string $modelLabel = 'وقت لعب';

    protected static ?string $pluralModelLabel = 'أوقات اللعب';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('day_of_week')
                    ->label('اليوم')
                    ->options(PlayTime::$dayNames)
                    ->required(),
                TimePicker::make('start_time')
                    ->label('وقت البداية')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('end_time')
                    ->label('وقت النهاية')
                    ->seconds(false)
                    ->required()
                    ->after('start_time'),
                Toggle::make('is_active')
                    ->label('مُفعّل')
                    ->default(true),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')
                    ->label('اليوم')
                    ->badge()
                    ->formatStateUsing(fn ($state) => PlayTime::$dayNames[$state] ?? $state),
                TextColumn::make('start_time')
                    ->label('من')
                    ->time('H:i'),
                TextColumn::make('end_time')
                    ->label('إلى')
                    ->time('H:i'),
                IconColumn::make('is_active')
                    ->label('مُفعّل')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('day_of_week')
                    ->label('اليوم')
                    ->options(PlayTime::$dayNames),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة وقت لعب'),
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
            ->defaultSort('day_of_week');
    }
}
