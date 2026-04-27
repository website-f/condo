<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Article;
use App\Models\IcpListing;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class PublicSiteController extends Controller
{
    public function home(Request $request)
    {
        $agent = $this->agent($request);

        $listings = $this->fetchListings($agent->username, perPage: 6, page: 1);
        $articles = Article::query()
            ->where('agent_username', $agent->username)
            ->published()
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('public.home', compact('agent', 'listings', 'articles'));
    }

    public function listings(Request $request)
    {
        $agent = $this->agent($request);

        $sourceFilter = strtolower(trim((string) $request->query('source', 'all')));
        if (! in_array($sourceFilter, ['all', 'ipp', 'icp'], true)) {
            $sourceFilter = 'all';
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 12;

        $listings = $this->fetchListings($agent->username, $perPage, $page, $sourceFilter);

        return view('public.listings', [
            'agent' => $agent,
            'listings' => $listings,
            'sourceFilter' => $sourceFilter,
        ]);
    }

    public function listing(Request $request, string $source, $id)
    {
        $agent = $this->agent($request);

        $source = strtolower($source);
        if (! in_array($source, ['ipp', 'icp'], true)) {
            abort(404);
        }

        $model = $source === 'ipp'
            ? Listing::query()->where('username', $agent->username)->where('id', $id)->active()->first()
            : IcpListing::query()->where('username', $agent->username)->where('id', $id)->active()->first();

        if (! $model) {
            abort(404);
        }

        $model->load('details');
        $model->source_key = $source;

        return view('public.listing', [
            'agent' => $agent,
            'listing' => $model,
            'source' => $source,
        ]);
    }

    public function articles(Request $request)
    {
        $agent = $this->agent($request);

        $articles = Article::query()
            ->where('agent_username', $agent->username)
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('public.articles', compact('agent', 'articles'));
    }

    public function article(Request $request, string $slug)
    {
        $agent = $this->agent($request);

        $article = Article::query()
            ->where('agent_username', $agent->username)
            ->where('slug', $slug)
            ->published()
            ->first();

        if (! $article) {
            abort(404);
        }

        return view('public.article', compact('agent', 'article'));
    }

    protected function agent(Request $request): Agent
    {
        $agent = $request->attributes->get('public_agent');

        if (! $agent instanceof Agent) {
            abort(404);
        }

        return $agent;
    }

    protected function fetchListings(string $username, int $perPage, int $page, string $sourceFilter = 'all'): LengthAwarePaginator
    {
        $ipp = $sourceFilter === 'icp'
            ? collect()
            : Listing::query()
                ->where('username', $username)
                ->active()
                ->orderByDesc('updateddate')
                ->orderByDesc('id')
                ->limit(500)
                ->get()
                ->each(fn ($l) => $l->source_key = 'ipp');

        $icp = $sourceFilter === 'ipp'
            ? collect()
            : IcpListing::query()
                ->where('username', $username)
                ->active()
                ->orderByDesc('updateddate')
                ->orderByDesc('id')
                ->limit(500)
                ->get()
                ->each(fn ($l) => $l->source_key = 'icp');

        $merged = $ipp->concat($icp)
            ->sortByDesc(fn ($l) => (string) ($l->updateddate ?? $l->createddate ?? ''))
            ->values();

        $total = $merged->count();
        $items = $merged->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()]
        );
    }
}
