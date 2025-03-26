@extends('layouts.admin')
@section('content')
    <!-- Modal HTML Session time out -->
    <x-successNotif />
    @if (session('message'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    {{-- <div id="success-header-modal" data-bs-backdrop="static" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="success-header-modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header modal-colored-header bg-success">
                    <h4 class="header-title m-0" style="letter-spacing: 0.05em;">Disable Session Expire</h4>
                    <button type="button" style="opacity: .00001;pointer-events: none;" class="btn-close" id="dismiss" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <form>
                    @csrf
                    <div class="modal-body">
                        <p style="font: 400; font-weight:bold">Do you want to enable the no session timeout.<br>Enter pin to proceed.</p>
                        <span>Enter pin:</span>
                        <div>
                            <input type="password" name="sessionPassword" id="sessionPassword" class="form-control"> 
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="Close-Modal" class="btn btn-light" data-bs-dismiss="modal">No</button>
                        <span id="SaveField" class="btn btn-success">Save changes</span>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal --> --}}
    <form action="/createLogFile" method="POST">
        @csrf
        <button type="submit" hidden id="TriggerLogFile"></button>
    </form>
    <form >
        @csrf
        <span type="submit" hidden id="TriggerUpdateLogFile"></span>
    </form>
    <!-- start page title -->
   
      
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <form class="d-flex">
                        <div class="input-group">
                            <!-- Custom Switch -->
                            @if (auth()->user()->usertype == 0)
                            <!-- Content for users with usertype == 0 can be placed here -->
                            @else
                                <div class="form-check form-switch">
                                    <input style="width: 2.4em" type="checkbox" value="no" class="form-check-input" id="SessionExpire">&nbsp;
                                    <label class="form-check-label" for="SessionExpire">Session Expire</label>
                                </div>
                            @endif
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
                        @if (empty($CompanyProfiles))
                            <option value="" disabled>No data available</option>
                        @else
                            @foreach ($CompanyProfiles as $CompanyProfiles)
                                @if (!in_array($CompanyProfiles->Country, $processedCountries))
                                    <option value="{{ $CompanyProfiles->Country }}">{{ $CompanyProfiles->Country }}</option>
                                    @php
                                        $processedCountries[] = $CompanyProfiles->Country;
                                    @endphp
                                @endif
                            @endforeach
                        @endif
                        </select>
                    </div>
                    <div class="d-flex">
                        <h4 class="header-title text-left p-0 mt-2 me-2">Organization</h4>
                        <select class="form-select  ms-3" id="regionOrgs">
                            <option selected value="">Select Organization</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row">
        <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-12 col-12 col-sm-12">
            <div class="card card-h-100">
                <div class="card-body">
                    <h4 class="header-title text-center mb-3">Available Rooms</h4>
                    <hr>
                    <div id="hideAvailableRooms">
                    <div class="table-responsive" style="height: 350px; overflow-y: auto; overflow-x: auto;">
                    <table class="table basic-datatable mb-0" id="table-room">
                        <thead style="background-color:#f1f3fa;">
                        </thead>
                        <tbody id="roomBody">
                        @if($DeviceRoom->isNotEmpty())
                            @foreach($DeviceRoom as $DeviceRoom)
                            <tr>
                                <td>
                                    <div class="form-check form-checkbox-success mb-2">
                                        <input type="checkbox" checked class="form-check-input ckbox" name="roomID1" value={{$DeviceRoom->DeviceRoomID}}>
                                    </div>
                                </td>
                                <td style="whitespace:nowrap">{{$DeviceRoom->DeviceRoomName}}</td>
                            </tr>
                            @endforeach
                        @else
                            <p class="text-danger" id="room-message">No data available.</p>
                        @endif
                        </tbody>
                    </table>
                    </div>
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->

        <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-12 col-12 col-sm-12">
            <div class="card card-h-100">
                <div class="card-body" id="all-data">
                    <h4 class="header-title text-center">Device Status</h4>
                    <hr>
                    <div id="hideDeviceStatus">
                        <div id="dstats" class="apex-charts" data-colors="#fa5c7c,#0acf97"></div>
                        <div class="row mt-2">
                            <div class="col-md-4 col-lg-4 col-4 ">
                                <p style="margin-bottom:.5rem;">
                                    <i class="mdi mdi-square text-success"></i> Online
                                    <span class="float-end" id="on-count">{{ isset($online) ? $online : 0 }}</span>
                                </p>
                                <p style="margin-bottom:.5rem;">
                                    <i class="mdi mdi-square" style="color:#54c081;"></i> OK
                                    <span class="float-end" id="OK-count">{{ isset($OK) ? $OK : 0 }}</span>
                                </p>
                                <p style="margin-bottom:.5rem;">
                                    <i class="mdi mdi-square" style="color: #e72929;"></i> Offline
                                    <span class="float-end" id="off-count">{{ isset($offline) ? $offline : 0 }}</span>
                                </p>
                            </div>
                            <div class="col-md-4 col-lg-4 col-4 ">
                                <p style="margin-bottom:.5rem;">
                                    <i class="mdi mdi-square" style="color:#2e95e4;"></i> Initializing
                                    <span class="float-end" id="Initializing-count">{{ isset($Initializing) ? $Initializing : 0 }}</span>
                                </p> <p style="margin-bottom:.5rem;">
                                    <i class="mdi mdi-square" style="color:#edc600;"></i> Compromised
                                    <span class="float-end" id="Compromised-count">{{ isset($Compromised) ? $Compromised : 0 }}</span>
                                </p> <p style="margin-bottom:.5rem;">
                                    <i class="mdi mdi-square" style="color:#a925ff;"></i> Fault
                                    <span class="float-end" id="Fault-count">{{ isset($Fault) ? $Fault : 0 }}</span>
                                </p>
                            </div>
                            <div class="col-md-4 col-lg-4 col-4 ">
                                <p style="margin-bottom:.5rem;">
                                    <i class="mdi mdi-square" style="color:#f96868;"></i> Missing
                                    <span class="float-end" id="Missing-count">{{ isset($Missing) ? $Missing : 0 }}</span>
                                </p>
                                <p style="margin-bottom:.5rem;">
                                    <i class="mdi mdi-square" style="color:#acadae;"></i> Unknown
                                    <span class="float-end" id="Unknown-count">{{ isset($Unknown) ? $Unknown : 0 }}</span>
                                </p>
                                <p style="margin-bottom:.5rem;">
                                    <i class="mdi mdi-square" style="color:#acadae;"></i> Not Present
                                    <span class="float-end" id="NotPresent-count">{{ isset($NotPresent) ? $NotPresent : 0 }}</span>
                                </p>
                            </div>
                        </div>
                    </div> <!-- end card-body-->
                </div>
            </div> <!-- end card-->
        </div> <!-- end col -->

        <div class="col-xxl-4 col-xl-12 col-lg-12 col-md-12 col-12 col-sm-12">
            <div class="card" style="height: 470px;" id="notif-summ">
                <div class="card-body">
                    <h4 class="header-title text-center mb-3">Notification Summary</h4>
                    <hr>
                    <div id="hideNotifSummary">
                    <div class="justify-content-start w-50">
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
                                <h3 id="new_notif">{{isset($NewNotif->new_count) ?  $NewNotif->new_count : ''}}</h3>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-3">
                                <h3>Resolved Notifications</h3>
                                <h3 id="res_notif">{{isset($ResolvedNotif->resolved_count) ? $ResolvedNotif->resolved_count : ''}}</h3>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-3">
                                <h3>Unresolved</h3>
                                <h3 id="unres_notif">{{isset($UnresolvedNotif->unresolved_count) ? $UnresolvedNotif->unresolved_count : ''}}</h3>
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
        </div> <!-- end col -->
    </div>
    <!-- end row -->
    <div class="row">
        <div class="col-12 col-lg-12 col-md-12 col-xl-12 col-xxl-6">
        <div class="card">
                <div class="card-body" style="height: 470px;">
                    <h4 class="header-title text-center">Devices</h4> 
                    <hr>
                    <div id="hideDevicesTable">
                        <div class="table-responsive" style="height: 350px; overflow-y: auto; overflow-x: auto;">
                            <table class="table basic-datatable" id="table-devices">
                                <thead class="text-light">
                                    <tr id="TRHeaderRepAnal" class="text-center" style="white-space: nowrap;">
                                        <th></th>
                                        <th class="py-1 px-0">Device</th>
                                        <th class="py-1 px-0">Device Description</th>
                                        <th class="py-1 px-0">Manufacturer</th>
                                        <th class="py-1 px-0">Location</th>
                                        <th class="py-1 px-0">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="device-body">
                                @if (empty($Devices))
                                <tr>
                                    <td class="" style="white-space:nowrap;color:black;">No Devices Found</td>
                                </tr>
                                @else
                                    @foreach($Devices as $Devices)
                                    <tr>
                                        <td>
                                            <div class="form-check form-checkbox-success mb-2">
                                                <input type="radio" id="" value={{$Devices->Device_Id}} name="DevicesRadio" class="form-check-input dr ">
                                            </div>
                                        </td>
                                        <td style="whitespace:nowrap;color:black;">{{$Devices->Device_Name}}</td>
                                        <td style="whitespace:nowrap;color:black;">{{$Devices->Device_Desc}}</td>
                                        <td style="whitespace:nowrap;color:black;">{{$Devices->Manufacturer}}</td>
                                        <td style="white-space:nowrap;color:black;">{{$Devices->Device_Loc}}</td>
                                        <td class="emedu" style="white-space:nowrap;color:black;">{{$Devices->Status}}</td>
                                    </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div>
        <div class="col-12 col-lg-12 col-md-12 col-xl-12 col-xxl-6">
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
        <div class="" id="upbydev">
            <div class="col-12 col-lg-12">
                <div class="card" style="height: 470px;">
                    <div class="card-body">
                            <div class="row"> 
                                <div class="col-12 col-sm-12 col-md-5 col-lg-5 col-xl-5 col-xxl-5">
                                    <input type="text" class="form-control datepicker" data-toggle="date-picker" data-range-picker="true" id="up_date" value="" style="min-width: 200px; width: 300px;" />
                                </div>
                                <div class="col-12 ms-4 mt-2 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">                                
                                    <h4 class="header-title align-items-center">Uptime By Device</h4>
                                </div>
                            </div>
                            <hr>
                    
                            <h3 class="header-title text-left text-dark">Average Uptime</h3>
                            <div class="progress">
                                <div class='progress-bar bg-info' id='aveuptime' role='progressbar' 
                                style='max-height:30px;width:{{isset($ave) ? $ave : 0}}%' aria-valuenow='{{isset($ave) ? $ave : 0}}' aria-valuemin='" + 0 + "' aria-valuemax='100'>
                            {{isset($ave) ? $ave : 0}}%</div>
                            </div>
                            <div class="row px-2">
                                <div class="table-responsive mx-0 p-0 mt-2" style="height:280px !important; overflow-y:auto;">
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
                                                <th data-value="IP_Address" class="py-1 px-0" id="th6"
                                                    style="">
                                                    IP Address
                                                </th>
                                                <th data-value="Serial_Number" class="py-1 px-0" id="th7"
                                                    style="">
                                                    Serial Number
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbbody5">
                                            @if(empty($data))
                                                <p>No Data</p>
                                            @else
                                                @foreach($data as $data)
                                                <tr class="text-center" style="color:black;white-space: nowrap;"> 
                                                    <td class="text-center">{{$data['Uptime']}}</td>
                                                    <td class="text-center">{{$data['Incidents']}}</td>
                                                    <td>{{$data['Device_Name']}}</td>
                                                    <td>{{$data['Manufacturer']}}</td>
                                                    <td>{{$data['Room_Type']}}</td>
                                                    <td>{{$data['IP_Address']}}</td>
                                                    <td>{{$data['Serial_Number']}}</td>
                                                </tr>
                                                @endforeach
                                            @endif
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
            <div class="card" style="height: 470px;" id="MRR">
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
            <div class="card" id="LRR">
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
@endsection
@section('javascripts')
    <script src="Modules/assets/js/esco/room.js?1729651730932"></script>
    <script src="Modules/assets/js/esco/offline.js"></script>
    <script src="Modules/assets/js/esco/SessionExpire.js"></script>
   
    <script>

        // convert php data in json
    var online = {!! isset($online) ? json_encode($online) : 0 !!};
    var offline = {!! isset($offline) ? json_encode($offline) : 0 !!};
    var OK = {!! isset($OK) ? json_encode($OK) : 0 !!};
    var Missing = {!! isset($Missing) ? json_encode($Missing) : 0 !!};
    var Unknown = {!! isset($Unknown) ? json_encode($Unknown) : 0 !!};
    var Fault = {!! isset($Fault) ? json_encode($Fault) : 0 !!};
    var Initializing = {!! isset($Initializing) ? json_encode($Initializing) : 0 !!};
    var Compromised = {!! isset($Compromised) ? json_encode($Compromised) : 0 !!};
    var NotPresent = {!! isset($NotPresent) ? json_encode($NotPresent) : 0 !!};


var option1 = {
    chart: {
        height: 288,
        
        type: "donut"
    },
    legend: {
        show: false
    },
    stroke: {
        colors: ["transparent"],
    },
    series: [offline, online,OK,Missing,Unknown,Fault,Initializing,Compromised,NotPresent],
    labels: ["Offline", "Online","OK","Missing","Unknown","Fault","Initializing","Compromised","NotPresent"],
    colors: ['#e72929', '#41B06E','#54c081','#f96868', '#acadae','#a925ff','#2e95e4','#edc600','#acadae'], // Custom colors for labels
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: '4000%'
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
                },
                 width: '120%', // Adjust the width of the donut chart
            }
        }
    }
};
        var chart1 = new ApexCharts(document.querySelector("#dstats"), option1);
        chart1.render();
        // Function to update chart data and re-render
        function updateCharts(onlineData, offlineData,OK,Missing,Unknown,Fault,Initializing,Compromised,NotPresent) {
            // Update chart options with new data
            chart1.updateOptions({
                series: [offlineData, onlineData,OK,Missing,Unknown,Fault,Initializing,Compromised,NotPresent]
            });
        }

        function resetChart() {
            chart1.updateSeries([]);
        }

        function uhooCreateHistory(){
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            console.log("uhooCreateHistory went here");
            $.ajax({
                type: "GET",
                url: "{{ route('uhoo_create_history') }}", 
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                contentType: "application/json; charset=utf-8",
                data: {
                   
                },
                dataType: "json",
                success: function (response) {
                    console.log('inserted a history');
                    //console.log(response.NewData);
                },
                error: function (xhr, status, error) {
                    console.error('Error:',xhr,status, error,);
                }
            });
        }

     function checkAndRun() {
            let minutes = new Date().getMinutes();
            if (minutes % 5 === 0) {
                uhooCreateHistory();
                console.log('uhooCreateHistory executed');
            }
        }
     setInterval(checkAndRun, 30000);
    </script>
    

@endsection
