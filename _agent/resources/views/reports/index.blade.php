@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
<div class="filters" style="margin-bottom:24px;">
    <form method="GET" action="{{ route('reports.index') }}" style="display:flex;gap:12px;align-items:center;">
        <label class="form-label" style="margin:0;white-space:nowrap;">Period:</label>
        <select name="period" class="form-select" style="width:auto;min-width:140px;padding:7px 12px;font-size:12px;" onchange="this.form.submit()">
            <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 days</option>
            <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 days</option>
            <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 days</option>
            <option value="365" {{ $period == '365' ? 'selected' : '' }}>Last year</option>
        </select>
    </form>
</div>

<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-label">Total Listings</div>
        <div class="stat-value">{{ number_format($totalListings) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Articles</div>
        <div class="stat-value" style="font-size:20px;">Coming Soon</div>
        <div class="stat-sub">Article analytics will return with the new content flow.</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Social Posts</div>
        <div class="stat-value">{{ number_format($totalSocialPosts) }}</div>
    </div>
</div>

<div class="grid-2" style="margin-bottom:24px;">
    <div class="card">
        <div class="card-header">Listings by Type</div>
        @if($listingsByType->count())
        <div class="table-wrap">
            <table>
                <thead><tr><th>Listing Type</th><th style="text-align:right;">Count</th><th style="width:200px;"></th></tr></thead>
                <tbody>
                @foreach($listingsByType as $item)
                    <tr>
                        <td style="font-weight:500;">{{ $item->listingtype ?: 'Unspecified' }}</td>
                        <td style="text-align:right;font-weight:500;">{{ number_format($item->total) }}</td>
                        <td>
                            <div style="background:var(--accent-light);border-radius:4px;height:8px;overflow:hidden;">
                                <div style="background:var(--accent);height:100%;width:{{ $totalListings > 0 ? round($item->total / $totalListings * 100) : 0 }}%;border-radius:4px;"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p style="font-size:13px;color:var(--text-secondary);">No data</p>
        @endif
    </div>

    <div class="card">
        <div class="card-header">Listings by Property Type</div>
        @if($listingsByPropertyType->count())
        <div class="table-wrap">
            <table>
                <thead><tr><th>Property Type</th><th style="text-align:right;">Count</th><th style="width:200px;"></th></tr></thead>
                <tbody>
                @foreach($listingsByPropertyType as $item)
                    <tr>
                        <td style="font-weight:500;">{{ $item->propertytype ?: 'Unspecified' }}</td>
                        <td style="text-align:right;font-weight:500;">{{ number_format($item->total) }}</td>
                        <td>
                            <div style="background:var(--accent-light);border-radius:4px;height:8px;overflow:hidden;">
                                <div style="background:var(--accent);height:100%;width:{{ $totalListings > 0 ? round($item->total / $totalListings * 100) : 0 }}%;border-radius:4px;"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p style="font-size:13px;color:var(--text-secondary);">No data</p>
        @endif
    </div>
</div>

<div class="grid-2" style="margin-bottom:24px;">
    <div class="card">
        <div class="card-header">Top States</div>
        @if($listingsByState->count())
        <div class="table-wrap">
            <table>
                <thead><tr><th>State</th><th style="text-align:right;">Listings</th></tr></thead>
                <tbody>
                @foreach($listingsByState as $item)
                    <tr>
                        <td>{{ $item->state ?: 'Unspecified' }}</td>
                        <td style="text-align:right;font-weight:500;">{{ number_format($item->total) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p style="font-size:13px;color:var(--text-secondary);">No data</p>
        @endif
    </div>

    <div class="card">
        <div class="card-header">Social Posts by Platform</div>
        @if($socialByPlatform->count())
        <div class="table-wrap">
            <table>
                <thead><tr><th>Platform</th><th style="text-align:right;">Posts</th></tr></thead>
                <tbody>
                @foreach($socialByPlatform as $item)
                    <tr>
                        <td>{{ ucfirst($item->platform) }}</td>
                        <td style="text-align:right;font-weight:500;">{{ number_format($item->total) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p style="font-size:13px;color:var(--text-secondary);">No data</p>
        @endif
    </div>
</div>
@endsection
