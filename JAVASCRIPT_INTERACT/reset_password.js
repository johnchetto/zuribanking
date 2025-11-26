// reset_passwd.js

document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  const otpInput = document.getElementById("otp");
  const newPassword = document.getElementById("new_password");
  const confirmPassword = document.getElementById("confirm_password");

  // Password toggle feature
  [newPassword, confirmPassword].forEach((input) => {
    const toggle = document.createElement("span");
    toggle.textContent = "👁️";
    toggle.style.cursor = "pointer";
    toggle.style.marginLeft = "8px";
    toggle.addEventListener("click", () => {
      input.type = input.type === "password" ? "text" : "password";
    });
    input.insertAdjacentElement("afterend", toggle);
  });

  // Password strength indicator
  newPassword.addEventListener("input", function () {
    const strengthText = document.getElementById("strengthText");
    const strengthBar = document.getElementById("strengthBar");

    const val = newPassword.value;
    let strength = 0;

    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[a-z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[@$!%*?&]/.test(val)) strength++;

    const width = (strength / 5) * 100;
    strengthBar.style.width = width + "%";

    if (strength <= 2) {
      strengthBar.style.background = "red";
      strengthText.textContent = "Weak password";
    } else if (strength === 3) {
      strengthBar.style.background = "orange";
      strengthText.textContent = "Medium strength";
    } else {
      strengthBar.style.background = "green";
      strengthText.textContent = "Strong password 💪";
    }
  });

  // Form validation before submission
  form.addEventListener("submit", function (e) {
    const otpValue = otpInput.value.trim();
    const newPass = newPassword.value.trim();
    const confirmPass = confirmPassword.value.trim();

    if (otpValue === "" || newPass === "" || confirmPass === "") {
      alert("⚠️ Please fill in all fields before submitting.");
      e.preventDefault();
      return;
    }

    if (!/^\d{6}$/.test(otpValue)) {
      alert("⚠️ OTP must be a 6-digit number.");
      otpInput.focus();
      e.preventDefault();
      return;
    }

    if (newPass.length < 8) {
      alert("⚠️ Password must be at least 8 characters long.");
      newPassword.focus();
      e.preventDefault();
      return;
    }

    if (newPass !== confirmPass) {
      alert("⚠️ Passwords do not match!");
      confirmPassword.focus();
      e.preventDefault();
      return;
    }

    alert("🔐 Resetting your password... Please wait.");
  });
});
