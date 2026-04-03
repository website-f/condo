<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index()
    {
        $agent = Auth::guard('agent')->user();
        $agent->load('subscription');

        $packages = Package::orderBy('cost', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $currentPackage = $packages->firstWhere('id', $agent->package);

        if ($currentPackage) {
            $currentSignature = $this->packageSignature($currentPackage);

            $packages = $packages->filter(function (Package $package) use ($currentPackage, $currentSignature) {
                return $package->id === $currentPackage->id
                    || $this->packageSignature($package) !== $currentSignature;
            })->values();
        }

        $upgradeHistory = DB::table('UpgradeRecords')
            ->where('username', $agent->username)
            ->orderBy('createddate', 'desc')
            ->get();

        return view('billing.index', compact('agent', 'packages', 'upgradeHistory'));
    }

    private function packageSignature(Package $package): string
    {
        return implode('|', [
            number_format((float) $package->cost, 2, '.', ''),
            (string) $package->creditlimit,
            (string) $package->maxaccount,
        ]);
    }
}
