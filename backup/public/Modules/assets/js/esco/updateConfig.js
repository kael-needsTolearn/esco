$(document).ready(function () {
    $(document).on("click", ".update-btn", function () {
        var id = $(this).data("id");
        $.ajax({
            url: "admin/edit-config",
            data: { id },
            type: "get",
            success: function (res) {
                // console.log(res.data);
                $("#code_id").val(res.data.Code_ID);
                $("#update_name").val(res.data.Code_Name);
                $("#update_desc").val(res.data.Code_Description);
                $("#update_value").val(res.data.Code_Value);
            },
        });
    });
    $(document).on("submit", "#config-update", function (e) {
        e.preventDefault();
        var data = $(this).serialize();
        $.ajax({
            url: "admin/update-config",
            data: data, // Remove curly braces around data
            type: "post",
            success: function (res) {
                if (res.success) {
                    $("#UpdateSysCon").modal("hide");
                    $.toast({
                        heading: "Success",
                        text: res.success,
                        position: "top-right",
                        loaderBg: "white",
                        showHideTransition: "fade",
                        icon: "success",
                        hideAfter: 10000,
                    });
                    $("#config-table").load(
                        location.href + " #config-table",
                        ""
                    );
                }else{
                    // $("#UpdateSysCon").modal("hide");
                    $.toast({
                        heading: "Error",
                        text: res.error,
                        position: "top-right",
                        loaderBg: "white",
                        showHideTransition: "fade",
                        icon: "error",
                        hideAfter: 10000,
                    });
                    // $("#config-table").load(
                    //     location.href + " #config-table",
                    //     ""
                    // );
                }

                // setTimeout(function () {
                //     $("#success-alert-modal").modal("hide").fadeOut();
                //     $("#error-alert-modal").modal("hide").fadeOut();
                // }, 3000);
            },
        });
    });
});
