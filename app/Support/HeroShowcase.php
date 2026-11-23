<?php

namespace App\Support;

use App\Models\Club;
use App\Models\EventPackage;
use App\Models\Game;
use App\Models\Movie;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Normalises the records of one specialty into the slide shape consumed by
 * resources/views/partials/hero-showcase.blade.php.
 *
 * Every booking page shows its own inventory in the hero: movies on /movies,
 * games on /games, clubs on /club and event packages on /cafe.
 */
class HeroShowcase
{
    /** @var array<int, array{title:string,meta:string,description:string,image:?string,gradient:string,icon:string,url:?string}> */
    private array $slides = [];

    public function __construct(private readonly string $accent = '#e71d24')
    {
    }

    /** @param  Collection<int, Movie>  $movies */
    public function movies(Collection $movies): self
    {
        foreach ($movies as $movie) {
            $this->push(
                title: $movie->name,
                meta: $this->movieMeta($movie),
                description: (string) $movie->description,
                image: $movie->image,
                from: $movie->posterFrom(),
                to: $movie->posterTo(),
                icon: '🎬',
                url: route('movies.show', $movie),
            );
        }

        return $this;
    }

    /** @param  Collection<int, Game>  $games */
    public function games(Collection $games): self
    {
        foreach ($games as $game) {
            $prices = $game->relationLoaded('prices') ? $game->prices : collect();
            $cheapest = $prices->min('price');

            $meta = array_filter([
                $game->devices_count ? $game->devices_count . ' أجهزة' : null,
                $game->meta,
                $cheapest !== null ? 'من ' . $this->money((float) $cheapest) : null,
            ]);

            $this->push(
                title: $game->name,
                meta: implode(' · ', $meta),
                description: (string) $game->description,
                image: $game->image,
                from: $game->colorFrom(),
                to: $game->colorTo(),
                icon: $game->icon ?: '🎮',
            );
        }

        return $this;
    }

    /** @param  Collection<int, Club>  $clubs */
    public function clubs(Collection $clubs): self
    {
        foreach ($clubs as $club) {
            $meta = array_filter([
                $club->location,
                $club->packages->count() . ' باقات',
                $club->minPrice() !== null ? 'من ' . $this->money($club->minPrice()) : null,
            ]);

            $this->push(
                title: $club->name,
                meta: implode(' · ', $meta),
                description: (string) $club->description,
                image: $club->image,
                from: $club->colorFrom(),
                to: $club->colorTo(),
                icon: '🛋️',
            );
        }

        return $this;
    }

    /** @param  Collection<int, EventPackage>  $packages */
    public function events(Collection $packages): self
    {
        foreach ($packages as $package) {
            $meta = array_filter([
                $package->people_count ? $package->people_count . ' أشخاص' : null,
                'من ' . $this->money((float) $package->price),
            ]);

            $this->push(
                title: $package->name,
                meta: implode(' · ', $meta),
                description: (string) $package->includes,
                image: $package->image,
                from: '#7c2d12',
                to: '#b45309',
                icon: '🎉',
            );
        }

        return $this;
    }

    /** @return array<int, array<string, mixed>> */
    public function slides(): array
    {
        return $this->slides;
    }

    public function count(): int
    {
        return count($this->slides);
    }

    public function accent(): string
    {
        return $this->accent;
    }

    private function push(
        string $title,
        string $meta,
        string $description,
        ?string $image,
        string $from,
        string $to,
        string $icon,
        ?string $url = null,
    ): void {
        $this->slides[] = [
            'title' => $title,
            'meta' => $meta,
            'description' => Str::limit($description, 220),
            'image' => $image ? asset('storage/' . $image) : null,
            'gradient' => "linear-gradient(150deg, {$from}, {$to})",
            'icon' => $icon,
            'url' => $url,
        ];
    }

    private function movieMeta(Movie $movie): string
    {
        return implode(' · ', array_filter([
            $movie->genre?->name,
            $movie->duration ? $movie->duration . ' دقيقة' : null,
            $movie->age_rating,
            $movie->rating > 0 ? '⭐ ' . number_format((float) $movie->rating, 1) : null,
        ]));
    }

    private function money(float $value): string
    {
        return Money::short($value);
    }
}
