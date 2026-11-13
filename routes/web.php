<?php

use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/movies')->name('home');

Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
Route::post('/movies/{movie}/book', [MovieController::class, 'book'])->name('movies.book');
Route::get('/tickets/{ref}', [MovieController::class, 'ticket'])->name('movies.ticket');

// Other interfaces keep their original static design.
Route::get('/home', fn () => response()->file(public_path('site/index.html')))->name('site.home');
Route::get('/games', fn () => response()->file(public_path('site/games.html')))->name('site.games');
Route::get('/club', fn () => response()->file(public_path('site/club.html')))->name('site.club');
Route::get('/cafe', fn () => response()->file(public_path('site/cafe.html')))->name('site.cafe');
