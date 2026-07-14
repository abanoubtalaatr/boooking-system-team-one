<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    /*public function showLoginForm()
    {
        return view('auth.login');
    }*/

    /**
     * Handle login request
     */
    public function login(Request $request)
    {

        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectByRole(Auth::user());
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials.'])
            ->onlyInput('email');
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectByRole($user)
    {

        return match ($user->role->value) {
            'admin'  => redirect()->route('admin.dashboard'),
            'doctor' => redirect()->route('doctor.dashboard'),
            default  => redirect()->route('login'),
        };
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
