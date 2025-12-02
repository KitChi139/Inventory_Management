<?php
include 'db_connect.php';

// --- FETCH FILTER VALUES ---
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_supplier = isset($_GET['supplier']) ? $_GET['supplier'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// --- QUERY: FETCH DISTINCT SUPPLIERS FOR DROPDOWN ---
$supplierQuery = $conn->query("SELECT DISTINCT supplier FROM messages WHERE supplier IS NOT NULL AND supplier <> ''");

// --- MAIN MESSAGES QUERY ---
$sql = "SELECT * FROM messages WHERE 1";

if ($search !== '') {
    $searchEsc = $conn->real_escape_string($search);
    $sql .= " AND (
        header LIKE '%$searchEsc%' OR
        preview LIKE '%$searchEsc%' OR
        supplier LIKE '%$searchEsc%' OR
        batch LIKE '%$searchEsc%'
    )";
}

if ($filter_supplier !== '' && $filter_supplier !== 'All') {
    $supplierEsc = $conn->real_escape_string($filter_supplier);
    $sql .= " AND supplier = '$supplierEsc'";
}

if ($filter_status !== '' && $filter_status !== 'All') {
    $statusEsc = $conn->real_escape_string($filter_status);
    $sql .= " AND status = '$statusEsc'";
}

$sql .= " ORDER BY date_created DESC";
$messages = $conn->query($sql);

// --- COUNTS ---
$totalMessages = $conn->query("SELECT COUNT(*) AS total FROM messages")->fetch_assoc()['total'];
$pendingMessages = $conn->query("SELECT COUNT(*) AS total FROM messages WHERE status='Pending'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message List</title>

    <link rel="stylesheet" href="message_list.css">
    <link rel="stylesheet" href="notification.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- NOTIFICATION BELL + DROPDOWN -->
<style>
/* minimal notif CSS, include in your main CSS if you prefer */
.notif-wrap { position: relative; display:inline-block; }
.notif-btn { background:none; border:none; font-size:20px; cursor:pointer; position:relative; }
.notif-count { position:absolute; top:-6px; right:-6px; background:#e53e3e; color:#fff; font-size:11px; padding:2px 6px; border-radius:999px; font-weight:700; }
.notif-dd { position:absolute; right:0; width:320px; max-width:90vw; background:#fff; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.12); display:none; z-index:1200; overflow:hidden; }
.notif-dd.show { display:block; }
.notif-dd .dd-header { display:flex; align-items:center; justify-content:space-between; padding:12px 14px; border-bottom:1px solid #f1f5f9; }
.notif-dd .dd-header h4{ margin:0; font-size:15px; }
.notif-dd .dd-list { max-height:260px; overflow:auto; }
.notif-item { display:flex; gap:10px; padding:12px; border-bottom:1px solid #f6f8fb; cursor:pointer; align-items:flex-start; }
.notif-item:last-child{ border-bottom:none; }
.notif-dot { width:12px; height:12px; border-radius:999px; flex:0 0 12px; margin-top:6px; }
.notif-item .title { font-weight:600; font-size:14px; }
.notif-item .body { font-size:13px; color:#555; margin-top:4px; }
.notif-item .meta { font-size:12px; color:#888; margin-top:6px; }
.notif-item.lowstock .notif-dot{ background:#ffb100; } /* amber for low stock */
.notif-item.message .notif-dot{ background:#4f9cf9; } /* blue for messages */
.notif-footer { padding:8px 12px; text-align:center; border-top:1px solid #f1f5f9; }
.notif-footer a { text-decoration:none; color:#0366d6; font-weight:600; }
.notif-settings { font-size:13px; display:flex; gap:8px; align-items:center; }
.notif-settings label { font-size:13px; color:#333; }
</style>

</head>

<body>

<!-- SIDEBAR (FULL COPY FROM request_list.php) -->
<aside class="sidebar" aria-label="Primary">
    <div class="profile">
        <div class="icon"><i class="fa-solid fa-user"></i></div>
        <img src="logo.png" alt="MediSync Logo" class="medisync-logo">
        <button class="toggle"><i class="fa-solid fa-bars"></i></button>
    </div>

    <h3 class="title">Navigation</h3>

    <nav>
        <ul class="menu">
            <li class="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
            <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
            <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
            <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
            <li id="nav-suppliers"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
            <li id="reports"><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
            <li id="users"><i class="fa-solid fa-users"></i><span>Users</span></li>
            <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
            <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
        </ul>
    </nav>
</aside>

<!-- MAIN CONTENT -->
<main class="main">

    <!-- TOP RIGHT BUTTONS -->
    <div class="topbar-right">
    <?php include 'notification_component.php'; ?>
</div>


    <!-- PAGE TITLE -->
    <div class="top-bar">
        <h2>Message List</h2>
    </div>

    <!-- STAT CARDS -->
    <section class="stats-cards">
        <div class="stat-card">
            <div class="stat-title">Total Messages</div>
            <div class="stat-number"><?= $totalMessages ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Selected Messages</div>
            <div class="stat-number" id="selected-count">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Pending Messages</div>
            <div class="stat-number"><?= $pendingMessages ?></div>
        </div>
    </section>

    <!-- FILTERS -->
    <section class="filters">
        <form method="GET" style="display:flex; width:100%; gap:12px;">

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search messages or suppliers..."
                value="<?= htmlspecialchars($search) ?>"
            >

            <!-- SUPPLIER FILTER -->
            <select name="supplier" class="filter-dropdown">
                <option value="All">All Suppliers</option>
                <?php while ($sp = $supplierQuery->fetch_assoc()): ?>
                    <option value="<?= $sp['supplier'] ?>" <?= ($filter_supplier == $sp['supplier']) ? 'selected' : '' ?>>
                        <?= $sp['supplier'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <!-- STATUS FILTER -->
            <select name="status" class="filter-dropdown">
                <option value="All">All Statuses</option>
                <option value="Pending"   <?= ($filter_status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                <option value="Submitted" <?= ($filter_status == 'Submitted') ? 'selected' : '' ?>>Submitted</option>
                <option value="Closed"    <?= ($filter_status == 'Closed') ? 'selected' : '' ?>>Closed</option>
            </select>

            <button class="btn-clear" type="submit">Apply</button>
            <a href="message_list.php" class="btn-clear" style="text-decoration:none;">Clear</a>
        </form>
    </section>

    <!-- MESSAGE TABLE -->
    <section class="layout">
        <div class="requests-column">

            <div class="table-container">
                <table class="request-table">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all-messages"></th>
                        <th>Message Header</th>
                        <th>Preview</th>
                        <th>Supplier</th>
                        <th>Batch</th>
                        <th>Date Created</th>
                        <th>Date Sent</th>
                        <th>Status</th>
                        <th class="action-header">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if ($messages->num_rows == 0): ?>
                        <tr>
                            <td colspan="9" style="text-align:center; padding:20px;">
                                No messages found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($row = $messages->fetch_assoc()): ?>
                            <tr>
                                <td><input type="checkbox" class="message-checkbox"></td>

                                <td><?= htmlspecialchars($row['header']) ?></td>
                                <td><?= htmlspecialchars($row['preview']) ?></td>
                                <td><?= htmlspecialchars($row['supplier']) ?></td>
                                <td><?= htmlspecialchars($row['batch']) ?></td>
                                <td><?= htmlspecialchars($row['date_created']) ?></td>
                                <td><?= htmlspecialchars($row['date_sent']) ?></td>

                                <td>
                                    <span class="status-badge 
                                        <?= $row['status']=='Pending' ? 'status-pending' : 
                                           ($row['status']=='Submitted' ? 'status-submitted' : 'status-closed') ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>

                                <td class="message-action-col">
                                    <div class="action-dropdown">
                                        <button class="action-dots"><i class="fa-solid fa-ellipsis-vertical"></i></button>

                                        <div class="dropdown-menu">
                                            <button class="dropdown-item contact-option">
                                                <i class="fa-solid fa-envelope"></i> Contact
                                            </button>
                                            <button class="dropdown-item restock-option">
                                                <i class="fa-solid fa-box"></i> Restock
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-actions">
                <button class="btn-secondary" onclick="clearSelection()">Clear Selection</button>
                <button class="btn-primary">Create Purchase Request</button>
            </div>
        </div>
    </section>
</main>

<!-- CONTACT MODAL -->
<!-- <form id="contactForm">

    hidden fields
    <input type="hidden" id="modalSupplierValue" name="supplier">
    <input type="hidden" id="modalBatchValue" name="batch">

    <div class="form-group">
        <label>Subject</label>
        <input type="text" name="subject" required>
    </div>

    <div class="form-group">
        <label>Message</label>
        <textarea name="message" rows="5" required></textarea>
    </div>

    <div class="supplier-info">
        <strong>Supplier:</strong> <span id="modal-supplier"></span><br>
        <strong>Batch:</strong> <span id="modal-batch"></span>
    </div>

    <div class="modal-actions">
        <button type="button" class="btn-secondary" id="close-modal">Cancel</button>
        <button type="submit" class="btn-primary">Send Message</button>
    </div>
</form> -->


    </div>
</div>

<!-- RESTOCK MODAL -->
<div class="modal" id="restock-modal">
    <div class="modal-content">
        <h2>Send Restock Request</h2>
        <p class="modal-subtitle">This will send an automated restock message</p>

        <div class="supplier-info">
            <strong>Supplier:</strong> <span id="restock-supplier"></span><br>
            <strong>Batch:</strong> <span id="restock-batch"></span><br>
            <strong>Message:</strong> <span id="restock-message"></span>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" id="close-restock-modal">Cancel</button>
            <button type="button" class="btn-primary" id="confirm-restock">Send Restock Request</button>
        </div>
    </div>
</div>


<!-- JAVASCRIPT -->
<script>
// Checkbox counter
document.getElementById("select-all-messages").addEventListener("change", function () {
    document.querySelectorAll(".message-checkbox").forEach(cb => cb.checked = this.checked);
    updateSelected();
});

document.querySelectorAll(".message-checkbox").forEach(cb => {
    cb.addEventListener("change", updateSelected);
});

function updateSelected() {
    document.getElementById("selected-count").textContent =
        document.querySelectorAll(".message-checkbox:checked").length;
}

function clearSelection() {
    document.querySelectorAll(".message-checkbox").forEach(cb => cb.checked = false);
    document.getElementById("select-all-messages").checked = false;
    updateSelected();
}

// Action dropdowns
document.addEventListener("click", () => {
    document.querySelectorAll(".dropdown-menu").forEach(menu => menu.classList.remove("show"));
});

document.querySelectorAll(".action-dots").forEach(btn => {
    btn.addEventListener("click", e => {
        e.stopPropagation();
        btn.nextElementSibling.classList.toggle("show");
    });
});

// Contact Modal
document.querySelectorAll(".contact-option").forEach(btn => {
    btn.addEventListener("click", () => {

        const row = btn.closest("tr");

        const supplier = row.cells[3].textContent;
        const batch = row.cells[4].textContent;

        // visible text
        document.getElementById("modal-supplier").textContent = supplier;
        document.getElementById("modal-batch").textContent = batch;

        // hidden inputs
        document.getElementById("modalSupplierValue").value = supplier;
        document.getElementById("modalBatchValue").value = batch;

        // show modal
        document.getElementById("contactForm").style.display = "flex";
    });
});


document.getElementById("close-modal").onclick = () =>
    document.getElementById("contactForm").style.display = "none";

// Restock Modal
document.querySelectorAll(".restock-option").forEach(btn => {
    btn.addEventListener("click", () => {
        const row = btn.closest("tr");
        const supplier = row.cells[3].textContent;
        const batch = row.cells[4].textContent;
        const header = row.cells[1].textContent;

        document.getElementById("restock-supplier").textContent = supplier;
        document.getElementById("restock-batch").textContent = batch;
        document.getElementById("restock-message").textContent =
            `Requesting restock for batch ${batch} - ${header}`;

        document.getElementById("restock-modal").style.display = "flex";
    });
});

document.getElementById("close-restock-modal").onclick = () =>
    document.getElementById("restock-modal").style.display = "none";

document.getElementById("confirm-restock").onclick = () => {
    alert("Restock request sent successfully!");
    document.getElementById("restock-modal").style.display = "none";
};

function loadNotifications() {
    $.ajax({
        url: "get_notifications.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            let total = data.messages + data.lowstock;

            if (total > 0) {
                $("#notifBadge").text(total).show();
            } else {
                $("#notifBadge").hide();
            }
        }
    });
}

// Load on page open
loadNotifications();

// Refresh every 10 seconds
setInterval(loadNotifications, 10000);

// AJAX submit for contact form
document.getElementById("contactForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let formData = new FormData(this);

    fetch("send_message.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("Message sent successfully!");
            document.getElementById("contactForm").style.display = "none";

            // You can optionally reload message list
            location.reload();
        } else {
            alert("Error: " + data.error);
        }
    });
});

<script>
(function(){
  const notifBtn = document.getElementById('notifBtn');
  const dropdown = document.getElementById('notifDropdown');
  const notifList = document.getElementById('notifList');
  const notifCountEl = document.getElementById('notifCount');
  const soundEl = document.getElementById('notifSound');
  const notifyToggle = document.getElementById('notifyToggle');

  // persist user preference (notify on/off)
  const PREF_KEY = 'notify_enabled_v1';
  const stored = localStorage.getItem(PREF_KEY);
  if (stored !== null) notifyToggle.checked = stored === '1';

  notifyToggle.addEventListener('change', () => {
    localStorage.setItem(PREF_KEY, notifyToggle.checked ? '1' : '0');
    // if user disables, clear the count badge (the "🛑 stops")
    if (!notifyToggle.checked) {
      notifCountEl.style.display = 'none';
    }
  });

  // Toggle dropdown
  notifBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('show');
    notifBtn.setAttribute('aria-expanded', dropdown.classList.contains('show'));
  });

  // close when clicking outside
  document.addEventListener('click', () => {
    dropdown.classList.remove('show');
    notifBtn.setAttribute('aria-expanded', 'false');
  });

  // when user clicks an alert, go to relevant page
  function handleAlertClick(alert) {
    if (alert.type === 'message') {
      window.location.href = 'message_list.php';
    } else if (alert.type === 'lowstock') {
      window.location.href = 'lowstock.php';
    } else {
      window.location.href = 'message_list.php';
    }
  }

  // Render alerts
  function renderAlerts(data) {
    notifList.innerHTML = '';
    if (!data.alerts || data.alerts.length === 0) {
      notifList.innerHTML = '<div style="padding:14px; color:#666;">No recent alerts</div>';
      notifCountEl.style.display = 'none';
      return;
    }
    data.alerts.forEach(a => {
      const div = document.createElement('div');
      div.className = `notif-item ${a.type}`;
      div.innerHTML = `
        <div class="notif-dot"></div>
        <div style="flex:1">
          <div class="title">${escapeHtml(a.title)}</div>
          <div class="body">${escapeHtml(a.body)}</div>
          <div class="meta">${a.date_at}${a.meta ? ' • ' + escapeHtml(a.meta) : ''}</div>
        </div>
      `;
      div.addEventListener('click', () => handleAlertClick(a));
      notifList.appendChild(div);
    });
    // update count badge
    const total = data.counts.total || 0;
    if (total > 0 && notifyToggle.checked) {
      notifCountEl.style.display = 'inline-block';
      notifCountEl.textContent = total;
    } else {
      notifCountEl.style.display = 'none';
    }
  }

  // simple escape to avoid HTML injection
  function escapeHtml(s){ return (s||'').toString().replace(/[&<>"']/g, (m)=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' })[m]); }

  // polling & new notification detection
  let lastSeenTime = localStorage.getItem('notif_last_time') || '';
  function fetchNotifs() {
    fetch('notifications.php')
      .then(r => r.json())
      .then(data => {
        if (data.status !== 'ok') return;
        renderAlerts(data);

        // detect new
        const newTime = data.last_time || '';
        if (newTime && lastSeenTime && newTime !== lastSeenTime) {
          // there's an update since lastSeenTime
          if (notifyToggle.checked) {
            // play the sound
            try { soundEl.currentTime = 0; soundEl.play(); } catch(e){}
          }
        }
        // update stored last time (update always so page restarts view)
        lastSeenTime = newTime;
        localStorage.setItem('notif_last_time', newTime);
      })
      .catch(err => {
        console.error('notif error', err);
      });
  }

  // initial fetch
  fetchNotifs();
  // poll every 10 seconds (tune as needed)
  setInterval(fetchNotifs, 10000);

  // expose small helper so dev can manually refresh
  window.refreshNotifs = fetchNotifs;
})();
</script>

<script src="notification.js" defer></script>


</script>

</body>
</html>
