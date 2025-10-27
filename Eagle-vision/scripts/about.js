document.addEventListener("DOMContentLoaded", () => {
  

  //testing
// Tab switching functionality
const tabs = document.querySelectorAll(".tab-btn");
const contents = document.querySelectorAll(".tab-content");

tabs.forEach(tab => {
  tab.addEventListener("click", () => {
    // remove active classes
    tabs.forEach(btn => btn.classList.remove("active"));
    contents.forEach(c => c.classList.remove("active"));

    // add active to clicked tab
    tab.classList.add("active");
    document.getElementById(tab.dataset.tab).classList.add("active");
  });
});

});
const cards = document.querySelectorAll('.service-card');
  const overlay = document.getElementById('detailOverlay');
  const detailHeading = document.getElementById('detailHeading');
  const detailImg = document.getElementById('detailImg');
  const detailPara = document.getElementById('detailPara');
  const closeBtn = document.getElementById('closeBtn');

  cards.forEach(card => {
    card.addEventListener('click', () => {
      detailHeading.textContent = card.dataset.heading;
      detailImg.src = card.dataset.img;
      detailPara.textContent = card.dataset.para;

      overlay.style.display = 'flex'; // show overlay
    });
  });

  closeBtn.addEventListener('click', () => {
    overlay.style.display = 'none';
  });

  // close if clicked outside card
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) {
      overlay.style.display = 'none';
    }
  });


document.addEventListener("DOMContentLoaded", () => {
  // true on phones/tablets and in Chrome DevTools mobile emulation
  const isTouch = window.matchMedia("(hover: none)").matches || 
                  "ontouchstart" in window || navigator.maxTouchPoints > 0;

  if (isTouch) {
    const cards = document.querySelectorAll(".flip-card");

    cards.forEach(card => {
      card.addEventListener("click", (e) => {
        // ignore clicks on interactive elements inside the card
        if (e.target.closest("a, button")) return;
        card.classList.toggle("is-flipped");
      });
    });

    // optional: tap outside to reset all
    document.addEventListener("click", (e) => {
      if (!e.target.closest(".flip-card")) {
        cards.forEach(c => c.classList.remove("is-flipped"));
      }
    }, true);
  }
});

