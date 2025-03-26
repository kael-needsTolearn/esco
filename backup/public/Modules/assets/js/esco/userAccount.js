{
    $(document).on("click", "#delete-acc", function () {
        $("#warning-alert-modal").modal("show").fadeIn();
        $("#warning-message").html("Delete this user account?");

        var id = $(this).data("value");
        $("#proceed-btn").val(id);
    });
    $(document).on("click", "#proceed-btn", function () {
        var id = $(this).val();
        $.ajax({
            url: "admin/delete-user",
            data: {
                id,
            },
            type: "get",
            success: function (res) {
                $("#warning-alert-modal").modal("hide");
                $("#success-alert-modal").modal("show").fadeIn();
                $("#success-message").html(res.deleted);
                $("#account-table").load(
                    window.location.href + " #account-table"
                );
            },
        });
    });
}
