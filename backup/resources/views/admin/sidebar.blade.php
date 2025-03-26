<div class="leftside-menu">

    <!-- LOGO -->
    <a href="/" class="logo text-center logo-light">
        <span class="logo-lg">
            <img src="Modules/assets/images/logo.png" alt="" height="46">
        </span>
        <span class="logo-sm">
            <img src="Modules/assets/images/users/esco-logo.png" alt="" height="16">
        </span>
    </a>

    <!-- LOGO -->
    <a href="index.html" class="logo text-center logo-dark">
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
                    class="side-nav-link {{ request()->routeIs(['dashboard', 'InitReports', 'analytics']) ? 'bg-success' : '' }}">
                    <i class="uil-home-alt "></i>
                    {{-- <span class="badge bg-success float-end">4</span> --}}
                    <span> Dashboards </span>
                </a>
                <div class="collapse" id="sidebarDashboards">
                    <ul class="side-nav-second-level">
                        {{-- <li>
                            <a href="{{route('dashboard')}}">Homepage</a>
                            <!-- a href="dashboard-analytics.html"Analytics -->
                        </li> --}}
                        <li>
                            <a href="{{ route('dashboard') }}">Analytics</a>
                            <!-- a href="dashboard-analytics.html"Analytics -->
                        </li>
                        <li>
                            <a href="{{ route('InitReports') }}">Reports</a>
                            <!-- a href="dashboard-analytics.html"Analytics -->
                        </li>

                    </ul>
                </div>
            </li>
            @if (Auth::user()->usertype != 0)
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarConfiguration" aria-expanded="false"
                        aria-controls="sidebarConfiguration"
                        class="side-nav-link {{ request()->routeIs(['company-profiles', 'add-profile', 'user-account', 'user-access', 'api-accounts', 'ApiAccess']) ? 'bg-success' : '' }}">
                        <i class="dripicons-gear"></i>
                        {{-- <span class="badge bg-success float-end">4</span> --}}
                        <span> System Configuration </span>
                    </a>
                    <div class="collapse" id="sidebarConfiguration">
                        <ul class="side-nav-second-level">

                            <li>
                                <a href="{{ route('company-profiles') }}">Company Profiles</a>
                                <!-- a href="dashboard-analytics.html"Analytics -->
                            </li>

                            <li>
                                <a href="{{ route('api-accounts') }}">API Accounts</a>
                                <!-- a href="dashboard-analytics.html"Analytics -->
                            </li>
                            <li>
                                <a href="{{ route('ApiAccess') }}">API Access</a>
                                <!-- a href="dashboard-analytics.html"Analytics -->
                            </li>
                            <li>
                                <a href="{{ route('user-account') }}">User Accounts</a>
                                <!-- a href="dashboard-analytics.html"Analytics -->
                            </li>
                            <li>
                                <a href="{{ route('user-access') }}">User Access</a>
                                <!-- a href="dashboard-analytics.html"Analytics -->
                            </li>

                            <li>
                                <a href="{{ route('systemConfig') }}">System Configuration</a>
                                <!-- a href="dashboard-analytics.html"Analytics -->
                            </li>

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
