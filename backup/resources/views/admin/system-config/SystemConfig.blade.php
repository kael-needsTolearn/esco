@extends('layouts.admin')
@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">

                <h4 class="page-title">System Configuration</h4>
            </div>
        </div>
    </div>
    <!-- Start Content Here-->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <div class="col-4">
                                    <div class="app-search dropdown d-none d-lg-block">
                                        <form>
                                            <div class="input-group">
                                                <input type="text" id="SearchTable" autocomplete="off"
                                                    class="form-control dropdown-toggle" placeholder="Search..."
                                                    id="top-search">
                                                <span class="mdi mdi-magnify search-icon"></span>
                                                <!-- <button class="input-group-text btn-info" type="submit">Search</button> -->
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <button class="btn btn-info mb-2 float-end" data-bs-toggle="modal"
                                    data-bs-target="#AddSysCon">Add System Configuration</button>
                            </div>
                            <div class="col-md-12 col-xl-12 col-lg-12" id="config-table">
                                <div class="table-responsive mt-2" style="height: 600px !important; overflow-y:auto;" id="config-table">
                                    
                                    <table class="table basic-datatable" id="AvailableAccounts">
                                        <thead>
                                            <tr class="text-center text-white">
                                                <th class="py-2 px-0"></th>
                                                <th class="py-2 px-0">Name</th>
                                                <th class="py-2 px-0">Description</th>
                                                <th class="py-2 px-0">Value</th>
                                            </tr>
                                        </thead>
                                        <tbody style="overflow:auto ;">
                                            @php
                                                $configs = App\Models\SystemConfiguration::paginate(10);
                                            @endphp
                                            @foreach ($configs as $config)
                                                <tr class="text-center text-nowrap">
                                                    <td>
                                                        <div class="d-flex justify-content-center gap-2">
                                                            <span data-id="{{ $config->Code_ID }}" data-bs-toggle="modal"
                                                                data-bs-target="#UpdateSysCon"
                                                                class="pointer-cursor update-btn"><i
                                                                    class="dripicons-document-edit text-primary"></i></span>
                                                            {{-- <span class="pointer-cursor delete-btn"><i
                                                                    class="dripicons-trash text-danger"></i></span> --}}
                                                        </div>
                                                    </td>
                                                    <td>{{ $config->Code_Name }}</td>
                                                    <td>{{ $config->Code_Description }}</td>
                                                    <td>
                                                        @if (
                                                            $config->Code_Name == 'Crestron_ApiRefresh' ||
                                                                $config->Code_Name == 'Zoho_CreateTicketTimer' ||
                                                                $config->Code_Name == 'Device_RefreshTime')
                                                            {{ $config->Code_Value }} minutes
                                                        @else
                                                            {{ $config->Code_Value }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                    {{ $configs->links('vendor.pagination.bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!--End of content here-->
    <!--Add Modal-->
    <!-- Add System Configuratio modal-->
    <div id="AddSysCon" class="modal fade" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="font-family: Calibri">
                <div class="modal-header">
                    <h3>Add System Configuration</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="ps-3 pe-3" method="POST" action="{{ route('addConfig') }}" id="sysConfigForm">
                        @csrf
                        <div class="mb-3">
                            <label for="AddName" class="form-label">Name</label>
                            <input autocomplete="off" class="form-control" type="text" id="AddName" required name="code_name"
                                placeholder="">
                        </div>

                        <div class="mb-3">
                            <label for="AddDescription" class="form-label">Description</label>
                            <input autocomplete="off" class="form-control" type="text" id="AddDescription" required name="code_desc"
                                placeholder="">
                        </div>

                        <div class="mb-3">
                            <label for="AddValue" class="form-label">Value</label>
                            <input autocomplete="off" class="form-control" type="text" id="AddValue" required name="code_value"
                                placeholder="">
                        </div>

                        <div class="mb-3 text-center">
                            <button class="btn btn-info" type="submit">Save</button>
                            {{-- <input type="submit" class="btn btn-info" value="Save" id="AddSysConBtn"> --}}
                        </div>
                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!--End of Add Modal-->
    <!--Update System Configuration modal-->
    <div id="UpdateSysCon" class="modal fade" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="font-family: Calibri">
                <div class="modal-header">
                    <h3>Update System Configuration</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form class="ps-3 pe-3" autocomplete="on" id="config-update" action="{{ route('update-config') }}"
                        method="POST">
                        @csrf
                        <input autocomplete="off" type="hidden" name="code_id" id="code_id">
                        <div class="mb-3">
                            <label for="UpdateName" class="form-label">Name</label>
                            <input autocomplete="off" class="form-control" type="text" id="update_name" readonly name="update_name"
                                placeholder="">
                        </div>

                        <div class="mb-3">
                            <label for="UpdateDescription" class="form-label">Description</label>
                            <input autocomplete="off" class="form-control" type="text" id="update_desc" name="update_desc"
                                placeholder="">
                        </div>

                        <div class="mb-3">
                            <label for="UpdateValue" class="form-label">Value</label>
                            <input autocomplete="off" class="form-control" type="text" id="update_value" name="update_value"
                                placeholder="">
                        </div>

                        <div class="mb-3 text-center">
                            <button class="btn btn-info" type="submit">Save</button>
                        </div>
                    </form>

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!--End of Update Modal-->

    {{-- <!-- Notifications !!!!!! Alert Modal -->
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
    </div><!-- /.modal --> --}}
    <x-alert />
@endsection
@section('javascripts')
    <script src="Modules/assets/js/esco/system-config.js"></script>
    <script src="Modules/assets/js/esco/updateConfig.js"></script>
@endsection
