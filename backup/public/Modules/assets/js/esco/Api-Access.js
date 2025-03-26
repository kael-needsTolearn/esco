var gCompanyId = "";
$(".select2").select2();
$(".hide").hide();
//<------This is the default in Javascript, dropdown search,Datepicker---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
$(document).on("click", "#SearchEmail", function (e) {
    e.preventDefault();
    $("#AvailableAccounts tbody").empty();
    $("#CurrentAccounts tbody").empty();

    var email = $("#CompanyIDSelect").val();
    gCompanyId = email;
    // console.log(email);
    if (email != "" && email != " ") {
        fetchApiAccounts(gCompanyId);
    } else {
        $("#ErrorAlert").click();
    }
});
function fetchApiAccounts(email) {
    $.ajax({
        url: "admin/fetch-api-access",
        type: "GET",
        data: {
            email,
        },
        success: function (response) {
            if (response.success) {
                var RowID = 0;
                $(response.success.not).each(function () {
                    RowID = RowID + 1;
                    var markup = "";
                    markup = "";
                    if(RowID%2==1){
                        markup = "<tr class='table-active text-center' style='color:black;white-space: nowrap;'> ";                
                    }else{
                    markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                    }
                    markup =
                        markup +
                        "<td>" +
                        "<div class='form-check form-checkbox-success mb-2 justify-content-center d-flex'>" +
                        "<input type='checkbox' class='form-check-input' name=check" +
                        RowID +
                        " value=" +
                        this.Api_Id +
                        " id=cb" +
                        RowID +
                        ">" +
                        "</div>" +
                        "</td>";
                    markup = markup + "<td>" + this.Platform + "</td>";
                    markup = markup + "<td>" + this.Description + "</td>";
                    markup = markup + "</tr>";
                    $("#tbbody1").append(markup);
                });
                var RowID = 0;
                $(response.success.have).each(function () {
                    RowID = RowID + 1;
                    var markup = "";
                    markup = "";
                    if(RowID%2==1){
                        markup = "<tr class='table-active text-center' style='color:black;white-space: nowrap;'> ";                
                    }else{
                    markup = "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                    }
                    markup =
                        markup +
                        "<td>" +
                        "<div class='form-check form-checkbox-success mb-2 justify-content-center d-flex'>" +
                        "<input type='checkbox' class='form-check-input' name=box" +
                        RowID +
                        " value=" +
                        this.Api_Id +
                        " id=ck" +
                        RowID +
                        ">" +
                        "</div>" +
                        "</td>";
                    markup = markup + "<td>" + this.Platform + "</td>";
                    markup = markup + "<td>" + this.Description + "</td>";
                    markup = markup + "</tr>";
                    $("#tbbody2").append(markup);
                });
                $(".hide.accounts").show();
            } else {
                alert("Error Response");
            }
        },
    });
}
function SaveApi() {
    var form1Data = $("#Form1").serialize();
    var form2Data = $("#Form2").serialize();

    var values = form1Data.split("&").map(function (pair) {
        return pair.split("=")[1];
    });
    var values1 = form2Data.split("&").map(function (pair) {
        return pair.split("=")[1];
    });
    //    console.log(values1)
    $.ajax({
        url: "admin/add-api-access",
        type: "GET",
        data: { values, values1, gCompanyId },
        success: function (response) {
            $.toast({
                    heading: "Success",
                    text:"You successfully saved the data.",
                    position: "top-right",
                    loaderBg: "white",
                    showHideTransition: "fade",
                    icon: "success",
                    hideAfter: 10000,
                });
            // location.reload();
            $("#AvailableAccounts tbody").empty();
            $("#CurrentAccounts tbody").empty();
            fetchApiAccounts(gCompanyId);
            // $("#accounts-table").load(
            //     window.location.href + " #accounts-table"
            // );
            // $("#tbbody1").load(window.location.href + " #tbbody1");
            // $("#tbbody2").load(window.location.href + " #tbbody2");
        },
    });
}
