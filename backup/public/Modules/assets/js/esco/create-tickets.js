function createDeviceTicket() {
    console.log("ticketing");
    // Function to fetch device data
    $.ajax({
        url: "/admin/create-device-ticket",
        type: "GET",
        success: function (res) {
            console.log(res);
            if (isResetChartPage() && isResetRoomPage()) {
                // $("#notif-summ").load(window.location.href + " #notif-summ");
                var initialDateRange = $("#notif_date").val();
                getNotification(initialDateRange); // Call getNotification with the updated date
            }
        },
        error: function (xhr, status, error) {
            console.error("Error fetching device data:", error);
        },
    });
}
function isResetChartPage() {
    // Check if the current page meets the condition to call resetChart()
    // You can use window.location or any other method to identify the page
    // For example, check if a specific element exists or has a certain attribute
    return $("#off-count").length > 0; // Assuming there's an element with id="reset-chart-page"
}
function isResetRoomPage() {
    // Check if the current page meets the condition to call resetChart()
    // You can use window.location or any other method to identify the page
    // For example, check if a specific element exists or has a certain attribute
    return $("#table-room").length > 0; // Assuming there's an element with id="reset-chart-page"
}
function initRefreshInterval() {
    // Call the function initially after 5 minutes
    // 60,000 per minute
    // 1,000 per second
    $.ajax({
        url: "fetch-config",
        type: "GET",
        data: {},
        success: function (response) {
            var data = response.data.Zoho_CreateTicketTimer;
            if (data) {
                var refreshTime = data * 60000;
            }
            // console.log(data);
            setTimeout(
                function () {
                    createDeviceTicket();
                    // Set interval to call the function every 2 minutes (120,000 milliseconds)
                    setInterval(
                        createDeviceTicket,
                        refreshTime ? refreshTime : 300000
                    );
                },
                refreshTime ? refreshTime : 300000
            ); // 15 minutes in milliseconds
        },
    });
}

// Call the initialization function
initRefreshInterval();
