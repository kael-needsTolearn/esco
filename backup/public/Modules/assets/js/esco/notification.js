$(document).ready(function () {
    var initialDateRange = $("#notif_date").val();
    // console.log(initialDateRange);
    // Listen for changes on the date input field
    $("#notif_date").on("change", function () {
        initialDateRange = $(this).val(); // Update the initial date range when the value changes
        getNotification(initialDateRange); // Call getNotification with the updated date
    });

    // Call getNotification with the initial date range
    getNotification(initialDateRange);
});
// Function to perform AJAX request
function getNotification(date) {
    $.ajax({
        url: "/date-notif",
        data: {
            date: date, // Corrected the data format
        },
        type: "GET", // Capitalized 'GET'
        success: function (res) {
           
            $(res.new).each(function () {
                $("#new_notif").html(this.Ticket_New);
            });
          
                $("#res_notif").html(res.res);
        
            $(res.unres).each(function () {
                $("#unres_notif").html(this.Ticket_Count);
            });
        },
        error: function (xhr, status, error) {
            console.error("Error:", error); // Log any errors
        },
    });
}
