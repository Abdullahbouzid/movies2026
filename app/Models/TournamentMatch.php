<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentMatch extends Model
{
    protected $fillable = [
        'tournament_id', 'round', 'round_label', 'position',
        'player_one_id', 'player_two_id', 'winner_id', 'score_one', 'score_two',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function playerOne(): BelongsTo
    {
        return $this->belongsTo(TournamentParticipant::class, 'player_one_id');
    }

    public function playerTwo(): BelongsTo
    {
        return $this->belongsTo(TournamentParticipant::class, 'player_two_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(TournamentParticipant::class, 'winner_id');
    }

    public function label(): string
    {
        return ($this->playerOne?->name ?? '—') . ' × ' . ($this->playerTwo?->name ?? '—');
    }
}
