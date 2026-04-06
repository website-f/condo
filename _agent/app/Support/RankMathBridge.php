<?php

namespace App\Support;

use App\Models\CondoListing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RankMathBridge
{
    public const TITLE = 'rank_math_title';
    public const DESCRIPTION = 'rank_math_description';
    public const FACEBOOK_TITLE = 'rank_math_facebook_title';
    public const FACEBOOK_DESCRIPTION = 'rank_math_facebook_description';
    public const TWITTER_TITLE = 'rank_math_twitter_title';
    public const TWITTER_DESCRIPTION = 'rank_math_twitter_description';
    public const FOCUS_KEYWORD = 'rank_math_focus_keyword';
    public const CANONICAL_URL = 'rank_math_canonical_url';
    public const ROBOTS = 'rank_math_robots';

    private const AUTO_TITLE = 'condo_rank_math_auto_title';
    private const AUTO_DESCRIPTION = 'condo_rank_math_auto_description';
    private const AUTO_KEYWORD = 'condo_rank_math_auto_focus_keyword';

    /**
     * @return array{
     *     meta_title:string,
     *     meta_description:string,
     *     focus_keyword:string,
     *     canonical_url:string,
     *     og_title:string,
     *     og_description:string,
     *     twitter_title:string,
     *     twitter_description:string,
     *     robots:array<int, string>
     * }
     */
    public function currentSeoData(CondoListing $listing): array
    {
        $derived = $this->derivedDefaults($listing);

        return [
            'meta_title' => $this->getMetaValue((int) $listing->getKey(), self::TITLE) ?: $derived['meta_title'],
            'meta_description' => $this->getMetaValue((int) $listing->getKey(), self::DESCRIPTION) ?: $derived['meta_description'],
            'focus_keyword' => $this->getMetaValue((int) $listing->getKey(), self::FOCUS_KEYWORD) ?: $derived['focus_keyword'],
            'canonical_url' => $this->getMetaValue((int) $listing->getKey(), self::CANONICAL_URL) ?: $derived['canonical_url'],
            'og_title' => $this->getMetaValue((int) $listing->getKey(), self::FACEBOOK_TITLE)
                ?: ($this->getMetaValue((int) $listing->getKey(), self::TITLE) ?: $derived['meta_title']),
            'og_description' => $this->getMetaValue((int) $listing->getKey(), self::FACEBOOK_DESCRIPTION)
                ?: ($this->getMetaValue((int) $listing->getKey(), self::DESCRIPTION) ?: $derived['meta_description']),
            'twitter_title' => $this->getMetaValue((int) $listing->getKey(), self::TWITTER_TITLE)
                ?: ($this->getMetaValue((int) $listing->getKey(), self::TITLE) ?: $derived['meta_title']),
            'twitter_description' => $this->getMetaValue((int) $listing->getKey(), self::TWITTER_DESCRIPTION)
                ?: ($this->getMetaValue((int) $listing->getKey(), self::DESCRIPTION) ?: $derived['meta_description']),
            'robots' => $this->decodeRobots($this->getMetaValue((int) $listing->getKey(), self::ROBOTS)),
        ];
    }

    public function syncDerivedDefaults(CondoListing $listing): void
    {
        $postId = (int) $listing->getKey();
        $derived = $this->derivedDefaults($listing);
        $autoMetaMap = [
            self::TITLE => self::AUTO_TITLE,
            self::DESCRIPTION => self::AUTO_DESCRIPTION,
            self::FOCUS_KEYWORD => self::AUTO_KEYWORD,
        ];

        foreach ($autoMetaMap as $metaKey => $autoKey) {
            $currentValue = $this->getMetaValue($postId, $metaKey);
            $previousAuto = $this->getMetaValue($postId, $autoKey);
            $newValue = $derived[$this->fieldNameForMetaKey($metaKey)];

            if ($currentValue === null || trim($currentValue) === '' || $currentValue === $previousAuto) {
                $this->upsertMeta($postId, $metaKey, $newValue);
            }

            $this->upsertMeta($postId, $autoKey, $newValue);
        }
    }

    public function saveManualSeo(CondoListing $listing, array $validated): void
    {
        $postId = (int) $listing->getKey();
        $normalized = [
            self::TITLE => trim((string) ($validated['meta_title'] ?? '')),
            self::DESCRIPTION => trim((string) ($validated['meta_description'] ?? '')),
            self::FOCUS_KEYWORD => trim((string) ($validated['focus_keyword'] ?? '')),
            self::CANONICAL_URL => trim((string) ($validated['canonical_url'] ?? '')),
            self::FACEBOOK_TITLE => trim((string) ($validated['og_title'] ?? '')),
            self::FACEBOOK_DESCRIPTION => trim((string) ($validated['og_description'] ?? '')),
            self::TWITTER_TITLE => trim((string) ($validated['twitter_title'] ?? '')),
            self::TWITTER_DESCRIPTION => trim((string) ($validated['twitter_description'] ?? '')),
        ];

        foreach ($normalized as $metaKey => $metaValue) {
            $this->upsertMeta($postId, $metaKey, $metaValue);
        }

        $robots = collect($validated['robots'] ?? [])
            ->map(fn (mixed $value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->upsertSerializedMeta($postId, self::ROBOTS, $robots);
    }

    /**
     * @return array{
     *     meta_title:string,
     *     meta_description:string,
     *     focus_keyword:string,
     *     canonical_url:string
     * }
     */
    private function derivedDefaults(CondoListing $listing): array
    {
        $title = trim((string) $listing->propertyname);
        $descriptionSource = trim((string) ($listing->description_text ?: $listing->keywords));

        if ($descriptionSource === '') {
            $descriptionSource = implode(', ', array_filter([
                trim((string) $listing->propertyname),
                trim((string) $listing->propertytype),
                trim((string) $listing->listingtype),
                trim((string) $listing->area),
                trim((string) $listing->state),
            ]));
        }

        $canonicalUrl = trim((string) ($listing->getAttribute('guid') ?: ''));

        if ($canonicalUrl === '') {
            $canonicalUrl = rtrim(CondoWordpressBridge::siteBaseUrl(), '/') . '/?post_type=properties&p=' . $listing->getKey();
        }

        return [
            'meta_title' => $title,
            'meta_description' => trim(Str::limit($descriptionSource, 160, '')),
            'focus_keyword' => trim((string) ($listing->keywords ?: $title)),
            'canonical_url' => $canonicalUrl,
        ];
    }

    private function fieldNameForMetaKey(string $metaKey): string
    {
        return match ($metaKey) {
            self::TITLE => 'meta_title',
            self::DESCRIPTION => 'meta_description',
            default => 'focus_keyword',
        };
    }

    private function getMetaValue(int $postId, string $metaKey): ?string
    {
        $value = DB::connection('condo')
            ->table('postmeta')
            ->where('post_id', $postId)
            ->where('meta_key', $metaKey)
            ->value('meta_value');

        return is_string($value) ? $value : null;
    }

    private function upsertMeta(int $postId, string $metaKey, string $metaValue): void
    {
        $metaValue = trim($metaValue);

        if ($metaValue === '') {
            DB::connection('condo')
                ->table('postmeta')
                ->where('post_id', $postId)
                ->where('meta_key', $metaKey)
                ->delete();

            return;
        }

        $existingMetaId = DB::connection('condo')
            ->table('postmeta')
            ->where('post_id', $postId)
            ->where('meta_key', $metaKey)
            ->value('meta_id');

        if ($existingMetaId) {
            DB::connection('condo')
                ->table('postmeta')
                ->where('meta_id', $existingMetaId)
                ->update(['meta_value' => $metaValue]);

            DB::connection('condo')
                ->table('postmeta')
                ->where('post_id', $postId)
                ->where('meta_key', $metaKey)
                ->where('meta_id', '!=', $existingMetaId)
                ->delete();

            return;
        }

        DB::connection('condo')->table('postmeta')->insert([
            'post_id' => $postId,
            'meta_key' => $metaKey,
            'meta_value' => $metaValue,
        ]);
    }

    /**
     * @param  array<int, string>  $metaValue
     */
    private function upsertSerializedMeta(int $postId, string $metaKey, array $metaValue): void
    {
        if ($metaValue === []) {
            DB::connection('condo')
                ->table('postmeta')
                ->where('post_id', $postId)
                ->where('meta_key', $metaKey)
                ->delete();

            return;
        }

        $this->upsertMeta($postId, $metaKey, serialize($metaValue));
    }

    /**
     * @return array<int, string>
     */
    private function decodeRobots(?string $rawValue): array
    {
        if ($rawValue === null || trim($rawValue) === '') {
            return [];
        }

        $decoded = @unserialize($rawValue, ['allowed_classes' => false]);

        if (is_array($decoded)) {
            return collect($decoded)
                ->map(fn (mixed $value) => trim((string) $value))
                ->filter()
                ->values()
                ->all();
        }

        return collect(preg_split('/[\s,|]+/', $rawValue) ?: [])
            ->map(fn (mixed $value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }
}
