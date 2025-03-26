var platform = $("#platform_name");
var xio = document.getElementById("xio-div");
var qsys = document.getElementById("qsys-div");
var gApi_Id = "";
platform.on("change", function (e) {
    var value = e.target.value;
    if (value === "xio") {
        xio.classList.remove("d-none");
        xio.classList.add("d-block");
        qsys.classList.remove("d-block");
        qsys.classList.add("d-none");
    } else if (value === "qsys") {
        qsys.classList.remove("d-none");
        qsys.classList.add("d-block");
        xio.classList.remove("d-block");
        xio.classList.add("d-none");
    } else {
        xio.classList.remove("d-block");
        xio.classList.add("d-none");
        qsys.classList.remove("d-block");
        qsys.classList.add("d-none");
    }
});

function DeleteAcc(element) {
    $("#warning-alert-modal").modal("show");
    gApi_Id = element;
}
$("#proceed-btn").click(function () {
    $.ajax({
        type: "get",
        url: "/DeleteApiAccount",
        data: {
            gApi_Id,
        },
        success: function (response) {
            if (response.error) {
                // $("#error-alert-modal").modal("show");
                // $("#error-message").html(
                //     "Delete Assigned Api on this Company Profile First!"
                // );
                $.toast({
                    heading: "Error",
                    text:"Delete Assigned Api on this Company Profile First!",
                    position: "top-right",
                    loaderBg: "white",
                    showHideTransition: "fade",
                    icon: "error",
                    hideAfter: 10000,
                });
                $("#account-table").load(
                    window.location.href + " #account-table"
                );
            } else {
                // $("#success-alert-modal").modal("show");
                // $("#success-message").html(
                //     "You Deleted the Company Profile Successfully!"
                // );
                $.toast({
                    heading: "Error",
                    text:"You Deleted the Company Profile Successfully!",
                    position: "top-right",
                    loaderBg: "white",
                    showHideTransition: "fade",
                    icon: "error",
                    hideAfter: 10000,
                });
                $("#account-table").load(
                    window.location.href + " #account-table"
                );
            }
        },
    });
});
$(document).on("submit", "#add-api-form", function (e) {
    e.preventDefault();
    // Get the form data
    var formData = $(this).serialize();

    // console.log(formData);
    $.ajax({
        url: "/admin/add-api",
        type: "POST",
        data: formData,
        success: function (res) {
            console.log(res);
            // Reset the form
            if (res.success) {
                $("#add-api-form")[0].reset();
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
                $("#account-table").load(
                    window.location.href + " #account-table"
                );
                // $("#all-data").load(window.location.href + " #all-data");
            } else {
                $.toast({
                    heading: "Error",
                    text: res.error,
                    position: "top-right",
                    loaderBg: "white",
                    showHideTransition: "fade",
                    icon: "error",
                    hideAfter: 10000,
                });
            }
            // If you want to hide a success message after 1.5 seconds, uncomment the following lines
            setTimeout(function () {
                $("#success-alert-modal").modal("hide").fadeOut();
                $("#error-alert-modal").modal("hide").fadeOut();
            }, 2000);
        },
        error: function (error) {
            console.log(error);

            var errors = error.responseJSON.errors;
            var errorString = "";
            $.each(errors, function (key, value) {
                errorString += value + "<br>";
            });

            $.toast({
                heading: "Error",
                text: errorString,
                position: "top-right",
                loaderBg: "white",
                showHideTransition: "fade",
                icon: "error",
                hideAfter: 10000,
            });
        },
    });
});
