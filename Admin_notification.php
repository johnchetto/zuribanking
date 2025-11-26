<?php
include 'db_connect.php'; // DB connection already included in dashboard

if (!isset($_SESSION['admin_id'])) {
    exit("Unauthorized access");
}

// Fetch notifications for Admin
$sql = "SELECT * FROM notifications 
        WHERE role_target IN ('admin', 'both') 
        ORDER BY created_at DESC
        LIMIT 10";
$result = $conn->query($sql);

// Count unread notifications
$count_unread = $conn->query("SELECT COUNT(*) AS unread 
                              FROM notifications 
                              WHERE role_target IN ('admin', 'both') AND is_read = 0");
$unread = $count_unread->fetch_assoc()['unread'];
?>

<!-- Admin Notification Bell for Horizontal Nav -->
<li class="notification-li" id="notifIcon">
  <a href="javascript:void(0);" class="nav-bell">
    <i class="fa fa-bell"></i>
    <?php if ($unread > 0): ?>
      <span class="badge" id="notifBadge"><?php echo $unread; ?></span>
    <?php endif; ?>
  </a>

  <div class="dropdown" id="notifDropdown">
    <h4>Notifications</h4>
    <ul id="notifList">
      <!-- Notification items are injected server-side here -->
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <li class="<?= $row['is_read'] ? '' : 'unread'; ?>">
            <strong><?= htmlspecialchars($row['title']); ?></strong><br>
            <?= nl2br(htmlspecialchars($row['message'])); ?><br>
            <small><?= htmlspecialchars($row['created_at']); ?></small>
          </li>
        <?php endwhile; ?>
      <?php else: ?>
        <li>No notifications available.</li>
      <?php endif; ?>
    </ul>
  </div>
</li>

<style>
/* Notification list item */
.notification-li {
  position: relative;
  list-style: none;
  margin-left: auto;
  display: flex;
  align-items: center;
}

/* Bell icon link */
.nav-bell {
  color: white;
  font-size: 28px;
  text-decoration: none;
  position: relative;
  display: inline-block;
  padding: 0.5rem 0.8rem;
}

.nav-bell:hover {
  color: #ffd700;
}

/* Notification badge */
.nav-bell .badge,
.notification-li .badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background: red;
  color: white;
  border-radius: 50%;
  padding: 3px 6px;
  font-size: 12px;
  font-weight: bold;
  z-index: 1;
}

/* Dropdown menu */
.dropdown {
  display: none;
  position: absolute;
  top: 40px;
  right: 0;
  background: #fff;
  width: 320px;
  box-shadow: 0 0 10px rgba(0,0,0,0.2);
  border-radius: 5px;
  overflow: hidden;
  z-index: 10;
}

.dropdown.active {
  display: block;
}

.dropdown h4 {
  background: #004aad;
  color: #08e49aff;
  padding: 10px;
  margin: 0;
  font-size: 14px;
}

.dropdown ul {
  list-style: none;
  margin: 0;
  padding: 0;
  max-height: 300px;
  overflow-y: auto;
}

.dropdown li {
  padding: 10px;
  border-bottom: 1px solid #0ce265ff;
}

.dropdown li.unread {
  background-color: #05cc41ff;
  border-left: 4px solid #0ce73bff;
}

.dropdown li:hover {
  background-color: #570b5aff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notifIcon = document.getElementById("notifIcon");
    const notifDropdown = document.getElementById("notifDropdown");
    const notifBadge = document.getElementById("notifBadge");

    // Toggle dropdown on click of the whole li (works for icon or area)
    notifIcon.addEventListener("click", function(e) {
        e.stopPropagation(); // Prevent nav clicks
        notifDropdown.classList.toggle("active");

        // Mark all as read via AJAX (mark_read_admin.php should return JSON { success:true })
        fetch('mark_read_admin.php')
        .then(res => res.json())
        .then(data => {
            if (data && data.success && notifBadge) notifBadge.style.display = "none";
        })
        .catch(()=> {
            // silently ignore fetch errors (optional: show console)
            console.warn('mark_read_admin.php fetch failed');
        });
    });

    // Prevent clicks inside the dropdown from closing it
    notifDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Close dropdown if clicked outside
    document.addEventListener("click", function() {
        notifDropdown.classList.remove("active");
    });
});
</script>

<!-- Font Awesome for bell (keeps same placement you had) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
