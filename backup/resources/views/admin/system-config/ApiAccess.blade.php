@extends('layouts.admin')
@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">

                <h4 class="page-title">API Access</h4>
            </div>
        </div>
    </div>
    <!-- Start Content Here-->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <h4 style="white-space: nowrap;" class="header-title text-left p-0 mt-2">Company</h4>
                        <!-- Single Select -->
                        <div style="width: 380px;">
                            <select class="form-control select2" id="CompanyIDSelect" data-toggle="select2">
                                <option disabled selected></option>
                                <option disabled>Select Company</option>
                                @foreach ($options as $option)
                                    <option value="{{ $option->Company_Id }}">
                                        {{ $option->Company_Name . ' - ' . $option->Country. ' - '.$option->Contract_Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-info" id="SearchEmail">Search</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row hide accounts">
        <div class="col-12" id="accounts-table">
            <form method="POST" action="{{ route('AddApiAccess') }}" id="Form1">
                <div class="card">
                    <div class="card-body" style="height: 400px;">
                        <h3 style="white-space: nowrap;" class="header-title text-secondary text-left p-0 ">Available API's
                        </h3>
                        <label class="text-secondary mt-2">Select to Assign API</label>
                        <div class="table-responsive" style="height: 470px !important; overflow-y:auto;">
                            <table class="table"
                                id="AvailableAccounts">
                                <thead>
                                    <tr class="text-center text-white">
                                    <th class="pt-3 th-md">Checkbox</th>
                                    <th class="pt-3 th-xxl">Platform</th>
                                    <th class="pt-3">Description</th>
                                    </tr>
                                </thead>
                                <tbody id="tbbody1" style="max-height: 200px; overflow:auto ;">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
            <form method="POST" action="{{ route('AddApiAccess') }}" id="Form2">
                <div class="card">
                    <div class="card-body">
                        <h3 style="white-space: nowrap;" class="header-title text-secondary text-left p-0">Current Assigned
                            API's</h3>
                        <label class="text-secondary mt-2">Select to Unassign API</label>
                    <div class="table-responsive" style="height: 470px !important; overflow-y:auto;">
                        <table class="table" id="CurrentAccounts">
                            <thead>
                                <tr class="text-center text-white">
                                <th class="pt-3 th-md">Checkbox</th>
                                    <th class="pt-3 th-xxl">Platform</th>
                                    <th class="pt-3">Description</th>
                                </tr>
                            </thead>
                            <tbody id="tbbody2" style="max-height: 200px; overflow:auto ;">

                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            </form>
            <div class="float-end mt-2">
                <button type="button" id="SaveApi" onclick="SaveApi()" class="btn btn-info">Save</button>
            </div>
        </div>
    </div>

    <!--End of content here-->
    <!-- Warning Alert Modal -->
    <button type="button" id="warningAlert" class="btn btn-warning hide" data-bs-toggle="modal"
        data-bs-target="#warning-alert-modal">Warning Alert</button>
    <div id="warning-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content" style="background-color: #fffdf6;">
                <div class="modal-body pb-3 mt-4">
                    <div class="text-center">
                        <i class="dripicons-warning h1 text-warning"></i>
                        <h4 class="mt-2" style="font-size: 22px;">Warning</h4>
                        <p class="mt-3" style="font-size: 17px;">Are you sure you want to proceed.<br> Click continue do
                            delete.</p>
                        <button type="button" class="btn btn-warning my-2" data-bs-dismiss="modal">Continue</button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    {{-- success --}}
    <button type="button" id="SuccessAlert" class="btn btn-success hide" data-bs-toggle="modal"
        data-bs-target="#success-alert-modal">Success Akert</button>
    <div id="success-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content" style="background-color: #e8faf4;">
                <div class="modal-body pb-3 mt-4">
                    <div class="text-center">
                        <i class="dripicons-checkmark  h1 text-success"></i>
                        <h4 class="mt-2" style="font-size: 22px;">Success!</h4>
                        <p class="mt-3" style="font-size: 17px;">You successfully saved the data.</p>
                        <button type="button" class="btn btn-success my-2" data-bs-dismiss="modal">Continue</button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    {{-- error --}}
    <button type="button" id="ErrorAlert" class="btn btn-danger hide" data-bs-toggle="modal"
        data-bs-target="#error-alert-modal">Error Alert</button>
    <div id="error-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content" style="background-color: #fdecf0;">
                <div class="modal-body pb-3 mt-4">
                    <div class="text-center">
                        <i class=" dripicons-wrong h1 text-danger"></i>
                        <h4 class="mt-2" style="font-size: 22px;">Error!</h4>
                        <p class="mt-3" style="font-size: 17px;">Insert the email address.<br>Please try again.</p>
                        <button type="button" class="btn btn-danger my-2" data-bs-dismiss="modal">Continue</button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection
@section('javascripts')
    <script src="Modules/assets/js/esco/Api-Access.js"></script>
@endsection
