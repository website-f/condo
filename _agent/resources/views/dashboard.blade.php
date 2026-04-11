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
        <div class="stat-label">Articles</div>
        <div class="stat-value" style="font-size:20px;">Coming Soon</div>
        <div class="stat-sub">This content section is being refreshed.</div>
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
        <div class="card-header">Articles</div>
        <div class="empty-state" style="padding:30px;">
            <p>The article workspace is coming soon.</p>
            <a href="{{ route('articles.index') }}" class="btn btn-secondary btn-sm">Open Preview</a>
        </div>
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
        <div class="card-header">Portal Focus</div>
        <div class="empty-state" style="padding:30px;">
            <p>This portal is now focused on listings, SEO, and social tools for agents.</p>
            <a href="{{ route('social.index') }}" class="btn btn-secondary btn-sm">Open Social Media</a>
        </div>
    </div>
</div>
@endsection
