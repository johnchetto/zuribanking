// ================================
// Transfer.js — Zuri Bank
// Handles money transfer securely
// ================================

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    const messageArea = document.getElementById('transfer-message-area');

    // ✅ Prevent normal form submission and handle via JS
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        // Collect form values
        const recipientName = document.getElementById('recipient-name').value.trim();
        const recipientAccount = document.getElementById('recipient-account').value.trim();
        const serviceProvider = document.getElementById('service-provider').value;
        const amount = parseFloat(document.getElementById('amount').value);
        const description = document.getElementById('description').value.trim();

        // ✅ Get logged-in user ID from authGuard or localStorage/sessionStorage
        const senderId = localStorage.getItem('customerID') || sessionStorage.getItem('customerID');

        if (!senderId) {
            messageArea.textContent = "Session expired. Please log in again.";
            messageArea.style.color = "red";
            return;
        }

        // ✅ Create transfer object
        const transferData = {
            sender_id: senderId,
            recipient_name: recipientName,
            recipient_account_number: recipientAccount,
            service_provider: serviceProvider,
            amount: amount,
            description: description
        };

        try {
            // ✅ Send transfer request to server
            const response = await fetch('/execute-transfer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(transferData)
            });

            const result = await response.json();

            if (response.ok) {
                messageArea.textContent = result.message || "✅ Transfer successful!";
                messageArea.style.color = "green";
                form.reset();
            } else {
                messageArea.textContent = result.message || "❌ Transfer failed. Please try again.";
                messageArea.style.color = "red";
            }

        } catch (error) {
            console.error("Error processing transfer:", error);
            messageArea.textContent = "⚠️ Network error. Please try again later.";
            messageArea.style.color = "red";
        }
    });
});
