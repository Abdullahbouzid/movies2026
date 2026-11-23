/* ═══════════════════════════════════════════════════
   حجز مناسبات مقهى الكلية — بيانات من EventController
   ═══════════════════════════════════════════════════ */
const DATA = window.EVENTS || { packages: [], weekDays: [], slots: [] };
const PACKAGES = DATA.packages || [];
const WEEK_DAYS = DATA.weekDays || [];
const SLOTS = DATA.slots || [];

const state = { dayIndex: 0, slot: null, package: null };

function renderDays() {
  const box = document.getElementById('eventDays');
  if (!box) return;
  box.innerHTML = WEEK_DAYS.map((d, i) => `
    <button type="button" class="day ${i === state.dayIndex ? 'active' : ''}" onclick="selectEventDay(${i})">
      ${d.name}<small>${d.date}</small>
    </button>`).join('');
}

function selectEventDay(index) {
  state.dayIndex = index;
  renderDays();
}

function renderSlots() {
  const box = document.getElementById('eventSlots');
  if (!box) return;
  box.innerHTML = SLOTS.map(s => `
    <button type="button" class="slot ${state.slot === s.start ? 'active' : ''}" onclick="selectEventSlot('${s.start}')">
      ${s.start} - ${s.end}
    </button>`).join('');
}

function selectEventSlot(start) {
  state.slot = start;
  renderSlots();
}

function renderPackageSelect() {
  const select = document.getElementById('eventPackage');
  if (!select) return;
  select.innerHTML = PACKAGES.map(p => `
    <option value="${p.id}">${p.name} — ${p.price} د.ل (${p.people} أشخاص)</option>`).join('');
  select.value = state.package ? state.package.id : (PACKAGES[0] ? PACKAGES[0].id : '');
}

function onPackageChange() {
  const id = Number(document.getElementById('eventPackage').value);
  state.package = PACKAGES.find(p => p.id === id) || null;
  updateTotal();
}

function updateTotal() {
  const el = document.getElementById('eventTotal');
  if (el) el.textContent = `${state.package ? state.package.price : 0} د.ل`;
}

function openEventBooking(packageId) {
  state.dayIndex = 0;
  state.slot = SLOTS[0] ? SLOTS[0].start : null;
  state.package = PACKAGES.find(p => p.id === Number(packageId)) || PACKAGES[0] || null;

  document.getElementById('eventModalTitle').textContent = 'حجز مناسبة';
  document.getElementById('eventModalSub').textContent = state.package ? state.package.name : '';
  document.getElementById('eventFormBody').style.display = 'block';
  document.getElementById('eventSuccessBody').style.display = 'none';
  document.getElementById('eventForm').reset();

  renderDays();
  renderSlots();
  renderPackageSelect();
  document.getElementById('eventPackage').addEventListener('change', onPackageChange);
  updateTotal();

  document.getElementById('eventModal').classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeEventBooking() {
  document.getElementById('eventModal')?.classList.remove('show');
  document.body.style.overflow = '';
}

function submitEventBooking(e) {
  e.preventDefault();

  const name = document.getElementById('eventName').value.trim();
  const phone = document.getElementById('eventPhone').value.trim();
  const whatsapp = document.getElementById('eventWhatsapp').value.trim();
  const notes = document.getElementById('eventNotes').value.trim();

  if (!state.package) { alert('يرجى اختيار الباقة'); return; }
  if (!state.slot) { alert('يرجى اختيار الفترة'); return; }
  if (name.split(/\s+/).length < 3) { alert('يرجى إدخال الاسم الثلاثي كاملاً'); return; }
  if (phone.replace(/\D/g, '').length < 6) { alert('يرجى إدخال رقم هاتف صحيح'); return; }
  if (whatsapp.replace(/\D/g, '').length < 6) { alert('يرجى إدخال رقم واتساب صحيح'); return; }

  const btn = document.getElementById('eventSubmit');
  btn.disabled = true;

  fetch(DATA.bookUrl || '/events/book', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': DATA.csrf || ''
    },
    body: JSON.stringify({
      event_package_id: state.package.id,
      event_date: WEEK_DAYS[state.dayIndex].iso,
      start_time: state.slot,
      notes,
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

      document.getElementById('eventSuccessText').innerHTML =
        `تم حجز <strong>${data.package}</strong><br>الموعد: ${data.event_date} · ${data.time}`;
      document.getElementById('eventRef').textContent = data.reference;

      const qr = document.getElementById('eventQr');
      qr.src = data.qr_url;
      qr.alt = `حجز ${data.reference}`;

      document.getElementById('eventFormBody').style.display = 'none';
      document.getElementById('eventSuccessBody').style.display = 'block';
    })
    .catch(() => alert('تعذّر الاتصال بالخادم، حاول مجددًا'))
    .finally(() => { btn.disabled = false; });
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('eventModal')?.addEventListener('click', e => {
    if (e.target.id === 'eventModal') closeEventBooking();
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEventBooking(); });
});
