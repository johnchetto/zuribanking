// Function to fetch balance from the server
function fetchBalance() {
    // Make an AJAX request to get_balance.php
    fetch('get_balance.php')
        .then(response => response.json()) // Parse JSON response
        .then(data => {
            // Update the balance in the page
            document.getElementById('current-balance').innerText = `KES ${data.balance.toFixed(2)}`;
            // Update the last updated time
            document.getElementById('last-updated-time').innerText = data.last_updated;
        })
        .catch(err => console.error('Error fetching balance:', err)); // Handle errors
}

// Call fetchBalance automatically every 5 seconds
setInterval(fetchBalance, 5000);

// Fetch balance immediately when page loads
fetchBalance();
