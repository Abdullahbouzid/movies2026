<?php

namespace App\Filament\Resources\Branches\RelationManagers;

use App\Models\Branch;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared read-only listing of a branch's bookings, one subclass per service.
 */
abstract class BranchBookingsRelationManager extends RelationManager
{
    /** Service key from Branch::SERVICES that gates this tab. */
    protected static string $service = '';

    protected static ?string $title = 'الحجوزات';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Branch && $ownerRecord->offers(static::$service);
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns(static::columns())
            ->filters(static::filters())
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    /** @return array<int, TextColumn> */
    abstract protected static function columns(): array;

    /** @return array<int, SelectFilter> */
    protected static function filters(): array
    {
        return [];
    }
}
