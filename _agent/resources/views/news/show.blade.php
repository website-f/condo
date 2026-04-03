@extends('layouts.app')
@section('title', $article->post_title)
@section('page-title', 'News')
@section('topbar-actions')
    <a href="{{ route('news.edit', $article->ID) }}" class="btn btn-secondary btn-sm">Edit</a>
    <form method="POST" action="{{ route('news.destroy', $article->ID) }}" onsubmit="return confirm('Move this news article to trash?');" style="margin:0;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
    </form>
    <a href="{{ route('news.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<div style="max-width:800px;">
    <div class="card">
        <style>
            .news-content img {
                max-width: 100%;
                height: auto;
                border-radius: 10px;
                margin: 12px 0;
            }

            .news-content a {
                word-break: break-word;
            }

            .news-content a[style*="display: flex"] {
                display: inline-flex !important;
                flex-wrap: wrap;
                align-items: center;
                gap: 10px;
                margin: 10px 0;
            }

            .news-content a[style*="display: flex"] img {
                width: 50px;
                height: 50px;
                margin: 0;
                border-radius: 0;
                flex-shrink: 0;
            }

            .news-content .hidden-photo-wrap {
                margin-top: 16px;
            }

            .news-content .show-photo-btn {
                padding: 8px 14px;
                border: 1px solid var(--border);
                border-radius: 8px;
                background: var(--accent-light);
                color: var(--text);
                font: inherit;
                cursor: pointer;
            }

            .news-content .hidden-photo-content {
                display: none;
                gap: 12px;
                margin-top: 12px;
            }

            .news-content .hidden-photo-content.is-open {
                display: grid !important;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }

            .news-content .hidden-photo-content img {
                width: 100%;
                height: 180px;
                object-fit: cover;
                margin: 0;
            }
        </style>
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:12px;">
            <div style="font-size:12px;color:var(--text-secondary);">{{ \Carbon\Carbon::parse($article->post_date)->format('F d, Y h:i A') }}</div>
            <span class="badge {{ $article->post_status === 'publish' ? 'badge-success' : ($article->post_status === 'draft' ? 'badge-warning' : 'badge-default') }}">
                {{ \Illuminate\Support\Str::headline($article->post_status) }}
            </span>
        </div>
        <h1 style="font-size:24px;font-weight:600;letter-spacing:-0.5px;margin-bottom:20px;line-height:1.3;">{{ $article->post_title }}</h1>
        @if($article->post_excerpt)
            <div style="padding:14px 16px;border-radius:10px;background:var(--accent-light);color:var(--text-secondary);font-size:13px;line-height:1.7;margin-bottom:18px;">
                {{ $article->post_excerpt }}
            </div>
        @endif
        <div class="news-content" style="font-size:14px;line-height:1.8;color:var(--text);">
            {!! $article->rendered_content !!}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.news-content .hidden-photo-wrap').forEach((wrap) => {
        const button = wrap.querySelector('.show-photo-btn');
        const content = wrap.querySelector('.hidden-photo-content');

        if (!button || !content) {
            return;
        }

        const setOpen = (open) => {
            content.classList.toggle('is-open', open);
            button.textContent = open ? 'Hide extra photos' : 'Show more photo';
        };

        setOpen(true);

        button.addEventListener('click', () => {
            setOpen(!content.classList.contains('is-open'));
        });
    });
</script>
@endsection
