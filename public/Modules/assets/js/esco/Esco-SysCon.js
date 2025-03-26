$(document).ready(function () {
    $('.hide').hide();
    // InitialState()
    //<------This is the default in Javascript, dropdown search,Datepicker---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
    $( ".btn-close" ).on( "click", function() {
        $('form').each(function() {
            this.reset();
          });
    });
    $( "#AddSysConBtn" ).on( "click", function() {
        var iname = $("#AddName").val();
        var idescription = $("#AddDescription").val();
        var ivalue = $("#AddValue").val();
        if (iname!='' && iname!=" "  &&  idescription!='' && idescription!=" " &&  ivalue!='' && ivalue!=" "){
            $(".btn-close").click();
            setTimeout(function() {
                $("#SuccessAlert").click();
              }, 400); // 2000 milliseconds = 2 seconds
        }else{
        }
    });
    $( "#UpdateSysConBtn" ).on( "click", function() {
        var iname = $("#UpdateName").val();
        var idescription = $("#UpdateDescription").val();
        var ivalue = $("#UpdateValue").val();
        if (iname!='' && iname!=" "  &&  idescription!='' && idescription!=" " &&  ivalue!='' && ivalue!=" "){
            $(".btn-close").click();
            setTimeout(function() {
                $("#SuccessAlert").click();
              }, 200); // 2000 milliseconds = 2 seconds
        }else{
        }
    });
    $("#SearchTable").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $.ajax({
            type: "GET",
            url: "/SystemConfigurationsearch",
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
    //<------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
});

function InitialState() {
    $.ajax({
        url:"/call-route",
        type:"POST",
        data:FormData,
        success: function (response) {

        if(response.success){

        }else{
            alert('Error Response');
        }
        }
    })

}

