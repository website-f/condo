@extends('layouts.app')

@section('title', 'Articles')
@section('page-title', 'Articles')

@section('topbar-actions')
    <a href="{{ route('articles.create') }}" class="btn btn-primary btn-sm">New Article</a>
@endsection

@section('content')
@php
    $search = $filters['search'] ?? '';
    $status = $filters['status'] ?? '';
@endphp

<style>
    .articles-shell{display:grid;gap:18px}.articles-hero,.articles-filters,.articles-board,.articles-stat{background:#fff;border:1px solid var(--border-light);border-radius:24px;box-shadow:var(--shadow-sm)}.articles-hero,.articles-filters,.articles-board{padding:22px}.articles-hero{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap}.articles-hero h3{font-size:clamp(28px,3vw,40px);line-height:1.05;letter-spacing:-.04em;margin-bottom:8px}.articles-note{color:var(--text-secondary);font-size:14px;line-height:1.6}.articles-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.articles-stat{padding:18px 20px}.articles-stat span{display:block;color:var(--text-secondary);font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin-bottom:10px}.articles-stat strong{display:block;font-size:32px;line-height:1;letter-spacing:-.05em}.articles-filter-form{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(180px,.5fr) auto auto;gap:12px}.articles-board-head{display:flex;justify-content:space-between;gap:16px;align-items:center;flex-wrap:wrap;margin-bottom:16px}.articles-board-head h4{font-size:18px;letter-spacing:-.02em}.articles-empty{padding:56px 20px;text-align:center;border:1px dashed var(--border);border-radius:22px;background:#fafbfc;color:var(--text-secondary)}.articles-empty p{font-size:15px;margin-bottom:16px}.articles-table{width:100%;border-collapse:collapse}.articles-table th{padding:14px 16px;background:#fafbfc;border-bottom:1px solid var(--border-light);font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-secondary)}.articles-table td{padding:18px 16px;border-bottom:1px solid var(--border-light);vertical-align:top}.articles-row-main{display:flex;gap:14px;align-items:flex-start}.articles-thumb{width:72px;height:72px;border-radius:18px;overflow:hidden;background:#eef2f7;border:1px solid var(--border-light);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--text-secondary);flex-shrink:0}.articles-thumb img{width:100%;height:100%;object-fit:cover}.articles-title{font-size:16px;font-weight:700;line-height:1.3;margin-bottom:6px;color:var(--text)}.articles-meta{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px}.articles-chip{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;background:#f4f5f8;color:var(--text-secondary);font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}.articles-summary{color:var(--text-secondary);font-size:13px;line-height:1.6;max-width:64ch}.articles-status{display:inline-flex;align-items:center;padding:6px 11px;border-radius:999px;font-size:12px;font-weight:700;border:1px solid var(--border-light)}.articles-status.status-publish{background:#e8f7ee;color:#18794e;border-color:#bfe3cd}.articles-status.status-future{background:#eef2ff;color:#3b5bdb;border-color:#d8e0ff}.articles-status.status-draft{background:#f5f5f7;color:#1d1d1f}.articles-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.articles-actions form{margin:0}.articles-pager{margin-top:18px;padding-top:18px;border-top:1px solid var(--border-light)}@media (max-width:1024px){.articles-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.articles-filter-form{grid-template-columns:1fr 1fr}}@media (max-width:760px){.articles-stats,.articles-filter-form{grid-template-columns:1fr}.articles-hero,.articles-board-head{flex-direction:column;align-items:stretch}.articles-table thead{display:none}.articles-table,.articles-table tbody,.articles-table tr,.articles-table td{display:block;width:100%}.articles-table tr{border-bottom:1px solid var(--border-light)}.articles-row-main{flex-direction:column}.articles-thumb{width:100%;height:180px}}
</style>

<div class="articles-shell">
    <section class="articles-hero">
        <div>
            <h3>Articles</h3>
            <div class="articles-note">Write once here and send the post straight to your WordPress site without opening WP admin.</div>
        </div>
        <a href="{{ route('articles.create') }}" class="btn btn-primary">New Article</a>
    </section>

    <section class="articles-stats">
        <article class="articles-stat"><span>Total</span><strong>{{ number_format($stats['total']) }}</strong></article>
        <article class="articles-stat"><span>Published</span><strong>{{ number_format($stats['published']) }}</strong></article>
        <article class="articles-stat"><span>Scheduled</span><strong>{{ number_format($stats['scheduled']) }}</strong></article>
        <article class="articles-stat"><span>Drafts</span><strong>{{ number_format($stats['drafts']) }}</strong></article>
    </section>

    <section class="articles-filters">
        <form method="GET" class="articles-filter-form">
            <input type="text" name="search" class="form-input" value="{{ $search }}" placeholder="Search title, excerpt, content, or slug">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                <option value="publish" @selected($status === 'publish')>Published</option>
                <option value="schedule" @selected($status === 'schedule')>Scheduled</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="{{ route('articles.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </section>

    <section class="articles-board">
        <div class="articles-board-head">
            <div>
                <h4>Article List</h4>
                <div class="articles-note">{{ $articles->total() }} result{{ $articles->total() === 1 ? '' : 's' }}</div>
            </div>
        </div>

        @if($articles->count() === 0)
            <div class="articles-empty">
                <p>No articles found for this view.</p>
                <a href="{{ route('articles.create') }}" class="btn btn-primary">Create First Article</a>
            </div>
        @else
            <div class="table-wrap" style="margin:0;padding:0;">
                <table class="articles-table">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Go Live</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($articles as $article)
                            @php
                                $summarySource = trim(strip_tags($article->post_excerpt ?: $article->post_content));
                            @endphp
                            <tr>
                                <td>
                                    <div class="articles-row-main">
                                        <div class="articles-thumb">
                                            @if($article->featured_image_url)
                                                <img src="{{ $article->featured_image_url }}" alt="">
                                            @else
                                                No image
                                            @endif
                                        </div>
                                        <div>
                                            <div class="articles-title">{{ $article->post_title ?: 'Untitled article' }}</div>
                                            <div class="articles-meta">
                                                @if($article->category_names !== [])
                                                    <span class="articles-chip">{{ $article->category_names[0] }}</span>
                                                @endif
                                                @foreach(array_slice($article->tag_names, 0, 3) as $tag)
                                                    <span class="articles-chip">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                            <div class="articles-summary">{{ $summarySource !== '' ? Str::limit($summarySource, 140) : 'No summary yet.' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="color:var(--text-secondary);font-size:13px;line-height:1.6;">{{ $article->formatted_publish_at ?? 'Not set' }}</td>
                                <td><span class="articles-status status-{{ $article->post_status }}">{{ $article->status_label }}</span></td>
                                <td>
                                    <div class="articles-actions">
                                        @if($article->post_status === 'publish' && $article->public_url)
                                            <a href="{{ $article->public_url }}" class="btn btn-secondary btn-sm" target="_blank" rel="noopener">View</a>
                                        @endif
                                        <a href="{{ route('articles.edit', $article->getKey()) }}" class="btn btn-primary btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('articles.destroy', $article->getKey()) }}" onsubmit="return confirm('Move this article to Recently Deleted?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($articles->hasPages())
                <div class="articles-pager">
                    {{ $articles->links('components.pagination') }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
