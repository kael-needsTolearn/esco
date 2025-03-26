var selectedValues = [];
var gCountry = "";
var gOrgId = "";
var gMacAddress = "";
var gmonthSelect = "";
var gyearSelect = "";
var gCompany_Id="";
var activeAjaxRequests = "";
var gcheckedRooms = [];
var uncheckedRooms = [];
//Note that the initial display is in the controller

$(document).ready(function () {
    // $(".form-switch").hide();
    
    $('#SaveField').on('click', function(e) {
        e.preventDefault();
        var Inputval = $('#sessionPassword').val();
        var csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get the CSRF token
        if(Inputval==''){
            $.toast({
                heading: "Warning",
                text:"Enter a pin to save changes.",
                position: "top-right",
                loaderBg: "white",
                showHideTransition: "fade",
                icon: "warning",
                hideAfter: 2000,
            });
        }else{
        
        }
    });
    $('#Close-Modal').on('click', function(e) {
        setupIdleTracking();
    });
    //end of handling session timeout
    var initialDateRange = $("#up_date").val();
    checkedRooms=[];
    $("#MRR").hide();
    $("#LRR").hide();
    $(".hide").hide();
    $('input[type="checkbox"].ckbox:checked').each(function() {
        checkedRooms.push($(this).val());
    });
    setInterval(function() {
        //triggerCheckboxChange();
        if($("#regionID").val() === "Select Region"){
            triggerTickets();
            triggerUptimeDevice();
        }else{
           // console.log('do nothing');
        }
       
        //refreshDashboard();
        //console.log('trigger checkbox and tickets and radio')
    }, 60000);
    setInterval(function() {
        if($("#regionID").val() === "Select Region"){
            triggerCheckboxChange();
            refreshDashboard();
        }else{
           // console.log($("#regionID").val());
           // console.log('do nothing');
        }
       
        //console.log('trigger checkbox and tickets and radio')
    }, 90000);
    $(document).on("change", "#monthSelect", function () {
        gmonthSelect = $(this).val();
    });
    $(document).on("change", "#yearSelect", function () {
        gyearSelect = $(this).val();
    });
    $(document).on("change", "#regionID", function () {
        var country = $(this).val();
        $.ajax({
            url: "/get-region",
            type: "get",
            data: {
                country,
            },
            success: function (res) {
                $("#regionOrgs option:not(:first)").remove();
                $(res.CompanyId).each(function(index,item){
                   $("#regionOrgs").append("<option value="+item.Company_Id+">"+item.Company_Address+"</option>");
                })
            },
        });
        var today = new Date();
        var todayFormatted = ('0' + (today.getMonth() + 1)).slice(-2) + '/' + ('0' + today.getDate()).slice(-2) + '/' + today.getFullYear();
       
        $('#up_date').val(todayFormatted + ' - ' + todayFormatted);
        $('#up_date').trigger('change');
        //hide contents
        $("#hideDeviceStatus").hide();
        $("#hideNotifSummary").hide();
        $("#hideDevicesTable").hide(); 
        $("#hideUptimeDevice").hide();
        $("#hideAvailableRooms").hide();
        $("#upbydev").hide();
        //Hide uptime by device too
        resetChart();
        $("#chart-table1").empty();
        $("#off-count").html(0);
        $("#on-count").html(0);
        $("#device-body").empty();
        var country = this.value;
        gCountry = country;
    });
    $(document).on("change", "#regionOrgs", function () { //onchange of organization display rooms
        resetChart1();
        if(this.value!= ''){
            $("#MRR").show(); //most reliable room
            $("#LRR").show(); //Least reliable room
            resetChart();
            $("#off-count").html(0);
            $("#on-count").html(0);
            $("#Unknown-count").html(0);
            $("#device-body").empty();
            $("#tbbody1").empty();
            $("#tbbody2").empty();
            gOrgId = this.value;
                $.ajax({
                    url: "/get-rooms",
                    type: "get",
                    data: {
                        gOrgId,gCountry,
                    },
                    success: function (res) {
                       
                        $("#new_notif").empty();
                        $("#res_notif").empty();
                        $("#unres_notif").empty();

                        $("#roomBody tr").remove();
                        $(res.Rooms).each(function(index,items){
                            markup="";
                            markup= markup +"<tr class='table-active'>"+
                                                "<td>"+
                                                "<div class='form-check form-checkbox-success mb-2'>"+
                                                    "<input value="+items.DeviceRoomID+" name='roomID1' type='checkbox' class='form-check-input ckbox'>"+
                                                "</div>"+
                                                "</td>"+
                                                "<td style='whitespace:nowrap'>"+items.DeviceRoomName+"</td>"+
                                            "</tr>"
                            $("#roomBody").append(markup);
                        });
                        //here po 
                        $("#new_notif").append(res.NewNotif.new_count);
                        $("#res_notif").append(res.ResolvedNotif.resolved_count);
                        $("#unres_notif").append(res.UnresolvedNotif.unresolved_count);


                        $("#hideDeviceStatus").show();
                        $("#hideNotifSummary").show();
                        $("#hideDevicesTable").show(); 
                        $("#hideUptimeDevice").show();
                        $("#hideAvailableRooms").show();
                        $("#upbydev").show();
                    },
                });
        }
 
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
                if(response.error){
                    console.log(response.error);
                }else{
                    $("#basic-datatable tbody tr").remove();
                    var RowID = 0;
                    $(response.desc).each(function () {
                        var markup = "";
                        RowID = RowID + 1;
                        markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                        markup = markup + "<td>" + this.Room_Type + "</td>";
                        var percentage = this.AverageUptime;
                        if (percentage <= 0) {
                            percentage = 0;
                        }
                        markup =markup +"<td>" +Number(percentage).toFixed(2) + "%" +"</td>";
                        markup = markup + "<td>" + this.Location + "</td>";
                        markup = markup + "</tr>";
                        $("#tbbody1").append(markup);
                    });

                    RowID = 0;
                    $(response.asc).each(function () {
                        var markup = "";
                        RowID = RowID + 1;
                        markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                        markup = markup + "<td>" + this.Room_Type + "</td>";
                        var percentage = this.AverageUptime;
                        if (percentage <= 0) {
                            percentage = 0;
                        }
                        markup = markup +"<td>" +Number(percentage).toFixed(2) +"%" +"</td>";
                        markup = markup + "<td>" + this.Location + "</td>";
                        markup = markup + "</tr>";
                        $("#tbbody2").append(markup);
                    });
                }
            },
        });
    });
    $(document).on("change", 'input[type="checkbox"].ckbox', function () {
        // Initialize an empty array to store selected values
            checkedRooms=[];
            uncheckedRooms=[];
            $(".hide").show();
        $('input[type="checkbox"].ckbox').each(function() {
            if ($(this).is(':checked')) {
                checkedRooms.push($(this).val());
            } else {
                uncheckedRooms.push($(this).val());
            }
        });
            initialDateRange = $("#up_date").val(); 
            if (checkedRooms == "") {
                $(".progress").hide();
            } else {
                $(".progress").show();
            }
            gcheckedRooms=checkedRooms;
            checkRoom(checkedRooms);
           
    });
    $(document).on("change", "#up_date", function () {
        $(".hide").show();
        initialDateRange = $(this).val();
        InitUptime(selectedValues, initialDateRange);
    });
    $(document).on("change", "#notif_date", function () {
       // $(".hide").show();
        initialDateRange = $(this).val();
        $("#new_notif").empty();
        $("#res_notif").empty();
        $("#unres_notif").empty();
        $.ajax({
            type: "GET",
            url: "/filter-notification",
            data: {
                initialDateRange,
            },
            success: function (response) {
                
             if(response.Error){

             }else{

                $("#new_notif").append(response.NewNotif.new_count);
                $("#res_notif").append(response.ResolvedNotif.resolved_count);
                $("#unres_notif").append(response.UnresolvedNotif.unresolved_count);
            }
            },
      
        });
     //   InitUptime(selectedValues, initialDateRange);
    });
    $(document).on("change", 'input[name="DevicesRadio"]', function () {
        var selectedValue = $(this).val(); //this is selected device ID
        gMacAddress = selectedValue;
        resetChart1();
    });
    //if($("#regionID").val() === ""){
        triggerCheckboxChange();
   // }else{
    //    console.log('do nothings');
    //}
    
    setTimeout(refreshDevice, 131000); 

});

function resetChart1() {
    $("#chart-table1").empty(); // Clear the chart container
}
function resetChart() {
   
    $("#chart-table1").empty(); // Clear the chart container
}
function refreshDevice(){
    $.ajax({
         url: "/refresh-device",
         type: "GET",
         success: function(res) {
            DeviceStatus();
            setTimeout(refreshDevice, 131000);
         }, 
	error: function(xhr, status, error) {
                console.log(xhr,status,error)
		 $.toast({
                    heading: "Error",
                    text:"Session Expired!",
                    position: "top-right",
                    loaderBg: "white",
                    showHideTransition: "fade",
                    icon: "error",
                    hideAfter: 10000,
                });
		location.reload();
            }
     });
}

function refreshDeviceWithParam(SelValues) {
    $.ajax({
        url: "refresh-device-with-param",
        type: "GET",
        data: { SelValues, gOrgId, gCountry, gmonthSelect, gyearSelect },
        success: function (response) {
            
            if(response.error){
                console.log(response.error);
            }
            if (response.DeviceOfflineIncidets) {
                var offlineData = parseInt(
                    response.DeviceOfflineIncidets[0].offline_count
                );
                var onlineData = parseInt(
                    response.DeviceOfflineIncidets[0].online_count
                );
                if ($("#off-count").length && $("#on-count").length) {
                    $("#off-count").html(offlineData);
                    $("#on-count").html(onlineData);
                    // Update chart1 with new data
                    if (isResetChartPage() && isResetRoomPage()) {
                        resetChart();
                        updateCharts(onlineData, offlineData,OK,Missing,Unknown,Fault,Initializing,Compromised,NotPresent);
                     
                    }
                }
            }
            $("#device-body tr").remove();
            $("#unres_notif").empty();// 06-18 here
            $(response.Devices).each(function () {
                var markup = "";
                markup = "";
                markup = "<tr class='text-center'>";
                markup =  markup +  "<td>" +  "<input type='radio' value='" +  this.Device_Id +  "' name='DevicesRadio' class='form-check-input dr ' >" +  "</td>";
                markup =  markup + "<td style='whitespace:nowrap;color:black;'>" +this.Device_Name + "</td>";
                markup = markup +"<td style='whitespace:nowrap;color:black;'>" + this.Manufacturer + "</td>";
                markup = markup +"<td style='whitespace:nowrap;color:black;'>" + this.Device_Desc + "</td>";
                markup = markup + "<td style='whitespace:nowrap;color:black;'>" +this.DeviceRoomLocation + "</td>";
                markup = markup + "<td style='whitespace:nowrap;color:black;'>" + this.Status + "</td>";
                markup = markup + "</tr>";
                $("#table-devices tbody").append(markup);
            });
            $(response.NotificationSumm).each(function () {
                $("#unres_notif").append(this.Ticket_Count);
            });
            $(response.DeviceOfflineIncidets).each(function () {});
            $('input[type="radio"][value="' + gMacAddress + '"]').prop("checked", true).change();
        },
        error: function (xhr, status, error) {
            console.error("Error fetching device data:", error);
        },
    });
}
function isResetChartPage() {
    return $("#off-count").length > 0; // Assuming there's an element with id="reset-chart-page"
}
function isResetRoomPage() {
    return $("#table-room").length > 0; // Assuming there's an element with id="reset-chart-page"
}
function DeviceStatus(){
    $.ajax({
        url: "/DeviceStatus",
        method: "GET",
        data: { checkedRooms: checkedRooms },
        success: function (res) {
            if(res.error){
                location.reload(true);
            }else{
                var onlineData = res.online;
                var offlineData = res.offline;
                var OKData = res.OK;
                var MissingData = res.Missing;
                var UnknownData = res.Unknown;
                var InitializingData = res.Initializing;
                var CompromisedData = res.Compromised;
                var FaultData = res.Fault;
                var NotPresentData = res.NotPresent;
                    $("#off-count").html(res.offline);
                    $("#on-count").html(res.online);
                    $("#OK-count").html(res.OK);
                    $("#Missing-count").html(res.Missing);
                    $("#Unknown-count").html(res.Unknown);
                    $("#Initializing-count").html(res.Initializing);
                    $("#Compromised-count").html(res.Compromised);
                    $("#Fault-count").html(res.Fault);
                    $("#NotPresent-count").html(res.NotPresent);
                    let online = parseInt(onlineData);
                    let offline = parseInt(offlineData);
                    let OK = parseInt(OKData);
                    let Missing = parseInt(MissingData);
                    let Unknown = parseInt(UnknownData);
                    let Initializing = parseInt(InitializingData);
                    let Compromised = parseInt(CompromisedData);
                    let Fault = parseInt(FaultData);
                    let NotPresent = parseInt(NotPresentData);
                    resetChart();
                    updateCharts(online, offline,OK,Missing,Unknown,Fault,Initializing,Compromised,NotPresent);
            }    
        },
    });
}
function checkRoom(checkedRooms) { //Initial Display of Device Status

    if (checkedRooms.length >= 0) {
        $.ajax({
            url: "/rooms",
            method: "GET",
            data: { checkedRooms: checkedRooms },
            success: function (res) {
                $("#device-body tr").remove();
                var tbody = $("#device-body");
                    var onlineData = res.online;
                    var offlineData = res.offline;
                    var OKData = res.OK;
                    var MissingData = res.Missing;
                    var UnknownData = res.Unknown;
                    var InitializingData = res.Initializing;
                    var CompromisedData = res.Compromised;
                    var FaultData = res.Fault;
                    var NotPresentData = res.NotPresent;

                    if ($("#off-count").length && $("#on-count").length) {
                        $("#off-count").html(res.offline);
                        $("#on-count").html(res.online);
                        $("#OK-count").html(res.OK);
                        $("#Missing-count").html(res.Missing);
                        $("#Unknown-count").html(res.Unknown);
                        $("#Initializing-count").html(res.Initializing);
                        $("#Compromised-count").html(res.Compromised);
                        $("#Fault-count").html(res.Fault);
                        $("#NotPresent-count").html(res.NotPresent);
                        
                        var RowID=0;
                        $("#table-devices tbody tr").not(':first').remove();
                        $(res.devices).each(function(index,device){
                            RowID = RowID+1;
                            var markup = '';
                                markup = "";
                                markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                                markup = markup + `<td><input type='radio' value='${device.Device_Id}' name='DevicesRadio' class='form-check-input dr'></td>`;
                                markup = markup + `<td style='whitespace:nowrap;color:black;'>  ${device.Device_Name} </td>`;
                                markup = markup + `<td style='whitespace:nowrap;color:black;'> ${device.Device_Desc} </td>`;
                                markup = markup + `<td style='whitespace:nowrap;color:black;'> ${device.Manufacturer} </td>`;
                                markup = markup + `<td style='whitespace:nowrap;color:black;'>${device.Device_Loc} </td>`;
                                markup = markup + `<td style='whitespace:nowrap;color:black;'> ${device.Status} </td>`;
                                markup = markup + "</tr>";
                            $("#device-body").append(markup);
                        });
               
                        let online = parseInt(onlineData);
                        let offline = parseInt(offlineData);
                        let OK = parseInt(OKData);
                        let Missing = parseInt(MissingData);
                        let Unknown = parseInt(UnknownData);
                        let Initializing = parseInt(InitializingData);
                        let Compromised = parseInt(CompromisedData);
                        let Fault = parseInt(FaultData);
                        let NotPresent = parseInt(NotPresentData);
                        resetChart();
                        updateCharts(online, offline,OK,Missing,Unknown,Fault,Initializing,Compromised,NotPresent);
                    }
              
                    $("#new_notif").append(res.NewNotif);
                    $("#res_notif").append(res.ResolvedNotif);
                    $("#unres_notif").append(res.UnresolvedNotif);
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
            
         if(response.Error){
           // alert("No Account Access Assigned");
         }else{
            $("#basic-datatable1 tbody tr").remove();
            var RowID = 0;
            var uptime = 0;
            var count = 0;
            $(response.data).each(function () {
                var markup = "";
                RowID = RowID + 1;
                markup ="<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                markup = markup + "<td class='text-center'>" + this.Uptime + "</td>";
                markup =  markup + "<td class='text-center'>" +  this.Incidents +  "</td>";
                markup = markup + "<td>" + this.Device_Name + "</td>";
                markup = markup + "<td>" + this.Manufacturer + "</td>";
                markup = markup + "<td>" + this.Room_Type + "</td>";
                markup = markup + "<td>" + this.IP_Address + "</td>";
                markup = markup + "<td>" + this.Serial_Number + "</td>";
                markup = markup + "</tr>";
                $("#tbbody5").append(markup);
            });
            $("#aveuptime").remove();
            // Append progress bars
            $(".progress").append(
                "<div class='progress-bar bg-info' id='aveuptime' role='progressbar' style='max-height:30px;width:" +
                response.ave +   
                    "%' aria-valuenow='" +
                    response.ave+
                    "' aria-valuemin='" +
                    0 +
                    "' aria-valuemax='100'>" +
                    response.ave +
                    "%" +
                    "</div>"
              
            );
        }
        },
  
    });
}
function refreshDashboard(){
    $.ajax({
        type: "GET",
        url: "/refreshDashboard",
        data: {
        },
        success: function (response) {
            let markup = '';
            $(response.DeviceRoom).each(function(){
                if(!gcheckedRooms.includes(this.DeviceRoomID)){
                    markup += `<tr>
                                    <td>
                                        <div class="form-check form-checkbox-success mb-2">
                                            <input type="checkbox" class="form-check-input ckbox" name="roomID1" value=${this.DeviceRoomID}>
                                        </div>
                                    </td>
                                    <td style="whitespace:nowrap">${this.DeviceRoomName}</td>
                                </tr>`;
                }else{
                    markup += `<tr>
                    <td>
                        <div class="form-check form-checkbox-success mb-2">
                            <input type="checkbox" checked class="form-check-input ckbox" name="roomID1" value=${this.DeviceRoomID}>
                        </div>
                    </td>
                    <td style="whitespace:nowrap">${this.DeviceRoomName}</td>
                </tr>`;
                }
            });
            $('#roomBody').html(markup); 
        //  if(response.Error){
        //    // alert("No Account Access Assigned");
        //  }else{
        //     $("#basic-datatable1 tbody tr").remove();
        //     var RowID = 0;
        //     var uptime = 0;
        //     var count = 0;
        //     $(response.data).each(function () {
        //         var markup = "";
        //         RowID = RowID + 1;
        //         markup ="<tr class='text-center' style='color:black;white-space: nowrap;'> ";
        //         markup = markup + "<td class='text-center'>" + this.Uptime + "</td>";
        //         markup =  markup + "<td class='text-center'>" +  this.Incidents +  "</td>";
        //         markup = markup + "<td>" + this.Device_Name + "</td>";
        //         markup = markup + "<td>" + this.Manufacturer + "</td>";
        //         markup = markup + "<td>" + this.Room_Type + "</td>";
        //         markup = markup + "<td>" + this.IP_Address + "</td>";
        //         markup = markup + "<td>" + this.Serial_Number + "</td>";
        //         markup = markup + "</tr>";
        //         $("#roomBody").append(markup);
        //     });
        //     $("#aveuptime").remove();
        //     $(".progress").append(
        //         "<div class='progress-bar bg-info' id='aveuptime' role='progressbar' style='max-height:30px;width:" +
        //         response.ave +   
        //             "%' aria-valuenow='" +
        //             response.ave+
        //             "' aria-valuemin='" +
        //             0 +
        //             "' aria-valuemax='100'>" +
        //             response.ave +
        //             "%" +
        //             "</div>"
        //     );
        //     $('input[type="radio"][value="' + gMacAddress + '"]').prop("checked", true).change();

        // }
        },
    });
}
function triggerCheckboxChange() {
 
    $('input[type="checkbox"].ckbox:checked').each(function() {
        $(this).trigger('change');
        return false;
    });
}
function triggerTickets(){
    var Org = $("#regionOrgs").val();
    if(Org==''){
        $.ajax({
            url: "/refresh-notification",
            type: "GET",
            success: function(res) {
               $("#new_notif").empty();
               $("#res_notif").empty();
               $("#unres_notif").empty();
              // $("#roomBody tr").remove();
    
               $("#new_notif").append(res.NewNotif.new_count);
               $("#res_notif").append(res.ResolvedNotif.resolved_count);
               $("#unres_notif").append(res.UnresolvedNotif.unresolved_count);
           
            },
            error: function(xhr, status, error) {
                console.log(xhr,status,error)
	
		 $.toast({
                    heading: "Error",
                    text:"Session Expired!",
                    position: "top-right",
                    loaderBg: "white",
                    showHideTransition: "fade",
                    icon: "error",
                    hideAfter: 10000,
                });
		location.reload();
            }
        });
    }
  
}
function triggerUptimeDevice(){
    var initialDateRange = $("#up_date").val();
    $.ajax({
        type: "GET",
        url: "/uptime",
        data: {
            selectedValues,
            initialDateRange,
        },
        success: function (response) {
            
         if(response.Error){
           // alert("No Account Access Assigned");
         }else{
            $("#basic-datatable1 tbody tr").remove();
            var RowID = 0;
            var uptime = 0;
            var count = 0;
            $(response.data).each(function () {
                var markup = "";
                RowID = RowID + 1;
                markup ="<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                markup = markup + "<td class='text-center'>" + this.Uptime + "</td>";
                markup =  markup + "<td class='text-center'>" +  this.Incidents +  "</td>";
                markup = markup + "<td>" + this.Device_Name + "</td>";
                markup = markup + "<td>" + this.Manufacturer + "</td>";
                markup = markup + "<td>" + this.Room_Type + "</td>";
                markup = markup + "<td>" + this.IP_Address + "</td>";
                markup = markup + "<td>" + this.Serial_Number + "</td>";
                markup = markup + "</tr>";
                $("#tbbody5").append(markup);
            });
            $("#aveuptime").remove();
            // Append progress bars
            $(".progress").append(
                "<div class='progress-bar bg-info' id='aveuptime' role='progressbar' style='max-height:30px;width:" +
                response.ave +   
                    "%' aria-valuenow='" +
                    response.ave+
                    "' aria-valuemin='" +
                    0 +
                    "' aria-valuemax='100'>" +
                    response.ave +
                    "%" +
                    "</div>"
            );
            $('input[type="radio"][value="' + gMacAddress + '"]').prop("checked", true).change();

        }
        },
    });
}
