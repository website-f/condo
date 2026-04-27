@extends('layouts.app')

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('topbar-actions')
    <div class="btn-group">
        <a href="{{ route('articles.index') }}" class="btn btn-secondary btn-sm">Back To Articles</a>
        @if($article && $article->post_status === 'publish')
            <a href="{{ $article->public_url }}" class="btn btn-secondary btn-sm" target="_blank" rel="noopener">View Live</a>
        @endif
    </div>
@endsection

@section('content')
@php
    $isEditing = $article !== null;
    $currentImageUrl = $article?->featured_image_url;
    $siteBaseUrl = rtrim(\App\Models\ManagedArticle::wordpressSiteBaseUrl(), '/');
    $previewFallback = $article?->public_url ?: ($siteBaseUrl !== '' ? $siteBaseUrl . '/?p=preview' : '?p=preview');
    $previewBase = $siteBaseUrl !== '' ? $siteBaseUrl . '/?name=' : '?name=';
@endphp

<style>
    .article-form-shell{display:grid;gap:18px}.article-form-card{background:#fff;border:1px solid var(--border-light);border-radius:24px;box-shadow:var(--shadow-sm);padding:22px}.article-form-hero{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap}.article-form-hero h3{font-size:clamp(28px,3vw,40px);line-height:1.05;letter-spacing:-.04em;margin-bottom:8px}.article-form-note{color:var(--text-secondary);font-size:14px;line-height:1.6}.article-form-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(320px,.8fr);gap:18px}.article-form-stack{display:grid;gap:18px}.article-form-section{border:1px solid var(--border-light);border-radius:22px;padding:20px;background:#fff}.article-form-section h4{font-size:18px;line-height:1.15;letter-spacing:-.02em;margin-bottom:16px}.article-form-help{color:var(--text-secondary);font-size:13px;line-height:1.6;margin-top:8px}.article-form-status{display:grid;gap:12px}.article-form-status label{display:flex;gap:12px;align-items:flex-start;padding:14px 16px;border:1px solid var(--border-light);border-radius:18px;background:#fafbfc;cursor:pointer}.article-form-status strong{display:block;font-size:14px;margin-bottom:4px}.article-form-status span{display:block;font-size:13px;color:var(--text-secondary);line-height:1.5}.article-form-thumb{width:100%;min-height:220px;border:1px dashed var(--border);border-radius:22px;background:#fafbfc;display:grid;place-items:center;overflow:hidden;color:var(--text-secondary);font-size:13px;font-weight:600}.article-form-thumb img{width:100%;height:100%;object-fit:cover}.article-form-actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;padding-top:18px;border-top:1px solid var(--border-light)}.article-form-meta{display:grid;gap:10px}.article-form-chip{display:inline-flex;align-items:center;padding:6px 11px;border-radius:999px;background:#f4f5f8;color:var(--text-secondary);font-size:12px;font-weight:700}.article-form-preview{padding:12px 14px;border-radius:18px;background:#fafbfc;border:1px solid var(--border-light);color:var(--text-secondary);font-size:13px;line-height:1.6;word-break:break-word}.article-form-remove{display:inline-flex;align-items:center;gap:10px;font-size:14px;font-weight:600;color:var(--text);margin-bottom:0}@media (max-width:980px){.article-form-grid{grid-template-columns:1fr}}
</style>

<div class="article-form-shell">
    <section class="article-form-card article-form-hero">
        <div>
            <h3>{{ $isEditing ? 'Edit article' : 'Create article' }}</h3>
            <div class="article-form-note">This publishes to the real WordPress site, so the user can manage posts here instead of inside WP admin.</div>
        </div>
        @if($article)
            <div class="article-form-meta">
                <span class="article-form-chip">{{ $article->status_label }}</span>
                <span class="article-form-note">WordPress post #{{ $article->getKey() }}</span>
            </div>
        @endif
    </section>

    <form method="POST" action="{{ $isEditing ? route('articles.update', $article->getKey()) : route('articles.store') }}" enctype="multipart/form-data">
        @csrf
        @if($isEditing)
            @method('PUT')
        @endif

        <div class="article-form-grid">
            <div class="article-form-stack">
                <section class="article-form-card article-form-section">
                    <h4>Post Details</h4>
                    <div class="form-group">
                        <label class="form-label" for="title">Title</label>
                        <input type="text" id="title" name="title" class="form-input" value="{{ old('title', $form['title']) }}" maxlength="200" required data-title-input>
                        @error('title')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="slug">Slug</label>
                        <input type="text" id="slug" name="slug" class="form-input" value="{{ old('slug', $form['slug']) }}" maxlength="200" data-slug-input>
                        <div class="article-form-help">Leave blank to auto-generate from the title.</div>
                        @error('slug')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="article-form-preview">
                        Permalink: <span data-slug-preview>{{ $previewFallback }}</span>
                    </div>
                    <div class="form-group" style="margin-top:16px;">
                        <label class="form-label" for="excerpt">Short Summary</label>
                        <textarea id="excerpt" name="excerpt" class="form-textarea" rows="4">{{ old('excerpt', $form['excerpt']) }}</textarea>
                        <div class="article-form-help">This is the quick summary shown on article lists and previews.</div>
                        @error('excerpt')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="content">Post Content</label>
                        <textarea id="content" name="content" class="form-textarea" rows="16">{{ old('content', $form['content']) }}</textarea>
                        @error('content')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </section>

                <section class="article-form-card article-form-section">
                    <h4>SEO</h4>
                    <div class="form-group">
                        <label class="form-label" for="meta_title">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title" class="form-input" value="{{ old('meta_title', $form['meta_title']) }}" maxlength="200">
                        @error('meta_title')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="meta_description">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" class="form-textarea" rows="4">{{ old('meta_description', $form['meta_description']) }}</textarea>
                        @error('meta_description')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="focus_keyword">Focus Keyword</label>
                        <input type="text" id="focus_keyword" name="focus_keyword" class="form-input" value="{{ old('focus_keyword', $form['focus_keyword']) }}" maxlength="120">
                        @error('focus_keyword')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </section>
            </div>

            <div class="article-form-stack">
                <section class="article-form-card article-form-section">
                    <h4>Publishing</h4>
                    <div class="article-form-status">
                        @foreach($statusOptions as $value => $label)
                            <label>
                                <input type="radio" name="status" value="{{ $value }}" @checked(old('status', $form['status']) === $value)>
                                <span>
                                    <strong>{{ $label }}</strong>
                                    <span>
                                        @if($value === 'draft')
                                            Keep it private until you are ready.
                                        @elseif($value === 'publish')
                                            Publish now, or auto-schedule if the time below is in the future.
                                        @else
                                            Pick the exact go-live time for this article.
                                        @endif
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('status')<div class="form-error" style="margin-top:10px;">{{ $message }}</div>@enderror

                    <div class="form-group" style="margin-top:16px;margin-bottom:0;">
                        <label class="form-label" for="publish_at">Go Live Time</label>
                        <input type="datetime-local" id="publish_at" name="publish_at" class="form-input" value="{{ old('publish_at', $form['publish_at']) }}">
                        <div class="article-form-help">Timezone: {{ config('app.timezone') }}</div>
                        @error('publish_at')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </section>

                <section class="article-form-card article-form-section">
                    <h4>Category And Tags</h4>
                    <div class="form-group">
                        <label class="form-label" for="category">Category</label>
                        <input type="text" id="category" name="category" class="form-input" value="{{ old('category', $form['category']) }}" maxlength="120" placeholder="Example: Property Tips">
                        @error('category')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="tags">Tags</label>
                        <input type="text" id="tags" name="tags" class="form-input" value="{{ old('tags', $form['tags']) }}" maxlength="255" placeholder="Example: condo, investment, malaysia">
                        <div class="article-form-help">Separate tags with commas.</div>
                        @error('tags')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </section>

                <section class="article-form-card article-form-section">
                    <h4>Featured Image</h4>
                    <div class="article-form-thumb">
                        @if($currentImageUrl)
                            <img src="{{ $currentImageUrl }}" alt="">
                        @else
                            No featured image yet
                        @endif
                    </div>
                    <div class="form-group" style="margin-top:16px;">
                        <label class="form-label" for="featured_image">Upload Image</label>
                        <input type="file" id="featured_image" name="featured_image" class="form-input" accept="image/*">
                        @error('featured_image')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    @if($currentImageUrl)
                        <label class="article-form-remove">
                            <input type="checkbox" name="remove_featured_image" value="1" @checked(old('remove_featured_image', $form['remove_featured_image']))>
                            <span>Remove current featured image</span>
                        </label>
                    @endif
                </section>
            </div>
        </div>

        <section class="article-form-card article-form-section" style="margin-top:18px;">
            <div class="article-form-actions">
                <a href="{{ route('articles.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Save Changes' : 'Create Article' }}</button>
            </div>
        </section>
    </form>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        var titleInput = document.querySelector('[data-title-input]');
        var slugInput = document.querySelector('[data-slug-input]');
        var slugPreview = document.querySelector('[data-slug-preview]');
        var manualSlug = Boolean(slugInput && slugInput.value.trim() !== '');
        var previewFallback = @json($previewFallback);
        var previewBase = @json($previewBase);

        function slugify(value) {
            return String(value || '')
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        function updatePreview() {
            if (!slugPreview || !slugInput) {
                return;
            }

            var slug = slugInput.value.trim();
            slugPreview.textContent = slug !== '' ? previewBase + slug : previewFallback;
        }

        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function () {
                if (manualSlug) {
                    return;
                }

                slugInput.value = slugify(titleInput.value);
                updatePreview();
            });

            slugInput.addEventListener('input', function () {
                manualSlug = slugInput.value.trim() !== '';
                slugInput.value = slugify(slugInput.value);
                updatePreview();
            });

            updatePreview();
        }
    })();
</script>
@endsection
