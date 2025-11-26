// ===========================
//  admin_dashboard.js
//  For Admin Dashboard Page
// ===========================

document.addEventListener("DOMContentLoaded", () => {
    // ---- 1. Authentication Check ----
    const adminLoggedIn = localStorage.getItem("adminLoggedIn");
    if (!adminLoggedIn) {
       // alert("Unauthorized access! Please log in as an admin to continue.");
       // window.location.href = "/LOGIN_PAGE/login.html";
        //return;
    }

    // ---- 2. DOM Elements ----
    const totalCustomersEl = document.getElementById("total-customers");
    const pendingApprovalsEl = document.getElementById("pending-approvals");
    const totalTransactionsEl = document.getElementById("total-transactions");
    const systemLogsEl = document.getElementById("system-logs-count");

    const loginTableBody = document.querySelector(".recent-logins tbody");
    const transactionsTableBody = document.querySelector(".recent-transactions tbody");

    // ---- 3. Simulate Admin Statistics ----
    function loadDashboardStats() {
        // These can later come from your backend (PHP + Database)
        const stats = {
            totalCustomers: 25,
            pendingApprovals: 12,
            totalTransactions: 10,
            systemLogs: 1
        };

        totalCustomersEl.textContent = stats.totalCustomers.toLocaleString();
        pendingApprovalsEl.textContent = stats.pendingApprovals;
        totalTransactionsEl.textContent = stats.totalTransactions.toLocaleString();
        systemLogsEl.textContent = stats.systemLogs.toLocaleString();
    }

    // ---- 4. Simulate Recent Activities ----
    function loadRecentLogins() {
        const recentLogins = [
            { userId: "CUS-1023", time: "1:05 PM", status: "Success" },
            { userId: "CUS-1054", time: "12:58 PM", status: "Failure" },
            { userId: "ADM-002", time: "12:30 PM", status: "Success" },
            { userId: "CUS-1120", time: "11:59 AM", status: "Success" }
        ];

        loginTableBody.innerHTML = ""; // Clear existing rows

        recentLogins.forEach((login) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${login.userId}</td>
                <td>${login.time}</td>
                <td>${login.status}</td>
            `;
            loginTableBody.appendChild(row);
        });
    }

    function loadRecentTransactions() {
        const transactions = [
            { id: "TRX-23540", amount: "Ksh 2,000.00", user: "CUS-0789", status: "Pending" },
            { id: "TRX-23541", amount: "Ksh 15,000.00", user: "CUS-0456", status: "Success" },
            { id: "TRX-23542", amount: "Ksh 350.00", user: "CUS-0678", status: "Failed" },
            { id: "TRX-23543", amount: "Ksh 8,900.00", user: "CUS-0234", status: "Success" },
            { id: "TRX-23544", amount: "Ksh 500.00", user: "CUS-0109", status: "Pending" }
        ];

        transactionsTableBody.innerHTML = "";

        transactions.forEach((txn) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${txn.id}</td>
                <td>${txn.amount}</td>
                <td>${txn.user}</td>
                <td>${txn.status}</td>
            `;
            transactionsTableBody.appendChild(row);
        });
    }

    // ---- 5. Initialize Page ----
    loadDashboardStats();
    loadRecentLogins();
    loadRecentTransactions();

    // ---- 6. Auto Refresh (optional simulation) ----
    setInterval(() => {
        loadDashboardStats();
        loadRecentLogins();
        loadRecentTransactions();
        console.log("Dashboard refreshed with latest data.");
    }, 60000); // Refresh every 1 minute (optional)
});
