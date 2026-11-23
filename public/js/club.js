/* ═══════════════════════════════════════════════════
   النادي الاجتماعي — حجز من الواجهة العامة
   البيانات تأتي من ClubController عبر window.CLUBS
   ═══════════════════════════════════════════════════ */
const DATA = window.CLUBS || { clubs: [], weekDays: [] };
const CLUBS = DATA.clubs || [];
const WEEK_DAYS = DATA.weekDays || [];

const state = { club: null, dayIndex: 0, pkg: null };

function clubById(id) {
  return CLUBS.find(c => c.id === Number(id)) || null;
}

function renderDays() {
  const box = document.getElementById('clubDays');
  if (!box) return;
  box.innerHTML = WEEK_DAYS.map((d, i) => `
    <button type="button" class="day ${i === state.dayIndex ? 'active' : ''}" onclick="selectDay(${i})">
      ${d.name}<small>${d.date}</small>
    </button>`).join('');
}

function selectDay(index) {
  state.dayIndex = index;
  renderDays();
}

function renderPackages() {
  const box = document.getElementById('clubPackages');
  if (!box) return;
  const packages = (state.club && state.club.packages) || [];

  box.innerHTML = packages.map(p => `
    <button type="button" class="pick ${state.pkg && state.pkg.id === p.id ? 'active' : ''}" onclick="selectPackage(${p.id})">
      <span>
        <b>${p.name}</b>
        <small>${p.start} - ${p.end} · ${p.people} أشخاص${p.includes ? ' · ' + p.includes : ''}</small>
      </span>
      <span class="amt" style="font-weight:900;color:var(--gold);white-space:nowrap">${p.price} د.ل</span>
    </button>`).join('');
}

function selectPackage(id) {
  const packages = (state.club && state.club.packages) || [];
  state.pkg = packages.find(p => p.id === Number(id)) || null;
  renderPackages();
  updateTotal();
}

function updateTotal() {
  document.getElementById('clubTotal').textContent = `${state.pkg ? state.pkg.price : 0} د.ل`;
}

function openClubBooking(id) {
  const club = clubById(id);
  if (!club) return;

  state.club = club;
  state.dayIndex = 0;
  state.pkg = (club.packages || []).find(p => p.isDefault) || (club.packages || [])[0] || null;

  document.getElementById('clubModalTitle').textContent = club.name;
  document.getElementById('clubModalSub').textContent = club.location || '';
  document.getElementById('clubFormBody').style.display = 'block';
  document.getElementById('clubSuccessBody').style.display = 'none';
  document.getElementById('clubForm').reset();

  renderDays();
  renderPackages();
  updateTotal();

  document.getElementById('clubModal').classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeClubBooking() {
  document.getElementById('clubModal')?.classList.remove('show');
  document.body.style.overflow = '';
}

function submitClubBooking(e) {
  e.preventDefault();

  const name = document.getElementById('clubName').value.trim();
  const phone = document.getElementById('clubPhone').value.trim();
  const whatsapp = document.getElementById('clubWhatsapp').value.trim();

  if (!state.club || !state.pkg) { alert('يرجى اختيار الباقة'); return; }
  if (name.split(/\s+/).length < 3) { alert('يرجى إدخال الاسم الثلاثي كاملاً'); return; }
  if (phone.replace(/\D/g, '').length < 6) { alert('يرجى إدخال رقم هاتف صحيح'); return; }
  if (whatsapp.replace(/\D/g, '').length < 6) { alert('يرجى إدخال رقم واتساب صحيح'); return; }

  const btn = document.getElementById('clubSubmit');
  btn.disabled = true;

  fetch((DATA.bookUrl || '').replace('__ID__', state.club.id), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': DATA.csrf || ''
    },
    body: JSON.stringify({
      club_package_id: state.pkg.id,
      visit_date: WEEK_DAYS[state.dayIndex].iso,
      full_name: name,
      country_code: DATA.defaultCountry || '+218',
      country_name: 'ليبيا',
      phone,
      whatsapp_country_code: DATA.defaultCountry || '+218',
      whatsapp
    })
  })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
      if (!ok || !data.ok) {
        alert((data.errors && Object.values(data.errors)[0]) || 'تعذّر إتمام الحجز، حاول مجددًا');
        return;
      }

      document.getElementById('clubSuccessText').innerHTML =
        `تم حجز <strong>${data.package}</strong> في <strong>${data.club}</strong><br>الموعد: ${data.visit_date} · ${data.time}`;
      document.getElementById('clubRef').textContent = data.reference;

      const qr = document.getElementById('clubQr');
      qr.src = data.qr_url;
      qr.alt = `حجز ${data.reference}`;

      document.getElementById('clubFormBody').style.display = 'none';
      document.getElementById('clubSuccessBody').style.display = 'block';
    })
    .catch(() => alert('تعذّر الاتصال بالخادم، حاول مجددًا'))
    .finally(() => { btn.disabled = false; });
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('clubModal')?.addEventListener('click', e => {
    if (e.target.id === 'clubModal') closeClubBooking();
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeClubBooking(); });
});
