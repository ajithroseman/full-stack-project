const navLinks = document.querySelectorAll('.nav-menu a');

/* ===== Fixed Hero + Bottom→Top Watermark — split in PX, clamped to sticky bar ===== */
/* ================= Fixed Hero + Bottom→Top Watermark (zero-lag, glued to navbar) ================= */
(function () {
  "use strict";
  const $ = (s, r) => (r || document).querySelector(s);

  // cache sizes to avoid extra layout while scrolling
  let vh = window.innerHeight || 1;
  let heroH = 1;

  function recalcSizes(hero) {
    vh = window.innerHeight || 1;
    heroH = hero ? (hero.getBoundingClientRect().height || 1) : 1;
  }

  function init() {
    const hero = $('#hero') || $('.banner');
    if (!hero) return;

    // Use the banner <img> as the background for fixed layers
    const img = hero.querySelector('img');
    if (img) {
      const applyUrl = () =>
        document.documentElement.style.setProperty('--hero-url', `url('${img.src}')`);
      if (img.complete) applyUrl(); else img.addEventListener('load', applyUrl, { once: true });
    }

    const sec = $('.section-nav'); // sticky secondary navbar

    // main update function (now accepts y from rAF)
    let lastScrollYForDir = 0;
let overlapTimeout = null;
const OVERLAP_ACTIVE_PX = 6;   // overlap while active (tweak 4..10)
const OVERLAP_IDLE_PX = 2;     // default overlap when idle
const OVERLAP_IDLE_DELAY = 140; // ms to wait after scroll stops before reverting

function update(yFromRAF) {
  const y = (typeof yFromRAF === 'number') ? yFromRAF : (window.scrollY || document.documentElement.scrollTop || 0);

  // progress through hero
  const p = Math.max(0, Math.min(1, y / heroH));

  // desired split
  const desiredSplitPx = (1 - p) * vh;

  // navbar bottom
  let navBottomPx = 0;
  if (sec) {
    const r  = sec.getBoundingClientRect();
    const cs = getComputedStyle(sec);
    const mb = parseFloat(cs.marginBottom) || 0;
    navBottomPx = Math.max(0, r.bottom + mb);
  }

  const offset = Math.max(0, vh - navBottomPx);
  const EPS = 4.5;

  let splitPx = desiredSplitPx - offset;
  splitPx = Math.max(navBottomPx - EPS, splitPx);
  splitPx = Math.min(vh, Math.max(0, splitPx));

  // --- ensure split remains slightly BELOW the nav while scrolling down ---
  const scrollingDown = y > lastScrollYForDir;
  if (scrollingDown) {
    const SAFETY = 1;
    const target = Math.max(splitPx, navBottomPx + SAFETY);
    const SMOOTH = 0.28;
    splitPx = splitPx * (1 - SMOOTH) + target * SMOOTH;
  }

  // --- integer rounding bias: use ceil when fast-scrolling down, else round normally ---
  // detect a "fast" frame skip: difference > small threshold (tweak threshold if needed)
  const dy = Math.abs(y - lastScrollYForDir);
  const FAST_THRESHOLD = 12; // px/frame — tweak 8..20 depending on device
  let finalSplitPx;
  if (scrollingDown && dy > FAST_THRESHOLD) {
    // bias up to avoid hairline on fast downward motion
    finalSplitPx = Math.ceil(splitPx);
  } else {
    finalSplitPx = Math.round(splitPx);
  }

  // --- dynamic clip-overlap: increase while actively scrolling down fast, revert after idle ---
  if (scrollingDown && dy > FAST_THRESHOLD) {
    // apply larger overlap immediately
    document.documentElement.style.setProperty('--clip-overlap', OVERLAP_ACTIVE_PX + 'px');

    // reset idle timeout
    if (overlapTimeout) clearTimeout(overlapTimeout);
    overlapTimeout = setTimeout(() => {
      document.documentElement.style.setProperty('--clip-overlap', OVERLAP_IDLE_PX + 'px');
      overlapTimeout = null;
    }, OVERLAP_IDLE_DELAY);
  }

  lastScrollYForDir = y;

  // set the CSS var for split
  document.documentElement.style.setProperty('--split', finalSplitPx + 'px');
}
    // set sizes
    recalcSizes(hero);

    // handle resize
    window.addEventListener('resize', () => { recalcSizes(hero); update(); });
    window.addEventListener('orientationchange', () => { recalcSizes(hero); update(); });
    window.addEventListener('load', () => { recalcSizes(hero); update(); });

    // rAF throttle scroll handler
    let lastY = 0;
    let ticking = false;
    window.addEventListener('scroll', () => {
      lastY = window.scrollY || document.documentElement.scrollTop || 0;
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(() => {
          update(lastY);
          ticking = false;
        });
      }
    }, { passive: true });

    update(); // first paint
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();




// highlight active link on scroll
const sections = document.querySelectorAll('section[id]');

window.addEventListener('scroll', () => {
  let scrollPos = window.scrollY + 100; // offset for navbar height

  sections.forEach(section => {
    const top = section.offsetTop;
    const bottom = top + section.offsetHeight;

    const link = document.querySelector(`.nav-menu a[href="#${section.id}"]`);

    if (scrollPos >= top && scrollPos <= bottom) {
      navLinks.forEach(l => l.classList.remove('active'));
      if (link) link.classList.add('active');
    }
  });
});


// Secondary nav bar - FIXED version (replaces all secondary nav code)
document.addEventListener('DOMContentLoaded', () => {
  const sectionNavLinks = document.querySelectorAll('.section-nav a');
  const sections = Array.from(sectionNavLinks).map(link => document.querySelector(link.hash));
  
  let isProgrammaticScroll = false;
  let programmaticScrollTimeout = null;

  function highlightSectionNav() {
    // Don't update during programmatic scroll (click navigation)
    if (isProgrammaticScroll) return;
    
    const scrollPos = window.scrollY + 120;
    let activeFound = false;

    sections.forEach((sec, i) => {
      if (!sec) return;

      const sectionTop = sec.offsetTop;
      const sectionBottom = sectionTop + sec.offsetHeight;

      if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
        sectionNavLinks.forEach(l => l.classList.remove('active'));
        sectionNavLinks[i].classList.add('active');
        activeFound = true;
      }
    });
  }

  // Smooth scroll for secondary nav menu
  sectionNavLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();

      const targetId = link.getAttribute('href');
      const target = document.querySelector(targetId);
      
      if (target) {
        const offset = 120;
        const targetPosition = target.offsetTop - offset;

        // Set flag to prevent scroll-based updates
        isProgrammaticScroll = true;
        if (programmaticScrollTimeout) {
          clearTimeout(programmaticScrollTimeout);
        }

        // Update active class immediately
        sectionNavLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');

        // Scroll to target
        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });

        // Reset flag after scroll should be complete
        programmaticScrollTimeout = setTimeout(() => {
          isProgrammaticScroll = false;
          programmaticScrollTimeout = null;
        }, 1000);
      }
    });
  });

  // Use a single scroll listener with proper throttling
  let scrollTimeout;
  function handleScroll() {
    if (!scrollTimeout) {
      scrollTimeout = setTimeout(() => {
        highlightSectionNav();
        scrollTimeout = null;
      }, 50);
    }
  }

  // Add scroll listener
  window.addEventListener('scroll', handleScroll, { passive: true });

  // Initial highlight
  highlightSectionNav();
});


// testimonial section
const slides = document.querySelectorAll('#Focus-areas .testimonial-slide');
const slider = document.querySelector('#Focus-areas .testimonial-slider');
const prev = document.querySelector('#Focus-areas .prev');
const next = document.querySelector('#Focus-areas .next');
let currentIndex = 0;

function showSlide(index) {
  if (index >= slides.length) currentIndex = 0;
  else if (index < 0) currentIndex = slides.length - 1;
  else currentIndex = index;

  slider.style.transform = `translateX(-${currentIndex * 100}%)`;
}

// Navigation buttons
prev.addEventListener('click', () => showSlide(currentIndex - 1));
next.addEventListener('click', () => showSlide(currentIndex + 1));

// Auto slide
let autoSlide = setInterval(() => showSlide(currentIndex + 1), 5000);

// Pause on hover
document.querySelector('#Focus-areas').addEventListener('mouseenter', () => clearInterval(autoSlide));
document.querySelector('#Focus-areas').addEventListener('mouseleave', () => {
  autoSlide = setInterval(() => showSlide(currentIndex + 1), 5000);
});

// why EVTech section
const whySlides = document.querySelectorAll('#Latest-thinking .why-slide');
const whyPrev = document.querySelector('#Latest-thinking .why-prev');
const whyNext = document.querySelector('#Latest-thinking .why-next');
const whyContainer = document.querySelector('#Latest-thinking .why-right'); // container element
let whyIndex = 0;

function showWhySlide(index) {
  if (index >= whySlides.length) whyIndex = 0;
  else if (index < 0) whyIndex = whySlides.length - 1;
  else whyIndex = index;

  whySlides.forEach(slide => slide.classList.remove('active'));
  whySlides[whyIndex].classList.add('active');

   // Adjust container height dynamically
  whyContainer.style.height = whySlides[whyIndex].offsetHeight + 'px';
}
// Initialize container height on page load
window.addEventListener('load', () => {
  whyContainer.style.height = whySlides[whyIndex].offsetHeight + 'px';
});

whyPrev.addEventListener('click', () => showWhySlide(whyIndex - 1));
whyNext.addEventListener('click', () => showWhySlide(whyIndex + 1));

// Auto-slide every 6 seconds
setInterval(() => showWhySlide(whyIndex + 1), 6000);

// Show first slide initially
showWhySlide(whyIndex);
