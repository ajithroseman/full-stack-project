// Mobile JavaScript - Add this to your existing JavaScript
document.addEventListener('DOMContentLoaded', function() {
  const hamburger = document.getElementById('hamburger');
  const navMenu = document.getElementById('nav-menu');
  const dropdowns = document.querySelectorAll('.dropdown');
  const arrows = document.querySelectorAll('.arrow');

  // Toggle mobile menu
  hamburger.addEventListener('click', function() {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
    
    // Close all dropdowns when closing the menu
    if (!navMenu.classList.contains('active')) {
      dropdowns.forEach(dropdown => {
        dropdown.classList.remove('active');
      });
      arrows.forEach(arrow => {
        arrow.classList.remove('rotate');
      });
    }
  });

  // Handle dropdown toggle on mobile
  dropdowns.forEach(dropdown => {
    const dropdownLink = dropdown.querySelector('a');
    
    dropdownLink.addEventListener('click', function(e) {
      // Only prevent default and toggle on mobile
      if (window.innerWidth <= 768) {
        e.preventDefault();
        
        // Close other dropdowns
        dropdowns.forEach(otherDropdown => {
          if (otherDropdown !== dropdown) {
            otherDropdown.classList.remove('active');
            const otherArrow = otherDropdown.querySelector('.arrow');
            if (otherArrow) otherArrow.classList.remove('rotate');
          }
        });
        
        // Toggle current dropdown
        dropdown.classList.toggle('active');
        const arrow = dropdown.querySelector('.arrow');
        if (arrow) arrow.classList.toggle('rotate');
      }
    });
  });

  // Close menu when clicking on a non-dropdown link
  const nonDropdownLinks = document.querySelectorAll('.nav-menu > li:not(.dropdown) > a');
  nonDropdownLinks.forEach(link => {
    link.addEventListener('click', function() {
      if (window.innerWidth <= 768) {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
        
        // Close all dropdowns
        dropdowns.forEach(dropdown => {
          dropdown.classList.remove('active');
        });
        arrows.forEach(arrow => {
          arrow.classList.remove('rotate');
        });
      }
    });
  });

  // Close menu when clicking outside
  document.addEventListener('click', function(e) {
    if (window.innerWidth <= 768 && 
        !hamburger.contains(e.target) && 
        !navMenu.contains(e.target)) {
      hamburger.classList.remove('active');
      navMenu.classList.remove('active');
      
      // Close all dropdowns
      dropdowns.forEach(dropdown => {
        dropdown.classList.remove('active');
      });
      arrows.forEach(arrow => {
        arrow.classList.remove('rotate');
      });
    }
  });

  // Handle window resize
  window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
      // Reset mobile menu state when switching to desktop
      hamburger.classList.remove('active');
      navMenu.classList.remove('active');
      
      // Close all dropdowns
      dropdowns.forEach(dropdown => {
        dropdown.classList.remove('active');
      });
      arrows.forEach(arrow => {
        arrow.classList.remove('rotate');
      });
    }
  });
});