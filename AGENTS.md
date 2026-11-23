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
php artisan test                   # 117 feature tests
```

Admin user: `admin@cinema.test` / `password`.

## Data model

- `Genre` — name
- `Package` — name, seats_count, includes
- `Movie` — name, image, video (uploaded MP4/WebM/MOV, ≤150 MB), trailer_url
  (external fallback), genre_id, description, duration, rating, ratings_count,
  age_rating, status (`available` | `coming_soon`), badge (`trending`|`new`|`popular`),
  poster_from/poster_to (gradient), accent_icon (coming-soon emoji), release_date
- `movie_package` pivot — per-movie, per-package `price` (a movie has many packages)
- `Showtime` — movie_id, day_of_week (0=Sunday..6=Saturday), start_time, end_time
  (8 slots per movie per day)
- `Booking` — booking_ref (`CP-XXXXXX`), movie/showtime/package, show_date, customer
  fields, price, payment_status, attendance_status
- `Game` — name, slug (auto from name), icon (emoji), image, description, meta,
  devices_count (independent booking units per game),
  color_from/color_to (gradient, deterministic palette fallback), badge
  (`trending`|`new`|`popular`), rating, ratings_count, sort_order, is_active
- `GamePrice` — game_id, label (`30 دقيقة`|`ساعة`|`ساعتان`), minutes, price,
  is_default, sort_order (durations + prices per game)
- `PlayTime` — game_id, day_of_week (0=Sunday..6=Saturday), start_time, end_time,
  is_active; `PlayTime::$dayNames` maps int → English day name (Sunday…Saturday)
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
- `Club` — name, location, description, image, color_from/color_to, badge,
  sort_order, is_active
- `ClubPackage` — club_id, name, includes, people_count, start_time, end_time,
  price, is_default, sort_order, is_active
- `ClubBooking` — booking_ref, club/club_package, visit_date, package_name snapshot,
  start_time/end_time, people_count, price, qr_path, customer fields
  (full_name, country_name/code, phone, whatsapp_country_code, whatsapp),
  payment_status, attendance_status
- `EventPackage` — name, includes, people_count, price, image, is_default,
  sort_order, is_active
- `EventBooking` — booking_ref, event_package, event_date, start_time/end_time,
  package_name snapshot, people_count, price, qr_path, customer fields, notes,
  payment_status, attendance_status
- `Branch` — name, location, phone, image, `services` (JSON array of
  `cinema`|`games`|`club`|`events`, validated against `Branch::SERVICES` on save),
  is_main (only one branch may be main — enforced in `booted()`), sort_order,
  is_active. Relations: `bookings`, `gameBookings`, `clubBookings`, `eventBookings`.
- Every booking table has a nullable `branch_id`. `App\Models\Concerns\BelongsToBranch`
  fills it on create: the first active branch (main first, then `sort_order`)
  that offers the booking's service, else the main branch, else null.

## Branches (فروع)

- `/admin/branches` — `BranchResource`: form has a services `CheckboxList`
  (`Branch::SERVICES`), the table shows service badges + a main-center badge, and
  `getRelations()` registers four read-only relation managers
  (`Branches/RelationManagers/*BookingsRelationManager`) that each list the
  branch's bookings for one service. They share the abstract
  `BranchBookingsRelationManager`; `canViewForRecord()` hides a tab when the
  branch doesn't offer that service.
- Booking admin tables (`Bookings`, `GameBookings`, `ClubBookings`,
  `EventBookings`) reuse `BuildsBookingAdmin::branchColumn()` / `branchFilter()`
  and their forms expose an editable `branch_id` select.
- `BranchSeeder` (called first in `DatabaseSeeder`) creates المركز الرئيسي (all
  services, `is_main`) plus a games-only and a cinema-only branch.
- Tests: `tests/Feature/BranchFlowTest.php`.

## Games system (public `/games` + admin)

- Public `/games` is **DB-driven** (no longer the static `public/site/games.html`):
  `GameController@index` builds a payload (games with prices + per-day play times,
  week days, countries) printed as `window.GAMES`; `@book` stores a `GameBooking`
  and returns JSON (`ok`, `reference`, `qr_url`, …) or redirects with `booked_ref`.
- Views/assets: `resources/views/games/index.blade.php`, `public/css/games.css`,
  `public/js/games.js` — ported from the original `games.html` design.
- `app/Support/WeekSchedule.php` — `generate(array $times, $start, int $days)` expands
  weekly slot templates into dated days (used by showtime creation).
- `app/Support/GameSchedule.php` — `takenDevices(Game, $date)` / `freeDevices(Game, $slots)`
  allocate the game's `devices_count` across a slot so the same device cannot be
  double-booked.
- `app/Support/PlayTimeConflict.php` — `firstOverlap()`/`clash()` detect an overlapping
  time range for the same `game_id` + `day_of_week` (excluding self by id).
- `app/Support/QrCode.php` — renders booking QR PNGs to `storage/app/public/qrcodes`
  via `bacon/bacon-qr-code` (GDLib renderer; needs the PHP `gd` extension).
- `app/Support/TournamentDraw.php` — single-elimination bracket builder: shuffles
  participants, seeds them, pads to the nearest power of two with byes, and
  auto-advances byes. Round labels: ربع النهائي / نصف النهائي / النهائي.
- Filament resources: `Games` (with `PricesRelationManager` + `PlayTimesRelationManager`
  tabs and a `prices` repeater in the form), `PlayTimes`, `GameBookings`,
  `Tournaments` (with `ParticipantsRelationManager` + `MatchesRelationManager` and an
  «إجراء القرعة» draw action), `TournamentAnnouncements`.
- `GameSeeder` ports the original hardcoded games/tournament data; called from
  `DatabaseSeeder` after `CinemaSeeder`.
- Play-time conflicts are enforced twice: `PlayTime` has a `booted()` guard that throws
  `ValidationException` (covers seeders, relation managers, any code path), and
  `App\Rules\PlayTimeConflictRule` reports the clash on the `end_time` form field.
- Tests: `tests/Feature/GamesFlowTest.php` covers the public page, payload, booking
  persistence/JSON, validation, draw bracket, announcement, and admin resource pages.
  `tests/Feature/SchedulingFlowTest.php` covers English day names, week auto-population,
  device allocation, and play-time conflict rejection (model + admin form).

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
- The home page is served unchanged from `public/site/index.html` via
  `response()->file(...)` so Blade does not parse the `@`/`{` characters.
- `/club` and `/cafe` are now DB-driven: `ClubController@index` + `EventController@index`
  render `resources/views/club/index.blade.php` and `resources/views/events/index.blade.php`
  with `public/js/club.js` / `public/js/events.js`. Both post bookings as JSON and
  render an in-place QR success card.
- `VerifyController` serves `/verify/{ticket,game,club,event}/{ref}` pages so scanning a
  booking QR shows its status.

## Hero showcase (all four booking pages)

- `App\Support\HeroShowcase` normalises one specialty's records into slide arrays
  (`title`, `meta`, `description`, `image`, `gradient`, `icon`, `url`). Chain
  `->movies()`, `->games()`, `->clubs()` or `->events()` then `->slides()`.
- Each booking controller passes `'showcase' => (new HeroShowcase('#accent'))->…->slides()`;
  the four views `@include('partials.hero-showcase')` so every hero shows its **own**
  inventory — movies on `/movies`, games on `/games`, clubs on `/club`, event packages
  on `/cafe`.
- Assets: `public/css/hero-showcase.css` + `public/js/hero-showcase.js`. The track is a
  CSS scroll-snap container, so cards stay readable without JS; the script only adds
  arrows, dots and keyboard support. Hidden entirely when a specialty has no active rows.
- Standalone views (movies/games) link the assets directly; layout-based views
  (club/cafe) use the layout's `@stack('head')` for CSS and `@stack('scripts')` for JS.

## Video uploads (150 MB)

Three layers must agree, all driven by `config/uploads.php`:

- PHP: `upload_max_filesize=160M`, `post_max_size=170M` — `public/.user.ini` for FPM/CGI,
  plus the CLI `php.ini` for `php artisan serve`.
- Livewire: `config/livewire.php` → `temporary_file_upload.rules = file|max:153600`,
  `max_upload_time = 30` minutes.
- Filament: `FileUpload::make('video')->maxSize(config('uploads.movie_video_max_kb'))`.

Override with `MOVIE_VIDEO_MAX_KB` / `UPLOAD_MAX_MINUTES`. The Filament field keeps
`trailer_url` as an external fallback; `Movie::videoUrl()` prefers the upload.

## Booking integrity, privacy & assets

- `app/Support/SeatAvailability.php` — `sold()`, `remaining()`, `hasRoomFor()`,
  `weekGrid()`. Capacity lives on `Showtime.seats_capacity`; sold seats are the
  sum of `Booking.seats`. `MovieController@book` re-checks capacity inside a DB
  transaction, so a full slot returns 422 (`ok: false`) instead of overselling.
- Payload `times` is **keyed by day index**, and each day is a list of slots:
  `times[0][0].seatsLeft`. Full slots render disabled with the label `مكتمل`.
- `app/Support/Mask.php` — `Mask::phone()` for ticket/verify pages. Never print
  a full customer phone on a page reachable by reference.
- `HasBookingQr::whatsappUrl()` — strips the trunk zero from local numbers
  (`0925…` → `218925…`) before building the `wa.me` link.
- Check-in: `POST /verify/{type}/{ref}/check-in` (`verify.check-in`). Type
  segments are `ticket` (cinema, what QR codes encode), `game`, `club`, `event`;
  `cinema` remains a legacy alias for the POST route.
- `@versioned('css/movies.css')` — Blade directive appending `?v=<filemtime>`
  (`app/Support/AssetVersion.php`). Use it for every `public/` asset.
- `resources/views/partials/meta.blade.php` — description/canonical/OG/Twitter
  tags. Standalone pages `@include` it; layout pages fill `@section('meta')`.
- Upload limits live in `config/uploads.php` (`movie_video_max_kb`,
  `image_max_kb`) and must match Livewire's temporary-upload rule.
- `.github/workflows/tests.yml` runs the suite on PHP 8.3 and 8.4.

## Treasury & revenue reporting

- `paid_at` (nullable datetime) on `bookings`, `game_bookings`, `club_bookings`,
  `event_bookings` — stamped automatically when a row flips to
  `payment_status = paid`, cleared when it flips back. Never set it by hand:
  call `markPaid()` / `markPending()`.
- `app/Models/Concerns/HasPaymentState` — model-level `saving` hook that stamps
  `paid_at`, plus `markPaid()`, `markPending()` and the `paidBetween($from, $to)`
  scope. Included in all four booking models, so resource actions, edit forms
  and seeders all record a cash-in date.
- `app/Support/Treasury` — the single source of truth for money figures.
  `activities()` maps `cinema|games|club|events` → label/model/icon/color;
  `forActivity()`, `summary($from, $to, $branchId)`, `dailySeries($from, $to)`,
  `todayTotal()`, `stalePending($days)`. Revenue is derived from the booking
  tables; there is no separate ledger to drift.
- Admin pages under `/admin` in the `الخزينة والتقارير` nav group:
  `treasury` (overview), `cinema-treasury`, `games-treasury`, `club-treasury`,
  `events-treasury`, `revenue-report`. Base class
  `app/Filament/Pages/Treasury/ActivityTreasury.php`; concrete pages only set
  `$activity`.
- `TreasuryOverview` unions the four booking tables into `treasury_rows` and
  queries it through the read-only `App\Models\TreasuryEntry` model. Each union
  branch computes `row_id` (`activity || '-' || id` on SQLite,
  `concat(...)` on MySQL) because the tables share id values and Filament needs
  a unique key to paginate/sort/search.
- `app/Support/CsvExport` — dependency-free CSV stream with a UTF-8 BOM so Excel
  renders Arabic. The report page also links to a printable view
  (`/admin/revenue-report/print`, registered via
  `Panel::authenticatedRoutes`) that the browser saves as PDF.
- Dashboard widgets: `TreasuryStatsOverview`, `RevenueChart`,
  `ActivityRevenueChart`. They are lazy-loaded, so their content only appears
  once mounted — assert on them with `Livewire::test(...)`, not `get('/admin')`.

## Conventions & gotchas

- All UI text is Arabic, `dir="rtl"`.
- Currency is **Libyan dinar (LYD)** — symbol `د.ل`, from `config/currency.php`.
  Store decimals; never hardcode the symbol. Format with `App\Support\Money`:
  `Money::format()` for exact amounts (Filament columns, tables), `Money::short()`
  for compact hero/card figures, `Money::label('السعر')` for form labels.
  Avoid Filament's `->money('LYD')` — it renders `LYD 15.000`, not `د.ل`.
- Customer booking is a **3-step wizard** in the movies modal: `data-panel="1|2|3"`
  (ticket → details → review), driven by `currentBooking.step` in `public/js/movies.js`.
  Step 2 blocks on incomplete fields; step 3 renders `#reviewCard` before POSTing.
- **Capacity**: `showtimes.seats_capacity` (per slot, default 60) guards cinema;
  `club_packages.capacity` / `event_packages.capacity` guard venue slots and are
  **0 = unlimited** so legacy packages keep working. Helpers live in
  `App\Support\SeatAvailability` (cinema) and `App\Support\SlotAvailability`
  (club/event). Club/Event bookings wrap the create in `DB::transaction` with
  `lockForUpdate()` on the package row so concurrent requests cannot oversell.
- **Rolling schedule**: showtimes are stored per concrete date, so the public grid
  expires once the seeded range runs out. `showtimes:generate --prune` tops up
  `config('cinema.schedule_horizon_days')` days and is scheduled nightly at 02:00
  in `routes/console.php`. Slots/seat capacity come from `config/cinema.php`.
- **WhatsApp links**: `HasBookingQr::internationalNumber()` normalizes phone input.
  Customers paste `+218…` while the country selector still shows +218, so the dial
  code is dropped when the number already starts with it (prevents `218218…`).
- `age_rating` options: الجميع, +7, +12, +16, +18.
- Blade: do NOT call `@json(...)` with a closure/arrow-fn — the parser breaks on
  `[...]` inside. Precompute JSON in a `@php` block instead.
- Filament resources need `User` to implement `FilamentUser` (`canAccessPanel`) or
  non-local environments return 403.
- Repeater state in tests is keyed by UUID, not numeric index.
- `composer.json` pins `config.platform.php` to `8.3.31` so the lock resolves
  Symfony 7.4 (PHP 8.3 compatible). Without it, `composer update` pulls
  Symfony 8.x which needs PHP >= 8.4.1 and `composer install` fails on 8.3.
