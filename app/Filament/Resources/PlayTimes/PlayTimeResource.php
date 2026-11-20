<?php

namespace App\Filament\Resources\PlayTimes;

use App\Filament\Resources\PlayTimes\Pages\CreatePlayTime;
use App\Filament\Resources\PlayTimes\Pages\EditPlayTime;
use App\Filament\Resources\PlayTimes\Pages\ListPlayTimes;
use App\Filament\Resources\PlayTimes\Schemas\PlayTimeForm;
use App\Filament\Resources\PlayTimes\Tables\PlayTimesTable;
use App\Models\PlayTime;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlayTimeResource extends Resource
{
    protected static ?string $model = PlayTime::class;

    protected static ?string $modelLabel = 'وقت لعب';

    protected static ?string $pluralModelLabel = 'أوقات اللعب';

    protected static ?string $navigationLabel = 'أوقات اللعب';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return PlayTimeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlayTimesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlayTimes::route('/'),
            'create' => CreatePlayTime::route('/create'),
            'edit' => EditPlayTime::route('/{record}/edit'),
        ];
    }
}
