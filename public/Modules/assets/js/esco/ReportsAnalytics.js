var gCtr = 1;
var gUserId=$("#UserId").val();
var gRegion='';
var gOrganization='';
var gStatus = '';
var gy='';
var alertShown = true;
var gx='';
var gPrevVal='';
var gDevId='';
var gColumn='';
var gInputval='';
var gResetTheEnterPw = false;
//Global
function handleClick(x, y, PrevVal,DevId,Column) {
    gx=x;gy=y;gPrevVal=PrevVal;gDevId=DevId;gColumn=Column;
    var previousContent =  $("#td" + y).html();
    //console.log(previousContent);
    $('[id^="td"]').css('pointer-events', 'none');
    $("#TRHeaderRepAnal").css('pointer-events', 'none');
    gy=y;
    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    // Clear the cell and add the input field

    $("#td" + y).empty();
    $("#td" + y).append(`<input class='text-center' id="input${y}" value="${escapeHtml(PrevVal)}" type="text" style="border: none; background-color: transparent; outline: none; padding: 5px; width: 140px; height: 30px;">`);
    $('#input' + y).focus();
    $("#td" + y).css('pointer-events', 'auto');
   
    alertShown = false;
    $("#td" + y).click(function(event) {
        event.stopPropagation();
    });
    $("#input" + y).click(function(event) {
        $('#input' + y).focus();
        alertShown = false;
       // console.log(alertShown);
    });
    $(document).on('click', function(event) {
        $('#input' + y).blur(); // Remove focus from the input field
        if(alertShown == false){
        var modal = new bootstrap.Modal(document.getElementById('success-header-modal'));
            modal.show(); // Show the modal
            alertShown = true;
        }
    });
    $('.b-close').on('click', function() {
        $("#td" + y).empty();
        $("#td" + y).append(`<input class='text-center' id="input${y}" value="${escapeHtml(PrevVal)}" type="text" style="border: none; background-color: transparent; outline: none; padding: 5px; width: 140px; height: 30px;">`);
         var modal = new bootstrap.Modal(document.getElementById('success-header-modal'));
         modal.hide();
         alertShown = true;
         $("#TRHeaderRepAnal").css('pointer-events', 'auto');
         $('[id^="td"]').css('pointer-events', 'auto');
         $('[id^="input"]').remove();
         $("#td" + y).append(previousContent);
         $("#validationpw").val('');
         x='';
         y='';
         PrevVal='';
         DevId='';
         Column='';
         //gy='';
        // $('#input' + y).empty();
        // modalShown = false;
    });
   
}
$('#SaveField').on('click', function() {
    var Inputval= $('#input' + gy).val()
    var Organization = $('#OrganizationId').val();
    gInputval = Inputval;
    gOrganization = Organization;
   // console.log( Inputval,DevId,Column);
    if(gResetTheEnterPw==false){
        AskPassword();
    }else{
        $.ajax({
            url: "/UpdateField",
            type: "GET",
            data: {
                Inputval,gDevId,gColumn,
            },
            success: function (response) {
                $.toast({
                    heading: "Success",
                    text:response.validated,
                    position: "top-right",
                    loaderBg: "white",
                    showHideTransition: "fade",
                    icon: "success",
                    hideAfter: 6000,
                });
                $('[id^="td"]').css('pointer-events', 'auto');
                $('.b-close').click();
                if(Organization==''){
                    reloadReports();
                }else{
                location.reload(true);
                }
            },
        });
    }
});
    function reloadReports(){
        Region = gRegion;
        $.ajax({
            url: "/filter-region",
            type: "GET",
            data: {
                Region,
            },
            success: function (response) {
                if(response.AuthUser.usertype==0){
                    var RowID=0;
                    $('#basic-datatable tr').not(':first').remove();
                    $(response.report).each(function (){
                        RowID = RowID+1;
                        var markup = '';
                            markup = "";
                            markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                            markup = markup + `<td id='${'td' + RowID}'>${this.Device_Loc}</td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'> ${this.Device_Name}</td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'> ${this.Device_Desc}</td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'>${this.Room_Type}</td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'>${this.Manufacturer}</td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'> ${this.IP_Address} </td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'> ${this.Serial_Number} </td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'>${this.Mac_Address }</td>`;
                            if (this.Status == 'Online' || this.Status == 'OK'){//green
                                markup = markup + `<td id='${'td'+RowID}' > <span class='badge custom-success bg-success-lighten px-2 py-1'>${this.Status}</span></td>`;
                            }else{//red
                            markup = markup + `<td id='${'td'+RowID}'><span class='badge custom-danger bg-danger-lighten px-2 py-1'>${this.Status} </span></td>`;
                            }
                        markup = markup + "</tr>";
                        $("#tbbody1").append(markup);
                    });
                    }else{
                        var RowID=0;
                        $('#basic-datatable tr').not(':first').remove();
                        $(response.report).each(function (){
                            RowID = RowID+1;
                            var markup = '';
                                markup = "";
                                markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Loc}','${this.Device_Id}','Device_Loc')">${this.Device_Loc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Loc}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Name}','${this.Device_Id}','Device_Name')">${this.Device_Name === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Name}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Desc}','${this.Device_Id}','Device_Desc')">${this.Device_Desc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Desc}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Room_Type}','${this.Device_Id}','Room_Type')">${this.Room_Type === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Room_Type}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Manufacturer}','${this.Device_Id}','Manufacturer')">${this.Manufacturer === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Manufacturer}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.IP_Address}','${this.Device_Id}','IP_Address')">${this.IP_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.IP_Address}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Serial_Number}','${this.Device_Id}','Serial_Number')">${this.Serial_Number === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Serial_Number}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Mac_Address}','${this.Device_Id}','Mac_Address')">${this.Mac_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Mac_Address}</span> </td>`;
                                if (this.Status == 'Online' || this.Status == 'OK'){//green
                                    markup = markup + `<td id='${'td'+RowID}' > <span class='badge custom-success bg-success-lighten px-2 py-1'>${this.Status}</span></td>`;
                                }else{//red
                                markup = markup + `<td id='${'td'+RowID}'><span class='badge custom-danger bg-danger-lighten px-2 py-1'>${this.Status} </span></td>`;
                                }
                            markup = markup + "</tr>";
                            $("#tbbody1").append(markup);
                        });
                    }
                $('#OrganizationId option:not(:first)').remove();
                RowID=0;
                $(response.org).each(function (){
                    var markup = '';
                    RowID = RowID+1;
                    markup = markup + "<option style='border-radius: 0px;' value= '" + this.Company_Id + "' >"+this.Company_Name+ "-"+ this.Company_Address+"</option>";
                    $("#OrganizationId").append(markup);

                });

                $('#basic-datatable1 tr').not(':first').remove();
                RowID=0;
                $(response.DisplayZoho).each(function (){
                //   console.log(response.DisplayZoho);
                    RowID = RowID+1;
                    var markup = '';
                    markup = "";
                    if(RowID%2==1){
                        markup = "<tr class='table-active text-left' style='color:black;white-space: nowrap;'> ";                
                    }else{
                    markup = "<tr class='text-left' style='color:black;white-space: nowrap;'> ";
                    }
                    markup = markup + "<td>"+'Critical'+"</td>";
                    markup = markup + "<td>"+this.Ticket_Number+"</td>";
                    markup = markup + "<td>"+this.Device_Name+"</td>";
                    markup = markup + "<td>"+this.Subject+"</td>";
                    markup = markup + "<td>"+this.Log_Last_Online+"</td>";
                    markup = markup + "<td>"+this.created_at+"</td>";
                    markup = markup + "<td>"+this.Elapse_Time+"</td>";
                    if(this.updated_at==null){
                        markup = markup + "<td>"+" "+"</td>";
                    }else{
                    markup = markup + "<td>"+this.updated_at+"</td>";
                    }
                    if (this.Status != 'Open'){
                        markup = markup + "<td class='text-left'> <span class='badge custom-success bg-success-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }else if(this.Status == 'New'){
                        markup = markup + "<td class='text-left'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    else{
                    markup = markup + "<td class='text-left'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    markup = markup + "</tr>";
                    $("#tbbody2").append(markup);
                
                });
                
                $("#TicketStats").children().not(":first").remove();
                $.each(response.TicketStatus, function(index, item) {
                    $("#TicketStats").append("<option>"+item.Status+"</option>");
                });
            },
        });
        $('#tab1').click();
    }
    function AskPassword(){
            $("#Close-Modal").click();
            var modal = new bootstrap.Modal(document.getElementById('Modal-Enter-Pass'));
            modal.show(); // Show the modal  
    }
    $('#form-pwvalidation').on('submit', function(event) {
        event.preventDefault(); // Prevent the form from refreshing the page
        var formData = $(this).serialize(); // Serialize form data for submission
        Inputval=gInputval;
        Organization = gOrganization;
        $.ajax({
            url:'/pwvalidation', // The route where the form data will be submitted
            method: "POST",
            data: formData,
            success: function(response) {
                // Handle success response
                if(response.validated){
                    gResetTheEnterPw=true;
                    $.ajax({
                        url: "/UpdateField",
                        type: "GET",
                        data: {
                            Inputval,gDevId,gColumn,
                        },
                        success: function (response) {
                            $.toast({
                                heading: "Success",
                                text:response.validated,
                                position: "top-right",
                                loaderBg: "white",
                                showHideTransition: "fade",
                                icon: "success",
                                hideAfter: 6000,
                            });
                            $('[id^="td"]').css('pointer-events', 'auto');
                            $('.b-close').click();
                            if(Organization==''){
                                reloadReports();
                            }else{
                              location.reload(true);
                            }
                        },
                    });
                }else{
                    $.toast({
                        heading: "Warning",
                        text:response.notvalidated,
                        position: "top-right",
                        loaderBg: "white",
                        showHideTransition: "fade",
                        icon: "warning",
                        hideAfter: 6000,
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert('An error occurred during password validation.');
            }
        });
    });
   
//end global

$(document).ready(function () {
    setTimeout(function() {
        gResetTheEnterPw = false;
        setTimeout(arguments.callee, 300000);
    }, 300000);
    AlertNotif();
    var currentDate = new Date();
    currentDate.setDate(1);
    
    // Formatting the start of the month
    var formattedStartMonth = ('0' + (currentDate.getMonth() + 1)).slice(-2);
    var formattedStartDate = ('0' + currentDate.getDate()).slice(-2); // Ensure two digits for day
    var formattedStartDateString = formattedStartMonth + '/' + formattedStartDate + '/' + currentDate.getFullYear();
    
    // Calculating end of the month
    var endOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
    
    // Formatting the end of the month
    var formattedEndMonth = ('0' + (endOfMonth.getMonth() + 1)).slice(-2); // Ensure two digits for month
    var formattedEndDate = ('0' + endOfMonth.getDate()).slice(-2); // Ensure two digits for day
    var formattedEndDateString = formattedEndMonth + '/' + formattedEndDate + '/' + endOfMonth.getFullYear();
    
    $('#TDates').val(formattedStartDateString+' - '+formattedEndDateString);
    /////////////////////////////////////////creation of select date in reports anal alert notification
    $("#tab1").click(function(){
        $("#exportBtn1").removeClass("hide-item");
        $("#exportBtn2").addClass("hide-item");
    });
    $("#tab2").click(function(){
        $("#exportBtn1").addClass("hide-item");
        $("#exportBtn2").removeClass("hide-item");
    });
    $("#TDates").change(function(){
        var AlertDateSelected =  $("#TDates").val();
        var org = $("#OrganizationId").val();
        // var parts = formattedDate.split('/');
        // var AlertDateSelected = parts[0] + '-' + parts[1] + '-' + parts[2];
        $.ajax({
            url: "/ReportsChangeDate",
            type: "GET",
            data: {
                gRegion,gUserId,gOrganization,gStatus,AlertDateSelected,org,
            },
            success: function (response) {
                if(response.TicketsByDate){
                    var RowID=0;
                    $('#basic-datatable1 tbody tr').remove();
                $.each(response.TicketsByDate, function(index, item) {
                 
                        RowID = RowID+1;
                        var markup = '';
                        markup = "";
                        if(RowID%2==1){
                            markup = "<tr class='table-active text-left' style='color:black;white-space: nowrap;'> ";                
                        }else{
                        markup = "<tr class='text-left' style='color:black;white-space: nowrap;'> ";
                        }
                        markup = markup + "<td>"+'Critical'+"</td>";
                        markup = markup + "<td>"+item.Ticket_Number+"</td>";
                        markup = markup + "<td>"+item.Device_Name+"</td>";
                        markup = markup + "<td>"+item.Subject+"</td>";
                        markup = markup + "<td>"+item.Log_Last_Online+"</td>";
                        markup = markup + "<td>"+item.created_at+"</td>";
                        markup = markup + "<td>"+item.Elapse_Time+"</td>";
                        if(this.updated_at==null){
                            markup = markup + "<td>"+" "+"</td>";
                        }else{
                        markup = markup + "<td>"+this.updated_at+"</td>";
                        }
                    // console.log(this.Status);
                        if (this.Status != 'Open'){
                            markup = markup + "<td class='text-left'> <span class='badge custom-success bg-success-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                        }else if(this.Status == 'New'){
                            markup = markup + "<td class='text-left'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                        }
                        else{
                        markup = markup + "<td class='text-left'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                        }
                        markup = markup + "</tr>";
                        $("#tbbody2").append(markup);
                    
                    });
                }else{
                    alert("No value on ticket dates selected")
                }
            }
        }); 
    });
    $("#TicketStats").change(function(){
        var status = $("#TicketStats").val();
        var org = $("#OrganizationId").val();
    
        gStatus=status;
        $.ajax({
            type: "get",
            url: "/ChangeTicketStatus",
            data: {
                gRegion,gOrganization,gUserId,status,org
            },
            success: function (response) {
                if(response.DevicesOfUser){
                    var RowID=0;
                    $('#basic-datatable1 tbody tr').remove();
                    $.each(response.DevicesOfUser, function(index, item) {
                            RowID = RowID+1;
                            var markup = '';
                            markup = "";
                            if(RowID%2==1){
                                markup = "<tr class='table-active text-left' style='color:black;white-space: nowrap;'> ";                
                            }else{
                            markup = "<tr class='text-left' style='color:black;white-space: nowrap;'> ";
                            }
                            markup = markup + "<td>"+'Critical'+"</td>";
                            markup = markup + "<td>"+item.Ticket_Number+"</td>";
                            markup = markup + "<td>"+item.Device_Name+"</td>";
                            markup = markup + "<td>"+item.Subject+"</td>";
                            markup = markup + "<td>"+item.Log_Last_Online+"</td>";
                            markup = markup + "<td>"+item.created_at+"</td>";
                            markup = markup + "<td>"+item.Elapse_Time+"</td>";
                            if(this.updated_at==null){
                              
                                markup = markup + "<td>"+" "+"</td>";
                            }else{
                            markup = markup + "<td>"+this.updated_at+"</td>";
                            }
                        // console.log(this.Status);
                            if (this.Status != 'Open'){
                                markup = markup + "<td class='text-left'> <span class='badge custom-success bg-success-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                            }else if(this.Status == 'New'){
                                markup = markup + "<td class='text-left'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                            }
                            else{
                            markup = markup + "<td class='text-left'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                            }
                            markup = markup + "</tr>";
                            $("#tbbody2").append(markup);
                     });
                }else{
                    alert("no devices found");
                }
            },

        });   
    }); 
    $("#DevSearch").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $.ajax({
            type: "GET",
            url: "/search",
            data: {
                 value,gRegion,gOrganization,gUserId,
            },
            success: function (response) {
                var RowID=0;
                    $('#basic-datatable tbody tr').remove();
                    $(response.results).each(function (){
                    RowID = RowID+1;
                    var markup = '';
                    markup = "";
                    if(RowID%2==1){
                        markup = "<tr class='table-active text-center' style='color:black;white-space: nowrap;'> ";                
                    }else{
                    markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                    }
                    markup = markup + "<td>"+this.Device_Loc+"</td>";
                    markup = markup + "<td>"+this.Device_Name+"</td>";
                    markup = markup + "<td>"+this.Device_Desc+"</td>";
                    markup = markup + "<td>"+this.Room_Type+"</td>";
                    markup = markup + "<td>"+this.Manufacturer+"</td>";
                    markup = markup + "<td>"+this.IP_Address+"</td>";
                    markup = markup + "<td>"+this.Serial_Number+"</td>";
                    markup = markup + "<td>"+this.Mac_Address+"</td>";
                    if (this.Status == 'Online'){
                        markup = markup + "<td class='text-center'> <span class='badge custom-success bg-success-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }else if(this.Status == 'OK'){
                        markup = markup + "<td class='text-center'> <span class='badge custom-success bg-success-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    else{
                    markup = markup + "<td class='text-center'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    markup = markup + "</tr>";
                    $("#tbbody1").append(markup);
                   
                });
           
            },
            });   
    });
    $("#exportBtn1").click(function(){
        $("#basic-datatable").table2csv()
    });
    $("#exportBtn2").click(function(){
        $("#basic-datatable1").table2csv()
    });
    $("#DevName").on("keyup", function () {
        var value = $(this).val();
        var thirdColumnText = $(this).find('td:nth-child(3)').text().toLowerCase();
        $(this).toggle(thirdColumnText.indexOf(value) > 0)
    });   
    $("#RegionId").change(function(){ //Populate Reports Analytics data
        $("#alertnotifs").removeClass('disable-standard');
        var Region = $(this).val();
        gRegion = Region;
        $.ajax({
            url: "/filter-region",
            type: "GET",
            data: {
                Region,
            },
            success: function (response) {
                if(response.AuthUser.usertype==0){
                    var RowID=0;
                    $('#basic-datatable tr').not(':first').remove();
                    $(response.report).each(function (){
                        RowID = RowID+1;
                        var markup = '';
                            markup = "";
                            markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                            markup = markup + `<td id='${'td' + RowID}'>${this.Device_Loc}</td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'> ${this.Device_Name}</td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'> ${this.Device_Desc}</td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'>${this.Room_Type}</td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'>${this.Manufacturer}</td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'> ${this.IP_Address} </td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'> ${this.Serial_Number} </td>`;
                            RowID = RowID+1;
                            markup = markup + `<td id='${'td' + RowID}'>${this.Mac_Address }</td>`;
                            if (this.Status == 'Online' || this.Status == 'OK'){//green
                                markup = markup + `<td id='${'td'+RowID}' > <span class='badge custom-success bg-success-lighten px-2 py-1'>${this.Status}</span></td>`;
                            }else{//red
                            markup = markup + `<td id='${'td'+RowID}'><span class='badge custom-danger bg-danger-lighten px-2 py-1'>${this.Status} </span></td>`;
                            }
                        markup = markup + "</tr>";
                        $("#tbbody1").append(markup);
                    });
                    }else{
                        var RowID=0;
                        $('#basic-datatable tr').not(':first').remove();
                        $(response.report).each(function (){
                            RowID = RowID+1;
                            var markup = '';
                                markup = "";
                                markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Loc}','${this.Device_Id}','Device_Loc')">${this.Device_Loc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Loc}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Name}','${this.Device_Id}','Device_Name')">${this.Device_Name === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Name}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Desc}','${this.Device_Id}','Device_Desc')">${this.Device_Desc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Desc}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Room_Type}','${this.Device_Id}','Room_Type')">${this.Room_Type === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Room_Type}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Manufacturer}','${this.Device_Id}','Manufacturer')">${this.Manufacturer === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Manufacturer}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.IP_Address}','${this.Device_Id}','IP_Address')">${this.IP_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.IP_Address}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Serial_Number}','${this.Device_Id}','Serial_Number')">${this.Serial_Number === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Serial_Number}</span> </td>`;
                                RowID = RowID+1;
                                markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Mac_Address}','${this.Device_Id}','Mac_Address')">${this.Mac_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Mac_Address}</span> </td>`;
                                if (this.Status == 'Online' || this.Status == 'OK'){//green
                                    markup = markup + `<td id='${'td'+RowID}' > <span class='badge custom-success bg-success-lighten px-2 py-1'>${this.Status}</span></td>`;
                                }else{//red
                                markup = markup + `<td id='${'td'+RowID}'><span class='badge custom-danger bg-danger-lighten px-2 py-1'>${this.Status} </span></td>`;
                                }
                            markup = markup + "</tr>";
                            $("#tbbody1").append(markup);
                        });
                    }
                $('#OrganizationId option:not(:first)').remove();
                RowID=0;
                $(response.org).each(function (){
                    var markup = '';
                    RowID = RowID+1;
                    markup = markup + "<option style='border-radius: 0px;' value= '" + this.Company_Id + "' >"+this.Company_Name+ "-"+ this.Company_Address+"</option>";
                    $("#OrganizationId").append(markup);

                });

                $("#ReportsContent").removeClass('hide-item'); //show all

                $('#basic-datatable1 tr').not(':first').remove();
                RowID=0;
                $(response.DisplayZoho).each(function (){
                 //   console.log(response.DisplayZoho);
                    RowID = RowID+1;
                    var markup = '';
                    markup = "";
                    if(RowID%2==1){
                        markup = "<tr class='table-active text-left' style='color:black;white-space: nowrap;'> ";                
                    }else{
                    markup = "<tr class='text-left' style='color:black;white-space: nowrap;'> ";
                    }
                    markup = markup + "<td>"+'Critical'+"</td>";
                    markup = markup + "<td>"+this.Ticket_Number+"</td>";
                    markup = markup + "<td>"+this.Device_Name+"</td>";
                    markup = markup + "<td>"+this.Subject+"</td>";
                    markup = markup + "<td>"+this.Log_Last_Online+"</td>";
                    markup = markup + "<td>"+this.created_at+"</td>";
                    markup = markup + "<td>"+this.Elapse_Time+"</td>";
                    if(this.updated_at==null){
                        markup = markup + "<td>"+" "+"</td>";
                    }else{
                    markup = markup + "<td>"+this.updated_at+"</td>";
                    }
                    if (this.Status != 'Open'){
                        markup = markup + "<td class='text-left'> <span class='badge custom-success bg-success-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }else if(this.Status == 'New'){
                        markup = markup + "<td class='text-left'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    else{
                    markup = markup + "<td class='text-left'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    markup = markup + "</tr>";
                    $("#tbbody2").append(markup);
                
                });
                
                $("#TicketStats").children().not(":first").remove();
                $.each(response.TicketStatus, function(index, item) {
                    $("#TicketStats").append("<option>"+item.Status+"</option>");
                });
            },
        });
        $('#tab1').click();
    });

    $("#OrganizationId").change(function(){
        var Organization = $(this).val();
        gOrganization =Organization;
        $.ajax({
            url: "/filter-Organization",
            type: "GET",
            data: {
                gRegion,gUserId,Organization
            },
            success: function (response) {
          

                if(response.AuthUser.usertype==0){//not admin

                    var RowID=0;
                    $('#basic-datatable tbody tr').remove();
                    $(response.Devices).each(function (){
                        RowID = RowID+1;
                        var markup = '';
                        markup = "";
                        markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                        markup = markup + `<td id='${'td' + RowID}'> ${this.Device_Loc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Loc}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> ${this.Device_Name === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Name}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'>${this.Device_Desc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Desc}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> ${this.Room_Type === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Room_Type}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> ${this.Manufacturer === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Manufacturer}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> ${this.IP_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.IP_Address}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> ${this.Serial_Number === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Serial_Number}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> ${this.Mac_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Mac_Address}</span> </td>`;
                        if (this.Status == 'Online' || this.Status == 'OK'){//green
                            markup = markup + `<td id='${'td'+RowID}' > <span class='badge custom-success bg-success-lighten px-2 py-1'>${this.Status}</span></td>`;
                        }else{//red
                        markup = markup + `<td id='${'td'+RowID}'><span class='badge custom-danger bg-danger-lighten px-2 py-1'>${this.Status} </span></td>`;
                        }
                        markup = markup + "</tr>";
                        $("#tbbody1").append(markup);
                    });
                }else{
                    var RowID=0;
                    $('#basic-datatable tbody tr').remove();
                    $(response.Devices).each(function (){
                        RowID = RowID+1;
                        var markup = '';
                        markup = "";
                        markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                        markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Loc}','${this.Device_Id}','Device_Loc')">${this.Device_Loc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Loc}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Name}','${this.Device_Id}','Device_Name')">${this.Device_Name === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Name}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Desc}','${this.Device_Id}','Device_Desc')">${this.Device_Desc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Desc}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Room_Type}','${this.Device_Id}','Room_Type')">${this.Room_Type === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Room_Type}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Manufacturer}','${this.Device_Id}','Manufacturer')">${this.Manufacturer === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Manufacturer}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.IP_Address}','${this.Device_Id}','IP_Address')">${this.IP_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.IP_Address}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Serial_Number}','${this.Device_Id}','Serial_Number')">${this.Serial_Number === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Serial_Number}</span> </td>`;
                        RowID = RowID+1;
                        markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Mac_Address}','${this.Device_Id}','Mac_Address')">${this.Mac_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Mac_Address}</span> </td>`;
                        if (this.Status == 'Online' || this.Status == 'OK'){//green
                            markup = markup + `<td id='${'td'+RowID}' > <span class='badge custom-success bg-success-lighten px-2 py-1'>${this.Status}</span></td>`;
                        }else{//red
                        markup = markup + `<td id='${'td'+RowID}'><span class='badge custom-danger bg-danger-lighten px-2 py-1'>${this.Status} </span></td>`;
                        }
                        markup = markup + "</tr>";
                        $("#tbbody1").append(markup);
                    });
                }
                $("#TicketStats").children().not(":first").remove();
                $.each(response.DropdownStatus, function(index, item) {
                    $("#TicketStats").append("<option>"+item.Status+"</option>");
                });
                
                var RowID=0;
                $('#basic-datatable1 tbody tr').remove();
                $(response.ZohoTickets).each(function (){
                    RowID = RowID+1;
                    var markup = '';
                    markup = "";
                    markup = "<tr class='text-left' style='color:black;white-space: nowrap;'> ";
                    markup = markup + "<td>"+'Critical'+"</td>";
                    markup = markup + "<td>"+this.Ticket_Number+"</td>";
                    markup = markup + "<td>"+this.Device_Name+"</td>";
                    markup = markup + "<td>"+this.Subject+"</td>";
                    markup = markup + "<td>"+this.Log_Last_Online+"</td>";
                    markup = markup + "<td>"+this.created_at+"</td>";
                    markup = markup + "<td>"+this.Elapse_Time+"</td>";
                    if(this.updated_at==null){
                        markup = markup + "<td>"+" "+"</td>";
                    }else{
                    markup = markup + "<td>"+this.updated_at+"</td>";
                    }
                    if (this.Status != 'Open'){
                        markup = markup + "<td class='text-left'> <span class='badge custom-success bg-success-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }else if(this.Status == 'New'){
                        markup = markup + "<td class='text-left'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    else{
                    markup = markup + "<td class='text-left'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    markup = markup + "</tr>";
                    $("#tbbody2").append(markup);
                });
            },
        });
    });
    $("#th1").click(function(){
        var val = $(this).data('value');
    if ($("#tbbody1").length && $("#tbbody1").find("tr").length != 0) {
        if ($('#DL0').hasClass('dripicons-chevron-down')) {
            $("#DL0").removeClass('dripicons-chevron-down');
            $("#DL0").addClass('dripicons-chevron-up');
        }else{
            $("#DL0").removeClass('dripicons-chevron-up');
            $("#DL0").addClass('dripicons-chevron-down');
        }
         sort(val);
    }
    });
    $("#th2").click(function(){
        var val = $(this).data('value');
        if ($("#tbbody1").length && $("#tbbody1").find("tr").length != 0) {

        if ($('#DL1').hasClass('dripicons-chevron-down')) {
                $("#DL1").removeClass('dripicons-chevron-down');
                $("#DL1").addClass('dripicons-chevron-up');
            }else{
                $("#DL1").removeClass('dripicons-chevron-up');
                $("#DL1").addClass('dripicons-chevron-down');
            }
        sort(val);
        }
    });
    $("#th3").click(function(){
        var val = $(this).data('value');
        if ($("#tbbody1").length && $("#tbbody1").find("tr").length != 0) {

        if ($('#DL2').hasClass('dripicons-chevron-down')) {
            $("#DL2").removeClass('dripicons-chevron-down');
            $("#DL2").addClass('dripicons-chevron-up');
        }else{
            $("#DL2").removeClass('dripicons-chevron-up');
            $("#DL2").addClass('dripicons-chevron-down');
        }
        sort(val);
    }
    });
    $("#th4").click(function(){
        var val = $(this).data('value');
        if ($("#tbbody1").length && $("#tbbody1").find("tr").length != 0) {

        if ($('#DL3').hasClass('dripicons-chevron-down')) {
            $("#DL3").removeClass('dripicons-chevron-down');
            $("#DL3").addClass('dripicons-chevron-up');
        }else{
            $("#DL3").removeClass('dripicons-chevron-up');
            $("#DL3").addClass('dripicons-chevron-down');
        }
        sort(val);
    }
    });
    $("#th5").click(function(){
        var val = $(this).data('value');
        if ($("#tbbody1").length && $("#tbbody1").find("tr").length != 0) {

        if ($('#DL4').hasClass('dripicons-chevron-down')) {
            $("#DL4").removeClass('dripicons-chevron-down');
            $("#DL4").addClass('dripicons-chevron-up');
        }else{
            $("#DL4").removeClass('dripicons-chevron-up');
            $("#DL4").addClass('dripicons-chevron-down');
        }
        sort(val);
    }
    });
    $("#th6").click(function(){
        var val = $(this).data('value');
        if ($("#tbbody1").length && $("#tbbody1").find("tr").length != 0) {

        if ($('#DL5').hasClass('dripicons-chevron-down')) {
            $("#DL5").removeClass('dripicons-chevron-down');
            $("#DL5").addClass('dripicons-chevron-up');
        }else{
            $("#DL5").removeClass('dripicons-chevron-up');
            $("#DL5").addClass('dripicons-chevron-down');
        }
        sort(val);
    }
    });
    $("#th7").click(function(){
        var val = $(this).data('value');
        if ($("#tbbody1").length && $("#tbbody1").find("tr").length != 0) {

        if ($('#DL6').hasClass('dripicons-chevron-down')) {
            $("#DL6").removeClass('dripicons-chevron-down');
            $("#DL6").addClass('dripicons-chevron-up');
        }else{
            $("#DL6").removeClass('dripicons-chevron-up');
            $("#DL6").addClass('dripicons-chevron-down');
        }
        sort(val);
    }
    });
    $("#th8").click(function(){
        var val = $(this).data('value');
        if ($("#tbbody1").length && $("#tbbody1").find("tr").length != 0) {

        if ($('#DL7').hasClass('dripicons-chevron-down')) {
            $("#DL7").removeClass('dripicons-chevron-down');
            $("#DL7").addClass('dripicons-chevron-up');
        }else{
            $("#DL7").removeClass('dripicons-chevron-up');
            $("#DL7").addClass('dripicons-chevron-down');
        }
        sort(val);
    }
    });
    $("#th9").click(function(){
        var val = $(this).data('value');
        if ($("#tbbody1").length && $("#tbbody1").find("tr").length != 0) {

        if ($('#DL8').hasClass('dripicons-chevron-down')) {
            $("#DL8").removeClass('dripicons-chevron-down');
            $("#DL8").addClass('dripicons-chevron-up');
        }else{
            $("#DL8").removeClass('dripicons-chevron-up');
            $("#DL8").addClass('dripicons-chevron-down');
        }
        sort(val);
    }
    });
});
function AlertNotif(){//alert notification display
    $.ajax({
    type: "GET",
    url: "/AlertNotification",
    data: {
       
    },
    success: function (response) {
        //this updates the date. dont remove
        $("#DevStats").removeClass('disable-standard');
    },
    });   
}

function sort(val){

    $.ajax({
        url: "/sort",
        type: "GET",
        data: {
            val,gCtr,gOrganization,gUserId,gRegion,
        },
        success: function (response) {

            if(response.AuthUser.usertype==0){//not admin
                var RowID=0;
                if(gCtr==1){
                if (response.asc != null) {
                    gCtr=0;
                    $('#basic-datatable tbody tr').remove();
                    $(response.asc).each(function (){
                        //console.log(response.asc);
                    RowID = RowID+1;
                    var markup = '';
                    markup = "";
                    markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                    markup = markup + `<td id='${'td' + RowID}'> ${this.Device_Loc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Loc}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'>${this.Device_Name === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Name}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> ${this.Device_Desc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Desc}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> ${this.Room_Type === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Room_Type}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> ${this.Manufacturer === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Manufacturer}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'>${this.IP_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.IP_Address}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> ${this.Serial_Number === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Serial_Number}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> ${this.Mac_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Mac_Address}</span> </td>`;
                    if (this.Status == 'Online' || this.Status == 'OK'){//green
                        markup = markup + `<td id='${'td'+RowID}' > <span class='badge custom-success bg-success-lighten px-2 py-1'>${this.Status}</span></td>`;
                    }else{//red
                    markup = markup + `<td id='${'td'+RowID}'><span class='badge custom-danger bg-danger-lighten px-2 py-1'>${this.Status} </span></td>`;
                    }
                    markup = markup + "</tr>";
                    $("#tbbody1").append(markup);
                  //  console.log(`${this.Device_Loc}`);
                });
                }}
                if(gCtr!=1){
                if (response.desc != null){
                    gCtr=1;
                    $('#basic-datatable tbody tr').remove();
                    $(response.desc).each(function (){
                    RowID = RowID+1;
                    var markup = '';
                    markup = "";
                    markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                    markup = markup + `<td id='${'td' + RowID}'> ${this.Device_Loc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Loc}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> ${this.Device_Name === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Name}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'>${this.Device_Desc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Desc}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> ${this.Room_Type === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Room_Type}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> ${this.Manufacturer === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Manufacturer}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'>${this.IP_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.IP_Address}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> ${this.Serial_Number === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Serial_Number}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> ${this.Mac_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Mac_Address}</span> </td>`;
                    if (this.Status == 'Online' || this.Status == 'OK'){//green
                        markup = markup + `<td id='${'td'+RowID}' > <span class='badge custom-success bg-success-lighten px-2 py-1'>${this.Status}</span></td>`;
                    }else{//red
                    markup = markup + `<td id='${'td'+RowID}'><span class='badge custom-danger bg-danger-lighten px-2 py-1'>${this.Status} </span></td>`;
                    }
                    markup = markup + "</tr>";
                    $("#tbbody1").append(markup);
                
                });
            
                }} else {
                    //alert("Error Response");
                }
            }else{//admin
                var RowID=0;
                if(gCtr==1){
                if (response.asc != null) {
                    gCtr=0;
                    $('#basic-datatable tbody tr').remove();
                    $(response.asc).each(function (){
                        //console.log(response.asc);
                    RowID = RowID+1;
                    var markup = '';
                    markup = "";
                    markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Loc}','${this.Device_Id}','Device_Loc')">${this.Device_Loc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Loc}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Name}','${this.Device_Id}','Device_Name')">${this.Device_Name === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Name}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Desc}','${this.Device_Id}','Device_Desc')">${this.Device_Desc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Desc}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Room_Type}','${this.Device_Id}','Room_Type')">${this.Room_Type === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Room_Type}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Manufacturer}','${this.Device_Id}','Manufacturer')">${this.Manufacturer === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Manufacturer}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.IP_Address}','${this.Device_Id}','IP_Address')">${this.IP_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.IP_Address}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Serial_Number}','${this.Device_Id}','Serial_Number')">${this.Serial_Number === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Serial_Number}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Mac_Address}','${this.Device_Id}','Mac_Address')">${this.Mac_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Mac_Address}</span> </td>`;
                    if (this.Status == 'Online' || this.Status == 'OK'){//green
                        markup = markup + `<td id='${'td'+RowID}' > <span class='badge custom-success bg-success-lighten px-2 py-1'>${this.Status}</span></td>`;
                    }else{//red
                    markup = markup + `<td id='${'td'+RowID}'><span class='badge custom-danger bg-danger-lighten px-2 py-1'>${this.Status} </span></td>`;
                    }
                    markup = markup + "</tr>";
                    $("#tbbody1").append(markup);
                  //  console.log(`${this.Device_Loc}`);
                });
                }}
                if(gCtr!=1){
                if (response.desc != null){
                    gCtr=1;
                    $('#basic-datatable tbody tr').remove();
                    $(response.desc).each(function (){
                    RowID = RowID+1;
                    var markup = '';
                    markup = "";
                    markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Loc}','${this.Device_Id}','Device_Loc')">${this.Device_Loc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Loc}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Name}','${this.Device_Id}','Device_Name')">${this.Device_Name === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Name}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Device_Desc}','${this.Device_Id}','Device_Desc')">${this.Device_Desc === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Device_Desc}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Room_Type}','${this.Device_Id}','Room_Type')">${this.Room_Type === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Room_Type}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Manufacturer}','${this.Device_Id}','Manufacturer')">${this.Manufacturer === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Manufacturer}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.IP_Address}','${this.Device_Id}','IP_Address')">${this.IP_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.IP_Address}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Serial_Number}','${this.Device_Id}','Serial_Number')">${this.Serial_Number === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Serial_Number}</span> </td>`;
                    RowID = RowID+1;
                    markup = markup + `<td id='${'td' + RowID}'> <span onclick="handleClick('${'Cell' + RowID}', '${RowID}', '${this.Mac_Address}','${this.Device_Id}','Mac_Address')">${this.Mac_Address === '' ? '<span style=opacity:.0001;>Edit here</span>' : this.Mac_Address}</span> </td>`;
                    if (this.Status == 'Online' || this.Status == 'OK'){//green
                        markup = markup + `<td id='${'td'+RowID}' > <span class='badge custom-success bg-success-lighten px-2 py-1'>${this.Status}</span></td>`;
                    }else{//red
                    markup = markup + `<td id='${'td'+RowID}'><span class='badge custom-danger bg-danger-lighten px-2 py-1'>${this.Status} </span></td>`;
                    }
                    markup = markup + "</tr>";
                    $("#tbbody1").append(markup);
                
                });
            
                }} else {
                    //alert("Error Response");
                }
            }
            
             
        //    $('#OrganizationId option:not(:first)').remove();
            RowID=0;
            $(response.Org).each(function (){
                var markup = '';
                RowID = RowID+1;
                markup = markup + "<option style='border-radius: 0px;' value= '" + this.Company_Name + "' >"+this.Company_Name+"</option>";
                $("#OrganizationId").append(markup);
            });
        },
        });
}
