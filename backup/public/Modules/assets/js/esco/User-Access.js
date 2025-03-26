var gEmail = "";
$(document).ready(function () {
    $(".hide").hide();
    // InitialState()
    //<------This is the default in Javascript, dropdown search,Datepicker---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
    $("#SearchEmail").click(function () {
        var email = $("#EmailAddress").val();
        gEmail = email;
        if (email != "" && email != " ") {
            $.ajax({
                url: "admin/UserAccess",
                type: "GET",
                data: {
                    email,
                },
                success: function (response) {
                    if (response.Error) {
                        $("#ErrorAlert").click();
                        $("#ErrorMessage").html(response.Error);
                    } else {
                        fetchUserAccess(email);
                    }
                },
            });
        }
        //<------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
    });
});
function fetchUserAccess(email) {
    $.ajax({
        url: "admin/UserAccess",
        type: "GET",
        data: {
            email,
        },
        success: function (response) {
            console.log(response);
            if (response.success) {
                $("#tbbody1").empty();
                $("#tbbody2").empty();
                // $("#SuccessAlert").click();
                var RowID = 0;
                $(response.success.not).each(function () {
                    RowID = RowID + 1;
                    var markup = "";
                    markup = "";
                    if (RowID % 2 == 1) {
                        markup =
                            "<tr class='table-active text-center' style='color:black;white-space: nowrap;'> ";
                    } else {
                        markup =
                            "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                    }
                    markup =
                        markup +
                        "<td>" +
                        "<div class='form-check form-checkbox-success justify-content-center d-flex'>" +
                        "<input type='checkbox' class='form-check-input' name=check" +
                        RowID +
                        " value=" +
                        this.Company_Id +
                        " id=cb" +
                        RowID +
                        ">" +
                        "</div>" +
                        "</td>";
                    markup = markup + "<td>" + this.Company_Name + "</td>";
                    markup = markup + "<td>" + this.Country + "</td>";
                    markup = markup + "<td>" + this.Contract_Name + "</td>";
                    markup = markup + "<td>" + this.Account_Manager + "</td>";
                    markup = markup + "</tr>";
                    $("#tbbody1").append(markup);
                });
                var RowID = 0;
                $(response.success.have).each(function () {
                    RowID = RowID + 1;
                    var markup = "";
                    markup = "";
                    if (RowID % 2 == 1) {
                        markup =
                            "<tr class='table-active text-center' style='color:black;white-space: nowrap;'> ";
                    } else {
                        markup =
                            "<tr class='text-center' style='color:black;white-space: nowrap;'> ";
                    }
                    markup =
                        markup +
                        "<td>" +
                        "<div class='form-check form-checkbox-success  justify-content-center d-flex'>" +
                        "<input type='checkbox' class='form-check-input' name=box" +
                        RowID +
                        " value=" +
                        this.Company_Id +
                        " id=ck" +
                        RowID +
                        ">" +
                        "</div>" +
                        "</td>";
                    markup = markup + "<td>" + this.Company_Name + "</td>";
                    markup = markup + "<td>" + this.Country + "</td>";
                    markup = markup + "<td>" + this.Contract_Name + "</td>";
                    markup = markup + "<td>" + this.Account_Manager + "</td>";
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
function SaveUsers() {
    var form1Data = $("#Form1").serialize();
    var form2Data = $("#Form2").serialize();

    var values = form1Data.split("&").map(function (pair) {
        return pair.split("=")[1];
    });
    var values1 = form2Data.split("&").map(function (pair) {
        return pair.split("=")[1];
    });
    $.ajax({
        url: "admin/add-user-access",
        type: "GET",
        data: { values, values1, gEmail },
        success: function (response) {
            $.toast({
                heading: "Success",
                text: "You successfully saved the data.",
                position: "top-right",
                loaderBg: "white",
                showHideTransition: "fade",
                icon: "success",
                hideAfter: 10000,
            });
            setTimeout(function () {
                $(".my-2").click();
                $("#AvailableAccounts tbody").empty();
                $("#CurrentAccounts tbody").empty();
                fetchUserAccess(gEmail);
            }, 1700);

            // $("#accounts-table").load(
            //     window.location.href + " #accounts-table"
            // );
            // $("#tbbody1").load(window.location.href + " #tbbody1");
            // $("#tbbody2").load(window.location.href + " #tbbody2");
        },
    });
}
