var gCtr = 1;
var gUserId=$("#UserId").val();
var gRegion='';
var gOrganization='';
$(document).ready(function () {
    $("#DevSearch").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        console.log(value,gRegion,gOrganization,'r',gUserId);
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
                    markup = markup + "<td>"+this.Serial_Number+"</td>";
                    markup = markup + "<td>"+this.Mac_Address+"</td>";
                    if (this.Status == 'Online'){
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
        $("#basic-datatable").table2excel({
            exclude: ".noExl", // exclude any element with the class 'noExl'
            name: "Reports",
            filename: "excel-export",
            fileext: ".xls",
            exclude_img: true,
            exclude_links: true,
            exclude_inputs: true
        });
    });
    $("#exportBtn2").click(function(){
        $("#basic-datatable1").table2excel({
            exclude: ".noExl", // exclude any element with the class 'noExl'
            name: "Reports",
            filename: "excel-export",
            fileext: ".xls",
            exclude_img: true,
            exclude_links: true,
            exclude_inputs: true
        });
    });
    $("#DevName").on("keyup", function () {
        var value = $(this).val();
        var thirdColumnText = $(this).find('td:nth-child(3)').text().toLowerCase();
        $(this).toggle(thirdColumnText.indexOf(value) > 0)
    });    
    $("#RegionId").change(function(){
       
        $("#DevStats").removeClass('disable-standard');
        $("#alertnotifs").removeClass('disable-standard');
        var Region = $(this).val();
        gRegion = Region;
    $.ajax({
        url: "/filter-region",
        type: "GET",
        data: {
            Region,gUserId,
        },
        success: function (response) {

            var RowID=0;
            if (response.data) {
                $('#basic-datatable tbody tr').remove();
                $(response.data).each(function (){
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
                markup = markup + "<td>"+this.Serial_Number+"</td>";
                markup = markup + "<td>"+this.Mac_Address+"</td>";

                if (this.Status == 'Online'){
                    markup = markup + "<td class='text-center'> <span class='badge custom-success bg-success-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                }
                else{
                markup = markup + "<td class='text-center'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                }
                markup = markup + "</tr>";
                $("#tbbody1").append(markup);
            });

            } else {
                alert("Error Response");
            }

            $('#OrganizationId option:not(:first)').remove();
            RowID=0;
            $(response.Org).each(function (){
                var markup = '';
                RowID = RowID+1;
                markup = markup + "<option style='border-radius: 0px;' value= '" + this.Company_Id + "' >"+this.Company_Name+ "-"+ this.Company_Address+"</option>";
                $("#OrganizationId").append(markup);

            });
            
        },
        });
        //AlertNotif();
    });
    $("#OrganizationId").change(function(){
        var Organization = $(this).val();
        gOrganization =Organization;
        $.ajax({
            url: "/filter-region",
            type: "GET",
            data: {
                gRegion,gUserId,Organization
            },
            success: function (response) {
          
                var RowID=0;
                if (response.RegionOrg) {
                    $('#basic-datatable tbody tr').remove();
                    $(response.RegionOrg).each(function (){
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
                    markup = markup + "<td>"+this.Serial_Number+"</td>";
                    markup = markup + "<td>"+this.Mac_Address+"</td>";
    
                    if (this.Status == 'Online'){
                        markup = markup + "<td class='text-center'> <span class='badge custom-success bg-success-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    else{
                    markup = markup + "<td class='text-center'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    markup = markup + "</tr>";
                    $("#tbbody1").append(markup);
                });
    
                } else {
                    alert("Error Response");
                }
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
});
function DevStats(){
    $("#exportBtn2").addClass("hide-item");
    $("#exportBtn1").removeClass("hide-item");
}
function AlertNotif(){
 $("#exportBtn1").addClass("hide-item");
 $("#exportBtn2").removeClass("hide-item");
 $.ajax({
    type: "GET",
    url: "/AlertNotification",
    data: {
        gRegion,gOrganization,gUserId,
    },
    success: function (response) {

        var RowID=0;
            $('#basic-datatable1 tbody tr').remove();
            $(response.AlertNotif).each(function (){
            RowID = RowID+1;
            var markup = '';
            markup = "";
            if(RowID%2==1){
                markup = "<tr class='table-active text-left' style='color:black;white-space: nowrap;'> ";                
            }else{
            markup = "<tr class='text-left' style='color:black;white-space: nowrap;'> ";
            }
            markup = markup + "<td>"+'Critical'+"</td>";
            markup = markup + "<td>"+this.Device_Name+"</td>";
            markup = markup + "<td>"+this.Subject+"</td>";
            markup = markup + "<td>"+this.Log_Last_Online+"</td>";
            markup = markup + "<td>"+this.created_at+"</td>";
            markup = markup + "<td>"+this.Elapse_Time+"</td>";
            if (this.Status == 'Online'){
                markup = markup + "<td class='text-left'> <span class='badge custom-success bg-success-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
            }
            else{
            markup = markup + "<td class='text-left'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
            }
            markup = markup + "</tr>";
            $("#tbbody2").append(markup);
           
        });
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

            var RowID=0;
                if(gCtr==1){
                if (response.asc != null) {
                    gCtr=0;
                    $('#basic-datatable tbody tr').remove();
                    $(response.asc).each(function (){
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
                    markup = markup + "<td>"+this.Serial_Number+"</td>";
                    markup = markup + "<td>"+this.Mac_Address+"</td>";

                    if (this.Status == 'Online'){
                        markup = markup + "<td class='text-center'> <span class='badge custom-success bg-success-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    else{
                    markup = markup + "<td class='text-center'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    markup = markup + "</tr>";
                    $("#tbbody1").append(markup);
                
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
                    markup = markup + "<td>"+this.Serial_Number+"</td>";
                    markup = markup + "<td>"+this.Mac_Address+"</td>";

                    if (this.Status == 'Online'){
                        markup = markup + "<td class='text-center'> <span class='badge custom-success bg-success-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    else{
                    markup = markup + "<td class='text-center'> <span class='badge custom-danger bg-danger-lighten px-2 py-1'>"+this.Status+"</span>"+"</td>";
                    }
                    markup = markup + "</tr>";
                    $("#tbbody1").append(markup);
                
                });
            
                }} else {
                    //alert("Error Response");
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
