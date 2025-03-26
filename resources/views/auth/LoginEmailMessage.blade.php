<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <div class="col-xxl-4 col-11 col-lg-5" id="sample">
        <div class="card" style="position: relative;">
            <img src="Modules/assets/images/logo.png" height="35" class="me-2" style="position: absolute; left: 38%; top: -10%; height: 100px;" alt="master-card-img">
            
            <div class="card-body mt-5 p-4">
                <div class="text-center w-75 m-auto">
                    <p style="line-height: 1.5; font-size:20px;" class="p-1 text-muted ">Please Refer To Your Email to Login.</p>
                    <!-- <p class="text-muted mb-2">Enter your email address and we'll send you an email with instructions to reset your password.</p> -->
                </div>
        
            </div> <!-- end card-body -->
            <div class="card-footer" style="background-color: #59595b;">
                <div class="d-flex justify-content-between">
                <span class="text-white">Back to <a href="{{ route('login') }}" class="text-info">Log In</a></span>
            </div>
                </div>
        </div>
        <!-- end card -->
    </div>
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

</x-guest-layout>
