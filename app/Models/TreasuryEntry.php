<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only row of the consolidated treasury view.
 *
 * The table is never created: every query is built from a union of the four
 * booking tables aliased as `treasury_rows`, so Filament gets one model with a
 * real primary key to paginate, sort and search across all activities.
 */
class TreasuryEntry extends Model
{
    protected $table = 'treasury_rows';

    protected $primaryKey = 'row_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
