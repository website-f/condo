@extends('public.layout')

@section('title', 'Articles — ' . ($agent->full_name ?? $agent->username))

@section('content')
<h1 class="section-title" style="margin-top:0">Articles</h1>

@if($articles->count() === 0)
    <p style="color:#64748b">No articles published yet.</p>
@else
    <div class="grid">
        @foreach($articles as $article)
            <a class="card" href="/articles/{{ $article->slug }}">
                @if($article->featured_image_url)
                    <img class="card-img" src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" loading="lazy">
                @else
                    <div class="card-img"></div>
                @endif
                <div class="card-body">
                    <div class="card-title">{{ $article->title }}</div>
                    <div class="card-meta">{{ optional($article->published_at)->format('M d, Y') }}</div>
                    @if($article->excerpt)
                        <div style="color:#475569;font-size:13px;margin-top:4px">{{ \Illuminate\Support\Str::limit($article->excerpt, 120) }}</div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>

    <div class="pagination">
        {{ $articles->links() }}
    </div>
@endif
@endsection
