<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>ESCO 360</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
        <!-- App favicon -->
        <link rel="shortcut icon" href="Modules/assets/images/favicon.ico">
        
        <!-- App css -->
        <link href="Modules/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="Modules/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="Modules/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
<!-- Include the table2csv library from a CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/table2csv/1.0.0/table2csv.min.js"></script>

    </head>

    <body class="loading authentication-bg" data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":false, "leftSidebarScrollable":false,"darkMode":false, "showRightSidebarOnStart": true}'>
    <div class="overlay">   </div>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                      {{$slot}}
                        <!-- end card -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->
     
        <!-- bundle -->
        <script src="Modules/assets/js/vendor.min.js"></script>
        <script src="Modules/assets/js/app.min.js"></script>
        <script src="Modules/assets/js/esco/resetpassword.js"></script>
    </body>
</html>
