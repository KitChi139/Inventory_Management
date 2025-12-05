<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Portal - MediSync</title>
    <link rel="stylesheet" href="supplier_portal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="supplier_portal.js" defer></script>
</head>
<body>
    <!-- Top Navigation Bar -->
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

    <!-- Secondary Navigation Tabs -->
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
        <button id="m" class="tab-link active" data-tab="messages">
            <i class="fas fa-envelope"></i>
            <span>Messages</span>
        </button>
        <button id="cp" class="tab-link" data-tab="company-profile">
            <i class="fas fa-building"></i>
            <span>Company Profile</span>
        </button>
    </nav>
        <!-- Main Content Area -->
            <main class="main-content">
        <!-- Messages Section -->
        <section class="content" id="messages-section">
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
        </main>
            <!-- Message View Modal -->
    <div id="messageModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="messageModalTitle">Message</h3>
                <button class="close-modal-btn" onclick="closeMessageModal()">&times;</button>
            </div>
            <div class="modal-body" id="messageModalBody">
                <!-- Dynamic content -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="closeMessageModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
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
    </body>
    <script>
        $(document).ready(function () {
            //Navigation
            $("#db").click(function(){ window.location.href = "supplier_portal_db.php";});
            $("#pr").click(function(){ window.location.href = "supplier_portal_1pr.php";});
            $("#dr").click(function(){ window.location.href = "supplier_portal_2dr.php";});
            $("#ar").click(function(){ window.location.href = "supplier_portal_3ar.php";});
            $("#cr").click(function(){ window.location.href = "supplier_portal_4cr.php";});
            // $("#m").click(function(){ window.location.href = "supplier_portal_m.php"; });
            $("#cp").click(function(){ window.location.href = "supplier_portal_cp.php"; });
        });
    </script>
</html>