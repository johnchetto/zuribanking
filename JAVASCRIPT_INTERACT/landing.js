// landing.js — Interactive logic for Zuri Online Banking landing page

document.addEventListener("DOMContentLoaded", () => {
  const ctaButtons = document.querySelectorAll(".call-to-action-group a");
  const featureCards = document.querySelectorAll(".feature-card");

  // 🌟 Smooth fade-in animation for feature cards on scroll
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
        }
      });
    },
    { threshold: 0.2 }
  );

  featureCards.forEach((card) => {
    observer.observe(card);
  });

  // 🖱️ Add click animation to CTA buttons
  ctaButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      btn.classList.add("clicked");
      setTimeout(() => btn.classList.remove("clicked"), 300);
    });
  });

  // 🔄 Optional: Redirect logged-in users automatically
  const userRole = localStorage.getItem("userRole");
  if (userRole === "admin") {
    window.location.href = "/ADMIN_DASHBOARD/admin_dashboard.html";
  } else if (userRole === "customer") {
    window.location.href = "/CUSTOMER_DASHBOARD/customer_dashboard.html";
  }

  // 🎨 Optional: Dynamic header text color change on scroll
  const header = document.querySelector(".site-header");
  window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  });

  // ✅ Accessibility: Smooth scroll for internal links
  const internalLinks = document.querySelectorAll('a[href^="#"]');
  internalLinks.forEach((link) => {
    link.addEventListener("click", (event) => {
      event.preventDefault();
      const target = document.querySelector(link.getAttribute("href"));
      if (target) {
        target.scrollIntoView({ behavior: "smooth" });
      }
    });
  });
});
