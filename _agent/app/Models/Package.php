<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    public const LEGACY_CONDO_PREMIUM_NAMES = [
        'Premium',
        'Premium+',
    ];

    public const LEGACY_CONDO_PREMIUM_LITE_NAMES = [
        'ICPPremium',
    ];

    public const CONDO_PREMIUM_NAME = 'Condo Premium Package';
    public const CONDO_PREMIUM_LITE_NAME = 'Condo Premium Lite Package';

    public const CONDO_PACKAGE_NAMES = [
        self::CONDO_PREMIUM_NAME,
        self::CONDO_PREMIUM_LITE_NAME,
    ];

    private const CONDO_PREMIUM_LISTING_LIMIT = 500;
    private const CONDO_PREMIUM_DAILY_LIMIT = 100;
    private const CONDO_PREMIUM_LITE_LISTING_LIMIT = 100;
    private const CONDO_PREMIUM_LITE_DAILY_LIMIT = 50;

    protected $table = 'Packages';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'creditlimit',
        'maxaccount',
        'token',
        'icp_limit',
        'ipp_limit',
        'local_limit',
        'cost',
        'color',
        'is_unknown',
        'group_limit',
        'join_group_limit',
    ];

    protected $casts = [
        'creditlimit' => 'int',
        'maxaccount' => 'int',
        'token' => 'int',
        'icp_limit' => 'int',
        'ipp_limit' => 'int',
        'local_limit' => 'int',
        'cost' => 'float',
        'is_unknown' => 'int',
        'group_limit' => 'int',
        'join_group_limit' => 'int',
    ];

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'package', 'id');
    }

    public function getFormattedCostAttribute(): string
    {
        return 'RM ' . number_format((float) $this->cost, 2);
    }

    public function getDisplayNameAttribute(): string
    {
        return match ($this->condo_family_key) {
            'condo_premium' => self::CONDO_PREMIUM_NAME,
            'condo_premium_lite' => self::CONDO_PREMIUM_LITE_NAME,
            default => trim((string) $this->name),
        };
    }

    public function getCondoFamilyKeyAttribute(): ?string
    {
        $name = trim((string) $this->name);

        if (in_array($name, array_merge(self::LEGACY_CONDO_PREMIUM_NAMES, [self::CONDO_PREMIUM_NAME]), true)) {
            return 'condo_premium';
        }

        if (in_array($name, array_merge(self::LEGACY_CONDO_PREMIUM_LITE_NAMES, [self::CONDO_PREMIUM_LITE_NAME]), true)) {
            return 'condo_premium_lite';
        }

        return null;
    }

    public function getIsCondoPackageAttribute(): bool
    {
        return $this->condo_family_key !== null;
    }

    public function getPlanGroupKeyAttribute(): string
    {
        return match ($this->condo_family_key) {
            'condo_premium' => 'group:condo_premium',
            'condo_premium_lite' => 'group:condo_premium_lite',
            default => 'group:standard:' . implode('|', [
                number_format((float) $this->cost, 2, '.', ''),
                (string) $this->creditlimit,
                (string) $this->maxaccount,
                trim((string) $this->name),
            ]),
        };
    }

    public function getListingSpaceLimitAttribute(): int
    {
        return match ($this->condo_family_key) {
            'condo_premium' => self::CONDO_PREMIUM_LISTING_LIMIT,
            'condo_premium_lite' => self::CONDO_PREMIUM_LITE_LISTING_LIMIT,
            default => max(0, (int) ($this->attributes['maxaccount'] ?? 0)),
        };
    }

    public function getDailyAutoSubmitLimitAttribute(): int
    {
        return match ($this->condo_family_key) {
            'condo_premium' => self::CONDO_PREMIUM_DAILY_LIMIT,
            'condo_premium_lite' => self::CONDO_PREMIUM_LITE_DAILY_LIMIT,
            default => max(0, (int) ($this->attributes['creditlimit'] ?? 0)),
        };
    }

    public function getArticleSubmissionUsesCreditAttribute(): bool
    {
        return $this->condo_family_key === 'condo_premium_lite';
    }

    /**
     * @return array<int, array{label:string,value:string,note:?string}>
     */
    public function getBillingFeatureRowsAttribute(): array
    {
        if ($this->is_condo_package) {
            return [
                [
                    'label' => 'Listing Space',
                    'value' => number_format($this->listing_space_limit) . ' spaces',
                    'note' => 'Delete a condo listing and the space returns automatically.',
                ],
                [
                    'label' => 'Daily Credits',
                    'value' => number_format($this->daily_auto_submit_limit) . ' per day',
                    'note' => $this->article_submission_uses_credit
                        ? 'Social schedules use 1 credit each. Publishing or scheduling an article also uses 1 credit on Lite.'
                        : 'Social schedules use 1 credit each. The daily pool resets automatically.',
                ],
                [
                    'label' => 'Articles',
                    'value' => $this->article_submission_uses_credit ? 'Unlimited access' : 'Unlimited publishing',
                    'note' => $this->article_submission_uses_credit
                        ? 'Drafts stay unlimited. Publishing or scheduling uses daily credit on Lite.'
                        : 'Draft, publish, and schedule directly from Laravel.',
                ],
                [
                    'label' => 'Social Media',
                    'value' => 'Included',
                    'note' => 'Manage channels and schedule condo listings from Laravel with the same package.',
                ],
            ];
        }

        return [
            [
                'label' => 'Credit Limit',
                'value' => number_format((int) $this->creditlimit),
                'note' => null,
            ],
            [
                'label' => 'Max Accounts',
                'value' => number_format((int) $this->maxaccount),
                'note' => null,
            ],
        ];
    }
}
