<?php

namespace App\Support;

use App\Models\Article;
use App\Models\CondoListing;
use App\Models\DeletedItem;
use App\Models\IcpListing;
use App\Models\Listing;
use App\Models\ManagedArticle;
use App\Models\NewsUpdate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecentlyDeletedService
{
    public const GROUP_LISTINGS = 'listings';
    public const GROUP_NEWS = 'news';
    public const GROUP_ARTICLES = 'articles';
    public const GROUP_SOCIAL = 'social';

    public const TYPE_LISTING_IPP = 'listing_ipp';
    public const TYPE_LISTING_ICP = 'listing_icp';
    public const TYPE_LISTING_CONDO = 'listing_condo';
    public const TYPE_NEWS = 'news';
    public const TYPE_ARTICLE = 'article';
    public const TYPE_SOCIAL_SCHEDULE = 'social_schedule';

    public const MIGRATION_COMMAND = 'php artisan migrate';

    public function __construct(
        private readonly FsPosterBridge $fsPosterBridge,
        private readonly CondoWordpressBridge $condoWordpressBridge,
        private readonly ManagedArticleService $managedArticleService,
    ) {
    }

    public function registryAvailable(): bool
    {
        try {
            $connection = (new DeletedItem())->getConnectionName() ?: config('database.default');

            return Schema::connection($connection)->hasTable((new DeletedItem())->getTable());
        } catch (\Throwable) {
            return false;
        }
    }

    public function migrationCommand(): string
    {
        return self::MIGRATION_COMMAND;
    }

    public function rememberArticle(Article $article, string $deletedBy): void
    {
        $this->remember([
            'agent_username' => $article->agent_username,
            'entity_group' => self::GROUP_ARTICLES,
            'entity_type' => self::TYPE_ARTICLE,
            'entity_key' => (string) $article->getKey(),
            'source_key' => 'cms',
            'title' => trim((string) $article->title) !== '' ? trim((string) $article->title) : 'Untitled article',
            'summary' => $this->joinSummary([
                $article->status ? Str::headline((string) $article->status) : null,
                $article->category,
            ]),
            'payload' => [
                'deleted_by' => $deletedBy,
                'article' => $article->getAttributes(),
            ],
        ]);
    }

    public function rememberManagedArticle(ManagedArticle $article, string $deletedBy): void
    {
        $this->remember([
            'agent_username' => $deletedBy,
            'entity_group' => self::GROUP_ARTICLES,
            'entity_type' => self::TYPE_ARTICLE,
            'entity_key' => (string) $article->getKey(),
            'source_key' => 'wordpress',
            'title' => trim((string) $article->post_title) !== '' ? trim((string) $article->post_title) : 'Untitled article',
            'summary' => $this->joinSummary([
                $article->status_label,
                $article->category_names[0] ?? null,
            ]),
            'payload' => [
                'deleted_by' => $deletedBy,
                'source' => 'wordpress',
                'previous_status' => (string) $article->getRawOriginal('post_status'),
            ],
        ]);
    }

    public function rememberNews(NewsUpdate $news, ?string $deletedBy = null): void
    {
        $this->remember([
            'agent_username' => null,
            'entity_group' => self::GROUP_NEWS,
            'entity_type' => self::TYPE_NEWS,
            'entity_key' => (string) $news->getKey(),
            'source_key' => 'condo',
            'title' => trim((string) $news->post_title) !== '' ? trim((string) $news->post_title) : 'Untitled news article',
            'summary' => Str::limit(trim(strip_tags((string) ($news->post_excerpt ?: $news->post_content))), 150),
            'payload' => [
                'deleted_by' => $deletedBy,
                'previous_status' => (string) $news->getRawOriginal('post_status'),
            ],
        ]);
    }

    public function rememberListing(Listing|CondoListing $listing, string $source, string $deletedBy): void
    {
        $type = match ($source) {
            'icp' => self::TYPE_LISTING_ICP,
            'condo' => self::TYPE_LISTING_CONDO,
            default => self::TYPE_LISTING_IPP,
        };

        $payload = [
            'deleted_by' => $deletedBy,
            'propertyid' => (string) $listing->propertyid,
            'photo_paths' => ListingEditor::photoPaths($listing),
        ];

        if ($source === 'condo') {
            $payload['previous_status'] = (string) $listing->getRawOriginal('post_status');
        }

        $this->remember([
            'agent_username' => $listing->username,
            'entity_group' => self::GROUP_LISTINGS,
            'entity_type' => $type,
            'entity_key' => (string) $listing->getKey(),
            'source_key' => $source,
            'title' => trim((string) $listing->propertyname) !== '' ? trim((string) $listing->propertyname) : 'Untitled listing',
            'summary' => $this->joinSummary([
                strtoupper($source),
                $listing->listingtype,
                $listing->propertytype,
                $listing->area ?: $listing->state,
            ]),
            'payload' => $payload,
        ]);
    }

    public function rememberSocialSchedule(string $username, array $group): void
    {
        $scheduledAt = Arr::get($group, 'scheduled_at') instanceof Carbon
            ? Arr::get($group, 'scheduled_at')->format('Y-m-d H:i:s')
            : (string) Arr::get($group, 'scheduled_at_form', '');

        $this->remember([
            'agent_username' => $username,
            'entity_group' => self::GROUP_SOCIAL,
            'entity_type' => self::TYPE_SOCIAL_SCHEDULE,
            'entity_key' => (string) ($group['group_id'] ?? ''),
            'source_key' => 'fs_poster',
            'title' => trim((string) ($group['listing_title'] ?? '')) !== ''
                ? 'Schedule for ' . trim((string) $group['listing_title'])
                : 'FS Poster schedule',
            'summary' => $this->joinSummary([
                collect($group['social_networks'] ?? [])->map(fn (string $network) => Str::headline($network))->implode(', '),
                $scheduledAt !== '' ? Carbon::parse($scheduledAt, config('app.timezone'))->format('M d, Y h:i A') : null,
            ]),
            'payload' => [
                'deleted_by' => $username,
                'group' => [
                    'group_id' => (string) ($group['group_id'] ?? ''),
                    'listing_id' => (int) ($group['listing_id'] ?? 0),
                    'listing_title' => (string) ($group['listing_title'] ?? ''),
                    'channel_ids' => collect($group['channel_ids'] ?? [])->map(fn (mixed $id) => (int) $id)->values()->all(),
                    'scheduled_at' => $scheduledAt,
                    'message' => (string) ($group['message'] ?? ''),
                    'channel_customizations' => collect($group['channel_customizations'] ?? [])
                        ->mapWithKeys(fn (mixed $value, mixed $key) => is_numeric($key) && is_array($value) ? [(int) $key => $value] : [])
                        ->all(),
                ],
            ],
        ]);
    }

    public function itemsForAgent(string $username): Collection
    {
        $registry = $this->visibleRegistryItems($username);
        $registryByLookup = $registry->keyBy(
            fn (DeletedItem $item) => $this->lookupKey($item->entity_type, $item->entity_key)
        );

        return $this->listingItems($username, $registryByLookup)
            ->concat($this->articleItems($username, $registry, $registryByLookup))
            ->concat($this->socialItems($registry))
            ->sortByDesc(fn (array $item) => $item['deleted_at']->timestamp)
            ->values();
    }

    public function restore(string $username, string $type, string $key): string
    {
        return match ($type) {
            self::TYPE_LISTING_IPP => $this->restoreIppListing($username, (int) $key),
            self::TYPE_LISTING_ICP => $this->restoreIcpListing($username, (int) $key),
            self::TYPE_LISTING_CONDO => $this->restoreCondoListing($username, (int) $key),
            self::TYPE_NEWS => $this->restoreNews((int) $key),
            self::TYPE_ARTICLE => $this->restoreArticle($username, (int) $key),
            self::TYPE_SOCIAL_SCHEDULE => $this->restoreSocialSchedule($username, $key),
            default => throw ValidationException::withMessages([
                'type' => 'This deleted item type is not supported.',
            ]),
        };
    }

    public function permanentlyDelete(string $username, string $type, string $key): string
    {
        return match ($type) {
            self::TYPE_LISTING_IPP => $this->purgeIppListing($username, (int) $key),
            self::TYPE_LISTING_ICP => $this->purgeIcpListing($username, (int) $key),
            self::TYPE_LISTING_CONDO => $this->purgeCondoListing($username, (int) $key),
            self::TYPE_NEWS => $this->purgeNews((int) $key),
            self::TYPE_ARTICLE => $this->purgeArticle($username, (int) $key),
            self::TYPE_SOCIAL_SCHEDULE => $this->purgeRegistryOnly($username, self::TYPE_SOCIAL_SCHEDULE, (string) $key, 'Schedule permanently deleted.'),
            default => throw ValidationException::withMessages([
                'type' => 'This deleted item type is not supported.',
            ]),
        };
    }

    private function remember(array $attributes): void
    {
        if (! $this->registryAvailable()) {
            return;
        }

        DeletedItem::query()->updateOrCreate(
            [
                'entity_type' => $attributes['entity_type'],
                'entity_key' => $attributes['entity_key'],
            ],
            [
                'agent_username' => $attributes['agent_username'],
                'entity_group' => $attributes['entity_group'],
                'source_key' => $attributes['source_key'],
                'title' => $attributes['title'],
                'summary' => $attributes['summary'],
                'payload' => $attributes['payload'],
                'deleted_at' => now(),
            ]
        );
    }

    private function visibleRegistryItems(string $username): Collection
    {
        if (! $this->registryAvailable()) {
            return collect();
        }

        /** @var EloquentCollection<int, DeletedItem> $items */
        $items = DeletedItem::query()
            ->visibleToAgent($username)
            ->orderByDesc('deleted_at')
            ->get();

        return $items->values();
    }

    private function listingItems(string $username, Collection $registryByLookup): Collection
    {
        return $this->ippListingItems($username, $registryByLookup)
            ->concat($this->icpListingItems($username, $registryByLookup))
            ->concat($this->condoListingItems($username, $registryByLookup));
    }

    private function ippListingItems(string $username, Collection $registryByLookup): Collection
    {
        if (
            ! Schema::connection('mysql2')->hasTable('softdeleteposts')
            || ! Schema::connection('mysql2')->hasTable('softdeletepostdetails')
        ) {
            return collect();
        }

        return collect(DB::connection('mysql2')
            ->table('softdeleteposts')
            ->where('username', $username)
            ->orderByDesc('deleteddate')
            ->get())
            ->map(function (object $row) use ($registryByLookup) {
                $registry = $registryByLookup->get($this->lookupKey(self::TYPE_LISTING_IPP, (string) $row->id));

                return $this->buildItem([
                    'group' => self::GROUP_LISTINGS,
                    'type' => self::TYPE_LISTING_IPP,
                    'key' => (string) $row->id,
                    'source_key' => 'ipp',
                    'source_label' => 'IPP',
                    'title' => trim((string) $row->propertyname) !== '' ? trim((string) $row->propertyname) : 'Untitled listing',
                    'summary' => $this->joinSummary([
                        $row->listingtype,
                        $row->propertytype,
                        trim((string) $row->area) !== '' ? $row->area : $row->state,
                    ]),
                    'subtitle' => 'Property ID ' . trim((string) $row->propertyid),
                    'deleted_at' => $this->resolveDeletedAt($registry, [
                        $row->deleteddate ?? null,
                        $row->updateddate ?? null,
                        $row->createddate ?? null,
                    ]),
                ]);
            });
    }

    private function icpListingItems(string $username, Collection $registryByLookup): Collection
    {
        $connectionName = IcpListing::resolvedConnectionName();

        if (
            ! Schema::connection($connectionName)->hasTable('softdeletemobileposts')
            || ! Schema::connection($connectionName)->hasTable('softdeletemobilepostdetails')
        ) {
            return collect();
        }

        return collect(DB::connection($connectionName)
            ->table('softdeletemobileposts')
            ->where('username', $username)
            ->orderByDesc('updateddate')
            ->get())
            ->map(function (object $row) use ($registryByLookup) {
                $registry = $registryByLookup->get($this->lookupKey(self::TYPE_LISTING_ICP, (string) $row->id));

                return $this->buildItem([
                    'group' => self::GROUP_LISTINGS,
                    'type' => self::TYPE_LISTING_ICP,
                    'key' => (string) $row->id,
                    'source_key' => 'icp',
                    'source_label' => 'ICP',
                    'title' => trim((string) $row->propertyname) !== '' ? trim((string) $row->propertyname) : 'Untitled listing',
                    'summary' => $this->joinSummary([
                        $row->listingtype,
                        $row->propertytype,
                        trim((string) $row->area) !== '' ? $row->area : $row->state,
                    ]),
                    'subtitle' => 'Property ID ' . trim((string) $row->propertyid),
                    'deleted_at' => $this->resolveDeletedAt($registry, [
                        $row->updateddate ?? null,
                        $row->createddate ?? null,
                    ]),
                ]);
            });
    }

    private function condoListingItems(string $username, Collection $registryByLookup): Collection
    {
        if (! CondoListing::schemaAvailable()) {
            return collect();
        }

        return CondoListing::query()
            ->with('details')
            ->where('post_type', 'properties')
            ->where('post_status', 'trash')
            ->get()
            ->filter(fn (CondoListing $listing) => $listing->username === $username)
            ->map(function (CondoListing $listing) use ($registryByLookup) {
                $registry = $registryByLookup->get($this->lookupKey(self::TYPE_LISTING_CONDO, (string) $listing->getKey()));

                return $this->buildItem([
                    'group' => self::GROUP_LISTINGS,
                    'type' => self::TYPE_LISTING_CONDO,
                    'key' => (string) $listing->getKey(),
                    'source_key' => 'condo',
                    'source_label' => 'Condo',
                    'title' => trim((string) $listing->propertyname) !== '' ? trim((string) $listing->propertyname) : 'Untitled listing',
                    'summary' => $this->joinSummary([
                        $listing->listingtype,
                        $listing->propertytype,
                        $listing->area ?: $listing->state,
                    ]),
                    'subtitle' => 'Property ID ' . trim((string) $listing->propertyid),
                    'deleted_at' => $this->resolveDeletedAt($registry, [
                        $listing->getRawOriginal('post_modified'),
                        $listing->getRawOriginal('post_date'),
                    ]),
                ]);
            })
            ->values();
    }

    private function newsItems(Collection $registryByLookup): Collection
    {
        if (! Schema::connection('condo')->hasTable('posts')) {
            return collect();
        }

        return NewsUpdate::query()
            ->newsPosts()
            ->where('post_status', 'trash')
            ->orderByDesc('post_modified')
            ->get()
            ->map(function (NewsUpdate $news) use ($registryByLookup) {
                $registry = $registryByLookup->get($this->lookupKey(self::TYPE_NEWS, (string) $news->getKey()));

                return $this->buildItem([
                    'group' => self::GROUP_NEWS,
                    'type' => self::TYPE_NEWS,
                    'key' => (string) $news->getKey(),
                    'source_key' => 'condo',
                    'source_label' => 'News',
                    'title' => trim((string) $news->post_title) !== '' ? trim((string) $news->post_title) : 'Untitled news article',
                    'summary' => Str::limit(trim(strip_tags((string) ($news->post_excerpt ?: $news->post_content))), 150),
                    'subtitle' => 'WordPress post #' . $news->getKey(),
                    'deleted_at' => $this->resolveDeletedAt($registry, [
                        $news->getRawOriginal('post_modified'),
                        $news->getRawOriginal('post_date'),
                    ]),
                ]);
            });
    }

    private function articleItems(string $username, Collection $registry, Collection $registryByLookup): Collection
    {
        $wordpressItems = $this->managedArticleService
            ->trashedQueryForAgent($username)
            ->orderByDesc('post_modified')
            ->get()
            ->map(function (ManagedArticle $article) use ($registryByLookup) {
                $registry = $registryByLookup->get($this->lookupKey(self::TYPE_ARTICLE, (string) $article->getKey()));

                return $this->buildItem([
                    'group' => self::GROUP_ARTICLES,
                    'type' => self::TYPE_ARTICLE,
                    'key' => (string) $article->getKey(),
                    'source_key' => 'wordpress',
                    'source_label' => 'Article',
                    'title' => trim((string) $article->post_title) !== '' ? trim((string) $article->post_title) : 'Untitled article',
                    'summary' => $this->joinSummary([
                        $article->status_label,
                        $article->category_names[0] ?? null,
                    ]),
                    'subtitle' => 'WordPress post #' . $article->getKey(),
                    'deleted_at' => $this->resolveDeletedAt($registry, [
                        $article->getRawOriginal('post_modified'),
                        $article->getRawOriginal('post_date'),
                    ]),
                ]);
            });

        $legacyItems = $registry
            ->where('entity_type', self::TYPE_ARTICLE)
            ->filter(fn (DeletedItem $item) => Arr::get($item->payload, 'source', 'cms') !== 'wordpress')
            ->map(function (DeletedItem $item) {
                $payload = (array) ($item->payload['article'] ?? []);

                return $this->buildItem([
                    'group' => self::GROUP_ARTICLES,
                    'type' => self::TYPE_ARTICLE,
                    'key' => (string) $item->entity_key,
                    'source_key' => 'cms',
                    'source_label' => 'Article',
                    'title' => trim((string) $item->title) !== '' ? trim((string) $item->title) : 'Untitled article',
                    'summary' => $this->joinSummary([
                        Arr::get($payload, 'status') ? Str::headline((string) Arr::get($payload, 'status')) : null,
                        Arr::get($payload, 'category'),
                    ]),
                    'subtitle' => 'CMS article #' . $item->entity_key,
                    'deleted_at' => $item->deleted_at ?: now(),
                ]);
            })
            ->values();

        return $wordpressItems
            ->concat($legacyItems)
            ->values();
    }

    private function socialItems(Collection $registry): Collection
    {
        return $registry
            ->where('entity_type', self::TYPE_SOCIAL_SCHEDULE)
            ->map(function (DeletedItem $item) {
                $group = (array) Arr::get($item->payload, 'group', []);
                $channelCount = collect(Arr::get($group, 'channel_ids', []))->count();

                return $this->buildItem([
                    'group' => self::GROUP_SOCIAL,
                    'type' => self::TYPE_SOCIAL_SCHEDULE,
                    'key' => (string) $item->entity_key,
                    'source_key' => 'fs_poster',
                    'source_label' => 'Social',
                    'title' => trim((string) $item->title) !== '' ? trim((string) $item->title) : 'FS Poster schedule',
                    'summary' => $this->joinSummary([
                        trim((string) Arr::get($group, 'listing_title')),
                        $channelCount > 0 ? $channelCount . ' channel' . ($channelCount === 1 ? '' : 's') : null,
                    ]),
                    'subtitle' => 'Schedule group ' . $item->entity_key,
                    'deleted_at' => $item->deleted_at ?: now(),
                ]);
            })
            ->values();
    }

    private function buildItem(array $attributes): array
    {
        /** @var Carbon $deletedAt */
        $deletedAt = $attributes['deleted_at'];

        return [
            'group' => $attributes['group'],
            'type' => $attributes['type'],
            'key' => $attributes['key'],
            'source_key' => $attributes['source_key'],
            'source_label' => $attributes['source_label'],
            'title' => $attributes['title'],
            'summary' => $attributes['summary'] ?? null,
            'subtitle' => $attributes['subtitle'] ?? null,
            'deleted_at' => $deletedAt,
            'deleted_at_label' => $deletedAt->format('M d, Y h:i A'),
        ];
    }

    private function restoreIppListing(string $username, int $id): string
    {
        if (
            ! Schema::connection('mysql2')->hasTable('softdeleteposts')
            || ! Schema::connection('mysql2')->hasTable('softdeletepostdetails')
        ) {
            throw ValidationException::withMessages([
                'type' => 'IPP recycle-bin tables are not available on this environment.',
            ]);
        }

        DB::connection('mysql2')->transaction(function () use ($username, $id) {
            $row = DB::connection('mysql2')
                ->table('softdeleteposts')
                ->where('id', $id)
                ->where('username', $username)
                ->first();

            if (! $row) {
                abort(404);
            }

            $this->assertLegacyListingCanBeRestored('mysql2', 'Posts', (int) $row->id, (string) $row->propertyid);

            DB::connection('mysql2')->table('Posts')->insert([
                'id' => (int) $row->id,
                'username' => $row->username,
                'propertyid' => $row->propertyid,
                'propertyname' => $row->propertyname,
                'propertytype' => $row->propertytype,
                'price' => $row->price,
                'listingtype' => $row->listingtype,
                'state' => $row->state,
                'area' => $row->area,
                'keywords' => $row->keywords,
                'totalphoto' => $row->totalphoto,
                'photopath' => $row->photopath,
                'cobroke' => $row->cobroke,
                'createddate' => $row->createddate,
                'updateddate' => Carbon::now()->format('YmdHis'),
                'isDeleted' => 0,
            ]);

            $details = DB::connection('mysql2')
                ->table('softdeletepostdetails')
                ->where('postid', $id)
                ->get();

            foreach ($details as $detail) {
                DB::connection('mysql2')->table('PostDetails')->insert([
                    'id' => (int) $detail->id,
                    'postid' => (int) $detail->postid,
                    'meta_key' => $detail->meta_key,
                    'meta_value' => $detail->meta_value,
                ]);
            }

            DB::connection('mysql2')->table('softdeletepostdetails')->where('postid', $id)->delete();
            DB::connection('mysql2')->table('softdeleteposts')->where('id', $id)->delete();
        });

        $this->forget(self::TYPE_LISTING_IPP, (string) $id);

        return 'IPP listing restored.';
    }

    private function restoreIcpListing(string $username, int $id): string
    {
        $connectionName = IcpListing::resolvedConnectionName();
        $listingTable = IcpListing::resolvedTableName();
        $detailTable = IcpListing::resolvedDetailTableName();

        if (
            ! Schema::connection($connectionName)->hasTable('softdeletemobileposts')
            || ! Schema::connection($connectionName)->hasTable('softdeletemobilepostdetails')
        ) {
            throw ValidationException::withMessages([
                'type' => 'ICP recycle-bin tables are not available on this environment.',
            ]);
        }

        DB::connection($connectionName)->transaction(function () use ($connectionName, $listingTable, $detailTable, $username, $id) {
            $row = DB::connection($connectionName)
                ->table('softdeletemobileposts')
                ->where('id', $id)
                ->where('username', $username)
                ->first();

            if (! $row) {
                abort(404);
            }

            $this->assertLegacyListingCanBeRestored($connectionName, $listingTable, (int) $row->id, (string) $row->propertyid);

            DB::connection($connectionName)->table($listingTable)->insert([
                'id' => (int) $row->id,
                'username' => $row->username,
                'propertyid' => $row->propertyid,
                'propertyname' => $row->propertyname,
                'propertytype' => $row->propertytype,
                'price' => $row->price,
                'listingtype' => $row->listingtype,
                'state' => $row->state,
                'area' => $row->area,
                'keywords' => $row->keywords,
                'totalphoto' => $row->totalphoto,
                'photopath' => $row->photopath,
                'cobroke' => $row->cobroke,
                'createddate' => $row->createddate,
                'updateddate' => Carbon::now()->format('YmdHis'),
                'isDeleted' => 0,
            ]);

            $details = DB::connection($connectionName)
                ->table('softdeletemobilepostdetails')
                ->where('postid', $id)
                ->get();

            foreach ($details as $detail) {
                DB::connection($connectionName)->table($detailTable)->insert([
                    'id' => (int) $detail->id,
                    'postid' => (int) $detail->postid,
                    'meta_key' => $detail->meta_key,
                    'meta_value' => $detail->meta_value,
                ]);
            }

            DB::connection($connectionName)->table('softdeletemobilepostdetails')->where('postid', $id)->delete();
            DB::connection($connectionName)->table('softdeletemobileposts')->where('id', $id)->delete();
        });

        $this->forget(self::TYPE_LISTING_ICP, (string) $id);

        return 'ICP listing restored.';
    }

    private function restoreCondoListing(string $username, int $id): string
    {
        /** @var CondoListing|null $listing */
        $listing = CondoListing::query()
            ->with('details')
            ->where('post_type', 'properties')
            ->where('post_status', 'trash')
            ->find($id);

        if (! $listing || $listing->username !== $username) {
            abort(404);
        }

        $registry = $this->findRegistryItem($username, self::TYPE_LISTING_CONDO, (string) $id);
        $previousStatus = trim((string) Arr::get($registry?->payload, 'previous_status', 'publish'));
        $previousStatus = $previousStatus !== '' && $previousStatus !== 'trash' ? $previousStatus : 'publish';
        $propertyId = trim((string) $listing->propertyid);

        if ($propertyId !== '') {
            $duplicate = DB::connection('condo')
                ->table('postmeta as meta')
                ->join('posts as posts', 'posts.ID', '=', 'meta.post_id')
                ->where('meta.meta_key', CondoWordpressBridge::META_PROPERTY_ID)
                ->where('meta.meta_value', $propertyId)
                ->where('posts.post_type', 'properties')
                ->whereNotIn('posts.post_status', ['trash', 'auto-draft', 'inherit'])
                ->where('posts.ID', '!=', $id)
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'type' => 'This condo listing cannot be restored because its property ID is already in use.',
                ]);
            }
        }

        $listing->post_status = $previousStatus;
        $listing->post_modified = now()->format('Y-m-d H:i:s');
        $listing->post_modified_gmt = now()->clone()->utc()->format('Y-m-d H:i:s');
        $listing->save();

        $this->forget(self::TYPE_LISTING_CONDO, (string) $id);

        return 'Condo listing restored.';
    }

    private function restoreNews(int $id): string
    {
        /** @var NewsUpdate|null $news */
        $news = NewsUpdate::query()
            ->newsPosts()
            ->where('post_status', 'trash')
            ->find($id);

        if (! $news) {
            abort(404);
        }

        $registry = $this->findRegistryItem(null, self::TYPE_NEWS, (string) $id);
        $previousStatus = trim((string) Arr::get($registry?->payload, 'previous_status', 'draft'));
        $previousStatus = $previousStatus !== '' && $previousStatus !== 'trash' ? $previousStatus : 'draft';
        $now = now();

        $news->post_status = $previousStatus;
        $news->post_modified = $now->format('Y-m-d H:i:s');
        $news->post_modified_gmt = $now->clone()->utc()->format('Y-m-d H:i:s');
        $news->save();

        $this->forget(self::TYPE_NEWS, (string) $id);

        return 'News article restored.';
    }

    private function restoreArticle(string $username, int $id): string
    {
        /** @var ManagedArticle|null $managedArticle */
        $managedArticle = $this->managedArticleService
            ->trashedQueryForAgent($username)
            ->find($id);

        if ($managedArticle) {
            $registry = $this->findRegistryItem($username, self::TYPE_ARTICLE, (string) $id);
            $previousStatus = trim((string) Arr::get($registry?->payload, 'previous_status', 'draft'));
            $previousStatus = $previousStatus !== '' && $previousStatus !== 'trash' ? $previousStatus : 'draft';
            $now = now();

            $managedArticle->post_status = $previousStatus;
            $managedArticle->post_modified = $now->format('Y-m-d H:i:s');
            $managedArticle->post_modified_gmt = $now->copy()->utc()->format('Y-m-d H:i:s');
            $managedArticle->save();

            $this->forget(self::TYPE_ARTICLE, (string) $id);

            return 'Article restored.';
        }

        $item = $this->findRegistryItem($username, self::TYPE_ARTICLE, (string) $id);

        if (! $item) {
            abort(404);
        }

        $payload = (array) Arr::get($item->payload, 'article', []);

        if ($payload === []) {
            throw ValidationException::withMessages([
                'type' => 'This deleted article no longer has enough data to restore.',
            ]);
        }

        $payload['slug'] = $this->uniqueArticleSlug(trim((string) ($payload['slug'] ?? '')));

        if (DB::table('cms_articles')->where('id', $id)->exists()) {
            unset($payload['id']);
        }

        DB::table('cms_articles')->insert($payload);
        $this->forget(self::TYPE_ARTICLE, (string) $id);

        return 'Article restored.';
    }

    private function restoreSocialSchedule(string $username, string $groupId): string
    {
        $item = $this->findRegistryItem($username, self::TYPE_SOCIAL_SCHEDULE, $groupId);

        if (! $item) {
            abort(404);
        }

        $group = (array) Arr::get($item->payload, 'group', []);
        $listingId = (int) Arr::get($group, 'listing_id', 0);

        /** @var CondoListing $listing */
        $listing = CondoListing::query()
            ->active()
            ->with('details')
            ->findOrFail($listingId);

        if ($listing->username !== $username) {
            abort(403);
        }

        $this->fsPosterBridge->storeScheduleGroup($listing, $username, [
            'listing_id' => $listingId,
            'channel_ids' => collect(Arr::get($group, 'channel_ids', []))
                ->map(fn (mixed $value) => (int) $value)
                ->filter()
                ->values()
                ->all(),
            'scheduled_at' => (string) Arr::get($group, 'scheduled_at', now()->addMinutes(15)->format('Y-m-d H:i:s')),
            'message' => (string) Arr::get($group, 'message', ''),
            'channel_customizations' => collect(Arr::get($group, 'channel_customizations', []))
                ->mapWithKeys(fn (mixed $value, mixed $key) => is_numeric($key) && is_array($value) ? [(int) $key => $value] : [])
                ->all(),
        ], $groupId);

        $this->forget(self::TYPE_SOCIAL_SCHEDULE, $groupId);

        return 'Social schedule restored.';
    }

    private function purgeIppListing(string $username, int $id): string
    {
        if (
            ! Schema::connection('mysql2')->hasTable('softdeleteposts')
            || ! Schema::connection('mysql2')->hasTable('softdeletepostdetails')
        ) {
            throw ValidationException::withMessages([
                'type' => 'IPP recycle-bin tables are not available on this environment.',
            ]);
        }

        DB::connection('mysql2')->transaction(function () use ($username, $id) {
            $row = DB::connection('mysql2')
                ->table('softdeleteposts')
                ->where('id', $id)
                ->where('username', $username)
                ->first();

            if (! $row) {
                abort(404);
            }

            $details = DB::connection('mysql2')
                ->table('softdeletepostdetails')
                ->where('postid', $id)
                ->get();

            $this->deleteLegacyPhotoFiles($row->photopath, $details);

            DB::connection('mysql2')->table('softdeletepostdetails')->where('postid', $id)->delete();
            DB::connection('mysql2')->table('softdeleteposts')->where('id', $id)->delete();
        });

        $this->forget(self::TYPE_LISTING_IPP, (string) $id);

        return 'IPP listing permanently deleted.';
    }

    private function purgeIcpListing(string $username, int $id): string
    {
        $connectionName = IcpListing::resolvedConnectionName();

        if (
            ! Schema::connection($connectionName)->hasTable('softdeletemobileposts')
            || ! Schema::connection($connectionName)->hasTable('softdeletemobilepostdetails')
        ) {
            throw ValidationException::withMessages([
                'type' => 'ICP recycle-bin tables are not available on this environment.',
            ]);
        }

        DB::connection($connectionName)->transaction(function () use ($connectionName, $username, $id) {
            $row = DB::connection($connectionName)
                ->table('softdeletemobileposts')
                ->where('id', $id)
                ->where('username', $username)
                ->first();

            if (! $row) {
                abort(404);
            }

            $details = DB::connection($connectionName)
                ->table('softdeletemobilepostdetails')
                ->where('postid', $id)
                ->get();

            $this->deleteLegacyPhotoFiles($row->photopath, $details);

            DB::connection($connectionName)->table('softdeletemobilepostdetails')->where('postid', $id)->delete();
            DB::connection($connectionName)->table('softdeletemobileposts')->where('id', $id)->delete();
        });

        $this->forget(self::TYPE_LISTING_ICP, (string) $id);

        return 'ICP listing permanently deleted.';
    }

    private function purgeCondoListing(string $username, int $id): string
    {
        /** @var CondoListing|null $listing */
        $listing = CondoListing::query()
            ->with('details')
            ->where('post_type', 'properties')
            ->where('post_status', 'trash')
            ->find($id);

        if (! $listing || $listing->username !== $username) {
            abort(404);
        }

        DB::connection('condo')->transaction(function () use ($listing) {
            $this->condoWordpressBridge->deleteListingAssets($listing);

            DB::connection('condo')->table('term_relationships')->where('object_id', $listing->getKey())->delete();
            DB::connection('condo')->table('postmeta')->where('post_id', $listing->getKey())->delete();
            DB::connection('condo')->table('posts')->where('ID', $listing->getKey())->delete();
        });

        $this->forget(self::TYPE_LISTING_CONDO, (string) $id);

        return 'Condo listing permanently deleted.';
    }

    private function purgeNews(int $id): string
    {
        /** @var NewsUpdate|null $news */
        $news = NewsUpdate::query()
            ->newsPosts()
            ->where('post_status', 'trash')
            ->find($id);

        if (! $news) {
            abort(404);
        }

        DB::connection('condo')->transaction(function () use ($id) {
            DB::connection('condo')->table('term_relationships')->where('object_id', $id)->delete();
            DB::connection('condo')->table('postmeta')->where('post_id', $id)->delete();
            DB::connection('condo')->table('posts')->where('ID', $id)->delete();
        });

        $this->forget(self::TYPE_NEWS, (string) $id);

        return 'News article permanently deleted.';
    }

    private function purgeArticle(string $username, int $id): string
    {
        /** @var ManagedArticle|null $article */
        $article = $this->managedArticleService
            ->trashedQueryForAgent($username)
            ->find($id);

        if ($article) {
            DB::connection('condo')->transaction(function () use ($article) {
                $this->managedArticleService->permanentlyDelete($article);
            });

            $this->forget(self::TYPE_ARTICLE, (string) $id);

            return 'Article permanently deleted.';
        }

        return $this->purgeRegistryOnly($username, self::TYPE_ARTICLE, (string) $id, 'Article permanently deleted.');
    }

    private function purgeRegistryOnly(string $username, string $type, string $key, string $message): string
    {
        $item = $this->findRegistryItem($username, $type, $key);

        if (! $item) {
            abort(404);
        }

        $item->delete();

        return $message;
    }

    private function forget(string $type, string $key): void
    {
        if (! $this->registryAvailable()) {
            return;
        }

        DeletedItem::query()
            ->where('entity_type', $type)
            ->where('entity_key', $key)
            ->delete();
    }

    private function findRegistryItem(?string $username, string $type, string $key): ?DeletedItem
    {
        if (! $this->registryAvailable()) {
            return null;
        }

        $query = DeletedItem::query()
            ->where('entity_type', $type)
            ->where('entity_key', $key);

        if ($username === null) {
            $query->whereNull('agent_username');
        } else {
            $query->visibleToAgent($username);
        }

        return $query->first();
    }

    private function resolveDeletedAt(?DeletedItem $registryItem, array $fallbacks): Carbon
    {
        if ($registryItem?->deleted_at instanceof Carbon) {
            return $registryItem->deleted_at->copy();
        }

        foreach ($fallbacks as $value) {
            $parsed = $this->parseDate($value);

            if ($parsed instanceof Carbon) {
                return $parsed;
            }
        }

        return now();
    }

    private function parseDate(mixed $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            if (ctype_digit($value) && strlen($value) === 14) {
                return Carbon::createFromFormat('YmdHis', $value, config('app.timezone'));
            }

            return Carbon::parse($value, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    private function lookupKey(string $type, string $key): string
    {
        return $type . ':' . $key;
    }

    private function joinSummary(array $parts): ?string
    {
        $summary = collect($parts)
            ->map(fn (mixed $value) => trim((string) $value))
            ->filter()
            ->implode(' • ');

        return $summary !== '' ? $summary : null;
    }

    private function assertLegacyListingCanBeRestored(string $connection, string $table, int $id, string $propertyId): void
    {
        if (DB::connection($connection)->table($table)->where('id', $id)->exists()) {
            throw ValidationException::withMessages([
                'type' => 'This listing cannot be restored because its original row ID already exists again.',
            ]);
        }

        if ($propertyId !== '' && DB::connection($connection)->table($table)->where('propertyid', $propertyId)->exists()) {
            throw ValidationException::withMessages([
                'type' => 'This listing cannot be restored because its property ID is already in use.',
            ]);
        }
    }

    private function deleteLegacyPhotoFiles(?string $featuredPhoto, Collection $details): void
    {
        $photoValues = collect([$featuredPhoto])
            ->concat($details->where('meta_key', 'Photos')->pluck('meta_value'));

        $photoPaths = ListingEditor::normalizePhotoPaths($photoValues->implode('::'));

        foreach (array_unique($photoPaths) as $path) {
            $storagePath = ListingEditor::localStoragePhotoPath($path);

            if ($storagePath) {
                Storage::disk('public')->delete($storagePath);
            }
        }
    }

    private function uniqueArticleSlug(string $slug): string
    {
        $slug = trim($slug) !== '' ? trim($slug) : 'restored-article';
        $candidate = $slug;
        $suffix = 2;

        while (DB::table('cms_articles')->where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }
}
