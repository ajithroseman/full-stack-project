// Navigation JavaScript for Desktop and Mobile
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const hamburger = document.getElementById('hambtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const body = document.body;
    
    // Mobile menu toggle
    hamburger.addEventListener('click', function() {
        hamburger.classList.toggle('active');
        mobileMenu.classList.toggle('active');
        body.classList.toggle('menu-open');
    });

    // Close mobile menu when clicking on a link (but not dropdown parent links)
    const mobileLinks = mobileMenu.querySelectorAll('a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Only close menu if it's not a dropdown parent link
            const parentLi = link.parentElement;
            const hasDropdown = parentLi.querySelector('.dropdown');
            
            if (!hasDropdown) {
                hamburger.classList.remove('active');
                mobileMenu.classList.remove('active');
                body.classList.remove('menu-open');
                
                // Also close any open dropdowns
                const allDropdowns = mobileMenu.querySelectorAll('.dropdown');
                allDropdowns.forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
            hamburger.classList.remove('active');
            mobileMenu.classList.remove('active');
            body.classList.remove('menu-open');
        }
    });

    // Desktop dropdown functionality
    const desktopDropdownItems = document.querySelectorAll('.nav-center .links > li');
    
    desktopDropdownItems.forEach(item => {
        const dropdown = item.querySelector('.dropdown');
        if (dropdown) {
            let timeoutId;

            // Show dropdown on hover
            item.addEventListener('mouseenter', function() {
                clearTimeout(timeoutId);
                dropdown.style.display = 'block';
                setTimeout(() => {
                    dropdown.style.opacity = '1';
                    dropdown.style.transform = 'translateY(0)';
                }, 10);
            });

            // Hide dropdown on mouse leave with delay
            item.addEventListener('mouseleave', function() {
                dropdown.style.opacity = '0';
                dropdown.style.transform = 'translateY(-10px)';
                timeoutId = setTimeout(() => {
                    dropdown.style.display = 'none';
                }, 200);
            });
        }
    });

    // Mobile dropdown functionality
    const mobileDropdownItems = mobileMenu.querySelectorAll('li');
    
    mobileDropdownItems.forEach(item => {
        const dropdown = item.querySelector('.dropdown');
        const link = item.querySelector('a');
        
        if (dropdown && link) {
            // Prevent default click behavior for parent link
            link.addEventListener('click', function(e) {
                // Only prevent default if this link has a dropdown
                e.preventDefault();
                
                // Close all other dropdowns first
                const allDropdowns = mobileMenu.querySelectorAll('.dropdown');
                allDropdowns.forEach(dd => {
                    if (dd !== dropdown) {
                        dd.classList.remove('active');
                    }
                });
                
                // Toggle current dropdown
                dropdown.classList.toggle('active');
            });
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Desktop view - reset mobile menu
            hamburger.classList.remove('active');
            mobileMenu.classList.remove('active');
            body.classList.remove('menu-open');
            
            // Reset mobile dropdowns
            const mobileDropdowns = mobileMenu.querySelectorAll('.dropdown');
            mobileDropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });

    // Set active page
    function setActivePage() {
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        const navLinks = document.querySelectorAll('.nav-center a, .mobile-menu a');
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href');
            if (href === currentPage || (currentPage === '' && href === 'index.html')) {
                link.classList.add('active');
            }
        });
    }

    // Initialize active page
    setActivePage();

    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Additional CSS classes toggle functionality
function toggleMobileMenu() {
    const hamburger = document.getElementById('hambtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    hamburger.classList.toggle('active');
    mobileMenu.classList.toggle('active');
    document.body.classList.toggle('menu-open');
}

// Expose function globally if needed
window.toggleMobileMenu = toggleMobileMenu;
