// File: /JS/customer/authGuard.js
// Purpose: Prevent unauthorized users from accessing dashboard pages

document.addEventListener("DOMContentLoaded", () => {
    const isLoggedIn = sessionStorage.getItem("isLoggedIn");
    const otpVerified = sessionStorage.getItem("otpVerified");

    // Redirect user if not authenticated
    if (!isLoggedIn || !otpVerified) {
        //alert("⚠️ Access Denied. Please log in and verify OTP first.");
        //window.location.href = "/LOGIN_PAGE/login.html"; // adjust path if needed
    } else {
        console.log("✅ Access granted to Transfer Page.");
    }
});
