// ===============================
// Customer Support Page Script
// ===============================

// ✅ Ensure admin is authenticated before accessing this page
document.addEventListener("DOMContentLoaded", () => {
  if (typeof checkAdminAuth === "function") {
    checkAdminAuth(); // from authGuard_admin.js
  }

  // ========== FAQ Accordion Functionality ==========
  const faqButtons = document.querySelectorAll(".faq-question");

  faqButtons.forEach(button => {
    button.addEventListener("click", () => {
      const answer = button.nextElementSibling;

      // Collapse all other FAQs for clean UX
      document.querySelectorAll(".faq-answer").forEach(el => {
        if (el !== answer) {
          el.style.maxHeight = null;
          el.previousElementSibling.classList.remove("active");
        }
      });

      // Toggle selected FAQ
      button.classList.toggle("active");

      if (answer.style.maxHeight) {
        answer.style.maxHeight = null;
      } else {
        answer.style.maxHeight = answer.scrollHeight + "px";
      }
    });
  });

  // ========== Support Ticket Form Handling ==========
  const form = document.querySelector("form");
  const feedback = document.getElementById("contact-feedback");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const subject = document.getElementById("subject").value.trim();
    const priority = document.getElementById("priority").value;
    const message = document.getElementById("message").value.trim();

    if (!subject || !message) {
      feedback.textContent = "⚠️ Please fill in all required fields.";
      feedback.style.color = "red";
      return;
    }

    // Simulate sending to backend (you can replace with actual API call)
    try {
      feedback.textContent = "⏳ Sending your ticket...";
      feedback.style.color = "#007bff";

      // Example of sending data to backend
      const response = await fetch("/submit-ticket", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          ticket_subject: subject,
          ticket_priority: priority,
          ticket_message: message,
          created_at: new Date().toISOString()
        })
      });

      if (response.ok) {
        feedback.textContent = "✅ Your support ticket has been submitted successfully!";
        feedback.style.color = "green";
        form.reset();
      } else {
        feedback.textContent = "❌ Failed to submit ticket. Please try again later.";
        feedback.style.color = "red";
      }
    } catch (error) {
      console.error("Error:", error);
      feedback.textContent = "❌ Network error. Please check your connection.";
      feedback.style.color = "red";
    }
  });
});
