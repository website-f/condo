<?php

namespace App\Http\Controllers;

use App\Support\RecentlyDeletedService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RecentlyDeletedController extends Controller
{
    private const SECTIONS = [
        'all' => 'All',
        RecentlyDeletedService::GROUP_LISTINGS => 'Listings',
        RecentlyDeletedService::GROUP_NEWS => 'News',
        RecentlyDeletedService::GROUP_ARTICLES => 'Articles',
        RecentlyDeletedService::GROUP_SOCIAL => 'Social',
    ];

    public function __construct(private readonly RecentlyDeletedService $recentlyDeletedService)
    {
    }

    public function index(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $activeSection = $this->resolveSection($request->query('section'));
        $search = trim((string) $request->query('search', ''));
        $allItems = $this->recentlyDeletedService->itemsForAgent($username);
        $counts = collect(self::SECTIONS)
            ->mapWithKeys(function (string $label, string $key) use ($allItems) {
                if ($key === 'all') {
                    return [$key => $allItems->count()];
                }

                return [$key => $allItems->where('group', $key)->count()];
            });

        $items = $allItems
            ->when($activeSection !== 'all', fn (Collection $collection) => $collection->where('group', $activeSection)->values())
            ->when($search !== '', function (Collection $collection) use ($search) {
                $needle = Str::lower($search);

                return $collection->filter(function (array $item) use ($needle) {
                    return Str::contains(Str::lower(implode(' ', array_filter([
                        $item['title'] ?? '',
                        $item['summary'] ?? '',
                        $item['subtitle'] ?? '',
                        $item['source_label'] ?? '',
                    ]))), $needle);
                })->values();
            });

        $sections = collect(self::SECTIONS)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'count' => $counts->get($key, 0),
            ])
            ->values();

        $stats = [
            'total' => $counts->get('all', 0),
            'listings' => $counts->get(RecentlyDeletedService::GROUP_LISTINGS, 0),
            'news' => $counts->get(RecentlyDeletedService::GROUP_NEWS, 0),
            'articles' => $counts->get(RecentlyDeletedService::GROUP_ARTICLES, 0),
            'social' => $counts->get(RecentlyDeletedService::GROUP_SOCIAL, 0),
        ];

        return view('recently-deleted.index', [
            'items' => $this->paginateCollection($items, $request, 12),
            'sections' => $sections,
            'activeSection' => $activeSection,
            'stats' => $stats,
            'search' => $search,
            'registryAvailable' => $this->recentlyDeletedService->registryAvailable(),
            'migrationCommand' => $this->recentlyDeletedService->migrationCommand(),
        ]);
    }

    public function restore(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'key' => ['required', 'string'],
        ]);

        $username = Auth::guard('agent')->user()->username;
        $message = $this->recentlyDeletedService->restore($username, $validated['type'], $validated['key']);

        return redirect()
            ->route('recently-deleted.index', $request->only(['section', 'search', 'page']))
            ->with('success', $message);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'key' => ['required', 'string'],
        ]);

        $username = Auth::guard('agent')->user()->username;
        $message = $this->recentlyDeletedService->permanentlyDelete($username, $validated['type'], $validated['key']);

        return redirect()
            ->route('recently-deleted.index', $request->only(['section', 'search', 'page']))
            ->with('success', $message);
    }

    private function resolveSection(?string $section): string
    {
        $section = strtolower(trim((string) $section));

        return array_key_exists($section, self::SECTIONS) ? $section : 'all';
    }

    private function paginateCollection(Collection $items, Request $request, int $perPage): LengthAwarePaginator
    {
        $currentPage = max(1, (int) $request->query('page', 1));
        $currentItems = $items->forPage($currentPage, $perPage)->values();

        return new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->except('page'),
            ]
        );
    }
}
