<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>سينما بلاس | الأفلام</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/movies.css') }}">
</head>
<body data-page="movies">

<header>
  <div class="container nav">
    <a class="logo" href="{{ route('site.home') }}">
      <img src="{{ asset('logonew1.png') }}" alt="سينما بلاس" width="150" height="50">
    </a>
    <nav class="links" id="mainNav">
      <a href="{{ route('site.home') }}">الرئيسية</a>
      <a href="{{ route('movies.index') }}" class="active">الأفلام</a>
      <a href="{{ route('site.games') }}">صالة الألعاب</a>
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
        <div class="hero-badge">🎬 عروض الأسبوع</div>
        <h1>جميع <em>الأفلام</em> المعروضة</h1>
        <div class="hero-meta">
          <span class="star">🎞️ {{ $movies->count() }} أفلام</span><span class="sep">•</span>
          <span>7 أيام عرض</span><span class="sep">•</span>
          <span>+{{ config('cinema.halls') }} قاعات</span>
        </div>
        <p>اختر اليوم والتصنيف، تصفّح أوقات العرض، واحجز تذكرتك بضغطة واحدة. تجربة سينمائية استثنائية بأحدث الشاشات وأنظمة الصوت.</p>
        <div class="hero-actions">
          <button class="btn btn-gold" onclick="openBooking()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/></svg>
            احجز تذكرتك
          </button>
          <a href="#movie-list" class="btn btn-ghost">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
            تصفّح الأفلام
          </a>
        </div>
      </div>

      <div class="hero-poster">
        <div class="hero-poster-icon">🎬</div>
        <h4>سينما<br>بلاس</h4>
        <span>NOW SHOWING</span>
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
      @foreach ($movies as $movie)
        <div class="ticker-item"><span class="tick-ico">🎬</span> {{ $movie->name }} <span class="tick-dot"></span></div>
      @endforeach
    </div>
  </div>

  <!-- ════════ STATS ════════ -->
  <section class="stats-section container">
    <div class="stats-bar reveal">
      <div class="stat-item">
        <div class="stat-num"><span data-count="{{ $movies->count() }}">0</span></div>
        <div class="stat-label">أفلام معروضة</div>
      </div>
      <div class="stat-item">
        <div class="stat-num"><span data-count="{{ $weeklyShowings }}">0</span>+</div>
        <div class="stat-label">عرض أسبوعي</div>
      </div>
      <div class="stat-item">
        <div class="stat-num"><span data-count="{{ config('cinema.halls') }}">0</span></div>
        <div class="stat-label">قاعات مجهزة</div>
      </div>
      <div class="stat-item">
        <div class="stat-num"><span data-count="{{ config('cinema.satisfaction') }}">0</span>%</div>
        <div class="stat-label">رضا الزوار</div>
      </div>
    </div>
  </section>

  <!-- ════════ CONTROLS ════════ -->
  <section class="controls container" id="movie-list">
    <div class="days" id="days"></div>
    <div class="genres" id="genres"></div>
  </section>

  <!-- ════════ MOVIES ════════ -->
  <section class="movies-section container">
    <div class="section-head">
      <h2>قائمة الأفلام</h2>
      <span class="count" id="count">0 فيلم</span>
    </div>
    <div class="grid" id="grid"></div>
  </section>

  <!-- ════════ COMING SOON ════════ -->
  <section class="coming-section">
    <div class="container">
      <div class="section-head reveal">
        <h2>قريبًا في السينما</h2>
        <span class="count">{{ $comingSoon->count() }} أفلام</span>
      </div>
      <div class="coming-grid">
        @foreach ($comingSoon as $movie)
          <div class="coming-card">
            <div class="coming-visual" style="--c1:{{ $movie->posterFrom() }};--c2:{{ $movie->posterTo() }}">
              <span>{{ $movie->accent_icon ?: '🎬' }}</span>
            </div>
            <div class="coming-body">
              <div class="coming-date">📅 {{ $movie->release_date?->translatedFormat('d F Y') ?? '—' }}</div>
              <h4>{{ $movie->name }}</h4>
              <p>{{ $movie->description }}</p>
              <div class="coming-meta">
                <span>⏱ {{ $movie->duration }} د</span>
                <span>🎭 {{ $movie->genre->name }}</span>
                @if ($movie->age_rating)<span>{{ $movie->age_rating }}</span>@endif
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- ════════ WHY US ════════ -->
  <section class="why-section container">
    <div class="section-head reveal">
      <h2>لماذا تختار سينما بلاس؟</h2>
    </div>
    <div class="why-grid">
      <div class="why-card">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M7 4v16M17 4v16M2 9h5M2 15h5M17 9h5M17 15h5"/></svg>
        </div>
        <h4>شاشات 4K</h4>
        <p>أحدث تقنيات العرض بدقة 4K مع ألوان واقعية</p>
      </div>
      <div class="why-card">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/></svg>
        </div>
        <h4>صوت محيطي</h4>
        <p>أنظمة صوت Dolby Atmos لتجربة سينمائية كاملة</p>
      </div>
      <div class="why-card">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h4>حجز فوري</h4>
        <p>احجز في أقل من دقيقة واحصل على QR مباشرة</p>
      </div>
      <div class="why-card">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.5-1.5 3-3.5 3-6a5 5 0 0 0-10 0c0 2.5 1.5 4.5 3 6l2 2 2-2z"/></svg>
        </div>
        <h4>تجربة VIP</h4>
        <p>مقاعد فاخرة ومشروبات وفشار مجانًا</p>
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

<!-- زر عائم -->
<button class="floating-cta" onclick="openBooking()" aria-label="احجز الآن">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/></svg>
  <span class="floating-text">احجز تذكرتك</span>
</button>

<!-- ════════ نافذة الحجز ════════ -->
<div class="modal-overlay" id="bookingModal">
  <div class="modal">
    <div class="modal-header">
      <button class="modal-close" onclick="closeBooking()" aria-label="إغلاق">×</button>
      <div class="modal-poster" id="modalPoster"></div>
      <div class="modal-title-wrap">
        <h3 id="modalTitle">—</h3>
        <p class="modal-meta" id="modalMeta">—</p>
        <span class="modal-day-badge" id="modalDay">—</span>
      </div>
    </div>

    <div class="modal-body" id="modalBody">
      <div class="ticket-tabs" id="ticketTabs"></div>

      <div class="vip-perks" id="vipPerks">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        <span>تذكرة VIP تشمل المشروبات والفشار مجانًا 🍿🥤</span>
      </div>

      <form id="bookingForm" onsubmit="submitBooking(event)" novalidate>

        <div class="field-modal movie-field">
          <label>اختر الفيلم <span class="req">*</span></label>
          <select id="modalMovie" onchange="onMovieChange()" required></select>
        </div>

        <div class="field-modal">
          <label>وقت العرض <span class="req">*</span></label>
          <select id="modalShowtime" required></select>
        </div>

        <div class="field-modal">
          <label>الاسم الثلاثي <span class="req">*</span></label>
          <input type="text" id="fullName" placeholder="مثال: محمد أحمد العلي" required>
        </div>

        <div class="field-modal">
          <label>تاريخ الميلاد <span class="req">*</span></label>
          <input type="date" id="birthdate" required>
        </div>

        <div class="field-modal">
          <label>رقم الهاتف <span class="req">*</span></label>
          <div class="phone-input">
            <div class="country-select" id="phoneCountrySelect">
              <button type="button" class="country-btn" onclick="toggleCountryDropdown('phoneCountrySelect')">
                <span class="flag">🇱🇾</span><span class="code-text">{{ config('cinema.default_country') }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
              </button>
              <div class="country-dropdown"><div class="country-search-wrap"><input type="text" class="country-search" placeholder="ابحث بالاسم أو الرمز +218" oninput="filterCountryList(this, 'phoneCountryList')"></div><div class="country-list" id="phoneCountryList"></div></div>
            </div>
            <input type="tel" id="phoneNumber" placeholder="91 345 6789" required>
          </div>
        </div>

        <div class="field-modal">
          <label>رقم الواتساب <span class="req">*</span></label>
          <div class="phone-input">
            <div class="country-select" id="whatsappCountrySelect">
              <button type="button" class="country-btn" onclick="toggleCountryDropdown('whatsappCountrySelect')">
                <span class="flag">🇱🇾</span><span class="code-text">{{ config('cinema.default_country') }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
              </button>
              <div class="country-dropdown"><div class="country-search-wrap"><input type="text" class="country-search" placeholder="ابحث بالاسم أو الرمز +218" oninput="filterCountryList(this, 'whatsappCountryList')"></div><div class="country-list" id="whatsappCountryList"></div></div>
            </div>
            <input type="tel" id="whatsappNumber" placeholder="91 345 6789" required>
          </div>
        </div>

        <div class="total-row">
          <span class="total-label">المجموع الكلي<small id="totalSubLabel"></small></span>
          <strong id="totalPrice">— <small>﷼</small></strong>
        </div>

        <button type="submit" class="btn btn-gold submit-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          تأكيد الحجز
        </button>
      </form>
    </div>

    <!-- حالة النجاح -->
    <div class="modal-body" id="successBody" style="display:none">
      <div class="success-state">
        <div class="success-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
        <h3>تم تأكيد الحجز بنجاح!</h3>
        <p id="successMessage">سيتم إرسال تفاصيل التذكرة إلى رقم الواتساب المُدخل.</p>
        <div class="qr-ticket">
          <div class="qr-ticket-header"><span>رقم الحجز</span><strong id="bookingRef">CP-000000</strong></div>
          <div class="qr-ticket-body"><img id="qrImage" alt="رمز QR" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='200' height='200'><rect width='200' height='200' fill='%23f4f4f6'/></svg>"></div>
          <div class="qr-ticket-footer">
            <div class="screenshot-note">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
              <div class="screenshot-note-text"><strong>⚠️ يجب عليك أخذ لقطة شاشة</strong><span>احفظ هذه التذكرة على هاتفك وأظهر رمز QR عند بوابة الدخول. لن تتمكن من الوصول للتذكرة بعد إغلاق هذه النافذة.</span></div>
            </div>
          </div>
        </div>
        <a class="btn btn-ghost" id="ticketLink" href="#" style="width:100%;justify-content:center;margin-bottom:10px">عرض التذكرة / طباعة</a>
        <button type="button" class="btn btn-gold" onclick="closeBooking()">تم، حفظت التذكرة</button>
      </div>
    </div>
  </div>
</div>

<script>
window.CINEMA = @json($payload);
</script>
<script src="{{ asset('js/movies.js') }}"></script>
</body>
</html>
