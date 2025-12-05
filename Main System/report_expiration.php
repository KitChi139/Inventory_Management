        <?php
        require 'db_connect.php';

        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            header("Location: login.php");
            exit();
        }



        $today = date('Y-m-d');
        $nearExpiryThreshold = date('Y-m-d', strtotime('+30 days')); 

        $items = [];
        $summary = ['near'=>0,'expired'=>0,'total'=>0];

    $sql = "
    SELECT i.Quantity, i.BatchNum, i.ExpirationDate, p.ProductName, c.Category_Name, c.CategoryID
    FROM inventory i
    JOIN products p ON i.ProductID = p.ProductID
    JOIN categories c ON p.CategoryID = c.CategoryID
    WHERE 1
    ";

    if(!empty($_GET['start_date'])){
        $start = $conn->real_escape_string($_GET['start_date']);
        $sql .= " AND i.ExpirationDate >= '$start'";
    }
    if(!empty($_GET['end_date'])){
        $end = $conn->real_escape_string($_GET['end_date']);
        $sql .= " AND i.ExpirationDate <= '$end'";
    }

    if(!empty($_GET['category'])){
        $catID = (int)$_GET['category'];
        $sql .= " AND c.CategoryID = $catID";
    }

    if(!empty($_GET['severity'])){
        $sev = $_GET['severity'];
        if($sev == 'expired') $sql .= " AND i.ExpirationDate < '$today'";
        elseif($sev == 'near') $sql .= " AND i.ExpirationDate BETWEEN '$today' AND '$nearExpiryThreshold'";
        elseif($sev == 'safe') $sql .= " AND i.ExpirationDate > '$nearExpiryThreshold'";
    }

    $sql .= " ORDER BY i.ExpirationDate ASC";

    $result = $conn->query($sql);

    $items = [];
    $summary = ['near'=>0,'expired'=>0,'total'=>0];
    while($row = $result->fetch_assoc()){
        $expDate = $row['ExpirationDate'];

        if($expDate < $today){
            $summary['expired']++;
            $summary['total']++; 
            $items[] = $row;
        } elseif($expDate <= $nearExpiryThreshold){
            $summary['near']++;
            $summary['total']++; 
            $items[] = $row;
        }
        
    }
    if(isset($_GET['export']) && $_GET['export']==1){
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="expiration_report.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Item','Batch','Qty','Category','Expiry','Status']);

        foreach($items as $row){
            $status = ($row['ExpirationDate'] < $today) ? 'Expired' : (($row['ExpirationDate'] <= $nearExpiryThreshold) ? 'Near Expiry' : 'Safe');
            fputcsv($out, [$row['ProductName'],$row['BatchNum'],$row['Quantity'],$row['Category_Name'],$row['ExpirationDate'],$status]);
        }
        fclose($out);
        exit();
    }
    
$nearestFive = [];
foreach ($items as $row) {
    $expDate = $row['ExpirationDate'];
    if ($expDate > $nearExpiryThreshold) continue; 
    $nearestFive[] = $row;
    if(count($nearestFive) >= 5) break;
}

    ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Expiration / Wastage Report</title>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="styles/sidebar.css" />
        <link rel="stylesheet" href="styles/notification.css" />

        <style>
        :root {
            --primary: #043873;
            --accent: #4f9cf9;
            --danger: #ff4d4f;
            --muted: #777;
            --bg: #f5f8ff;
            --radius: 10px;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',sans-serif; background-color:var(--bg); display:flex; min-height:100vh; color:#333; font-size:18px; }

        .topbar-right { display:flex; align-items:center; gap:15px; justify-content:flex-end; margin-bottom:10px; z-index:100; }
        .profile-icon { width:36px; height:36px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:18px; cursor:pointer; transition:0.2s; }
        .profile-icon:hover { background:var(--accent); }

        .heading-bar {   display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fff;
  padding: 16px 20px; 
  border-radius: var(--radius);
  box-shadow: var(--card-shadow);
  margin-bottom: 15px; }
        .heading-bar h1 {   font-size: 32px;      
  font-weight: 700;
  color: var(--primary); }

        .report-container { background:white; padding:20px; border-radius:var(--radius); box-shadow:0 0 10px rgba(0,0,0,0.06); font-size:18px; }
        .report-header { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px; margin-bottom:20px; }
        .report-left { display:flex; align-items:center; gap:12px; min-width:250px; flex-grow:1; }
        .title-icon { width:44px; height:44px; border-radius:8px; background:linear-gradient(135deg,var(--accent),#1b68d6); display:flex; align-items:center; justify-content:center; color:white; font-size:24px; }
        .filter-group { background:#f0f6ff; padding:8px 14px; border-radius:8px; display:flex; align-items:center; gap:8px; cursor:pointer; user-select:none; color:var(--primary); font-weight:600; border:1px solid rgba(4,56,115,0.08); min-width:140px; justify-content:center; white-space:nowrap; transition:0.2s; }
        .filter-group:hover { background:#d7e6ff; }
        .export-btn { padding:8px 16px; background:var(--accent); border:none; color:white; border-radius:8px; cursor:pointer; font-weight:600; font-size:18px; min-width:110px; transition:0.2s; }
        .export-btn:hover { background:#3b7cd3; }
        .summary { margin-top:20px; padding:18px 20px; border-radius:10px; background:#f8fbff; border:1px solid rgba(4,56,115,0.06); }
        .expire-list { margin-top:15px; }
        .expire-item { background:#f8fbff; padding:15px 20px; border-radius:8px; border:1px solid rgba(4,56,115,0.08); display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
        .expire-name { font-weight:700; color:var(--primary); font-size:18px; }
        .expire-batch { font-size:14px; color:#666; }
        .expire-date { font-weight:600; color:var(--primary); background:#eef5ff; padding:6px 12px; border-radius:6px; min-width:100px; text-align:center; }
        .expire-date.expired { background:#ffd6d6; color:#dc3545; }
        .expire-date.near-expiry { background:#fff4e5; color:#ff9f00; }
        .table-wrapper {
            max-height: 420px; 
            overflow-y: auto;
            margin-top: 10px;
            border: 1px solid #eee;
            border-radius: 8px;
            background: white;
        }

        .table-wrapper thead th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 5;
        }

        table { width: 100%; border-collapse: collapse; min-width: 700px; table-layout: auto; }
        thead th { background: #f8f9fa; color: #444; font-weight: 600;
        font-size: 16px; position: sticky; top: 0; z-index: 10; }
        td, th { text-align: left; padding: 12px 16px;
        border-bottom: 1px solid #eee; vertical-align: middle;
        font-size: 15px; white-space: nowrap; }
    

 
        .has-dropdown {
            position: relative; 
        }

        .has-dropdown .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #fff;
            list-style: none;
            padding: 0;
            margin: 0;
            width: 220px;
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

        tr.expired-row td {
            background: #ffe5e5 !important;
            color: #c62828;
            font-weight:600;
        }

        tr.near-row td {
            background: #fff4d6 !important;
            color: #cc7a00;
            font-weight:600;
        }

        .filter-dropdown {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        padding: 12px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        z-index: 10;
        min-width: 200px;
        flex-direction: column;
        gap: 6px;
    }

    .filter-dropdown input,
    .filter-dropdown select {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #ccc;
        border-radius: 6px;
    }

#dateFilterDropdown {
    display: none;          
    min-width: 200px;       
    flex-direction: row;    
    gap: 4px;
    padding: 8px;
}


#dateFilterDropdown input[type="date"] {
    flex: 1;                
    padding: 4px 6px;
    font-size: 14px;
    width: auto;            
}


#dateFilterDropdown .export-btn {
    padding: 6px 12px;
    font-size: 14px;
    flex-shrink: 0;        
    min-width: auto;
}
        </style>
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
            <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
    <li class="nav-reports has-dropdown">
    <i class="fa-solid fa-file-lines"></i><span>Reports</span>
    <ul class="dropdown-menu" >
  <li id="inventorymanagement">
    <a class="report-link">Inventory Management</a>
  </li>

  <li id="expirationwastage" class="active">
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

<<<<<<< HEAD
            <!-- Heading -->
            <div class="heading-bar"><h1>Expiration / Wastage Report</h1></div>

            <!-- Report container -->
=======
            <div class="heading-bar"><h1>Expiration / Wastage Report</h1><div class="topbar-right">
                <?php include 'notification_component.php'; ?>
                <div class="profile-icon"><i class="fa-solid fa-user"></i></div>
            </div></div>
>>>>>>> d68acbff0bf7cc8d9ae2f3de19d7deee889eb7d1
            <div class="report-container">
                <div class="report-header">
                    <div class="report-left">
                        <div class="title-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                        <div>
                            <div style="font-size:16px;font-weight:700;color:var(--primary)">Expiration / Wastage Report</div>
                            <div class="small-muted">Timeline of item expiry and wastage</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px; position:relative;">

    <div class="filter-group" id="dateFilterBtn"><i class="fa-solid fa-calendar"></i> Date Range
        <div class="filter-dropdown" id="dateFilterDropdown">
            <form method="get">
                <input type="date" name="start_date" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : '' ?>" placeholder="Start Date">
                <input type="date" name="end_date" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>" placeholder="End Date">

                <?php if(!empty($_GET['category'])): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($_GET['category']) ?>">
                <?php endif; ?>
                <?php if(!empty($_GET['severity'])): ?>
                    <input type="hidden" name="severity" value="<?= htmlspecialchars($_GET['severity']) ?>">
                <?php endif; ?>
                <button type="submit" class="export-btn">Apply</button>
            </form>
        </div>
    </div>

    <div class="filter-group" id="categoryFilterBtn"><i class="fa-solid fa-tags"></i> Item Category
        <div class="filter-dropdown" id="categoryFilterDropdown">
            <form method="get">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php
                    $catResult = $conn->query("SELECT CategoryID, Category_Name FROM categories ORDER BY Category_Name");
                    while($cat = $catResult->fetch_assoc()){
                        $selected = (isset($_GET['category']) && $_GET['category']==$cat['CategoryID']) ? 'selected' : '';
                        echo "<option value='{$cat['CategoryID']}' $selected>{$cat['Category_Name']}</option>";
                    }
                    ?>
                </select>

                <?php if(!empty($_GET['start_date'])): ?>
                    <input type="hidden" name="start_date" value="<?= htmlspecialchars($_GET['start_date']) ?>">
                <?php endif; ?>
                <?php if(!empty($_GET['end_date'])): ?>
                    <input type="hidden" name="end_date" value="<?= htmlspecialchars($_GET['end_date']) ?>">
                <?php endif; ?>
                <?php if(!empty($_GET['severity'])): ?>
                    <input type="hidden" name="severity" value="<?= htmlspecialchars($_GET['severity']) ?>">
                <?php endif; ?>
                <button type="submit" class="export-btn">Apply</button>
            </form>
        </div>
    </div>

    <div class="filter-group" id="severityFilterBtn"><i class="fa-solid fa-exclamation-triangle"></i> Severity
        <div class="filter-dropdown" id="severityFilterDropdown">
            <form method="get">
                <select name="severity">
                    <option value="">All</option>
                    <option value="expired" <?= (isset($_GET['severity']) && $_GET['severity']=='expired') ? 'selected' : '' ?>>Expired</option>
                    <option value="near" <?= (isset($_GET['severity']) && $_GET['severity']=='near') ? 'selected' : '' ?>>Near Expiry</option>
                    <option value="safe" <?= (isset($_GET['severity']) && $_GET['severity']=='safe') ? 'selected' : '' ?>>Safe</option>
                </select>

                <?php if(!empty($_GET['start_date'])): ?>
                    <input type="hidden" name="start_date" value="<?= htmlspecialchars($_GET['start_date']) ?>">
                <?php endif; ?>
                <?php if(!empty($_GET['end_date'])): ?>
                    <input type="hidden" name="end_date" value="<?= htmlspecialchars($_GET['end_date']) ?>">
                <?php endif; ?>
                <?php if(!empty($_GET['category'])): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($_GET['category']) ?>">
                <?php endif; ?>
                <button type="submit" class="export-btn">Apply</button>
            </form>
        </div>
    </div>

        <a href="report_expiration.php?<?= http_build_query($_GET) ?>&export=1" class="export-btn"><i class="fa-solid fa-file-export"></i> Export CSV</a>
        <a href="report_expiration.php" class="export-btn" style="background:#ff4d4f;">
    <i class="fa-solid fa-eraser"></i> Clear Filters
</a>
    </div>
                </div>

                <div class="expire-list">
                        <?php foreach($nearestFive as $row): ?>
                            <?php 
                                $expDate = $row['ExpirationDate'];
                                $statusClass = '';
                                if($expDate < $today) $statusClass = 'expired';
                                elseif($expDate <= $nearExpiryThreshold) $statusClass = 'near-expiry';
                            ?>
                            <div class='expire-item'>
                                <div>
                                    <div class='expire-name'><?= htmlspecialchars($row['ProductName']) ?></div>
                                    <div class='expire-batch'>Batch <?= htmlspecialchars($row['BatchNum']) ?></div>
                                </div>
                                <div class='expire-date <?= $statusClass ?>'><?= $expDate ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <div class="summary">
                    <div class="report-title">Summary</div>
                    <p>Near Expiry: <strong><?= $summary['near'] ?></strong></p>
                    <p>Expired: <strong><?= $summary['expired'] ?></strong></p>
                    <p>Total Risk Items: <strong><?= $summary['total'] ?></strong></p>
                </div>

                <div style="margin-top:20px;">
                    <div class="report-title">Expiration List</div>
                    <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Item</th><th>Batch</th><th>Qty</th><th>Category</th><th>Expiry</th></tr></thead>
                        <tbody>
        <?php foreach($items as $row): ?>
            <?php
                $expDate = $row['ExpirationDate'];

                if ($expDate < $today) {
                    $rowClass = "expired-row";
                } elseif ($expDate <= $nearExpiryThreshold) {
                    $rowClass = "near-row";
                } else {
                    continue;
                }
            ?>
            <tr class="<?= $rowClass ?>">
                <td><?= htmlspecialchars($row['ProductName']) ?></td>
                <td><?= htmlspecialchars($row['BatchNum']) ?></td>
                <td><?= (int)$row['Quantity'] ?></td>
                <td><?= htmlspecialchars($row['Category_Name']) ?></td>
                <td><?= $row['ExpirationDate'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>

                    </table>
                    </div>
                </div>
            </div>
        </main>

        <script src="sidebar.js"></script>
        <script src="notification.js" defer></script>
        <script>
        $(document).ready(function(){

        $("#dateFilterBtn").click(function(e){
            e.stopPropagation();
            $("#dateFilterDropdown").toggle();
            $("#categoryFilterDropdown, #severityFilterDropdown").hide();
        });

        $("#categoryFilterBtn").click(function(e){
            e.stopPropagation();
            $("#categoryFilterDropdown").toggle();
            $("#dateFilterDropdown, #severityFilterDropdown").hide();
        });

        $("#severityFilterBtn").click(function(e){
            e.stopPropagation();
            $("#severityFilterDropdown").toggle();
            $("#dateFilterDropdown, #categoryFilterDropdown").hide();
        });

        $(".filter-dropdown").click(function(e){
            e.stopPropagation();
        });

        $(document).click(function(){
            $(".filter-dropdown").hide();
        });
    });


        $(document).ready(function(){

            $(".has-dropdown > span, .has-dropdown > i").click(function(){
                $(this).parent().toggleClass("open");
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

        });
        </script>

        </body>
        </html>
