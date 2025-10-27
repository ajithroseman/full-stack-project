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




// Secondary Navigation Show/Hide on Scroll - EXACT WORKING CODE
// Secondary Navigation - FIXED
document.addEventListener('DOMContentLoaded', function() {
    const secondaryNav = document.getElementById('secondary-nav');
    const scrollTrigger = document.querySelector('.banner');
    
    if (!secondaryNav || !scrollTrigger) return;
    
    function getScrollTriggerBottom() {
        const rect = scrollTrigger.getBoundingClientRect();
        return rect.bottom + window.pageYOffset;
    }
    
    function handleScroll() {
        const scrollTriggerBottom = getScrollTriggerBottom();
        const currentScrollY = window.pageYOffset;
        
        if (currentScrollY > scrollTriggerBottom - 200) {
            secondaryNav.classList.add('show');
        } else {
            secondaryNav.classList.remove('show');
        }
    }
    
    window.addEventListener('scroll', handleScroll);
    handleScroll();
    
    const secondaryNavLinks = secondaryNav.querySelectorAll('a[href^="#"]');
    
    secondaryNavLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const offset = 120;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - offset;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Remove all active classes first
                secondaryNavLinks.forEach(navLink => {
                    navLink.classList.remove('active');
                });
                // Then add to clicked link
                this.classList.add('active');
            }
        });
    });
    
    function updateActiveNav() {
        const sections = ['#overview2', '#solutions', '#testimonials', '#industries2', '#whyEVTech', '#insights'];
        const scrollPos = window.pageYOffset + 120;
        
        // Remove ALL active classes first
        secondaryNavLinks.forEach(link => link.classList.remove('active'));
        
        // Find which section is in view and set it active
        sections.forEach(sectionId => {
            const section = document.querySelector(sectionId);
            const navLink = secondaryNav.querySelector(`a[href="${sectionId}"]`);
            
            if (section && navLink) {
                const sectionTop = section.offsetTop;
                const sectionBottom = sectionTop + section.offsetHeight;
                
                if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                    navLink.classList.add('active');
                }
            }
        });
    }
    
    window.addEventListener('scroll', updateActiveNav);
    updateActiveNav(); // This will set the initial active state based on scroll position
});

// testimonial section
const slides = document.querySelectorAll('#testimonials .testimonial-slide');
const slider = document.querySelector('#testimonials .testimonial-slider');
const prev = document.querySelector('#testimonials .prev');
const next = document.querySelector('#testimonials .next');
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
document.querySelector('#testimonials').addEventListener('mouseenter', () => clearInterval(autoSlide));
document.querySelector('#testimonials').addEventListener('mouseleave', () => {
  autoSlide = setInterval(() => showSlide(currentIndex + 1), 5000);
});

// why EVTech section
const whySlides = document.querySelectorAll('#whyEVTech .why-slide');
const whyPrev = document.querySelector('#whyEVTech .why-prev');
const whyNext = document.querySelector('#whyEVTech .why-next');
const whyContainer = document.querySelector('#whyEVTech .why-right'); // container element
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
