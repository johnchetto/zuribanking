// login.js — enhanced validation + password visibility toggle
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  const emailInput = document.getElementById("Email");
  const passwordInput = document.getElementById("password");
  const userType = document.getElementById("usertype");
  const togglePassword = document.getElementById("togglePassword");

  // 👁️ Toggle show/hide password
  togglePassword.addEventListener("click", () => {
    const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);
    togglePassword.textContent = type === "password" ? "👁️" : "🙈";
  });

  form.addEventListener("submit", (event) => {
    emailInput.classList.remove("error");
    passwordInput.classList.remove("error");
    userType.classList.remove("error");

    let errors = [];

    // Validate email
    const emailValue = emailInput.value.trim();
    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
    if (!emailValue) {
      errors.push("Email field cannot be empty.");
      emailInput.classList.add("error");
    } else if (!emailPattern.test(emailValue)) {
      errors.push("Please enter a valid email address.");
      emailInput.classList.add("error");
    }

    // 💪 Strong password validation
    const passwordValue = passwordInput.value.trim();
    const strongPasswordPattern =
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^])[A-Za-z\d@$!%*?&#^]{8,}$/;

    if (!passwordValue) {
      errors.push("Password cannot be empty.");
      passwordInput.classList.add("error");
    } else if (!strongPasswordPattern.test(passwordValue)) {
      errors.push(
        "Password must include at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character."
      );
      passwordInput.classList.add("error");
    }

    // Validate user type
    if (userType.value === "") {
      errors.push("Please select a user type.");
      userType.classList.add("error");
    }

    // Prevent submission if there are errors
    if (errors.length > 0) {
      event.preventDefault();
      alert(errors.join("\n"));
    }
  });
});
