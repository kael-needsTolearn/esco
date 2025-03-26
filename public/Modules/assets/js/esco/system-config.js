var ctr = false;
$(document).ready(function () {
   // console.log('fetch config');
    // $("#Company_Logo_lg").find("img.Logow").remove();
    // $("#Company_Logo_sm").find("img.Logow").remove();
    // $("#user-append").find("img.userim").remove();
    // $.ajax({
    //     type: "get",
    //     url: "/fetch-config-logo",
    //     data: {                  
    //     },
    //     success: function (res) {
    //         $("#Company_Logo_lg").append("<img class='Logow' src='Modules/assets/images/logo.png' alt='' height='46'>");
    //         $("#Company_Logo_sm").append("<img class='Logow' src='Modules/assets/images/logo.png' alt='' height='16'>");
    //         // $("#Company_Logo_lg").append("<img class='Logow' src='Modules/assets/images/"+res.data+"' alt='' height='46'>");
    //         // $("#Company_Logo_sm").append("<img class='Logow' src='Modules/assets/images/"+res.data+"' alt='' height='16'>");
    //     },
    // });

    $.ajax({
        type: "get",
        url: "/fetch-config-user",
        data: {                  
        },
        success: function (res) {
            $("#user-append").append("<img src='Modules/assets/images/users/esco-logo.png' alt='user-image' class='rounded-circle userim'>");
        },
    });
    $("#AddSystemConfig").click(function() {

        $.ajax({
            type: "get",
            url: "/CompanyAccounts",
            data: {                  
            },
            success: function (res) {
                $("#addOptionCompany option:not(:first)").remove();
              // $("#UpdateCompanyAccount option:not(:first)").remove();
               RowID = 0;
               var markup = '';
                $.each(res.data, function(index, item) {
                    RowID = RowID + 1;
                    markup = "<option value="+item.Company_Id+">"+item.Company_Name+' - '+item.Contract_Name+' - '+item.Account_Manager+"</option>";
                    $("#addOptionCompany").append(markup);
                  //  $("#UpdateCompanyAccount").append(markup);
                });
            },
        });
    });
    $(document).on("submit", "#sysConfigForm", function (e) {
        e.preventDefault();
        var formData = $(this).serialize();
       // var hi =$("#addOptionCompany").val();
      //  console.log(hi);
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
                    location.reload(true);
                }else{
                    console.log(res.error);
                }
                // If you want to hide a success message after 1.5 seconds, uncomment the following lines
                setTimeout(function() {
                    $("#success-alert-modal").modal("hide").fadeOut();
                }, 3000);
            },
        });
    });

});

// function EscoList(){
//     ctr = !ctr;
//     if(ctr){
//         $("#ddlist").addClass("show");
//         $("#ddlist").attr("aria-expanded", "true");
//     }else{
//         $("#ddlist").removeClass("show");
//         $("#ddlist").attr("aria-expanded", "false");
//     }
    
// }