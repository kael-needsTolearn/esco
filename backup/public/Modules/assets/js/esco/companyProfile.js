var gCompany_Id = "";
$(document).ready(function () {
    $("#proceed-btn").click(function () {
        $.ajax({
            type: "get",
            url: "/DeleteCompanyAccount",
            data: {
                gCompany_Id,
            },
            success: function (response) {
                if (response.error) {
                    // $("#error-alert-modal").modal("show");
                    // $("#error-message").html(
                    //     "Delete Assigned Api on this Company Profile First!"
                    // );
                    $.toast({
                        heading: "Error",
                        text: "Delete Assigned Api on this Company Profile First!",
                        position: "top-right",
                        loaderBg: "white",
                        showHideTransition: "fade",
                        icon: "error",
                        hideAfter: 10000,
                    });
                    $("#company-table").load(
                        window.location.href + " #company-table"
                    );
                } else {
                    // $("#success-alert-modal").modal("show");
                    // $("#success-message").html(
                    //     "You Deleted the Company Profile Successfully!"
                    // );
                    $.toast({
                        heading: "Success!",
                        text: "You Deleted the Company Profile Successfully!",
                        position: "top-right",
                        loaderBg: "white",
                        showHideTransition: "fade",
                        icon: "success",
                        hideAfter: 10000,
                    });
                    $("#company-table").load(
                        window.location.href + " #company-table"
                    );
                }
            },
        });
    });
    $("#AddCompProf").click(function () {
        $("#Company_Name").val("");
        $("#Company_Address").val("");
        $("#Country").val("");
        $("#Contract_Name").val("");
        $("#start_date").val("");
        $("#end_date").val("");
        $("#Account_Manager").val("");
        $("#Account_Manager_Email").val("");
    });
    $(document).on("submit", "#add-profile-form", function (e) {
        e.preventDefault();
        // Get the form data
        var formData = $(this).serialize();
        // console.log(formData);
        $.ajax({
            url: "/admin/add-profile",
            type: "post",
            data: formData,
            success: function (res) {
                console.log(res);
                // Reset the form
                if (res.success) {
                    $("#add-profile-form")[0].reset();
                    $("#event-modal").modal("hide");
                    $.toast({
                        heading: "Success",
                        text: res.success,
                        position: "top-right",
                        loaderBg: "white",
                        showHideTransition: "fade",
                        icon: "success",
                        hideAfter: 10000,
                    });
                    $("#company-table").load(
                        window.location.href + " #company-table"
                    );
                    // $("#all-data").load(window.location.href + " #all-data");
                }
                // If you want to hide a success message after 1.5 seconds, uncomment the following lines
                setTimeout(function () {
                    $("#success-alert-modal").modal("hide").fadeOut();
                }, 3000);
            },
        });
    });
});

function EditAccount(CompanyID) {
    $.ajax({
        url: "/edit-company",
        data: { CompanyID },
        type: "get",
        success: function (res) {
            console.log(res.data);
            $("#AddCompProf").click();
            $("#Company_Id").val(res.data.Company_Id);
            console.log(res.data.Company_Id)
            $("#Company_Name").val(res.data.Company_Name);
            $("#Company_Address").val(res.data.Company_Address);
            $("#Country").val(res.data.Country);
            $("#Contract_Name").val(res.data.Contract_Name);
            $("#start_date").val(res.data.Contract_Start_Date);
            $("#end_date").val(res.data.Contract_End_Date);
            $("#Account_Manager").val(res.data.Account_Manager);
            $("#Account_Manager_Email").val(res.data.Account_Manager_Email);
        },
    });
}
function DeleteAcc(element) {
    $("#warning-alert-modal").modal("show");
    gCompany_Id = element;
}
