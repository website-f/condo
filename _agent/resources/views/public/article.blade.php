@extends('public.layout')

@section('title', $article->meta_title ?: $article->title)
@section('description', $article->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $article->excerpt), 160))

@section('content')
<p style="margin:0 0 14px"><a href="/articles">&larr; Back to articles</a></p>

<article style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:28px;max-width:780px;margin:0 auto">
    <h1 style="margin:0 0 6px;font-size:28px;color:#0f172a">{{ $article->title }}</h1>
    <div class="article-meta">
        Published {{ optional($article->published_at)->format('M d, Y') }}
        @if($article->category) &middot; {{ $article->category }} @endif
    </div>

    @if($article->featured_image_url)
        <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" style="width:100%;border-radius:8px;margin-bottom:18px">
    @endif

    @if($article->excerpt)
        <p style="font-size:17px;color:#334155;font-weight:500;border-left:3px solid #2563eb;padding-left:14px;margin:0 0 18px">{{ $article->excerpt }}</p>
    @endif

    <div class="article-content">
        {!! $article->content !!}
    </div>
</article>
@endsection
