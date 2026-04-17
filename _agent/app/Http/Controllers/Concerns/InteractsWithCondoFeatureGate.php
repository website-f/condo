<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Agent;
use App\Support\CondoPackageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

trait InteractsWithCondoFeatureGate
{
    protected function condoFeatureAccessResponse(
        CondoPackageManager $packageManager,
        string $pageTitle,
        string $featureTitle,
        string $summary,
        array $bullets = []
    ): RedirectResponse|null {
        /** @var Agent|null $agent */
        $agent = Auth::guard('agent')->user();

        if (! $agent || $packageManager->hasAccess($agent)) {
            return null;
        }

        return redirect()
            ->route('billing.index')
            ->with('package_feature_gate', [
                'page_title' => $pageTitle,
                'feature_title' => $featureTitle,
                'summary' => $summary,
                'bullets' => collect($bullets)
                    ->map(fn (mixed $bullet) => trim((string) $bullet))
                    ->filter()
                    ->values()
                    ->all(),
            ])
            ->withErrors([
                'package' => $featureTitle . ' is locked. ' . $summary,
            ]);
    }
}
