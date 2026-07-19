<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\WebLoginRequest;
use App\Models\User;
use App\Services\AdminLandingPageResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(private readonly AdminLandingPageResolver $landingPage) {}

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route($this->dashboardRoute($request->user()));
        }

        return view('auth.login');
    }

    public function store(WebLoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);
        $credentials[] = fn (Builder $query): Builder => $query->where('status', '!=', 'suspended');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'بيانات تسجيل الدخول غير صحيحة.',
            ]);
        }

        $request->session()->regenerate();

        if (! $request->user()->hasAnyRole(['super-admin', 'admin', 'doctor'])) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'هذا الحساب لا يمتلك دورًا مسموحًا.']);
        }

        return redirect()->intended(route($this->dashboardRoute($request->user())));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function dashboardRoute(User $user): string
    {
        return $user->isAdmin() ? $this->landingPage->routeName($user) : 'web.doctor.dashboard';
    }
}
