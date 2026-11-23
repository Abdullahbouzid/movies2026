/* ═══════════════════════════════════════════════════
   سينما بلاس — واجهة الأفلام (مبنية على بيانات Laravel)
   البيانات تُمرَّر عبر window.CINEMA من القالب.
   ═══════════════════════════════════════════════════ */
const CINEMA = window.CINEMA || {};

const MOVIES = CINEMA.movies || [];
const PACKAGES = CINEMA.packages || [];
const COUNTRIES = CINEMA.countries || [];
const WEEK_DAYS = CINEMA.weekDays || [];
const DAY_NAMES = CINEMA.dayNames || [];

const BOOK_URL_TEMPLATE = CINEMA.bookUrl || '';
const CSRF_TOKEN = CINEMA.csrf || '';
const DEFAULT_MOVIE_ID = CINEMA.defaultMovieId || (MOVIES[0] ? MOVIES[0].id : null);

/* ═══════════════════════════════════════════════════
   أدوات مساعدة
   ═══════════════════════════════════════════════════ */
function starsForRating(r) {
  const full = Math.floor(r / 2);
  const half = (r / 2 - full) >= 0.5 ? 1 : 0;
  return '★'.repeat(full) + (half ? '½' : '') + '☆'.repeat(5 - full - half);
}
function money(n) {
  const v = Number(n);
  return Number.isInteger(v) ? v : v.toFixed(2);
}
function showtimesFor(movie, dayIndex) {
  const byDay = (movie.times && movie.times[dayIndex]) || [];
  return byDay;
}
function firstDayWithShowtimes(movie) {
  for (let i = 0; i < WEEK_DAYS.length; i++) {
    if (showtimesFor(movie, i).length) return i;
  }
  return currentDay;
}

/* ═══════════════════════════════════════════════════
   الحالة
   ═══════════════════════════════════════════════════ */
let currentDay = CINEMA.currentDay ?? 0;
if (MOVIES.length && !MOVIES.some(m => showtimesFor(m, currentDay).length)) {
  for (let i = 0; i < WEEK_DAYS.length; i++) {
    if (MOVIES.some(m => showtimesFor(m, i).length)) { currentDay = i; break; }
  }
}
let currentGenre = "الكل";
let currentBooking = { movie: null, showtimes: [], selectedShowtimeId: null, packageId: null, step: 1 };
const selectedCountries = {
  phoneCountrySelect: { code: CINEMA.defaultCountry || '+218', flag: '🇱🇾', name: 'ليبيا' },
  whatsappCountrySelect: { code: CINEMA.defaultCountry || '+218', flag: '🇱🇾', name: 'ليبيا' }
};

/* ═══════════════════════════════════════════════════
   عناصر DOM
   ═══════════════════════════════════════════════════ */
const daysEl = document.getElementById("days");
const genresEl = document.getElementById("genres");
const gridEl = document.getElementById("grid");
const countEl = document.getElementById("count");

/* ═══════════════════════════════════════════════════
   الأيام
   ═══════════════════════════════════════════════════ */
function renderDays() {
  daysEl.innerHTML = WEEK_DAYS.map((d, i) => `
    <button class="day-pill ${i === currentDay ? "active" : ""}" data-day="${i}">
      ${d.isToday ? '<span class="today-dot"></span>' : ""}
      <span class="d-name">${d.name}</span>
      <span class="d-date">${d.date}</span>
    </button>
  `).join("");
  daysEl.querySelectorAll(".day-pill").forEach(btn => {
    btn.addEventListener("click", () => {
      currentDay = Number(btn.dataset.day);
      renderDays();
      renderMovies();
    });
  });
}

/* ═══════════════════════════════════════════════════
   التصنيفات
   ═══════════════════════════════════════════════════ */
function renderGenres() {
  const genres = ["الكل", ...new Set(MOVIES.map(m => m.genre))];
  genresEl.innerHTML = genres.map(g => `
    <button class="chip ${g === currentGenre ? "active" : ""}" data-genre="${g}">${g}</button>
  `).join("");
  genresEl.querySelectorAll(".chip").forEach(btn => {
    btn.addEventListener("click", () => {
      currentGenre = btn.dataset.genre;
      renderGenres();
      renderMovies();
    });
  });
}

/* ═══════════════════════════════════════════════════
   بطاقة الفيلم
   ═══════════════════════════════════════════════════ */
function badgeHTML(badge) {
  if (badge === 'trending') return '<span class="badge trending">🔥 رائج</span>';
  if (badge === 'new') return '<span class="badge new-tag">✨ جديد</span>';
  if (badge === 'popular') return '<span class="badge popular">⭐ مميز</span>';
  return '';
}
function posterStyle(m) {
  const gradient = `linear-gradient(155deg,${m.g1},${m.g2})`;
  return m.poster
    ? `--g1:${m.g1};--g2:${m.g2};background-image:url('${m.poster}'),${gradient};background-size:cover;background-position:center`
    : `--g1:${m.g1};--g2:${m.g2};background-image:${gradient}`;
}

function renderMovies() {
  const list = MOVIES.filter(m => currentGenre === "الكل" || m.genre === currentGenre);
  countEl.textContent = `${list.length} ${list.length === 1 ? "فيلم" : "أفلام"}`;

  if (!list.length) {
    gridEl.innerHTML = `<div class="empty">لا توجد أفلام مطابقة لهذا التصنيف حاليًا 🎬</div>`;
    return;
  }

  gridEl.innerHTML = list.map((m, i) => {
    const times = showtimesFor(m, currentDay);
    const first = times.length ? times[0] : null;
    return `
      <article class="movie-card" style="animation-delay:${i * 55}ms">
        <div class="poster" style="${posterStyle(m)}">
          <div class="texture"></div>
          ${badgeHTML(m.badge)}
          <span class="badge rating">★ ${m.rating}</span>
          <span class="badge cert">${m.cert || ''}</span>
          <div class="play-float">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
          </div>
          <div class="poster-bottom">
            <h3>${m.title}</h3>
            <span>${(m.genre || '').toUpperCase()}</span>
          </div>
        </div>

        <div class="card-body">
          <div class="meta">
            <span>⏱ ${m.duration} دقيقة</span>
            <span class="dot"></span>
            <span>${m.genre}</span>
          </div>

          <div class="card-rating">
            <span class="stars">${starsForRating(m.rating)}</span>
            <b>${m.rating}</b>
            <span class="op">(${m.ratingsCount}+)</span>
          </div>

          <div class="showtimes-label">أوقات العرض — ${WEEK_DAYS[currentDay].name}</div>
          <div class="showtimes">
            ${times.length
              ? times.map(t => {
                  const full = typeof t.seatsLeft === 'number' && t.seatsLeft <= 0;
                  return `<button class="time${full ? ' time-full' : ''}" data-movie="${m.id}" data-showtime="${t.id}"
                            ${full ? 'disabled title="اكتمل العدد"' : `title="${t.seatsLeft} مقعد متاح"`}>${t.time}${full ? ' ✕' : ''}</button>`;
                }).join("")
              : '<span class="no-times">لا توجد أوقات عرض في هذا اليوم</span>'}
          </div>

          <div class="card-footer">
            <div class="price-tag">
              <span class="price-label">السعر من</span>
              <strong>${money(m.minPrice)} <small>د.ل</small></strong>
            </div>
            <button class="btn-book" data-movie="${m.id}" data-showtime="${first ? first.id : ''}">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/>
              </svg>
              <span>احجز</span>
            </button>
          </div>
        </div>
      </article>
    `;
  }).join("");

  gridEl.querySelectorAll('.time, .btn-book').forEach(el => {
    el.addEventListener('click', () => openBooking(Number(el.dataset.movie), el.dataset.showtime ? Number(el.dataset.showtime) : null));
  });
}

/* ═══════════════════════════════════════════════════
   تبويبات الباقات (سعر كل باقة لهذا الفيلم)
   ═══════════════════════════════════════════════════ */
function renderTicketTabs(movie) {
  const box = document.getElementById('ticketTabs');
  const available = PACKAGES.filter(p => movie.prices[p.id] !== null && movie.prices[p.id] !== undefined);
  box.innerHTML = available.map((p, idx) => `
    <button type="button" class="ticket-tab ${idx === 0 ? 'active' : ''}" data-package="${p.id}">
      ${p.isVip ? '<span class="tab-badge">VIP</span>' : ''}
      <span class="tab-label">${p.name}</span>
      <span class="tab-price">${money(movie.prices[p.id])} <small>د.ل</small></span>
    </button>
  `).join("");
  box.querySelectorAll('.ticket-tab').forEach(tab => {
    tab.addEventListener('click', () => selectPackage(Number(tab.dataset.package)));
  });
  currentBooking.packageId = available.length ? available[0].id : null;
  renderVipPerks();
}

function renderVipPerks() {
  const pkg = PACKAGES.find(p => p.id === currentBooking.packageId);
  const perks = document.getElementById('vipPerks');
  if (pkg && pkg.isVip) {
    perks.querySelector('span').textContent = pkg.includes || 'باقة VIP تشمل المشروبات والفشار مجانًا 🍿🥤';
    perks.classList.add('show');
  } else {
    perks.classList.remove('show');
  }
}

function selectPackage(packageId) {
  currentBooking.packageId = packageId;
  document.querySelectorAll('.ticket-tab').forEach(t =>
    t.classList.toggle('active', Number(t.dataset.package) === packageId));
  renderVipPerks();
  updateTotal();
}

function updateTotal() {
  const movie = currentBooking.movie;
  if (!movie) return;
  const price = movie.prices[currentBooking.packageId];
  const pkg = PACKAGES.find(p => p.id === currentBooking.packageId);
  document.getElementById('totalPrice').innerHTML = `${money(price)} <small>د.ل</small>`;
  document.getElementById('totalSubLabel').textContent = pkg ? pkg.name : '';
}

/* ═══════════════════════════════════════════════════
   تطبيق تفاصيل الفيلم على المودال
   ═══════════════════════════════════════════════════ */
function applyMovieToModal(movie, preselectedShowtimeId) {
  currentBooking.movie = movie;
  const dayIndex = showtimesFor(movie, currentDay).length ? currentDay : firstDayWithShowtimes(movie);
  const times = showtimesFor(movie, dayIndex);
  currentBooking.dayIndex = dayIndex;
  currentBooking.showtimes = times;
  const ids = times.map(t => t.id);
  currentBooking.selectedShowtimeId = ids.includes(preselectedShowtimeId) ? preselectedShowtimeId : ids[0];

  const day = WEEK_DAYS[dayIndex];
  document.getElementById('modalDay').textContent = `${day.name} — ${day.date}`;

  document.getElementById('modalTitle').textContent = movie.title;
  document.getElementById('modalMeta').textContent =
    `${movie.genre} • ★${movie.rating} • ${movie.duration} دقيقة${movie.cert ? ' • ' + movie.cert : ''}`;
  const modalPosterEl = document.getElementById('modalPoster');
  modalPosterEl.style.backgroundImage = movie.poster
    ? `url('${movie.poster}'), linear-gradient(155deg, ${movie.g1}, ${movie.g2})`
    : `linear-gradient(155deg, ${movie.g1}, ${movie.g2})`;
  modalPosterEl.style.backgroundSize = 'cover';
  modalPosterEl.style.backgroundPosition = 'center';

  const sel = document.getElementById('modalShowtime');
  sel.innerHTML = currentBooking.showtimes.map(t => {
    const full = typeof t.seatsLeft === 'number' && t.seatsLeft <= 0;
    const label = typeof t.seatsLeft === 'number' ? `${t.time} — ${full ? 'مكتمل' : t.seatsLeft + ' مقعد'}` : t.time;
    return `<option value="${t.id}" ${t.id === currentBooking.selectedShowtimeId ? 'selected' : ''} ${full ? 'disabled' : ''}>${label}</option>`;
  }).join('');

  renderTicketTabs(movie);
  updateTotal();

  const poster = document.getElementById('modalPoster');
  poster.classList.remove('pulse');
  void poster.offsetWidth;
  poster.classList.add('pulse');
}

function onMovieChange() {
  const id = Number(document.getElementById('modalMovie').value);
  const movie = MOVIES.find(m => m.id === id);
  if (!movie) return;
  applyMovieToModal(movie);
}

/* ═══════════════════════════════════════════════════
   فتح/إغلاق المودال
   ═══════════════════════════════════════════════════ */
function openBooking(movieId, preselectedShowtimeId) {
  const modal = document.getElementById('bookingModal');
  if (!modal || !MOVIES.length) return;

  const movieSelect = document.getElementById('modalMovie');
  movieSelect.innerHTML = MOVIES.map(m =>
    `<option value="${m.id}">${m.title} — ${m.genre} (★${m.rating})</option>`
  ).join('');

  document.getElementById('bookingForm').reset();

  let movie = movieId ? MOVIES.find(m => m.id === movieId) : null;
  if (!movie) movie = MOVIES.find(m => m.id === DEFAULT_MOVIE_ID) || MOVIES[0];
  movieSelect.value = movie.id;

  applyMovieToModal(movie, preselectedShowtimeId);

  document.getElementById('modalBody').style.display = 'block';
  document.getElementById('successBody').style.display = 'none';
  goToStep(1);
  modal.classList.add('show');
  document.body.style.overflow = 'hidden';
  document.querySelectorAll('.country-select.open').forEach(el => el.classList.remove('open'));
}

function closeBooking() {
  const modal = document.getElementById('bookingModal');
  if (!modal) return;
  modal.classList.remove('show');
  document.body.style.overflow = '';
  document.querySelectorAll('.country-select.open').forEach(el => el.classList.remove('open'));
}

/* ═══════════════════════════════════════════════════
   قائمة الدول
   ═══════════════════════════════════════════════════ */
function renderCountryList(listId, containerId, q = '') {
  const listEl = document.getElementById(listId);
  if (!listEl) return;
  const qq = q.trim();
  const cleanQ = qq.replace(/^\+/, '');
  const filtered = COUNTRIES.filter(c => {
    if (!qq) return true;
    return c.name.includes(qq)
      || (c.name_en && c.name_en.toLowerCase().includes(qq.toLowerCase()))
      || c.code.replace('+', '').includes(cleanQ);
  });
  if (!filtered.length) { listEl.innerHTML = `<div class="country-empty">لا توجد نتائج مطابقة</div>`; return; }
  const sel = selectedCountries[containerId];
  listEl.innerHTML = filtered.map(c => `
    <div class="country-item ${c.code === sel.code ? 'selected' : ''}" data-container="${containerId}" data-code="${c.code}" data-flag="${c.flag}" data-name="${c.name}">
      <span class="flag">${c.flag}</span><span class="cname">${c.name}</span><span class="ccode">${c.code}</span>
    </div>`).join('');
  listEl.querySelectorAll('.country-item').forEach(item => {
    item.addEventListener('click', () => selectCountry(item.dataset.container, item.dataset.code, item.dataset.flag, item.dataset.name));
  });
}
function selectCountry(containerId, code, flag, name) {
  selectedCountries[containerId] = { code, flag, name };
  const btn = document.querySelector(`#${containerId} .country-btn`);
  if (btn) { btn.querySelector('.flag').textContent = flag; btn.querySelector('.code-text').textContent = code; }
  document.getElementById(containerId).classList.remove('open');
  const s = document.querySelector(`#${containerId} .country-search`);
  if (s) s.value = '';
}
function toggleCountryDropdown(containerId) {
  const c = document.getElementById(containerId);
  if (!c) return;
  const wasOpen = c.classList.contains('open');
  document.querySelectorAll('.country-select.open').forEach(el => el.classList.remove('open'));
  if (!wasOpen) {
    c.classList.add('open');
    const s = c.querySelector('.country-search');
    if (s) { s.value = ''; setTimeout(() => s.focus(), 50); }
    const listId = containerId === 'phoneCountrySelect' ? 'phoneCountryList' : 'whatsappCountryList';
    renderCountryList(listId, containerId);
  }
}
function filterCountryList(input, listId) {
  const c = input.closest('.country-select');
  renderCountryList(listId, c.id, input.value);
}

/* ═══════════════════════════════════════════════════
   تأكيد الحجز (يُرسل إلى Laravel)
   ═══════════════════════════════════════════════════ */
/* ═══════════════════════════════════════════════════
   معالج الحجز — التنقّل بين الخطوات
   ═══════════════════════════════════════════════════ */
function goToStep(step) {
  currentBooking.step = step;

  document.querySelectorAll('.step-panel').forEach(panel =>
    panel.classList.toggle('is-active', Number(panel.dataset.panel) === step));

  document.querySelectorAll('#bookingSteps .step').forEach(el => {
    const n = Number(el.dataset.step);
    el.classList.toggle('is-active', n === step);
    el.classList.toggle('is-done', n < step);
  });

  const body = document.getElementById('modalBody');
  if (body) body.scrollTop = 0;

  if (step === 3) renderReview();
}

/** Validates only the fields of the step being left, so errors appear in place. */
function validateStep(step) {
  if (step === 1) {
    if (!currentBooking.movie) return 'يرجى اختيار الفيلم';
    if (!document.getElementById('modalShowtime').value) return 'يرجى اختيار وقت العرض';
    if (!currentBooking.packageId) return 'يرجى اختيار نوع التذكرة';
    return null;
  }

  if (step === 2) {
    const fullName = document.getElementById('fullName').value.trim();
    const birthdate = document.getElementById('birthdate').value;
    const phone = document.getElementById('phoneNumber').value.trim();
    const whatsapp = document.getElementById('whatsappNumber').value.trim();

    if (fullName.split(/\s+/).filter(Boolean).length < 3) return 'يرجى إدخال الاسم الثلاثي كاملاً';
    if (!birthdate) return 'يرجى إدخال تاريخ الميلاد';
    if (phone.replace(/\D/g, '').length < 6) return 'يرجى إدخال رقم هاتف صحيح';
    if (whatsapp.replace(/\D/g, '').length < 6) return 'يرجى إدخال رقم واتساب صحيح';
    return null;
  }

  return null;
}

function copyPhoneToWhatsapp(cb) {
  const phone = document.getElementById('phoneNumber');
  const wa = document.getElementById('whatsappNumber');
  if (!cb.checked) return;
  wa.value = phone.value;
  selectedCountries.whatsappCountrySelect = { ...selectedCountries.phoneCountrySelect };
  const btn = document.querySelector('#whatsappCountrySelect .country-btn');
  if (btn) {
    btn.querySelector('.flag').textContent = selectedCountries.phoneCountrySelect.flag || '🇱🇾';
    btn.querySelector('.code-text').textContent = selectedCountries.phoneCountrySelect.code;
  }
}

function renderReview() {
  const movie = currentBooking.movie;
  const pkg = PACKAGES.find(p => p.id === currentBooking.packageId);
  const showtimeId = document.getElementById('modalShowtime').value;
  const time = (currentBooking.showtimes || []).find(t => String(t.id) === String(showtimeId));
  const day = WEEK_DAYS[currentBooking.dayIndex ?? currentDay];
  const price = movie ? movie.prices[currentBooking.packageId] : null;
  const phone = document.getElementById('phoneNumber').value.trim();
  const code = selectedCountries.phoneCountrySelect.code || '';

  const rows = [
    ['الفيلم', movie ? movie.title : '—'],
    ['اليوم', `${day.name} — ${day.date}`],
    ['وقت العرض', time ? time.time : '—'],
    ['نوع التذكرة', pkg ? pkg.name : '—'],
    ['الاسم', document.getElementById('fullName').value.trim() || '—'],
    ['الهاتف', phone ? `${code} ${phone}` : '—'],
  ];

  document.getElementById('reviewCard').innerHTML = rows.map(([label, value]) => `
    <div class="review-row">
      <span class="rv-label">${label}</span>
      <span class="rv-value">${value}</span>
    </div>
  `).join("") + `
    <div class="review-row rv-highlight">
      <span class="rv-label">الإجمالي</span>
      <span class="rv-value">${money(price)} د.ل</span>
    </div>
  `;
}

document.addEventListener('click', (e) => {
  const btn = e.target.closest('.step-next, .step-back');
  if (!btn) return;
  const target = Number(btn.dataset.goto);
  const current = currentBooking.step || 1;

  if (target > current) {
    const error = validateStep(current);
    if (error) { alert(error); return; }
  }
  goToStep(target);
});

async function submitBooking(e) {
  e.preventDefault();
  const movie = currentBooking.movie;
  const fullName = document.getElementById('fullName').value.trim();
  const birthdate = document.getElementById('birthdate').value;
  const phone = document.getElementById('phoneNumber').value.trim();
  const whatsapp = document.getElementById('whatsappNumber').value.trim();
  const showtimeId = document.getElementById('modalShowtime').value;

  if (!movie) { alert('يرجى اختيار الفيلم'); return; }
  const invalid = validateStep(1) || validateStep(2);
  if (invalid) { alert(invalid); return; }

  const btn = document.querySelector('.submit-btn');
  btn.disabled = true;

  const payload = {
    _token: CSRF_TOKEN,
    showtime_id: showtimeId,
    package_id: currentBooking.packageId,
    show_date: WEEK_DAYS[currentBooking.dayIndex ?? currentDay].iso,
    full_name: fullName,
    birthdate: birthdate,
    country_code: selectedCountries.phoneCountrySelect.code,
    country_name: selectedCountries.phoneCountrySelect.name,
    phone: phone,
    whatsapp_country_code: selectedCountries.whatsappCountrySelect.code,
    whatsapp: whatsapp,
  };

  try {
    const res = await fetch(BOOK_URL_TEMPLATE.replace('__ID__', movie.id), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      const msg = data.errors ? Object.values(data.errors)[0] : 'تعذّر إتمام الحجز، يرجى المحاولة مرة أخرى.';
      alert(msg);
      btn.disabled = false;
      return;
    }
    showSuccess(data, fullName);
  } catch (err) {
    alert('حدث خطأ في الاتصال، يرجى المحاولة مرة أخرى.');
  }
  btn.disabled = false;
}

function showSuccess(data, fullName) {
  const pkg = PACKAGES.find(p => p.id === currentBooking.packageId);
  document.getElementById('bookingRef').textContent = data.reference;

  const qr = document.getElementById('qrImage');
  qr.src = data.qr_url;
  qr.alt = `تذكرة ${data.reference}`;

  document.getElementById('successMessage').innerHTML =
    `تم حجز <strong>${pkg ? pkg.name : 'تذكرة'}</strong> لمشاهدة فيلم <strong>«${data.movie}»</strong>` +
    (data.seats ? ` — <strong>${data.seats}</strong> مقعد` : '') +
    (pkg && pkg.isVip ? '<br><span style="color:#e71d24;font-weight:800">🍿 يشمل المشروبات والفشار</span>' : '') +
    '<br><span style="font-size:12.5px;color:#6a6a72">احفظ التذكرة، أو أرسلها إلى واتسابك من الزر أدناه.</span>';

  document.getElementById('ticketLink').href = data.ticket_url;

  const wa = document.getElementById('whatsappLink');
  if (wa) {
    if (data.whatsapp_url) {
      wa.href = data.whatsapp_url;
      wa.style.display = 'flex';
    } else {
      wa.style.display = 'none';
    }
  }

  document.getElementById('modalBody').style.display = 'none';
  document.getElementById('successBody').style.display = 'block';
  document.getElementById('successBody').scrollTop = 0;
}

/* ═══════════════════════════════════════════════════
   تحسينات الصفحة
   ═══════════════════════════════════════════════════ */
function initPageEnhancements() {
  const particlesBox = document.getElementById('heroParticles');
  if (particlesBox) {
    for (let i = 0; i < 30; i++) {
      const p = document.createElement('div');
      p.className = 'hero-particle';
      p.style.left = Math.random() * 100 + '%';
      p.style.top = (60 + Math.random() * 40) + '%';
      p.style.animationDuration = (6 + Math.random() * 8) + 's';
      p.style.animationDelay = (Math.random() * 6) + 's';
      p.style.width = p.style.height = (2 + Math.random() * 3) + 'px';
      p.style.opacity = 0.3 + Math.random() * 0.5;
      particlesBox.appendChild(p);
    }
  }

  const counters = document.querySelectorAll('[data-count]');
  const animateCounter = (el) => {
    const target = parseInt(el.dataset.count, 10);
    const duration = 1800;
    const start = performance.now();
    const formatter = (n) => n >= 1000 ? n.toLocaleString('en-US') : n;
    const step = (now) => {
      const elapsed = now - start;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = formatter(Math.floor(target * eased));
      if (progress < 1) requestAnimationFrame(step);
      else el.textContent = formatter(target);
    };
    requestAnimationFrame(step);
  };
  const counterObserver = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) { animateCounter(e.target); counterObserver.unobserve(e.target); }
    });
  }, { threshold: 0.5 });
  counters.forEach(c => counterObserver.observe(c));

  const revealObserver = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('visible'); revealObserver.unobserve(e.target); }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
  document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

  const tickerTrack = document.getElementById('tickerTrack');
  if (tickerTrack) { tickerTrack.innerHTML += tickerTrack.innerHTML; }
}

/* ═══════════════════════════════════════════════════
   الموبايل
   ═══════════════════════════════════════════════════ */
function initMobileMenu() {
  const btn = document.getElementById('mobileMenuBtn');
  const nav = document.getElementById('mainNav');
  if (!btn || !nav) return;
  btn.addEventListener('click', e => { e.stopPropagation(); nav.classList.toggle('open'); });
  document.addEventListener('click', e => {
    if (!e.target.closest('#mainNav') && !e.target.closest('#mobileMenuBtn')) nav.classList.remove('open');
  });
}

/* ═══════════════════════════════════════════════════
   التشغيل
   ═══════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initPageEnhancements();
  renderDays();
  renderGenres();
  renderMovies();

  document.getElementById('bookingModal')?.addEventListener('click', e => { if (e.target.id === 'bookingModal') closeBooking(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeBooking(); });
  document.addEventListener('click', e => { if (!e.target.closest('.country-select')) document.querySelectorAll('.country-select.open').forEach(el => el.classList.remove('open')); });

  renderCountryList('phoneCountryList', 'phoneCountrySelect');
  renderCountryList('whatsappCountryList', 'whatsappCountrySelect');
});
