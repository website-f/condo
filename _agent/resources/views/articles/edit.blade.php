@extends('layouts.app')
@section('title', 'Edit Article')
@section('page-title', 'Edit Article')
@section('topbar-actions')
    <a href="{{ route('articles.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('articles.update', $article) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="grid-2" style="grid-template-columns:2fr 1fr;align-items:start;">
        <div>
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header">Content</div>
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-input" value="{{ old('title', $article->title) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Excerpt</label>
                    <textarea name="excerpt" class="form-textarea" rows="2">{{ old('excerpt', $article->excerpt) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-textarea" rows="16" required>{{ old('content', $article->content) }}</textarea>
                </div>
            </div>
            <div class="card">
                <div class="card-header">SEO</div>
                <div class="form-group">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-input" value="{{ old('meta_title', $article->meta_title) }}" maxlength="60">
                </div>
                <div class="form-group">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-textarea" rows="2" maxlength="160">{{ old('meta_description', $article->meta_description) }}</textarea>
                </div>
            </div>
        </div>
        <div>
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header">Publish</div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" {{ old('status', $article->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $article->status) === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ old('status', $article->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-input" value="{{ old('category', $article->category) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-input" value="{{ old('tags', is_array($article->tags) ? implode(', ', $article->tags) : $article->tags) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Featured Image</label>
                    @if($article->featured_image_url)
                        <div style="margin-bottom:8px;"><img src="{{ $article->featured_image_url }}" style="max-width:100%;border-radius:8px;"></div>
                    @endif
                    <input type="file" name="featured_image" class="form-input" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Update Article</button>
            </div>
        </div>
    </div>
</form>
@endsection
