<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- 🔔 Notification Component -->
<div style="position: relative; display: inline-block; margin-left:auto;">
    <div class="notification" id="notifIcon">
        🔔
        <span class="badge" id="notifBadge">0</span>

        <div class="dropdown" id="notifDropdown">
            <h4>Notifications</h4>
            <ul id="notifList"></ul>
        </div>
    </div>
</div>

<style>
.notification { 
    position: relative; 
    cursor: pointer; 
    font-size: 24px; 
}
.badge { 
    position: absolute; 
    top: -5px; 
    right: -10px; 
    background: red; 
    color: #fff; 
    border-radius: 50%; 
    padding: 2px 6px; 
    font-size: 12px; 
}
.dropdown { 
    display: none; 
    position: absolute; 
    top: 35px; 
    right: 0; 
    background: #fff; 
    width: 300px; 
    box-shadow: 0 0 10px rgba(0,0,0,0.2); 
    border-radius: 5px; 
    overflow: hidden; 
    z-index: 1000; 
}
.dropdown.active { display: block; }
.dropdown h4 { 
    background: #004aad; 
    color: #fff; 
    padding: 10px; 
    margin: 0; 
    font-size: 14px; 
}
.dropdown ul { 
    list-style: none; 
    margin: 0; 
    padding: 0; 
    max-height: 250px; 
    overflow-y: auto; 
}
.dropdown li { 
    padding: 10px; 
    border-bottom: 1px solid #eee; 
}
.dropdown li.unread { 
    background-color: #e6f0ff; 
    border-left: 4px solid #004aad; 
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const notifIcon = document.getElementById("notifIcon");
    const notifDropdown = document.getElementById("notifDropdown");
    const notifBadge = document.getElementById("notifBadge");
    const notifList = document.getElementById("notifList");

    // Fetch notifications from server
    function fetchNotifications() {
        fetch('fetch_notifications.php')
        .then(res => res.json())
        .then(data => {
            // Update badge
            if (data.unread > 0) {
                notifBadge.style.display = "inline-block";
                notifBadge.textContent = data.unread;
            } else {
                notifBadge.style.display = "none";
            }

            // Update dropdown list
            notifList.innerHTML = '';
            if (data.notifications.length > 0) {
                data.notifications.forEach(n => {
                    const li = document.createElement("li");
                    li.className = n.is_read == 0 ? "unread" : "";
                    li.innerHTML = `<strong>${n.title}</strong><br>${n.message}<br><small>${n.created_at}</small>`;
                    notifList.appendChild(li);
                });
            } else {
                const li = document.createElement("li");
                li.textContent = "No notifications available.";
                notifList.appendChild(li);
            }
        });
    }

    // Initial fetch
    fetchNotifications();

    // Poll every 10 seconds
    setInterval(fetchNotifications, 10000);

    // Toggle dropdown and mark all as read
    notifIcon.addEventListener("click", function() {
        notifDropdown.classList.toggle("active");
        fetch('mark_read_customer.php').then(() => {
            notifBadge.style.display = "none";
            fetchNotifications();
        });
    });
});
</script>
