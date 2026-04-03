@extends('layouts.app')
@section('title', 'Articles')
@section('page-title', 'Articles')
@section('topbar-actions')
    <a href="{{ route('articles.create') }}" class="btn btn-primary btn-sm">New Article</a>
@endsection

@section('content')
<div class="filters">
    <form method="GET" action="{{ route('articles.index') }}" style="display:flex;gap:16px;flex-wrap:wrap;width:100%;align-items:center;">
        <input type="text" name="search" class="form-input filter-search" placeholder="Search articles..." value="{{ request('search') }}">
        <select name="status" class="form-select">
            <option value="">All Status</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
        </select>
        <select name="category" class="form-select">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->hasAny(['search', 'status', 'category']))
            <a href="{{ route('articles.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>
</div>

@if($articles->count())
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th>Updated</th>
                    <th style="width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($articles as $article)
                <tr>
                    <td style="font-weight:500;">{{ Str::limit($article->title, 50) }}</td>
                    <td>{{ $article->category ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $article->status === 'published' ? 'badge-success' : ($article->status === 'draft' ? 'badge-warning' : 'badge-default') }}">
                            {{ $article->status }}
                        </span>
                    </td>
                    <td style="color:var(--text-secondary);">{{ $article->published_at?->format('M d, Y') ?? '—' }}</td>
                    <td style="color:var(--text-secondary);">{{ $article->updated_at?->format('M d, Y') }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('articles.edit', $article) }}" class="btn btn-secondary btn-sm">Edit</a>
                            <form action="{{ route('articles.destroy', $article) }}" method="POST" onsubmit="return confirm('Delete this article?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="pagination">{{ $articles->withQueryString()->links('components.pagination') }}</div>
@else
<div class="card">
    <div class="empty-state">
        <p>No articles found</p>
        <a href="{{ route('articles.create') }}" class="btn btn-primary">Create your first article</a>
    </div>
</div>
@endif
@endsection
