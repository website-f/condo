@extends('public.layout')

@section('title', $agent->full_name ?? $agent->username)

@section('content')
<section class="hero">
    <h1>Welcome to {{ $agent->full_name ?? $agent->username }}'s site</h1>
    <p>Property listings and articles, brought to you on condo.com.my.</p>
</section>

<h2 class="section-title">Latest Listings</h2>
@if($listings->count() === 0)
    <p style="color:#64748b">No active listings yet.</p>
@else
    <div class="grid">
        @foreach($listings as $listing)
            <a class="card" href="/listings/{{ $listing->source_key }}/{{ $listing->id }}">
                @if($listing->image_url)
                    <img class="card-img" src="{{ $listing->image_url }}" alt="{{ $listing->propertyname }}" loading="lazy">
                @else
                    <div class="card-img"></div>
                @endif
                <div class="card-body">
                    <div><span class="badge {{ $listing->source_key }}">{{ strtoupper($listing->source_key) }}</span></div>
                    <div class="card-title">{{ $listing->propertyname }}</div>
                    <div class="card-meta">{{ $listing->location_label }}</div>
                    <div class="price">{{ $listing->formatted_price }}</div>
                </div>
            </a>
        @endforeach
    </div>
    <p style="margin-top:18px"><a href="/listings">View all listings &rarr;</a></p>
@endif

<h2 class="section-title">Latest Articles</h2>
@if($articles->count() === 0)
    <p style="color:#64748b">No articles yet.</p>
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
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
