<?php

namespace App\Support;

use App\Models\CondoAgentDomain;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CondoAgentDomainManager
{
    private ?bool $tableAvailable = null;

    public function tableAvailable(): bool
    {
        if ($this->tableAvailable !== null) {
            return $this->tableAvailable;
        }

        try {
            return $this->tableAvailable = Schema::hasTable((new CondoAgentDomain())->getTable());
        } catch (Throwable) {
            return $this->tableAvailable = false;
        }
    }

    public function publicBaseHost(): string
    {
        $configured = trim((string) config('services.shared_assets.public_base_host'));

        if ($configured === '') {
            return 'condo.com.my';
        }

        $host = parse_url($configured, PHP_URL_HOST);

        if (is_string($host) && trim($host) !== '') {
            return strtolower(trim($host));
        }

        return strtolower(trim($configured, '/'));
    }

    /**
     * @return array<int, string>
     */
    public function reservedSubdomains(): array
    {
        return collect(config('services.shared_assets.public_reserved_subdomains', []))
            ->map(fn (mixed $value) => strtolower(trim((string) $value)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function defaultHostForUsername(string $username): ?string
    {
        $username = trim($username);

        if ($username === '') {
            return null;
        }

        $label = $this->subdomainLabelForUsername($username);

        if ($label === null) {
            return null;
        }

        return $label . '.' . $this->publicBaseHost();
    }

    public function primaryDomainForAgent(string $username): ?CondoAgentDomain
    {
        if (! $this->tableAvailable()) {
            return null;
        }

        return CondoAgentDomain::query()
            ->where('agent_username', trim($username))
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->orderBy('host')
            ->first();
    }

    public function ensureDefaultDomain(string $username): ?CondoAgentDomain
    {
        $this->ensureTableAvailable();

        $username = trim($username);
        $host = $this->defaultHostForUsername($username);

        if ($username === '' || $host === null) {
            return null;
        }

        $existing = CondoAgentDomain::query()
            ->where('host', $host)
            ->first();

        if ($existing !== null && trim((string) $existing->agent_username) !== $username) {
            throw new RuntimeException('The default public host ' . $host . ' is already assigned to ' . $existing->agent_username . '.');
        }

        $hasPrimary = CondoAgentDomain::query()
            ->where('agent_username', $username)
            ->where('is_active', true)
            ->where('is_primary', true)
            ->exists();

        return CondoAgentDomain::query()->updateOrCreate(
            ['host' => $host],
            [
                'agent_username' => $username,
                'is_active' => true,
                'is_primary' => $existing?->is_primary ?? ! $hasPrimary,
                'ssl_status' => $existing?->ssl_status ?? 'pending',
            ]
        );
    }

    private function ensureTableAvailable(): void
    {
        if (! $this->tableAvailable()) {
            throw new RuntimeException('The condo_agent_domains table is missing. Run php artisan migrate first.');
        }
    }

    private function subdomainLabelForUsername(string $username): ?string
    {
        $label = strtolower(trim($username));
        $label = preg_replace('/[^a-z0-9-]+/i', '-', str_replace(['.', '_'], '-', $label)) ?? $label;
        $label = preg_replace('/-+/', '-', $label) ?? $label;
        $label = trim((string) $label, '-');

        if ($label === '') {
            return null;
        }

        if (in_array($label, $this->reservedSubdomains(), true)) {
            $label = 'u-' . $label;
        }

        $label = Str::limit($label, 63, '');
        $label = trim((string) $label, '-');

        return $label !== '' ? $label : null;
    }
}
