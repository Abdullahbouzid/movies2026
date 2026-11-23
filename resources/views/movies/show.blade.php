@extends('layouts.app')

@section('title', 'سينما بلاس | ' . $movie->name)
@section('nav_movies', 'active')

@section('meta')
@include('partials.meta', [
    'title' => 'سينما بلاس | ' . $movie->name,
    'description' => \Illuminate\Support\Str::limit(strip_tags($movie->description ?? ''), 150) ?: ('احجز تذاكر فيلم ' . $movie->name . ' في سينما بلاس — أوقات العرض وأسعار الباقات مع تأكيد فوري برمز QR.'),
    'image' => $movie->image ? \Illuminate\Support\Facades\Storage::disk('public')->url($movie->image) : null,
])
@endsection

@push('styles')
<style>
.wrap{padding:40px 0 10px}
.top{display:grid;grid-template-columns:320px 1fr;gap:32px;align-items:start}
.poster{border-radius:22px;overflow:hidden;box-shadow:0 30px 60px -30px rgba(0,0,0,.4)}
.poster img{width:100%;display:block}
.trailer{margin-bottom:24px;border-radius:18px;overflow:hidden;border:1px solid var(--line);background:#000}
.trailer video{width:100%;display:block;max-height:420px}
.info h1{font-size:clamp(26px,4vw,40px);font-weight:900;letter-spacing:-1px;margin-bottom:12px}
.info .meta{display:flex;gap:14px;flex-wrap:wrap;color:var(--muted);font-weight:700;font-size:14px;margin-bottom:16px}
.info .meta .star{color:var(--gold)}
.info p{color:var(--muted);line-height:2;font-size:15px;margin-bottom:22px}
.pkg-table{display:grid;gap:12px;margin-bottom:26px}
.pkg-row{display:flex;align-items:center;justify-content:space-between;gap:14px;border:1.5px solid var(--line);border-radius:15px;padding:14px 18px}
.pkg-row .nm{font-weight:900}
.pkg-row .inc{font-size:12.5px;color:var(--muted);margin-top:4px}
.pkg-row .pr{font-weight:900;font-size:20px;white-space:nowrap}
.schedule{margin-top:14px}
.day-block{border:1px solid var(--line);border-radius:16px;margin-bottom:12px;overflow:hidden}
.day-head{background:var(--card-2);padding:12px 18px;font-weight:900;font-size:15px}
.slots{display:flex;flex-wrap:wrap;gap:9px;padding:16px 18px}
.slot{border:1px solid var(--line);border-radius:10px;padding:9px 14px;font-weight:800;font-size:13.5px;background:#fff}
@media(max-width:760px){.top{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="container wrap">
  <a href="{{ route('movies.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:22px">→ رجوع للأفلام</a>

  <div class="top">
    <div class="poster">
      <img src="{{ $movie->image ? asset('storage/' . $movie->image) : asset('storage/movies/movie-1.jpg') }}" alt="{{ $movie->name }}">
    </div>
    <div class="info">
      <h1>{{ $movie->name }}</h1>
      <div class="meta">
        <span class="star">★ {{ $movie->rating }}</span>
        <span>•</span><span>{{ $movie->genre->name }}</span>
        <span>•</span><span>⏱ {{ $movie->duration }} دقيقة</span>
        @if ($movie->age_rating)<span>•</span><span>{{ $movie->age_rating }}</span>@endif
      </div>
      <p>{{ $movie->description }}</p>

      @if ($movie->videoUrl())
        <div class="trailer">
          <video controls playsinline preload="metadata" poster="{{ $movie->image ? asset('storage/' . $movie->image) : '' }}">
            <source src="{{ $movie->videoUrl() }}">
          </video>
        </div>
      @endif

      <div class="section-head"><h2 style="font-size:20px">أسعار التذاكر حسب الباقة</h2></div>
      <div class="pkg-table">
        @foreach ($movie->packages as $package)
          <div class="pkg-row">
            <div><div class="nm">{{ $package->name }}</div><div class="inc">{{ $package->includes }}</div></div>
            <div class="pr">{{ \App\Support\Money::short((float) $package->pivot->price) }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="schedule">
    <div class="section-head" style="margin-top:30px"><h2>أوقات العرض — الأيام القادمة</h2></div>
    @forelse ($movie->showtimes->groupBy(fn ($s) => $s->show_date?->toDateString()) as $date => $daySlots)
      <div class="day-block">
        <div class="day-head">
          {{ \Illuminate\Support\Carbon::parse($date)->format('l') }}
          <span style="font-weight:600;opacity:.65;font-size:13px">{{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</span>
        </div>
        <div class="slots">
          @foreach ($daySlots as $slot)
            <span class="slot">{{ $slot->start_time_short }} — {{ $slot->end_time_short }}</span>
          @endforeach
        </div>
      </div>
    @empty
      <p style="color:var(--muted);font-weight:700">لا توجد عروض مجدولة حاليًا.</p>
    @endforelse
  </div>
</div>
@endsection
