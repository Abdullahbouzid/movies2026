<div style="text-align:center;padding:8px 0 4px">
  @if ($url)
    <img src="{{ $url }}" alt="QR {{ $reference }}" style="width:220px;height:220px;border-radius:14px;border:1px solid rgba(0,0,0,.1);margin:0 auto;display:block">
    <p style="margin-top:12px;font-family:monospace;font-weight:800;letter-spacing:1px">{{ $reference }}</p>
    <p style="font-size:12.5px;color:#6a6a72;margin-top:6px">امسح الرمز للتحقق من الحجز</p>
  @else
    <p style="color:#6a6a72">لا يوجد رمز لهذا الحجز.</p>
  @endif
</div>
