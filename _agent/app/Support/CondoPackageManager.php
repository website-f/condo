<?php

namespace App\Support;

use App\Models\Agent;
use App\Models\Package;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CondoPackageManager
{
    public function packageFor(Agent $agent): ?Package
    {
        $agent->loadMissing('subscription');

        return $agent->subscription;
    }

    public function hasAccess(Agent $agent): bool
    {
        return (bool) $this->packageFor($agent)?->is_condo_package;
    }

    public function listingSpaceLimit(Agent $agent): int
    {
        return max(0, (int) ($this->packageFor($agent)?->listing_space_limit ?? 0));
    }

    public function dailyAutoSubmitLimit(Agent $agent): int
    {
        return max(0, (int) ($this->packageFor($agent)?->daily_auto_submit_limit ?? 0));
    }

    public function articleSubmissionUsesCredit(Agent $agent): bool
    {
        return (bool) $this->packageFor($agent)?->article_submission_uses_credit;
    }

    public function condoPackages(): Collection
    {
        return Package::query()
            ->whereIn('name', Package::CONDO_PACKAGE_NAMES)
            ->orderByRaw('FIELD(name, ?, ?)', [
                Package::CONDO_PREMIUM_NAME,
                Package::CONDO_PREMIUM_LITE_NAME,
            ])
            ->get();
    }

    public function activeCondoListingCount(Agent|string $agent): int
    {
        $username = $agent instanceof Agent ? $agent->username : trim((string) $agent);

        if ($username === '') {
            return 0;
        }

        return (int) DB::connection('condo')
            ->table('posts')
            ->join('postmeta as owner_meta', function ($join) {
                $join->on('owner_meta.post_id', '=', 'posts.ID')
                    ->where('owner_meta.meta_key', '=', CondoWordpressBridge::META_USERNAME);
            })
            ->where('posts.post_type', 'properties')
            ->whereNotIn('posts.post_status', ['trash', 'auto-draft', 'inherit'])
            ->where('owner_meta.meta_value', $username)
            ->distinct('posts.ID')
            ->count('posts.ID');
    }

    public function remainingListingSpace(Agent $agent): int
    {
        return max(0, $this->listingSpaceLimit($agent) - $this->activeCondoListingCount($agent));
    }

    /**
     * @return array{
     *     enabled:bool,
     *     package_name:string,
     *     listing_limit:int,
     *     listing_used:int,
     *     listing_remaining:int,
     *     daily_limit:int,
     *     daily_remaining:int,
     *     article_submission_uses_credit:bool
     * }
     */
    public function summaryForAgent(Agent $agent): array
    {
        $package = $this->packageFor($agent);

        if (! $package || ! $package->is_condo_package) {
            return [
                'enabled' => false,
                'package_name' => $package?->display_name ?? 'No active condo package',
                'listing_limit' => 0,
                'listing_used' => 0,
                'listing_remaining' => 0,
                'daily_limit' => 0,
                'daily_remaining' => 0,
                'article_submission_uses_credit' => false,
            ];
        }

        $this->syncDailyCredits($agent);
        $listingUsed = $this->activeCondoListingCount($agent);
        $listingLimit = $this->listingSpaceLimit($agent);

        return [
            'enabled' => true,
            'package_name' => (string) $package->display_name,
            'listing_limit' => $listingLimit,
            'listing_used' => $listingUsed,
            'listing_remaining' => max(0, $listingLimit - $listingUsed),
            'daily_limit' => $this->dailyAutoSubmitLimit($agent),
            'daily_remaining' => max(0, (int) ($agent->credit ?? 0)),
            'article_submission_uses_credit' => $this->articleSubmissionUsesCredit($agent),
        ];
    }

    public function syncDailyCredits(Agent $agent): Agent
    {
        if (! $this->hasAccess($agent)) {
            return $agent;
        }

        $dailyLimit = $this->dailyAutoSubmitLimit($agent);

        if ($dailyLimit <= 0) {
            return $agent;
        }

        $today = now()->toDateString();
        $storedDate = trim((string) ($agent->creditenddate ?? ''));
        $currentCredits = max(0, (int) ($agent->credit ?? 0));

        if ($storedDate === $today && $currentCredits <= $dailyLimit) {
            return $agent;
        }

        DB::connection('mysql')
            ->table('Users')
            ->where('id', $agent->getKey())
            ->update([
                'credit' => $dailyLimit,
                'creditenddate' => $today,
                'resetcredit' => 0,
            ]);

        $agent->forceFill([
            'credit' => $dailyLimit,
            'creditenddate' => $today,
            'resetcredit' => 0,
        ]);

        return $agent;
    }

    public function ensureCondoListingCapacity(Agent $agent): void
    {
        if (! $this->hasAccess($agent)) {
            throw ValidationException::withMessages([
                'source' => 'Subscribe to a Condo Premium package to unlock condo listings.',
            ]);
        }

        if ($this->remainingListingSpace($agent) > 0) {
            return;
        }

        throw ValidationException::withMessages([
            'source' => 'Your condo listing space is full. Delete an existing condo listing or upgrade your package.',
        ]);
    }

    public function consumeCredit(Agent $agent, int $amount, string $field = 'credit'): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->syncDailyCredits($agent);
        $currentCredits = max(0, (int) ($agent->credit ?? 0));

        if ($currentCredits < $amount) {
            throw ValidationException::withMessages([
                $field => 'No condo daily credit left right now. Please upgrade your package or wait for the next daily reset.',
            ]);
        }

        $updatedCredits = $currentCredits - $amount;

        DB::connection('mysql')
            ->table('Users')
            ->where('id', $agent->getKey())
            ->update([
                'credit' => $updatedCredits,
                'creditenddate' => trim((string) ($agent->creditenddate ?? '')) !== ''
                    ? (string) $agent->creditenddate
                    : now()->toDateString(),
            ]);

        $agent->forceFill([
            'credit' => $updatedCredits,
            'creditenddate' => trim((string) ($agent->creditenddate ?? '')) !== ''
                ? (string) $agent->creditenddate
                : now()->toDateString(),
        ]);
    }
}
