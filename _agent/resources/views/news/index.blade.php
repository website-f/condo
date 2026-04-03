@extends('layouts.app')
@section('title', 'News')
@section('page-title', 'News')
@section('topbar-actions')
    <a href="{{ route('news.create') }}" class="btn btn-primary btn-sm">New News</a>
@endsection

@section('content')
<div class="filters">
    <form method="GET" action="{{ route('news.index') }}" style="display:flex;gap:12px;width:100%;align-items:center;">
        <input type="text" name="search" class="form-input filter-search" placeholder="Search news..." value="{{ request('search') }}">
        <select name="status" class="form-select" style="min-width:150px;">
            <option value="">All statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
        @if(request('search') || request('status'))
            <a href="{{ route('news.index') }}" class="btn btn-secondary btn-sm">Clear</a>
        @endif
    </form>
</div>

@if($news->count())
<div class="grid-3">
    @foreach($news as $article)
    <div class="card" style="display:grid;gap:14px;">
        <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;">
            <div style="font-size:11px;color:var(--text-secondary);">{{ $article->formatted_post_date ?? $article->post_date }}</div>
            <span class="badge {{ $article->post_status === 'publish' ? 'badge-success' : ($article->post_status === 'draft' ? 'badge-warning' : 'badge-default') }}">
                {{ \Illuminate\Support\Str::headline($article->post_status) }}
            </span>
        </div>

        <div>
            <h3 style="font-size:15px;font-weight:600;letter-spacing:-0.2px;margin-bottom:8px;line-height:1.4;">{{ $article->post_title }}</h3>
            <p style="font-size:13px;color:var(--text-secondary);display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                {{ Str::limit(strip_tags($article->post_excerpt ?: $article->post_content), 140) }}
            </p>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('news.show', $article->ID) }}" class="btn btn-secondary btn-sm">View</a>
            <a href="{{ route('news.edit', $article->ID) }}" class="btn btn-secondary btn-sm">Edit</a>
            <form method="POST" action="{{ route('news.destroy', $article->ID) }}" onsubmit="return confirm('Move this news article to trash?');" style="margin:0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
        </div>
    </div>
    @endforeach
</div>
<div class="pagination">{{ $news->withQueryString()->links('components.pagination') }}</div>
@else
<div class="card">
    <div class="empty-state"><p>No news available</p></div>
</div>
@endif
@endsection
