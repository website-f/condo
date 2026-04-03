@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Listings</div>
        <div class="stat-value">{{ number_format($totalListings) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Articles</div>
        <div class="stat-value">{{ number_format($totalArticles) }}</div>
        <div class="stat-sub">{{ $publishedArticles }} published · {{ $draftArticles }} drafts</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Social Posts</div>
        <div class="stat-value">{{ number_format($publishedPosts) }}</div>
        <div class="stat-sub">{{ $scheduledPosts }} scheduled</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Package</div>
        <div class="stat-value" style="font-size:20px;">{{ $agent->subscription?->name ?? 'N/A' }}</div>
        <div class="stat-sub">{{ $agent->subscription?->formatted_cost ?? '' }}/mo</div>
    </div>
</div>

<div class="grid-2" style="margin-bottom:24px;">
    <div class="card">
        <div class="card-header">Recent Articles</div>
        @if($recentArticles->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Title</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                @foreach($recentArticles as $article)
                    <tr>
                        <td><a href="{{ route('articles.edit', $article) }}" style="color:inherit;text-decoration:none;font-weight:500;">{{ Str::limit($article->title, 40) }}</a></td>
                        <td><span class="badge {{ $article->status === 'published' ? 'badge-success' : 'badge-default' }}">{{ $article->status }}</span></td>
                        <td style="color:var(--text-secondary);">{{ $article->created_at?->format('M d') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:30px;"><p>No articles yet</p><a href="{{ route('articles.create') }}" class="btn btn-primary btn-sm">Write your first article</a></div>
        @endif
    </div>
    <div class="card">
        <div class="card-header">Recent Listings</div>
        @if($recentListings->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Property</th><th>Type</th><th>Price</th></tr>
                </thead>
                <tbody>
                @foreach($recentListings as $listing)
                    <tr>
                        <td style="font-weight:500;">{{ Str::limit($listing->propertyname, 35) }}</td>
                        <td><span class="badge badge-default">{{ $listing->listingtype }}</span></td>
                        <td style="font-weight:500;">{{ $listing->formatted_price }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:30px;"><p>No listings found</p></div>
        @endif
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">Upcoming Social Posts</div>
        @if($upcomingPosts->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Platform</th><th>Content</th><th>Scheduled</th></tr>
                </thead>
                <tbody>
                @foreach($upcomingPosts as $post)
                    <tr>
                        <td><span class="badge badge-default">{{ $post->platform }}</span></td>
                        <td>{{ Str::limit($post->content, 40) }}</td>
                        <td style="color:var(--text-secondary);">{{ $post->scheduled_at?->format('M d, H:i') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:30px;"><p>No scheduled posts</p></div>
        @endif
    </div>
    <div class="card">
        <div class="card-header">Latest News</div>
        @if($recentNews->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Title</th><th>Date</th></tr>
                </thead>
                <tbody>
                @foreach($recentNews as $news)
                    <tr>
                        <td><a href="{{ route('news.show', $news->ID) }}" style="color:inherit;text-decoration:none;font-weight:500;">{{ Str::limit($news->post_title, 45) }}</a></td>
                        <td style="color:var(--text-secondary);">{{ \Carbon\Carbon::parse($news->post_date)->format('M d') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:30px;"><p>No news available</p></div>
        @endif
    </div>
</div>
@endsection
