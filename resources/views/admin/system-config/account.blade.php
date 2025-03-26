@extends('layouts.admin')
@section('content')
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex">
                            <i class="mdi mdi-book-multiple fs-2 text-primary"></i>
                            <h4 class="header-title text-left p-0 mt-2 ms-2" style="font-size: 25px">
                                API Accounts
                            </h4>
                        </div>
                        <div class="d-flex">
                            <!-- <button> -->
                            <i type="button" class="mdi mdi-book-plus fs-2 text-primary" data-bs-toggle="modal"
                                data-bs-target="#event-modal"></i>
                            <!-- </button> -->
                            <!-- modal -->
                            <div class="modal fade"  data-bs-backdrop="static" data-bs-keyboard="false" id="event-modal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form class="needs-validation" name="event-form" id="add-api-form" novalidate=""
                                            method="POST" action="{{ route('add-api') }}">
                                            @csrf
                                            <div class="modal-header py-3 px-4 border-bottom-0" >
                                                <h5 class="modal-title" id="modal-title">
                                                    API Account
                                                </h5>
                                                <button type="button" class="btn-close btn-close1" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body px-4 pb-4 pt-0" >
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="mb-3">
                                                            <label class="control-label form-label">Platform</label>
                                                            <select name="Platform" id="platform_name" class="form-control">
                                                                <option value="xio">Crestron</option>
                                                                <option value="qsys">Q-Sys</option>
                                                                <option value="eutelogy">Eutelogy </option>
                                                                <option value="uhoo">Uhoo</option>
                                                            </select>
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
                                                    <div id="uhoo-div" class="d-none">
                                                        <div class="col-12">
                                                            <div class="mb-3">
                                                                <label class="control-label form-label">Uhoo API KEY</label>
                                                                <input type="text" class="form-control" name="variable3" />
                                                                <div class="invalid-feedback">
                                                                    Please input a valid api key
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="float-end pb-2">
                                                    <div class="d-flex text-end">
                                                        <button type="button" class="btn btn-light me-1 btn-close1"
                                                            data-bs-dismiss="modal">
                                                            Close
                                                        </button>
                                                        <button type="submit" class="btn btn-success"
                                                            id="btn-save-event">
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
                        <div class="table-responsive" style="height: 575px !important; overflow-y:auto;">
                            <table id="basic-datatable" class="table" style="max-height: 50px !important">
                                <thead class="text-center text-white">
                                    <tr>
                                        <th>Platform</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody class="text-center">
                                    @foreach ($accounts as $index => $item)
                                        <tr class="" style="color:black;white-space: nowrap;">
                                            <td>{{ strtoupper($item->Platform) }}</td>
                                            <td>{{ $item->Description }}</td>
                                            <td class="text-center justify-content-center  d-flex"
                                                id="Row{{ $index }}">
                                                <!-- <span onclick="EditAccount('{{ $item->Api_Id }}')"
                                                    id="Edit{{ $index }} data-value="{{ $item->Company_Id }}"
                                                    style="cursor: pointer;"><i
                                                        class="mdi mdi-account-edit-outline"></i></span> -->
                                                <span onclick="DeleteAcc('{{ $item->Api_Id }}')"
                                                    id="Delete{{ $index }} data-value="{{ $item->Company_Id }}"
                                                    style="cursor: pointer;"><i style="font-size:20px;" class="mdi mdi-delete-outline"></i></span>
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
<script>
    
//   $("#platform_name").on("change", function () {
//     var selectedValue = $(this).val();
//     if (selectedValue === "uhoo") {
       
//     }
// });

</script>
    <script src="Modules/assets/js/esco/apiAccount.js"></script>
@endsection
