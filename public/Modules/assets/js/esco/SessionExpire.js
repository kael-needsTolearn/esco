var gNewVal = '';

$(document).ready(function() {
  $(".form-switch").hide();

  async function fetchSessionTimeout() {
    try {
      const response = await fetch("/sessionTimeout", {
        method: "GET"
      });

      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }

      const res = await response.json();

      if (res.auth.usertype === 0) {
        setupIdleTracking();
      } else { // admin
        document.querySelectorAll(".form-switch").forEach(el => el.style.display = 'block');
      }
    } catch (error) {
      console.error("Error fetching session timeout data:", error);
    }
  }

  fetchSessionTimeout();

  $(document).on("change", 'input[type="checkbox"]#SessionExpire', function() {
    var currentValue = $(this).val();
    var newValue = (currentValue === 'no') ? 'yes' : location.reload(true);
    $(this).val(newValue);
    gNewVal = newValue;

    $.ajax({
      url: "/sessionTimeout",
      type: "GET",
      success: function(res) {
        if (res.auth.usertype === 0) {
          setupIdleTracking();
        } else { // admin
            if(gNewVal='yes'){
              setupIdleTracking();
            } 
        }
      },
      error: function(xhr, status, error) {
        console.error("Error fetching device data:", error);
      }
    });
  });

  function setupIdleTracking() {
    if(gNewVal==='no'){
        return false;
    }else{

    
    function checkIdleTime() {
      $.toast({
        heading: "SESSION EXPIRED",
        text: "Session expired, please login again.",
        position: "top-right",
        loaderBg: "white",
        showHideTransition: "fade",
        icon: "warning",
        hideAfter: 2000
      });

      // Prevent form submission if session expire checkbox is off
      if (gNewVal === 'no') {
        return false; // Prevent form submission
      }

      setTimeout(function() {
        $('#logoutForm').submit();
      }, 2000); // 5 seconds delay
    }

    function resetIdleTimer() {
      clearTimeout(idleTimeout);
      idleTimeout = setTimeout(checkIdleTime, 1800000); // 5 seconds delay
    }

    let idleTimeout;

    document.addEventListener('mousemove', resetIdleTimer);
    document.addEventListener('keydown', resetIdleTimer);
    document.addEventListener('click', resetIdleTimer);
    document.addEventListener('scroll', resetIdleTimer);

    resetIdleTimer();
    }
  }
});