@extends('layouts.app')

@section('title', 'سينما بلاس | النادي الاجتماعي')

@section('meta')
@include('partials.meta', [
    'title' => 'سينما بلاس | النادي الاجتماعي',
    'description' => 'احجز في النادي الاجتماعي بسينما بلاس: مسبح، صالة رياضية، جلسات عائلية وباقات يومية — أسعار واضحة وتأكيد حجز فوري برمز QR.',
])
@endsection

@push('head')
<link rel="stylesheet" href="@versioned('css/hero-showcase.css')">
@endpush

@push('styles')
<style>
.page-head{padding:38px 0 10px}
.page-head h1{font-size:clamp(24px,3.4vw,34px);font-weight:900;letter-spacing:-.5px}
.page-head p{color:var(--muted);font-size:14.5px;font-weight:600;margin-top:8px}
.club-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(330px,1fr));gap:22px;margin-top:26px}
.club-card{background:var(--card);border:1px solid var(--line);border-radius:20px;overflow:hidden;display:flex;flex-direction:column;transition:.25s}
.club-card:hover{transform:translateY(-4px);box-shadow:0 28px 60px -40px rgba(0,0,0,.35)}
.club-visual{height:150px;display:grid;place-items:center;font-size:44px;position:relative;overflow:hidden}
.club-visual::after{content:'';position:absolute;inset:0;background:radial-gradient(circle at 70% 20%,rgba(255,255,255,.28),transparent 60%)}
.club-badge{position:absolute;top:12px;inset-inline-start:12px;background:rgba(0,0,0,.55);color:#fff;font-size:11.5px;font-weight:800;padding:5px 12px;border-radius:100px;z-index:2}
.club-body{padding:18px 18px 20px;display:flex;flex-direction:column;gap:10px;flex:1}
.club-body h3{font-size:17.5px;font-weight:900}
.club-loc{font-size:12.5px;color:var(--muted);font-weight:700}
.club-desc{font-size:13.5px;color:var(--muted);line-height:1.75}
.pkgs{display:flex;flex-direction:column;gap:8px;margin-top:auto}
.pkg{border:1px dashed var(--line);border-radius:13px;padding:10px 12px;display:flex;justify-content:space-between;align-items:center;gap:10px}
.pkg b{font-size:13.5px;font-weight:800;display:block}
.pkg small{font-size:11.5px;color:var(--muted);font-weight:700}
.pkg .amt{font-size:14px;font-weight:900;color:var(--gold);white-space:nowrap}
.club-cta{margin-top:6px}
.club-cta .btn{width:100%;justify-content:center}
.empty{text-align:center;color:var(--muted);font-weight:700;padding:60px 0}
/* modal */
.overlay{position:fixed;inset:0;background:rgba(6,6,12,.6);backdrop-filter:blur(6px);display:none;align-items:center;justify-content:center;z-index:200;padding:18px}
.overlay.show{display:flex}
.sheet{background:#fff;border-radius:22px;width:100%;max-width:520px;max-height:92vh;overflow:auto;box-shadow:0 40px 90px -30px rgba(0,0,0,.5)}
.sheet-head{padding:20px 22px 14px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:flex-start;gap:12px;position:sticky;top:0;background:#fff;z-index:2}
.sheet-head h3{font-size:17px;font-weight:900}
.sheet-head p{font-size:12.5px;color:var(--muted);font-weight:700;margin-top:4px}
.x{border:none;background:rgba(0,0,0,.05);width:32px;height:32px;border-radius:50%;font-size:19px;cursor:pointer;line-height:1;color:var(--muted)}
.sheet-body{padding:18px 22px 24px;display:flex;flex-direction:column;gap:14px}
.f label{display:block;font-size:12.5px;font-weight:800;margin-bottom:7px}
.f input,.f select{width:100%;font-family:inherit;font-size:14px;padding:11px 13px;border:1px solid var(--line);border-radius:11px;background:var(--card-2);color:var(--text);font-weight:600}
.f input:focus,.f select:focus{outline:none;border-color:var(--gold);background:#fff}
.days{display:flex;gap:8px;overflow-x:auto;padding-bottom:4px}
.day{flex:0 0 auto;border:1px solid var(--line);background:var(--card-2);border-radius:12px;padding:8px 14px;font-family:inherit;font-weight:800;font-size:12.5px;cursor:pointer;text-align:center;line-height:1.35}
.day small{display:block;font-size:11px;color:var(--muted);font-weight:700;margin-top:2px}
.day.active{background:linear-gradient(135deg,var(--gold),#8b1115);color:#fff;border-color:transparent}
.day.active small{color:rgba(255,255,255,.82)}
.pkgs-pick{display:flex;flex-direction:column;gap:8px}
.pick{border:1px solid var(--line);background:var(--card-2);border-radius:13px;padding:12px 14px;text-align:right;font-family:inherit;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:10px}
.pick.active{border-color:var(--gold);background:rgba(231,29,36,.06);box-shadow:0 0 0 1px var(--gold) inset}
.pick b{font-size:13.5px;font-weight:800;display:block}
.pick small{font-size:11.5px;color:var(--muted);font-weight:700}
.total{display:flex;justify-content:space-between;align-items:center;background:var(--card-2);border-radius:13px;padding:14px 16px;font-weight:800}
.total strong{font-size:20px;color:var(--gold)}
.success{text-align:center;padding:12px 0 4px}
.success .ico{width:60px;height:60px;border-radius:50%;background:rgba(16,150,90,.12);color:#0f7a4a;display:grid;place-items:center;font-size:30px;margin:0 auto 14px}
.success h3{font-size:18px;font-weight:900;margin-bottom:8px}
.success p{font-size:13.5px;color:var(--muted);font-weight:700;line-height:1.9}
.success img{width:170px;margin:16px auto 0;display:block;border-radius:12px;border:1px solid var(--line)}
.ref{font-family:monospace;font-size:16px;font-weight:900;letter-spacing:1px;color:var(--text);margin-top:10px}
</style>
@endpush

@section('content')
<div class="container">
  <div class="page-head">
    <h1>النادي الاجتماعي</h1>
    <p>احجز جلستك في النادي الاجتماعي — اختر اليوم والباقة وسنتولى الباقي.</p>
  </div>

  @include('partials.hero-showcase', [
    'slides' => $showcase,
    'accent' => '#0f766e',
    'label' => 'مساحات للنادي',
    'heading' => 'أندية ومساحات متاحة',
  ])

  @if (session('booked_ref'))
    <div class="alert alert-success">تم تأكيد حجزك بنجاح — رقم الحجز: {{ session('booked_ref') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
  @endif

  <div class="club-grid">
    @forelse ($clubs as $club)
      <article class="club-card">
        <div class="club-visual" style="background:linear-gradient(150deg,{{ $club->colorFrom() }},{{ $club->colorTo() }})">
          @if ($club->badge)
            <span class="club-badge">{{ ['trending' => 'الأكثر طلبًا', 'new' => 'جديد', 'popular' => 'شائع'][$club->badge] ?? $club->badge }}</span>
          @endif
          @if ($club->image)
            <img src="{{ asset('storage/' . $club->image) }}" alt="{{ $club->name }}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover">
          @else
            🛋️
          @endif
        </div>
        <div class="club-body">
          <h3>{{ $club->name }}</h3>
          @if ($club->location)
            <span class="club-loc">📍 {{ $club->location }}</span>
          @endif
          @if ($club->description)
            <p class="club-desc">{{ $club->description }}</p>
          @endif

          <div class="pkgs">
            @foreach ($club->packages as $package)
              <div class="pkg">
                <div>
                  <b>{{ $package->name }}</b>
                  <small>{{ $package->start_time_short }} - {{ $package->end_time_short }} · {{ $package->people_count }} أشخاص</small>
                </div>
                <span class="amt">{{ \App\Support\Money::short((float) $package->price) }}</span>
              </div>
            @endforeach
          </div>

          <div class="club-cta">
            <button class="btn btn-gold" onclick="openClubBooking({{ $club->id }})">احجز الآن</button>
          </div>
        </div>
      </article>
    @empty
      <p class="empty">لا توجد أندية متاحة حاليًا.</p>
    @endforelse
  </div>
</div>

<div class="overlay" id="clubModal">
  <div class="sheet">
    <div class="sheet-head">
      <div>
        <h3 id="clubModalTitle">حجز النادي</h3>
        <p id="clubModalSub">—</p>
      </div>
      <button class="x" onclick="closeClubBooking()" aria-label="إغلاق">×</button>
    </div>

    <div class="sheet-body" id="clubFormBody">
      <form id="clubForm" onsubmit="submitClubBooking(event)" novalidate>
        <div class="f" style="margin-bottom:14px">
          <label>اليوم</label>
          <div class="days" id="clubDays"></div>
        </div>

        <div class="f" style="margin-bottom:14px">
          <label>الباقة</label>
          <div class="pkgs-pick" id="clubPackages"></div>
        </div>

        <div class="f" style="margin-bottom:14px">
          <label>الاسم الثلاثي</label>
          <input type="text" id="clubName" placeholder="مثال: محمد أحمد العلي" required>
        </div>

        <div class="f" style="margin-bottom:14px">
          <label>رقم الهاتف</label>
          <input type="tel" id="clubPhone" placeholder="0913504510" required dir="ltr">
        </div>

        <div class="f" style="margin-bottom:14px">
          <label>رقم الواتساب</label>
          <input type="tel" id="clubWhatsapp" placeholder="0925271793" required dir="ltr">
        </div>

        <div class="total">
          <span>المجموع</span>
          <strong id="clubTotal">0 د.ل</strong>
        </div>

        <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;margin-top:14px" id="clubSubmit">تأكيد الحجز</button>
      </form>
    </div>

    <div class="sheet-body" id="clubSuccessBody" style="display:none">
      <div class="success">
        <div class="ico">✓</div>
        <h3>تم تأكيد الحجز!</h3>
        <p id="clubSuccessText">—</p>
        <div class="ref" id="clubRef">CL-000000</div>
        <img id="clubQr" alt="رمز QR للحجز">
        <p style="margin-top:14px">أظهر رمز QR عند الوصول.</p>
        <button class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:14px" onclick="closeClubBooking()">تم</button>
      </div>
    </div>
  </div>
</div>

<script>window.CLUBS = @json($payload);</script>
@endsection

@push('scripts')
<script src="@versioned('js/hero-showcase.js')"></script>
<script src="@versioned('js/club.js')"></script>
@endpush
