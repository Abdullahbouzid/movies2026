<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    public const FORMATS = [
        'knockout' => 'خروج المغلوب',
        'league' => 'دوري',
        'groups' => 'مجموعات',
    ];

    public const STATUSES = [
        'upcoming' => 'قادمة',
        'open' => 'التسجيل مفتوح',
        'drawn' => 'تمت القرعة',
        'running' => 'جارية',
        'finished' => 'منتهية',
    ];

    protected $fillable = [
        'name', 'game_id', 'description', 'image', 'participants_count',
        'format', 'status', 'entry_fee', 'prize', 'starts_on', 'starts_at', 'drawn_at',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'drawn_at' => 'datetime',
        'entry_fee' => 'decimal:2',
        'prize' => 'decimal:2',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TournamentParticipant::class)->orderBy('seed');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class)->orderBy('round')->orderBy('position');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(TournamentAnnouncement::class);
    }

    public function isDrawn(): bool
    {
        return $this->drawn_at !== null;
    }

    public function registeredCount(): int
    {
        return $this->participants()->where('is_bye', false)->count();
    }
}
