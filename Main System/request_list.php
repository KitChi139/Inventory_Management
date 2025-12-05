<?php
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("Location: login.php");
  exit();
}


$conn->query("CREATE TABLE IF NOT EXISTS requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    quantity INT NOT NULL,
    requester VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_approved TIMESTAMP NULL,
    FOREIGN KEY (ProductID) REFERENCES products(ProductID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$sql = "SELECT 
        b.BatchID,
        b.request_date,
        b.shipping_date,
        b.complete_date,
        com.companyName,
        b.status AS batch_status
    FROM batches b
    LEFT JOIN requests r ON r.BatchID = b.BatchID
    LEFT JOIN suppliers s ON s.SupplierID = r.SupplierID
    LEFT JOIN company com ON com.comID = s.comID
    GROUP BY b.BatchID
    ORDER BY b.BatchID DESC";

    
$batchList = $conn->query($sql);

$inventoryItems = $conn->query($sql);

$totalItems = $conn->query("SELECT COUNT(*) AS total FROM requests")->fetch_assoc()['total'];
$criticalItems = $conn->query("SELECT COUNT(*) AS total FROM inventory WHERE Quantity <= 5")->fetch_assoc()['total'];

$suppliers = $conn->query("SELECT DISTINCT CompanyName FROM company WHERE CompanyName IS NOT NULL");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request List</title>
    <link rel="stylesheet" href="styles/sidebar.css">
    <style>

  .has-dropdown {
    position: relative;
  }

  .has-dropdown .dropdown-menu {
    display: none;
    position: absolute;
    top: 100%; 
    left: 0;
    background: white;
    list-style: none;
    padding: 0;
    margin: 0;
    min-width: 220px;
    border-radius: var(--radius);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    z-index: 10;
  }

  .has-dropdown:hover .dropdown-menu {
    display: block;
  }

  .has-dropdown .dropdown-menu li {
    padding: 12px 16px;
    cursor: pointer;
    transition: 0.2s;
  }

  .has-dropdown .dropdown-menu li:hover {
    background-color: #f0f6ff;
  }
        </style>
    <link rel="stylesheet" href="styles/request_list.css">
    <link rel="stylesheet" href="styles/notification.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

<aside class="sidebar" id="sidebar">
  <div class="profile">
    <div class="icon">
      <img src="logo.png?v=2" alt="MediSync Logo" class="medisync-logo">
    </div>
    <button class="toggle" id="toggleBtn"><i class="fa-solid fa-bars"></i></button>
  </div>
      <ul class="menu">
        <li id="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
        <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
            <li id="request" class="active"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
    <li class="nav-reports has-dropdown">
    <i class="fa-solid fa-file-lines"></i><span>Reports</span>
    <ul class="dropdown-menu" >
  <li id="inventorymanagement">
    <a class="report-link">Inventory Management</a>
  </li>
  <li id="expirationwastage">
    <a class="report-link">Expiration / Wastage</a>
  </li>


    </ul>
  </li>

        <?php if ($_SESSION['roleName'] === 'Admin'): ?>
        <li id="users"><i class="fa-solid fa-users"></i><span>Users</span></li>
        <?php endif; ?>
        <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
        <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
      </ul>
</aside>

    <main class="main">

          <div class="heading-bar">
    <h1>Request List</h1>
  </div>

        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-title">Total Items</div>
                <div class="stat-number"><?= $totalItems ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Selected Items</div>
                <div class="stat-number" id="selected-count">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Critical Items</div>
                <div class="stat-number"><?= $criticalItems ?></div>
            </div>
        </section>

        <section class="filters">
            <input type="text" class="search-input" id="search-input" placeholder="Search batch number or item name...">
            <select class="filter-dropdown" id="supplier-filter">
                <option value="">All Suppliers</option>
                <?php while($s = $suppliers->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($s['CompanyName']) ?>"><?= htmlspecialchars($s['CompanyName']) ?></option>
                <?php endwhile; ?>
            </select>
            <select class="filter-dropdown" id="category-filter">
                <option value="">All Categories</option>
                  <?php
                  $catRes = $conn->query("SELECT Category_Name FROM categories ORDER BY Category_Name");
                  while ($cat = $catRes->fetch_assoc()):
                  ?>
                    <option value="<?= htmlspecialchars($cat['Category_Name']) ?>"><?= htmlspecialchars($cat['Category_Name']) ?></option>
                  <?php endwhile; ?>
            </select>
            <button class="btn-clear" id="clear-filters">Clear Selection</button>
        </section>

        <section class="layout">
            <div class="requests-column">
                <div class="table-container">
                    <table class="request-table">
                        <thead>
                            <tr>
                                <th>Batch ID</th>
                                <th>Status</th>
                                <th>Request Date</th>
                                <th>Shipping Date</th>
                                <th>Completed Date</th>
                                <th>Supplier</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
<?php while ($batch = $batchList->fetch_assoc()): ?>
<tr>
    <td><?= str_pad($batch['BatchID'], 4, "0", STR_PAD_LEFT) ?></td>
    <td><?= htmlspecialchars($batch['batch_status']) ?></td>
    <td><?= htmlspecialchars($batch['request_date']) ?></td>
    <td><?= htmlspecialchars($batch['shipping_date'] ?? 'N/A') ?></td>
    <td><?= htmlspecialchars($batch['complete_date'] ?? 'N/A') ?></td>
    <td><?= htmlspecialchars($batch['companyName'] ?? 'N/A') ?></td>
    <td>
        <button class="btn-view-batch" 
                data-batchid="<?= $batch['BatchID'] ?>">
            View Batch
        </button>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

<div class="modal" id="batch-modal" style="display:none;">
    <div class="modal-content">
        <h2>Batch Details</h2>

        <div class="batch-info">
            <p><strong>Batch ID:</strong> <span id="modal-batch-id"></span></p>
            <p><strong>Status:</strong> <span id="modal-batch-status"></span></p>
            <p><strong>Shipping Date:</strong> <span id="modal-shipping"></span></p>
            <p><strong>Completion Date:</strong> <span id="modal-complete"></span></p>
        </div>

        <h3>Items in Batch</h3>
        <table class="modal-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Status</th>
                    <th>Supplier</th>
                    <th>Approved Date</th>
                    <th>Declined Date</th>
                    <th>Completed Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="batch-items-body">

            </tbody>
        </table>
        <div class="modal-actions">
            <button class="btn-primary" id="mark-batch-complete">
                Mark Batch Complete
            </button>
        </div>
        <button class="btn-secondary close-batch-modal">Close</button>
    </div>
</div>

    <div class="modal" id="contact-modal" style="display:none;">
        <div class="modal-content">
            <h2>Contact Supplier</h2>
            <p class="modal-subtitle">Send a message to the supplier</p>
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
    <div class="modal" id="warning-modal">
        <div class="modal-content warning-modal-content">
            <h2 class="modal-title">
                <i class="fa-solid fa-triangle-exclamation"></i> Warning
            </h2>
            <p id="warning-message"></p>

            <div class="modal-actions">
                <button class="btn-secondary" id="close-warning">Close</button>
            </div>
        </div>
    </div>
    <div class="modal" id="confirmation-modal" style="display:none;">
        <div class="modal-content warning-modal-content">
            <h2 class="modal-title">
                <i class="fa-solid fa-circle-exclamation"></i> Confirm Action
            </h2>
            <p id="confirmation-message">Are you sure?</p>
            <div class="modal-actions">
                <button class="btn-secondary" id="cancel-confirmation">Cancel</button>
                <button class="btn-primary" id="confirm-action">Yes</button>
            </div>
        </div>
    </div>
    <script src="sidebar.js"></script>
    <script>
        let confirmCallback = null; 

        function showConfirmation(message, callback) {
            $('#confirmation-message').text(message);
            confirmCallback = callback;
            $('#confirmation-modal').fadeIn(150);
        }

        $('#cancel-confirmation').on('click', function() {
            $('#confirmation-modal').fadeOut(150);
            confirmCallback = null;
        });

        $('#confirm-action').on('click', function() {
            if (confirmCallback) confirmCallback();
            $('#confirmation-modal').fadeOut(150);
            confirmCallback = null;
        });

        $('#confirmation-modal').on('click', function(e) {
            if (e.target.id === 'confirmation-modal') $('#confirmation-modal').fadeOut(150);
        });
    $(document).on('click', '.btn-view-batch', function() {
    const batchID = $(this).data('batchid');

    $('#modal-batch-id').text(batchID);
    $('#batch-items-body').html('<tr><td colspan="9">Loading...</td></tr>');
    $('#batch-modal').show();

$.ajax({
    url: 'request_list_fetch.php',
    type: 'POST',
    data: { batchID },
    success: function(res) {
        const data = JSON.parse(res);
        $('#batch-items-body').html(data.html);

        if (data.batch_status && data.batch_status.toLowerCase() === 'completed') {
            $("#mark-batch-complete")
                .prop("disabled", true)
                .css("opacity", "0.5")
                .text("Batch Complete");
        } else if (data.incomplete > 0) {
            $("#mark-batch-complete")
                .prop("disabled", true)
                .css("opacity", "0.5")
                .text("Mark Batch Complete");

            $("#warning-message").html(`⚠ ${data.incomplete} item(s) still incomplete`);
            $("#warning-modal").fadeIn(150);
        } else {
            $("#mark-batch-complete")
                .prop("disabled", false)
                .css("opacity", "1")
                .text("Mark Batch Complete");

            $("#warning-modal").fadeOut(0); 
        }
    },
    error: function() {
        $('#batch-items-body').html('<tr><td colspan="9">Error loading data</td></tr>');
    }
});

});


    $('.close-batch-modal').on('click', () => $('#batch-modal').hide());
    $('#batch-modal').on('click', function(e) {
        if (e.target.id === 'batch-modal') $(this).hide();
    });

        $('#search-input').on('keyup', function() {
            const search = $(this).val().toLowerCase();
            const category = $('#category-filter').val().toLowerCase();
            const status = $('#status-filter').val()?.toLowerCase() || '';
            
            $('.request-table tbody tr').each(function() {
                const text = $(this).text().toLowerCase();
                const rowCategory = $(this).find('td:nth-child(4)').text().toLowerCase(); // Category column
                const rowStatus = $(this).data('status')?.toLowerCase() || '';
                
                const matchesSearch = text.includes(search);
                const matchesCategory = !category || rowCategory.includes(category);
                const matchesStatus = !status || rowStatus.includes(status);
                
                $(this).toggle(matchesSearch && matchesCategory && matchesStatus);
            });
        });

        $('#category-filter, #status-filter').on('change', function() {
            $('#search-input').trigger('keyup');
        });

        $('#clear-filters').on('click', function() {
            $('#search-input').val('');
            $('#category-filter').val('');
            $('#status-filter').val('');
            $('#search-input').trigger('keyup');
        });

        $(document).on("click", ".item-complete-btn", function () {
    const requestID = $(this).data("requestid");

    showConfirmation("Are you sure you want to mark this item as completed?", function() {
        $.ajax({
            url: "request_list_item_complete.php",
            method: "POST",
            data: { requestID },
            success: function (res) {
                res = res.trim(); 

                if (res === "OK") {
                    const batchID = $("#modal-batch-id").text();
                    $.post("request_list_fetch.php", { batchID }, function (result) {
                        const data = JSON.parse(result);
                        $('#batch-items-body').html(data.html);

                        if (data.incomplete > 0) {
                            $("#mark-batch-complete")
                                .prop("disabled", true)
                                .css("opacity", "0.5");
                            if ($("#incomplete-warning").length === 0) {
                                $(".modal-actions").prepend(`
                                    <p id="incomplete-warning" 
                                    style="color:#d9534f;font-weight:bold;margin-bottom:10px;">
                                    ⚠ ${data.incomplete} item(s) still incomplete
                                    </p>
                                `);
                            } else {
                                $("#incomplete-warning")
                                    .text(`⚠ ${data.incomplete} item(s) still incomplete`);
                            }
                        } else {
                            $("#mark-batch-complete")
                                .prop("disabled", false)
                                .css("opacity", "1");
                            $("#incomplete-warning").remove();
                        }
                    });
                    return;
                }

                let msg = "";
                if (res === "NOT_APPROVED") msg = "❌ Cannot complete: Item is still Pending approval.";
                else if (res === "DECLINED_CANNOT_COMPLETE") msg = "❌ Cannot complete: Item was Declined.";
                else if (res === "ALREADY_DONE") msg = "⚠ This item is already marked as completed.";
                else if (res === "NOT_FOUND") msg = "❌ Request not found.";
                else msg = "Unexpected Error: " + res;

                $("#warning-message").html(msg);
                $("#warning-modal").fadeIn(150);
            }
        });
    });

    });
$("#mark-batch-complete").on("click", function () {
    const batchID = $("#modal-batch-id").text();

    showConfirmation("Are you sure you want to mark the entire batch as completed and add stocks into inventory?", function() {
        $.ajax({
            url: "request_list_batch_complete.php",
            method: "POST",
            data: { batchID },
            success: function (res) {
                res = res.trim()
                if (res === "INCOMPLETE") {
                    alert("Cannot complete batch. All items must be completed first.");
                    return;
                }
                if (res === "OK") {
                    alert("Batch marked as complete and stocks added to inventory!");
                    location.reload();
                } else {
                    alert("Unexpected response: " + res);
                }
            },
            error: function () {
                alert("Server error while completing batch.");
            }
        });
    });
});

$("#close-warning").on("click", function () {
    $("#warning-modal").fadeOut(150);
});

$("#warning-modal").on("click", function (e) {
    if (e.target.id === "warning-modal") $("#warning-modal").fadeOut(150);
});

        const closeModalBtn = document.getElementById('close-modal');
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => {
                document.getElementById('contact-modal').style.display = 'none';
            });
        }

        document.getElementById('contact-modal').addEventListener('click', (e) => {
            if (e.target.id === 'contact-modal') {
                document.getElementById('contact-modal').style.display = 'none';
            }
        });

        $("#toggleBtn").click(function() {
            $("#sidebar").toggleClass("hide");
        });

        $(document).ready(function () {

        $("#dashboard").click(function(){ window.location.href = "dashboard.php"; });
        $("#inventory").click(function(){ window.location.href = "Inventory.php";});
        $("#request").click(function(){ window.location.href = "request_list.php";});
        $("#inventorymanagement").click(function(){ window.location.href = "report_inventory.php";});
        $("#expirationwastage").click(function(){ window.location.href = "report_expiration.php";});
  $(document).ready(function(){
    const current = window.location.pathname.split("/").pop(); 

    $(".report-link").each(function(){
      const link = $(this).attr("href");
      if(link === current){
        $(this).addClass("active");
        $("#reports").addClass("active"); 
      }
    });
  });

        $("#users").click(function(){ window.location.href = "admin.php"; });
        $("#settings").click(function(){ window.location.href = "settings.php"; });
        $("#logout").click(function(){ window.location.href = "logout.php"; });
      });

       $(document).on("click", ".report-link", function(e){
      e.stopPropagation();
      const view = $(this).data("view");
      $("#view-title").text($(this).text());
      $("#view-content").removeClass("cards-container").html(views[view]);
      validateInventoryReport(); 
  }); 
$("#reports").click(function(e){
    e.stopPropagation();
    $(this).toggleClass("active");
});

    </script>
    <script src="notification.js" defer></script>
</body>
</html>
