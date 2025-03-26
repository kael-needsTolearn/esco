<div class="leftside-menu">

    <!-- LOGO -->
    <a href="/" class="logo text-center logo-light">
        <span class="logo-lg " id="Company_Logo_lg">
         <img src="Modules/assets/images/logo.png" alt="" height="46"> 
        </span>
        <span class="logo-sm " id="Company_Logo_sm">
            <img src="Modules/assets/images/users/esco-logo.png" alt="" height="16"> 
        </span>
    </a>

    <!-- LOGO dark-->
    <a href="/" class="logo text-center logo-dark">
        <span class="logo-lg">
            <img src="Modules/assets/images/logo-dark.png" alt="" height="16">
        </span>
        <span class="logo-sm">
            <img src="Modules/assets/images/logo_sm_dark.png" alt="" height="16">
        </span>
    </a>

    <div class="h-100" id="leftside-menu-container" data-simplebar="">

        <!--- Sidemenu -->
        <ul class="side-nav">

            <li class="side-nav-title side-nav-item">Navigation</li>

            <li class="side-nav-item ">
                <a data-bs-toggle="collapse" href="#sidebarDashboards" aria-expanded="false"
                    aria-controls="sidebarDashboards"
                    class="side-nav-link {{ request()->routeIs(['dashboard', 'InitReports', 'analytics','RoomWellness']) ? 'bg-success' : '' }}">
                    <i class="uil-home-alt "></i>
                    {{-- <span class="badge bg-success float-end">4</span> --}}
                    <span> Dashboards </span>
                </a>
                <div class="collapse" id="sidebarDashboards">
                    <ul class="side-nav-second-level">
                   
                        <li>
                            <a href="{{ route('dashboard') }}">Analytics</a>
                        </li>
                        <li>
                            <a href="{{ route('InitReports') }}">Reports</a>
                        </li>
                        
                        @if(!$uhoo->isEmpty())
                        <li>
                            <a href="{{ route('uhooDashboard') }}">Room Wellness</a>
                        </li>
                        <li>
                            <a href="{{ route('uhooDeviceData') }}">Asset Manager</a>
                        </li>
                        @else

                        @endif
                        
                    </ul>
                </div>
            </li>
            @if (Auth::user()->usertype != 0)
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarConfiguration" aria-expanded="false"
                        aria-controls="sidebarConfiguration"
                        class="side-nav-link {{ request()->routeIs(['company-profiles', 'add-profile', 'user-account', 'user-access', 'api-accounts', 'ApiAccess']) ? 'bg-success' : '' }}">
                        <i class="dripicons-gear"></i>
                        <span> System Configuration </span>
                    </a>
                    <div class="collapse" id="sidebarConfiguration">
                        <ul class="side-nav-second-level">

                            <li>
                                <a href="{{ route('company-profiles') }}">Company Profiles</a>
                                <!-- a href="dashboard-analytics.html"Analytics -->
                            </li>
                            @if (Auth::user()->usertype == 2)
                            <li>
                                <a href="{{ route('api-accounts') }}">API Accounts</a>
                                <!-- a href="dashboard-analytics.html"Analytics -->
                            </li>
                            <li>
                                <a href="{{ route('ApiAccess') }}">API Access</a>
                                <!-- a href="dashboard-analytics.html"Analytics -->
                            </li>
                            @endif
                            <li>
                                <a href="{{ route('user-account') }}">User Accounts</a>
                                <!-- a href="dashboard-analytics.html"Analytics -->
                            </li>
                            <li>
                                <a href="{{ route('user-access') }}">User Access</a>
                                <!-- a href="dashboard-analytics.html"Analytics -->
                            </li>
                            @if (Auth::user()->usertype == 2)
                            <li>
                                <a href="{{ route('systemConfig') }}">System Configuration</a>
                                <!-- a href="dashboard-analytics.html"Analytics -->
                            </li>
                            @endif

                        </ul>
                    </div>
                </li>
            @endif


        </ul>


        <!-- End Sidebar -->

        <div class="clearfix"></div>

    </div>
    <!-- Sidebar -left -->

</div>
