@extends('layouts.admin')
@section('content')
    <!-- Modal HTML -->
    <div id="success-header-modal" data-bs-backdrop="static" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="success-header-modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header modal-colored-header bg-success">
                    <h4 class="modal-title" id="success-header-modalLabel">Update</h4>
                    <button type="button" class="btn-close b-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to override the exisitng data? Click "Save changes" to proceed otherwise close.
                </div>
                <div class="modal-footer">
                    <button type="button" id="Close-Modal" class="btn btn-light b-close" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="SaveField" class="btn btn-success">Save changes</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="Modal-Enter-Pass" data-bs-backdrop="static" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal-Enter-Pass-modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header modal-colored-header bg-success">
                    <h4 class="modal-title" id="success-header-modalLabel">Validation</h4>
                    <button type="button" class="btn-close b-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <form action="{{route("pwvalidation")}}" method="POST" id="form-pwvalidation">
                    @csrf
                    <div class="modal-body">
                            <label for="valida" class="form-label">Enter your password: </label>
                            <input type="password" name="validationpw" id="validationpw" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="Close-Modal" class="btn btn-light b-close" data-bs-dismiss="modal">Close</button>
                        <button  type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

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
                <h4 class="page-title">Reports</h4>
            </div>
        </div>
    </div>
    <!-- Start Content Here-->
    <!-- large display block small display hide-->
    <div class="row d-none d-lg-block">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body d-flex">
                    <div class="  d-flex">
                        <h4 class="header-title text-left p-0 mt-2 me-2">Region</h4> 
                        <select class="form-select text-uppercase ms-3 me-4" id="RegionId"  style=" border-radius: 5px; width:280px;">
                            <option style="  border-radius: 0px;" value="" disable selected></option>
                            @foreach (array_unique($region->pluck('Country')->toArray()) as $Country)
                                <option style="border-radius: 0px;" value="{{ $Country }}">{{ $Country }}</option>
                            @endforeach
                            <!-- @foreach ($region as $RegionOrg)
                                <option style="  border-radius: 0px;" value="{{ $RegionOrg->Country  }}"> {{ $RegionOrg->Country }}</option>
                            @endforeach   -->
                        </select>
                    </div>
                    <div class="d-flex">
                        <h4 class="header-title text-left p-0 mt-2 me-2">Organization</h4>
                        <select class="form-select text-uppercase  ms-3" id="OrganizationId"  style="  border-radius: 5px; width:280px;">
                            <option style="  border-radius: 0px;" value="" disable selected></option>
                          <!-- @foreach ($region->unique('Company_Name') as $RegionOrg)
                                <option style="  border-radius: 0px;" value="{{ $RegionOrg->Company_Name }}"> {{ $RegionOrg->Company_Name }}</option>
                            @endforeach  -->
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
                            <select class="form-select text-uppercase ">
                                <option value=""></option>
                                 @foreach ($region as $RegionOrg)
                                    <option value="{{ $RegionOrg->Company_Id }}"> {{ $RegionOrg->Country }}</option>
                                @endforeach 

                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="d-flex mt-1  justify-content-between">
                            <h4 class="header-title text-left p-0 mt-2 me-2">Organization</h4>
                            <select class="form-select text-uppercase" style="margin-left: 4px;">
                                 @foreach ($region->unique('Company_Name') as $RegionOrg)
                                    <option value="{{ $RegionOrg->Company_Id }}"> {{ $RegionOrg->Company_Name }}</option>
                                @endforeach 
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <span class="p-0 float-end pointer-cursor" id="exportBtn1">Export to CSV <i class="mdi mdi-download ms-1"></i></span>
                    <span class="p-0 float-end hide-item pointer-cursor" id="exportBtn2">Export to CSV <i class="mdi mdi-download ms-1"></i></span>

                    <form action="">
                        <div id="basicwizard">
                            <ul class="nav nav-pills nav-justified form-wizard-header mb-4"
                                style="background-color: rgb(255, 255, 255);">
                                <li class="nav-item" style="max-width: 400px !important;">
                                    <div class="d-flex g-2" style="width: 400px !important;">
                                        <i class="dripicons-graph-bar fs-2 text-success"></i>
                                        <h4 class="header-title text-left p-0 mt-2 ms-2" style="font-size: 25px;">Reports Analytics</h4>
                                    </div>
                                </li>
                                <li class="nav-item DevStatus disable-standard"  id="DevStats" style="max-width: 300px !important;">
                                    <a href="#basictab1"  id="tab1" data-bs-toggle="tab" data-toggle="tab"
                                        class=" text-start nav-link rounded-0 pt-2 pb-2">
                                        <i class="dripicons-device-desktop me-1" style="font-size: 15px;"></i>
                                        <span   class="d-none d-sm-inline disable-standard" style="font-size: 15px;">Device Status</span>
                                    </a>
                                </li>
                                <li class="nav-item disable-standard" onclick="AlertNotif()" id="alertnotifs" style="max-width: 300px !important;">
                                    <a href="#basictab2" id="tab2" data-bs-toggle="tab" data-toggle="tab"
                                        class="text-start nav-link rounded-0 pt-2 pb-2">
                                        <i class=" dripicons-warning me-1" style="font-size: 15px;"></i>
                                        <span   class="d-none d-sm-inline " style="font-size: 15px;">Alert Notification</span>
                                    </a>
                                </li>
                            </ul>
                            
                            <div class="tab-content b-0 mb-0 hide-item" id="ReportsContent">
                                <div class="tab-pane" id="basictab1">
                                    <div class="row">
                                        <div class="col-md-12 col-xl-12 col-lg-12" >
                                            @if (count($devices) <= 0)
                                                <p class="text-danger">No data available.</p>
                                            @else
                                            <div class="row">
                                                <div class="col-md-4 col-12 col-lg-4 col-xl-4 col-xxl-4">
                                                <input autocomplete="off" style="height: 35px; width:300px;" type="text" id="DevSearch"   class="form-control mb-2 ">
                                                </div>

                                            </div>
                                            <div class="table-responsive"  style="height: 470px !important; overflow-y:auto;">
                                                <table id="basic-datatable"  class="table" >
                                                    <thead class="text-light">
                                                        <tr id="TRHeaderRepAnal" class="text-center"
                                                            style="white-space: nowrap;">
                                                            <th data-value="Device_Loc" id="th1">Device Location<i
                                                                    class="dripicons-chevron-down me-1" id="DL0"
                                                                    style="font-size: 15px;"></i></th>
                                                            </th>
                                                            <th data-value="Device_Name" id="th2">Device Name <i
                                                                    class="dripicons-chevron-down me-1" id="DL1"
                                                                    style="font-size: 15px;"></i>  </th>
                                                            <th data-value="Device_Desc" id="th3">Device Description <i
                                                                    class="dripicons-chevron-down me-1" id="DL2"
                                                                    style="font-size: 15px;"></i>  </th>
                                                            </th>
                                                            <th data-value="Room_Type" id="th4">Room Type <i
                                                                    class="dripicons-chevron-down me-1" id="DL3"
                                                                    style="font-size: 15px;"></i>  </th>
                                                            </th>
                                                            <th data-value="Manufacturer" id="th5">Manufacturer <i
                                                                    class="dripicons-chevron-down me-1" id="DL4"
                                                                    style="font-size: 15px;"></i>  </th>
                                                            </th>
                                                            <th data-value="IP_Address" id="th9">IP Address <i
                                                                    class="dripicons-chevron-down me-1" id="DL8"
                                                                    style="font-size: 15px;"></i>  </th>
                                                            </th>
                                                            <th data-value="Serial_Number" id="th6">Serial Number <i
                                                                    class="dripicons-chevron-down me-1" id="DL5"
                                                                    style="font-size: 15px;"></i></th>
                                                            </th>
                                                            <th data-value="Mac_Address" id="th7">Mac Address <i
                                                                    class="dripicons-chevron-down me-1" id="DL6"
                                                                    style="font-size: 15px;"></i> </th>
                                                            </th>
                                                            <th data-value="Status" id="th8">Status <i
                                                                    class="dripicons-chevron-down me-1" id="DL7"
                                                                    style="font-size: 15px;"></i>  </th>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbbody1">
                                          
                                                      
                                                    </tbody>
                                                </table>
                                                </div>
                                                @endif
                                            {{-- @endif --}}
                                        </div>
                                    </div> <!-- end row -->
                                </div>

                      
                                <div class="tab-pane" id="basictab2">
                                <div class="col-md-12 col-12 col-lg-12 col-xxl-12 col-xl-12 d-flex justify-content-end">
                                <input type="text" class="form-control datepicker-range me-2 mb-2" data-toggle="date-picker" data-range-picker="true" id="TDates" value="" style="min-width: 200px; width: 300px;" />
                                    <select class="form-select text-uppercase mb-2" id="TicketStats"  style=" border-radius: 5px; width:280px;">
                                      <option value=""></option>
                                    </select>
                            </div>
                                <div class="row">
                                    <div class="col-12">
                                    <div class="table-responsive"  style="height: 470px !important; overflow-y:auto;">
                                        <table id="basic-datatable1"  class="table basic-datatable" > 
                                            <thead class="text-light">
                                                <tr>
                                                    <th>Severity</th>
                                                    <th>Ticket ID</th>
                                                    <th>Component</th>
                                                    <th>Alert Message</th>
                                                    <th>Date Last Detected Online</th>
                                                    <th>Ticket Created</th>
                                                    <th>Lapse Time</th>
                                                    <th>Ticket Resolved</th>
                                                    <th>Status</th>

                                                </tr>
                                            </thead>
                                            <tbody id="tbbody2">
                                               
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                </div>
                             

                            </div> <!-- tab-content -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascripts')
    <script src="Modules/assets/js/esco/ReportsAnalytics.js?1728883503393"></script>
    <script src="Modules/assets/js/esco/SessionExpire.js?1728883503393"></script>
    <script src="Modules/assets/js/esco/refresh-device.js?1728883503393"></script>
        <!-- bundle -->
        {{-- <script src="Modules/assets/js/vendor.min.js?1728883503393"></script>
        <script src="Modules/assets/js/app.min.js?1728883503393"></script> --}}

        <script src="https://cdn.jsdelivr.net/npm/table2csv@1.1.6/src/table2csv.min.js"></script>
        <script>
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