<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\EventPackage;
use App\Models\Game;
use App\Models\Movie;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $movie = Movie::query()
            ->available()
            ->with('genre')
            ->orderByDesc('rating')
            ->orderByDesc('ratings_count')
            ->first();

        return view('home.index', [
            'featured' => $movie,
            'nowShowing' => Movie::query()
                ->available()
                ->with('genre')
                ->orderByDesc('rating')
                ->limit(6)
                ->get(),
            'comingSoon' => Movie::query()
                ->comingSoon()
                ->with('genre')
                ->orderBy('release_date')
                ->limit(4)
                ->get(),
            'games' => Game::query()
                ->where('is_active', true)
                ->with(['prices' => fn ($q) => $q->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->limit(3)
                ->get(),
            'clubs' => Club::query()->orderBy('name')->limit(3)->get(),
            'eventPackages' => EventPackage::query()->orderBy('price')->limit(3)->get(),
            'stats' => [
                'movies' => Movie::query()->available()->count(),
                'genres' => Movie::query()->available()->distinct()->count('genre_id'),
                'games' => Game::query()->where('is_active', true)->count(),
                'packages' => EventPackage::query()->count(),
            ],
        ]);
    }
}
