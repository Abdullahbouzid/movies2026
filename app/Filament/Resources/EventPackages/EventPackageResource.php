<?php

namespace App\Filament\Resources\EventPackages;

use App\Filament\Resources\EventPackages\Pages\CreateEventPackage;
use App\Filament\Resources\EventPackages\Pages\EditEventPackage;
use App\Filament\Resources\EventPackages\Pages\ListEventPackages;
use App\Filament\Resources\EventPackages\Schemas\EventPackageForm;
use App\Filament\Resources\EventPackages\Tables\EventPackagesTable;
use App\Models\EventPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventPackageResource extends Resource
{
    protected static ?string $model = EventPackage::class;

    protected static ?string $modelLabel = 'باقة مناسبات';

    protected static ?string $pluralModelLabel = 'باقات المناسبات';

    protected static ?string $navigationLabel = 'باقات المناسبات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return EventPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventPackagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventPackages::route('/'),
            'create' => CreateEventPackage::route('/create'),
            'edit' => EditEventPackage::route('/{record}/edit'),
        ];
    }
}
