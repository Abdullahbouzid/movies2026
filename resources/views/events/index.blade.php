@extends('layouts.app')

@section('title', 'سينما بلاس | حجز مناسبات داخل مقهى الكلية')

@section('meta')
@include('partials.meta', [
    'title' => 'سينما بلاس | حجز مناسبات داخل مقهى الكلية',
    'description' => 'احجز مناسباتك داخل مقهى الكلية: أعياد ميلاد، تجمعات عائلية وجلسات خاصة — باقات مرنة، أسعار واضحة، وتأكيد فوري برمز QR.',
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
.ev-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:22px;margin-top:26px}
.ev-card{background:var(--card);border:1px solid var(--line);border-radius:20px;padding:22px;display:flex;flex-direction:column;gap:12px;transition:.25s}
.ev-card:hover{transform:translateY(-4px);box-shadow:0 28px 60px -40px rgba(0,0,0,.35)}
.ev-icon{width:52px;height:52px;border-radius:15px;background:linear-gradient(135deg,var(--gold),#8b1115);display:grid;place-items:center;font-size:24px;color:#fff}
.ev-card h3{font-size:17.5px;font-weight:900}
.ev-card p{font-size:13.5px;color:var(--muted);line-height:1.75}
.ev-meta{display:flex;gap:14px;font-size:12.5px;color:var(--muted);font-weight:700;flex-wrap:wrap}
.ev-price{font-size:22px;font-weight:900;color:var(--gold);margin-top:auto}
.ev-price small{font-size:12px;color:var(--muted);font-weight:700}
.empty{text-align:center;color:var(--muted);font-weight:700;padding:60px 0}
.overlay{position:fixed;inset:0;background:rgba(6,6,12,.6);backdrop-filter:blur(6px);display:none;align-items:center;justify-content:center;z-index:200;padding:18px}
.overlay.show{display:flex}
.sheet{background:#fff;border-radius:22px;width:100%;max-width:520px;max-height:92vh;overflow:auto;box-shadow:0 40px 90px -30px rgba(0,0,0,.5)}
.sheet-head{padding:20px 22px 14px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:flex-start;gap:12px;position:sticky;top:0;background:#fff;z-index:2}
.sheet-head h3{font-size:17px;font-weight:900}
.sheet-head p{font-size:12.5px;color:var(--muted);font-weight:700;margin-top:4px}
.x{border:none;background:rgba(0,0,0,.05);width:32px;height:32px;border-radius:50%;font-size:19px;cursor:pointer;line-height:1;color:var(--muted)}
.sheet-body{padding:18px 22px 24px;display:flex;flex-direction:column;gap:14px}
.f label{display:block;font-size:12.5px;font-weight:800;margin-bottom:7px}
.f input,.f select,.f textarea{width:100%;font-family:inherit;font-size:14px;padding:11px 13px;border:1px solid var(--line);border-radius:11px;background:var(--card-2);color:var(--text);font-weight:600}
.f textarea{resize:vertical;min-height:76px}
.f input:focus,.f select:focus,.f textarea:focus{outline:none;border-color:var(--gold);background:#fff}
.days{display:flex;gap:8px;overflow-x:auto;padding-bottom:4px}
.day{flex:0 0 auto;border:1px solid var(--line);background:var(--card-2);border-radius:12px;padding:8px 14px;font-family:inherit;font-weight:800;font-size:12.5px;cursor:pointer;text-align:center;line-height:1.35}
.day small{display:block;font-size:11px;color:var(--muted);font-weight:700;margin-top:2px}
.day.active{background:linear-gradient(135deg,var(--gold),#8b1115);color:#fff;border-color:transparent}
.day.active small{color:rgba(255,255,255,.82)}
.slots{display:grid;grid-template-columns:repeat(auto-fill,minmax(104px,1fr));gap:8px}
.slot{border:1px solid var(--line);background:var(--card-2);border-radius:11px;padding:10px 8px;font-family:inherit;font-weight:800;font-size:12.5px;cursor:pointer}
.slot.active{background:linear-gradient(135deg,var(--gold),#8b1115);color:#fff;border-color:transparent}
.total{display:flex;justify-content:space-between;align-items:center;background:var(--card-2);border-radius:13px;padding:14px 16px;font-weight:800}
.total strong{font-size:20px;color:var(--gold)}
.success{text-align:center;padding:12px 0 4px}
.success .ico{width:60px;height:60px;border-radius:50%;background:rgba(16,150,90,.12);color:#0f7a4a;display:grid;place-items:center;font-size:30px;margin:0 auto 14px}
.success h3{font-size:18px;font-weight:900;margin-bottom:8px}
.success p{font-size:13.5px;color:var(--muted);font-weight:700;line-height:1.9}
.success img{width:170px;margin:16px auto 0;display:block;border-radius:12px;border:1px solid var(--line)}
.ref{font-family:monospace;font-size:16px;font-weight:900;letter-spacing:1px;margin-top:10px}
</style>
@endpush

@section('content')
<div class="container">
  <div class="page-head">
    <h1>حجز مناسبات داخل مقهى الكلية</h1>
    <p>أعياد ميلاد، مناسبات عائلية واجتماعات عمل — اختر الباقة والفترة الزمنية.</p>
  </div>

  @include('partials.hero-showcase', [
    'slides' => $showcase,
    'accent' => '#b45309',
    'label' => 'باقات المناسبات',
    'heading' => 'تعرّف على باقات المناسبات',
  ])

  @if (session('booked_ref'))
    <div class="alert alert-success">تم تأكيد حجزك بنجاح — رقم الحجز: {{ session('booked_ref') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
  @endif

  <div class="ev-grid">
    @forelse ($packages as $package)
      <article class="ev-card">
        <div class="ev-icon">🎉</div>
        <h3>{{ $package->name }}</h3>
        <p>{{ $package->includes }}</p>
        <div class="ev-meta">
          <span>👥 {{ $package->people_count }} أشخاص</span>
          <span>⏱ 3 ساعات</span>
        </div>
        <div class="ev-price">
          {{ \App\Support\Money::short((float) $package->price) }}
          <small>/ للباقة</small>
        </div>
        <button class="btn btn-gold" style="justify-content:center" onclick="openEventBooking({{ $package->id }})">احجز الباقة</button>
      </article>
    @empty
      <p class="empty">لا توجد باقات متاحة حاليًا.</p>
    @endforelse
  </div>
</div>

<div class="overlay" id="eventModal">
  <div class="sheet">
    <div class="sheet-head">
      <div>
        <h3 id="eventModalTitle">حجز مناسبة</h3>
        <p id="eventModalSub">—</p>
      </div>
      <button class="x" onclick="closeEventBooking()" aria-label="إغلاق">×</button>
    </div>

    <div class="sheet-body" id="eventFormBody">
      <form id="eventForm" onsubmit="submitEventBooking(event)" novalidate>
        <div class="f" style="margin-bottom:14px">
          <label>اليوم</label>
          <div class="days" id="eventDays"></div>
        </div>

        <div class="f" style="margin-bottom:14px">
          <label>الفترة</label>
          <div class="slots" id="eventSlots"></div>
        </div>

        <div class="f" style="margin-bottom:14px">
          <label>الباقة</label>
          <select id="eventPackage"></select>
        </div>

        <div class="f" style="margin-bottom:14px">
          <label>الاسم الثلاثي</label>
          <input type="text" id="eventName" placeholder="مثال: محمد أحمد العلي" required>
        </div>

        <div class="f" style="margin-bottom:14px">
          <label>رقم الهاتف</label>
          <input type="tel" id="eventPhone" placeholder="0913504510" required dir="ltr">
        </div>

        <div class="f" style="margin-bottom:14px">
          <label>رقم الواتساب</label>
          <input type="tel" id="eventWhatsapp" placeholder="0925271793" required dir="ltr">
        </div>

        <div class="f" style="margin-bottom:14px">
          <label>ملاحظات (اختياري)</label>
          <textarea id="eventNotes" placeholder="عدد الضيوف، تجهيزات خاصة…"></textarea>
        </div>

        <div class="total">
          <span>المجموع</span>
          <strong id="eventTotal">0 د.ل</strong>
        </div>

        <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;margin-top:14px" id="eventSubmit">تأكيد الحجز</button>
      </form>
    </div>

    <div class="sheet-body" id="eventSuccessBody" style="display:none">
      <div class="success">
        <div class="ico">✓</div>
        <h3>تم تأكيد الحجز!</h3>
        <p id="eventSuccessText">—</p>
        <div class="ref" id="eventRef">EV-000000</div>
        <img id="eventQr" alt="رمز QR للحجز">
        <p style="margin-top:14px">أظهر رمز QR عند الوصول.</p>
        <button class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:14px" onclick="closeEventBooking()">تم</button>
      </div>
    </div>
  </div>
</div>

<script>window.EVENTS = @json($payload);</script>
@endsection

@push('scripts')
<script src="@versioned('js/hero-showcase.js')"></script>
<script src="@versioned('js/events.js')"></script>
@endpush
