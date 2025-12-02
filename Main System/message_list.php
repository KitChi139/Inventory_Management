<?php
require 'db_connect.php';

// Protect page - require login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("Location: login.php");
  exit();
}

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
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="message_list.css">
    <link rel="stylesheet" href="notification.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar" aria-label="Primary">
        <div class="profile">
            <div class="icon">
                <img src="logo.png?v=2" alt="MediSync Logo" class="medisync-logo">
            </div>
            <button class="toggle" id="toggleBtn"><i class="fa-solid fa-bars"></i></button>
        </div>

        
        <ul class="menu">
            <li id="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
            <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
            <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
            <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
            <li id="nav-suppliers"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
            <li id="reports"><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
            <li id="users"><i class="fa-solid fa-users"></i><span>Users</span></li>
            <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
            <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main">
        <!-- Notification + Profile icon (top-right) -->
        <div class="topbar-right">
            <button class="messages-btn" onclick="window.location.href='request_list.php'">
                <i class="fa-solid fa-list-check"></i>
                <span>Requests</span>
            </button>
            <?php include 'notification_component.php'; ?>
            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>

        <!-- Heading Bar -->
        <div class="heading-bar">
            <h1>Message List</h1>   
        </div>

        <!-- Statistics Cards -->
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

        <!-- Filters -->
        <section class="filters">
            <form method="GET" style="display:flex; width:100%; gap:12px;">
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Search messages or suppliers..."
                    value="<?= htmlspecialchars($search) ?>"
                >
                <select name="supplier" class="filter-dropdown">
                    <option value="All">All Suppliers</option>
                    <?php while ($sp = $supplierQuery->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($sp['supplier']) ?>" <?= ($filter_supplier == $sp['supplier']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sp['supplier']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <select name="status" class="filter-dropdown">
                    <option value="All">All Statuses</option>
                    <option value="Pending"   <?= ($filter_status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Submitted" <?= ($filter_status == 'Submitted') ? 'selected' : '' ?>>Submitted</option>
                    <option value="Closed"    <?= ($filter_status == 'Closed') ? 'selected' : '' ?>>Closed</option>
                </select>
                <button class="btn-clear" type="submit">Apply</button>
                <a href="message_list.php" class="btn-clear" style="text-decoration:none; display:inline-block;">Clear</a>
            </form>
        </section>

        <!-- Message Table -->
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
                                    <td colspan="9" style="text-align:center; padding:20px; color:#777;">
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
                                        <td><?= htmlspecialchars($row['date_sent'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="status-badge 
                                                <?= $row['status']=='Pending' ? 'status-pending' : 
                                                   ($row['status']=='Submitted' ? 'status-submitted' : 'status-closed') ?>">
                                                <?= htmlspecialchars($row['status']) ?>
                                            </span>
                                        </td>
                                        <td class="message-action-col">
                                            <div class="action-dropdown">
                                                <button class="action-dots"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                                <div class="dropdown-menu">
                                                    <button class="dropdown-item contact-option"><i class="fa-solid fa-envelope"></i>Contact</button>
                                                    <button class="dropdown-item restock-option"><i class="fa-solid fa-box"></i>Restock</button>
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

    <!-- Contact Modal -->
    <div class="modal" id="contact-modal" style="display:none;">
        <div class="modal-content">
            <h2>Contact Supplier</h2>
            <p class="modal-subtitle">Send a custom message to the supplier</p>
            <form class="modal-form" id="contact-form">
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" id="contact-subject" placeholder="Enter subject" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea id="contact-message" placeholder="Enter your message" rows="5" required></textarea>
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

    <!-- Restock Confirmation Modal -->
    <div class="modal" id="restock-modal" style="display:none;">
        <div class="modal-content">
            <h2>Send Restock Request</h2>
            <p class="modal-subtitle">This will send an automated restock message to the supplier</p>
            <div class="supplier-info" style="margin: 20px 0;">
                <strong>Supplier:</strong> <span id="restock-supplier"></span><br>
                <strong>Batch:</strong> <span id="restock-batch"></span><br>
                <strong>Message:</strong> <span id="restock-message"></span>
            </div>
            <p style="color: #666; font-size: 14px;">An automated message will be sent requesting restock for this item.</p>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="close-restock-modal">Cancel</button>
                <button type="button" class="btn-primary" id="confirm-restock">Send Restock Request</button>
            </div>
        </div>
    </div>

    <script>
    <script src="sidebar.js"></script>
    <script>

        const totalSelectedEl = document.querySelector('.stat-card:nth-child(2) .stat-number');

        // Select all checkbox
        document.getElementById('select-all-messages').addEventListener('change', (e) => {
            const checkboxes = document.querySelectorAll('.message-checkbox');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            updateSelectedMessages();
        });

        // Individual checkbox change
        document.querySelectorAll('.message-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const checkboxes = document.querySelectorAll('.message-checkbox');
                const allChecked = [...checkboxes].every(box => box.checked);
                document.getElementById('select-all-messages').checked = allChecked;
                updateSelectedMessages();
            });
        });

        function updateSelectedMessages() {
            const selected = document.querySelectorAll('.message-checkbox:checked').length;
            totalSelectedEl.textContent = selected;
        }

        function clearSelection() {
            document.querySelectorAll('.message-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('select-all-messages').checked = false;
            updateSelectedMessages();
        }

        // Dropdown menu functionality
        document.querySelectorAll('.action-dots').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    if (menu !== btn.nextElementSibling) {
                        menu.classList.remove('show');
                    }
                });
                // Toggle current dropdown
                btn.nextElementSibling.classList.toggle('show');
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        });

        // Contact option - opens custom message modal
        document.querySelectorAll('.contact-option').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const row = btn.closest('tr');
                const supplier = row.cells[3].textContent;
                const batch = row.cells[4].textContent;
                
                document.getElementById('modal-supplier').textContent = supplier;
                document.getElementById('modal-batch').textContent = batch;
                document.getElementById('contact-modal').style.display = 'flex';
                
                // Close dropdown
                btn.closest('.dropdown-menu').classList.remove('show');
            });
        });

        // Restock option - opens automated message confirmation
        document.querySelectorAll('.restock-option').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const row = btn.closest('tr');
                const supplier = row.cells[3].textContent;
                const batch = row.cells[4].textContent;
                const header = row.cells[1].textContent;
                
                document.getElementById('restock-supplier').textContent = supplier;
                document.getElementById('restock-batch').textContent = batch;
                document.getElementById('restock-message').textContent = `"Please restock items from batch ${batch} - ${header}"`;
                document.getElementById('restock-modal').style.display = 'flex';
                
                // Close dropdown
                btn.closest('.dropdown-menu').classList.remove('show');
            });
        });

        // Close contact modal
        document.getElementById('close-modal').addEventListener('click', () => {
            document.getElementById('contact-modal').style.display = 'none';
        });

        document.getElementById('contact-modal').addEventListener('click', (e) => {
            if (e.target.id === 'contact-modal') {
                document.getElementById('contact-modal').style.display = 'none';
            }
        });

        // Close restock modal
        document.getElementById('close-restock-modal').addEventListener('click', () => {
            document.getElementById('restock-modal').style.display = 'none';
        });

        document.getElementById('restock-modal').addEventListener('click', (e) => {
            if (e.target.id === 'restock-modal') {
                document.getElementById('restock-modal').style.display = 'none';
            }
        });

        // Confirm restock
        document.getElementById('confirm-restock').addEventListener('click', () => {
            alert('Automated restock request sent successfully!');
            document.getElementById('restock-modal').style.display = 'none';
        });

        // Contact form submission
        document.getElementById('contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const subject = document.getElementById('contact-subject').value;
            const message = document.getElementById('contact-message').value;
            const supplier = document.getElementById('modal-supplier').textContent;
            const batch = document.getElementById('modal-batch').textContent;
            
            // Send via AJAX
            $.ajax({
                url: 'send_message.php',
                method: 'POST',
                data: {
                    subject: subject,
                    message: message,
                    supplier: supplier,
                    batch: batch
                },
                success: function(response) {
                    alert('Message sent successfully!');
                    document.getElementById('contact-modal').style.display = 'none';
                    document.getElementById('contact-form').reset();
                    location.reload();
                },
                error: function() {
                    alert('Error sending message. Please try again.');
                }
            });
        });

        // Sidebar toggle
        // Sidebar toggle handled by sidebar.js

        // Navigation handlers
        $("#dashboard").click(function(){ window.location.href = "dashboard.php"; });
        $("#inventory").click(function(){ window.location.href = "Inventory.php"; });
        $("#low-stock").click(function(){ window.location.href = "lowstock.php"; });
        $("#request").click(function(){ window.location.href = "request_list.php"; });
        $("#nav-suppliers").click(function(){ window.location.href = "supplier.php"; });
        $("#reports").click(function(){ window.location.href = "report.php"; });
        $("#users").click(function(){ window.location.href = "admin.php"; });
        $("#settings").click(function(){ window.location.href = "settings.php"; });
        $("#logout").click(function(){ window.location.href = "logout.php"; });
    </script>
    <script src="notification.js" defer></script>
</body>
</html>
