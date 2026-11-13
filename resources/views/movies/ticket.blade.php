@extends('layouts.app')

@section('title', 'سينما بلاس | تذكرة ' . $booking->booking_ref)

@push('styles')
<style>
.ticket-wrap{max-width:560px;margin:50px auto;padding:0 22px}
.ticket{background:#fff;border:1px solid var(--line);border-radius:24px;overflow:hidden;box-shadow:0 40px 80px -40px rgba(0,0,0,.35)}
.ticket-head{background:linear-gradient(135deg,#111,#8b1115);color:#fff;padding:26px 24px;text-align:center}
.ticket-head .ok{font-size:40px}
.ticket-head h1{font-size:22px;font-weight:900;margin:10px 0 6px}
.ticket-head p{font-size:13.5px;color:rgba(255,255,255,.85)}
.ticket-body{padding:26px 24px}
.qr{display:grid;place-items:center;margin-bottom:22px}
.qr img{border:1px solid var(--line);border-radius:16px;padding:10px;background:#fff}
.ref{text-align:center;font-size:22px;font-weight:900;letter-spacing:1px;margin-bottom:22px}
.ref span{display:block;font-size:12px;color:var(--muted);font-weight:700;letter-spacing:0;margin-bottom:6px}
.rows{display:flex;flex-direction:column;gap:11px}
.row{display:flex;justify-content:space-between;gap:14px;border-bottom:1px dashed var(--line);padding-bottom:11px;font-size:14px}
.row span{color:var(--muted);font-weight:700}
.row b{font-weight:800;text-align:left}
.status{display:flex;gap:10px;margin-top:22px;flex-wrap:wrap}
.badge-s{flex:1;text-align:center;border-radius:12px;padding:12px;font-weight:800;font-size:13px;min-width:140px}
.badge-wait{background:rgba(240,170,20,.14);color:#9a6a00;border:1px solid rgba(240,170,20,.35)}
.badge-ok{background:rgba(16,150,90,.12);color:#0f7a4a;border:1px solid rgba(16,150,90,.3)}
.note{margin-top:20px;background:var(--gold-soft);border:1px solid rgba(231,29,36,.25);border-radius:13px;padding:14px;font-size:13px;color:#8b1115;font-weight:700;text-align:center}
.actions{display:flex;gap:10px;margin-top:22px}
.actions .btn{flex:1;justify-content:center}
</style>
@endpush

@section('content')
<div class="ticket-wrap">
  <div class="ticket">
    <div class="ticket-head">
      <div class="ok">✅</div>
      <h1>تم تأكيد طلب الحجز!</h1>
      <p>سيتم التواصل معك عبر رقم الواتساب المُدخل لتأكيد الدفع.</p>
    </div>
    <div class="ticket-body">
      <div class="ref"><span>رقم الحجز</span>{{ $booking->booking_ref }}</div>

      <div class="qr">
        <img src="{{ $qrUrl }}" alt="QR {{ $booking->booking_ref }}" width="240" height="240">
      </div>

      <div class="rows">
        <div class="row"><span>الفيلم</span><b>{{ $booking->movie->name }}</b></div>
        <div class="row"><span>الباقة</span><b>{{ $booking->package->name }}</b></div>
        <div class="row"><span>التاريخ</span><b>{{ $booking->show_date->format('Y-m-d') }}</b></div>
        <div class="row"><span>الوقت</span><b>{{ $booking->showtime->start_time_short }} — {{ $booking->showtime->end_time_short }}</b></div>
        <div class="row"><span>الاسم</span><b>{{ $booking->full_name }}</b></div>
        <div class="row"><span>الجوال</span><b>{{ $booking->country_code }} {{ $booking->phone }}</b></div>
        <div class="row"><span>المجموع</span><b>{{ rtrim(rtrim(number_format((float) $booking->price, 2), '0'), '.') }} ﷼</b></div>
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
