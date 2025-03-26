@extends('layouts.admin')
@section('content')

    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex">
                            <!-- <i class="uil uil-file fs-2 text-primary"></i> -->
                            <h4 class="header-title text-left p-0 mt-2 ms-2" style="font-size: 25px">
                                Company Profiles
                            </h4>
                        </div>
                        <div class="d-flex">
                            <!-- <button> -->
                            <i type="button" class="uil uil-file-plus fs-2 text-primary" id="AddCompProf" data-bs-toggle="modal"
                                data-bs-target="#event-modal"></i>
                            <!-- </button> -->
                            <div class="modal fade w-full mt-5" id="event-modal" tabindex="-1" >
                                <div class="modal-dialog" >
                                    <div class="modal-content" style="width:650px;">
                                        <div class="card">
                                            <div class="card-body">
                                                <form class="p-3" action="{{ route('add-profile') }}" method="post" enctype="multipart/form-data"
                                                    id="add-profile-form">
                                                    @csrf
                                                    <div id="basicwizard">

                                                        <ul class="nav nav-pills nav-justified form-wizard-header mb-4">
                                                            <li class="nav-item">
                                                                <a href="#basictab1" data-bs-toggle="tab" data-toggle="tab"
                                                                    class="nav-link rounded-0 pt-2 pb-2 active">
                                                                    <i class="mdi mdi-account-circle me-1"></i>
                                                                    <span class="d-none d-sm-inline">Company</span>
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="#basictab2" data-bs-toggle="tab" data-toggle="tab"
                                                                    class="nav-link rounded-0 pt-2 pb-2">
                                                                    <i class="mdi mdi-file me-1"></i>
                                                                    <span class="d-none d-sm-inline">Contract</span>
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="#basictab3" data-bs-toggle="tab" data-toggle="tab"
                                                                    class="nav-link rounded-0 pt-2 pb-2">
                                                                    <i
                                                                        class="mdi mdi-checkbox-marked-circle-outline me-1"></i>
                                                                    <span class="d-none d-sm-inline">Account</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                        <div class="tab-content b-0 mb-0">
                                                            <div class="tab-pane active" id="basictab1">
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <div class="row mb-3">
                                                                            <div class="col-12 d-flex">
                                                                            <label class=" col-md-3 col-form-label text-nowrap " for="Company_Name">
                                                                                Company Name</label>
                                                                            <input autocomplete="off" hidden type="text" autofocus class="form-control" id="Company_Id" name="Company_Id">
                                                                                <input autocomplete="off" type="text" autofocus  class="form-control text-uppercase" id="Company_Name" name="Company_Name">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                         <div class="col-12 d-flex">
                                                                            <label class="col-md-3 col-form-label text-nowrap" for="Company_Address">
                                                                                Company Address</label>
                                                                            <input autocomplete="off" type="text" id="Company_Address"  name="Company_Address" class="form-control text-uppercase">
                                                                            </div>
                                                                        </div>

                                                                        <div class="row mb-3">
                                                                        <div class="col-12 d-flex">
                                                                            <label class="col-md-3 col-form-label" for="Country">
                                                                                Country</label>
                                                                                <input autocomplete="off" type="text" id="Country"
                                                                                    name="Country" class="form-control text-uppercase">
                                                                            </div>
                                                                        </div>
                                                                    </div> <!-- end col -->
                                                                </div> <!-- end row -->
                                                            </div>

                                                            <div class="tab-pane" id="basictab2">
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <div class="row mb-3">
                                                                            <label
                                                                                class="col-md-3 col-form-label text-nowrap "
                                                                                for="Contract_Name">
                                                                                Contract Name</label>
                                                                            <div class="col-md-9">
                                                                                <input autocomplete="off" type="text" id="Contract_Name"
                                                                                    name="Contract_Name"
                                                                                    class="form-control text-uppercase" >
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <label
                                                                                class="col-md-3 col-form-label text-nowrap"
                                                                                for="Contract_Start_Date">
                                                                                Start Date</label>
                                                                            <div class="col-md-9">
                                                                                <input autocomplete="off" type="text"
                                                                                    name="Contract_Start_Date"
                                                                                    class="form-control date"
                                                                                    id="start_date"
                                                                                    data-toggle="date-picker"
                                                                                    data-single-date-picker="true">
                                                                            </div>
                                                                        </div>

                                                                        <div class="row mb-3">
                                                                            <label
                                                                                class="col-md-3 col-form-label text-nowrap"
                                                                                for="Contract_End_Date"> End Date</label>
                                                                            <div class="col-md-9">
                                                                                <input autocomplete="off" type="text"
                                                                                    name="Contract_End_Date"
                                                                                    class="form-control date"
                                                                                    id="end_date"
                                                                                    data-toggle="date-picker"
                                                                                    data-single-date-picker="true">
                                                                            </div>
                                                                        </div>
                                                                    </div> <!-- end col -->
                                                                </div> <!-- end row -->
                                                            </div>

                                                            <div class="tab-pane" id="basictab3">
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <div class="row mb-3">
                                                                        <div class="col-12 d-flex">
                                                                            <label class="col-md-4 col-form-label text-nowrap  " for="Account_Manager">
                                                                                Account Manager Name</label>
                                                                                <input autocomplete="off" type="text" id="Account_Manager" name="Account_Manager" class="form-control text-uppercase">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                        <div class="col-12 d-flex">
                                                                            <label class="col-md-4 col-form-label  text-nowrap " for="Account_Manager_Email">
                                                                                Account Manager Email</label>
                                                                                <input autocomplete="off" type="email"
                                                                                    id="Account_Manager_Email"
                                                                                    name="Account_Manager_Email"
                                                                                    class="form-control">
                                                                            </div>
                                                                        </div>
                                                                    </div> <!-- end col -->
                                                                </div>
                                                                <div class="float-end">
                                                                    <button class="btn btn-info"
                                                                        type="submit">Save</button>
                                                                </div> <!-- end row -->
                                                            </div>
                                                        </div> <!-- tab-content -->

                                                    </div> <!-- end #basicwizard-->
                                                </form>

                                            </div>
                                        </div>

                                    </div>
                                    <!-- end modal-content-->
                                </div>
                                <!-- end modal dialog-->
                            </div>
                        </div>
                    </div>
                    <hr />
                    <div class="col-md-12 col-12 col-xl-12 col-lg-12" id="company-table">
                        <div class="table-responsive" style="height:575px;">
                            <table id="basic-datatable" class="table basic-datatable"
                                style="max-height: 50px !important;">
                                <thead>
                                    <tr class="text-nowrap text-white text-center">
                                        <th>Company ID</th>
                                        <th>Company Name</th>
                                        <th>Company Address</th>
                                        <th>Country</th>
                                        <th>ESCO Account Manager</th>
                                        <th>ESCO Account Manager Email</th>
                                        <th class="text-center">Contract Name</th>
                                        <th>Contract Start Date</th>
                                        <th>Contract End Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($profiles as $index => $item)  
                                        <tr class=" text-nowrap">
                                        <td>{{ $item->Company_Id }}</td>
                                        <td>{{ $item->Company_Name }}</td>
                                        <td>{{ $item->Company_Address }}</td>
                                        <td>{{ $item->Country }}</td>
                                        <td>{{ $item->Account_Manager }}</td>
                                        <td>{{ $item->Account_Manager_Email }}</td>
                                        <td>{{ $item->Contract_Name }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($item->Contract_Start_Date)->format('F d, Y') }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($item->Contract_End_Date)->format('F d, Y') }}</td>
                                        <td class="text-center justify-content-center  d-flex" id="Row{{ $index }}" >
                                            <span onclick="EditAccount('{{$item->Company_Id}}')" id="Edit{{ $index }} data-value="{{$item->Company_Id}}" style="cursor: pointer;"><i style="font-size:20px;" class="mdi mdi-account-edit-outline ms-1"></i></span> 
                                            <span onclick="DeleteAcc('{{$item->Company_Id}}')" id="Delete{{ $index }} data-value="{{$item->Company_Id}}" style="cursor: pointer;"><i style="font-size:20px;" class="mdi mdi-delete-outline"></i></span> 
                                        </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-alert />
@endsection
@section('javascripts')
    <script src="Modules/assets/js/esco/companyProfile.js?1716948656661"></script>
@endsection
