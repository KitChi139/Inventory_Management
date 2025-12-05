<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Portal - MediSync</title>
    <link rel="stylesheet" href="supplier_portal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <button class="tab-link active" data-tab="dashboard">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </button>
        <button class="tab-link" data-tab="pending-requests">
            <i class="fas fa-hourglass-half"></i>
            <span>Pending Requests</span>
        </button>
        <button class="tab-link" data-tab="declined-requests">
            <i class="fas fa-times-circle"></i>
            <span>Declined Requests</span>
        </button>
        <button class="tab-link" data-tab="approved-requests">
            <i class="fas fa-check-circle"></i>
            <span>Approved Requests</span>
        </button>
        <button class="tab-link" data-tab="completed-requests">
            <i class="fas fa-clipboard-check"></i>
            <span>Completed Requests</span>
        </button>
        <button class="tab-link" data-tab="messages">
            <i class="fas fa-envelope"></i>
            <span>Messages</span>
        </button>
        <button class="tab-link" data-tab="company-profile">
            <i class="fas fa-building"></i>
            <span>Company Profile</span>
        </button>
    </nav>

    <main class="main-content">
        
    <div id="notificationsModal" class="modal-overlay">
        <div class="modal-container notifications-modal">
            <div class="modal-header">
                <h3 id="notificationsTitle">Notifications</h3>
                <button class="close-modal-btn" onclick="closeNotificationsModal()">&times;</button>
            </div>
            <div class="modal-body" id="notificationsBody">
            </div>
        </div>
    </div>



      
</body>
</html>
