<?php

namespace App\Filament\Pages\Treasury;

class CinemaTreasury extends ActivityTreasury
{
    protected static string $activity = 'cinema';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'خزينة السينما';
}
