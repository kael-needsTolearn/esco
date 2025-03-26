$(document).ready(function () {
   
    $(".flippers-container").hide();
  
    $(document).on("submit", "#ResetPW", function (e) {
        e.preventDefault();
        var data = $(this).serialize();
        $.ajax({
            url: "/SaveResetPassword",
            data: data, // Remove curly braces around data
            type: "post",
            success: function (res) {
               if(res.AlertNotif){
                $.toast({
                    heading: "Error",
                    text: res.AlertNotif,
                    position: "top-right",
                    loaderBg: "white",
                    showHideTransition: "fade",
                    icon: "error",
                    hideAfter: 6000,
                });
               }else{
                    $.toast({
                        heading: "Success",
                        text: "Succesfully changed your password!", // Assuming res.AlertNotif contains the error message
                        position: "top-right",
                        loaderBg: "white",
                        showHideTransition: "fade",
                        icon: "success",
                        hideAfter: 2000,
                    });
                    setTimeout(function() {
                        window.location.href = "/";
                    }, 2500); // 5000 milliseconds = 5 seconds
               }
            }
        });
    });
    $('.x_button-primary').on('click', function() {
    });
    $('#logout').on('click', function() {
      //  window.location.href = '/login';

    });
});

function LoginPage(){
    var emailaddress=$("#emailaddress").val();
    $(".flippers-container").addClass("fade-in");
    $(".flippers-container").show();
    // setTimeout(function() {
    //     $(".flippers-container").hide(); // Hide the scaling dots container after 5 seconds
    // }, 4000); // 5000 milliseconds = 5 seconds
    //alert('here');
    $.ajax({
        url: "/LoginAuth",
        type: "get",
        data: {emailaddress},
        success: function (response) {
            if(response.message){
            $.toast({
                heading: "Error",
                text: response.message,
                position: "top-right",
                loaderBg: "white",
                showHideTransition: "fade",
                icon: "error",
                hideAfter: 8000,
            });
            setTimeout(function() {
                $(".flippers-container").hide();
            }, 1000);
            }else{

                window.location.href = "/EmailMessage";
                setTimeout(function() {
                    $(".flippers-container").hide();
                }, 1000);
               
            }
           
        },
    });
}