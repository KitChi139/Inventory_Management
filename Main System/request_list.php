<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Request List</title>
  <link rel="stylesheet" href="request_list.css">
  <link rel="stylesheet" href="notification.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  

  <style>
    /* i did this because it wont work in the css file */
/* === FILTERS SECTION (aligned horizontally) === */
.filters {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 15px;
}

.filters input[type="text"] {
  flex: 1;
  padding: 10px 14px;
  border: 1px solid #d3d3d3;
  border-radius: 8px;
  background-color: #fff;
  font-size: 14px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.filter-right {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}

.filter-right select {
  padding: 10px 14px;
  border: 1px solid #d3d3d3;
  border-radius: 8px;
  background-color: #fff;
  font-size: 14px;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  transition: border-color 0.2s ease;
}

.filter-right select:focus {
  outline: none;
  border-color: #4f9cf9;
  box-shadow: 0 0 0 2px rgba(79,156,249,0.3);
}

/* === TABLE ACTION BUTTONS (bottom-right corner) === */
.table-actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-top: 25px;
  padding-top: 15px;
  border-top: 1px solid #e0e0e0;
  position: relative;
}

#selectedCount {
  position: absolute;
  left: 0;
  color: #043873;
  font-weight: 600;
  bottom: 15px;
}

.buttons {
  display: flex;
  gap: 10px;
}

.btn {
  padding: 10px 22px;
  border: 1px solid #ccc;
  border-radius: 30px;
  cursor: pointer;
  background: #fff;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.25s ease;
}

.btn.clear {
  color: #333;
}

.btn.create {
  background: #4f9cf9;
  color: #fff;
  border: none;
  padding: 12px 30px;
  font-size: 15px;
  font-weight: 700;
  border-radius: 40px;
  box-shadow: 0 4px 10px rgba(79,156,249,0.3);
}

.btn.create:hover {
  background: #1c74e9;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(79,156,249,0.35);
}
</style>

</head>
<body>

<aside class="sidebar" aria-label="Primary">
  <div class="profile">
    <div class="icon"><i class="fa-solid fa-user"></i></div>
    <button class="toggle"><i class="fa-solid fa-bars"></i></button>
  </div>

  <h3 class="title">Navigation</h3>
  
  <nav>
    <ul class="menu">
      <li id="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
      <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
      <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
      <li class="active"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
      <li id="nav-suppliers"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
      <li id="reports"><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
      <li id="users"><i class="fa-solid fa-users"></i><span>Users</span></li>
      <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
      <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
    </ul>
  </nav>
</aside>

<?php
// LOAD 3 MOST RECENT MESSAGES
$recentMessages = $conn->query("
    SELECT header, supplier, date_created 
    FROM messages 
    ORDER BY date_created DESC 
    LIMIT 3
");
?>


<main class="main">
  <header class="topbar">
    <div class="top-left"><h2>Request List</h2></div>
    <div class="top-right">
  <!-- MESSAGE ICON -->
  <button class="icon-btn"><i id="openMessages" class="fa-solid fa-message"></i></button>


  <!-- NOTIFICATION DROPDOWN -->
<div class="notif-wrap">
  <button class="notif-btn" id="notifBtn">
    <i class="fa-solid fa-bell"></i>
    <span class="notif-count" id="notifCount">0</span>
  </button>

  <div class="notif-dd" id="notifDropdown">
    <div class="dd-header">
      <h4>Notifications</h4>
      <div class="notif-settings">
        <input type="checkbox" id="notifyToggle" checked>
        <label for="notifyToggle">Notify</label>
      </div>
    </div>
    <div class="dd-list" id="notifList"></div>
    <div class="notif-footer">
      <a href="message_list.php">See all messages / low stock</a>
    </div>
  </div>
</div>


<!-- notification sound -->
<audio id="notifSound" src="notification_ping.mp3" preload="auto"></audio>

</div>

  </header>

  <div class="cards">
    <div class="card blue">
      <h3>Total Requests</h3>
      <p>
        <?php 
          $sql = "SELECT COUNT(*) AS total FROM requests";
          $res = $conn->query($sql);
          echo $res->fetch_assoc()['total'];
        ?>
      </p>
    </div>
    <div class="card yellow">
      <h3>Pending</h3>
      <p>
        <?php 
          $sql = "SELECT COUNT(*) AS total FROM requests WHERE status='Pending'";
          $res = $conn->query($sql);
          echo $res->fetch_assoc()['total'];
        ?>
      </p>
    </div>
    <div class="card green">
      <h3>Approved</h3>
      <p>
        <?php 
          $sql = "SELECT COUNT(*) AS total FROM requests WHERE status='Approved'";
          $res = $conn->query($sql);
          echo $res->fetch_assoc()['total'];
        ?>
      </p>
    </div>
  </div>

  <div class="table-section">
    <div class="filters">
  <input type="text" id="search" placeholder="Search requests...">
  
  <div class="filter-right">
    <select id="supplierFilter">
      <option value="all">All Suppliers</option>
      <?php
        $suppliers = $conn->query("SELECT DISTINCT supplier_name FROM suppliers");
        while($s = $suppliers->fetch_assoc()) {
          echo "<option value='{$s['supplier_name']}'>{$s['supplier_name']}</option>";
        }
      ?>
    </select>
    
    <select id="statusFilter">
      <option value="all">All Statuses</option>
      <option value="Pending">Pending</option>
      <option value="Approved">Approved</option>
      <option value="Rejected">Rejected</option>
    </select>
  </div>
</div>

<table id="requestTable">
  <thead>
    <tr>
      <th><input type="checkbox" id="selectAll"></th>
      <th>Request ID</th>
      <th>Product</th>
      <th>Quantity</th>
      <th>Requester</th>
      <th>Supplier</th>
      <th>Status</th>
      <th>Date</th>
    </tr>
  </thead>
  <tbody>
    <?php
      $sql = "SELECT r.request_id, p.ProductName, r.quantity, r.requester, r.status, r.request_date, s.supplier_name
              FROM requests r
              JOIN products p ON r.ProductID = p.ProductID
              LEFT JOIN suppliers s ON s.supplier_id = p.CategoryID
              ORDER BY r.request_date DESC";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          echo "<tr data-supplier='{$row['supplier_name']}' data-status='{$row['status']}'>
                  <td><input type='checkbox' class='row-check'></td>
                  <td>{$row['request_id']}</td>
                  <td>{$row['ProductName']}</td>
                  <td>{$row['quantity']}</td>
                  <td>{$row['requester']}</td>
                  <td>{$row['supplier_name']}</td>
                  <td><span class='status {$row['status']}'>{$row['status']}</span></td>
                  <td>{$row['request_date']}</td>
                </tr>";
        }
      }
    ?>
  </tbody>
</table>

<div class="table-actions">
  <span id="selectedCount">0 selected</span>
  <div class="buttons">
    <button id="clearSelection" class="btn clear">Clear Selection</button>
    <button id="createRequest" class="btn create">Create Purchase Request</button>
  </div>
</div>

<!-- MESSAGE POPUP MODAL -->
<div id="messageModal" class="msg-modal">
  <div class="msg-modal-content">

      <div class="msg-header">
        <h3>Messages</h3>
        <span id="closeMsg" class="close-btn">&times;</span>
      </div>

      <input type="text" class="msg-search" placeholder="Search messages...">

      <div class="msg-list">
      <?php while($m = $recentMessages->fetch_assoc()): ?>
    <div class="msg-item">
    <div class="msg-avatar">
        <?= strtoupper(substr($m['supplier'], 0, 2)) ?>
    </div>

    <div class="msg-details">
        <strong><?= htmlspecialchars($m['supplier']) ?></strong>
        <span class="msg-date"><?= date("M d, Y", strtotime($m['date_created'])) ?></span>
        <div class="msg-preview">
            <?= htmlspecialchars($m['header']) ?>
        </div>
    </div>
</div>
<?php endwhile; ?>


        <div class="see-more-container">
    <a href="message_list.php" class="see-more">See More</a>
</div>

      </div>
  </div>
</div>

  </div>
</main>

<script>
$(function () {
  $(".toggle").click(() => $(".sidebar").toggleClass("hide"));

  // Search Filter
  $("#search").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#requestTable tbody tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
  });

  // Supplier Filter
  $("#supplierFilter, #statusFilter").change(function(){
    var supplier = $("#supplierFilter").val();
    var status = $("#statusFilter").val();

    $("#requestTable tbody tr").each(function(){
      var matchSupplier = supplier === "all" || $(this).data("supplier") === supplier;
      var matchStatus = status === "all" || $(this).data("status") === status;
      $(this).toggle(matchSupplier && matchStatus);
    });
  });

  // Checkbox logic
  $("#selectAll").on("change", function() {
    $(".row-check").prop("checked", this.checked);
    updateSelection();
  });

  $("#requestTable").on("change", ".row-check", function() {
    updateSelection();
  });

  $("#clearSelection").click(function(){
    $(".row-check").prop("checked", false);
    $("#selectAll").prop("checked", false);
    updateSelection();
  });

  function updateSelection(){
    var selected = $(".row-check:checked").length;
    $("#selectedCount").text(selected + " selected");
  }

  // Placeholder for Create Purchase Request
  $("#createRequest").click(function(){
    var selected = $(".row-check:checked");
    if(selected.length === 0){
      alert("Please select at least one item.");
      return;
    }
    alert(selected.length + " item(s) selected for purchase request!");
  });

  // MESSAGE POPUP
$("#openMessages").click(function() {
  $("#messageModal").css("display", "flex");
});

// NOTIFICATION DROPDOWN OPEN/CLOSE
$("#notifBtn").click(function(e){
    e.stopPropagation();
    $("#notifDropdown").toggleClass("show");

    if ($("#notifDropdown").hasClass("show")) {
        loadNotificationDropdown();
    }
});


$("#closeMsg").click(function() {
  $("#messageModal").hide();
});

// close when clicking outside
$(window).on("click", function(e) {
  if (e.target.id === "messageModal") {
    $("#messageModal").hide();
  }
});

// SEARCH BAR – FILTER MESSAGES
const searchInput = document.querySelector(".msg-search");
const messageItems = document.querySelectorAll(".msg-item");

searchInput.addEventListener("keyup", () => {
    const value = searchInput.value.toLowerCase();

    messageItems.forEach(item => {
        const text = item.innerText.toLowerCase();

        if (text.includes(value)) {
            item.style.display = "flex";
        } else {
            item.style.display = "none";
        }
    });
});

// AJAX SEARCH — fetch results from database
$(".msg-search").on("keyup", function () {
    let searchVal = $(this).val();

    $.ajax({
        url: "search_messages.php",
        method: "POST",
        data: { search: searchVal },
        success: function (data) {
            $(".msg-list").html(data);
        }
    });
});

  // Navigation
  $("#dashboard").click(()=> window.location.href = "dashboard.php");
  $("#inventory").click(()=> window.location.href = "inventory.php");
  $("#low-stock").click(()=> window.location.href = "lowstock.php");
  $("#nav-suppliers").click(()=> window.location.href = "supplier.php");
  $("#logout").click(()=> window.location.href = "logout.php");


});

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

// close when clicking outside
$(document).click(function () {
    $("#notifDropdown").removeClass("show");
});

function loadNotificationDropdown() {
    $.ajax({
        url: "get_notifications.php",
        method: "GET",
        dataType: "json",
        success: function(data) {

            let list = $("#notifList");
            list.html(""); // clear

            // Recent Messages
            if (data.messages > 0) {
                list.append(`
                    <div class="notif-item msg">
                        <div class="left">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div class="right">
                            <p><strong>${data.messages} New Messages</strong></p>
                            <span>Click to view</span>
                        </div>
                    </div>
                `);
            }

            // Low Stock Alerts
            if (data.lowstock > 0) {
                list.append(`
                    <div class="notif-item lowstock">
                        <div class="left">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="right">
                            <p><strong>${data.lowstock} Low Stock Items</strong></p>
                            <span>Click to view</span>
                        </div>
                    </div>
                `);
            }

            if (data.messages === 0 && data.lowstock === 0) {
                list.html(`<div style="padding:12px; color:#777;">No new notifications</div>`);
            }

            // CLICK ACTIONS
            $(".notif-item.msg").click(() => window.location.href = "message_list.php");
            $(".notif-item.lowstock").click(() => window.location.href = "lowstock.php");
        }
    });
}


</script>

<script src="notification.js" defer></script>
</body>
</html>
