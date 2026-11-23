<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\GameBooking;
use App\Support\Treasury;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Printable version of the revenue report. Browsers can save this page as PDF,
 * which avoids shipping a PDF renderer and its font dependencies.
 */
class ReportController extends Controller
{
    public function revenue(Request $request): View
    {
        $from = $request->query('from') ?: today()->startOfMonth()->toDateString();
        $to = $request->query('to') ?: today()->toDateString();

        $summary = Treasury::summary($from, $to);

        $topMovies = Booking::query()
            ->paidBetween($from, $to)
            ->selectRaw('movie_id, count(*) as bookings, sum(price) as total')
            ->groupBy('movie_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('movie:id,name')
            ->get();

        $topGames = GameBooking::query()
            ->paidBetween($from, $to)
            ->selectRaw('game_id, count(*) as bookings, sum(price) as total')
            ->groupBy('game_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('game:id,name')
            ->get();

        return view('reports.revenue', [
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'topMovies' => $topMovies,
            'topGames' => $topGames,
            'generatedAt' => Carbon::now(),
            'dailySeries' => Treasury::dailySeries(Carbon::parse($from), Carbon::parse($to)),
        ]);
    }
}
