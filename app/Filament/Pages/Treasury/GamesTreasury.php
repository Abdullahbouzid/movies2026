<?php

namespace App\Filament\Pages\Treasury;

class GamesTreasury extends ActivityTreasury
{
    protected static string $activity = 'games';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'خزينة الألعاب';
}
