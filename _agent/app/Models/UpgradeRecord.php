<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpgradeRecord extends Model
{
    protected $connection = 'mysql';
    protected $table = 'UpgradeRecords';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'adminaccess',
        'target',
        'currentpackage',
        'upgradepackage',
        'cost',
        'status',
        'processby',
        'createddate',
        'processdate',
        'reason',
        'upgradecount',
    ];

    public function currentPackageRelation(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'currentpackage', 'id');
    }

    public function upgradePackageRelation(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'upgradepackage', 'id');
    }

    public function scopeForAgent(Builder $query, string $username): Builder
    {
        return $query->where('target', $username);
    }

    public function getCurrentPackageNameAttribute(): string
    {
        $name = trim((string) ($this->currentPackageRelation?->display_name ?? $this->currentPackageRelation?->name ?? ''));

        return $name !== '' ? $name : ('Package #' . (string) $this->currentpackage);
    }

    public function getUpgradePackageNameAttribute(): string
    {
        $name = trim((string) ($this->upgradePackageRelation?->display_name ?? $this->upgradePackageRelation?->name ?? ''));

        return $name !== '' ? $name : ('Package #' . (string) $this->upgradepackage);
    }

    public function getFormattedCreatedDateAttribute(): ?string
    {
        return $this->formatLegacyDateValue($this->attributes['createddate'] ?? null);
    }

    public function getFormattedProcessDateAttribute(): ?string
    {
        return $this->formatLegacyDateValue($this->attributes['processdate'] ?? null);
    }

    public function getCreatedTimestampAttribute(): int
    {
        return $this->normalizeTimestamp($this->attributes['createddate'] ?? null) ?? 0;
    }

    public function getStatusLabelAttribute(): string
    {
        return match (strtoupper(trim((string) ($this->attributes['status'] ?? '')))) {
            'A', '1' => 'Completed',
            'R' => 'Rejected',
            'C' => 'Cancelled',
            default => 'Pending',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status_label) {
            'Completed' => 'badge-success',
            'Rejected', 'Cancelled' => 'badge-danger',
            default => 'badge-warning',
        };
    }

    public function getAmountFormattedAttribute(): string
    {
        return 'RM ' . number_format((float) ($this->attributes['cost'] ?? 0), 2);
    }

    public function getInvoiceNumberAttribute(): string
    {
        return 'INV-UR-' . str_pad((string) $this->getKey(), 6, '0', STR_PAD_LEFT);
    }

    public function getInvoiceFilenameAttribute(): string
    {
        return strtolower($this->invoice_number . '-' . $this->target . '.html');
    }

    public function getRequestedByLabelAttribute(): string
    {
        $value = trim((string) ($this->attributes['username'] ?? ''));

        return $value !== '' ? $value : '-';
    }

    public function getProcessedByLabelAttribute(): string
    {
        $value = trim((string) ($this->attributes['processby'] ?? ''));

        return $value !== '' ? $value : '-';
    }

    protected function formatLegacyDateValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            if (ctype_digit($value) && strlen($value) === 14) {
                return Carbon::createFromFormat('YmdHis', $value, config('app.timezone'))->format('M d, Y h:i A');
            }

            if (ctype_digit($value) && strlen($value) === 10) {
                return Carbon::createFromTimestamp((int) $value, config('app.timezone'))->format('M d, Y h:i A');
            }

            return Carbon::parse($value, config('app.timezone'))->format('M d, Y h:i A');
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function normalizeTimestamp(mixed $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            if (ctype_digit($value) && strlen($value) === 10) {
                return (int) $value;
            }

            if (ctype_digit($value) && strlen($value) === 14) {
                return Carbon::createFromFormat('YmdHis', $value, config('app.timezone'))->timestamp;
            }

            return Carbon::parse($value, config('app.timezone'))->timestamp;
        } catch (\Throwable) {
            return null;
        }
    }
}
