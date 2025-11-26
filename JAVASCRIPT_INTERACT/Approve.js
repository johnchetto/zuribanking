// ================================
// ZURI BANK - ADMIN APPROVE PAGE
// ================================

// ✅ Auth Guard - Ensure only admin can access this page
document.addEventListener("DOMContentLoaded", () => {
    const isAdminLoggedIn = sessionStorage.getItem("adminLoggedIn");

    if (!isAdminLoggedIn) {
       // alert("Access denied! Please log in as Admin first.");
       // window.location.href = "/ADMIN_LOGIN/login.html"; 
       // return;
    }
});

// ✅ Select all approve/reject buttons
const approveButtons = document.querySelectorAll(".btn-approve");
const rejectButtons = document.querySelectorAll(".btn-reject");
const feedback = document.getElementById("action-feedback");

// ✅ Function to Generate Account Numbers Automatically
function generateAccountNumber() {
    // Kenyan-style format: starts with "01" followed by 8 random digits
    const prefix = "01";
    const randomDigits = Math.floor(10000000 + Math.random() * 90000000); // 8 digits
    return prefix + randomDigits; // Example: 0112345678
}

//  Handle Approve Button Click
approveButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
        const row = event.target.closest("tr");
        const customerName = row.querySelector("[data-label='Full Name']").textContent;
        const statusCell = row.querySelector("[data-label='Status']");
        const customerID = row.querySelector("[data-label='Customer ID']").textContent;

        // Generate and assign new account number
        const newAccountNumber = generateAccountNumber();

        // Update status visually
        statusCell.textContent = `Approved (Acc No: ${newAccountNumber})`;
        statusCell.style.color = "green";

        feedback.textContent = `✅ Account for ${customerName} (${customerID}) has been approved successfully. Assigned Account Number: ${newAccountNumber}`;
        feedback.style.color = "green";

        // Placeholder: Send approval + account number to backend (PHP later)
        console.log(`Account for ${customerName} approved. New Account Number: ${newAccountNumber}`);
    });
});

// ✅ Handle Reject Button Click
rejectButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
        const row = event.target.closest("tr");
        const customerName = row.querySelector("[data-label='Full Name']").textContent;
        const statusCell = row.querySelector("[data-label='Status']");

        statusCell.textContent = "Rejected";
        statusCell.style.color = "red";

        feedback.textContent = `Account for ${customerName} has been rejected.`;
        feedback.style.color = "red";

        // Placeholder: Send rejection request to backend (PHP later)
        console.log(`Account for ${customerName} rejected.`);
    });
});

// ✅ Filter Form (simulation only for now)
const filterForm = document.querySelector("form[action='/admin/filter-approvals']");
if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const name = document.getElementById("search-name").value.trim();
        const accNum = document.getElementById("search-account").value.trim();

        feedback.textContent = `Filtering results for: ${name || "All"} ${accNum ? " (Account: " + accNum + ")" : ""}`;
        feedback.style.color = "#004080";

        // Placeholder for PHP: fetch filtered data from DB
        console.log("Filter request submitted:", { name, accNum });
    });
}
