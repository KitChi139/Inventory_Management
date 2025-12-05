
let lastCount = parseInt(localStorage.getItem("lastNotifCount") || "0");

function loadNotifications() {
    $.ajax({
        url: "get_notifications.php",
        method: "GET",
        dataType: "json",
        success: function(data) {

            const total = data.messages + data.lowstock;

            if (total > 0) {
                $("#notifBadge").text(total).show();
            } else {
                $("#notifBadge").hide();
            }

            const marked = localStorage.getItem("notifRead") === "1";

            if (!marked && total > lastCount) {
                const audio = document.getElementById("notifSound");
                audio.play().catch(() => {});
            }

            lastCount = total;
            localStorage.setItem("lastNotifCount", total);
        }
    });
}

$(document).ready(function () {
    loadNotifications();
});

setInterval(loadNotifications, 10000);

$(document).on("click", "#notifBtn", function () {

    $.get("get_notifications.php?mark_read=1");

    localStorage.setItem("notifRead", "1");

    localStorage.setItem("lastNotifCount", lastCount);

    $("#notifBadge").hide();
});

function resetReadFlagIfNeeded() {
    const savedCount = parseInt(localStorage.getItem("lastNotifCount") || "0");

    if (savedCount < lastCount) {
        localStorage.setItem("notifRead", "0");
    }
}

setInterval(resetReadFlagIfNeeded, 2000);
