<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Portal - MediSync</title>
    <link rel="stylesheet" href="styles/supplier_portal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="supplier_portal.js" defer></script>
</head>
<body>

    <header class="top-nav">
        <div class="nav-left">
            <div class="logo-container">
                <img src="logo.png" alt="MediSync Logo" class="logo-img">
            </div>
        </div>
        <div class="nav-right">
            <button class="icon-btn notification-btn" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
            </button>
            <button class="icon-btn profile-btn">
                <i class="fas fa-user-circle"></i>
            </button>
            <button class="logout-button" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </header>

    <nav class="tab-navigation">
        <button id="db" class="tab-link" data-tab="dashboard">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </button>
        <button id="pr" class="tab-link" data-tab="pending-requests">
            <i class="fas fa-hourglass-half"></i>
            <span>Pending Requests</span>
        </button>
        <button id="dr" class="tab-link" data-tab="declined-requests">
            <i class="fas fa-times-circle"></i>
            <span>Declined Requests</span>
        </button>
        <button id="ar" class="tab-link" data-tab="approved-requests">
            <i class="fas fa-check-circle"></i>
            <span>Approved Requests</span>
        </button>
        <button id="cr" class="tab-link" data-tab="completed-requests">
            <i class="fas fa-clipboard-check"></i>
            <span>Completed Requests</span>
        </button>
        <button id="m" class="tab-link" data-tab="messages">
            <i class="fas fa-envelope"></i>
            <span>Messages</span>
        </button>
        <button id="cp" class="tab-link active" data-tab="company-profile">
            <i class="fas fa-building"></i>
            <span>Company Profile</span>
        </button>
    </nav>

        <main class="main-content">

            <section class="content" id="company-profile-section">
                <div class="page-header">
                    <h1>Company Profile</h1>
                </div>

                <div class="profile-card">
                    <div class="profile-card-header">
                        <h3>Contact Information</h3>
                        <button class="edit-icon-btn" onclick="openEditModal('contact')">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <div class="profile-content">
                        <div class="info-grid">
                            <div class="info-item-compact">
                                <i class="fas fa-building info-icon-small"></i>
                                <div class="info-details-compact">
                                    <strong>Head Office:</strong> <span id="display-address"></span>
                                </div>
                            </div>
                            <div class="info-item-compact">
                                <i class="fas fa-phone info-icon-small"></i>
                                <div class="info-details-compact">
                                    <strong>Phone:</strong> <span id="display-phone"></span>
                                </div>
                            </div>
                            <div class="info-item-compact">
                                <i class="fas fa-envelope info-icon-small"></i>
                                <div class="info-details-compact">
                                    <strong>Email:</strong> <span id="display-email"></span>
                                </div>
                            </div>
                            <div class="info-item-compact">
                                <i class="fas fa-globe info-icon-small"></i>
                                <div class="info-details-compact">
                                    <strong>Website:</strong> <span id="display-website"></span>
                                </div>
                            </div>
                            <div class="info-item-compact full-width">
                                <i class="fas fa-map-marker-alt info-icon-small"></i>
                                <div class="info-details-compact">
                                    <strong>Regional Offices:</strong> <span id="display-regional"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="profile-card-header">
                        <h3>Items / Stock / Status</h3>
                        <button class="edit-icon-btn" onclick="openEditModal('inventory')">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <div class="profile-content">
                        <div class="table-container">
                            <table class="data-table" id="inventoryTable">
                                <thead>
                                    <tr>
                                        <th>Items</th>
                                        <th>Stock</th>
                                        <th>Price (₱)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="inventoryTableBody">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </main>

    <div id="editModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="modalTitle">Edit Information</h3>
                <button class="close-modal-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">

            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveEdit()">Save Changes</button>
            </div>
        </div>
    </div>
</body>
<script>
    $(document).ready(function () {

        $("#db").click(function(){ window.location.href = "supplier_portal_db.php";});
        $("#pr").click(function(){ window.location.href = "supplier_portal_1pr.php";});
        $("#dr").click(function(){ window.location.href = "supplier_portal_2dr.php";});
        $("#ar").click(function(){ window.location.href = "supplier_portal_3ar.php";});
        $("#cr").click(function(){ window.location.href = "supplier_portal_4cr.php";});
        $("#m").click(function(){ window.location.href = "supplier_portal_m.php"; });

    });
</script>
</html>
