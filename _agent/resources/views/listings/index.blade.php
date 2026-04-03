@extends('layouts.app')
@section('title', 'Listings')
@section('page-title', 'Listings')
@section('topbar-actions')
    <a href="{{ route('listings.create') }}" class="btn btn-primary btn-sm">New Listing</a>
@endsection

@section('content')
@php
    $filterKeys = ['search', 'listingtype', 'propertytype', 'state', 'min_price', 'max_price', 'sort', 'dir'];
    $currentFilters = request()->except('page', 'source');
    $hasFilters = request()->hasAny($filterKeys);
    $emptyCopy = match ($activeSource) {
        'ipp' => 'No IPP listings found matching your criteria.',
        'icp' => 'No ICP listings found matching your criteria.',
        default => 'No listings found matching your criteria.',
    };
@endphp

<style>
    .listing-source-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }

    .listing-source-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 10px 18px;
        border-radius: 999px;
        border: 1px solid var(--border);
        background: var(--card-bg);
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow: var(--shadow-sm);
    }

    .listing-source-tab:hover {
        color: var(--text);
        background: var(--accent-light);
    }

    .listing-source-tab.is-active {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff;
    }

    .listing-filter-form {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        width: 100%;
        align-items: center;
    }

    .listing-card-link {
        display: block;
        color: inherit;
        text-decoration: none;
    }

    .listing-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .listing-card-body-main {
        padding-bottom: 0;
    }

    .listing-card-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        border-top: 1px solid var(--border-light);
        padding-top: 16px;
    }

    .listing-card-readonly {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        border-top: 1px solid var(--border-light);
        padding-top: 16px;
    }

    .listing-readonly-note {
        font-size: 13px;
        color: var(--text-secondary);
    }

    @media (max-width: 768px) {
        .listing-source-tab {
            flex: 1 1 calc(50% - 6px);
        }
    }

    @media (max-width: 480px) {
        .listing-source-tab {
            flex-basis: 100%;
        }
    }
</style>

<div class="listing-source-tabs" aria-label="Listing sources">
    @foreach($sourceTabs as $tab)
        @php
            $tabQuery = array_filter(
                array_merge($currentFilters, ['source' => $tab['key']]),
                static fn ($value) => !is_null($value) && $value !== ''
            );
        @endphp
        <a
            href="{{ route('listings.index', $tabQuery) }}"
            class="listing-source-tab {{ $activeSource === $tab['key'] ? 'is-active' : '' }}"
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>

<div class="filters">
    <form method="GET" action="{{ route('listings.index') }}" class="listing-filter-form">
        <input type="hidden" name="source" value="{{ $activeSource }}">
        <input type="text" name="search" class="form-input filter-search" placeholder="Search properties..." value="{{ request('search') }}">
        <select name="listingtype" class="form-select">
            <option value="">All Types</option>
            @foreach($listingTypes as $type)
                <option value="{{ $type }}" {{ request('listingtype') === $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
        </select>
        <select name="propertytype" class="form-select">
            <option value="">All Property</option>
            @foreach($propertyTypes as $ptype)
                <option value="{{ $ptype }}" {{ request('propertytype') === $ptype ? 'selected' : '' }}>{{ $ptype }}</option>
            @endforeach
        </select>
        <select name="state" class="form-select">
            <option value="">All States</option>
            @foreach($states as $state)
                <option value="{{ $state }}" {{ request('state') === $state ? 'selected' : '' }}>{{ $state }}</option>
            @endforeach
        </select>
        <input type="number" name="min_price" class="form-input" placeholder="Min price" value="{{ request('min_price') }}">
        <input type="number" name="max_price" class="form-input" placeholder="Max price" value="{{ request('max_price') }}">
        <button type="submit" class="btn btn-primary">Filter</button>
        @if($hasFilters || $activeSource !== 'all')
            <a href="{{ route('listings.index', ['source' => $activeSource]) }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>
</div>

@if($activeSource === 'condo')
    <div class="card">
        <div class="empty-state">
            <p>The Condo tab is ready, but Condo listing sync is still pending.</p>
            <a href="{{ route('listings.index', ['source' => 'all']) }}" class="btn btn-secondary">Back to All Listings</a>
        </div>
    </div>
@elseif($listings->count())
    <div class="grid-4">
        @foreach($listings as $listing)
            @php
                $listingSource = $listing->source_key ?? 'ipp';
                $canManage = (bool) ($listing->can_manage ?? false);
                $showParams = [
                    'id' => $listing->id,
                    'return_source' => $activeSource,
                ];

                if ($listingSource !== 'ipp') {
                    $showParams['source'] = $listingSource;
                }
            @endphp
            <article class="listing-card">
                <a href="{{ route('listings.show', $showParams) }}" class="listing-card-link">
                    <div class="listing-card-img">
                        @if($listing->photopath)
                            <img src="{{ $listing->photopath }}" alt="{{ $listing->propertyname }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            No Image
                        @endif
                    </div>
                    
                    <div class="listing-card-body listing-card-body-main">
                        <div class="listing-card-head">
                            <span class="badge {{ $listingSource === 'icp' ? 'badge-warning' : 'badge-success' }}">
                                {{ $listing->source_label ?? strtoupper($listingSource) }}
                            </span>
                        </div>
                        <div class="listing-card-price">{{ $listing->formatted_price }}</div>
                        <div class="listing-card-title">{{ $listing->propertyname }}</div>
                        <div class="listing-card-meta" style="margin-bottom:14px;">
                            @if($listing->propertytype)<span>{{ $listing->propertytype }}</span>@endif
                            @if($listing->listingtype)<span>{{ $listing->listingtype }}</span>@endif
                            @if($listing->state)<span>{{ $listing->state }}</span>@endif
                            @if($listing->area)<span>{{ $listing->area }}</span>@endif
                        </div>
                    </div>
                </a>

                <div class="listing-card-body" style="padding-top:16px;">
                    @if($canManage)
                        <div class="listing-card-actions">
                            <a href="{{ route('listings.show', $showParams) }}" class="btn btn-secondary btn-sm" style="flex:1;">View</a>
                            <a href="{{ route('listings.edit', $listing->id) }}" class="btn btn-secondary btn-sm" style="flex:1;">Edit</a>
                            <form method="POST" action="{{ route('listings.destroy', $listing->id) }}" onsubmit="return confirm('Delete this listing? This action cannot be undone.');" style="margin:0;flex:1;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="width:100%;">Delete</button>
                            </form>
                        </div>
                    @else
                        <div class="listing-card-readonly">
                            <a href="{{ route('listings.show', $showParams) }}" class="btn btn-secondary btn-sm">View</a>
                            <span class="listing-readonly-note">ICP listings are view-only here for now.</span>
                        </div>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    <div class="pagination">{{ $listings->withQueryString()->links('components.pagination') }}</div>
@else
    <div class="card">
        <div class="empty-state">
            <p>{{ $emptyCopy }}</p>
            <a href="{{ route('listings.create') }}" class="btn btn-primary">Create your first listing</a>
        </div>
    </div>
@endif
@endsection
