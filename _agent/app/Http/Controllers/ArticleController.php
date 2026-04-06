<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Support\RecentlyDeletedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function __construct(private readonly RecentlyDeletedService $recentlyDeletedService)
    {
    }

    public function index(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $query = Article::where('agent_username', $username);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $articles = $query->orderBy('created_at', 'desc')->paginate(12);
        $categories = Article::where('agent_username', $username)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('articles.index', compact('articles', 'categories'));
    }

    public function create()
    {
        return view('articles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|string',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published',
        ]);

        $data = $request->except('featured_image', 'tags');
        $data['agent_username'] = Auth::guard('agent')->user()->username;
        $data['slug'] = Str::slug($request->title) . '-' . Str::random(5);

        if ($request->filled('tags')) {
            $data['tags'] = array_map('trim', explode(',', $request->tags));
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('articles', 'public');
        }

        if ($request->status === 'published') {
            $data['published_at'] = now();
        }

        Article::create($data);

        return redirect()->route('articles.index')->with('success', 'Article created successfully.');
    }

    public function edit(Article $article)
    {
        $this->authorizeAgent($article);
        return view('articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $this->authorizeAgent($article);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|string',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,archived',
        ]);

        $data = $request->except('featured_image', 'tags');

        if ($request->filled('tags')) {
            $data['tags'] = array_map('trim', explode(',', $request->tags));
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('articles', 'public');
        }

        if ($request->status === 'published' && !$article->published_at) {
            $data['published_at'] = now();
        }

        $article->update($data);

        return redirect()->route('articles.index')->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        $this->authorizeAgent($article);

        if (! $this->recentlyDeletedService->registryAvailable()) {
            return redirect()
                ->route('articles.index')
                ->withErrors([
                    'type' => 'Run php artisan migrate first. Article recovery needs the shared cms_deleted_items table.',
                ]);
        }

        $this->recentlyDeletedService->rememberArticle($article, Auth::guard('agent')->user()->username);
        $article->delete();

        return redirect()
            ->route('articles.index')
            ->with('success', 'Article moved to Recently Deleted.');
    }

    private function authorizeAgent(Article $article): void
    {
        if ($article->agent_username !== Auth::guard('agent')->user()->username) {
            abort(403);
        }
    }
}
