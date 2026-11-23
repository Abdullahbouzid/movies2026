<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>سينما بلاس | الرئيسية</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="@versioned('css/home.css')">
</head>
<body>

<header>
  <div class="container nav">
    <a class="logo" href="/home">
      <img src="/site/logonew1.png" alt="سينما بلاس" width="150" height="50">
    </a>
    <nav class="links" id="mainNav">
      <a href="/home" class="active">الرئيسية</a>
      <a href="/movies">الأفلام</a>
      <a href="/games">صالة الألعاب</a>
      <a href="/club">النادي الاجتماعي</a>
      <a href="/cafe">حجز مناسبات</a>
    </nav>
    <div class="nav-actions">
      <a class="btn-login" href="/admin/login">تسجيل الدخول</a>
      <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="القائمة">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>
</header>

<main>

  {{-- ════════ HERO ════════ --}}
  <section class="hero container">
    <div class="hero-card">
      <div class="hero-glow"></div>

      @if ($featured)
        <div class="hero-content">
          <span class="hero-eyebrow">⭐ الأعلى تقييمًا هذا الأسبوع</span>
          <h1>{{ $featured->name }}</h1>
          <div class="hero-meta">
            <span class="star">★ {{ number_format((float) $featured->rating, 1) }}</span>
            <span class="dot">•</span>
            <span>{{ $featured->genre?->name }}</span>
            <span class="dot">•</span>
            <span>{{ $featured->duration }} دقيقة</span>
            @if ($featured->age_rating)
              <span class="dot">•</span>
              <span class="age">{{ $featured->age_rating }}</span>
            @endif
          </div>
          <p>{{ \Illuminate\Support\Str::limit($featured->description, 180) }}</p>
          <div class="hero-actions">
            <a href="/movies" class="btn btn-primary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/></svg>
              احجز تذكرتك
            </a>
            <a href="/movies/{{ $featured->id }}" class="btn btn-outline">التفاصيل والإعلان</a>
          </div>
        </div>

        <a class="hero-poster" href="/movies/{{ $featured->id }}" aria-label="{{ $featured->name }}">
          @if ($featured->image)
            <img src="{{ asset('storage/' . $featured->image) }}" alt="{{ $featured->name }}">
          @else
            <div class="hero-poster-fallback" style="background:linear-gradient(155deg,{{ $featured->posterFrom() }},{{ $featured->posterTo() }})">
              <span>{{ $featured->name }}</span>
            </div>
          @endif
        </a>
      @else
        <div class="hero-content">
          <span class="hero-eyebrow">🎬 سينما بلاس</span>
          <h1>تجربة سينما مختلفة</h1>
          <p>احجز تذكرتك في ثوانٍ، واختر مقعدك وباقتك المفضلة.</p>
          <div class="hero-actions">
            <a href="/movies" class="btn btn-primary">تصفّح الأفلام</a>
          </div>
        </div>
      @endif
    </div>
  </section>

  {{-- ════════ STATS ════════ --}}
  <section class="container stats">
    <div class="stats-bar">
      <div class="stat"><strong>{{ $stats['movies'] }}</strong><span>فيلم معروض</span></div>
      <div class="stat"><strong>{{ $stats['genres'] }}</strong><span>تصنيف سينمائي</span></div>
      <div class="stat"><strong>{{ $stats['games'] }}</strong><span>جهاز ألعاب</span></div>
      <div class="stat"><strong>{{ $stats['packages'] }}</strong><span>باقة مناسبات</span></div>
    </div>
  </section>

  {{-- ════════ NOW SHOWING ════════ --}}
  @if ($nowShowing->isNotEmpty())
    <section class="container block">
      <div class="block-head">
        <h2>يُعرض الآن</h2>
        <a class="block-link" href="/movies">كل الأفلام ←</a>
      </div>
      <div class="movie-grid">
        @foreach ($nowShowing as $movie)
          <a class="movie-card" href="/movies/{{ $movie->id }}">
            <div class="movie-poster">
              @if ($movie->image)
                <img src="{{ asset('storage/' . $movie->image) }}" alt="{{ $movie->name }}" loading="lazy">
              @else
                <div class="movie-poster-fallback" style="background:linear-gradient(155deg,{{ $movie->posterFrom() }},{{ $movie->posterTo() }})">
                  <span>{{ $movie->name }}</span>
                </div>
              @endif
              <span class="movie-rating">★ {{ number_format((float) $movie->rating, 1) }}</span>
              @if ($movie->badge)
                <span class="movie-badge badge-{{ $movie->badge }}">
                  {{ ['trending' => 'رائج', 'new' => 'جديد', 'popular' => 'مميز'][$movie->badge] ?? $movie->badge }}
                </span>
              @endif
            </div>
            <div class="movie-info">
              <h3>{{ $movie->name }}</h3>
              <p>{{ $movie->genre?->name }} • {{ $movie->duration }} دقيقة</p>
            </div>
          </a>
        @endforeach
      </div>
    </section>
  @endif

  {{-- ════════ COMING SOON ════════ --}}
  @if ($comingSoon->isNotEmpty())
    <section class="container block">
      <div class="block-head">
        <h2>قريبًا</h2>
      </div>
      <div class="soon-grid">
        @foreach ($comingSoon as $movie)
          <a class="soon-card" href="/movies/{{ $movie->id }}">
            <span class="soon-icon">{{ $movie->accent_icon ?: '🎬' }}</span>
            <div class="soon-body">
              <h3>{{ $movie->name }}</h3>
              <p>{{ $movie->genre?->name }}</p>
              @if ($movie->release_date)
                <span class="soon-date">{{ $movie->release_date->format('Y-m-d') }}</span>
              @endif
            </div>
          </a>
        @endforeach
      </div>
    </section>
  @endif

  {{-- ════════ EXPERIENCES ════════ --}}
  <section class="container block">
    <div class="block-head">
      <h2>تجارب أخرى</h2>
    </div>
    <div class="exp-grid">
      <a class="exp-card" href="/games">
        <div class="exp-visual" style="--c1:#1e1b4b;--c2:#4c1d95"><span>🎮</span></div>
        <div class="exp-body">
          <h3>صالة الألعاب</h3>
          <p>{{ $games->count() ? $games->pluck('name')->take(2)->join(' • ') : 'أحدث أجهزة الألعاب' }}</p>
          <span class="exp-link">افتح الصفحة</span>
        </div>
      </a>
      <a class="exp-card" href="/club">
        <div class="exp-visual" style="--c1:#422006;--c2:#f59e0b"><span>🎵</span></div>
        <div class="exp-body">
          <h3>النادي الاجتماعي</h3>
          <p>{{ $clubs->count() ? $clubs->pluck('name')->take(2)->join(' • ') : 'أنشطة وفعاليات' }}</p>
          <span class="exp-link">افتح الصفحة</span>
        </div>
      </a>
      <a class="exp-card" href="/cafe">
        <div class="exp-visual" style="--c1:#5c1a0f;--c2:#e71d24"><span>☕</span></div>
        <div class="exp-body">
          <h3>حجز المناسبات</h3>
          <p>{{ $eventPackages->count() ? 'باقات تبدأ من ' . \App\Support\Money::short((float) $eventPackages->min('price')) : 'احجز مناسبتك' }}</p>
          <span class="exp-link">افتح الصفحة</span>
        </div>
      </a>
    </div>
  </section>

  {{-- ════════ WHY US ════════ --}}
  <section class="container block">
    <div class="block-head"><h2>لماذا تختارنا؟</h2></div>
    <div class="why-grid">
      <div class="why-card">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M7 4v16M17 4v16M2 9h5M2 15h5M17 9h5M17 15h5"/></svg>
        </div>
        <h3>أحدث التقنيات</h3>
        <p>شاشات 4K وأنظمة صوت محيطية بأعلى المعايير.</p>
      </div>
      <div class="why-card">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h3>حجز في دقيقة</h3>
        <p>اختر الفيلم والباقة واحصل على تذكرتك برمز QR فورًا.</p>
      </div>
      <div class="why-card">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.5-1.5 3-3.5 3-6a5 5 0 0 0-10 0c0 2.5 1.5 4.5 3 6l2 2 2-2z"/></svg>
        </div>
        <h3>خدمة VIP</h3>
        <p>مقاعد فاخرة ومشروبات وفشار ضمن باقة VIP.</p>
      </div>
      <div class="why-card">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </div>
        <h3>عروض متجددة</h3>
        <p>أفلام جديدة كل أسبوع وفعاليات مستمرة.</p>
      </div>
    </div>
  </section>

</main>

<footer>
  <div class="container">
    <div class="foot-grid">
      <div class="foot-brand">
        <img src="/site/logonew1.png" alt="سينما بلاس" width="150" height="50">
        <p>الكلية الدولي للغات — ليبيا، تقدم أفضل برامج تعليم اللغات، بالإضافة إلى صالات سينما مجهزة بأحدث التقنيات.</p>
      </div>
      <div class="foot-col">
        <h5>روابط سريعة</h5>
        <a href="/movies">الأفلام المعروضة</a>
        <a href="/games">صالة الألعاب</a>
        <a href="/club">النادي الاجتماعي</a>
        <a href="/cafe">حجز المناسبات</a>
      </div>
      <div class="foot-col">
        <h5>تواصل معنا</h5>
        <a href="#">📍 ليبيا - طرابلس / زناتة</a>
        <a href="tel:+218913504510" dir="ltr">📞 0913504510</a>
        <a href="tel:+218925271793" dir="ltr">📞 0925271793</a>
        <a href="mailto:info@inter-college.ly">✉️ info@inter-college.ly</a>
      </div>
    </div>
    <div class="foot-bottom">
      <span>© 2026 الكلية الدولي للغات. جميع الحقوق محفوظة.</span>
      <span>صُمم بشغف لعشّاق السينما 🎬</span>
    </div>
  </div>
</footer>

<a class="floating-cta" href="/movies" aria-label="احجز الآن">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/></svg>
  <span class="floating-text">احجز تذكرتك</span>
</a>

<script>
  document.getElementById('mobileMenuBtn')?.addEventListener('click', () => {
    document.getElementById('mainNav')?.classList.toggle('open');
  });
</script>
</body>
</html>
