// ===============================================
// ZURI ONLINE BANKING SYSTEM - ADMIN TRANSACTION LOGS PAGE
// ===============================================

// ✅ 1. Function to generate new unique log IDs
function generateLogID() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, "0");
    const d = String(now.getDate()).padStart(2, "0");
    const random = Math.floor(1 + Math.random() * 999)
        .toString()
        .padStart(3, "0");
    return `LOG-${y}${m}${d}-${random}`;
}

// ✅ 2. Simulated log data (placeholder for backend)
let logs = [
    {
        id: "LOG-20251010-001",
        timestamp: "2025-10-10T12:35:10",
        user: "ADM-001",
        action: "Accessed Transaction Attempts Page",
        status: "Information",
        device: "192.168.1.5 (Chrome)",
    },
    {
        id: "LOG-20251010-002",
        timestamp: "2025-10-10T12:30:45",
        user: "CUS-0456",
        action: "Fund Transfer initiated (TRX-12346)",
        status: "Suspicious",
        device: "58.23.10.12 (Mobile)",
    },
    {
        id: "LOG-20251010-003",
        timestamp: "2025-10-10T12:25:01",
        user: "CUS-0789",
        action: "Login attempt failed (Wrong Password)",
        status: "Warning",
        device: "105.160.20.1 (Firefox)",
    },
    {
        id: "LOG-20251010-004",
        timestamp: "2025-10-10T12:00:00",
        user: "SYSTEM",
        action: "End-of-day reconciliation report generation",
        status: "Information",
        device: "Internal Server",
    },
];

// ✅ 3. Function to display logs in the table
function loadLogs(data) {
    const tbody = document.querySelector("tbody");
    tbody.innerHTML = "";

    data.forEach((log) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td data-label="Log ID">${log.id}</td>
            <td data-label="Timestamp"><time datetime="${log.timestamp}">
                ${new Date(log.timestamp).toLocaleString()}
            </time></td>
            <td data-label="User/Account">${log.user}</td>
            <td data-label="Action">${log.action}</td>
            <td data-label="Status" style="color:${getStatusColor(log.status)}">${log.status}</td>
            <td data-label="Device">${log.device}</td>
        `;
        tbody.appendChild(row);
    });
}

// ✅ 4. Helper: color coding based on log type
function getStatusColor(status) {
    switch (status.toLowerCase()) {
        case "information":
            return "green";
        case "warning":
            return "orange";
        case "error":
            return "red";
        case "suspicious":
            return "crimson";
        default:
            return "black";
    }
}

// ✅ 5. Handle Filter Form
const filterForm = document.querySelector("form[action='/admin/filter-logs']");
if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const startDate = document.getElementById("date-range-start").value;
        const endDate = document.getElementById("date-range-end").value;
        const txID = document.getElementById("search-id").value.trim().toLowerCase();
        const userAccount = document.getElementById("user-account").value.trim().toLowerCase();
        const logType = document.getElementById("log-type").value.trim().toLowerCase();

        // Filter logic
        const filtered = logs.filter((log) => {
            const logDate = new Date(log.timestamp);
            const isWithinRange =
                (!startDate || logDate >= new Date(startDate)) &&
                (!endDate || logDate <= new Date(endDate));
            const matchTx = !txID || log.id.toLowerCase().includes(txID);
            const matchUser = !userAccount || log.user.toLowerCase().includes(userAccount);
            const matchType = !logType || log.status.toLowerCase() === logType;

            return isWithinRange && matchTx && matchUser && matchType;
        });

        loadLogs(filtered);
        console.log("Filter applied:", { startDate, endDate, txID, userAccount, logType });
    });
}

// ✅ 6. Automatically generate new log entries (simulation)
setInterval(() => {
    const newLog = {
        id: generateLogID(),
        timestamp: new Date().toISOString(),
        user: ["ADM-001", "CUS-0456", "CUS-0999", "SYSTEM"][Math.floor(Math.random() * 4)],
        action: ["User login successful", "Deposit completed", "Suspicious login attempt", "System backup initiated"][
            Math.floor(Math.random() * 4)
        ],
        status: ["Information", "Warning", "Suspicious", "Error"][Math.floor(Math.random() * 4)],
        device: ["192.168.0.1 (Edge)", "10.0.0.4 (Safari)", "Internal Server", "105.160.40.3 (Android)"][
            Math.floor(Math.random() * 4)
        ],
    };

    logs.unshift(newLog);
    loadLogs(logs.slice(0, 10)); // show latest 10 logs
    console.log("New log added:", newLog);
}, 15000); // every 15 seconds

// ✅ 7. Initialize page
document.addEventListener("DOMContentLoaded", () => loadLogs(logs));
