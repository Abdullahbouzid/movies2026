<?php

use App\Http\Controllers\ClubController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\VerifyController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/movies')->name('home');

Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
Route::post('/movies/{movie}/book', [MovieController::class, 'book'])
    ->middleware('throttle:20,1')
    ->name('movies.book');
Route::get('/tickets/{ref}', [MovieController::class, 'ticket'])->name('movies.ticket');
Route::get('/verify/ticket/{ref}', [VerifyController::class, 'movie'])->name('movies.verify');

Route::get('/home', fn () => response()->file(public_path('site/index.html')))->name('site.home');

Route::get('/games', [GameController::class, 'index'])->name('site.games');
Route::post('/games/{game}/book', [GameController::class, 'book'])
    ->middleware('throttle:20,1')
    ->name('games.book');

Route::get('/verify/game/{ref}', [VerifyController::class, 'game'])->name('games.verify');
Route::get('/verify/club/{ref}', [VerifyController::class, 'club'])->name('clubs.verify');
Route::get('/verify/event/{ref}', [VerifyController::class, 'event'])->name('events.verify');

Route::post('/verify/{type}/{ref}/check-in', [VerifyController::class, 'checkIn'])
    ->whereIn('type', ['ticket', 'cinema', 'game', 'club', 'event'])
    ->middleware('throttle:30,1')
    ->name('verify.check-in');

Route::get('/club', [ClubController::class, 'index'])->name('site.club');
Route::post('/clubs/{club}/book', [ClubController::class, 'book'])
    ->middleware('throttle:20,1')
    ->name('clubs.book');

Route::get('/cafe', [EventController::class, 'index'])->name('site.cafe');
Route::post('/events/book', [EventController::class, 'book'])
    ->middleware('throttle:20,1')
    ->name('events.book');
