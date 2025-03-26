$(document).ready(function () {

    $(document).on("submit", "#sysConfigForm", function (e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            type: "POST",
            url: "/admin/add-config",
            data: formData,
            success: function (res) {
                // console.log(res);
                if (res.success) {
                    $("#sysConfigForm")[0].reset();
                    $.toast({
                        heading: "Success",
                        text: res.success,
                        position: "top-right",
                        loaderBg: "white",
                        showHideTransition: "fade",
                        icon: "success",
                        hideAfter: 10000,
                    });
                    $("#config-table").load(window.location.href + " #config-table");
                    // $("#all-data").load(window.location.href + " #all-data");
                }
                // If you want to hide a success message after 1.5 seconds, uncomment the following lines
                setTimeout(function() {
                    $("#success-alert-modal").modal("hide").fadeOut();
                }, 3000);
            },
        });
    });

    console.log("hello");
});


