<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\UpgradeRecord;
use App\Support\CondoPackageManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function __construct(
        private readonly CondoPackageManager $condoPackageManager
    ) {
    }

    public function index()
    {
        $agent = Auth::guard('agent')->user();
        $agent->load('subscription');
        $this->condoPackageManager->syncDailyCredits($agent);

        $allPackages = Package::orderBy('cost', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $currentPackage = $allPackages->firstWhere('id', $agent->package);

        $packages = $allPackages
            ->filter(fn (Package $package) => $this->shouldShowPackage($package, $currentPackage))
            ->groupBy(fn (Package $package) => $package->plan_group_key)
            ->map(fn (Collection $group) => $this->representativePackage($group, $currentPackage))
            ->values();
        $packages = $packages
            ->sortBy([
                fn (Package $package) => (float) $package->cost,
                fn (Package $package) => trim((string) $package->display_name),
                fn (Package $package) => (int) $package->id,
            ])
            ->values();

        $upgradeHistory = $this->historyQuery($agent->username)
            ->get();

        $billingAudit = [
            'history_source' => 'UpgradeRecords.target',
            'date_storage' => 'unix_timestamp',
            'hidden_internal_packages' => $allPackages
                ->reject(fn (Package $package) => $currentPackage && (int) $package->id === (int) $currentPackage->id)
                ->filter(fn (Package $package) => (int) ($package->is_unknown ?? 0) === 1)
                ->count(),
            'hidden_zero_cost_packages' => $allPackages
                ->reject(fn (Package $package) => $currentPackage && (int) $package->id === (int) $currentPackage->id)
                ->filter(fn (Package $package) => (int) ($package->is_unknown ?? 0) !== 1 && (float) $package->cost <= 0)
                ->reject(fn (Package $package) => $package->is_condo_package)
                ->count(),
        ];
        $condoPackageSummary = $this->condoPackageManager->summaryForAgent($agent);

        return view('billing.index', compact('agent', 'packages', 'upgradeHistory', 'billingAudit', 'currentPackage', 'condoPackageSummary'));
    }

    public function exportHistory()
    {
        $agent = Auth::guard('agent')->user();
        $records = $this->historyQuery($agent->username)->get();
        $filename = 'billing-history-' . $agent->username . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, [
                'Invoice',
                'Date',
                'From',
                'To',
                'Amount',
                'Status',
                'Requested By',
                'Processed By',
                'Processed Date',
                'Reason',
            ]);

            foreach ($records as $record) {
                fputcsv($handle, [
                    $record->invoice_number,
                    $record->formatted_created_date,
                    $record->current_package_name,
                    $record->upgrade_package_name,
                    $record->amount_formatted,
                    $record->status_label,
                    $record->requested_by_label,
                    $record->processed_by_label,
                    $record->formatted_process_date,
                    trim((string) $record->reason),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function invoice(int $record)
    {
        $agent = Auth::guard('agent')->user();
        $upgradeRecord = $this->findOwnedHistoryRecord($record, $agent->username);

        return response()->view('billing.invoice', [
            'agent' => $agent,
            'record' => $upgradeRecord,
            'downloadMode' => false,
        ]);
    }

    public function exportInvoice(int $record)
    {
        $agent = Auth::guard('agent')->user();
        $upgradeRecord = $this->findOwnedHistoryRecord($record, $agent->username);
        $html = view('billing.invoice', [
            'agent' => $agent,
            'record' => $upgradeRecord,
            'downloadMode' => true,
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $upgradeRecord->invoice_filename . '"',
        ]);
    }

    private function representativePackage(Collection $group, ?Package $currentPackage): Package
    {
        if ($currentPackage) {
            $currentInGroup = $group->firstWhere('id', (int) $currentPackage->id);

            if ($currentInGroup instanceof Package) {
                return $currentInGroup;
            }
        }

        /** @var Package $representative */
        $representative = $group
            ->sortBy([
                fn (Package $package) => $this->packageRepresentativeRank($package),
                fn (Package $package) => (int) $package->id,
            ])
            ->first();

        return $representative;
    }

    private function packageRepresentativeRank(Package $package): int
    {
        return match (trim((string) $package->name)) {
            Package::CONDO_PREMIUM_NAME,
            Package::CONDO_PREMIUM_LITE_NAME => 0,
            'Premium',
            'ICPPremium' => 1,
            'Premium+' => 2,
            default => 3,
        };
    }

    private function shouldShowPackage(Package $package, ?Package $currentPackage): bool
    {
        if ($currentPackage && (int) $package->id === (int) $currentPackage->id) {
            return true;
        }

        if ((int) ($package->is_unknown ?? 0) === 1) {
            return false;
        }

        if ($package->is_condo_package) {
            return true;
        }

        return (float) $package->cost > 0;
    }

    private function historyQuery(string $username): Builder
    {
        return UpgradeRecord::query()
            ->with(['currentPackageRelation:id,name', 'upgradePackageRelation:id,name'])
            ->forAgent($username)
            ->orderBy('createddate', 'desc');
    }

    private function findOwnedHistoryRecord(int $recordId, string $username): UpgradeRecord
    {
        return $this->historyQuery($username)
            ->whereKey($recordId)
            ->firstOrFail();
    }
}
