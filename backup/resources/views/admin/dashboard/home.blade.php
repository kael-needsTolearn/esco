@extends('layouts.admin')
@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <form class="d-flex">
                        <div class="input-group">
                            <!-- <input type="text" class="form-control form-control-light" id="dash-daterange"> -->
                            <!-- <span class="input-group-text bg-primary border-primary text-white">
                                <i class="mdi mdi-calendar-range font-13"></i>
                            </span> -->
                        </div>
                    </form>
                </div>
                <h4 class="page-title">Dashboard</h4>
            </div>
        </div>
    </div>
    <!-- large display block small display hide-->
    <div class="row d-lg-block">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body d-flex">
                    <div class="  d-flex">
                        <h4 class="header-title text-left p-0 mt-2 me-2">Region</h4>
                        <select class="form-select ms-3 me-4" id="regionID">
                            <option selected>Select Region</option>
                            @php
                                $processedCountries = [];
                            @endphp

                            @foreach ($regions as $item)
                                @if (!in_array($item->Country, $processedCountries))
                                    <option value="{{ $item->Country }}">{{ $item->Country }}</option>
                                    @php
                                        $processedCountries[] = $item->Country;
                                    @endphp
                                @endif
                            @endforeach

                        </select>
                    </div>
                    <div class="d-flex">
                        <h4 class="header-title text-left p-0 mt-2 me-2">Organization</h4>
                        <select class="form-select  ms-3" id="regionOrgs">
                            <option selected value="">Select Region First</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- large display hide small display block-->
    <div class="row d-lg-none d-md-none d-sm-none d-xl-none d-block">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="d-flex justify-content-between gap-5">
                            <h4 class="header-title text-left p-0 mt-2">Region</h4>
                            <select class="form-select ">
                                <option selected>Open this select menu</option>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="d-flex mt-1  justify-content-between">
                            <h4 class="header-title text-left p-0 mt-2 me-2">Organization</h4>
                            <select class="form-select " style="margin-left: 4px;">
                                <option selected>Open this select menu</option>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <h4 class="header-title text-center mb-3">Available Rooms</h4>
                    <hr>
                    <table class="table table-centered table-borderless mb-0" id="table-room">
                        <thead style="background-color:#f1f3fa;">

                        </thead>
                        <tbody id="roomBody">
                            <p class="text-danger" id="room-message">No data availabale.</p>
                            {{-- @if ($rooms->isEmpty())
                                <p class="text-danger" id="room-message">No data availabale.</p>
                            @else
                                @foreach ($rooms as $index => $item)
                                    <tr @if ($index % 2 == 0) class="table-active" @endif>
                                        <td>
                                            <!-- Switch-->
                                            <div class="form-check form-checkbox-success mb-2">
                                                <input type="checkbox" class="form-check-input"
                                                    id="customCheckcolor2{{ $index + 1 }}"
                                                    name="roomID{{ $index + 1 }}" value="{{ $item->DeviceRoomID }}">
                                            </div>
                                        </td>
                                        <td>{{ $item->DeviceRoomName }}</td>
                                    </tr>
                                @endforeach
                            @endif --}}
                        </tbody>
                    </table>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->

        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-6">
            <div class="card card-h-100">
                <div class="card-body" style="height: 400px;" id="all-data">
                    {{-- @php
                        $user = Auth::user();
                        $accesses = App\Models\UserAccess::where('User_Id', $user->id)->get();
                        foreach ($accesses as $access) {
                            $devices = App\Models\Device::where('Company_Id', $access->Company_Id)->get();
                            $online = App\Models\Device::where('Company_Id', $access->Company_Id)
                                ->where('Status', 'online')
                                ->count();
                            $offline = App\Models\Device::where('Company_Id', $access->Company_Id)
                                ->where('Status', 'offline')
                                ->count();
                        }
                    @endphp --}}
                    <h4 class="header-title text-center">Device Status</h4>
                    <hr>
                    <div id="average-sales" class="apex-charts" data-colors="#fa5c7c,#0acf97"></div>


                    <div class="chart-widget-list">
                        <p>
                            <i class="mdi mdi-square text-danger"></i> Offline
                            <span class="float-end" id="off-count">{{ isset($offline) ? $offline : 0 }}</span>

                        </p>
                        <p>
                            <i class="mdi mdi-square text-success"></i> Online
                            <span class="float-end" id="on-count">{{ isset($online) ? $online : 0 }}</span>

                        </p>
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->

        </div> <!-- end col -->



        <div class="col-xxl-6 col-xl-12 col-lg-12 col-md-12 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title text-center">Devices</h4> 
                    <hr>
                    <div class="table-responsive" style="height: 350px; overflow-y: auto; overflow-x: auto;">
                        <table class="table basic-datatable" id="table-devices">
                            <thead class="text-light">
                                <tr id="TRHeaderRepAnal" class="text-center" style="white-space: nowrap;">
                                    <th></th>
                                    <th class="py-1 px-0">Device</th>
                                    <th class="py-1 px-0">Manufacturer</th>
                                    <th class="py-1 px-0">Location</th>
                                    <th class="py-1 px-0">Staus</th>

                                </tr>
                            </thead>
                            <tbody id="device-body">

                            </tbody>
                        </table>
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->
    </div>
    <!-- end row -->
    <div class="row">
        <div class="col-12 col-lg-12 col-md-12 col-xl-4 col-xxl-4">
            <div class="card" style="height: 470px;" id="notif-summ">
                <div class="card-body">
                    <h4 class="header-title text-center mb-3">Notification Summary</h4>
                    <hr>
                    <div class="justify-content-start w-50">
                        {{-- <select name="" id="" class="form-select ">
                                <option value="">Last 30 days</option>
                            </select> --}}
                        @php
                            use Carbon\Carbon;

                            // Get the current year
                            $currentYear = Carbon::now()->year;

                            // Create Carbon instances for the first and last date of the current year
                            $firstDateOfYear = Carbon::create($currentYear, 1, 1)->format('m/d/Y');
                            $lastDateOfYear = Carbon::create($currentYear, 12, 31)->format('m/d/Y');
                        @endphp

                        <input type="text" class="form-control datepicker p-5 mb-3" data-toggle="date-picker"
                            data-range-picker="true" id="notif_date"
                            value="{{ $firstDateOfYear }} - {{ $lastDateOfYear }}" />


                    </div>
                    <div class="row">
                        <div class="col-12 col-xl-12">
                            <div class="d-flex justify-content-between align-items-center my-3">
                                <h3>New Notifications</h3>
                                <h3 id="new_notif">0</h3>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-3">
                                <h3>Resolved Notifications</h3>
                                <h3 id="res_notif">0</h3>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-3">
                                <h3>Unresolved</h3>
                                <h3 id="unres_notif">0</h3>
                            </div>
                        </div>
                        <div class="col-12 col-xl-12 d-none">
                            <div class="card border-2 border p-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center ">
                                        <i class="mdi mdi-close-circle-outline text-danger fs-1"></i>
                                        <h5>Critical</h5>
                                        <h5>0</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-12 col-md-12 col-xl-8 col-xxl-8">
            <div class="card">
                <div class="card-body" style="height: 470px;">
                    <h4 class="header-title text-center mb-3">Device Offline Incidents</h4>
                    <hr>
                    <div>
                        <div class=" d-flex gap-1">
                            <!-- Add these select fields to your HTML -->
                            <select id="monthSelect" class="form-select">
                                <!-- Options will be added dynamically -->
                            </select>
                            <select id="yearSelect" class="form-select">
                                <!-- Options will be added dynamically -->
                            </select>
                        </div>
                        <div id="chart-table1"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-lg-12">
            <div class="card" style="height: 470px;">
                <div class="card-body">
                    <h4 class="header-title text-center">Uptime By Device</h4>
                    <div class="hide">
                        <div class="d-flex justify-content-start " style="width: 300px;">
                            <div class="">
                                <input type="text" class="form-control datepicker" data-toggle="date-picker"
                                    data-range-picker="true" id="up_date" value="" style="min-width: 200px" />
                            </div>
                        </div>
                        <hr>
                        <h3 class="header-title text-left text-dark">Average Uptime</h3>
                        <div class="progress">

                        </div>
                        <div class="row px-2">
                            <div class="table-responsive mx-0 p-0 mt-2" style="height:320px !important; overflow-y:auto;">
                                <table id="basic-datatable1" class="table basic-datatable">
                                    <thead class="text-light">
                                        <tr id="TRHeaderRepAnal" class="text-center" style="white-space: nowrap;">
                                            <th data-value="Uptime" class="py-1 px-0" id="th1" style="">
                                                Uptime
                                            </th>
                                            <th data-value="Incidents" class="py-1 px-0" id="th2" style="">
                                                Incidents</th>
                                            <th data-value="Device_Name" class="py-1 px-0" id="th3"
                                                style="">
                                                Device Name</th>
                                            <th data-value="Model" class="py-1 px-0" id="th4" style="">Brand
                                                Name
                                            </th>
                                            <th data-value="RoomName" class="py-1 px-0" id="th5" style="">
                                                Room
                                                Name
                                            </th>
                                            <th data-value="Serial_Number" class="py-1 px-0" id="th5"
                                                style="">
                                                Serial Number
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbbody5">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="hide-item" id="reliable">
            <div class="py-2" style="min-width: 200px">
                @php
                    // use Carbon\Carbon;

                    // Get the current year
                    $currentYear = Carbon::now()->year;

                    // Create Carbon instances for the first and last date of the current year
                    $firstDateOfYear = Carbon::create($currentYear, 1, 1)->format('m/d/Y');
                    $lastDateOfYear = Carbon::create($currentYear, 12, 31)->format('m/d/Y');
                @endphp

                <input type="text" class="form-control datepicker p-5" data-toggle="date-picker"
                    data-range-picker="true" id="reliable_date" value="" />
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card" style="height: 470px;">
                <div class="card-body">
                    <h4 class="header-title text-center mb-3">Most Reliable Room</h4>
                    <hr>
                    <div class="">
                        <div class="row px-2">
                            <div class="table-responsive mx-0 p-0 mt-2" style="height:310px !important; overflow-y:auto;">
                                <table id="basic-datatablehey" class="table basic-datatable">
                                    <thead class="text-light">
                                        <tr id="TRHeaderRepAnal" class="text-center" style="white-space: nowrap;">

                                            <th data-value="Room1" class="py-1 px-0" id="th1" style="">Room
                                            </th>
                                            <th data-value="Active_Tickets1" class="py-1 px-0" id="th2"
                                                style=""> Uptime</th>
                                            <th data-value="Duration1" class="py-1 px-0" id="th3" style="">
                                                Location</th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbbody1">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-body" style="height: 470px;">
                    <h4 class="header-title text-center mb-3">Least Reliable Room</h4>
                    <hr>
                    <div class="">
                        <div class="row px-2">
                            <div class="table-responsive mx-0 p-0 mt-2" style="height:310px !important; overflow-y:auto;">
                                <table id="basic-datatable2" class="table basic-datatable">
                                    <thead class="text-light">
                                        <tr id="TRHeaderRepAnal" class="text-center" style="white-space: nowrap;">

                                            <th data-value="Room2" class="py-1 px-0" id="th1" style="">Room
                                            </th>
                                            <th data-value="Active_Tickets2" class="py-1 px-0" id="th2"
                                                style=""> Uptime</th>
                                            <th data-value="Duration2" class="py-1 px-0" id="th3" style="">
                                                Location</th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbbody2">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="offline_days">
    <div class="flippers-container">
        <div class="flippers d-flex">
            <div class=""><p class="p-2">E</p></div>
            <div class=""><p class="p-2">S</p></div>
            <div class=""><p class="p-2">C</p></div>
            <div class=""><p class="p-2">O</p></div>
            <div class=""><p class="p-2">3</p></div>
            <div class=""><p class="p-2">6</p></div>
            <div class=""><p class="p-2">0</p></div>
        </div>
    </div>
    {{-- @php
        $offline = $devices->count();
        $online = $devices->count();
    @endphp --}}
@endsection
@section('javascripts')
    <script src="Modules/assets/js/esco/room.js"></script>
    <script src="Modules/assets/js/esco/Chart.js"></script>
    <script src="Modules/assets/js/esco/RegionFilter.js"></script>
    <script src="Modules/assets/js/esco/offline.js"></script>
    <script src="Modules/assets/js/esco/notification.js"></script>
    <script src="Modules/assets/js/esco/uptime.js"></script>
    <script>
        // convert php data in json
        var online = {!! isset($online) ? json_encode($online) : 0 !!};
        var offline = {!! isset($offline) ? json_encode($offline) : 0 !!};

        var option1 = {
            chart: {
                height: 238,
                type: "donut"
            },
            legend: {
                show: false
            },
            stroke: {
                colors: ["transparent"],
            },
            series: [offline, online],
            labels: ["Offline", "Online"],
            colors: ['#E72929', '#41B06E'], // Custom colors for labels
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: "bottom"
                    }
                }
            }],
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Devices',
                                fontSize: '22px',
                                fontFamily: 'Calibri, "Helvetica Neue", Helvetica, Arial, sans-serif',
                                color: '#000000' // Set the font color to black
                            },
                        }
                    }
                }
            }
        };
        var chart1 = new ApexCharts(document.querySelector("#average-sales"), option1);
        // var chart2 = new ApexCharts(document.querySelector("#average-sales1"), option2);
        chart1.render();
        // chart2.render();


        // Function to update chart data and re-render
        function updateCharts(onlineData, offlineData) {
            // Update chart options with new data
            chart1.updateOptions({
                series: [offlineData, onlineData]
            });
        }

        function resetChart() {
            // Clear existing chart data
            chart1.updateSeries([]);
        }
    </script>
    <script>
        $(document).ready(function() {
            $(".flippers-container").addClass("fade-in");

            setTimeout(function() {
                $(".flippers-container").hide(); // Hide the scaling dots container after 5 seconds
            }, 3000); // 5000 milliseconds = 5 seconds
        });
    </script>
    {{-- <script>
        $(document).ready(function() {
            $(document).on("change", "input[type='radio'][name='device']", function() {
                resetChart(); // Reset the chart before updating
                checkOfflineIncident();
            });

            function resetChart() {
                $("#chart-table1").empty(); // Clear the chart container
            }

            function checkOfflineIncident() {
                var device = $("input[type='radio'][name='device']:checked").val();
                $.ajax({
                    url: "/get-offline-incident",
                    data: {
                        device: device
                    },
                    type: "get",
                    success: function(res) {
                        var chartData = res.offline_dates;
                        console.log(chartData);
                        if (chartData.length > 0) {
                            updateChart(chartData); // Call the function to update chartData
                        } else {
                            updateChart([]);
                        }
                    },
                    error: function() {
                        console.error("Error fetching offline incident data.");
                    },
                });
            }

            function updateChart(chartData) {
                var daysInMonth = 31; // Update with the actual number of days in the month
                var seriesData = [];
                console.log(chartData)
                for (var i = 1; i <= daysInMonth; i++) {
                    var date = new Date(2024, 4, i); // Year, Month (0-indexed), Day
                    var dateString = date.toISOString().split('T')[0];

                    // Check if the date has offline devices
                    if (chartData.includes(dateString)) {
                        seriesData.push({
                            x: date.getTime(),
                            y: 1
                        });
                    } else {
                        seriesData.push({
                            x: date.getTime(),
                            y: 0
                        });
                    }
                }

                var options = {
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded',
                            colors: {
                                ranges: [{
                                    from: 0,
                                    to: 1,
                                    color: '#ff0000'
                                }]
                            }
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        type: 'datetime',
                        labels: {
                            datetimeFormatter: {
                                year: 'yyyy',
                                month: 'MMM',
                                day: 'dd'
                            }
                        }
                    },
                    yaxis: {
                        show: false
                    }, // Hide the y-axis
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        enabled: true,
                        x: {
                            format: 'dd MMM yyyy'
                        }
                    }
                };

                options.series = [{
                    data: seriesData
                }];

                var chart = new ApexCharts(document.querySelector("#chart-table1"), options);
                chart.render();
            }

            // Initial call to update the chart
            // checkOfflineIncident();
        });
    </script> --}}
@endsection
