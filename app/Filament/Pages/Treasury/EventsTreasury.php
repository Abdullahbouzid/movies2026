<?php

namespace App\Filament\Pages\Treasury;

class EventsTreasury extends ActivityTreasury
{
    protected static string $activity = 'events';

    protected static ?int $navigationSort = 13;

    protected static ?string $navigationLabel = 'خزينة المناسبات';
}
