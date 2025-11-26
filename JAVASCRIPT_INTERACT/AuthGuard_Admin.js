// ===============================
// ZURI BANK - ADMIN AUTH GUARD
// ===============================

// Run after page loads
document.addEventListener("DOMContentLoaded", () => {
    const isAdminLoggedIn = sessionStorage.getItem("adminLoggedIn");

    if (!isAdminLoggedIn) {
        alert("Access Denied! Please log in as an Admin first.");
        window.location.href = "/ADMIN_LOGIN/login.html"; // redirect to admin login page
    }
});
