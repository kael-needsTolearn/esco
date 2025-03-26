@extends('layouts.admin')
@section('content')
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex">
                            <i class="mdi mdi-account-group fs-2 text-primary"></i>
                            <h4 class="header-title text-left p-0 mt-2 ms-2" style="font-size: 25px">
                                User Accounts
                            </h4>
                        </div>
                        <div class="d-flex">
                            <!-- <button> -->
                            {{-- <i type="button" class="mdi mdi-account-plus fs-2 text-primary" data-bs-toggle="modal"
                                data-bs-target="#event-modal"></i> --}}
                            <a href="/register">

                                <i type="button" class="mdi mdi-account-plus fs-2 text-primary">

                                </i>
                            </a>
                            <!-- modal -->
                            <div class="modal fade" id="event-modal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form class="needs-validation" name="event-form" id="add-api-form" novalidate=""
                                            method="POST" action="{{ route('add-api') }}">
                                            @csrf
                                            <div class="modal-header py-3 px-4 border-bottom-0">
                                                <h5 class="modal-title" id="modal-title">
                                                    API Account
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body px-4 pb-4 pt-0">
                                                <div class="row">
                                                    {{-- <div class="col-12">
                                                        <div class="mb-3">
                                                            <label class="control-label form-label">Company Name</label>
                                                            <select name="company_id" id="company_id" class="form-select">
                                                                <option selected disabled>Select Company</option>
                                                                @foreach ($profiles as $item)
                                                                    <option value="{{ $item->Company_ID }}">
                                                                        {{ $item->Company_Name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <!-- <input class="form-control" placeholder="Insert Event Name" type="text" name="title" id="event-title" required=""> -->
                                                            <div class="invalid-feedback">
                                                                Please select platform
                                                            </div>
                                                        </div>
                                                    </div> --}}
                                                    <div class="col-12">
                                                        <div class="mb-3">
                                                            <label class="control-label form-label">Platform</label>
                                                            <select name="Platform" id="platform_name" class="form-control">
                                                                <option value="xio">Crestron</option>
                                                                <option value="qsys">Q-Sys</option>
                                                                <option value="eutelogy">
                                                                    Eutelogy
                                                                </option>
                                                            </select>
                                                            <!-- <input class="form-control" placeholder="Insert Event Name" type="text" name="title" id="event-title" required=""> -->
                                                            <div class="invalid-feedback">
                                                                Please select platform
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="mb-3">
                                                            <label class="control-label form-label">Description</label>
                                                            <textarea class="form-control" name="Description" id="Description" cols="30" rows="3"></textarea>
                                                            <!-- <input class="form-control" placeholder="Insert Event Name" type="text" name="title" id="event-title" required=""> -->
                                                            <div class="invalid-feedback">
                                                                Please select desctiption
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="xio-div">
                                                        <div class="col-12">
                                                            <div class="mb-3">
                                                                <label class="control-label form-label">XiO API Key</label>
                                                                <input type="text" class="form-control"
                                                                    name="variable1" />
                                                                <div class="invalid-feedback">
                                                                    Please input a valid api key
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="mb-3">
                                                                <label class="control-label form-label">XiO API Account
                                                                    ID</label>
                                                                <input type="text" class="form-control"
                                                                    name="variable2" />
                                                                <div class="invalid-feedback">
                                                                    Please input a valid api account id
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="qsys-div" class="d-none">
                                                        <div class="col-12">
                                                            <div class="mb-3">
                                                                <label class="control-label form-label">Q-Sys Bearer
                                                                    Key</label>
                                                                <input type="text" class="form-control"
                                                                    name="variable3" />
                                                                <div class="invalid-feedback">
                                                                    Please input a valid api key
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="float-end pb-2">
                                                    <!-- <div class="col-6">
                                                                                                <button
                                                                                                  type="button"
                                                                                                  class="btn btn-danger"
                                                                                                  id="btn-delete-event"
                                                                                                >
                                                                                                  Delete
                                                                                                </button>
                                                                                              </div> -->
                                                    <div class="d-flex text-end">
                                                        <button type="button" class="btn btn-light me-1"
                                                            data-bs-dismiss="modal">
                                                            Close
                                                        </button>
                                                        <button type="submit" class="btn btn-success" id="btn-save-event">
                                                            Save
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- end modal-content-->
                                </div>
                                <!-- end modal dialog-->
                            </div>
                            <!-- end modal -->
                        </div>
                    </div>
                    <hr />
                    <div class="col-md-12 col-xl-12 col-lg-12" id="account-table">
                        <div class="table-responsive" style="height: 580px !important; overflow-y:auto;">
                            <table id="basic-datatable" class="table">
                                <thead class="text-center text-white">
                                    <tr>
                                        <th>Email</th>
                                        <th>User Type</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">
                               @if($UserType ==2)
            
                                    @foreach ($users as $index => $user)
                                        @if ($index % 2 == 0)
                                            <tr class="table-active" style="color:black;white-space: nowrap;">
                                            @else
                                            <tr class="" style="color:black;white-space: nowrap;">
                                        @endif
                                        <td>{{ $user->email }}</td>

                                        @if($user->usertype == 0 )
                                        <td>CLIENT</td>
                                        @elseif ($user->usertype == 2)
                                        <td>SUPER ADMIN</td>
                                        @elseif($user->usertype == 1)                   
                                        <td>ADMIN</td>
                                        @else
                                        <td>No assigned usertype</td>
                                        @endif

                                        <td>{{ strtoupper($user->Position) }}</td>
                                        @if ($user->Status == 'active'||$user->Status == 'Active' )
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-success px-2 py-1">
                                                    {{ $user->Status }}</span>
                                            </td>
                                        @else
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-danger px-2 py-1">
                                                    {{ $user->Status }}</span>
                                            </td>
                                        @endif
                                        @if (Auth::user()->id == $user->id)
                                            <td>
                                                <span class="badge bg-success px-2 py-1">
                                                    current
                                                </span>
                                            </td>
                                        @else
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    @if($user->Status == 'active')
                                                    <span id="edit-acc" data-value="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#UpdateSysCon" class=" disable-standard">
                                                        <i style="font-size:20px;" class="mdi mdi-account-reactivate-outline ms-1"></i>
                                                    </span>
                                                    <span id="delete-acc" class="pointer-cursor"data-value="{{ $user->id }}"><i style="font-size:20px;" class="mdi mdi-delete-outline"></i></span>
                                                    @else
                                                    <span id="edit-acc" data-value="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#UpdateSysCon" class=" pointer-cursor">
                                                        <i style="font-size:20px;" class="mdi mdi-account-reactivate-outline ms-1"></i>
                                                    </span>
                                                    <span id="delete-acc" class="disable-standard" data-value="{{ $user->id }}"><i style="font-size:20px;" class="mdi mdi-delete-outline"></i></span>
                                                    @endif
                                                    <span id="update-acc" data-bs-toggle="modal" data-bs-target="#update-user" class="pointer-cursor" data-value="{{ $user->id }}"><i style="font-size:16px;" class="dripicons-document-edit"></i></span>

                                                </div>
                                            </td>
                                        
                                        @endif
                                        </tr>
                                    @endforeach
                                @else
                                @foreach ($users as $index => $user)
                                        @if ($index % 2 == 0)
                                            <tr class="table-active" style="color:black;white-space: nowrap;">
                                            @else
                                            <tr class="" style="color:black;white-space: nowrap;">
                                        @endif
                                        <td>{{ $user->email }}</td>

                                        @if($user->usertype == 0 )
                                        <td>CLIENT</td>
                                        @elseif ($user->usertype == 2)
                                        <td>SUPER ADMIN</td>
                                        @elseif($user->usertype == 1)                   
                                        <td>ADMIN</td>
                                        @else
                                        <td>No assigned usertype</td>
                                        @endif

                                        <td>{{ strtoupper($user->Position) }}</td>
                                        @if ($user->Status == 'active'||$user->Status == 'Active' )
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-success px-2 py-1">
                                                    {{ $user->Status }}</span>
                                            </td>
                                        @else
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-danger px-2 py-1">
                                                    {{ $user->Status }}</span>
                                            </td>
                                        @endif
                                        @if (Auth::user()->id == $user->id)
                                            <td>
                                                <span class="badge bg-success px-2 py-1">
                                                    current
                                                </span>
                                            </td>
                                        @else
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    @if($user->Status == 'active')
                                                    <span id="edit-acc" data-value="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#UpdateSysCon" class=" disable-standard">
                                                        <i style="font-size:20px;" class="mdi mdi-account-reactivate-outline ms-1"></i>
                                                    </span>
                                                    <span id="delete-acc" class="pointer-cursor"data-value="{{ $user->id }}"><i style="font-size:20px;" class="mdi mdi-delete-outline"></i></span>
                                                    @else
                                                    <span id="edit-acc" data-value="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#UpdateSysCon" class=" pointer-cursor">
                                                        <i style="font-size:20px;" class="mdi mdi-account-reactivate-outline ms-1"></i>
                                                    </span>
                                                    <span id="delete-acc" class="disable-standard" data-value="{{ $user->id }}"><i style="font-size:20px;" class="mdi mdi-delete-outline"></i></span>
                                                    @endif

                                                </div>
                                            </td>
                                        
                                        @endif
                                        </tr>
                                    @endforeach
                                @endif



                                    <!-- /.modal-content -->
                                            <div id="update-user" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">

                                                        <div class="modal-body">
                                                            <div class="text-center mt-2 mb-4">
                                                                <a href="index.html" class="text-success">
                                                                    <span style="font-size:25px;">Update User Account</span>
                                                                </a>
                                                            </div>
                                                            <form method="POST"  id="UpdateTheUser">

                                                            <div class=" mt-4 mb-1">
                                                                <label for="FirstName" class="text-nowrap me-3">First Name</label>
                                                                <input autocomplete="off" class="form-control text-uppercase" type="text" name="FirstName" id="FirstName"
                                                                    style="background-color: white; color: black;" required=""
                                                                    placeholder="First Name">
                                                                <x-input-error :messages="$errors->get('FirstName')" class="mt-2" />
                                                            </div>
                                                            <div class=" mb-1">
                                                                <label for="LastName" class="text-nowrap me-3">Last Name</label>
                                                                <input autocomplete="off" class="form-control text-uppercase" type="text" name="LastName" id="LastName"
                                                                    style="background-color: white; color: black;" required=""
                                                                    placeholder="Last Name">
                                                                <x-input-error :messages="$errors->get('LastName')" class="mt-2" />
                                                            </div>
                                                            <div class=" mb-1">
                                                                <label for="email" class="text-nowrap me-3">Email</label>
                                                                <input autocomplete="off" class="form-control" type="email" name="email" id="email"
                                                                    style="background-color: white; color: black;" required="" placeholder="Email">
                                                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                                            </div>
                                                            <div class="mb-1">
                                                                <label for="Position" class="text-nowrap me-3">Position</label>
                                                                <input autocomplete="off" class="form-control text-uppercase" type="text" name="Position" id="Position"
                                                                    style="background-color: white; color: black;" required=""
                                                                    placeholder="Position">
                                                                <x-input-error :messages="$errors->get('Position')" class="mt-2" />
                                                            </div>
                                                            <label for="Status" class="text-nowrap me-3">User Type</label>
                                                                <select name="usertype" id="usertype" class="form-select" style="background-color: white; color: black;" required>
                                                                    <option value="0">Client</option>
                                                                    <option value="1">ESCO Admin</option>
                                                                    <option value="2">ESCO Super Admin</option>
                                                                </select>
                                                          
                                                                <x-input-error :messages="$errors->get('Status')" class="mt-2" />
                                                            </div>
                                                            </form>
                                                                <div class="mb-3 text-center">
                                                                    <button class="btn btn-primary" id="UpdateUserData" type="submit">Save</button>
                                                                </div>

                                                          

                                                        </div>
                                                    </div><!-- /.modal-content -->
                                                </div><!-- /.modal-dialog -->
                                            </div><!-- /.modal -->
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
    <script src="Modules/assets/js/esco/userAccount.js"></script>
@endsection
