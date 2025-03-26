<x-guest-layout>
    <div class="col-xxl-7 col-11 col-sm-11 col-xl-7 col-md-11  col-lg-7">
        <div class="card" style="position: relative;">
            <img src="Modules/assets/images/logo.png" height="35" class="me-2"
                style="position: absolute; left: 43%; top: -10%; height: 100px;" alt="master-card-img">

            <div class="card-body px-4">
                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-6">
                            <div class=" mt-4 mb-1">
                                <label for="FirstName" class="text-nowrap me-3">First Name</label>
                                <input autocomplete="off" value="{{ old('FirstName') }}" class="form-control text-uppercase" type="text" name="FirstName" id="FirstName"
                                    style="background-color: #59595b; color: white;" required="" 
                                    placeholder="First Name">
                                <x-input-error :messages="$errors->get('FirstName')" class="mt-2" />
                            </div>
                            <div class=" mb-1">
                                <label for="LastName" class="text-nowrap me-3">Last Name</label>
                                <input autocomplete="off" value="{{ old('LastName') }}"  class="form-control text-uppercase" type="text" name="LastName" id="LastName"
                                    style="background-color: #59595b; color: white;" required=""
                                    placeholder="Last Name">
                                <x-input-error :messages="$errors->get('LastName')" class="mt-2" />
                            </div>
                            <div class=" mb-1">
                                <label for="email" class="text-nowrap me-3">Email</label>
                                <input autocomplete="off" value="{{ old('email') }}" class="form-control" type="text" name="email" id="email"
                                    style="background-color: #59595b; color: white;" required="" placeholder="Email">
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <div class="mb-1">
                                <label for="Position" class="text-nowrap me-3">Position</label>
                                <input autocomplete="off" value="{{ old('Position') }}"  class="form-control text-uppercase" type="text" name="Position" id="Position"
                                    style="background-color: #59595b; color: white;" required=""
                                    placeholder="Position">
                                <x-input-error :messages="$errors->get('Position')" class="mt-2" />
                            </div>
                        </div>
                        <div class="col-6 mt-4">
                            <div class="mb-1">
                                <label for="Status" class="text-nowrap me-3">User Type</label>
                                <select name="usertype" id="" class="form-select" style="background-color: #59595b; color: white;" required>
                                    <option value="0">Client</option>
                                    <option value="1">ESCO Admin</option>
                                    <option value="2">ESCO Super Admin</option>
                                </select>
                                {{-- <input class="form-control" type="text" name="Status" id="Status"
                                    style="background-color: #59595b; color: white;" required=""
                                    placeholder="Status"> --}}
                                <x-input-error :messages="$errors->get('Status')" class="mt-2" />
                            </div>
                            <div class="mb-1">
                                <label for="Status" class="text-nowrap me-3">Start Date</label>
                                <input autocomplete="off" class="form-control date" type="text" name="StartDate" id="StartDate"
                                    style="background-color: #59595b; color: white;" required=""
                                    placeholder="Start Date" data-toggle="date-picker" data-single-date-picker="true">
                                <x-input-error :messages="$errors->get('StartDate')" class="mt-2" />
                            </div>
                            <div class="mb-1">
                                <label for="Status" class="text-nowrap me-3">Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="Password" name="password" class="form-control"
                                        style="background-color: #59595b; color: white;" placeholder="******">
                                    <div class="input-group-text" data-password="false">
                                        <span class="password-eye"></span>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                            <div class="mb-3">
                                <label for="ConfirmPassword" class="text-nowrap me-3">Confirm Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="ConfirmPassword" name="password_confirmation"
                                        class="form-control" style="background-color: #59595b; color: white;"
                                        placeholder="******">
                                    <div class="input-group-text" data-password="false">
                                        <span class="password-eye"></span>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                    <div class="mb-1 mt-4 text-center">
                        <button class="btn btn-info text-uppercase"
                            style="width: 140px;height: 45px;font-family: calibri; background-color: #77b62e;"
                            type="submit"> Register </button>
                    </div>
                </form>
            </div> <!-- end card-body -->
            <div class="card-footer" style="background-color: #59595b;">
                <div class="d-flex justify-content-between">
                    <span class="text-white"><small>ESCO
                            {{-- <a href="{{ route('login') }}"
                            class="ms-1 text-info text-decoration-none">Login here</a> </small></span> --}}
                            <!-- <a href="pages-recoverpw.html" class="text-white"><small>Forgot your password?</small></a> -->
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
