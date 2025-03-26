var selectedValues = [];
var gCountry = "";
var gOrgId = "";
var gMacAddress = "";
var gmonthSelect = "";
var gyearSelect = "";
$(document).ready(function () {
 
    var initialDateRange = $("#up_date").val();
    $(".hide").hide();

    initRefreshInterval();

    // ReliableRoom();
    //test
    $(document).on("change", "#monthSelect", function () {
        gmonthSelect = $(this).val();
    });
    $(document).on("change", "#yearSelect", function () {
        gyearSelect = $(this).val();
    });
    $(document).on("change", "#regionID", function () {
        resetChart();
        updateCharts(0, 0);
        $("#off-count").html(0);
        $("#on-count").html(0);
        $("#device-body").empty();
        var country = this.value;
        var selectOrg = $("#regionOrgs");
        gCountry = country;

        $.ajax({
            url: "/get-region",
            type: "get",
            data: {
                country,
            },
            success: function (res) {
                var orgs = res.orgs;
                var rooms = res.rooms;
                selectOrg.empty();
                var tbody = $("#roomBody");
                if (orgs.length !== 0) {
                    // Append new options
                    selectOrg.append(
                        "<option>" + "Select Organization" + "</option>"
                    );
                    orgs.forEach(function (org, index) {
                        selectOrg.append(
                            '<option value="' +
                                org.Company_Id +
                                '">' +
                                org.Company_Name +
                                "-" +
                                org.Company_Address +
                                "</option>"
                        );
                    });
                } else {
                    selectOrg.append(
                        "<option>" + "Select Region First" + "</option>"
                    );
                }
                if (rooms && rooms.length > 0) {
                    tbody.empty();
                    $("#room-message").hide();
                    rooms.forEach(function (room, index) {
                        // Get the device name
                        var roomName = room.DeviceRoomName;
                        var roomID = room.DeviceRoomID;
                        // Create a new row element
                        var rows = $("<tr>");
                        var checkWrapper = $(
                            "<div class='form-check form-checkbox-success mb-2'>"
                        );
                        // Create a table data cell
                        var td = $("<td>");
                        // Append the device name input inside the table data cell
                        rows.append(
                            td.append(
                                checkWrapper.append(
                                    "<input type='checkbox' class='form-check-input' name='roomID" +
                                        (index + 1) +
                                        "' value='" +
                                        roomID +
                                        "'>"
                                )
                            )
                        );
                        rows.append(
                            "<td style='whitespace:nowrap'>" +
                                roomName +
                                "</td>"
                        );
                        if (index % 2 === 0) {
                            rows.addClass("table-active");
                        }
                        // Append the new row to the tbody
                        tbody.append(rows);
                    });
                } else {
                    tbody.empty();
                    $("#room-message").show();
                }
                // Call checkRoom after populating checkboxes
                // checkRoom();
            },
        });
    });
    $(document).on("change", "#regionOrgs", function () {
        resetChart();
        updateCharts(0, 0);
        $("#off-count").html(0);
        $("#on-count").html(0);
        $("#device-body").empty();
        var orgId = this.value;
        $("#tbbody1").empty();
        $("#tbbody2").empty();
        gOrgId = orgId;
        $.ajax({
            url: "/get-rooms",
            type: "get",
            data: {
                orgId,
            },
            success: function (res) {
                var rooms = res.rooms;
                var tbody = $("#roomBody");
                if (rooms && rooms.length > 0) {
                    tbody.empty();
                    $("#room-message").hide();
                    rooms.forEach(function (room, index) {
                        // Get the device name
                        var roomName = room.DeviceRoomName;
                        var roomID = room.DeviceRoomID;
                        // Create a new row element
                        var rows = $("<tr>");
                        var checkWrapper = $(
                            "<div class='form-check form-checkbox-success mb-2'>"
                        );
                        // Create a table data cell
                        var td = $("<td>");
                        // Append the device name input inside the table data cell
                        rows.append(
                            td.append(
                                checkWrapper.append(
                                    "<input type='checkbox' class='form-check-input' name='roomID" +
                                        (index + 1) +
                                        "' value='" +
                                        roomID +
                                        "'>"
                                )
                            )
                        );
                        rows.append(
                            "<td style='whitespace:nowrap'>" +
                                roomName +
                                "</td>"
                        );
                        if (index % 2 === 0) {
                            rows.addClass("table-active");
                        }
                        // Append the new row to the tbody
                        tbody.append(rows);
                    });
                } else {
                    tbody.empty();

                    $("#room-message").show();
                }
                $("#unres_notif").empty();
                $(res.NotifCount).each(function () {
                    $("#new_notif").append(this.Ticket_New);
                    $("#unres_notif").append(this.Ticket_Count);
                });
                // Call checkRoom after populating checkboxes
                // checkRoom();
            },
        });
    });
    $(document).on("change", "#regionOrgs, #reliable_date", function () {
        var date_range = $("#reliable_date").val();
        var orgId = $("#regionOrgs").val();
        $("#tbbody1").empty();
        $("#tbbody2").empty();
        $("#reliable").addClass("d-flex").show();
        $.ajax({
            url: "/reliable-rooms",
            type: "get",
            data: {
                orgId,
                date_range,
            },
            success: function (response) {
                $("#basic-datatable tbody tr").remove();
                var RowID = 0;
                $(response.desc).each(function () {
                    var markup = "";
                    RowID = RowID + 1;
                    markup =
                        "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                    markup = markup + "<td>" + this.Room + "</td>";
                    var percentage = this.AveragePercentage;
                    if (percentage <= 0) {
                        percentage = 0;
                    }
                    markup =
                        markup +
                        "<td>" +
                        Number(percentage).toFixed(2) +
                        "%" +
                        "</td>";
                    markup = markup + "<td>" + this.Location + "</td>";
                    markup = markup + "</tr>";
                    $("#tbbody1").append(markup);
                });

                RowID = 0;
                $(response.asc).each(function () {
                    var markup = "";
                    RowID = RowID + 1;
                    markup =
                        "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                    markup = markup + "<td>" + this.Room + "</td>";
                    var percentage = this.AveragePercentage;
                    if (percentage <= 0) {
                        percentage = 0;
                    }
                    markup =
                        markup +
                        "<td>" +
                        Number(percentage).toFixed(2) +
                        "%" +
                        "</td>";
                    markup = markup + "<td>" + this.Location + "</td>";
                    markup = markup + "</tr>";
                    $("#tbbody2").append(markup);
                });
            },
        });
    });
    //end test
    // Attach event listener for checkboxes
    $(document).on("change", 'input[type="checkbox"]', function () {
        // Initialize an empty array to store selected values
        $(".hide").show();
        var selectedValue = $(this).val();
        initialDateRange = $("#up_date").val(); // assuming #up_date is an input element
        if ($(this).is(":checked")) {
            selectedValues.push(selectedValue);
        } else {
            var index = selectedValues.indexOf(selectedValue);
            if (index !== -1) {
                //   $("#basic-datatable1 tbody tr").remove();
                selectedValues.splice(index, 1);
            }
        }
        if (selectedValues == "") {
            $(".progress").hide();
        } else {
            $(".progress").show();
        }

        checkRoom();
        InitUptime(selectedValues, initialDateRange);
    });
    $(document).on("change", "#up_date", function () {
        // Handle change event for #up_date
        $(".hide").show();
        initialDateRange = $(this).val();
        checkRoom();
        InitUptime(selectedValues, initialDateRange);
    });
    $(document).on("change", 'input[name="DevicesRadio"]', function () {
        var selectedValue = $(this).val(); //this is selected device ID
        gMacAddress = selectedValue;
    });
    //     var DeviceID = $("input[name='DevicesRadio']:checked").val();
    // alert(DeviceID)
    checkRoom();
    // Function to fetch data based on checked rooms
    // Initial call to checkRoom to handle any pre-existing checked checkboxes
});
//From fresh-devices
function refreshDevice() {
    console.log("refreshing");
    // Function to fetch device data
    $.ajax({
        url: "refresh-device",
        type: "GET",
        success: function (res) {
            // if (res.online || res.offline) {
            //     // var onlineData = res.online;
            //     // var offlineData = res.offline;
            //     var onlineData = 0;
            //     var offlineData = 0;
            //     if ($("#off-count").length && $("#on-count").length) {
            //         $("#off-count").html(0);
            //         $("#on-count").html(0);
            //         // Update chart1 with new data
            //         if (isResetChartPage() && isResetRoomPage()) {
            //             resetChart();
            //             // resetChart1();
            //             updateCharts(onlineData, offlineData);
            //             $("#chart-table1").empty();
            //             var body = $("#device-body");
            //             var tbody = $("#roomBody");
            //             body.empty();
            //             tbody.empty();
            //             $("#room-message").show();
            //             $("#room-message").hide();
            //             // checkRoom();
            //         }
            //     }
            // }
        },
        error: function (xhr, status, error) {
            console.error("Error fetching device data:", error);
        },
    });
}

function refreshDeviceWithParam(SelValues) {
    console.log("Refresh with params");
    $.ajax({
        url: "refresh-device-with-param",
        type: "GET",
        data: { SelValues, gOrgId, gCountry, gmonthSelect, gyearSelect },
        success: function (response) {
            
            if(response.error){
                alert(response.error);
            }
            if (response.DeviceOfflineIncidets) {
                var offlineData = parseInt(
                    response.DeviceOfflineIncidets[0].offline_count
                );
                var onlineData = parseInt(
                    response.DeviceOfflineIncidets[0].online_count
                );
                // console.log(onlineData, offlineData);
                // var onlineData = 0;
                // var offlineData = 0;
                if ($("#off-count").length && $("#on-count").length) {
                    $("#off-count").html(offlineData);
                    $("#on-count").html(onlineData);
                    // Update chart1 with new data
                    if (isResetChartPage() && isResetRoomPage()) {
                        resetChart();
                        // resetChart1();
                        updateCharts(onlineData, offlineData);
                        // $("#chart-table1").empty();
                        // var body = $("#device-body");
                        // var tbody = $("#roomBody");
                        // body.empty();
                        // tbody.empty();
                        // $("#room-message").show();
                        // $("#room-message").hide();
                        // checkRoom();
                    }
                }
            }
            $("#device-body tr").remove();
            $("#unres_notif").empty();
            $(response.Devices).each(function () {
                var markup = "";
                markup = "";
                markup = "<tr class='text-center'>";
                markup =
                    markup +
                    "<td>" +
                    "<input type='radio' value='" +
                    this.Mac_Address +
                    "' name='DevicesRadio' class='form-check-input dr ' >" +
                    "</td>";
                markup =
                    markup +
                    "<td style='whitespace:nowrap;color:black;'>" +
                    this.Device_Name +
                    "</td>";
                markup =
                    markup +
                    "<td style='whitespace:nowrap;color:black;'>" +
                    this.Manufacturer +
                    "</td>";
                markup =
                    markup +
                    "<td style='whitespace:nowrap;color:black;'>" +
                    this.DeviceRoomLocation +
                    "</td>";
                markup =
                    markup +
                    "<td style='whitespace:nowrap;color:black;'>" +
                    this.Status +
                    "</td>";
                markup = markup + "</tr>";
                $("#table-devices tbody").append(markup);
            });
            $(response.NotificationSumm).each(function () {
                $("#unres_notif").append(this.Ticket_Count);
            });
            $(response.DeviceOfflineIncidets).each(function () {});
            $('input[type="radio"][value="' + gMacAddress + '"]')
                .prop("checked", true)
                .change();
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
    $.ajax({
        url: "fetch-config",
        type: "GET",
        data: {},
        success: function (response) {
            var data = response.data.Crestron_ApiRefresh;
            if (data) {
                var refreshTime = data * 60000 + 1000; //multiply per minute plus 1 extra second
            }
            setTimeout(
                function () {
                    if (selectedValues.length === 0) {
                        refreshDevice();
                    } else {
                        refreshDeviceWithParam(selectedValues);
                    }
                    // Set interval to call the function every 2 minutes (120,000 milliseconds)
                    //setInterval(refreshDevice, data ? data : 120000);
                    setInterval(
                        function () {
                            if (selectedValues.length === 0) {
                                refreshDevice();
                            } else {
                                refreshDeviceWithParam(selectedValues);
                            }
                        },
                        refreshTime ? refreshTime : 121000 // 2minutes and 1 second
                    );
                },
                refreshTime ? refreshTime : 121000
            ); // 15 minutes in milliseconds
        },
    });
}
//end of fresh devices
function checkRoom() {
    var checkedRooms = [];
    $('input[type="checkbox"]').each(function () {
        if ($(this).is(":checked")) {
            checkedRooms.push($(this).val());
        }
    });
    if (checkedRooms.length >= 0) {
        $.ajax({
            url: "/rooms",
            method: "GET",
            data: { checkedRooms: checkedRooms },
            success: function (res) {
                var devices = res.devices;
                var tbody = $("#device-body");
                if (res.online || res.offline) {
                    var onlineData = res.online;
                    var offlineData = res.offline;
                    if ($("#off-count").length && $("#on-count").length) {
                        $("#off-count").html(res.offline);
                        $("#on-count").html(res.online);
                        tbody.empty();
                        if (devices && devices.length > 0) {
                            devices.forEach(function (device, index) {
                                var deviceName = device.Device_Name;
                                var deviceManu = device.Manufacturer;
                                var deviceLoc = device.Device_Loc;
                                var mac = device.Mac_Address;
                                var rows = $("<tr>");
                                var checkWrapper = $(
                                    "<div class='form-check form-checkbox-success mb-2'>"
                                );
                                var td = $("<td>");
                                rows.append(
                                    td.append(
                                        checkWrapper.append(
                                            // "<input type='checkbox' class='form-check-input' value='" + mac + "'>"+
                                            "<input type='radio' id='' value='" +
                                                mac +
                                                "' name='DevicesRadio' class='form-check-input dr ' >"
                                        )
                                    )
                                );
                                rows.append(
                                    "<td style='whitespace:nowrap;color:black;'>" +
                                        deviceName +
                                        "</td>"
                                );
                                rows.append(
                                    "<td style='whitespace:nowrap;color:black;'>" +
                                        deviceManu +
                                        "</td>"
                                );
                                rows.append(
                                    "<td style='white-space:nowrap;color:black;'>" +
                                        deviceLoc +
                                        "</td>"
                                );
                                rows.append(
                                    "<td style='white-space:nowrap;color:black;'>" +
                                        device.Status +
                                        "</td>"
                                );
                                if (index % 2 === 0) {
                                    rows.addClass("table-active");
                                }
                                tbody.append(rows);
                            });
                        }
                        let online = parseInt(onlineData);
                        let offline = parseInt(offlineData);
                        resetChart();
                        updateCharts(online, offline);
                    }
                }
            },
        });
    }
}

function InitUptime(selectedValues, initialDateRange) {
    $.ajax({
        type: "GET",
        url: "/uptime",
        data: {
            selectedValues,
            initialDateRange,
        },
        success: function (response) {
            // console.log(response);
            $("#basic-datatable1 tbody tr").remove();
            var RowID = 0;
            var uptime = 0;
            var count = 0;
            $(response.data).each(function () {
                var markup = "";
                RowID = RowID + 1;
                markup =
                    "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                // if (this.uptime_percentage == null) {
                //     uptime = 100;
                // } else {
                //     uptime = this.uptime_percentage;
                // }
                markup =
                    markup + "<td class='text-center'>" + this.Uptime + "</td>";
                // if (this.count == null) {
                //     count = 0;
                // } else {
                //     count = this.count;
                // }
                markup =
                    markup +
                    "<td class='text-center'>" +
                    this.Incidents +
                    "</td>";
                markup = markup + "<td>" + this.Device_Name + "</td>";
                markup = markup + "<td>" + this.Manufacturer + "</td>";
                markup = markup + "<td>" + this.Room_Type + "</td>";
                markup = markup + "<td>" + this.Serial_Number + "</td>";
                markup = markup + "</tr>";
                $("#tbbody5").append(markup);
            });
            $("#aveuptime").remove();
            var all = 0;

            // Iterate through the array and calculate sum
            $.each(response.data, function () {
                all += this.percent;
            });
            var ave = all / response.data.length;
            // Append progress bars
            $(".progress").append(
                "<div class='progress-bar bg-info' id='aveuptime' role='progressbar' style='max-height:30px;width:" +
                    ave.toFixed(2) +
                    "%' aria-valuenow='" +
                    ave +
                    "' aria-valuemin='" +
                    0 +
                    "' aria-valuemax='100'>" +
                    ave.toFixed(2) +
                    "%" +
                    "</div>"
            );
        },
    });
}

// function ReliableRoom() {
//     $.ajax({
//         type: "GET",
//         url: "/reliablerooms",
//         data: {},
//         success: function (response) {
//             $("#basic-datatable tbody tr").remove();
//             var RowID = 0;
//             $(response.MostReliable).each(function () {
//                 var markup = "";
//                 RowID = RowID + 1;
//                 markup =
//                     "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
//                 markup = markup + "<td>" + this.DeviceRoomName + "</td>";
//                 markup =
//                     markup +
//                     "<td>" +
//                     Number(this.average).toFixed(2) +
//                     "%" +
//                     "</td>";
//                 markup = markup + "<td>" + this.DeviceRoomLocation + "</td>";
//                 markup = markup + "</tr>";
//                 $("#tbbody1").append(markup);
//             });
// function ReliableRoom() {
//     $.ajax({
//         type: "GET",
//         url: "/reliablerooms",
//         data: {},
//         success: function (response) {
//             $("#basic-datatable tbody tr").remove();
//             var RowID = 0;
//             $(response.MostReliable).each(function () {
//                 var markup = "";
//                 RowID = RowID + 1;
//                 markup =
//                     "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
//                 markup = markup + "<td>" + this.DeviceRoomName + "</td>";
//                 markup =
//                     markup +
//                     "<td>" +
//                     Number(this.average).toFixed(2) +
//                     "%" +
//                     "</td>";
//                 markup = markup + "<td>" + this.DeviceRoomLocation + "</td>";
//                 markup = markup + "</tr>";
//                 $("#tbbody1").append(markup);
//             });

//             RowID = 0;
//             $(response.LeastReliable).each(function () {
//                 var markup = "";
//                 RowID = RowID + 1;
//                 markup =
//                     "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
//                 markup = markup + "<td>" + this.DeviceRoomName + "</td>";
//                 markup =
//                     markup +
//                     "<td>" +
//                     Number(this.average).toFixed(2) +
//                     "%" +
//                     "</td>";
//                 markup = markup + "<td>" + this.DeviceRoomLocation + "</td>";
//                 markup = markup + "</tr>";
//                 $("#tbbody2").append(markup);
//             });
//         },
//     });
// }
//             RowID = 0;
//             $(response.LeastReliable).each(function () {
//                 var markup = "";
//                 RowID = RowID + 1;
//                 markup =
//                     "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
//                 markup = markup + "<td>" + this.DeviceRoomName + "</td>";
//                 markup =
//                     markup +
//                     "<td>" +
//                     Number(this.average).toFixed(2) +
//                     "%" +
//                     "</td>";
//                 markup = markup + "<td>" + this.DeviceRoomLocation + "</td>";
//                 markup = markup + "</tr>";
//                 $("#tbbody2").append(markup);
//             });
//         },
//     });
// }
