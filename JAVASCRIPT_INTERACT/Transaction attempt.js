// ===============================================
// ZURI ONLINE BANKING SYSTEM - ADMIN TRANSACTION MONITORING
// ===============================================

// ✅ 1. Generate unique transaction IDs
function generateTransactionID() {
    const date = new Date();
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, "0");
    const d = String(date.getDate()).padStart(2, "0");
    const random = Math.floor(1000 + Math.random() * 9000);
    return `TRX-${y}${m}${d}-${random}`;
}

// ✅ 2. Simulated transaction data (replace with backend data later)
let transactions = [
    {
        id: "TRX-12346",
        date: "2025-10-10T12:35",
        customer: "John Macharia",
        account: "**** 1234",
        type: "Transfer",
        amount: 5000,
        status: "Suspicious"
    },
    {
        id: "TRX-12345",
        date: "2025-10-10T12:30",
        customer: "Jane Kihara",
        account: "**** 5678",
        type: "Deposit",
        amount: 10000,
        status: "Successful"
    },
    {
        id: "TRX-12344",
        date: "2025-10-10T12:25",
        customer: "Alex Kihara",
        account: "**** 9012",
        type: "Withdrawal",
        amount: 2000,
        status: "Failed"
    }
];

// ✅ 3. Load transactions into table
function loadTransactions(data) {
    const tbody = document.querySelector("tbody");
    tbody.innerHTML = ""; // clear old rows

    data.forEach(tx => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td data-label="ID">${tx.id}</td>
            <td data-label="Date/Time"><time datetime="${tx.date}">${new Date(tx.date).toLocaleString()}</time></td>
            <td data-label="Customer">${tx.customer}</td>
            <td data-label="Account">${tx.account}</td>
            <td data-label="Type">${tx.type}</td>
            <td data-label="Amount">${tx.amount.toLocaleString()}</td>
            <td data-label="Status" style="color:${getStatusColor(tx.status)}">${tx.status}</td>
            <td data-label="Action">
                <button type="button" class="btn-view">View Details</button><br>
                <button type="button" class="btn-flag">Flag Transaction</button>
            </td>
        `;
        tbody.appendChild(row);
    });

    addRowEventListeners();
}

// ✅ 4. Color-coding for transaction status
function getStatusColor(status) {
    switch (status.toLowerCase()) {
        case "successful": return "green";
        case "failed": return "red";
        case "suspicious": return "orange";
        default: return "black";
    }
}

// ✅ 5. Event listeners for buttons in table
function addRowEventListeners() {
    document.querySelectorAll(".btn-view").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const row = e.target.closest("tr");
            const id = row.querySelector("[data-label='ID']").textContent;
            alert(` Viewing details for ${id}`);
            // (Later: fetch(`/api/transactions/${id}`) to view full info)
        });
    });

    document.querySelectorAll(".btn-flag").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const row = e.target.closest("tr");
            const id = row.querySelector("[data-label='ID']").textContent;
            const statusCell = row.querySelector("[data-label='Status']");
            statusCell.textContent = "Flagged";
            statusCell.style.color = "orange";
            alert(`🚩 Transaction ${id} has been flagged for review.`);
            // (Later: POST `/api/transactions/flag` {id})
        });
    });
}

// ✅ 6. Handle filter form
const filterForm = document.querySelector("form[action='/ADMIN_DASHBOARD/Transaction_attempt.html']");
if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const searchID = document.getElementById("search-id").value.trim().toLowerCase();
        const searchName = document.getElementById("search-name").value.trim().toLowerCase();
        const status = document.getElementById("status-filter").value.trim().toLowerCase();

        const filtered = transactions.filter(tx => {
            return (
                (!searchID || tx.id.toLowerCase().includes(searchID)) &&
                (!searchName || tx.customer.toLowerCase().includes(searchName)) &&
                (!status || tx.status.toLowerCase() === status)
            );
        });

        loadTransactions(filtered);
        console.log("Filter applied:", { searchID, searchName, status });
    });
}

// ✅ 7. Auto-generate new transactions every few seconds (simulation)
setInterval(() => {
    const newTx = {
        id: generateTransactionID(),
        date: new Date().toISOString(),
        customer: "System Test User",
        account: "**** " + Math.floor(1000 + Math.random() * 9000),
        type: ["Deposit", "Transfer", "Withdrawal"][Math.floor(Math.random() * 3)],
        amount: Math.floor(1000 + Math.random() * 9000),
        status: ["Successful", "Failed", "Suspicious"][Math.floor(Math.random() * 3)]
    };

    transactions.unshift(newTx);
    loadTransactions(transactions.slice(0, 10)); // show latest 10
    console.log("New transaction added:", newTx);
}, 10000); // every 10 seconds

// ✅ 8. Load transactions initially
document.addEventListener("DOMContentLoaded", () => loadTransactions(transactions));
