<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /** Show the registration form. */
    public function create(): View
    {
        return view('auth.register');
    }

    /** Register a new member and sign them in. */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => $request->validated('password'),
            'role' => $request->role(),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Service providers still need a profile before they can list anything.
        if ($request->wantsToProvideServices()) {
            return redirect()->route('provider.onboarding')
                ->with('status', 'Welcome! Tell us about the services you offer.');
        }

        return redirect()->route('dashboard');
    }
}
