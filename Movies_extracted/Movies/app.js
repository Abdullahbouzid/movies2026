/* ============ البيانات ============ */
const movies = [
  { title:"ظل المدينة",     genre:"إثارة",        rating:8.6, duration:128, cert:"+16", g1:"#eb1e23", g2:"#7a0d10", seed:1 },
  { title:"رحلة الصحراء",   genre:"مغامرة",       rating:8.1, duration:142, cert:"+12", g1:"#c4171c", g2:"#eb1e23", seed:2 },
  { title:"النجم الأخير",   genre:"خيال علمي",    rating:9.0, duration:155, cert:"+12", g1:"#8b1115", g2:"#e71d24", seed:3 },
  { title:"قلوب حائرة",     genre:"رومانسي",      rating:7.4, duration:112, cert:"+12", g1:"#e71d24", g2:"#a81216", seed:4 },
  { title:"المهمة: صفر",    genre:"أكشن",         rating:8.8, duration:136, cert:"+16", g1:"#5c0a0d", g2:"#e71d24", seed:5 },
  { title:"ضحكة الليل",     genre:"كوميدي",       rating:7.9, duration:105, cert:"+12", g1:"#e71d24", g2:"#c4171c", seed:6 },
  { title:"بحر الأسرار",    genre:"غموض",         rating:8.3, duration:121, cert:"+16", g1:"#a81216", g2:"#e71d24", seed:7 },
  { title:"عودة البطل",     genre:"دراما",        rating:8.5, duration:133, cert:"+12", g1:"#7a0d10", g2:"#c4171c", seed:8 },
  { title:"المدينة الحالمة", genre:"رسوم متحركة", rating:8.2, duration:98,  cert:"الجميع", g1:"#e71d24", g2:"#7a0d10", seed:9 }
];
const TICKET_PRICES = { regular: 35, vip: 80 };
const COUNTRIES = [
  { name:'السعودية',      nameEn:'Saudi Arabia', code:'+966', flag:'🇸🇦' },
  { name:'ليبيا',         nameEn:'Libya',        code:'+218', flag:'🇱🇾' },
  { name:'مصر',           nameEn:'Egypt',        code:'+20',  flag:'🇪🇬' },
  { name:'الإمارات',      nameEn:'UAE',          code:'+971', flag:'🇦🇪' },
  { name:'الكويت',        nameEn:'Kuwait',       code:'+965', flag:'🇰🇼' },
  { name:'قطر',           nameEn:'Qatar',        code:'+974', flag:'🇶🇦' },
  { name:'البحرين',       nameEn:'Bahrain',      code:'+973', flag:'🇧🇭' },
  { name:'عُمان',         nameEn:'Oman',         code:'+968', flag:'🇴🇲' },
  { name:'الأردن',        nameEn:'Jordan',       code:'+962', flag:'🇯🇴' },
  { name:'لبنان',         nameEn:'Lebanon',      code:'+961', flag:'🇱🇧' },
  { name:'سوريا',         nameEn:'Syria',        code:'+963', flag:'🇸🇾' },
  { name:'العراق',        nameEn:'Iraq',         code:'+964', flag:'🇮🇶' },
  { name:'فلسطين',        nameEn:'Palestine',    code:'+970', flag:'🇵🇸' },
  { name:'اليمن',         nameEn:'Yemen',        code:'+967', flag:'🇾🇪' },
  { name:'السودان',       nameEn:'Sudan',        code:'+249', flag:'🇸🇩' },
  { name:'المغرب',        nameEn:'Morocco',      code:'+212', flag:'🇲🇦' },
  { name:'الجزائر',       nameEn:'Algeria',      code:'+213', flag:'🇩🇿' },
  { name:'تونس',          nameEn:'Tunisia',      code:'+216', flag:'🇹🇳' },
  { name:'موريتانيا',     nameEn:'Mauritania',   code:'+222', flag:'🇲🇷' },
  { name:'الصومال',       nameEn:'Somalia',      code:'+252', flag:'🇸🇴' },
  { name:'جيبوتي',        nameEn:'Djibouti',     code:'+253', flag:'🇩🇯' },
  { name:'جزر القمر',     nameEn:'Comoros',      code:'+269', flag:'🇰🇲' },
  { name:'تركيا',         nameEn:'Turkey',       code:'+90',  flag:'🇹🇷' },
  { name:'إيران',         nameEn:'Iran',         code:'+98',  flag:'🇮🇷' },
  { name:'باكستان',       nameEn:'Pakistan',     code:'+92',  flag:'🇵🇰' },
  { name:'الهند',         nameEn:'India',        code:'+91',  flag:'🇮🇳' },
  { name:'بنغلاديش',      nameEn:'Bangladesh',   code:'+880', flag:'🇧🇩' },
  { name:'إندونيسيا',     nameEn:'Indonesia',    code:'+62',  flag:'🇮🇩' },
  { name:'ماليزيا',       nameEn:'Malaysia',     code:'+60',  flag:'🇲🇾' },
  { name:'الفلبين',       nameEn:'Philippines',  code:'+63',  flag:'🇵🇭' },
  { name:'الصين',         nameEn:'China',        code:'+86',  flag:'🇨🇳' },
  { name:'اليابان',       nameEn:'Japan',        code:'+81',  flag:'🇯🇵' },
  { name:'كوريا الجنوبية', nameEn:'South Korea', code:'+82',  flag:'🇰🇷' },
  { name:'الولايات المتحدة', nameEn:'USA',       code:'+1',   flag:'🇺🇸' },
  { name:'كندا',          nameEn:'Canada',       code:'+1',   flag:'🇨🇦' },
  { name:'بريطانيا',      nameEn:'United Kingdom', code:'+44', flag:'🇬🇧' },
  { name:'فرنسا',         nameEn:'France',       code:'+33',  flag:'🇫🇷' },
  { name:'ألمانيا',       nameEn:'Germany',      code:'+49',  flag:'🇩🇪' },
  { name:'إيطاليا',       nameEn:'Italy',        code:'+39',  flag:'🇮🇹' },
  { name:'إسبانيا',       nameEn:'Spain',        code:'+34',  flag:'🇪🇸' },
  { name:'البرتغال',      nameEn:'Portugal',     code:'+351', flag:'🇵🇹' },
  { name:'هولندا',        nameEn:'Netherlands',  code:'+31',  flag:'🇳🇱' },
  { name:'بلجيكا',        nameEn:'Belgium',      code:'+32',  flag:'🇧🇪' },
  { name:'سويسرا',        nameEn:'Switzerland',  code:'+41',  flag:'🇨🇭' },
  { name:'النمسا',        nameEn:'Austria',      code:'+43',  flag:'🇦🇹' },
  { name:'السويد',        nameEn:'Sweden',       code:'+46',  flag:'🇸🇪' },
  { name:'النرويج',       nameEn:'Norway',       code:'+47',  flag:'🇳🇴' },
  { name:'الدنمارك',      nameEn:'Denmark',      code:'+45',  flag:'🇩🇰' },
  { name:'فنلندا',        nameEn:'Finland',      code:'+358', flag:'🇫🇮' },
  { name:'أيرلندا',       nameEn:'Ireland',      code:'+353', flag:'🇮🇪' },
  { name:'روسيا',         nameEn:'Russia',       code:'+7',   flag:'🇷🇺' },
  { name:'أوكرانيا',      nameEn:'Ukraine',      code:'+380', flag:'🇺🇦' },
  { name:'بولندا',        nameEn:'Poland',       code:'+48',  flag:'🇵🇱' },
  { name:'اليونان',       nameEn:'Greece',       code:'+30',  flag:'🇬🇷' },
  { name:'أستراليا',      nameEn:'Australia',    code:'+61',  flag:'🇦🇺' },
  { name:'نيوزيلندا',     nameEn:'New Zealand',  code:'+64',  flag:'🇳🇿' },
  { name:'البرازيل',      nameEn:'Brazil',       code:'+55',  flag:'🇧🇷' },
  { name:'الأرجنتين',     nameEn:'Argentina',    code:'+54',  flag:'🇦🇷' },
  { name:'المكسيك',       nameEn:'Mexico',       code:'+52',  flag:'🇲🇽' },
  { name:'جنوب أفريقيا',  nameEn:'South Africa', code:'+27',  flag:'🇿🇦' },
  { name:'نيجيريا',       nameEn:'Nigeria',      code:'+234', flag:'🇳🇬' },
  { name:'كينيا',         nameEn:'Kenya',        code:'+254', flag:'🇰🇪' },
  { name:'إثيوبيا',       nameEn:'Ethiopia',     code:'+251', flag:'🇪🇹' },
  { name:'غانا',          nameEn:'Ghana',        code:'+233', flag:'🇬🇭' },
  { name:'أفغانستان',     nameEn:'Afghanistan',  code:'+93',  flag:'🇦🇫' },
  { name:'سنغافورة',      nameEn:'Singapore',    code:'+65',  flag:'🇸🇬' },
  { name:'تايلاند',       nameEn:'Thailand',     code:'+66',  flag:'🇹🇭' },
  { name:'فيتنام',        nameEn:'Vietnam',      code:'+84',  flag:'🇻🇳' }
];
const dayNames   = ["الأحد","الاثنين","الثلاثاء","الأربعاء","الخميس","الجمعة","السبت"];
const monthNames = ["يناير","فبراير","مارس","أبريل","مايو","يونيو","يوليو","أغسطس","سبتمبر","أكتوبر","نوفمبر","ديسمبر"];
const today = new Date();
const weekDays = [];
for (let i = 0; i < 7; i++) {
  const d = new Date(today.getFullYear(), today.getMonth(), today.getDate() + i);
  weekDays.push({ name: dayNames[d.getDay()], date: `${d.getDate()} ${monthNames[d.getMonth()]}`, isToday: i === 0 });
}
const timePools = [
  ["11:30","14:00","16:30","19:00","21:30"],
  ["12:00","14:45","17:15","20:00","22:30"],
  ["13:00","15:30","18:00","20:45","23:15"],
  ["12:30","15:00","17:45","20:15","22:45"],
  ["11:45","14:15","16:45","19:15","21:45","23:45"]
];
function getShowtimes(movie, dayIndex){ return timePools[(movie.seed + dayIndex) % timePools.length]; }
function generateBookingRef(){ const c='ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; let r='CP-'; for(let i=0;i<6;i++) r+=c[Math.floor(Math.random()*c.length)]; return r; }

/* ============ الحالة ============ */
let currentDay = 0;
let currentGenre = "الكل";
let currentBooking = { movie: null, times: [], selectedTime: null, ticketType: 'regular' };
const selectedCountries = {
  phoneCountrySelect: { code: '+966', flag: '🇸🇦' },
  whatsappCountrySelect: { code: '+966', flag: '🇸🇦' }
};

/* ============ الموبايل ============ */
function initMobileMenu(){
  const btn = document.getElementById('mobileMenuBtn');
  const nav = document.getElementById('mainNav');
  if (!btn || !nav) return;
  btn.addEventListener('click', e => { e.stopPropagation(); nav.classList.toggle('open'); });
  document.addEventListener('click', e => {
    if (!e.target.closest('#mainNav') && !e.target.closest('#mobileMenuBtn')) nav.classList.remove('open');
  });
}

/* ============ صفحة الأفلام ============ */
function initMoviesPage(){
  const daysEl = document.getElementById('days');
  const genresEl = document.getElementById('genres');
  const gridEl = document.getElementById('grid');
  const countEl = document.getElementById('count');
  if (!daysEl || !gridEl) return;

  function renderDays(){
    daysEl.innerHTML = weekDays.map((d, i) =>
      `<button class="day-pill ${i===currentDay?'active':''}" data-day="${i}">
        ${d.isToday ? '<span class="today-dot"></span>' : ''}
        <span class="d-name">${d.name}</span>
        <span class="d-date">${d.date}</span>
      </button>`).join('');
    daysEl.querySelectorAll('.day-pill').forEach(b => b.addEventListener('click', () => {
      currentDay = +b.dataset.day; renderDays(); renderMovies();
    }));
  }
  function renderGenres(){
    const gs = ["الكل", ...new Set(movies.map(m => m.genre))];
    genresEl.innerHTML = gs.map(g => `<button class="chip ${g===currentGenre?'active':''}" data-genre="${g}">${g}</button>`).join('');
    genresEl.querySelectorAll('.chip').forEach(b => b.addEventListener('click', () => {
      currentGenre = b.dataset.genre; renderGenres(); renderMovies();
    }));
  }
  function renderMovies(){
    const list = movies.filter(m => currentGenre === "الكل" || m.genre === currentGenre);
    countEl.textContent = `${list.length} ${list.length===1?'فيلم':'أفلام'}`;
    if (!list.length){ gridEl.innerHTML = `<div class="empty">لا توجد أفلام مطابقة حاليًا 🎬</div>`; return; }
    gridEl.innerHTML = list.map((m, i) => {
      const times = getShowtimes(m, currentDay);
      return `<article class="movie-card" style="animation-delay:${i*55}ms">
        <div class="poster" style="--g1:${m.g1};--g2:${m.g2}">
          <div class="texture"></div>
          <span class="badge rating">★ ${m.rating}</span>
          <span class="badge cert">${m.cert}</span>
          <div class="play-float"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></div>
          <div class="poster-bottom"><h3>${m.title}</h3><span>${m.genre.toUpperCase()}</span></div>
        </div>
        <div class="card-body">
          <div class="meta"><span>⏱ ${m.duration} دقيقة</span><span class="dot"></span><span>${m.genre}</span></div>
          <div class="showtimes-label">أوقات العرض — ${weekDays[currentDay].name}</div>
          <div class="showtimes">
            ${times.map(t => `<button class="time" onclick="openBooking('${m.title}', '${t}')">${t}</button>`).join('')}
          </div>
          <div class="card-footer">
            <div class="price-tag"><span class="price-label">السعر من</span><strong>${TICKET_PRICES.regular} <small>﷼</small></strong></div>
            <button class="btn-book" onclick="openBooking('${m.title}', '${times[0]}')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/></svg>
              <span>احجز تذكرة</span>
            </button>
          </div>
        </div>
      </article>`;
    }).join('');
  }
  renderDays(); renderGenres(); renderMovies();
}

/* ============ حجز التذكرة ============ */
function openBooking(movieTitle, preselectedTime){
  const modal = document.getElementById('bookingModal');
  if (!modal) return;
  const movie = movies.find(m => m.title === movieTitle);
  if (!movie) return;
  const times = getShowtimes(movie, currentDay);
  currentBooking = { movie, times, selectedTime: preselectedTime || times[0], ticketType: 'regular' };

  document.getElementById('modalTitle').textContent = movie.title;
  document.getElementById('modalMeta').textContent = `${movie.genre} • ★${movie.rating} • ${movie.duration} دقيقة • ${movie.cert}`;
  document.getElementById('modalDay').textContent = `${weekDays[currentDay].name} — ${weekDays[currentDay].date}`;
  document.getElementById('modalPoster').style.background = `linear-gradient(155deg, ${movie.g1}, ${movie.g2})`;

  const sel = document.getElementById('modalShowtime');
  sel.innerHTML = times.map(t => `<option value="${t}" ${t===currentBooking.selectedTime?'selected':''}>${t}</option>`).join('');
  document.getElementById('bookingForm').reset();
  sel.value = currentBooking.selectedTime;

  document.querySelectorAll('.ticket-tab').forEach(t => t.classList.toggle('active', t.dataset.type === 'regular'));
  document.getElementById('vipPerks').classList.remove('show');
  updateTotal();

  document.getElementById('modalBody').style.display = 'block';
  document.getElementById('successBody').style.display = 'none';
  modal.classList.add('show');
  document.body.style.overflow = 'hidden';
  document.querySelectorAll('.country-select.open').forEach(el => el.classList.remove('open'));
}
function closeBooking(){
  const modal = document.getElementById('bookingModal');
  if (!modal) return;
  modal.classList.remove('show');
  document.body.style.overflow = '';
  document.querySelectorAll('.country-select.open').forEach(el => el.classList.remove('open'));
}
function selectTicketType(type){
  currentBooking.ticketType = type;
  document.querySelectorAll('.ticket-tab').forEach(t => t.classList.toggle('active', t.dataset.type === type));
  document.getElementById('vipPerks').classList.toggle('show', type === 'vip');
  updateTotal();
}
function updateTotal(){
  const price = TICKET_PRICES[currentBooking.ticketType];
  const isVip = currentBooking.ticketType === 'vip';
  document.getElementById('totalPrice').innerHTML = `${price} <small>﷼</small>`;
  document.getElementById('totalSubLabel').textContent = isVip ? 'تذكرة VIP — تشمل مشروب + فشار' : 'تذكرة عادية';
}
function renderCountryList(listId, containerId, q=''){
  const listEl = document.getElementById(listId);
  if (!listEl) return;
  const qq = q.trim();
  const cleanQ = qq.replace(/^\+/, '');
  const filtered = COUNTRIES.filter(c => {
    if (!qq) return true;
    return c.name.includes(qq) || (c.nameEn && c.nameEn.toLowerCase().includes(qq.toLowerCase())) || c.code.replace('+','').includes(cleanQ);
  });
  if (!filtered.length){ listEl.innerHTML = `<div class="country-empty">لا توجد نتائج مطابقة</div>`; return; }
  const sel = selectedCountries[containerId];
  listEl.innerHTML = filtered.map(c => `
    <div class="country-item ${c.code===sel.code?'selected':''}" onclick="selectCountry('${containerId}', '${c.code}', '${c.flag}')">
      <span class="flag">${c.flag}</span><span class="cname">${c.name}</span><span class="ccode">${c.code}</span>
    </div>`).join('');
}
function selectCountry(containerId, code, flag){
  selectedCountries[containerId] = { code, flag };
  const btn = document.querySelector(`#${containerId} .country-btn`);
  if (btn){ btn.querySelector('.flag').textContent = flag; btn.querySelector('.code-text').textContent = code; }
  document.getElementById(containerId).classList.remove('open');
  const s = document.querySelector(`#${containerId} .country-search`);
  if (s) s.value = '';
}
function toggleCountryDropdown(containerId){
  const c = document.getElementById(containerId);
  if (!c) return;
  const wasOpen = c.classList.contains('open');
  document.querySelectorAll('.country-select.open').forEach(el => el.classList.remove('open'));
  if (!wasOpen){
    c.classList.add('open');
    const s = c.querySelector('.country-search');
    if (s){ s.value = ''; setTimeout(() => s.focus(), 50); }
    const listId = containerId === 'phoneCountrySelect' ? 'phoneCountryList' : 'whatsappCountryList';
    renderCountryList(listId, containerId);
  }
}
function filterCountryList(input, listId){
  const c = input.closest('.country-select');
  renderCountryList(listId, c.id, input.value);
}
function submitBooking(e){
  e.preventDefault();
  const fullName = document.getElementById('fullName').value.trim();
  const birthdate = document.getElementById('birthdate').value;
  const phone = document.getElementById('phoneNumber').value.trim();
  const whatsapp = document.getElementById('whatsappNumber').value.trim();
  const showtime = document.getElementById('modalShowtime').value;

  if (fullName.split(/\s+/).length < 3){ alert('يرجى إدخال الاسم الثلاثي كاملاً'); return; }
  if (!birthdate){ alert('يرجى إدخال تاريخ الميلاد'); return; }
  if (phone.replace(/\D/g,'').length < 6){ alert('يرجى إدخال رقم هاتف صحيح'); return; }
  if (whatsapp.replace(/\D/g,'').length < 6){ alert('يرجى إدخال رقم واتساب صحيح'); return; }

  const phoneFull = selectedCountries.phoneCountrySelect.code + phone;
  const price = TICKET_PRICES[currentBooking.ticketType];
  const isVip = currentBooking.ticketType === 'vip';
  const bookingRef = generateBookingRef();
  document.getElementById('bookingRef').textContent = bookingRef;

  const qrData = ['Cinema Plus Ticket', `Ref: ${bookingRef}`, `Movie: ${currentBooking.movie.title}`,
    `Time: ${showtime}`, `Day: ${weekDays[currentDay].name} ${weekDays[currentDay].date}`,
    `Type: ${isVip ? 'VIP' : 'Regular'}`, `Name: ${fullName}`, `Phone: ${phoneFull}`].join('\n');

  const qr = document.getElementById('qrImage');
  qr.src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(qrData)}&color=0a0a10&bgcolor=ffffff&margin=0`;
  qr.alt = `تذكرة ${bookingRef}`;

  document.getElementById('successMessage').innerHTML =
    `تم حجز <strong>${isVip?'تذكرة VIP':'تذكرة عادية'}</strong> لمشاهدة فيلم <strong>«${currentBooking.movie.title}»</strong>${isVip?'<br><span style="color:#e71d24;font-weight:800">🍿 يشمل المشروبات والفشار</span>':''}`;

  document.getElementById('modalBody').style.display = 'none';
  document.getElementById('successBody').style.display = 'block';
  document.getElementById('successBody').scrollTop = 0;
  console.log('Booking:', { ref: bookingRef, movie: currentBooking.movie.title, showtime, type: currentBooking.ticketType, price, fullName, birthdate, phone: phoneFull, whatsapp: selectedCountries.whatsappCountrySelect.code + whatsapp });
}
function initBookingLogic(){
  document.getElementById('bookingModal')?.addEventListener('click', e => { if (e.target.id === 'bookingModal') closeBooking(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeBooking(); });
  document.addEventListener('click', e => { if (!e.target.closest('.country-select')) document.querySelectorAll('.country-select.open').forEach(el => el.classList.remove('open')); });
  renderCountryList('phoneCountryList', 'phoneCountrySelect');
  renderCountryList('whatsappCountryList', 'whatsappCountrySelect');
}

/* ============ حجز المقهى ============ */
function initCafeForm(){
  const form = document.getElementById('cafeForm');
  if (!form) return;
  form.addEventListener('submit', e => {
    e.preventDefault();
    const name = document.getElementById('cafeName').value.trim();
    const phone = document.getElementById('cafePhone').value.trim();
    const occasion = document.getElementById('cafeOccasion').value;
    const date = document.getElementById('cafeDate').value;
    const guests = document.getElementById('cafeGuests').value;
    const notes = document.getElementById('cafeNotes').value.trim();
    if (name.split(/\s+/).length < 2){ alert('يرجى إدخال الاسم الكامل'); return; }
    if (phone.replace(/\D/g,'').length < 6){ alert('يرجى إدخال رقم جوال صحيح'); return; }
    if (!occasion){ alert('يرجى اختيار نوع المناسبة'); return; }
    if (!date){ alert('يرجى إدخال تاريخ المناسبة'); return; }
    if (!guests || guests < 5){ alert('عدد الضيوف 5 على الأقل'); return; }
    const ref = 'CF-' + Math.random().toString(36).slice(2,8).toUpperCase();
    alert(`✅ تم إرسال طلب حجز المناسبة!\n\nرقم الطلب: ${ref}\nالاسم: ${name}\nالجوال: ${phone}\nالمناسبة: ${occasion}\nالتاريخ: ${date}\nعدد الضيوف: ${guests}\n\nسيتواصل معك فريق المقهى خلال 24 ساعة.`);
    form.reset();
    console.log('Cafe:', { ref, name, phone, occasion, date, guests, notes });
  });
}

/* ============ FAQ Accordion ============ */
function initCafeFaq(){
  const faqItems = document.querySelectorAll('.faq-item');
  if (!faqItems.length) return;
  faqItems.forEach(item => {
    const q = item.querySelector('.faq-question');
    q.addEventListener('click', () => {
      const wasActive = item.classList.contains('active');
      faqItems.forEach(i => i.classList.remove('active'));
      if (!wasActive) item.classList.add('active');
    });
  });
}

/* ============ التشغيل ============ */
document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  const page = document.body.dataset.page;
  if (page === 'movies') initMoviesPage();
  if (document.getElementById('bookingModal')) initBookingLogic();
  if (page === 'cafe') { initCafeForm(); initCafeFaq(); }
});