/* ═══════════════════════════════════════════════════
   بيانات الصفحة (تأتي من قاعدة البيانات عبر GameController)
   ═══════════════════════════════════════════════════ */
const DATA = window.GAMES || { games: [], countries: [], weekDays: [] };
const GAMES = DATA.games || [];
const COUNTRIES = DATA.countries || [];

const WEEK_DAYS = DATA.weekDays || [];

let currentBooking = { game: null, price: null, dayIndex: 0, slot: null };

const selectedCountries = {
  phoneCountrySelect: { code: DATA.defaultCountry || '+218', flag: '🇱🇾' },
  whatsappCountrySelect: { code: DATA.defaultCountry || '+218', flag: '🇱🇾' }
};

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
      || (c.nameEn && c.nameEn.toLowerCase().includes(qq.toLowerCase()))
      || c.code.replace('+', '').includes(cleanQ);
  });
  if (!filtered.length) { listEl.innerHTML = `<div class="country-empty">لا توجد نتائج مطابقة</div>`; return; }
  const sel = selectedCountries[containerId];
  listEl.innerHTML = filtered.map(c => `
    <div class="country-item ${c.code === sel.code ? 'selected' : ''}" onclick="selectCountry('${containerId}', '${c.code}', '${c.flag}')">
      <span class="flag">${c.flag}</span><span class="cname">${c.name}</span><span class="ccode">${c.code}</span>
    </div>`).join('');
}

function selectCountry(containerId, code, flag) {
  selectedCountries[containerId] = { code, flag };
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
   حجز الألعاب
   ═══════════════════════════════════════════════════ */
function renderDurationGrid() {
  const grid = document.getElementById('durationGrid');
  if (!grid) return;
  const prices = (currentBooking.game && currentBooking.game.prices) || [];
  grid.innerHTML = prices.map(p => `
    <button type="button" class="duration-btn ${currentBooking.price && p.id === currentBooking.price.id ? 'active' : ''}"
            onclick="selectDuration(${p.id})">${p.label}<small>${p.price} د.ل</small></button>
  `).join('');
}

function selectDuration(id) {
  const p = (currentBooking.game.prices || []).find(x => x.id === id);
  if (!p) return;
  currentBooking.price = p;
  renderDurationGrid();
  updateTotal();
}

/* ═══════════════════════════════════════════════════
   الأيام وأوقات اللعب (مربوطة بعدد الأجهزة)
   ═══════════════════════════════════════════════════ */
function slotsFor(game, dayIndex) {
  const byDay = (game && game.times && game.times[dayIndex]) || [];
  return byDay;
}

function renderSlotDays() {
  const box = document.getElementById('slotDays');
  if (!box) return;

  box.innerHTML = WEEK_DAYS.map((d, i) => `
    <button type="button" class="slot-day ${i === currentBooking.dayIndex ? 'active' : ''}" onclick="selectSlotDay(${i})">
      ${d.name}<small>${d.date}</small>
    </button>
  `).join('');
}

function selectSlotDay(index) {
  currentBooking.dayIndex = index;
  currentBooking.slot = null;
  renderSlotDays();
  renderSlotTimes();
}

function renderSlotTimes() {
  const box = document.getElementById('slotTimes');
  const hint = document.getElementById('slotHint');
  if (!box) return;

  const slots = slotsFor(currentBooking.game, currentBooking.dayIndex);

  if (!slots.length) {
    box.innerHTML = '';
    if (hint) hint.textContent = 'لا توجد أوقات لعب متاحة في هذا اليوم، جرّب يومًا آخر.';
    currentBooking.slot = null;
    return;
  }

  if (hint) hint.textContent = 'الأوقات المتاحة — الرقم بين القوسين هو عدد الأجهزة الفارغة.';

  box.innerHTML = slots.map(s => {
    const full = s.free <= 0;
    const active = currentBooking.slot && currentBooking.slot.id === s.id ? 'active' : '';
    return `
      <button type="button" class="slot-time ${active}" ${full ? 'disabled' : ''} onclick="selectSlotTime(${s.id})">
        ${s.time} - ${s.end}
        <small>${full ? 'محجوز بالكامل' : `متاح ${s.free}/${s.total}`}</small>
      </button>`;
  }).join('');
}

function selectSlotTime(id) {
  const slots = slotsFor(currentBooking.game, currentBooking.dayIndex);
  const slot = slots.find(s => s.id === id);
  if (!slot || slot.free <= 0) return;
  currentBooking.slot = slot;
  renderSlotTimes();
  updateTotal();
}

function applyGameToModal(game) {
  currentBooking.game = game;

  const icon = document.getElementById('modalIcon');
  icon.textContent = game.icon || '🎮';
  icon.style.background = `linear-gradient(155deg, ${game.g1}, ${game.g2})`;
  icon.classList.remove('pulse');
  void icon.offsetWidth;
  icon.classList.add('pulse');

  document.getElementById('modalTitle').textContent = game.name;
  document.getElementById('modalMeta').textContent = game.meta || '';

  const prices = game.prices || [];
  currentBooking.price = prices.find(p => p.isDefault) || prices[0] || null;

  // Land on the first day that still has a free slot so the modal is usable immediately.
  currentBooking.slot = null;
  let startDay = 0;
  for (let i = 0; i < WEEK_DAYS.length; i++) {
    if (slotsFor(game, i).some(s => s.free > 0)) { startDay = i; break; }
  }
  currentBooking.dayIndex = startDay;

  renderSlotDays();
  renderSlotTimes();
  renderDurationGrid();
  updateTotal();
}

function onDeviceChange() {
  const id = parseInt(document.getElementById('modalDevice').value, 10);
  const game = GAMES.find(g => g.id === id);
  if (game) applyGameToModal(game);
}

function updateTotal() {
  const total = currentBooking.price ? currentBooking.price.price : 0;
  document.getElementById('totalPrice').innerHTML = `${total} <small>د.ل</small>`;

  if (!currentBooking.game) {
    document.getElementById('totalSubLabel').textContent = '—';
    return;
  }

  const day = WEEK_DAYS[currentBooking.dayIndex];
  const parts = [currentBooking.game.name];
  if (currentBooking.price) parts.push(currentBooking.price.label);
  if (day) parts.push(`${day.name} ${day.date}`);
  if (currentBooking.slot) parts.push(`${currentBooking.slot.time} - ${currentBooking.slot.end}`);

  document.getElementById('totalSubLabel').textContent = parts.join(' — ');
}

function openGameBooking(gameId) {
  const modal = document.getElementById('gameBookingModal');
  if (!modal) return;
  if (!GAMES.length) return;

  const deviceSelect = document.getElementById('modalDevice');
  deviceSelect.innerHTML = GAMES.map(g =>
    `<option value="${g.id}">${g.icon || '🎮'} ${g.name} — ${g.prices && g.prices.length ? g.prices[0].price + ' د.ل' : ''}</option>`
  ).join('');

  document.getElementById('gameBookingForm').reset();

  let game = gameId ? GAMES.find(g => g.id === parseInt(gameId, 10)) : null;
  if (!game) game = GAMES[0];
  deviceSelect.value = game.id;

  applyGameToModal(game);

  const today = WEEK_DAYS[0];
  document.getElementById('modalDay').textContent = today
    ? `${today.name} — ${today.date}`
    : '';

  document.getElementById('modalBody').style.display = 'block';
  document.getElementById('successBody').style.display = 'none';
  modal.classList.add('show');
  document.body.style.overflow = 'hidden';
  document.querySelectorAll('.country-select.open').forEach(el => el.classList.remove('open'));
}

function closeGameBooking() {
  const modal = document.getElementById('gameBookingModal');
  if (!modal) return;
  modal.classList.remove('show');
  document.body.style.overflow = '';
  document.querySelectorAll('.country-select.open').forEach(el => el.classList.remove('open'));
}

function submitGameBooking(e) {
  e.preventDefault();
  const fullName = document.getElementById('fullName').value.trim();
  const phone = document.getElementById('phoneNumber').value.trim();
  const whatsapp = document.getElementById('whatsappNumber').value.trim();

  if (!currentBooking.game || !currentBooking.price) { alert('يرجى اختيار الجهاز والمدة'); return; }
  if (!currentBooking.slot) { alert('يرجى اختيار وقت اللعب'); return; }
  if (fullName.split(/\s+/).length < 3) { alert('يرجى إدخال الاسم الثلاثي كاملاً'); return; }
  if (phone.replace(/\D/g, '').length < 6) { alert('يرجى إدخال رقم هاتف صحيح'); return; }
  if (whatsapp.replace(/\D/g, '').length < 6) { alert('يرجى إدخال رقم واتساب صحيح'); return; }

  const btn = e.target.querySelector('.submit-btn');
  if (btn) btn.disabled = true;

  const url = (DATA.bookUrl || '').replace('__ID__', currentBooking.game.id);

  fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': DATA.csrf || ''
    },
    body: JSON.stringify({
      game_price_id: currentBooking.price.id,
      play_time_id: currentBooking.slot.id,
      play_date: WEEK_DAYS[currentBooking.dayIndex].iso,
      full_name: fullName,
      country_code: selectedCountries.phoneCountrySelect.code,
      country_name: (COUNTRIES.find(c => c.code === selectedCountries.phoneCountrySelect.code) || {}).name || '',
      phone,
      whatsapp_country_code: selectedCountries.whatsappCountrySelect.code,
      whatsapp
    })
  })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
      if (!ok || !data.ok) {
        alert((data.errors && Object.values(data.errors)[0]) || 'تعذّر إتمام الحجز، حاول مجددًا');
        return;
      }
      document.getElementById('bookingRef').textContent = data.reference;
      const qr = document.getElementById('qrImage');
      qr.src = data.qr_url;
      qr.alt = `حجز ${data.reference}`;
      document.getElementById('successMessage').innerHTML =
        `تم حجز <strong>${data.game}</strong> لمدة <strong>${data.duration}</strong>
         <br>الموعد: <strong>${data.slot}</strong> — ${data.play_date}
         <br>الجهاز: <strong>${data.device}</strong> • المجموع: <strong>${data.price} د.ل</strong>`;
      document.getElementById('modalBody').style.display = 'none';
      document.getElementById('successBody').style.display = 'block';
      document.getElementById('successBody').scrollTop = 0;
    })
    .catch(() => alert('تعذّر الاتصال بالخادم، حاول مجددًا'))
    .finally(() => { if (btn) btn.disabled = false; });
}

/* ═══════════════════════════════════════════════════
   تحسينات الصفحة — جسيمات، عدّادات، Scroll Reveal
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

function initMobileMenu() {
  const btn = document.getElementById('mobileMenuBtn');
  const nav = document.getElementById('mainNav');
  if (!btn || !nav) return;
  btn.addEventListener('click', e => { e.stopPropagation(); nav.classList.toggle('open'); });
  document.addEventListener('click', e => {
    if (!e.target.closest('#mainNav') && !e.target.closest('#mobileMenuBtn')) nav.classList.remove('open');
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initPageEnhancements();

  document.getElementById('gameBookingModal')?.addEventListener('click', e => {
    if (e.target.id === 'gameBookingModal') closeGameBooking();
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeGameBooking(); });
  document.addEventListener('click', e => {
    if (!e.target.closest('.country-select')) document.querySelectorAll('.country-select.open').forEach(el => el.classList.remove('open'));
  });

  renderCountryList('phoneCountryList', 'phoneCountrySelect');
  renderCountryList('whatsappCountryList', 'whatsappCountrySelect');
  renderSlotDays();
});
