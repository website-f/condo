@extends('layouts.app')
@section('title', 'New Article')
@section('page-title', 'New Article')
@section('topbar-actions')
    <a href="{{ route('articles.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('articles.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="grid-2" style="grid-template-columns:2fr 1fr;align-items:start;">
        <div>
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header">Content</div>
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Article title" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Excerpt</label>
                    <textarea name="excerpt" class="form-textarea" rows="2" placeholder="Brief summary...">{{ old('excerpt') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-textarea" rows="16" placeholder="Write your article here..." required>{{ old('content') }}</textarea>
                </div>
            </div>
            <div class="card">
                <div class="card-header">SEO</div>
                <div class="form-group">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-input" value="{{ old('meta_title') }}" maxlength="60" placeholder="SEO title (max 60 chars)">
                    <div class="form-hint">Recommended: 50-60 characters</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-textarea" rows="2" maxlength="160" placeholder="SEO description (max 160 chars)">{{ old('meta_description') }}</textarea>
                    <div class="form-hint">Recommended: 120-160 characters</div>
                </div>
            </div>
        </div>
        <div>
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header">Publish</div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-input" value="{{ old('category') }}" placeholder="e.g. Property Tips">
                </div>
                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-input" value="{{ old('tags') }}" placeholder="Comma separated tags">
                    <div class="form-hint">Separate with commas</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Featured Image</label>
                    <input type="file" name="featured_image" class="form-input" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Create Article</button>
            </div>
        </div>
    </div>
</form>
@endsection
