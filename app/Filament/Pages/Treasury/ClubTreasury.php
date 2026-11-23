<?php

namespace App\Filament\Pages\Treasury;

class ClubTreasury extends ActivityTreasury
{
    protected static string $activity = 'club';

    protected static ?int $navigationSort = 12;

    protected static ?string $navigationLabel = 'خزينة النادي';
}
