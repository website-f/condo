<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Support\LegacyPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('agent')->check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $agent = Agent::where('username', $request->username)->first();

        if ($agent && LegacyPassword::check($request->password, $agent->password)) {
            DB::connection($agent->getConnectionName())
                ->table($agent->getTable())
                ->where($agent->getKeyName(), $agent->getKey())
                ->update([
                    'password' => $request->password,
                ]);

            $agent->password = $request->password;

            // The legacy Users table has no remember_token column, so keep this session-based.
            Auth::guard('agent')->login($agent);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::guard('agent')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
