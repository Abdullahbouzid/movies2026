@php
    $badgeLabels = [
        'trending' => ['label' => '🔥 الأكثر طلبًا', 'class' => 'hot'],
        'new' => ['label' => '✨ جديد', 'class' => 'new'],
        'popular' => ['label' => '⭐ مميز', 'class' => 'popular'],
    ];
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>سينما بلاس | صالة الألعاب</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/games.css') }}">
</head>
<body data-page="games">

<header>
  <div class="container nav">
    <a class="logo" href="{{ route('site.home') }}">
      <img src="{{ asset('logonew1.png') }}" alt="سينما بلاس" width="150" height="50">
    </a>
    <nav class="links" id="mainNav">
      <a href="{{ route('site.home') }}">الرئيسية</a>
      <a href="{{ route('movies.index') }}">الأفلام</a>
      <a href="{{ route('site.games') }}" class="active">صالة الألعاب</a>
      <a href="{{ route('site.club') }}">النادي الاجتماعي</a>
      <a href="{{ route('site.cafe') }}">حجز مناسبات داخل مقهى الكلية</a>
    </nav>
    <div style="display:flex;gap:9px;align-items:center">
      <a class="btn-login" href="/admin" target="_blank" style="text-decoration:none;display:inline-flex;align-items:center">لوحة التحكم</a>
      <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="القائمة">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>
</header>

<main>

  <!-- ════════ HERO ════════ -->
  <section class="hero container">
    <div class="hero-card">
      <div class="hero-bg-anim"></div>
      <div class="hero-particles" id="heroParticles"></div>

      <div class="hero-content">
        <div class="hero-badge">🎮 ترفيه وتحدّي</div>
        <h1>صالة <em>الألعاب</em></h1>
        <div class="hero-meta">
          <span class="star">⚡ {{ $games->count() }} أجهزة</span><span class="sep">•</span>
          <span>أحدث التقنيات</span><span class="sep">•</span>
          <span>أجواء حماسية</span>
        </div>
        <p>انغمس في عالم من الترفيه والتحدي — {{ $games->take(3)->pluck('name')->join('، ') }}، وأكثر. كل ما تحتاجه لقضاء وقت لا يُنسى تحت سقف واحد.</p>
        <div class="hero-actions">
          <button class="btn btn-gold" onclick="openGameBooking()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/></svg>
            احجز جلستك
          </button>
          <a href="#devices" class="btn btn-ghost">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
            تصفّح الأجهزة
          </a>
        </div>
      </div>

      <div class="hero-poster">
        <div class="hero-poster-icon">🎮</div>
        <h4>صالة<br>الألعاب</h4>
        <span>GAMING ZONE</span>
      </div>

      <div class="scroll-indicator">
        <span>مرّر للأسفل</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
      </div>
    </div>
  </section>

  <!-- ════════ TICKER ════════ -->
  <div class="ticker">
    <div class="ticker-track" id="tickerTrack">
      @foreach ($games as $game)
        <div class="ticker-item"><span class="tick-ico">{{ $game->icon ?: '🎮' }}</span> {{ $game->name }} <span class="tick-dot"></span></div>
      @endforeach
      <div class="ticker-item"><span class="tick-ico">🏆</span> بطولات أسبوعية <span class="tick-dot"></span></div>
      <div class="ticker-item"><span class="tick-ico">⚡</span> أسعار تنافسية <span class="tick-dot"></span></div>
    </div>
  </div>

  <!-- ════════ STATS ════════ -->
  <section class="stats-section container">
    <div class="stats-bar reveal">
      <div class="stat-item">
        <div class="stat-num"><span data-count="{{ max($games->count(), 1) }}">0</span></div>
        <div class="stat-label">أجهزة متاحة</div>
      </div>
      <div class="stat-item">
        <div class="stat-num"><span data-count="500">0</span>+</div>
        <div class="stat-label">لعبة متوفرة</div>
      </div>
      <div class="stat-item">
        <div class="stat-num"><span data-count="12000">0</span>+</div>
        <div class="stat-label">لاعب سعيد</div>
      </div>
      <div class="stat-item">
        <div class="stat-num"><span data-count="24">0</span>/7</div>
        <div class="stat-label">مفتوح دائمًا</div>
      </div>
    </div>
  </section>

  <!-- ════════ GAMES / DEVICES ════════ -->
  <section class="list-section container" id="devices">
    <div class="section-head reveal">
      <h2>الأجهزة المتاحة</h2>
      <span class="count" style="font-size:13.5px;color:var(--muted);font-weight:600;background:var(--card);border:1px solid var(--line);padding:7px 16px;border-radius:100px">{{ $games->count() }} أجهزة</span>
    </div>

    <div class="extra-grid">
      @forelse ($games as $game)
        @php
            $badge = $game->badge ? ($badgeLabels[$game->badge] ?? null) : null;
            $from = $game->minPrice();
            $default = $game->defaultPrice();
            $stars = (int) floor((float) $game->rating);
        @endphp
        <div class="extra-card">
          <div class="extra-card-visual" style="--c1:{{ $game->colorFrom() }};--c2:{{ $game->colorTo() }}">
            @if ($badge)
              <span class="card-badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
            @endif
            @if ($game->image)
              <img src="{{ asset('storage/' . $game->image) }}" alt="{{ $game->name }}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover">
            @else
              <span>{{ $game->icon ?: '🎮' }}</span>
            @endif
          </div>
          <div class="extra-card-body">
            <h4>{{ $game->name }}</h4>
            <p>{{ $game->meta ?: $game->description }}</p>
            <div class="card-rating">
              <span class="stars">{{ str_repeat('★', $stars) }}{{ str_repeat('☆', max(5 - $stars, 0)) }}</span>
              <b>{{ number_format((float) $game->rating, 1) }}</b>
              <span style="opacity:.7">({{ $game->ratings_count }}+)</span>
            </div>
            <div class="extra-card-footer">
              <div class="extra-price">
                {{ $from !== null ? $from . ' ﷼' : '—' }}
                <small>{{ $default ? 'لكل ' . $default->label : '' }}</small>
              </div>
              <button class="extra-book-btn" onclick="openGameBooking({{ $game->id }})">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/></svg>
                احجز
              </button>
            </div>
          </div>
        </div>
      @empty
        <p style="text-align:center;color:var(--muted);grid-column:1/-1">لا توجد ألعاب متاحة حاليًا.</p>
      @endforelse
    </div>
  </section>

  <!-- ════════ TOURNAMENT BANNER ════════ -->
  @if ($announcement)
    <section class="tournament container">
      <div class="tournament-card reveal">
        <div class="t-content">
          <div class="t-badge">🏆 {{ $announcement->badge ?: 'بطولة' }}</div>
          <h3>{{ $announcement->title }}</h3>
          <p>{{ $announcement->body }}</p>
          <div class="t-actions">
            <button class="btn btn-gold" onclick="openGameBooking({{ $announcement->tournament?->game_id ?? 0 }})">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
              سجّل في البطولة
            </button>
          </div>
        </div>
        <div class="t-visual">🏆</div>
      </div>
    </section>
  @endif

  <!-- ════════ WHY CHOOSE US ════════ -->
  <section class="why-section container">
    <div class="section-head reveal">
      <h2>لماذا صالتنا؟</h2>
    </div>
    <div class="why-grid">
      <div class="why-card">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4M8 10v4M16 12h.01M18 12h.01"/></svg>
        </div>
        <h4>أحدث الأجهزة</h4>
        <p>{{ $games->take(2)->pluck('name')->join('، ') }} وأحدث الإصدارات</p>
      </div>
      <div class="why-card">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20"/><circle cx="12" cy="12" r="10"/></svg>
        </div>
        <h4>أجواء اجتماعية</h4>
        <p>مساحة مثالية للقاء الأصدقاء والتحدي</p>
      </div>
      <div class="why-card">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 15 8l7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z"/></svg>
        </div>
        <h4>بطولات وجوائز</h4>
        <p>بطولات أسبوعية بجوائز قيّمة للفائزين</p>
      </div>
      <div class="why-card">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h4>حجز سهل</h4>
        <p>احجز في ثوانٍ عبر الإنترنت واحصل على QR فوري</p>
      </div>
    </div>
  </section>

</main>

<footer>
  <div class="container">
    <div class="foot-grid">
      <div class="foot-brand">
        <div class="logo"><img src="{{ asset('logonew1.png') }}" alt="سينما بلاس" width="150" height="50"></div>
        <p>الكلية الدولي للغات - ليبيا، تقدم أفضل برامج تعليم اللغات بأعلى المعايير التعليمية بأيدي مدرسين محترفين، بالإضافة إلى صالات سينما مجهزة بأحدث التقنيات.</p>
      </div>
      <div class="foot-col"><h5>روابط سريعة</h5><a href="{{ route('movies.index') }}">الأفلام المعروضة</a><a href="{{ route('site.games') }}">صالة الألعاب</a><a href="{{ route('site.club') }}">النادي الاجتماعي</a><a href="{{ route('site.cafe') }}">حجز المناسبات</a></div>
      <div class="foot-col"><h5>الدعم</h5><a href="#">الأسئلة الشائعة</a><a href="#">سياسة الاسترجاع</a><a href="#">اتصل بنا</a></div>
      <div class="foot-col"><h5>تواصل معنا</h5><a href="#">📍 ليبيا - طرابلس / زناتة</a><a href="#">📞 0913504510</a><a href="#">📞 0925271793</a><a href="#">✉️ info@inter-college.ly</a></div>
    </div>
    <div class="foot-bottom"><span>© 2026 الكلية الدولي للغات. جميع الحقوق محفوظة.</span><span>صُمم بشغف لعشّاق السينما 🎬</span></div>
  </div>
</footer>

<!-- زر عائم للحجز -->
<button class="floating-cta" onclick="openGameBooking()" aria-label="احجز الآن">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4M8 10v4M16 12h.01M18 12h.01"/></svg>
  <span class="floating-text">احجز جلستك</span>
</button>

<!-- ════════ نافذة حجز اللعبة ════════ -->
<div class="modal-overlay" id="gameBookingModal">
  <div class="modal">
    <div class="modal-header">
      <button class="modal-close" onclick="closeGameBooking()" aria-label="إغلاق">×</button>
      <div class="modal-icon" id="modalIcon">🎮</div>
      <div class="modal-title-wrap">
        <h3 id="modalTitle">—</h3>
        <p class="modal-meta" id="modalMeta">—</p>
        <span class="modal-day-badge" id="modalDay">—</span>
      </div>
    </div>

    <div class="modal-body" id="modalBody">
      <form id="gameBookingForm" onsubmit="submitGameBooking(event)" novalidate>

        <div class="field-modal device-field">
          <label>اختر الجهاز <span class="req">*</span></label>
          <select id="modalDevice" onchange="onDeviceChange()" required></select>
        </div>

        <div class="field-modal">
          <label>مدة الجلسة <span class="req">*</span></label>
          <div class="duration-grid" id="durationGrid"></div>
        </div>

        <div class="field-modal">
          <label>الاسم الثلاثي <span class="req">*</span></label>
          <input type="text" id="fullName" placeholder="مثال: محمد أحمد العلي" required>
        </div>

        <div class="field-modal">
          <label>رقم الهاتف <span class="req">*</span></label>
          <div class="phone-input">
            <div class="country-select" id="phoneCountrySelect">
              <button type="button" class="country-btn" onclick="toggleCountryDropdown('phoneCountrySelect')">
                <span class="flag">🇱🇾</span><span class="code-text">{{ $defaultCountry }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
              </button>
              <div class="country-dropdown"><div class="country-search-wrap"><input type="text" class="country-search" placeholder="ابحث بالاسم أو الرمز" oninput="filterCountryList(this, 'phoneCountryList')"></div><div class="country-list" id="phoneCountryList"></div></div>
            </div>
            <input type="tel" id="phoneNumber" placeholder="91 345 6789" required>
          </div>
        </div>

        <div class="field-modal">
          <label>رقم الواتساب <span class="req">*</span></label>
          <div class="phone-input">
            <div class="country-select" id="whatsappCountrySelect">
              <button type="button" class="country-btn" onclick="toggleCountryDropdown('whatsappCountrySelect')">
                <span class="flag">🇱🇾</span><span class="code-text">{{ $defaultCountry }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
              </button>
              <div class="country-dropdown"><div class="country-search-wrap"><input type="text" class="country-search" placeholder="ابحث بالاسم أو الرمز" oninput="filterCountryList(this, 'whatsappCountryList')"></div><div class="country-list" id="whatsappCountryList"></div></div>
            </div>
            <input type="tel" id="whatsappNumber" placeholder="91 345 6789" required>
          </div>
        </div>

        <div class="total-row">
          <span class="total-label">المجموع الكلي<small id="totalSubLabel">—</small></span>
          <strong id="totalPrice">0 <small>﷼</small></strong>
        </div>

        <button type="submit" class="btn btn-gold submit-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          تأكيد الحجز
        </button>
      </form>
    </div>

    <div class="modal-body" id="successBody" style="display:none">
      <div class="success-state">
        <div class="success-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
        <h3>تم تأكيد الحجز بنجاح!</h3>
        <p id="successMessage">سيتم إرسال تفاصيل الحجز إلى رقم الواتساب المُدخل.</p>
        <div class="qr-ticket">
          <div class="qr-ticket-header"><span>رقم الحجز</span><strong id="bookingRef">GM-000000</strong></div>
          <div class="qr-ticket-body"><img id="qrImage" alt="رمز QR" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='200' height='200'><rect width='200' height='200' fill='%23f4f4f6'/></svg>"></div>
          <div class="qr-ticket-footer">
            <div class="screenshot-note">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
              <div class="screenshot-note-text"><strong>⚠️ يجب عليك أخذ لقطة شاشة</strong><span>احفظ هذا الحجز على هاتفك وأظهر رمز QR عند الدخول.</span></div>
            </div>
          </div>
        </div>
        <button type="button" class="btn btn-gold" onclick="closeGameBooking()">تم، حفظت الحجز</button>
      </div>
    </div>
  </div>
</div>

<script>window.GAMES = @json($payload);</script>
<script src="{{ asset('js/games.js') }}"></script>
</body>
</html>
