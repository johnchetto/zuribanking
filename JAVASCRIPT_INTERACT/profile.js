// profile.js

document.addEventListener("DOMContentLoaded", () => {
  const profileForm = document.querySelector("form");
  const newPassword = document.getElementById("new-password");
  const confirmPassword = document.getElementById("confirm-password");
  const currentPassword = document.getElementById("current-password");
  const profileImageInput = document.getElementById("profile-image");
  const profileImagePreview = document.querySelector(".profile-image img");

  // --- Password confirmation validation ---
  profileForm.addEventListener("submit", (e) => {
    if (!currentPassword.value.trim()) {
      alert("Please enter your current password to save changes.");
      e.preventDefault();
      return;
    }

    if (newPassword.value || confirmPassword.value) {
      if (newPassword.value !== confirmPassword.value) {
        alert("New password and confirm password do not match!");
        e.preventDefault();
        return;
      }

      if (newPassword.value.length < 8) {
        alert("New password must be at least 8 characters long.");
        e.preventDefault();
        return;
      }
    }

    // Optionally, you can add AJAX submission here
    // For now, default POST form submission
  });

  // --- Profile image preview ---
  if (profileImageInput && profileImagePreview) {
    profileImageInput.addEventListener("change", (e) => {
      const file = e.target.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = (event) => {
        profileImagePreview.src = event.target.result;
      };
      reader.readAsDataURL(file);
    });
  }
});
