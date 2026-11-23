@extends('layouts.app')

@section('title', 'سينما بلاس | تذكرة ' . $booking->booking_ref)
@section('robots', 'noindex,nofollow')

@push('styles')
<style>
.ticket-wrap{max-width:600px;margin:46px auto 60px;padding:0 20px}
.ticket{background:#fff;border:1px solid var(--line);border-radius:26px;overflow:hidden;box-shadow:0 44px 90px -46px rgba(0,0,0,.42)}

.ticket-head{position:relative;background:radial-gradient(circle at 82% 8%,rgba(231,29,36,.42),transparent 58%),linear-gradient(135deg,#0b0b14,#2b0a0d);color:#fff;padding:30px 26px 32px;text-align:center}
.ticket-head::after{content:'';position:absolute;left:0;right:0;bottom:-1px;height:14px;background:radial-gradient(circle at 12px 14px,transparent 11px,#fff 12px) repeat-x;background-size:24px 24px}
.ticket-head .ok{width:62px;height:62px;margin:0 auto 14px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,#22c55e,#15803d);box-shadow:0 14px 30px -12px rgba(22,163,74,.75);animation:popIn .45s cubic-bezier(.2,.9,.4,1.25)}
.ticket-head .ok svg{width:30px;height:30px}
@keyframes popIn{from{transform:scale(.5);opacity:0}to{transform:scale(1);opacity:1}}
.ticket-head h1{font-size:21px;font-weight:900;margin-bottom:7px;letter-spacing:-.4px}
.ticket-head p{font-size:13px;color:rgba(255,255,255,.8);line-height:1.7;max-width:360px;margin-inline:auto}

.ticket-body{padding:26px 24px 30px}

.ref{display:flex;align-items:center;justify-content:space-between;gap:14px;background:#fafafa;border:1.5px dashed rgba(231,29,36,.35);border-radius:14px;padding:14px 17px;margin-bottom:22px}
.ref span{font-size:11.5px;color:var(--muted);font-weight:800}
.ref strong{font-size:20px;font-weight:900;letter-spacing:2px;direction:ltr;font-family:'Courier New',monospace;color:#e71d24}

.qr{display:grid;place-items:center;margin-bottom:24px}
.qr img{border:1px solid var(--line);border-radius:18px;padding:11px;background:#fff;box-shadow:0 18px 40px -24px rgba(0,0,0,.35)}

.rows{display:flex;flex-direction:column;border:1px solid var(--line);border-radius:16px;overflow:hidden;background:#fff}
.row{display:flex;justify-content:space-between;gap:14px;padding:13px 17px;font-size:13.5px;border-bottom:1px solid var(--line)}
.row:last-child{border-bottom:none}
.row span{color:var(--muted);font-weight:700;flex-shrink:0}
.row b{font-weight:800;text-align:left}
.row.total{background:linear-gradient(90deg,rgba(231,29,36,.07),transparent)}
.row.total b{color:#e71d24;font-size:16px}

.status{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap}
.badge-s{flex:1;text-align:center;border-radius:13px;padding:13px;font-weight:800;font-size:12.5px;min-width:140px;display:flex;align-items:center;justify-content:center;gap:7px}
.badge-s::before{content:'';width:7px;height:7px;border-radius:50%;background:currentColor;box-shadow:0 0 8px currentColor}
.badge-wait{background:rgba(240,170,20,.14);color:#9a6a00;border:1px solid rgba(240,170,20,.35)}
.badge-ok{background:rgba(16,150,90,.12);color:#0f7a4a;border:1px solid rgba(16,150,90,.3)}

.note{margin-top:20px;background:rgba(231,29,36,.07);border:1.5px dashed rgba(231,29,36,.42);border-radius:14px;padding:15px;font-size:12.5px;line-height:1.7;color:#8b1115;font-weight:700;text-align:center}

.actions{display:flex;gap:10px;margin-top:22px}
.actions .btn{flex:1;justify-content:center}

@media (max-width:560px){
  .ticket-wrap{margin:24px auto 40px;padding:0 14px}
  .ticket-head{padding:26px 20px 28px}
  .ticket-body{padding:20px 18px 24px}
  .row{font-size:12.5px;padding:12px 14px}
  .actions{flex-direction:column}
}
@media print{
  .actions,.floating-cta,header,footer{display:none!important}
  .ticket-wrap{margin:0;max-width:none}
  .ticket{box-shadow:none;border:none}
}
</style>
@endpush

@section('content')
<div class="ticket-wrap">
  <div class="ticket">
    <div class="ticket-head">
      <div class="ok">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
      </div>
      <h1>تم تأكيد طلب الحجز</h1>
      <p>سيتم التواصل معك عبر رقم الواتساب المُدخل لتأكيد الدفع.</p>
    </div>

    <div class="ticket-body">
      <div class="ref">
        <span>رقم الحجز</span>
        <strong>{{ $booking->booking_ref }}</strong>
      </div>

      <div class="qr">
        <img src="{{ $qrUrl }}" alt="QR {{ $booking->booking_ref }}" width="230" height="230">
      </div>

      <div class="rows">
        <div class="row"><span>الفيلم</span><b>{{ $booking->movie->name }}</b></div>
        <div class="row"><span>الباقة</span><b>{{ $booking->package->name }}</b></div>
        <div class="row"><span>التاريخ</span><b>{{ $booking->show_date->format('Y-m-d') }}</b></div>
        <div class="row"><span>الوقت</span><b>{{ $booking->showtime->start_time_short }} — {{ $booking->showtime->end_time_short }}</b></div>
        <div class="row"><span>الاسم</span><b>{{ $booking->full_name }}</b></div>
        <div class="row"><span>الجوال</span><b dir="ltr">{{ \App\Support\Mask::phone($booking->country_code . $booking->phone) }}</b></div>
        <div class="row total"><span>المجموع</span><b>{{ \App\Support\Money::short((float) $booking->price) }}</b></div>
      </div>

      <div class="status">
        <div class="badge-s {{ $booking->payment_status === 'paid' ? 'badge-ok' : 'badge-wait' }}">
          الدفع: {{ $booking->payment_status === 'paid' ? 'تم الدفع' : 'قيد الانتظار' }}
        </div>
        <div class="badge-s {{ $booking->attendance_status === 'attended' ? 'badge-ok' : 'badge-wait' }}">
          الحضور: {{ $booking->attendance_status === 'attended' ? 'تم الحضور' : 'قيد الانتظار' }}
        </div>
      </div>

      <div class="note">⚠️ احفظ رقم الحجز وأظهر رمز QR عند وصولك إلى السينما.</div>

      <div class="actions">
        <a href="{{ route('movies.index') }}" class="btn btn-gold">حجز تذكرة أخرى</a>
        <button type="button" class="btn btn-ghost" onclick="window.print()">طباعة</button>
      </div>
    </div>
  </div>
</div>
@endsection
