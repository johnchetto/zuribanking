// Wait for the page to fully load
document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");

  // Input fields
  const firstName = document.getElementById("first_name");
  const lastName = document.getElementById("last_name");
  const email = document.getElementById("email");
  const phone = document.getElementById("phone_number");
  const password = document.getElementById("password");
  const confirmPassword = document.getElementById("confirm_password");

  // Error display elements
  const firstNameError = document.getElementById("first_name_error");
  const lastNameError = document.getElementById("last_name_error");
  const emailError = document.getElementById("email_error");
  const phoneError = document.getElementById("phone_error");
  const passwordError = document.getElementById("password_error");
  const confirmPasswordError = document.getElementById("confirm_password_error");

  // Password toggle
  const passwordToggleIcons = document.querySelectorAll(".eye-icon");

  // 🔹 Function: Show or hide password
  passwordToggleIcons.forEach((icon) => {
    icon.addEventListener("click", () => {
      const input = icon.previousElementSibling;
      if (input.type === "password") {
        input.type = "text";
        icon.textContent = ""; // Eye closed
      } else {
        input.type = "password";
        icon.textContent = ""; // Eye open
      }
    });
  });

  // 🔹 Function: validate email format
  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  // 🔹 Function: validate phone number (Kenyan format or general)
  function isValidPhone(phone) {
    return /^[0-9]{10,13}$/.test(phone);
  }

  // 🔹 Handle form submission
  form.addEventListener("submit", function (event) {
    let isValid = true;

    // Reset all errors
    document.querySelectorAll(".error").forEach((el) => (el.textContent = ""));

    // Validate First Name
    if (firstName.value.trim() === "") {
      firstNameError.textContent = "First name is required.";
      isValid = false;
    }

    // Validate Last Name
    if (lastName.value.trim() === "") {
      lastNameError.textContent = "Last name is required.";
      isValid = false;
    }

    // Validate Email
    if (email.value.trim() === "") {
      emailError.textContent = "Email is required.";
      isValid = false;
    } else if (!isValidEmail(email.value.trim())) {
      emailError.textContent = "Enter a valid email address.";
      isValid = false;
    }

    // Validate Phone
    if (phone.value.trim() === "") {
      phoneError.textContent = "Phone number is required.";
      isValid = false;
    } else if (!isValidPhone(phone.value.trim())) {
      phoneError.textContent = "Enter a valid phone number (10–13 digits).";
      isValid = false;
    }

    // Validate Password
    if (password.value.trim() === "") {
      passwordError.textContent = "Password is required.";
      isValid = false;
    } else if (password.value.length < 6) {
      passwordError.textContent = "Password must be at least 6 characters.";
      isValid = false;
    }

    // Validate Confirm Password
    if (confirmPassword.value.trim() === "") {
      confirmPasswordError.textContent = "Please confirm your password.";
      isValid = false;
    } else if (password.value !== confirmPassword.value) {
      confirmPasswordError.textContent = "Passwords do not match.";
      isValid = false;
    }

    // Stop submission if errors exist
    if (!isValid) {
      event.preventDefault();
    }
  });
});
