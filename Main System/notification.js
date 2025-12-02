// ---------------------------------------
// Load previous count safely
// ---------------------------------------
let lastCount = parseInt(localStorage.getItem("lastNotifCount") || "0");

// ---------------------------------------
// Load notifications
// ---------------------------------------
function loadNotifications() {
    $.ajax({
        url: "get_notifications.php",
        method: "GET",
        dataType: "json",
        success: function(data) {

            const total = data.messages + data.lowstock;

            // Update bell badge
            if (total > 0) {
                $("#notifBadge").text(total).show();
            } else {
                $("#notifBadge").hide();
            }

            // ---------------------------------------
            // PLAY SOUND ONLY IF:
            // new notifications > previous count
            // AND user has NOT marked as read
            // ---------------------------------------
            const marked = localStorage.getItem("notifRead") === "1";

            if (!marked && total > lastCount) {
                const audio = document.getElementById("notifSound");
                audio.play().catch(() => {});
            }

            // Save new count
            lastCount = total;
            localStorage.setItem("lastNotifCount", total);
        }
    });
}

// First load AFTER DOM is fully ready
$(document).ready(function () {
    loadNotifications();
});

// Refresh every 10 seconds
setInterval(loadNotifications, 10000);

// ---------------------------------------
// USER OPENS THE DROPDOWN → MARK AS READ
// ---------------------------------------
$(document).on("click", "#notifBtn", function () {

    // Tell backend to reset unread status
    $.get("get_notifications.php?mark_read=1");

    // Tell ALL pages that user saw notifications
    localStorage.setItem("notifRead", "1");

    // Reset counter safely
    localStorage.setItem("lastNotifCount", lastCount);

    // Remove badge
    $("#notifBadge").hide();
});

// ---------------------------------------
// If new notification arrives → unmark read
// (allows sound to play again only when needed)
// ---------------------------------------
function resetReadFlagIfNeeded() {
    const savedCount = parseInt(localStorage.getItem("lastNotifCount") || "0");

    if (savedCount < lastCount) {
        localStorage.setItem("notifRead", "0");
    }
}

setInterval(resetReadFlagIfNeeded, 2000);
