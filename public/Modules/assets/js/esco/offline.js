$(document).ready(function () {
    // Call checkOfflineIncident initially
    // checkOfflineIncident();

    $(document).on( "change", "input[type='radio'][name='DevicesRadio']",function () {
            checkOfflineIncident();
           
    });
    
});
function resetChart1() {
  
    $("#chart-table1").empty(); // Clear the chart container
}
function checkOfflineIncident() {
    var device = $("input[type='radio'][name='DevicesRadio']:checked").val();
    $.ajax({
        url: "/get-offline-incident",
        data: {
            device
        },
        type: "get",
        success: function (res) {
            //console.log(res.offline_dates);
            initializeChart(res.offline_dates);
        },
        error: function () {
            console.error("Error fetching offline incident data.");
        },
    });
}

function initializeChart(offDates) {
    const offlineDates = offDates;
    let currentDate = new Date();
    let selectedMonth = currentDate.getMonth();
    let selectedYear = currentDate.getFullYear();
    function generateOfflineData(month, year) {
        const totalDays = new Date(year, month + 1, 0).getDate();
        const daysOfMonth = Array.from({ length: totalDays }, (_, i) => i + 1);
        const monthName = new Date(year, month, 1).toLocaleString("default", {
            month: "long",
        });

        return daysOfMonth.map((day) =>
            offlineDates.includes(`${monthName} ${day}, ${year}`) ? 1 : 0
        );
    }

    let offlineData = generateOfflineData(selectedMonth, selectedYear);

    const options = {
        chart: {
            type: "bar",
            height: 350,
        },
        series: [
            {
                name: "Offline",
                data: offlineData,
            },
        ],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "55%",
                endingShape: "rounded",
            },
        },
        dataLabels: {
            enabled: false,
        },
        xaxis: {
            categories: Array.from(
                { length: offlineData.length },
                (_, i) => i + 1
            ),
        },
        yaxis: {
            labels: {
                show: false,
            },
            title: {
                text: "Offline Days",
                style: {
                    color: "#FF0000",
                    fontSize: "16px",
                    fontWeight: "bold",
                },
            },
        },
        colors: ["#FF0000"],
    };

    const chart = new ApexCharts($("#chart-table1")[0], options);
    chart.render();

    function updateChart() {
        offlineData = generateOfflineData(selectedMonth, selectedYear);
        chart.updateSeries([
            {
                data: offlineData,
            },
        ]);
        chart.updateOptions({
            xaxis: {
                categories: Array.from(
                    { length: offlineData.length },
                    (_, i) => i + 1
                ),
            },
        });
    }

    $("#monthSelect, #yearSelect").on("change", function () {
        selectedMonth = $("#monthSelect").val();
        selectedYear = $("#yearSelect").val();
        updateChart();
    });

    // Clear previous options before appending
    $("#monthSelect").empty();
    $("#yearSelect").empty();

    // Populate month options
    for (let i = 0; i < 12; i++) {
        $("#monthSelect").append(
            `<option value="${i}">${new Date(selectedYear, i, 1).toLocaleString(
                "default",
                { month: "long" }
            )}</option>`
        );
    }

    // Populate year options
    const currentYear = new Date().getFullYear();
    for (let i = currentYear - 5; i <= currentYear + 5; i++) {
        $("#yearSelect").append(`<option value="${i}">${i}</option>`);
    }

    // Set selected month and year
    $("#monthSelect").val(selectedMonth);
    $("#yearSelect").val(selectedYear);
}
