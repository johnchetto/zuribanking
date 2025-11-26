// transaction.js
document.addEventListener("DOMContentLoaded", () => {
  console.log("Transaction page loaded");

  // Step 1️⃣: Check login session
  const isLoggedIn = sessionStorage.getItem("userType") === "customer";
  if (!isLoggedIn) {
    //alert("Access denied! Please log in to view your transactions.");
    //window.location.href = "/LOGIN_PAGE/login.html";
    //return;
  }

  // Step 2️⃣: Simulated transaction data (temporary)
  const transactions = [
    {
      id: "TRX-001290",
      date: "2025-10-09",
      recipient: "Alex Kihara",
      account: "**** 4567",
      amount: -5000.0,
      type: "Debit",
      status: "Successful"
    },
    {
      id: "TRX-001289",
      date: "2025-10-07",
      recipient: "Salary Payout",
      account: "N/A",
      amount: 85000.0,
      type: "Credit",
      status: "Successful"
    },
    {
      id: "TRX-001288",
      date: "2025-10-05",
      recipient: "Utility Payment",
      account: "**** 8901",
      amount: -1500.0,
      type: "Debit",
      status: "Pending"
    },
    {
      id: "TRX-001287",
      date: "2025-10-02",
      recipient: "Supermarket Payment",
      account: "**** 3245",
      amount: -3000.0,
      type: "Debit",
      status: "Successful"
    }
  ];

  // Step 3️⃣: Function to render transactions dynamically
  function renderTransactions(data) {
    const tbody = document.querySelector("table tbody");
    tbody.innerHTML = ""; // clear old rows

    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">No transactions found.</td></tr>`;
      return;
    }

    data.forEach((t) => {
      const row = document.createElement("tr");

      // amount style
      const amountClass = t.amount < 0 ? "amount-debit" : "amount-credit";
      const formattedAmount = `${t.amount < 0 ? "- " : "+ "}KES ${Math.abs(t.amount).toLocaleString("en-KE", { minimumFractionDigits: 2 })}`;

      // status style
      const statusClass =
        t.status === "Successful" ? "status-success" : "status-pending";

      row.innerHTML = `
        <td>${t.id}</td>
        <td><time datetime="${t.date}">${new Date(t.date).toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" })}</time></td>
        <td>${t.recipient}</td>
        <td>${t.account}</td>
        <td class="${amountClass}" style="text-align:right;">${formattedAmount}</td>
        <td>${t.type}</td>
        <td class="${statusClass}">${t.status}</td>
      `;
      tbody.appendChild(row);
    });
  }

  // Initial display
  renderTransactions(transactions);

  // Step 4️⃣: Handle Filter Form
  const filterForm = document.querySelector("form[action='/filter-transactions']");
  filterForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const searchTerm = document.getElementById("search-input").value.trim().toLowerCase();
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    const filtered = transactions.filter((t) => {
      const matchSearch = searchTerm ? t.id.toLowerCase().includes(searchTerm) : true;
      const matchStart = startDate ? new Date(t.date) >= new Date(startDate) : true;
      const matchEnd = endDate ? new Date(t.date) <= new Date(endDate) : true;
      return matchSearch && matchStart && matchEnd;
    });

    renderTransactions(filtered);
  });

  // Step 5️⃣: Simulate pagination info
  document.querySelector(".pagination-controls p").textContent = `Showing ${transactions.length} of ${transactions.length} transactions.`;
});
