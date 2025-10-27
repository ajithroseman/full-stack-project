// for banner 2 slide
    const observerOptions = {
        threshold: 0.3, // Trigger when 30% of the banner is visible
        rootMargin: '0px 0px -10% 0px' // Add some margin for better timing
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const content = entry.target.querySelector('.banner-2-content');
                
            if (entry.isIntersecting) {
                // Add animation class when banner comes into view
                content.classList.add('animate');
            } else {
                // Remove animation class when banner goes out of view (optional)
                // Comment out the next line if you want the animation to stay after first trigger
                content.classList.remove('animate');
            }
        });
    }, observerOptions);

    // Start observing the banner
    const banner = document.getElementById('banner2');
    observer.observe(banner);

    // Optional: Add smooth scrolling for better user experience
    document.documentElement.style.scrollBehavior = 'smooth';

// banner 2 slide

const observerOptions2 = {
        threshold: 0.3, // Trigger when 30% of the banner is visible
        rootMargin: '0px 0px -10% 0px' // Add some margin for better timing
    };

    const observer2 = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const content = entry.target.querySelector('.banner-3-content');
                
            if (entry.isIntersecting) {
                // Add animation class when banner comes into view
                content.classList.add('animate');
            } else {
                // Remove animation class when banner goes out of view (optional)
                // Comment out the next line if you want the animation to stay after first trigger
                content.classList.remove('animate');
            }
        });
    }, observerOptions2);

    // Start observing the banner
    const banner2 = document.getElementById('banner3');
    observer2.observe(banner2);

    // Optional: Add smooth scrolling for better user experience
    document.documentElement.style.scrollBehavior = 'smooth';

// key feature

document.addEventListener("DOMContentLoaded", () => {
  // tweak these values as needed
  const observerOptions = {
    threshold: 0.1,                 // 10% visible
    rootMargin: "0px 0px -5% 0px"   // fine-tune when it triggers
  };
  const baseDelay = 100;   // ms before first animation starts
  const stagger = 50;     // ms added per box index (0 => +0, 1 => +150, ...)

  // store pending timeouts so we can cancel them if element leaves view
  const timeouts = new WeakMap();

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      const el = entry.target;
      const idx = parseInt(el.dataset.index || "0", 10);

      if (entry.isIntersecting) {
        // schedule adding the class with delay + stagger
        const delay = baseDelay + (idx * stagger);
        // clear any existing timeout (safety)
        if (timeouts.has(el)) {
          clearTimeout(timeouts.get(el));
          timeouts.delete(el);
        }
        const t = setTimeout(() => {
          el.classList.add("show");
          timeouts.delete(el);
        }, delay);
        timeouts.set(el, t);
      } else {
        // leaving viewport: cancel any pending add and remove the class
        if (timeouts.has(el)) {
          clearTimeout(timeouts.get(el));
          timeouts.delete(el);
        }
        el.classList.remove("show"); // comment this line out if you want it to persist once shown
      }
    });
  }, observerOptions);

  // attach data-index to allow staggering, then observe
  const boxes = Array.from(document.querySelectorAll(".feature-box"));
  boxes.forEach((box, i) => {
    box.dataset.index = i;
    observer.observe(box);
  });
});

// optional add ons

document.addEventListener("DOMContentLoaded", () => {
  const cards = document.querySelectorAll(".addon-card");

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("show");
      } else {
        entry.target.classList.remove("show");
      }
    });
  }, {
    threshold: 0.2,          // when 20% visible
    rootMargin: "0px 0px -10% 0px" // tweak when to trigger (optional)
  });

  cards.forEach(card => observer.observe(card));
});


//seconday nav bar

// Secondary Navigation Show/Hide on Scroll
document.addEventListener('DOMContentLoaded', function() {
    const secondaryNav = document.getElementById('secondary-nav');
    const scrollTrigger = document.getElementById('scroll-trigger'); // the "on this page" section
    
    if (!secondaryNav || !scrollTrigger) return;
    
    // Get the bottom position of the scroll trigger element
    function getScrollTriggerBottom() {
        const rect = scrollTrigger.getBoundingClientRect();
        return rect.bottom + window.pageYOffset;
    }
    
    function handleScroll() {
        const scrollTriggerBottom = getScrollTriggerBottom();
        const currentScrollY = window.pageYOffset;
        
        // Show secondary nav when we've scrolled past the scroll trigger section
        if (currentScrollY > scrollTriggerBottom - 200) {
            secondaryNav.classList.add('show');
        } else {
            secondaryNav.classList.remove('show');
        }
    }
    
    // Listen for scroll events
    window.addEventListener('scroll', handleScroll);
    
    // Initial check in case page is already scrolled
    handleScroll();
    
    // Smooth scrolling for secondary nav links
    const secondaryNavLinks = secondaryNav.querySelectorAll('a[href^="#"]');
    
    secondaryNavLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Calculate offset to account for fixed header and secondary nav
                const offset = 160; // Adjust this value based on your header height
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - offset;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Update active state
                secondaryNavLinks.forEach(navLink => {
                    navLink.classList.remove('active');
                });
                this.classList.add('active');
            }
        });
    });
    
    // Update active nav item based on scroll position
    function updateActiveNav() {
        const sections = ['#banner2', '#banner3', '#key-features', '#add-on-features', '#scroll'];
        const scrollPos = window.pageYOffset + 200; // Offset for better accuracy
        
        sections.forEach(sectionId => {
            const section = document.querySelector(sectionId);
            const navLink = secondaryNav.querySelector(`a[href="${sectionId}"]`);
            
            if (section && navLink) {
                const sectionTop = section.getBoundingClientRect().top + window.pageYOffset;
                const sectionBottom = sectionTop + section.offsetHeight;
                
                if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                    secondaryNavLinks.forEach(link => link.classList.remove('active'));
                    navLink.classList.add('active');
                }
            }
        });
    }
    
    // Update active nav on scroll
    window.addEventListener('scroll', updateActiveNav);
    
    // Initial active nav update
    updateActiveNav();
});

//scroll section

 const wrapper = document.getElementById('cardsWrapper');

    function scrollCards(direction) {
      const scrollAmount = 270; // roughly one card + gap
      wrapper.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
    }
