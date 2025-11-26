<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/db_connect.php";

// Check DB connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get user ID from session
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../login.php");
    exit();
}

// Fetch all notifications for customers or both
$sql = "SELECT * FROM notifications 
        WHERE role_target='customer' OR role_target='both'
        ORDER BY created_at DESC";
$result = $conn->query($sql);

// Count unread notifications
$count_sql = "SELECT COUNT(*) AS unread 
              FROM notifications 
              WHERE (role_target='customer' OR role_target='both') AND is_read=0";
$unread_result = $conn->query($count_sql);
$unread = $unread_result->fetch_assoc()['unread'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Notifications</title>
<style>
body { font-family: Arial; background:#f8f9fa; margin:0; padding:0; }
.navbar { background:#004aad; color:#fff; padding:10px 20px; display:flex; justify-content:space-between; align-items:center; }
.navbar h2 { margin:0; font-size:18px; }
.notification { position:relative; cursor:pointer; font-size:24px; }
.badge { position:absolute; top:-5px; right:-10px; background:red; color:#fff; border-radius:50%; padding:2px 6px; font-size:12px; }
.dropdown { display:none; position:absolute; top:35px; right:0; background:#fff; width:350px; box-shadow:0 0 10px rgba(0,0,0,0.2); border-radius:5px; overflow:hidden; z-index:10; }
.dropdown.active { display:block; }
.dropdown h4 { background:#004aad; color:#fff; padding:10px; margin:0; font-size:14px; }
.dropdown ul { list-style:none; margin:0; padding:0; max-height:300px; overflow-y:auto; }
.dropdown li { padding:10px; border-bottom:1px solid #eee; }
.dropdown li:hover { background:#f1f1f1; }
.dropdown li.unread { 
    background-color: #cce5ff; 
    border-left: 4px solid #004aad; 
}

.dropdown small { color:#888; }

</style>
</head>
<body>

<div class="navbar">
    <h2>Zuri Online Banking</h2>

    <div class="notification" id="notifIcon">
        🔔
        <?php if ($unread > 0): ?>
            <span class="badge"><?php echo $unread; ?></span>
        <?php endif; ?>

        <div class="dropdown" id="notifDropdown">
            <h4>Notifications</h4>
            <ul>
                <?php if ($result->num_rows > 0): ?>
                    <?php
                    $result->data_seek(0); // reset pointer
                    while ($row = $result->fetch_assoc()): ?>
                        <li class="<?= $row['is_read'] ? '' : 'unread'; ?>">
                            <strong><?= htmlspecialchars($row['title']); ?></strong><br>
                            <?= htmlspecialchars($row['message']); ?><br>
                            <small><?= $row['created_at']; ?></small>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No notifications available.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<script>
const notifIcon = document.getElementById("notifIcon");
const notifDropdown = document.getElementById("notifDropdown");
const badge = document.querySelector(".badge");

// Toggle dropdown
notifIcon.addEventListener("click", function() {
    notifDropdown.classList.toggle("active");

    // Optional: mark all notifications as read via AJAX
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "mark_read_customer.php", true);
    xhr.send();

    if (badge) badge.style.display = "none";
});

// Polling function to fetch new notifications every 10 seconds
function fetchNotifications() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_notifications.php", true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const data = JSON.parse(xhr.responseText);
            
            // Update badge
            if (data.unread > 0) {
                if (badge) {
                    badge.style.display = "inline-block";
                    badge.textContent = data.unread;
                } else {
                    const newBadge = document.createElement("span");
                    newBadge.className = "badge";
                    newBadge.textContent = data.unread;
                    notifIcon.appendChild(newBadge);
                }
            } else if (badge) {
                badge.style.display = "none";
            }

            // Update dropdown
            const ul = notifDropdown.querySelector("ul");
            ul.innerHTML = '';
            if (data.notifications.length > 0) {
                data.notifications.forEach(n => {
                    const li = document.createElement("li");
                    li.className = n.is_read == 0 ? "unread" : "";
                    li.innerHTML = `<strong>${n.title}</strong><br>${n.message}<br><small>${n.created_at}</small>`;
                    ul.appendChild(li);
                });
            } else {
                const li = document.createElement("li");
                li.textContent = "No notifications available.";
                ul.appendChild(li);
            }
        }
    };
    xhr.send();
}

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

        // Update dropdown
        notifList.innerHTML = '';
        data.notifications.forEach(n => {
            const li = document.createElement("li");
            li.className = n.is_read == 0 ? "unread" : "";
            li.innerHTML = `<strong>${n.title}</strong><br>${n.message}<br><small>${n.created_at}</small>`;
            notifList.appendChild(li);
        });
    });
}

fetchNotifications();
setInterval(fetchNotifications, 10000); // every 10 sec

</script>


</body>
</html>
