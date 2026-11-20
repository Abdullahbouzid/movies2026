<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePrice extends Model
{
    protected $fillable = [
        'game_id', 'label', 'minutes', 'price', 'is_default', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
