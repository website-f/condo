<?php

namespace App\Http\Middleware;

use App\Models\Agent;
use App\Models\CondoAgentDomain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Illuminate\Support\Str;

class ResolveAgentSubsite
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $this->normalizeHost($request->getHost());

        if ($host === '' || ! $this->isAgentSubdomain($host)) {
            abort(404);
        }

        $agent = $this->resolveAgent($host);

        if ($agent === null) {
            abort(404);
        }

        $request->attributes->set('public_agent', $agent);
        $request->attributes->set('public_host', $host);

        view()->share('publicAgent', $agent);
        view()->share('publicHost', $host);

        return $next($request);
    }

    protected function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        return preg_replace('/:\d+$/', '', $host) ?: '';
    }

    protected function isAgentSubdomain(string $host): bool
    {
        foreach ($this->candidateBaseHosts() as $base) {
            if ($host === $base || $host === 'www.' . $base) {
                return false;
            }

            if (Str::endsWith($host, '.' . $base)) {
                return true;
            }
        }

        return false;
    }

    protected function candidateBaseHosts(): array
    {
        return [
            'condo.com.my',
            'ppp.my',
            'condo.test',
        ];
    }

    protected function resolveAgent(string $host): ?Agent
    {
        try {
            if (Schema::hasTable('condo_agent_domains')) {
                $domain = CondoAgentDomain::query()
                    ->where('host', $host)
                    ->where('is_active', true)
                    ->first();

                if ($domain) {
                    $agent = Agent::query()->where('username', $domain->agent_username)->first();
                    if ($agent) {
                        return $agent;
                    }
                }
            }
        } catch (Throwable) {
        }

        $label = $this->labelFromHost($host);

        if ($label === null) {
            return null;
        }

        return Agent::query()->where('username', $label)->first();
    }

    protected function labelFromHost(string $host): ?string
    {
        foreach ($this->candidateBaseHosts() as $base) {
            $suffix = '.' . $base;
            if (Str::endsWith($host, $suffix)) {
                $label = substr($host, 0, -strlen($suffix));
                return $label !== '' ? $label : null;
            }
        }

        return null;
    }
}
