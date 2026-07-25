(function () {
  'use strict';

  /* ================================================================
     UTILITY: add touch/swipe support to any element
     Calls onLeft() when swiped left (→ next), onRight() when swiped right (→ prev)
     ================================================================ */
  function addSwipe(el, onLeft, onRight) {
    var startX = null, startY = null;
    el.addEventListener('touchstart', function (e) {
      startX = e.touches[0].clientX;
      startY = e.touches[0].clientY;
    }, { passive: true });
    el.addEventListener('touchend', function (e) {
      if (startX === null) return;
      var dx = e.changedTouches[0].clientX - startX;
      var dy = e.changedTouches[0].clientY - startY;
      if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 50) {
        if (dx < 0) { onLeft();  }   // swipe left  → next
        else         { onRight(); }  // swipe right → prev
      }
      startX = startY = null;
    }, { passive: true });
  }

  /* ================================================================
     UTILITY: generic horizontal slider engine
     ================================================================ */
  function createSlider(opts) {
    var track = document.getElementById(opts.trackId);
    if (!track) return null;

    var prevBtn    = document.getElementById(opts.prevId);
    var nextBtn    = document.getElementById(opts.nextId);
    var progressEl = opts.progressId ? document.getElementById(opts.progressId) : null;
    var index = 0;

    function getVisible() {
      if (opts.visibleCount) return opts.visibleCount;
      var w = window.innerWidth;
      if (w < 576) return 1;
      if (w < 768) return 2;
      if (w < 1200) return 3;
      return 4;
    }

    function getCards() {
      return track.querySelectorAll(opts.cardSelector || '.product-card-col');
    }

    function maxIndex() {
      return Math.max(0, getCards().length - getVisible());
    }

    function slide() {
      var cards = getCards();
      if (!cards.length) return;
      var cardW  = cards[0].offsetWidth;
      var gap    = parseInt(getComputedStyle(track).gap) || 20;
      var offset = index * (cardW + gap);
      track.style.transform = 'translateX(-' + offset + 'px)';
      if (progressEl) {
        var pct = maxIndex() === 0 ? 100 : (index / maxIndex()) * 100;
        progressEl.style.width = pct + '%';
      }
    }

    function goPrev() { if (index > 0) { index--; slide(); } }
    function goNext() { if (index < maxIndex()) { index++; slide(); } }

    if (nextBtn) nextBtn.addEventListener('click', goNext);
    if (prevBtn) prevBtn.addEventListener('click', goPrev);

    /* Touch/swipe on the track wrapper */
    var wrap = track.closest('.prod-slider-wrap') || track.parentElement;
    if (wrap) addSwipe(wrap, goNext, goPrev);

    window.addEventListener('resize', function () {
      index = Math.min(index, maxIndex());
      slide();
    });

    slide();
    return {
      slide: slide,
      getVisible: getVisible,
      maxIndex: maxIndex,
      prev: goPrev,
      next: goNext,
      reset: function () { index = 0; slide(); }
    };
  }

  /* ================================================================
     HERO SLIDER — with auto-play + touch swipe
     ================================================================ */
  (function initHero() {
    var inner  = document.getElementById('heroCarouselInner');
    var dots   = document.querySelectorAll('.hero-dot');
    var prevEl = document.getElementById('heroPrev');
    var nextEl = document.getElementById('heroNext');
    if (!inner) return;

    var slides  = inner.querySelectorAll('.hero-slide');
    var current = 0;
    var timer   = null;

    function goTo(n) {
      current = (n + slides.length) % slides.length;
      inner.style.transform = 'translateX(-' + (current * 100) + '%)';
      dots.forEach(function (d, i) {
        d.classList.toggle('active', i === current);
      });
    }

    dots.forEach(function (d, i) {
      d.addEventListener('click', function () {
        clearInterval(timer);
        goTo(i);
        startAuto();
      });
    });

    if (prevEl) prevEl.addEventListener('click', function () { clearInterval(timer); goTo(current - 1); startAuto(); });
    if (nextEl) nextEl.addEventListener('click', function () { clearInterval(timer); goTo(current + 1); startAuto(); });

    /* Touch swipe on hero section */
    var heroSection = document.getElementById('heroSection') || inner.closest('section') || inner.parentElement;
    if (heroSection) {
      addSwipe(heroSection,
        function () { clearInterval(timer); goTo(current + 1); startAuto(); }, // left → next
        function () { clearInterval(timer); goTo(current - 1); startAuto(); }  // right → prev
      );
    }

    function startAuto() {
      clearInterval(timer);
      timer = setInterval(function () { goTo(current + 1); }, 5000);
    }

    goTo(0);
    startAuto();
  })();

  /* ================================================================
     PRODUCT SLIDERS (Sections 1, 2, 3 — used for any named sliders)
     ================================================================ */
  createSlider({ trackId: 'slider1Track', prevId: 'slider1Prev', nextId: 'slider1Next', progressId: 'slider1Progress' });
  createSlider({ trackId: 'slider2Track', prevId: 'slider2Prev', nextId: 'slider2Next', progressId: 'slider2Progress' });
  createSlider({ trackId: 'slider3Track', prevId: 'slider3Prev', nextId: 'slider3Next', progressId: 'slider3Progress' });

  /* ================================================================
     TESTIMONIAL SLIDER — with touch swipe
     ================================================================ */
  (function initTestimonials() {
    var slider  = document.getElementById('testimonialSlider');
    var prevBtn = document.getElementById('testimonialPrev');
    var nextBtn = document.getElementById('testimonialNext');
    if (!slider) return;

    var idx = 0;

    function getVisible() {
      var w = window.innerWidth;
      if (w < 576) return 1;
      if (w < 992) return 2;
      return 3;
    }

    var cards = slider.querySelectorAll('.testimonial-card');
    function maxI() { return Math.max(0, cards.length - getVisible()); }

    function slide() {
      if (!cards.length) return;
      var cardW = cards[0].offsetWidth;
      var gap   = parseInt(getComputedStyle(slider).gap) || 24;
      slider.style.transform = 'translateX(-' + (idx * (cardW + gap)) + 'px)';
    }

    function goPrev() { if (idx > 0) { idx--; slide(); } }
    function goNext() { if (idx < maxI()) { idx++; slide(); } }

    if (prevBtn) prevBtn.addEventListener('click', goPrev);
    if (nextBtn) nextBtn.addEventListener('click', goNext);

    /* Touch swipe on the testimonial wrapper */
    var wrap = slider.closest('.testimonial-slider-wrap') || slider.parentElement;
    if (wrap) addSwipe(wrap, goNext, goPrev);

    window.addEventListener('resize', function () { idx = Math.min(idx, maxI()); slide(); });

    slide();
  })();

})();
