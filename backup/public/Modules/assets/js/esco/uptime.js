// $(document).ready(function () {
//     var initialDateRange = $("#up_date").val();
//     console.log(initialDateRange);
//     // Listen for changes on the date input field
//     $("#up_date").on("change", function () {
//         initialDateRange = $(this).val(); // Update the initial date range when the value changes
//         getUptime(initialDateRange); // Call getUptime with the updated date
//     });

//     // Call getUptime with the initial date range
//     // getUptime(initialDateRange);
// });
// // Function to perform AJAX request
// function getUptime(date) {
//     $.ajax({
//         url: "/uptime",
//         data: {
//             date: date, // Corrected the data format
//         },
//         type: "GET", // Capitalized 'GET'
//         success: function (res) {
//             console.log(res); // Log the response
//         },
//         error: function (xhr, status, error) {
//             console.error("Error:", error); // Log any errors
//         },
//     });
// }
