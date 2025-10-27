document.addEventListener("DOMContentLoaded", () => {
 

  // -------------------------
  // Unified Overlay for ALL Read More
  // -------------------------
  const overlay = document.getElementById("detailOverlay");
  const closeBtn = overlay.querySelector(".close-btn");

  document.querySelectorAll(".read-more-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".flip-card-back, .industry-card");
      if (!card) return;

      overlay.querySelector(".detail-title").textContent =
        card.querySelector("h2")?.textContent || card.querySelector("h3")?.textContent || '';
      overlay.querySelector(".detail-subtitle").textContent =
        card.querySelector("h3")?.textContent || '';
      overlay.querySelector(".detail-desc").textContent =
        card.querySelector("p")?.textContent || '';
      overlay.querySelector(".detail-img").src =
        btn.getAttribute("data-image") || card.querySelector("img")?.src || '';

      overlay.style.display = "flex";
      document.body.style.overflow = "hidden"; // stop background scroll
    });
  });

  closeBtn.addEventListener("click", () => {
    overlay.style.display = "none";
    document.body.style.overflow = '';
  });

  overlay.addEventListener("click", e => {
    if (e.target === overlay) {
      overlay.style.display = "none";
      document.body.style.overflow = '';
    }
  });

  // -------------------------
  // Industry Cards: Drag + Auto-scroll
  // -------------------------
  const slider = document.querySelector('.industry-grid');
  if (!slider) return;

  let isDown = false, startX, scrollLeft, autoScrollInterval;
  let dragThreshold = 5;
  let hasDragged = false;

  function startAutoScroll() {
    stopAutoScroll();
    autoScrollInterval = setInterval(() => {
      slider.scrollLeft += 1.2;
      if (slider.scrollLeft >= slider.scrollWidth / 2) slider.scrollLeft = 0;
    }, 16);
  }
  function stopAutoScroll() { clearInterval(autoScrollInterval); }

  // Mouse events
  slider.addEventListener('mousedown', e => {
    if (e.target.closest('.read-more-btn')) return;
    isDown = true; hasDragged = false;
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
    slider.classList.add('active');
    stopAutoScroll();
  });

  slider.addEventListener('mousemove', e => {
    if (!isDown) return;
    const x = e.pageX - slider.offsetLeft;
    if (Math.abs(x - startX) > dragThreshold) hasDragged = true;
    if (hasDragged) slider.scrollLeft = scrollLeft - (x - startX) * 1.5;
  });

  slider.addEventListener('mouseup', e => {
    isDown = false; slider.classList.remove('active'); startAutoScroll();
    if (hasDragged) e.preventDefault();
  });
  slider.addEventListener('mouseleave', () => { isDown = false; slider.classList.remove('active'); startAutoScroll(); });

  // Touch events
  slider.addEventListener('touchstart', e => {
    if (e.target.closest('.read-more-btn')) return;
    isDown = true; hasDragged = false;
    startX = e.touches[0].pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
    stopAutoScroll();
  }, { passive: true });

  slider.addEventListener('touchmove', e => {
    if (!isDown) return;
    const x = e.touches[0].pageX - slider.offsetLeft;
    if (Math.abs(x - startX) > dragThreshold) hasDragged = true;
    if (hasDragged) slider.scrollLeft = scrollLeft - (x - startX) * 1.5;
  }, { passive: true });

  slider.addEventListener('touchend', e => {
    isDown = false; startAutoScroll();
    if (hasDragged) e.preventDefault();
  }, { passive: false });

  startAutoScroll(); // initial auto-scroll
});
