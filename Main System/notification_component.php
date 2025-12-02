<?php
require_once "db_connect.php";

// Fetch last 3 notifications (messages + low stock)
$recent = $conn->query("
    SELECT 'message' AS type, header AS title, supplier AS info, date_created AS created_at
    FROM messages
    ORDER BY date_created DESC
    LIMIT 3
");

$recentLow = $conn->query("
    SELECT 'lowstock' AS type, ProductName AS title, Quantity AS info, NOW() AS created_at
    FROM inventory i
    JOIN products p ON p.ProductID = i.ProductID
    WHERE i.Quantity <= 5
    ORDER BY i.Quantity ASC
    LIMIT 3
");
?>

<!-- 🔔 NOTIFICATION SYSTEM -->
<div class="notif-wrap" id="notifWrap">
    <button class="notif-btn" id="notifBtn" aria-expanded="false">
        <i class="fa-solid fa-bell"></i>
        <span class="notif-count" id="notifCount" style="display:none;">0</span>
    </button>

    <div class="notif-dd" id="notifDropdown">
        <div class="dd-header">
            <h4>Notifications</h4>

            <div class="notif-settings">
                <input type="checkbox" id="notifyToggle" checked>
                <label for="notifyToggle">Notify</label>
            </div>
        </div>

        <div class="dd-list" id="notifList">
            <div style="padding:14px; color:#666;">Loading...</div>
        </div>

        <div class="notif-footer">
            <a href="message_list.php">View All Notifications</a>
        </div>
    </div>
</div>

<!-- 🔊 SOUND -->
<!-- <audio id="notifSound" src="notification_ping.mp3" preload="auto"></audio> -->

<script>
// 🔽 OPEN/CLOSE DROPDOWN
$(document).on("click", "#notifBtn", function(e) {
    e.stopPropagation();
    $("#notifDropdown").toggleClass("show");

    if ($("#notifDropdown").hasClass("show")) {
        loadNotificationDropdown();
    }
});

// Close dropdown when clicking outside
$(document).click(function(e) {
    if (!$(e.target).closest("#notifWrap").length) {
        $("#notifDropdown").removeClass("show");
    }
});

// 🔄 AUTO UPDATE BADGE
function loadNotifications() {
    $.ajax({
        url: "get_notifications.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            let total = data.messages + data.lowstock;

            if (total > 0) {
                $("#notifCount").text(total).show();

                if ($("#notifyToggle").is(":checked")) {
                    document.getElementById("notifSound").play();
                }
            } else {
                $("#notifCount").hide();
            }
        }
    });
}

// 🔽 LOAD 3 MOST RECENT ITEMS INSIDE DROPDOWN
function loadNotificationDropdown() {
    $.ajax({
        url: "get_notification_items.php",
        method: "GET",
        success: function(html) {
            $("#notifList").html(html);
        }
    });
}

// Auto-refresh every 10 seconds
loadNotifications();
setInterval(loadNotifications, 10000);
</script>
