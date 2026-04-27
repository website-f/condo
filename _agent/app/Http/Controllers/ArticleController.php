<?php

namespace App\Http\Controllers;

use App\Models\ManagedArticle;
use App\Support\ManagedArticleService;
use App\Support\RecentlyDeletedService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArticleController extends Controller
{
    private const STATUS_OPTIONS = [
        'draft' => 'Save as draft',
        'publish' => 'Publish now',
        'schedule' => 'Schedule for later',
    ];

    public function __construct(
        private readonly ManagedArticleService $managedArticleService,
        private readonly RecentlyDeletedService $recentlyDeletedService,
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $username = Auth::guard('agent')->user()->username;
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => trim((string) $request->query('status', '')),
        ];

        return view('articles.index', [
            'articles' => $this->managedArticleService->paginateForAgent($username, $filters),
            'stats' => $this->managedArticleService->statsForAgent($username),
            'filters' => $filters,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        return view('articles.form', $this->formViewData($this->defaultForm()));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateArticle($request);
        $agent = Auth::guard('agent')->user();
        $username = $agent->username;

        $article = $this->managedArticleService->create($username, $validated);

        return redirect()
            ->route('articles.edit', $article->getKey())
            ->with('success', 'Article saved to your site.');
    }

    public function edit(string $article): View|RedirectResponse
    {
        $managedArticle = $this->findOwnedArticleOrFail($article);

        return view('articles.form', $this->formViewData(
            $this->fillFormFromArticle($managedArticle),
            $managedArticle
        ));
    }

    public function update(Request $request, string $article): RedirectResponse
    {
        $managedArticle = $this->findOwnedArticleOrFail($article);
        $validated = $this->validateArticle($request);
        $agent = Auth::guard('agent')->user();

        $updatedArticle = $this->managedArticleService->update($managedArticle, $agent->username, $validated);

        return redirect()
            ->route('articles.edit', $updatedArticle->getKey())
            ->with('success', 'Article updated successfully.');
    }

    public function destroy(string $article): RedirectResponse
    {
        $managedArticle = $this->findOwnedArticleOrFail($article);
        $username = Auth::guard('agent')->user()->username;

        $this->recentlyDeletedService->rememberManagedArticle($managedArticle, $username);
        $this->managedArticleService->trash($managedArticle);

        return redirect()
            ->route('articles.index')
            ->with('success', 'Article moved to Recently Deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateArticle(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:1200'],
            'content' => ['nullable', 'string'],
            'status' => ['required', 'in:' . implode(',', array_keys(self::STATUS_OPTIONS))],
            'publish_at' => ['nullable', 'date'],
            'category' => ['nullable', 'string', 'max:120'],
            'tags' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'focus_keyword' => ['nullable', 'string', 'max:120'],
            'featured_image' => ['nullable', 'image', 'max:10240'],
            'remove_featured_image' => ['nullable', 'boolean'],
        ]);

        if (($validated['status'] ?? 'draft') === 'schedule') {
            $publishAtRaw = trim((string) ($validated['publish_at'] ?? ''));

            if ($publishAtRaw === '') {
                throw ValidationException::withMessages([
                    'publish_at' => 'Choose when this article should go live.',
                ]);
            }

            try {
                $publishAt = Carbon::parse($publishAtRaw, config('app.timezone'));
            } catch (\Throwable) {
                throw ValidationException::withMessages([
                    'publish_at' => 'The scheduled publish time is not valid.',
                ]);
            }

            if ($publishAt->lte(now())) {
                throw ValidationException::withMessages([
                    'publish_at' => 'Scheduled articles must be set to a future time.',
                ]);
            }
        }

        $validated['remove_featured_image'] = $request->boolean('remove_featured_image');

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function formViewData(array $form, ?ManagedArticle $article = null): array
    {
        return [
            'article' => $article,
            'form' => $form,
            'pageTitle' => $article ? 'Edit Article' : 'New Article',
            'statusOptions' => self::STATUS_OPTIONS,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultForm(): array
    {
        return [
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'content' => '',
            'status' => 'draft',
            'publish_at' => '',
            'category' => '',
            'tags' => '',
            'meta_title' => '',
            'meta_description' => '',
            'focus_keyword' => '',
            'remove_featured_image' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fillFormFromArticle(ManagedArticle $article): array
    {
        return [
            'title' => (string) $article->post_title,
            'slug' => (string) $article->post_name,
            'excerpt' => (string) $article->post_excerpt,
            'content' => (string) $article->post_content,
            'status' => (string) $article->editor_status,
            'publish_at' => $article->publishedAt()?->format('Y-m-d\TH:i') ?? '',
            'category' => $article->category_names[0] ?? '',
            'tags' => implode(', ', $article->tag_names),
            'meta_title' => (string) ($article->seo_title ?? ''),
            'meta_description' => (string) ($article->seo_description ?? ''),
            'focus_keyword' => (string) ($article->focus_keyword ?? ''),
            'remove_featured_image' => false,
        ];
    }

    private function findOwnedArticleOrFail(string $articleId): ManagedArticle
    {
        $username = Auth::guard('agent')->user()->username;

        /** @var ManagedArticle|null $article */
        $article = ManagedArticle::query()
            ->manageable()
            ->ownedByAgent($username)
            ->find($articleId);

        abort_if(! $article, 404);

        return $article;
    }
}
