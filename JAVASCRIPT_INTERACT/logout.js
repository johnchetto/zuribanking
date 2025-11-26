// ===============================
// admin/logout.js
// Handles secure admin logout
// ===============================

document.addEventListener("DOMContentLoaded", () => {
    // Identify the logout button
    const logoutBtn = document.querySelector(".logout-btn");
    const cancelBtn = document.querySelector(".btn-cancel");

    // 1️⃣ If Admin is not logged in, redirect to login immediately
    const adminLoggedIn = localStorage.getItem("adminLoggedIn");
    if (!adminLoggedIn) {
       // alert("No active admin session found. Redirecting to login...");
       // window.location.href = "/LOGIN_PAGE/login.html";
       // return;
    }

    // 2️⃣ Handle Logout Button Click
    logoutBtn.addEventListener("click", (e) => {
        e.preventDefault();

        // Confirm logout for extra security
        const confirmLogout = confirm("Are you sure you want to log out?");
        if (confirmLogout) {
            // Remove all admin-related session data
            localStorage.removeItem("adminLoggedIn");
            localStorage.removeItem("adminEmail");
            localStorage.removeItem("sessionToken");

            // Optional: Clear everything for full session reset
            // localStorage.clear();

            // Redirect to login page
            alert("You have been securely logged out.");
            window.location.href = "/LOGIN_PAGE/login.html";
        }
    });

    // 3️⃣ Handle Cancel Button (optional — just go back to dashboard)
    cancelBtn.addEventListener("click", (e) => {
        e.preventDefault();
        window.location.href = "dashboard.html";
    });
});
