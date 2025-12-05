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

        <section class="content-section active" id="dashboard-section">
            <div class="page-header">
                <h1>Dashboard Overview</h1>
            </div>

            <div class="summary-grid">
                <div class="stat-card blue-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Requests</span>
                        <i class="fas fa-box stat-icon"></i>
                    </div>
                    <div class="stat-value">24</div>
                    <div class="stat-footer">This Month</div>
                </div>
                <div class="stat-card red-card">
                    <div class="stat-header">
                        <span class="stat-label">Pending</span>
                        <i class="fas fa-clock stat-icon"></i>
                    </div>
                    <div class="stat-value">5</div>
                    <div class="stat-footer">Awaiting Response</div>
                </div>
                <div class="stat-card yellow-card">
                    <div class="stat-header">
                        <span class="stat-label">Unread Messages</span>
                        <i class="fas fa-envelope stat-icon"></i>
                    </div>
                    <div class="stat-value">8</div>
                    <div class="stat-footer">New</div>
                </div>
                <div class="stat-card green-card">
                    <div class="stat-header">
                        <span class="stat-label">Completed</span>
                        <i class="fas fa-clipboard-check stat-icon"></i>
                    </div>
                    <div class="stat-value">12</div>
                    <div class="stat-footer">This Month</div>
                </div>
            </div>

            <div class="widgets-grid">
                <div class="widget-card">
                    <h3 class="widget-title">Request Status Overview</h3>
                    <div class="status-list">
                        <div class="status-row">
                            <div class="status-info">
                                <span class="status-name">Approved</span>
                                <span class="status-percent">60%</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill green-bar" style="width: 60%;"></div>
                            </div>
                        </div>
                        <div class="status-row">
                            <div class="status-info">
                                <span class="status-name">Pending</span>
                                <span class="status-percent">25%</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill yellow-bar" style="width: 25%;"></div>
                            </div>
                        </div>
                        <div class="status-row">
                            <div class="status-info">
                                <span class="status-name">Declined</span>
                                <span class="status-percent">15%</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill blue-bar" style="width: 15%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="widget-card">
                    <h3 class="widget-title">Quick Actions</h3>
                    <div class="quick-actions-list">
                        <button class="quick-action-btn" onclick="switchTab('pending-requests')">
                            <i class="fas fa-hourglass-half"></i>
                            <span>View Pending Requests</span>
                        </button>
                        <button class="quick-action-btn" onclick="switchTab('messages')">
                            <i class="fas fa-envelope-open"></i>
                            <span>View Messages</span>
                        </button>
                        <button class="quick-action-btn" onclick="switchTab('completed-requests')">
                            <i class="fas fa-clipboard-check"></i>
                            <span>View Completed Requests</span>
                        </button>
                    </div>
                </div>

                <div class="widget-card full-width">
                    <h3 class="widget-title">Recent Requests</h3>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>REQ-001</strong></td>
                                    <td>10/04/2025</td>
                                    <td>Face Mask, Medical Gloves</td>
                                    <td>₱45,000</td>
                                    <td><span class="badge badge-yellow">Pending</span></td>
                                </tr>
                                <tr>
                                    <td><strong>REQ-002</strong></td>
                                    <td>08/04/2025</td>
                                    <td>Syringes, Sanitizers</td>
                                    <td>₱47,500</td>
                                    <td><span class="badge badge-green">Approved</span></td>
                                </tr>
                                <tr>
                                    <td><strong>REQ-003</strong></td>
                                    <td>05/04/2025</td>
                                    <td>Thermometers</td>
                                    <td>₱36,250</td>
                                    <td><span class="badge badge-completed">Completed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>


       <section class="content-section" id="pending-requests-section">
    <div class="page-header">
        <h1>Pending Requests</h1>
    </div>

<div class="filter-bar">
  <input type="text" id="searchInput" placeholder="Search batch ID, item name, or quantity..." />
  <select id="categoryFilter">
    <option value="">All Categories</option>
    <option value="Protective Equipment">Protective Equipment</option>
    <option value="Antibiotics / Antibacterials">Antibiotics / Antibacterials</option>
    <option value="Analgesics / Antipyretics">Analgesics / Antipyretics</option>
    <option value="Antivirals">Antivirals</option>
    <option value="Antifungals">Antifungals</option>
    <option value="Antihistamines / Antiallergics">Antihistamines / Antiallergics</option>
    <option value="Antacids / Antiulcerants">Antacids / Antiulcerants</option>
  </select>

  <input type="date" id="dateFilter" />
  <button class="btn btn-secondary" onclick="clearFilters()">Clear</button>
</div>
        
    <div class="table-container">
  <table class="request-table">
    <thead>
      <tr>
        <th><input type="checkbox" /></th>
        <th>Request Batch ID</th>
        <th>Item Name</th>
        <th>Category</th>
        <th>Quantity</th>
        <th>Request Date</th>
      </tr>
    </thead>
    <tbody id="pendingTableBody">
      <tr>
        <td><input type="checkbox" /></td>
        <td>0004</td>
        <td>Acyclovir 400mg</td>
        <td>Antivirals</td>
        <td>25</td>
        <td>12/2/2025</td>
      </tr>
      <tr>
        <td><input type="checkbox" /></td>
        <td>0004</td>
        <td>Amoxicillin 500mg</td>
        <td>Antibiotics / Antibacterials</td>
        <td>25</td>
        <td>12/2/2025</td>
      </tr>
    </tbody>
  </table>
      
</div>
</section>


        <section class="content-section" id="approved-requests-section">
    <div class="page-header">
        <h1>Approved Requests</h1>
    </div>

<div class="filter-bar">
  <input type="text" id="searchInput" placeholder="Search batch ID, item name, or quantity..." />
  <select id="categoryFilter">
    <option value="">All Categories</option>
    <option value="Protective Equipment">Protective Equipment</option>
    <option value="Antibiotics / Antibacterials">Antibiotics / Antibacterials</option>
    <option value="Analgesics / Antipyretics">Analgesics / Antipyretics</option>
    <option value="Antivirals">Antivirals</option>
    <option value="Antifungals">Antifungals</option>
    <option value="Antihistamines / Antiallergics">Antihistamines / Antiallergics</option>
    <option value="Antacids / Antiulcerants">Antacids / Antiulcerants</option>
  </select>

  <input type="date" id="dateFilter" />
  <button class="btn btn-secondary" onclick="clearFilters()">Clear</button>
</div>
    
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th><input type="checkbox" /></th>
                    <th>Request Batch ID</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Request Date</th>
                    <th>Shipping Date</th>
                </tr>
            </thead>
            <tbody id="approvedTableBody">
                <tr>
                    <td><input type="checkbox" /></td>
                    <td>0001</td>
                    <td>Amoxicillin</td>
                    <td>Analgesics/Antipyretics</td>
                    <td>50</td>
                    <td>12/2/2025</td>
                    <td><input type="date" value="2025-12-04" /></td>
                </tr>
                <tr>
                    <td><input type="checkbox" /></td>
                    <td>0001</td>
                    <td>Paracetamol</td>
                    <td>Antibiotics/Antibacterials</td>
                    <td>25</td>
                    <td>12/2/2025</td>
                    <td><input type="date" value="2025-12-02" /></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>


        <section class="content-section" id="completed-requests-section">
    <div class="page-header">
        <h1>Completed Requests</h1>
    </div>

<div class="filter-bar">
  <input type="text" id="searchInput" placeholder="Search batch ID, item name, or quantity..." />
  <select id="categoryFilter">
    <option value="">All Categories</option>
    <option value="Protective Equipment">Protective Equipment</option>
    <option value="Antibiotics / Antibacterials">Antibiotics / Antibacterials</option>
    <option value="Analgesics / Antipyretics">Analgesics / Antipyretics</option>
    <option value="Antivirals">Antivirals</option>
    <option value="Antifungals">Antifungals</option>
    <option value="Antihistamines / Antiallergics">Antihistamines / Antiallergics</option>
    <option value="Antacids / Antiulcerants">Antacids / Antiulcerants</option>
  </select>

  <input type="date" id="dateFilter" />
  <button class="btn btn-secondary" onclick="clearFilters()">Clear</button>
</div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th><input type="checkbox" /></th>
                    <th>Request Batch ID</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Request Date</th>
                    <th>Shipping Completed</th>
                </tr>
            </thead>
            <tbody id="completedTableBody">
                <tr>
                    <td><input type="checkbox" /></td>
                    <td>0001</td>
                    <td>Amoxicillin</td>
                    <td>Analgesics/Antipyretics</td>
                    <td>50</td>
                    <td>12/2/2025</td>
                    <td>12/4/2025 3:43 PM</td>
                </tr>
                <tr>
                    <td><input type="checkbox" /></td>
                    <td>0001</td>
                    <td>Paracetamol</td>
                    <td>Antibiotics/Antibacterials</td>
                    <td>25</td>
                    <td>12/2/2025</td>
                    <td>12/3/2025 3:43 PM</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

        <section class="content-section" id="messages-section">
            <div class="page-header">
                <h1>Messages</h1>
            </div>

            <div class="messages-list-container">
                <div class="message-item" data-message="1">
                    <div class="message-item-header">
                        <div class="message-item-info">
                            <span class="message-item-subject">Order Inquiry - REQ-001</span>
                            <span class="message-item-sender">From: Inventory Manager</span>
                        </div>
                        <span class="message-item-date">10/04/2025</span>
                    </div>
                    <div class="message-item-preview">Hello, I dsadaswould like to inquire about the availability of Face Masks and Medical Gloves for REQ-001.</div>
                    <div class="message-item-actions">
                        <button class="view-message-btn" onclick="openMessageModal(1)">View Full Message</button>
                        <button class="reply-message-btn" onclick="openReplyModal(1)">Reply</button>
                    </div>
                </div>

                <div class="message-item" data-message="2">
                    <div class="message-item-header">
                        <div class="message-item-info">
                            <span class="message-item-subject">Schedule Update - REQ-002</span>
                            <span class="message-item-sender">From: Procurement Team</span>
                        </div>
                        <span class="message-item-date">08/04/2025</span>
                    </div>
                    <div class="message-item-preview">We need to update the delivery date for REQ-002. Please confirm if you can accommodate the new schedule.</div>
                    <div class="message-item-actions">
                        <button class="view-message-btn" onclick="openMessageModal(2)">View Full Message</button>
                        <button class="reply-message-btn" onclick="openReplyModal(2)">Reply</button>
                    </div>
                </div>

                <div class="message-item" data-message="3">
                    <div class="message-item-header">
                        <div class="message-item-info">
                            <span class="message-item-subject">Stock Availability Check</span>
                            <span class="message-item-sender">From: Hospital Admin</span>
                        </div>
                        <span class="message-item-date">07/04/2025</span>
                    </div>
                    <div class="message-item-preview">Please provide current stock levels for Syringes, Sanitizers, and Thermometers.</div>
                    <div class="message-item-actions">
                        <button class="view-message-btn" onclick="openMessageModal(3)">View Full Message</button>
                        <button class="reply-message-btn" onclick="openReplyModal(3)">Reply</button>
                    </div>
                </div>
            </div>

            <div class="message-reply-section">
                <form class="message-reply-form" onsubmit="return sendMessage(event)">
                    <div class="input-group">
                        <label>Send a Message</label>
                        <textarea id="replyMessage" rows="4"></textarea>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-secondary" onclick="clearReply()">Clear</button>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="content-section" id="company-profile-section">
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
                    <h3>Mission Statement</h3>
                    <button class="edit-icon-btn" onclick="openEditModal('mission')">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
                <div class="profile-content">
                    <p class="mission-statement" id="display-mission"></p>
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-card-header">
                    <h3>History</h3>
                    <button class="edit-icon-btn" onclick="openEditModal('history')">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
                <div class="profile-content">
                    <div class="history-content" id="display-history"></div>
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

    <div id="messageModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="messageModalTitle">Message</h3>
                <button class="close-modal-btn" onclick="closeMessageModal()">&times;</button>
            </div>
            <div class="modal-body" id="messageModalBody">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="closeMessageModal()">Close</button>
            </div>
        </div>
    </div>

    <div id="replyModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="replyModalTitle">Reply to Message</h3>
                <button class="close-modal-btn" onclick="closeReplyModal()">&times;</button>
            </div>
            <div class="modal-body" id="replyModalBody">
                <div class="input-group">
                    <label id="replyToLabel">Reply to:</label>
                    <textarea id="replyModalText" rows="6" placeholder="Type your reply here..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeReplyModal()">Cancel</button>
                <button class="btn btn-primary" onclick="sendReplyFromModal()">Send Reply</button>
            </div>
        </div>
    </div>
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
        <section class="content-section" id="messages-section">
            <div class="page-header">
                <h1>Messages</h1>
            </div>

            <div class="messages-list-container">
                <div class="message-item" data-message="1">
                    <div class="message-item-header">
                        <div class="message-item-info">
                            <span class="message-item-subject">Order Inquiry - REQ-001</span>
                            <span class="message-item-sender">From: Inventory Manager</span>
                        </div>
                        <span class="message-item-date">10/04/2025</span>
                    </div>
                    <div class="message-item-preview">Hello, I would like to inquire about the availability of Face Masks and Medical Gloves for REQ-001.</div>
                    <div class="message-item-actions">
                        <button class="view-message-btn" onclick="openMessageModal(1)">View Full Message</button>
                        <button class="reply-message-btn" onclick="openReplyModal(1)">Reply</button>
                    </div>
                </div>

                <div class="message-item" data-message="2">
                    <div class="message-item-header">
                        <div class="message-item-info">
                            <span class="message-item-subject">Schedule Update - REQ-002</span>
                            <span class="message-item-sender">From: Procurement Team</span>
                        </div>
                        <span class="message-item-date">08/04/2025</span>
                    </div>
                    <div class="message-item-preview">We need to update the delivery date for REQ-002. Please confirm if you can accommodate the new schedule.</div>
                    <div class="message-item-actions">
                        <button class="view-message-btn" onclick="openMessageModal(2)">View Full Message</button>
                        <button class="reply-message-btn" onclick="openReplyModal(2)">Reply</button>
                    </div>
                </div>

                <div class="message-item" data-message="3">
                    <div class="message-item-header">
                        <div class="message-item-info">
                            <span class="message-item-subject">Stock Availability Check</span>
                            <span class="message-item-sender">From: Hospital Admin</span>
                        </div>
                        <span class="message-item-date">07/04/2025</span>
                    </div>
                    <div class="message-item-preview">Please provide current stock levels for Syringes, Sanitizers, and Thermometers.</div>
                    <div class="message-item-actions">
                        <button class="view-message-btn" onclick="openMessageModal(3)">View Full Message</button>
                        <button class="reply-message-btn" onclick="openReplyModal(3)">Reply</button>
                    </div>
                </div>
            </div>

            <div class="message-reply-section">
                <form class="message-reply-form" onsubmit="return sendMessage(event)">
                    <div class="input-group">
                        <label>Send a Message</label>
                        <textarea id="replyMessage" rows="4"></textarea>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-secondary" onclick="clearReply()">Clear</button>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </div>
                </form>
            </div>
        </section>
        <section class="content-section" id="company-profile-section">
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
                    <h3>Mission Statement</h3>
                    <button class="edit-icon-btn" onclick="openEditModal('mission')">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
                <div class="profile-content">
                    <p class="mission-statement" id="display-mission"></p>
                </div>
            </div>


            <div class="profile-card">
                <div class="profile-card-header">
                    <h3>History</h3>
                    <button class="edit-icon-btn" onclick="openEditModal('history')">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
                <div class="profile-content">
                    <div class="history-content" id="display-history"></div>
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

    <div id="messageModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="messageModalTitle">Message</h3>
                <button class="close-modal-btn" onclick="closeMessageModal()">&times;</button>
            </div>
            <div class="modal-body" id="messageModalBody">

            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="closeMessageModal()">Close</button>
            </div>
        </div>
    </div>

    <div id="replyModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="replyModalTitle">Reply to Message</h3>
                <button class="close-modal-btn" onclick="closeReplyModal()">&times;</button>
            </div>
            <div class="modal-body" id="replyModalBody">
                <div class="input-group">
                    <label id="replyToLabel">Reply to:</label>
                    <textarea id="replyModalText" rows="6" placeholder="Type your reply here..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeReplyModal()">Cancel</button>
                <button class="btn btn-primary" onclick="sendReplyFromModal()">Send Reply</button>
            </div>
        </div>
    </div>

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


 


        <section class="content-section" id="messages-section">
            <div class="page-header">
                <h1>Messages</h1>
            </div>

            <div class="messages-list-container">
                <div class="message-item" data-message="1">
                    <div class="message-item-header">
                        <div class="message-item-info">
                            <span class="message-item-subject">Order Inquiry - REQ-001</span>
                            <span class="message-item-sender">From: Inventory Manager</span>
                        </div>
                        <span class="message-item-date">10/04/2025</span>
                    </div>
                    <div class="message-item-preview">Hello, I wdasdasfsdghfgjhgould like to inquire about the availability of Face Masks and Medical Gloves for REQ-001.</div>
                    <div class="message-item-actions">
                        <button class="view-message-btn" onclick="openMessageModal(1)">View Full Message</button>
                        <button class="reply-message-btn" onclick="openReplyModal(1)">Reply</button>
                    </div>
                </div>

                <div class="message-item" data-message="2">
                    <div class="message-item-header">
                        <div class="message-item-info">
                            <span class="message-item-subject">Schedule Update - REQ-002</span>
                            <span class="message-item-sender">From: Procurement Team</span>
                        </div>
                        <span class="message-item-date">08/04/2025</span>
                    </div>
                    <div class="message-item-preview">We need to update the delivery date for REQ-002. Please confirm if you can accommodate the new schedule.</div>
                    <div class="message-item-actions">
                        <button class="view-message-btn" onclick="openMessageModal(2)">View Full Message</button>
                        <button class="reply-message-btn" onclick="openReplyModal(2)">Reply</button>
                    </div>
                </div>

                <div class="message-item" data-message="3">
                    <div class="message-item-header">
                        <div class="message-item-info">
                            <span class="message-item-subject">Stock Availability Check</span>
                            <span class="message-item-sender">From: Hospital Admin</span>
                        </div>
                        <span class="message-item-date">07/04/2025</span>
                    </div>
                    <div class="message-item-preview">Please provide current stock levels for Syringes, Sanitizers, and Thermometers.</div>
                    <div class="message-item-actions">
                        <button class="view-message-btn" onclick="openMessageModal(3)">View Full Message</button>
                        <button class="reply-message-btn" onclick="openReplyModal(3)">Reply</button>
                    </div>
                </div>
            </div>

            <div class="message-reply-section">
                <form class="message-reply-form" onsubmit="return sendMessage(event)">
                    <div class="input-group">
                        <label>Send a Message</label>
                        <textarea id="replyMessage" rows="4"></textarea>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-secondary" onclick="clearReply()">Clear</button>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </div>
                </form>
            </div>
        </section>


        <section class="content-section" id="company-profile-section">
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
                    <h3>Mission Statement</h3>
                    <button class="edit-icon-btn" onclick="openEditModal('mission')">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
                <div class="profile-content">
                    <p class="mission-statement" id="display-mission"></p>
                </div>
            </div>


            <div class="profile-card">
                <div class="profile-card-header">
                    <h3>History</h3>
                    <button class="edit-icon-btn" onclick="openEditModal('history')">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
                <div class="profile-content">
                    <div class="history-content" id="display-history"></div>
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


    <div id="messageModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="messageModalTitle">Message</h3>
                <button class="close-modal-btn" onclick="closeMessageModal()">&times;</button>
            </div>
            <div class="modal-body" id="messageModalBody">

            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="closeMessageModal()">Close</button>
            </div>
        </div>
    </div>


    <div id="replyModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="replyModalTitle">Reply to Message</h3>
                <button class="close-modal-btn" onclick="closeReplyModal()">&times;</button>
            </div>
            <div class="modal-body" id="replyModalBody">
                <div class="input-group">
                    <label id="replyToLabel">Reply to:</label>
                    <textarea id="replyModalText" rows="6" placeholder="Type your reply here..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeReplyModal()">Cancel</button>
                <button class="btn btn-primary" onclick="sendReplyFromModal()">Send Reply</button>
            </div>
        </div>
    </div>


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
