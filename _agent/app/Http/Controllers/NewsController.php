<?php

namespace App\Http\Controllers;

use App\Models\NewsUpdate;
use App\Support\RecentlyDeletedService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    public function __construct(private readonly RecentlyDeletedService $recentlyDeletedService)
    {
    }

    public function index(Request $request)
    {
        $query = NewsUpdate::manageable();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('post_title', 'like', '%' . $request->search . '%')
                  ->orWhere('post_content', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('post_status', $request->status);
        }

        $news = $query->orderBy('post_date', 'desc')->paginate(12);
        $statuses = NewsUpdate::manageable()
            ->select('post_status')
            ->distinct()
            ->pluck('post_status')
            ->filter()
            ->values();

        return view('news.index', compact('news', 'statuses'));
    }

    public function create()
    {
        return view('news.create', [
            'article' => new NewsUpdate([
                'post_status' => 'draft',
                'post_date' => now()->format('Y-m-d H:i:s'),
            ]),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateNews($request);
        $article = new NewsUpdate();

        $this->fillAndSaveNews($article, $validated, true);

        return redirect()
            ->route('news.show', $article->ID)
            ->with('success', 'News article created successfully.');
    }

    public function show($id)
    {
        $article = NewsUpdate::manageable()->findOrFail($id);
        return view('news.show', compact('article'));
    }

    public function edit($id)
    {
        $article = NewsUpdate::manageable()->findOrFail($id);

        return view('news.edit', [
            'article' => $article,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateNews($request);
        $article = NewsUpdate::manageable()->findOrFail($id);

        $this->fillAndSaveNews($article, $validated, false);

        return redirect()
            ->route('news.show', $article->ID)
            ->with('success', 'News article updated successfully.');
    }

    public function destroy($id)
    {
        $article = NewsUpdate::manageable()->findOrFail($id);
        $modifiedAt = now();

        $this->recentlyDeletedService->rememberNews($article);

        $article->post_status = 'trash';
        $article->post_modified = $modifiedAt->format('Y-m-d H:i:s');
        $article->post_modified_gmt = $modifiedAt->copy()->utc()->format('Y-m-d H:i:s');
        $article->save();

        return redirect()
            ->route('news.index')
            ->with('success', 'News article moved to Recently Deleted.');
    }

    private function validateNews(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in($this->statusOptions())],
            'publish_at' => ['nullable', 'date'],
        ]);
    }

    private function fillAndSaveNews(NewsUpdate $article, array $validated, bool $creating): void
    {
        $publishedAt = $this->resolvePublishAt($validated['publish_at'] ?? null, $creating ? null : $article->post_date);
        $modifiedAt = now();

        $article->fill([
            'post_author' => (int) ($article->post_author ?: 1),
            'post_date' => $publishedAt->format('Y-m-d H:i:s'),
            'post_date_gmt' => $publishedAt->copy()->utc()->format('Y-m-d H:i:s'),
            'post_content' => trim((string) $validated['content']),
            'post_title' => trim((string) $validated['title']),
            'post_excerpt' => trim((string) ($validated['excerpt'] ?? '')),
            'post_status' => $validated['status'],
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => '',
            'post_name' => $this->generateUniqueSlug(
                trim((string) $validated['title']),
                $creating ? null : (int) $article->ID
            ),
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => $modifiedAt->format('Y-m-d H:i:s'),
            'post_modified_gmt' => $modifiedAt->copy()->utc()->format('Y-m-d H:i:s'),
            'post_content_filtered' => '',
            'post_parent' => 0,
            'menu_order' => 0,
            'post_type' => 'post',
            'post_mime_type' => '',
            'comment_count' => (int) ($article->comment_count ?? 0),
        ]);

        if ($creating) {
            $article->guid = '';
        }

        $article->save();

        if ($creating || trim((string) $article->guid) === '') {
            $article->guid = rtrim((string) config('services.shared_assets.wordpress_site_url', 'https://condo.com.my'), '/') . '/?p=' . $article->ID;
            $article->save();
        }
    }

    private function resolvePublishAt(?string $value, ?string $fallback = null): Carbon
    {
        $timezone = (string) config('app.timezone', 'UTC');

        if (filled($value)) {
            return Carbon::parse($value, $timezone)->setSecond(0);
        }

        if (filled($fallback) && $fallback !== '0000-00-00 00:00:00') {
            try {
                return Carbon::parse($fallback, $timezone);
            } catch (\Throwable) {
                // Fall through to now().
            }
        }

        return now()->setSecond(0);
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'news-update';
        $slug = $baseSlug;
        $suffix = 2;

        while (NewsUpdate::query()->newsPosts()
            ->when($ignoreId, fn ($query) => $query->where((new NewsUpdate())->getKeyName(), '!=', $ignoreId))
            ->where('post_name', $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function statusOptions(): array
    {
        return ['draft', 'publish', 'future', 'pending', 'private'];
    }
}
