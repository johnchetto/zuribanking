// forgot_passwd.js

document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  const emailInput = document.getElementById("user_email");

  form.addEventListener("submit", function (e) {
    const emailValue = emailInput.value.trim();

    // Basic validation
    if (emailValue === "") {
      alert("⚠️ Please enter your email or username before submitting.");
      emailInput.focus();
      e.preventDefault(); // stop form from submitting
      return;
    }

    // If user entered something, check if it looks like an email or username
    const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue);
    const isUsername = /^[A-Za-z0-9_]{3,20}$/.test(emailValue);

    if (!isEmail && !isUsername) {
      alert(
        "⚠️ Please enter a valid email address (e.g., user@example.com) or a valid username (3–20 letters/numbers)."
      );
      e.preventDefault();
      return;
    }

    // Optional: Show a loading message before the PHP page loads
    const button = form.querySelector("button[type='submit']");
    button.textContent = "Processing...";
    button.disabled = true;

    // Allow the form to continue to PHP
  });
});
