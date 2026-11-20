# AGENTS.md

## Project

"سينما بلاس" (Cinema Plus) — Laravel 13 + Filament 5 (PHP 8.4) cinema site.
Public frontend for movies + booking; admin panel at `/admin` for content.

## Stack & Commands

```bash
composer install
php artisan migrate:fresh --seed   # SQLite at database/database.sqlite
php artisan storage:link           # exposes storage/app/public at /storage
php artisan serve --port=8000
php artisan test                   # 10 feature tests
```

Admin user: `admin@cinema.test` / `password`.

## Data model

- `Genre` — name
- `Package` — name, seats_count, includes
- `Movie` — name, image, genre_id, description, duration, rating, ratings_count,
  age_rating, status (`available` | `coming_soon`), badge (`trending`|`new`|`popular`),
  poster_from/poster_to (gradient), accent_icon (coming-soon emoji), release_date
- `movie_package` pivot — per-movie, per-package `price` (a movie has many packages)
- `Showtime` — movie_id, day_of_week (0=Sunday..6=Saturday), start_time, end_time
  (8 slots per movie per day)
- `Booking` — booking_ref (`CP-XXXXXX`), movie/showtime/package, show_date, customer
  fields, price, payment_status, attendance_status
- `Game` — name, slug (auto from name), icon (emoji), image, description, meta,
  color_from/color_to (gradient, deterministic palette fallback), badge
  (`trending`|`new`|`popular`), rating, ratings_count, sort_order, is_active
- `GamePrice` — game_id, label (`30 دقيقة`|`ساعة`|`ساعتان`), minutes, price,
  is_default, sort_order (durations + prices per game)
- `PlayTime` — game_id, day_of_week (0=Sunday..6=Saturday), start_time, end_time,
  is_active; `PlayTime::$dayNames` maps int → Arabic day
- `GameBooking` — booking_ref (`GM-XXXXXX`), game/game_price, play_date,
  duration_label/minutes (snapshot), price, customer fields, payment_status,
  attendance_status; `qrPayload()` feeds the QR URL
- `Tournament` — name, game_id, description, image, participants_count, format
  (`knockout`|`league`|`groups`), status (`upcoming`|`open`|`drawn`|`running`|`finished`),
  entry_fee, prize, starts_on, starts_at, drawn_at
- `TournamentParticipant` — tournament_id, name, seed, is_bye
- `TournamentMatch` — tournament_id, round, round_label, position, player_one_id,
  player_two_id, scores, winner_id; `label()` → "playerOne × playerTwo"
- `TournamentAnnouncement` — tournament_id, title, body, image, badge,
  published_at, is_published; `published()` scope

## Games system (public `/games` + admin)

- Public `/games` is **DB-driven** (no longer the static `public/site/games.html`):
  `GameController@index` builds a payload (games with prices + per-day play times,
  week days, countries) printed as `window.GAMES`; `@book` stores a `GameBooking`
  and returns JSON (`ok`, `reference`, `qr_url`, …) or redirects with `booked_ref`.
- Views/assets: `resources/views/games/index.blade.php`, `public/css/games.css`,
  `public/js/games.js` — ported from the original `games.html` design.
- `app/Support/TournamentDraw.php` — single-elimination bracket builder: shuffles
  participants, seeds them, pads to the nearest power of two with byes, and
  auto-advances byes. Round labels: ربع النهائي / نصف النهائي / النهائي.
- Filament resources: `Games` (with `PricesRelationManager` + `PlayTimesRelationManager`
  tabs and a `prices` repeater in the form), `PlayTimes`, `GameBookings`,
  `Tournaments` (with `ParticipantsRelationManager` + `MatchesRelationManager` and an
  «إجراء القرعة» draw action), `TournamentAnnouncements`.
- `GameSeeder` ports the original hardcoded games/tournament data; called from
  `DatabaseSeeder` after `CinemaSeeder`.
- Tests: `tests/Feature/GamesFlowTest.php` covers the public page, payload, booking
  persistence/JSON, validation, draw bracket, announcement, and admin resource pages.

## Layout

- `app/Filament/Resources/{Genres,Packages,Movies,Showtimes,Bookings}/` — Filament 5
  resources use `Schemas/` (form) + `Tables/` (table) subfolders.
- `app/Filament/Resources/Movies/Concerns/ManagesPackagePrices.php` — trait used by
  Create/Edit Movie pages. `mutateFormDataBeforeFill` seeds the `package_prices`
  repeater; `extractPackagePrices` + `syncPackagePrices` persist pivot prices.
- `app/Http/Controllers/MovieController.php` — public listing, detail, booking, ticket.
  `index()` builds a `payload` array (movies with per-day showtimes and per-package
  prices, packages, countries, week days) that the Blade view prints as
  `window.CINEMA = @json($payload)`.
- `app/Support/Countries.php` — country dial codes for phone/WhatsApp selects.
- Views: `resources/views/movies/{index,show,ticket}.blade.php`, layout in
  `resources/views/layouts/app.blade.php` (Arabic/RTL, Tajawal font, red brand).
- The movies page (`resources/views/movies/index.blade.php`) is a **standalone**
  document (no `@extends`): it links `public/css/movies.css` and
  `public/js/movies.js`, which are the original `movies.html` design, and hydrates
  from `window.CINEMA`. Booking posts JSON to `/movies/{id}/book` and renders the
  success QR card in place.
- `public/css/movies.css` + `public/js/movies.js` are the extracted original design
  (hero, ticker, stats, badges, ticket tabs, country dropdown, QR ticket).
- Original static site preserved in `Movies_extracted/Movies/` for reference
  (branding, copy, poster gradients, data values).
- The home/games/club/cafe interfaces are served unchanged from `public/site/*.html`
  (their own inline CSS/JS = original design). Routes in `routes/web.php` return
  them via `response()->file(...)` so Blade does not parse the `@`/`{` characters.
  Nav links inside those files were rewritten from `*.html` to the Laravel routes.

## Conventions & gotchas

- All UI text is Arabic, `dir="rtl"`.
- Prices use the ﷼ symbol; store as decimal on the pivot.
- `age_rating` options: الجميع, +7, +12, +16, +18.
- Blade: do NOT call `@json(...)` with a closure/arrow-fn — the parser breaks on
  `[...]` inside. Precompute JSON in a `@php` block instead.
- Filament resources need `User` to implement `FilamentUser` (`canAccessPanel`) or
  non-local environments return 403.
- Repeater state in tests is keyed by UUID, not numeric index.
- `composer.json` pins `config.platform.php` to `8.3.31` so the lock resolves
  Symfony 7.4 (PHP 8.3 compatible). Without it, `composer update` pulls
  Symfony 8.x which needs PHP >= 8.4.1 and `composer install` fails on 8.3.
