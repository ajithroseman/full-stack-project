(function(){
  const section = document.getElementById('key-features');
  if (!section) return;

  if (!('IntersectionObserver' in window)) {
    // Fallback: always show if IO not supported
    section.classList.add('in-view');
    return;
  }

  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        section.classList.add('in-view');   // trigger slide
      } else {
        section.classList.remove('in-view'); // reset when out of view
      }
    });
  }, {
    threshold: 0.18,
    rootMargin: '0px 0px -10% 0px'
  });

  io.observe(section);
})();


// left tab content on right
(function() {
  const list = document.querySelectorAll('.lr-list button');
  const panels = document.querySelectorAll('.tab-panel');

  if (!list.length) return;

  function activate(btn, { scroll = false } = {}) {
    // Deactivate all
    list.forEach(b => {
      b.setAttribute('aria-selected', 'false');
      b.tabIndex = -1;
    });
    panels.forEach(p => {
      p.hidden = true;
      p.classList.remove('show');
    });

    // Activate selected
    btn.setAttribute('aria-selected', 'true');
    btn.tabIndex = 0;

    const targetId = btn.dataset.target;
    const targetPanel = document.getElementById(targetId);
    if (targetPanel) {
      targetPanel.hidden = false;
      targetPanel.classList.add('show');
    }

    // Only scroll when user really interacted
    if (scroll) {
      btn.scrollIntoView({
        block: 'nearest',
        inline: 'center',
        behavior: 'smooth'
      });
    }
  }

  // Initialize first tab (no scrolling)
  activate(list[0]);

  // Click handlers
  list.forEach((btn) => {
    btn.addEventListener('click', () => {
      activate(btn, { scroll: true });
    });

    // Keyboard navigation
    btn.addEventListener('keydown', (e) => {
      const idx = Array.prototype.indexOf.call(list, btn);
      let nextBtn = null;

      switch (e.key) {
        case 'ArrowDown':
        case 'ArrowRight':
          e.preventDefault();
          nextBtn = list[(idx + 1) % list.length];
          break;
        case 'ArrowUp':
        case 'ArrowLeft':
          e.preventDefault();
          nextBtn = list[(idx - 1 + list.length) % list.length];
          break;
        case 'Home':
          e.preventDefault();
          nextBtn = list[0];
          break;
        case 'End':
          e.preventDefault();
          nextBtn = list[list.length - 1];
          break;
        case 'Enter':
        case ' ':
          e.preventDefault();
          activate(btn); // no scroll on keyboard activation
          return;
      }

      if (nextBtn) {
        nextBtn.focus();
      }
    });
  });
})();
