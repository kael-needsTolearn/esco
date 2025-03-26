{
    var gId='';
    $(document).on("click", "#delete-acc", function () {
        $("#warning-alert-modal").modal("show").fadeIn();
        $("#warning-message").html("Delete this user account?");

        var id = $(this).data("value");
        $("#proceed-btn").val(id);
    });
    $(document).on("click", "#edit-acc", function () { //reactivate account
        var id = $(this).data("value");
        var ctr = 2;
        $.ajax({
            url: "admin/delete-user",
            data: {
                id,ctr,
            },
            type: "get",
            success: function (res) {
                //console.log(res.deleted);
                if(res.reactivated){
                    $.toast({
                        heading: "Success",
                        text: res.reactivated,
                        position: "top-right",
                        loaderBg: "white",
                        showHideTransition: "fade",
                        icon: "success",
                        hideAfter: 1500,
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 1500); 
                   }else{
                        $.toast({
                            heading: "Error",
                            text: res.error, // Assuming res.AlertNotif contains the error message
                            position: "top-right",
                            loaderBg: "white",
                            showHideTransition: "fade",
                            icon: "error",
                            hideAfter: 6000,
                        });
                   }
            },
        });
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
                //console.log(res.deleted);
                if(res.deleted){
                    $.toast({
                        heading: "Success",
                        text: res.deleted,
                        position: "top-right",
                        loaderBg: "white",
                        showHideTransition: "fade",
                        icon: "success",
                        hideAfter: 1500,
                    });
                    setTimeout(function() {
                        //window.location.href = "/admin/user-account";
                        // $("#account-table").load(
                        //     window.location.href + " #account-table"
                        // );
                        location.reload();
                    }, 1500); // 5000 milliseconds = 5 seconds
                   }else{
                        $.toast({
                            heading: "Error",
                            text: res.error, // Assuming res.AlertNotif contains the error message
                            position: "top-right",
                            loaderBg: "white",
                            showHideTransition: "fade",
                            icon: "error",
                            hideAfter: 6000,
                        });
                 
                   }



               
            },
        });
    });
    $(document).on("click", "#update-acc", function () { //updateaccount
        
        var id = $(this).data("value");
        gId=id;
        var ctr = 4;
        $.ajax({
            url: "admin/delete-user",
            data: {
                id,ctr,
            },
            type: "get",
            success: function (res) {
                $("#FirstName").val(res.users.First_Name)
                $("#LastName").val(res.users.Last_Name)
                $("#email").val(res.users.email)
                $("#Position").val(res.users.Position)
                $("#usertype").val(res.users.usertype)
            },
        });
    });
    $(document).on("click", "#UpdateUserData", function () { //updateaccount
       // var id = $(this).data("value");
        var ctr = 3;
        //e.preventDefault();
        var UpdateTheUser = $("#UpdateTheUser").serialize();
        var values = UpdateTheUser.split("&").map(function (pair) {
            return pair.split("=")[1];
        });
        var email=$("#email").val();
        $.ajax({
            url: "admin/delete-user",
            data: {
                gId,ctr,values,email,
            },
            type: "get",
            success: function (res) {
                //console.log(res.deleted);
                if(res.success){
                    $.toast({
                        heading: "Success",
                        text: res.success,
                        position: "top-right",
                        loaderBg: "white",
                        showHideTransition: "fade",
                        icon: "success",
                        hideAfter: 1500,
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 1500); 
                   }else{
                        $.toast({
                            heading: "Error",
                            text: res.error, // Assuming res.AlertNotif contains the error message
                            position: "top-right",
                            loaderBg: "white",
                            showHideTransition: "fade",
                            icon: "error",
                            hideAfter: 6000,
                        });
                   }
            },
        });
    });
}
