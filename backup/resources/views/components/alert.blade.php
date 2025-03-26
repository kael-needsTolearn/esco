    {{-- error --}}
    <div id="error-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content" style="background-color: #fdecf0;">
                <div class="modal-body pb-3 mt-4">
                    <div class="text-center">
                        <i class=" dripicons-wrong h1 text-danger"></i>
                        <h4 class="mt-2" style="font-size: 22px;">Error!</h4>
                        <p class="mt-3" style="font-size: 17px;" id="error-message"></p>
                        <button type="button" class="btn btn-danger my-2" data-bs-dismiss="modal">Continue</button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    {{-- success --}}
    <div id="success-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content" style="background-color: #f4ffe5;">
                <div class="modal-body pb-3 mt-4">
                    <div class="text-center">
                        <i class="dripicons-checkmark h1 text-success"></i>
                        <h4 class="mt-2" style="font-size: 22px;">Success!</h4>
                        <p class="mt-3" style="font-size: 17px;" id="success-message"></p>
                        <button type="button" class="btn btn-info my-2" data-bs-dismiss="modal">Continue</button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    {{-- warning --}}

    <div id="warning-alert-modal" class="modal fade mt-5" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content" style="background-color: #fffdf6; width:580px; height:360px;">
                <div class="modal-body pb-4 mt-4">
                    <div class="text-center">
                        <i class="dripicons-warning h1 text-warning" style="font-size:50px;"></i>
                        <h4 class="mt-2" style="font-size: 26px;">Warning</h4>
                        <p class="mt-3" style="font-size: 19px;">Are you sure you want to proceed?<br> Click continue to
                        <span class="fw-bold" style="color: red;">delete</span>.</p>
                            <button type="button" class="btn btn-light  my-2" data-bs-dismiss="modal">&nbsp;&nbsp;Cancel&nbsp;&nbsp;&nbsp;</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button type="button" class="btn text-white btn-warning my-2 " data-bs-dismiss="modal" id="proceed-btn">Continue</button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- <div id="warning-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content" style="background-color: #fdecf0;">
                <div class="modal-body pb-3 mt-4">
                    <div class="text-center">
                        <i class="dripicons-trash h1 text-danger"></i>
                        <h4 class="mt-2" style="font-size: 22px;">This action can't be undone!</h4>
                        <p class="mt-3" style="font-size: 17px;" id="warning-message"></p>
                    </div>
                    <div class="d-flex justify-content-between m-auto " style="width:70%">
                        <button type="button" class="btn btn-danger my-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger my-2" id="proceed-btn">Continue</button>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
