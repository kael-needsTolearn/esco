// $(document).ready(function () {
//     refreshDevice();
// });

// function refreshDevice(){
//     $.ajax({
//          url: "/refresh-device",
//          type: "GET",
//          success: function(res) {
//             setTimeout(version2, 20000);
//          },
//          error: function(xhr, status, error) {
//             $.toast({
//                 heading: "Error",
//                 text: error,
//                 position: "top-right",
//                 loaderBg: "white",
//                 showHideTransition: "fade",
//                 icon: "error",
//                 hideAfter: 3000,
//             });
//          }
//      });
// }
// function version2(){
//     $.ajax({
//         url: "/version2",
//         type: "GET",
//         success: function(res) {
//          //   console.log('2nd version2');
//             setTimeout(CreationOfTickets, 120000);  
//         },
//         error: function(xhr, status, error) {
//             console.error("Error fetching device data:", error);
//         }
//     });
// }
// function CreationOfTickets(){
//     $.ajax({
//         url: "/CreationOfTickets",
//         type: "GET",
//         success: function(res) {
//             //console.log('3rd Creation of tickets');
//             setTimeout(checkAndRunTask, 120000); 
//         },
//         error: function(xhr, status, error) {
//             console.error("Error fetching device data:", error);
//         }
//     });
// }
// function checkAndRunTask() {
//     var now = new Date();
//     var hours = now.getHours();

//     // Define the time range (6 AM to 12 PM)
//     if (hours >= 6 && hours < 12) {
//         if (!hasTaskRun()) {
//           //  console.log('4th  Comment in the tickets');
//             CommentInTickets();
//             markTaskAsExecuted();
//         } else {
//            // console.log('4th skip comment in tickets');
//             setTimeout(CommentDeviceRemoved, 120000); 
//             refreshDevice2p1();
//             version2p1();
//         }
//     } else {
//      //   console.log('4th skip comment in tickets');
//         setTimeout(CommentDeviceRemoved, 120000); 
//         refreshDevice2p1();
//         version2p1();
//     }
// }
// function CommentInTickets(){
//     $.ajax({
//         url: "/CommentInTickets",
//         type: "GET",
//         success: function(res) {
//             setTimeout(CommentDeviceRemoved, 120000); 
//             // refreshDevice2p1();
//             // version2p1();
//         },
//         error: function(xhr, status, error) {
//             console.error("Error fetching device data:", error);
//         }
//     });
// }
// function hasTaskRun() {
//     return localStorage.getItem('taskExecuted') === 'true';
// }
// function markTaskAsExecuted() {
//     localStorage.setItem('taskExecuted', 'true');
// }
// function refreshDevice2p1(){
//     $.ajax({
//          url: "/refresh-device",
//          type: "GET",
//          success: function(res) {
//          },
//          error: function(xhr, status, error) {
//              console.error("Error fetching device data:", error);
//          }
//      });
// }
// function version2p1(){
//     $.ajax({
//         url: "/version2",
//         type: "GET",
//         success: function(res) {
          
//         },
//         error: function(xhr, status, error) {
//             console.error("Error fetching device data:", error);
//         }
//     });
// }
// function  CommentDeviceRemoved(){
//     $.ajax({
//         url: "/CommentDeviceRemoved",
//         type: "GET",
//         success: function(res) {
//            // console.log('5th comment device removed');
//             setTimeout(checkAndRunUpdate, 120000); 
//         },
//         error: function(xhr, status, error) {
//             console.error("Error fetching device data:", error);
//         }
//     });
// } 
// function checkAndRunUpdate() {
//     var now = new Date();
//     var hours = now.getHours();
//     UpdateTickets();
// }
// function UpdateTickets(){
//     //Update then resolved by esco care 360if success
//     $.ajax({
//         url: "/UpdateTickets",
//         type: "GET",
//         success: function(res) {
//            // console.log('6th  Update tickets');
//             setTimeout(RetrieveTickets, 120000); 
//             refreshDevice2p1();
//             version2p1();
//         },
//         error: function(xhr, status, error) {
//             console.error("Error fetching device data:", error);
//         }
//     });
// }
// function RetrieveTickets(){
//     $.ajax({
//         url: "/RetrieveTickets",
//         type: "GET",
//         success: function(res) {
//            // console.log('7th Align status of zoho tickets and db tickets return to 1st step ');
//             setTimeout(refreshDevice, 120000); // 120,000 ms = 2 minutes
//         },
//         error: function(xhr, status, error) {
//             console.error("Error fetching device data:", error);
//         }
//     });
// }