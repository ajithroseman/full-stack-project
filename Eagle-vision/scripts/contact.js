
document.addEventListener('DOMContentLoaded', () => {
  // --- Safe copy-to-clipboard handler (no-op if no elements) ---

  // --- Map iframe fallback logic ---
  (function(){
    const iframe = document.getElementById('map-iframe');
    const fallback = document.getElementById('map-fallback');
    if (!iframe || !fallback) return; // nothing to do if elements missing

    let switched = false;
    const timer = setTimeout(() => {
      try {
        // Accessing iframe.contentWindow.location will throw for cross-origin,
        // so we rely on the catch to show fallback when appropriate.
        const ok = iframe && iframe.contentWindow && iframe.contentWindow.location;
      } catch (e) {
        fallback.style.display = 'block';
        iframe.style.display = 'none';
        switched = true;
      }
    }, 2500);

    // If offline show fallback immediately
    if (!navigator.onLine) {
      clearTimeout(timer);
      fallback.style.display = 'block';
      iframe.style.display = 'none';
      switched = true;
    }

    window.addEventListener('offline', () => {
      if (!switched) {
        fallback.style.display = 'block';
        iframe.style.display = 'none';
        switched = true;
      }
    });
  })();
});

