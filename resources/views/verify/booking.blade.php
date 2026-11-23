@extends('layouts.app')

@section('title', 'سينما بلاس | التحقق من الحجز')
@section('robots', 'noindex,nofollow')

@push('styles')
<style>
.verify-wrap{max-width:620px;margin:44px auto;padding:0 20px}
.verify-card{background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:0 30px 60px -40px rgba(0,0,0,.28);padding:26px 24px}
.verify-head{display:flex;align-items:center;gap:12px;border-radius:16px;background:var(--card-2);padding:16px 18px;margin-bottom:20px}
.verify-head .ok{width:38px;height:38px;border-radius:50%;display:grid;place-items:center;font-size:20px;flex-shrink:0}
.verify-head .ok.paid{background:rgba(16,150,90,.12);color:#0f7a4a;border:1px solid rgba(16,150,90,.3)}
.verify-head .ok.wait{background:rgba(240,170,20,.14);color:#9a6a00;border:1px solid rgba(240,170,20,.35)}
.verify-head h1{font-size:17px;font-weight:900;margin:0}
.verify-head p{font-size:12.5px;color:var(--muted);margin:4px 0 0}
.v-rows{display:flex;flex-direction:column;gap:10px}
.v-row{display:flex;justify-content:space-between;align-items:center;gap:14px;border-radius:12px;padding:11px 14px;font-size:13.5px;border:1px dashed var(--line)}
.v-row span{color:var(--muted);font-weight:700}
.v-row b{font-weight:800;text-align:left}
.badge-s{border-radius:9px;padding:5px 12px;font-weight:800;font-size:12px}
.badge-ok{background:rgba(16,150,90,.12);color:#0f7a4a;border:1px solid rgba(16,150,90,.3)}
.badge-wait{background:rgba(240,170,20,.14);color:#9a6a00;border:1px solid rgba(240,170,20,.35)}
.verify-note{margin-top:20px;background:var(--gold-soft);border:1px solid rgba(231,29,36,.25);border-radius:13px;padding:14px;font-size:13px;color:#8b1115;font-weight:700;text-align:center}
.verify-ok{margin-top:16px;background:rgba(16,150,90,.10);border:1px solid rgba(16,150,90,.3);border-radius:13px;padding:13px;font-size:13px;color:#0f7a4a;font-weight:800;text-align:center}
.verify-checkin{width:100%;font-family:inherit;font-size:14.5px;font-weight:800;padding:13px 20px;border-radius:12px;border:none;cursor:pointer;background:linear-gradient(135deg,#10965a,#0b6b40);color:#fff;box-shadow:0 12px 28px -14px rgba(16,150,90,.8);transition:.2s}
.verify-checkin:hover{transform:translateY(-2px)}
</style>
@endpush

@section('content')
<div class="verify-wrap">
  <div class="verify-card">
    <div class="verify-head">
      <div class="ok {{ $payment_status === 'paid' ? 'paid' : 'wait' }}">{{ $payment_status === 'paid' ? '✓' : '!' }}</div>
      <div>
        <h1>{{ $title }} — {{ $reference }}</h1>
        <p>{{ $customer }}</p>
      </div>
    </div>

    <div class="v-rows">
      <div class="v-row"><span>الاسم</span><b>{{ $customer }}</b></div>
      <div class="v-row"><span>الهاتف</span><b dir="ltr">{{ $phone }}</b></div>
      <div class="v-row"><span>واتساب</span><b dir="ltr">{{ $whatsapp }}</b></div>
      <div class="v-row"><span>الخدمة</span><b>{{ $item ?: '—' }}</b></div>
      <div class="v-row"><span>الباقة</span><b>{{ $package ?: '—' }}</b></div>
      <div class="v-row"><span>التاريخ</span><b>{{ $date ?: '—' }}</b></div>
      <div class="v-row"><span>الوقت</span><b dir="ltr">{{ $time ?: '—' }}</b></div>
      @if (! empty($people))
        <div class="v-row"><span>عدد الأشخاص</span><b>{{ $people }}</b></div>
      @endif
      @foreach (($extra ?? []) as $label => $value)
        @if ($value)
          <div class="v-row"><span>{{ $label }}</span><b>{{ $value }}</b></div>
        @endif
      @endforeach
      <div class="v-row"><span>المبلغ</span><b>{{ \App\Support\Money::short($price) }}</b></div>
      <div class="v-row">
        <span>حالة الدفع</span>
        <b class="badge-s {{ $payment_status === 'paid' ? 'badge-ok' : 'badge-wait' }}">{{ $payment_status === 'paid' ? 'مدفوع' : 'قيد الانتظار' }}</b>
      </div>
      <div class="v-row">
        <span>حالة الحضور</span>
        <b class="badge-s {{ $attendance_status === 'attended' ? 'badge-ok' : 'badge-wait' }}">{{ $attendance_status === 'attended' ? 'تم الحضور' : 'قيد الانتظار' }}</b>
      </div>
      <div class="v-row"><span>تاريخ الحجز</span><b>{{ $created_at?->format('d/m/Y H:i') }}</b></div>
    </div>

    @if (session('checked_in'))
      <div class="verify-ok">✓ تم تسجيل حضور هذا الحجز.</div>
    @endif

    @if ($payment_status !== 'paid')
      <div class="verify-note">هذا الحجز لم يُدفع بعد — يرجى تحصيل المبلغ قبل تسليم الخدمة.</div>
    @endif

    @if ($attendance_status !== 'attended')
      <form method="POST" action="{{ route('verify.check-in', ['type' => $type, 'ref' => $reference]) }}" style="margin-top:14px">
        @csrf
        <button type="submit" class="verify-checkin">تسجيل الحضور</button>
      </form>
    @endif
  </div>
</div>
@endsection
