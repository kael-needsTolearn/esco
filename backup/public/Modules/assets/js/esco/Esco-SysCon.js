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
            type: "POST",
            contentType: "application/json; charset=utf-8",
            url: "ComputerProfile.aspx/AssignedTo",
            data: JSON.stringify({
                vvalue: value,
            }),
            dataType: "JSON",
            success: function (response) {
                if (response.d.messagetype == "E") {
                    swal({
                        title: 'Error Encountered',
                        text: response.d.message,
                        type: 'error',
                        customClass: 'animated tada',
                        position: 'top'
                    })
                } else if (response.d.messagetype == "R") {
                    swal({
                        title: 'Session Expired',
                        text: response.d.message,
                        type: 'warning',
                        customClass: 'animated tada',
                        position: 'top'
                    })
                } else {
                    var xmlDoc = $.parseXML(response.d);
                    var xml = $(xmlDoc);
                    var list = xml.find("view");
                    RowID = 0;
                    $(list).each(function () {

                        $("#AssignedPosition").val($(this).find("Position").text())
                    });
                }
            },
            error: function (result) {
                swal(
                    'Error Encountered',
                    result.responseText,
                    'error'
                )
            }
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

