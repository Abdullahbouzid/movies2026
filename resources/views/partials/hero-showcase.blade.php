{{--
    Specialty hero showcase: every booking page presents its own inventory
    (movies / games / clubs / event packages) with image, name, details and
    description. Expects $slides, $accent and optional $label.

    The track scrolls with plain CSS (scroll-snap); hero-showcase.js only adds
    arrow buttons, dot navigation and keyboard support, so the content stays
    readable with JavaScript disabled.
--}}
@php
    $slides = $slides ?? [];
    $accent = $accent ?? '#e71d24';
    $label = $label ?? 'الأكثر طلبًا';
@endphp

@if (count($slides))
<section class="hs" dir="rtl" style="--hs-accent:{{ $accent }}">
  <div class="hs-head">
    <div class="hs-title">
      <span class="hs-pill">✨ {{ $label }}</span>
      <h2>{{ $heading ?? 'تعرّف على ما نقدّمه' }}</h2>
    </div>
    <div class="hs-nav" data-hs-nav hidden>
      {{-- RTL: the previous button renders rightmost, so it points right. --}}
      <button type="button" class="hs-arrow" data-hs-prev aria-label="السابق">›</button>
      <button type="button" class="hs-arrow" data-hs-next aria-label="التالي">‹</button>
    </div>
  </div>

  <div class="hs-track" data-hs-track tabindex="0" aria-label="عرض العناصر">
    @foreach ($slides as $slide)
      <article class="hs-card" data-hs-card>
        <div class="hs-media" style="background:{{ $slide['gradient'] }}">
          {{-- Emoji sits underneath; a failed image removes itself and reveals it. --}}
          <span class="hs-emoji" aria-hidden="true">{{ $slide['icon'] }}</span>
          @if (!empty($slide['image']))
            <img src="{{ $slide['image'] }}" alt="{{ $slide['title'] }}"
                 width="600" height="900" loading="lazy" decoding="async"
                 onerror="this.remove()">
          @endif
        </div>
        <div class="hs-body">
          <h3>{{ $slide['title'] }}</h3>
          @if (!empty($slide['meta']))
            <span class="hs-meta">{{ $slide['meta'] }}</span>
          @endif
          @if (!empty($slide['description']))
            <p>{{ $slide['description'] }}</p>
          @endif
          @if (!empty($slide['url']))
            <a class="hs-link" href="{{ $slide['url'] }}">التفاصيل ←</a>
          @endif
        </div>
      </article>
    @endforeach
  </div>

  <div class="hs-dots" data-hs-dots hidden></div>
</section>
@endif
