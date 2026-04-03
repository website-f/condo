@extends('layouts.app')
@section('title', 'New SEO Setting')
@section('page-title', 'New SEO Setting')
@section('topbar-actions')
    <a href="{{ route('seo.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('seo.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="grid-2" style="align-items:start;">
        <div class="card">
            <div class="card-header">Page Configuration</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Page Type</label>
                    <select name="page_type" class="form-select" required>
                        <option value="">Select...</option>
                        <option value="homepage">Homepage</option>
                        <option value="listing">Listing Page</option>
                        <option value="article">Article Page</option>
                        <option value="profile">Profile Page</option>
                        <option value="category">Category Page</option>
                        <option value="custom">Custom Page</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Page Identifier</label>
                    <input type="text" name="page_identifier" class="form-input" value="{{ old('page_identifier') }}" placeholder="e.g. slug or ID">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Title</label>
                <input type="text" name="meta_title" class="form-input" value="{{ old('meta_title') }}" maxlength="60" placeholder="Page title for search engines">
                <div class="form-hint">Max 60 characters</div>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Description</label>
                <textarea name="meta_description" class="form-textarea" rows="3" maxlength="160" placeholder="Description for search results">{{ old('meta_description') }}</textarea>
                <div class="form-hint">Max 160 characters</div>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-input" value="{{ old('meta_keywords') }}" placeholder="Comma-separated keywords">
            </div>
        </div>
        <div>
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header">Open Graph</div>
                <div class="form-group">
                    <label class="form-label">OG Title</label>
                    <input type="text" name="og_title" class="form-input" value="{{ old('og_title') }}" maxlength="60">
                </div>
                <div class="form-group">
                    <label class="form-label">OG Description</label>
                    <textarea name="og_description" class="form-textarea" rows="2" maxlength="160">{{ old('og_description') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">OG Image</label>
                    <input type="file" name="og_image" class="form-input" accept="image/*">
                </div>
            </div>
            <div class="card">
                <div class="card-header">Advanced</div>
                <div class="form-group">
                    <label class="form-label">Canonical URL</label>
                    <input type="url" name="canonical_url" class="form-input" value="{{ old('canonical_url') }}" placeholder="https://...">
                </div>
                <div class="form-group">
                    <label class="form-label">Robots</label>
                    <select name="robots" class="form-select">
                        <option value="index, follow">index, follow</option>
                        <option value="noindex, follow">noindex, follow</option>
                        <option value="index, nofollow">index, nofollow</option>
                        <option value="noindex, nofollow">noindex, nofollow</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Save SEO Setting</button>
            </div>
        </div>
    </div>
</form>
@endsection
