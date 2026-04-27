<?php

namespace App\Support;

use App\Models\BridgeSyncStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class BridgeSyncStatusStore
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_WARNING = 'warning';
    public const STATUS_FAILED = 'failed';

    private ?bool $tableAvailable = null;

    public function tableAvailable(): bool
    {
        if ($this->tableAvailable !== null) {
            return $this->tableAvailable;
        }

        try {
            $model = new BridgeSyncStatus();
            $connection = $model->getConnectionName() ?: config('database.default');

            return $this->tableAvailable = Schema::connection($connection)->hasTable($model->getTable());
        } catch (Throwable) {
            return $this->tableAvailable = false;
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function markSuccess(
        string $resourceType,
        int|string $resourceKey,
        ?string $agentUsername,
        string $syncTarget,
        string $operation,
        ?string $message = null,
        array $context = []
    ): void {
        $this->persist(
            $resourceType,
            $resourceKey,
            $agentUsername,
            $syncTarget,
            $operation,
            self::STATUS_SUCCESS,
            $message,
            null,
            $context
        );
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function markWarning(
        string $resourceType,
        int|string $resourceKey,
        ?string $agentUsername,
        string $syncTarget,
        string $operation,
        string $message,
        array $context = []
    ): void {
        $this->persist(
            $resourceType,
            $resourceKey,
            $agentUsername,
            $syncTarget,
            $operation,
            self::STATUS_WARNING,
            $message,
            $message,
            $context
        );
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function markFailure(
        string $resourceType,
        int|string $resourceKey,
        ?string $agentUsername,
        string $syncTarget,
        string $operation,
        string $error,
        array $context = []
    ): void {
        $this->persist(
            $resourceType,
            $resourceKey,
            $agentUsername,
            $syncTarget,
            $operation,
            self::STATUS_FAILED,
            null,
            $error,
            $context
        );
    }

    /**
     * @return Collection<int, array{
     *     resource_type:string,
     *     resource_key:string,
     *     resource_label:string,
     *     sync_target:string,
     *     sync_target_label:string,
     *     sync_status:string,
     *     message:string,
     *     last_synced_at:\Illuminate\Support\Carbon|null,
     *     last_synced_at_label:?string
     * }>
     */
    public function issuesForResource(string $resourceType, int|string $resourceKey): Collection
    {
        if (! $this->tableAvailable()) {
            return collect();
        }

        return BridgeSyncStatus::query()
            ->where('resource_type', $resourceType)
            ->where('resource_key', (string) $resourceKey)
            ->where('sync_status', '!=', self::STATUS_SUCCESS)
            ->orderByRaw("CASE sync_status WHEN 'failed' THEN 0 ELSE 1 END")
            ->orderByDesc('last_synced_at')
            ->get()
            ->map(fn (BridgeSyncStatus $status) => $this->formatStatus($status))
            ->values();
    }

    /**
     * @param  array<int, int|string>  $resourceKeys
     * @return array<string, array{
     *     count:int,
     *     messages:array<int, string>,
     *     targets:array<int, string>
     * }>
     */
    public function issueMapForResources(string $resourceType, array $resourceKeys): array
    {
        if (! $this->tableAvailable()) {
            return [];
        }

        $keys = collect($resourceKeys)
            ->map(fn (mixed $value) => (string) $value)
            ->filter(fn (string $value) => $value !== '')
            ->unique()
            ->values();

        if ($keys->isEmpty()) {
            return [];
        }

        return BridgeSyncStatus::query()
            ->where('resource_type', $resourceType)
            ->whereIn('resource_key', $keys->all())
            ->where('sync_status', '!=', self::STATUS_SUCCESS)
            ->get()
            ->groupBy('resource_key')
            ->map(function (Collection $statuses) {
                $formatted = $statuses
                    ->map(fn (BridgeSyncStatus $status) => $this->formatStatus($status))
                    ->values();

                return [
                    'count' => $formatted->count(),
                    'messages' => $formatted->pluck('message')->filter()->values()->all(),
                    'targets' => $formatted->pluck('sync_target_label')->filter()->values()->all(),
                ];
            })
            ->all();
    }

    /**
     * @return Collection<int, array{
     *     resource_type:string,
     *     resource_key:string,
     *     resource_label:string,
     *     sync_target:string,
     *     sync_target_label:string,
     *     sync_status:string,
     *     message:string,
     *     last_synced_at:\Illuminate\Support\Carbon|null,
     *     last_synced_at_label:?string
     * }>
     */
    public function recentIssuesForAgent(string $username, int $limit = 5): Collection
    {
        if (! $this->tableAvailable()) {
            return collect();
        }

        return BridgeSyncStatus::query()
            ->where('agent_username', trim($username))
            ->where('sync_status', '!=', self::STATUS_SUCCESS)
            ->orderByRaw("CASE sync_status WHEN 'failed' THEN 0 ELSE 1 END")
            ->orderByDesc('last_synced_at')
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (BridgeSyncStatus $status) => $this->formatStatus($status))
            ->values();
    }

    public function issueCountForAgent(string $username): int
    {
        if (! $this->tableAvailable()) {
            return 0;
        }

        return (int) BridgeSyncStatus::query()
            ->where('agent_username', trim($username))
            ->where('sync_status', '!=', self::STATUS_SUCCESS)
            ->count();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function persist(
        string $resourceType,
        int|string $resourceKey,
        ?string $agentUsername,
        string $syncTarget,
        string $operation,
        string $syncStatus,
        ?string $message,
        ?string $error,
        array $context = []
    ): void {
        if (! $this->tableAvailable()) {
            return;
        }

        BridgeSyncStatus::query()->updateOrCreate(
            [
                'resource_type' => trim($resourceType),
                'resource_key' => (string) $resourceKey,
                'sync_target' => trim($syncTarget),
            ],
            [
                'agent_username' => $this->trimOrNull($agentUsername),
                'last_operation' => $this->trimOrNull($operation),
                'sync_status' => trim($syncStatus),
                'last_message' => $this->trimOrNull($message),
                'last_error' => $this->trimOrNull($error),
                'last_context' => $context !== [] ? $context : null,
                'last_synced_at' => now(),
            ]
        );
    }

    /**
     * @return array{
     *     resource_type:string,
     *     resource_key:string,
     *     resource_label:string,
     *     sync_target:string,
     *     sync_target_label:string,
     *     sync_status:string,
     *     message:string,
     *     last_synced_at:\Illuminate\Support\Carbon|null,
     *     last_synced_at_label:?string
     * }
     */
    private function formatStatus(BridgeSyncStatus $status): array
    {
        $context = is_array($status->last_context) ? $status->last_context : [];
        $message = $this->trimOrNull($status->last_error)
            ?? $this->trimOrNull($status->last_message)
            ?? 'The last bridge attempt did not return a message.';

        return [
            'resource_type' => (string) $status->resource_type,
            'resource_key' => (string) $status->resource_key,
            'resource_label' => $this->resourceLabel((string) $status->resource_type, (string) $status->resource_key, $context),
            'sync_target' => (string) $status->sync_target,
            'sync_target_label' => $this->targetLabel((string) $status->sync_target),
            'sync_status' => (string) $status->sync_status,
            'message' => $message,
            'last_synced_at' => $status->last_synced_at,
            'last_synced_at_label' => $status->last_synced_at?->format('M d, Y h:i A'),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function resourceLabel(string $resourceType, string $resourceKey, array $context): string
    {
        $propertyName = $this->trimOrNull((string) ($context['property_name'] ?? ''));

        return $propertyName ?? ($resourceType . ' #' . $resourceKey);
    }

    private function targetLabel(string $syncTarget): string
    {
        return match ($syncTarget) {
            'fs_poster' => 'FS Poster',
            'public_site' => 'Public Site',
            'wordpress_cache' => 'WordPress Cache',
            default => str_replace('_', ' ', ucfirst($syncTarget)),
        };
    }

    private function trimOrNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
