<?php

namespace App\Filament\Resources\TournamentAnnouncements;

use App\Filament\Resources\TournamentAnnouncements\Pages\CreateTournamentAnnouncement;
use App\Filament\Resources\TournamentAnnouncements\Pages\EditTournamentAnnouncement;
use App\Filament\Resources\TournamentAnnouncements\Pages\ListTournamentAnnouncements;
use App\Filament\Resources\TournamentAnnouncements\Schemas\TournamentAnnouncementForm;
use App\Filament\Resources\TournamentAnnouncements\Tables\TournamentAnnouncementsTable;
use App\Models\TournamentAnnouncement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TournamentAnnouncementResource extends Resource
{
    protected static ?string $model = TournamentAnnouncement::class;

    protected static ?string $modelLabel = 'إعلان بطولة';

    protected static ?string $pluralModelLabel = 'إعلانات البطولات';

    protected static ?string $navigationLabel = 'إعلانات البطولات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return TournamentAnnouncementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TournamentAnnouncementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTournamentAnnouncements::route('/'),
            'create' => CreateTournamentAnnouncement::route('/create'),
            'edit' => EditTournamentAnnouncement::route('/{record}/edit'),
        ];
    }
}
