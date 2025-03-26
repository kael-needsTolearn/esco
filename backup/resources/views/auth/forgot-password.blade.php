<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <div class="col-xxl-4 col-11 col-lg-5">
        <div class="card" style="position: relative;">
            <img src="Modules/assets/images/logo.png" height="35" class="me-2" style="position: absolute; left: 38%; top: -10%; height: 100px;" alt="master-card-img">
            
            <div class="card-body mt-5 p-4">
                <div class="text-center w-75 m-auto">
                    <h4 class="text-dark-50 text-center mt-0 fw-bold">Reset Password</h4>
                </div>
                <form method="GET" action="{{ route('updatePassword') }}">
                    <div class="mb-3 mt-4">
                        <!-- <label for="emailaddress" class="form-label">Email address</label> -->
                       <span>Email Address:</span>
                        <input autocomplete="off" class="form-control" type="email" name="emailaddress" id="emailaddress" style="background-color: #59595b; color: white;" required="" placeholder="Enter Your Email Address">
                        <span>New Password:</span>
                        <input type="password" id="Password" name="newpassword" class="form-control" style="background-color: #59595b; color: white;" placeholder="******">
                        <span>Confirm Password:</span>
                        <input type="password" id="Password" name="confirmpassword" class="form-control" style="background-color: #59595b; color: white;" placeholder="******">
                    </div>
                    <div class="mb-2 mb-0 mt-4 text-center">
                        <button class="btn btn-info text-uppercase" style="width: 140px;height: 45px;font-family: calibri; background-color: #77b62e;" type="submit"> Reset Password</button>
                    </div>
                </form>
            </div> <!-- end card-body -->
            <div class="card-footer" style="background-color: #59595b;">
                <div class="d-flex justify-content-between">
                <span class="text-white">Back to <a style="#90c24f;" href="{{ route('login') }}" class="text-info">Log In</a></span>
            </div>
                </div>
        </div>
        <!-- end card -->
    </div>
        <!-- <div class="row mt-3">
            <div class="col-12 text-center">
            </div> 
        </div> -->
        <!-- end row -->

</x-guest-layout>
