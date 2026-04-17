@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@php
    $hasCondoPackageAccess = (bool) ($agent->has_condo_package_access ?? false);
    $articleActionUrl = $hasCondoPackageAccess ? route('articles.create') : route('billing.index');
    $articleIndexUrl = $hasCondoPackageAccess ? route('articles.index') : route('billing.index');
    $socialIndexUrl = $hasCondoPackageAccess ? route('social.index') : route('billing.index');
@endphp

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Listings</div>
        <div class="stat-value">{{ number_format($totalListings) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Articles</div>
        <div class="stat-value">{{ number_format($totalArticles) }}</div>
        <div class="stat-sub">{{ number_format($publishedArticles) }} published, {{ number_format($scheduledArticles) }} scheduled</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Social Schedules</div>
        <div class="stat-value">{{ number_format($totalSocialSchedules) }}</div>
        <div class="stat-sub">{{ number_format($scheduledPosts) }} queued, {{ number_format($publishedPosts) }} sent</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Package</div>
        <div class="stat-value" style="font-size:20px;">{{ $agent->subscription?->display_name ?? 'N/A' }}</div>
        <div class="stat-sub">{{ $agent->subscription?->formatted_cost ?? '' }}/mo</div>
    </div>
</div>

<div class="grid-2" style="margin-bottom:24px;">
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;gap:12px;align-items:center;">
            <span>Recent Articles</span>
            <a href="{{ $articleActionUrl }}" class="btn btn-secondary btn-sm">{{ $hasCondoPackageAccess ? 'New Article' : 'Unlock Articles' }}</a>
        </div>
        @if($recentArticles->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Title</th><th>Status</th><th>Go Live</th></tr>
                </thead>
                <tbody>
                @foreach($recentArticles as $article)
                    <tr>
                        <td style="font-weight:500;">{{ Str::limit($article->post_title, 42) }}</td>
                        <td><span class="badge badge-default">{{ $article->status_label }}</span></td>
                        <td style="color:var(--text-secondary);">{{ $article->formatted_publish_at ?? 'Not set' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:30px;">
            <p>No articles yet.</p>
            <a href="{{ $articleActionUrl }}" class="btn btn-primary btn-sm">{{ $hasCondoPackageAccess ? 'Create First Article' : 'View Packages' }}</a>
        </div>
        @endif
    </div>
    <div class="card">
        <div class="card-header">Recent Listings</div>
        @if($recentListings->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Property</th><th>Source</th><th>Type</th><th>Price</th></tr>
                </thead>
                <tbody>
                @foreach($recentListings as $listing)
                    <tr>
                        <td style="font-weight:500;">{{ Str::limit($listing->propertyname, 35) }}</td>
                        <td><span class="badge badge-default">{{ $listing->dashboard_source_label }}</span></td>
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
        <div class="card-header">Upcoming Social Schedules</div>
        @if($upcomingPosts->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Networks</th><th>Message</th><th>Scheduled</th></tr>
                </thead>
                <tbody>
                @foreach($upcomingPosts as $post)
                    <tr>
                        <td><span class="badge badge-default">{{ $post['network_label'] }}</span></td>
                        <td>{{ Str::limit($post['message_preview'], 40) }}</td>
                        <td style="color:var(--text-secondary);">{{ $post['scheduled_at']->format('M d, H:i') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:30px;"><p>No scheduled social activity</p></div>
        @endif
    </div>
    <div class="card">
        <div class="card-header">Content Tools</div>
        <div class="empty-state" style="padding:30px;">
            <p>
                @if($hasCondoPackageAccess)
                    Write articles here, manage listings, and send posts to social without opening WordPress admin.
                @else
                    Condo content tools are locked until this account has Condo Premium Package or Condo Premium Lite Package.
                @endif
            </p>
            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                <a href="{{ $articleIndexUrl }}" class="btn btn-secondary btn-sm">{{ $hasCondoPackageAccess ? 'Open Articles' : 'View Packages' }}</a>
                <a href="{{ $socialIndexUrl }}" class="btn btn-secondary btn-sm">{{ $hasCondoPackageAccess ? 'Open Social Media' : 'Open Billing' }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
