<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>ESCO 360</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description">
    <meta content="Coderthemes" name="author">
    <!-- App favicon -->
    <link rel="shortcut icon" href="Modules/assets/images/favicon.ico">
    <base href="/public">
    <!-- third party css -->
    <link href="Modules/assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css">
    <!-- third party css end -->

    <!-- App css -->
    <link href="Modules/assets/css/icons.min.css" rel="stylesheet" type="text/css">
    <link href="Modules/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style">
    <link href="Modules/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style">

    {{-- apexchart --}}
    {{-- <link href="{{ mix('node_modules/apexcharts/dist/apexcharts.min.js') }}" rel="stylesheet"> --}}
    <link href="/apexcharts.min.js" rel="stylesheet">
    <style>
        @-webkit-keyframes flippers {
            0% {
                -webkit-transform: perspective(40px) rotateY(-180deg);
                transform: perspective(40px) rotateY(-180deg);
            }

            50% {
                -webkit-transform: perspective(40px) rotateY(0deg);
                transform: perspective(40px) rotateY(0deg);
            }
        }

        @keyframes flippers {
            0% {
                -webkit-transform: perspective(40px) rotateY(-180deg);
                transform: perspective(40px) rotateY(-180deg);
            }

            50% {
                -webkit-transform: perspective(40px) rotateY(0deg);
                transform: perspective(40px) rotateY(0deg);
            }
        }

        .flippers-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            /* Set background color as desired */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            /* Make sure it's on top of other elements */
        }

        .flippers {
            height: 25px;
            display: grid;
            grid-template-columns: repeat(5, 20px);
            grid-gap: 5px;
        }

        .flippers div {
            -webkit-animation: flippers calc(1.25 * 1s) calc(var(--delay) * 1s) infinite ease;
            animation: flippers calc(1.25 * 1s) calc(var(--delay) * 1s) infinite ease;
            background-color: #90c24f;
            /* Changed the color to green */
            text-align: center;
            line-height: 5px;
            font-weight: bold;
            color: white;
        }

        .flippers div:nth-of-type(1) {
            --delay: 0.25;
        }

        .flippers div:nth-of-type(2) {
            --delay: 0.5;
        }

        .flippers div:nth-of-type(3) {
            --delay: 0.75;
        }

        .flippers div:nth-of-type(4) {
            --delay: 1;
        }

        .flippers div:nth-of-type(5) {
            --delay: 1.25;
        }
        .flippers div:nth-of-type(6) {
            --delay: 1.5;
        }
        .flippers div:nth-of-type(7) {
            --delay: 1.75;
        }

        @keyframes fade-out {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        .flippers-container.fade-out {
            animation: fade-out 2s forwards;
        }
    </style>


</head>

<body class="loading"
    data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":false, "leftSidebarScrollable":false,"darkMode":false}'>
    <!-- Begin page -->
    <div class="wrapper">
        <!-- ========== Left Sidebar Start ========== -->
        @include('admin.sidebar')
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">
                <!-- Topbar Start -->
                <div class="navbar-custom">
                    <ul class="list-unstyled topbar-menu float-end mb-0">
                        <li class="dropdown notification-list d-lg-none">
                            <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#"
                                role="button" aria-haspopup="false" aria-expanded="false">
                                <i class="dripicons-search noti-icon"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-animated dropdown-lg p-0">
                                <form class="p-3">
                                    <input type="text" class="form-control" placeholder="Search ..."
                                        aria-label="Recipient's username">
                                </form>
                            </div>
                        </li>
                        <li class="dropdown notification-list">
                            <a class="nav-link dropdown-toggle nav-user arrow-none me-0" data-bs-toggle="dropdown"
                                href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <span class="account-user-avatar">
                                    <img src="Modules/assets/images/users/esco-logo.png" alt="user-image"
                                        class="rounded-circle">
                                </span>
                                <span>
                                    <span class="account-user-name">ESCO</span>
                                    <span class="account-position">Founder</span>
                                </span>
                            </a>
                            <div
                                class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                                <!-- item-->
                                <div class=" dropdown-header noti-title">
                                    <h6 class="text-overflow m-0">Welcome {{ auth()->user()->First_Name }}
                                        {{ auth()->user()->Last_Name }}!</h6>
                                </div>

                                <!-- item-->
                                <!-- <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="mdi mdi-account-circle me-1"></i>
                                    <span>My Account</span>
                                </a> -->

                                <!-- item-->
                                <!-- <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="mdi mdi-account-edit me-1"></i>
                                    <span>Settings</span>
                                </a> -->

                                <!-- item-->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a href="{{ route('logout') }}" class="dropdown-item notify-item"
                                        onclick="event.preventDefault();
                                    this.closest('form').submit();">
                                        <i class="mdi mdi-logout me-1"></i>
                                        <span>Logout</span>
                                    </a>
                                </form>
                            </div>
                        </li>

                    </ul>
                    <button class="button-menu-mobile open-left">
                        <i class="mdi mdi-menu"></i>
                    </button>

                </div>
                <!-- end Topbar -->

                <!-- Start Content-->
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- container -->

            </div>
            <!-- content -->

            <!-- Footer Start -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <script>
                                document.write(new Date().getFullYear())
                            </script>
                        </div>
                        <div class="col-md-6">
                            <div class="text-md-end footer-links d-none d-md-block">
                                <a href="javascript: void(0);">About</a>
                                <a href="javascript: void(0);">Support</a>
                                <a href="javascript: void(0);">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->


    </div>
    <!-- END wrapper -->

    <!-- Right Sidebar -->


    <div class="rightbar-overlay"></div>
    

    {{-- <div class="scaling-dots" style="display: grid"></div> --}}
    <!-- /End-bar -->

    <!-- bundle -->
    <script src="Modules/assets/js/vendor.min.js"></script>
    <script src="Modules/assets/js/app.min.js"></script>

    <!-- third party js -->
    {{-- <script src="Modules/assets/js/vendor/apexcharts.min.js"></script> --}}
    {{-- <script src="Modules/assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="Modules/assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script> --}}
    <!-- third party js ends -->

    <!-- demo app -->
    {{-- <script src="Modules/assets/js/pages/demo.dashboard.js"></script> --}}
    <!-- end demo js-->
    {{-- <script src="Modules/assets/js/pages/demo.apex-column.js"></script> --}}
    <script src="Modules/assets/js/pages/demo.form-wizard.js"></script>
    <script src="Modules/assets/js/esco/Esco-SysCon.js"></script>
    {{-- <script src="{{ mix('node_modules/apexcharts/dist/apexcharts.min.js') }}"></script> --}}
    <script src="/apexcharts.min.js"></script>
    <script src="https://cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>

    {{-- refresh device around all routes --}}
    <script src="Modules/assets/js/esco/refresh-device.js"></script>
    <script src="Modules/assets/js/esco/create-tickets.js"></script>
    


    @yield('javascripts')
</body>

</html>
