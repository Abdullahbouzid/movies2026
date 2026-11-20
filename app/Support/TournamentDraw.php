<?php

namespace App\Support;

use App\Models\Tournament;
use App\Models\TournamentMatch;

class TournamentDraw
{
    /**
     * Build a single-elimination bracket for the tournament.
     *
     * Participants are shuffled (the draw), seeded in that order, then padded to
     * the nearest power of two with byes. A bye is auto-advanced to the next
     * round so the bracket is playable from the first match.
     */
    public function draw(Tournament $tournament): void
    {
        $tournament->matches()->delete();

        $players = $tournament->participants()
            ->where('is_bye', false)
            ->inRandomOrder()
            ->get()
            ->values();

        $count = $players->count();

        if ($count < 1) {
            return;
        }

        foreach ($players as $index => $player) {
            $player->update(['seed' => $index + 1]);
        }

        $size = 2;
        while ($size < $count) {
            $size *= 2;
        }

        $round = 1;
        $previous = collect();

        for ($i = 0; $i < $size / 2; $i++) {
            $one = $players->get($i * 2);
            $two = $players->get($i * 2 + 1);

            $match = TournamentMatch::create([
                'tournament_id' => $tournament->id,
                'round' => $round,
                'round_label' => $this->label((int) ($size / 2)),
                'position' => $i + 1,
                'player_one_id' => $one?->id,
                'player_two_id' => $two?->id,
            ]);

            $this->autoAdvance($match);
            $previous->push($match);
        }

        while ($previous->count() > 1) {
            $round++;
            $matchesInRound = (int) ($previous->count() / 2);
            $current = collect();

            for ($i = 0; $i < $matchesInRound; $i++) {
                $a = $previous->get($i * 2);
                $b = $previous->get($i * 2 + 1);

                $match = TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => $round,
                    'round_label' => $this->label($matchesInRound),
                    'position' => $i + 1,
                    'player_one_id' => $a?->winner_id,
                    'player_two_id' => $b?->winner_id,
                ]);

                $this->autoAdvance($match);
                $current->push($match);
            }

            $previous = $current;
        }

        $tournament->update([
            'drawn_at' => now(),
            'status' => $tournament->status === 'finished' ? 'finished' : 'drawn',
        ]);
    }

    private function autoAdvance(TournamentMatch $match): void
    {
        if ($match->player_one_id && ! $match->player_two_id) {
            $match->update(['winner_id' => $match->player_one_id]);
        } elseif ($match->player_two_id && ! $match->player_one_id) {
            $match->update(['winner_id' => $match->player_two_id]);
        }
    }

    private function label(int $matchesInRound): string
    {
        return match (true) {
            $matchesInRound === 1 => 'النهائي',
            $matchesInRound === 2 => 'نصف النهائي',
            $matchesInRound === 4 => 'ربع النهائي',
            default => 'دور الـ' . ($matchesInRound * 2),
        };
    }
}
