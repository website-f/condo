<?php

namespace App\Http\Controllers;

use App\Support\CondoAgentDomainManager;
use App\Support\LegacyPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function index()
    {
        $agent = Auth::guard('agent')->user();
        $agent->load('detail', 'subscription');
        return view('profile.index', compact('agent'));
    }

    public function visitSite(CondoAgentDomainManager $domains)
    {
        $agent = Auth::guard('agent')->user();
        $username = $agent->username;

        $domain = $domains->primaryDomainForAgent($username);
        $host = $domain?->host ?: $domains->defaultHostForUsername($username);

        if (! $host) {
            return back()->with('error', 'Unable to resolve a public site host for this account.');
        }

        try {
            $domains->ensureDefaultDomain($username);
        } catch (\Throwable) {
        }

        $scheme = request()->isSecure() ? 'https' : 'http';
        return redirect()->away($scheme . '://' . $host);
    }

    public function update(Request $request)
    {
        $agent = Auth::guard('agent')->user();
        $detail = $agent->detail;

        $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($detail) {
            $detail->update([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);
        }

        return redirect()->route('profile.index')->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $agent = Auth::guard('agent')->user();

        if (! LegacyPassword::check($request->current_password, $agent->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        DB::connection($agent->getConnectionName())
            ->table($agent->getTable())
            ->where($agent->getKeyName(), $agent->getKey())
            ->update([
                'password' => $request->password,
            ]);

        $agent->password = $request->password;

        return redirect()->route('profile.index')->with('success', 'Password changed.');
    }
}
