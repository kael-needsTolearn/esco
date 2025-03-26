<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <div class="col-xxl-4 col-11 col-lg-5">
        <div class="card" style="position: relative;">
            <img src="Modules/assets/images/logo.png" height="35" class="me-2"
                style="position: absolute; left: 38%; top: -10%; height: 100px;" alt="master-card-img">

            <div class="card-body p-4">
                <div class="mb-3 mt-5">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <form action="/authenticate" method="POST">
                    @csrf
                    <div class="mb-3 d-flex">
                        <img src="Modules/assets/images/layouts/user.png" height="35" class="me-2"
                            style="opacity: .40;" alt="master-card-img">
                        <input autocomplete="off" class="form-control" name="email" type="email" id="emailaddress"
                            style="background-color: #59595b; color: white;" required="" placeholder="Email">

                    </div>
                    <div class="mb-3 d-flex">
                        <img src="Modules/assets/images/layouts/lock.png" height="35" class="me-2"
                            style="opacity: .40;" alt="master-card-img">
                        <div class="input-group input-group-merge">
                            <input type="password" id="password" name="password" class="form-control"
                                style="background-color: #59595b; color: white;" placeholder="******">
                            <div class="input-group-text" data-password="false">
                                <span class="password-eye"></span>
                            </div>
                        </div>

                    </div>

                    <div class="mb-3 mb-0 mt-5 text-center">
                        <button class="btn btn-info text-uppercase"
                            style="width: 140px;height: 45px;font-family: calibri; background-color: #77b62e;"
                            type="submit"> Log In </button>
                    </div>
                </form>
            </div> <!-- end card-body -->
            <div class="card-footer" style="background-color: #59595b;">
                <div class="d-flex justify-content-between">
          
                    @if (Route::has('password.request'))
                        <a id="passwordreq" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                            href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
