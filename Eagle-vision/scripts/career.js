document.addEventListener("DOMContentLoaded", () => {
  const descButtons = document.querySelectorAll(".desc-btn");
  const jobDescriptions = document.querySelectorAll(".job-desc");

  // Default: Show job-1 (Marketing Executive)
  document.querySelector("#job-1").classList.add("active");

  descButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      const jobId = btn.getAttribute("data-job");
        
      // Hide all descriptions
      jobDescriptions.forEach(desc => desc.classList.remove("active"));
        
      // Show selected description
      const activeJob = document.getElementById("job-" + jobId);
      if (activeJob) {
        activeJob.classList.add("active");
    
        // Scroll with offset
        const yOffset = -100; // tweak this value to scroll further
        const y = activeJob.getBoundingClientRect().top + window.pageYOffset + yOffset;
        window.scrollTo({ top: y, behavior: "smooth" });
      }
    });

  });
});
document.addEventListener("DOMContentLoaded", () => {
  const applyButtons = document.querySelectorAll(".apply-btn");
  const modal = document.getElementById("applyModal");
  const closeBtn = document.getElementById("closeApply");
  const jobInput = document.getElementById("job");

  // Open modal on Apply button click
  applyButtons.forEach(btn => {
    btn.addEventListener("click", (e) => {
      const jobCard = e.target.closest(".job-card");
      const jobTitle = jobCard.querySelector("h3").innerText;

      jobInput.value = jobTitle; // autofill job title
      modal.style.display = "flex";
    });
  });

  // Close modal
  closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // Close modal when clicking outside content
  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  });

  // Handle form submission (for now just log it)
  document.getElementById("applyForm").addEventListener("submit", (e) => {
    e.preventDefault();
    alert("Application submitted successfully!");
    modal.style.display = "none";
  });
});
