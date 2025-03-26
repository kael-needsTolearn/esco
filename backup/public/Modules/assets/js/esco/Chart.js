// document.addEventListener("DOMContentLoaded", function () {
//     var daysOfMonth = []; // Array to store days of the month
//     var offlineData = []; // Array to store offline status for each day

//     // Generating days of the month
//     var currentDate = new Date();
//     var currentMonth = currentDate.getMonth();
//     var totalDays = new Date(currentDate.getFullYear(), currentMonth + 1, 0).getDate();
//     for (var i = 1; i <= totalDays; i++) {
//         daysOfMonth.push(i);
//         offlineData.push(Math.random() < 0.2); // Simulating offline status randomly (20% chance of being offline)
//     }

//     var monthName = currentDate.toLocaleString('default', { month: 'long' }); // Get the name of the current month

//     var options = {
//         chart: {
//             type: 'bar',
//             height: 350
//         },
//         series: [{
//             name: 'Offline Days',
//             data: offlineData.map(status => status ? 1 : 0)
//         }],
//         plotOptions: {
//             bar: {
//                 horizontal: false,
//                 columnWidth: '55%',
//                 endingShape: 'rounded'
//             },
//         },
//         dataLabels: {
//             enabled: false
//         },
//         xaxis: {
//             categories: daysOfMonth
//         },
//         yaxis: {
//             title: {
//                 text: monthName, // Displaying the name of the month on the y-axis
//                 style: {
//                     color: '#FF0000', // Setting the color to red
//                     fontSize: '16px', // Adjusting font size if needed
//                     fontWeight: 'bold' // Making the text bold if needed
//                 }
//             }
//         },
//         colors: ['#000000'] // Setting the color to black for the bars
//     }

//     var chart = new ApexCharts(document.querySelector("#chart-table1"), options);
//     chart.render();
// });
