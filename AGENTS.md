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
