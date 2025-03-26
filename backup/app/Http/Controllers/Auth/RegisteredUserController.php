<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.CreateUser');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'FirstName' => ['required', 'string', 'max:255'],
            'LastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'Position' => ['required', 'string', 'max:255'],
            // 'usertype' => ['required', 'string', 'max:255'],
            'StartDate' => ['required'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'First_Name' => $request->FirstName,
            'Last_Name' => $request->LastName,
            'email' => $request->email,
            'Position' => $request->Position,
            'usertype' => $request->usertype,
            'Start_Date' => $request->StartDate,
            'password' => Hash::make($request->password),
        ]);


        event(new Registered($user));

        // Auth::login($user);
        return redirect('/login');
        //  return redirect('/dashboard');

       // return redirect()->back();
        // return redirect(route('dashboard', absolute: false));
    }
}
