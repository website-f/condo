@extends('layouts.app')
@section('title', 'Edit SEO Setting')
@section('page-title', 'Edit SEO Setting')
@section('topbar-actions')
    <a href="{{ route('seo.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('seo.update', $seo) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="grid-2" style="align-items:start;">
        <div class="card">
            <div class="card-header">Page Configuration</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Page Type</label>
                    <select name="page_type" class="form-select" required>
                        @foreach(['homepage','listing','article','profile','category','custom'] as $type)
                            <option value="{{ $type }}" {{ $seo->page_type === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Page Identifier</label>
                    <input type="text" name="page_identifier" class="form-input" value="{{ old('page_identifier', $seo->page_identifier) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Title</label>
                <input type="text" name="meta_title" class="form-input" value="{{ old('meta_title', $seo->meta_title) }}" maxlength="60">
            </div>
            <div class="form-group">
                <label class="form-label">Meta Description</label>
                <textarea name="meta_description" class="form-textarea" rows="3" maxlength="160">{{ old('meta_description', $seo->meta_description) }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-input" value="{{ old('meta_keywords', $seo->meta_keywords) }}">
            </div>
        </div>
        <div>
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header">Open Graph</div>
                <div class="form-group">
                    <label class="form-label">OG Title</label>
                    <input type="text" name="og_title" class="form-input" value="{{ old('og_title', $seo->og_title) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">OG Description</label>
                    <textarea name="og_description" class="form-textarea" rows="2">{{ old('og_description', $seo->og_description) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">OG Image</label>
                    @if($seo->og_image_url)
                        <div style="margin-bottom:8px;"><img src="{{ $seo->og_image_url }}" style="max-width:100%;border-radius:8px;"></div>
                    @endif
                    <input type="file" name="og_image" class="form-input" accept="image/*">
                </div>
            </div>
            <div class="card">
                <div class="card-header">Advanced</div>
                <div class="form-group">
                    <label class="form-label">Canonical URL</label>
                    <input type="url" name="canonical_url" class="form-input" value="{{ old('canonical_url', $seo->canonical_url) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Robots</label>
                    <select name="robots" class="form-select">
                        @foreach(['index, follow','noindex, follow','index, nofollow','noindex, nofollow'] as $opt)
                            <option value="{{ $opt }}" {{ $seo->robots === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Update SEO Setting</button>
            </div>
        </div>
    </div>
</form>
@endsection
