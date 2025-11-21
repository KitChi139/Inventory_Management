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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <button class="messages-btn" onclick="window.location.href='request_list.php'">
            <i class="fa-solid fa-list-check"></i> <span>Requests</span>
        </button>
        <button class="icon-btn"><i class="fa-solid fa-bell"></i></button>
        <div class="profile-icon"><i class="fa-solid fa-user"></i></div>
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
<div class="modal" id="contact-modal">
    <div class="modal-content">
        <h2>Contact Supplier</h2>
        <p class="modal-subtitle">Send a custom message to the supplier</p>

        <form>
            <div class="form-group">
                <label>Subject</label>
                <input type="text">
            </div>

            <div class="form-group">
                <label>Message</label>
                <textarea rows="5"></textarea>
            </div>

            <div class="supplier-info">
                <strong>Supplier:</strong> <span id="modal-supplier"></span><br>
                <strong>Batch:</strong> <span id="modal-batch"></span>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="close-modal">Cancel</button>
                <button type="submit" class="btn-primary">Send Message</button>
            </div>
        </form>
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
        document.getElementById("modal-supplier").textContent = row.cells[3].textContent;
        document.getElementById("modal-batch").textContent = row.cells[4].textContent;
        document.getElementById("contact-modal").style.display = "flex";
    });
});

document.getElementById("close-modal").onclick = () =>
    document.getElementById("contact-modal").style.display = "none";

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
</script>

</body>
</html>
