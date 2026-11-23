/* Hero showcase: arrow buttons, dot navigation and keyboard scrolling.
   Everything here is an enhancement — the track is a plain scroll container. */
(function () {
  'use strict';

  function initShowcase(root) {
    var track = root.querySelector('[data-hs-track]');
    var nav = root.querySelector('[data-hs-nav]');
    var dotsBox = root.querySelector('[data-hs-dots]');
    var cards = track ? Array.prototype.slice.call(track.querySelectorAll('[data-hs-card]')) : [];
    if (!track || cards.length < 2) return;

    var prev = root.querySelector('[data-hs-prev]');
    var next = root.querySelector('[data-hs-next]');
    var rtl = getComputedStyle(root).direction === 'rtl';

    function step() {
      var card = cards[0];
      var gap = parseFloat(getComputedStyle(track).columnGap || '0') || 0;
      return card.getBoundingClientRect().width + gap;
    }

    function maxScroll() {
      return track.scrollWidth - track.clientWidth;
    }

    function offset() {
      return Math.abs(track.scrollLeft);
    }

    function sync() {
      var max = maxScroll();
      var atStart = offset() <= 1;
      var atEnd = offset() >= max - 1;

      // In RTL, scrollLeft runs 0 → negative, so "next" is the lower bound.
      if (rtl) {
        if (prev) prev.disabled = atEnd;
        if (next) next.disabled = atStart;
      } else {
        if (prev) prev.disabled = atStart;
        if (next) next.disabled = atEnd;
      }

      if (!dotsBox) return;
      var active = max <= 0 ? 0 : Math.round(offset() / step());
      active = Math.max(0, Math.min(active, cards.length - 1));
      Array.prototype.forEach.call(dotsBox.children, function (dot, i) {
        dot.setAttribute('aria-current', i === active ? 'true' : 'false');
      });
    }

    function scrollBy(direction) {
      var delta = step() * direction;
      track.scrollBy({ left: rtl ? -delta : delta, behavior: 'smooth' });
    }

    if (nav) nav.hidden = false;

    if (prev) prev.addEventListener('click', function () { scrollBy(-1); });
    if (next) next.addEventListener('click', function () { scrollBy(1); });

    if (dotsBox) {
      dotsBox.hidden = false;
      cards.forEach(function (card, i) {
        var dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'hs-dot';
        dot.setAttribute('aria-label', 'العنصر ' + (i + 1));
        dot.addEventListener('click', function () {
          var left = i * step();
          track.scrollTo({ left: rtl ? -left : left, behavior: 'smooth' });
        });
        dotsBox.appendChild(dot);
      });
    }

    track.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowLeft') { scrollBy(rtl ? 1 : -1); event.preventDefault(); }
      if (event.key === 'ArrowRight') { scrollBy(rtl ? -1 : 1); event.preventDefault(); }
    });

    var ticking = false;
    track.addEventListener('scroll', function () {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(function () { sync(); ticking = false; });
    }, { passive: true });

    window.addEventListener('resize', sync);
    sync();
  }

  function boot() {
    Array.prototype.forEach.call(document.querySelectorAll('.hs'), initShowcase);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
