@extends('layouts.app')
@section('title', 'Listings')
@section('page-title', 'Listings')
@section('topbar-actions')
    <a href="{{ route('listings.create', ['source' => in_array($activeSource, ['ipp', 'icp', 'condo'], true) ? $activeSource : 'ipp']) }}" class="btn btn-primary btn-sm" style="border-radius: 999px; padding: 10px 24px;">New Listing</a>
@endsection

@section('content')
@php
    $filterKeys = ['search', 'listingtype', 'propertytype', 'state', 'min_price', 'max_price', 'sort', 'dir'];
    $currentFilters = request()->except('page', 'source');
    $createSource = in_array($activeSource, ['ipp', 'icp', 'condo'], true) ? $activeSource : 'ipp';
    $hasFilters = request()->hasAny($filterKeys);
    $emptyCopy = match ($activeSource) {
        'ipp' => 'No IPP listings found matching your criteria.',
        'icp' => 'No ICP listings found matching your criteria.',
        'condo' => 'No Condo listings found matching your criteria.',
        default => 'No listings found matching your criteria.',
    };
@endphp

<style>
    /* Styling strictly minimal, clean, Apple-like */
    :root {
        --apple-bg: #fbfbfd;
        --apple-card: #ffffff;
        --apple-text: #1d1d1f;
        --apple-text-muted: #86868b;
        --apple-border: rgba(0, 0, 0, 0.08);
        --apple-blue: #0066cc;
        --apple-blue-hover: #0077ed;
        --apple-radius: 18px;
        --apple-shadow: 0 4px 24px rgba(0,0,0,0.04);
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    
    body {
        background-color: var(--apple-bg);
        color: var(--apple-text);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    /* Tabs segment control */
    .listing-source-tabs-container {
        display: flex;
        justify-content: center;
        margin-bottom: 32px;
        width: 100%;
        padding: 0 16px;
    }
    .listing-source-tabs {
        display: inline-flex;
        background: rgba(118, 118, 128, 0.12);
        padding: 4px;
        border-radius: 12px;
        gap: 2px;
        width: 100%;
        max-width: 600px;
    }

    .listing-source-tab {
        flex: 1;
        text-align: center;
        padding: 10px 16px;
        border-radius: 8px;
        color: var(--apple-text);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: var(--transition);
        border: none;
        background: transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .listing-source-tab:hover {
        background: rgba(0,0,0,0.04);
    }

    .listing-source-tab.is-active {
        background: #ffffff;
        color: var(--apple-text);
        font-weight: 600;
        box-shadow: 0 3px 8px rgba(0,0,0,0.12), 0 3px 1px rgba(0,0,0,0.04);
    }

    .listing-source-count {
        font-size: 11px;
        font-weight: 600;
        color: var(--apple-text-muted);
        background: rgba(0,0,0,0.05);
        padding: 1px 7px;
        border-radius: 10px;
        min-width: 20px;
        text-align: center;
        line-height: 1.5;
    }

    .listing-source-tab.is-active .listing-source-count {
        background: rgba(0,0,0,0.08);
        color: var(--apple-text);
    }

    /* Filters Box */
    .filters-wrapper {
        background: var(--apple-card);
        border-radius: var(--apple-radius);
        box-shadow: var(--apple-shadow);
        border: 1px solid var(--apple-border);
        margin-bottom: 32px;
        overflow: hidden;
        transition: var(--transition);
    }
    .filters-header {
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }
    .filters-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .filters-header h3 svg {
        width: 20px;
        height: 20px;
        color: var(--apple-text-muted);
    }
    .filters-toggle-icon {
        transition: transform 0.3s ease;
        color: var(--apple-text-muted);
    }
    .filters-wrapper.is-open .filters-toggle-icon {
        transform: rotate(180deg);
    }
    .filters-body {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s cubic-bezier(0, 1, 0, 1);
        padding: 0 24px;
        opacity: 0;
    }
    .filters-wrapper.is-open .filters-body {
        max-height: 1000px;
        padding-bottom: 24px;
        opacity: 1;
        transition: max-height 0.4s ease-in-out, opacity 0.3s ease-in-out 0.1s, padding-bottom 0.4s ease-in-out;
    }

    .listing-filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }

    .listing-filter-form .form-input,
    .listing-filter-form .form-select {
        width: 100%;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid var(--apple-border);
        background: #f5f5f7;
        font-size: 15px;
        color: var(--apple-text);
        transition: var(--transition);
    }

    .listing-filter-form .form-input:focus,
    .listing-filter-form .form-select:focus {
        background: #ffffff;
        border-color: var(--apple-blue);
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.15);
    }

    .filter-actions {
        display: flex;
        gap: 12px;
        grid-column: 1 / -1;
        justify-content: flex-end;
        margin-top: 8px;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 24px;
        border-radius: 999px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        transition: var(--transition);
        cursor: pointer;
        border: none;
    }
    .btn-primary {
        background: var(--apple-blue);
        color: #ffffff;
    }
    .btn-primary:hover {
        background: var(--apple-blue-hover);
        transform: scale(1.02);
    }
    .btn-secondary {
        background: rgba(0,0,0,0.05);
        color: var(--apple-text);
    }
    .btn-secondary:hover {
        background: rgba(0,0,0,0.1);
    }
    .btn-danger {
        background: #ff3b30;
        color: #ffffff;
    }
    .btn-danger:hover {
        background: #ff453a;
        transform: scale(1.02);
    }
    .btn-sm {
        padding: 8px 18px;
        font-size: 13px;
    }

    /* Cards */
    .grid-4 {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
    }

    .listing-card {
        background: var(--apple-card);
        border-radius: var(--apple-radius);
        box-shadow: var(--apple-shadow);
        border: 1px solid var(--apple-border);
        overflow: hidden;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
    }

    .listing-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.08);
    }

    .listing-card-link {
        display: block;
        color: inherit;
        text-decoration: none;
        flex: 1;
    }

    .listing-card-img {
        height: 200px;
        background: #f5f5f7;
        position: relative;
        overflow: hidden;
    }

    .listing-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .listing-card:hover .listing-card-img img {
        transform: scale(1.05);
    }

    .listing-card-body {
        padding: 20px;
    }

    .listing-card-head {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .badge-success { background: #e3f2fd; color: #0d47a1; }
    .badge-warning { background: #fff3e0; color: #e65100; }
    .badge-default { background: #f5f5f7; color: var(--apple-text-muted); }

    .listing-card-price {
        font-size: 22px;
        font-weight: 700;
        color: var(--apple-text);
        margin-bottom: 6px;
        letter-spacing: -0.02em;
    }

    .listing-card-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--apple-text);
        margin-bottom: 12px;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .listing-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 13px;
        color: var(--apple-text-muted);
    }
    
    .listing-card-meta span {
        background: #f5f5f7;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 500;
    }

    .listing-card-actions {
        display: flex;
        gap: 8px;
        padding: 16px 20px;
        background: #fbfbfd;
        border-top: 1px solid var(--apple-border);
    }
    
    .listing-card-actions .btn {
        flex: 1;
    }

    .listing-card-readonly {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 16px 20px;
        background: #fbfbfd;
        border-top: 1px solid var(--apple-border);
    }

    .listing-readonly-note {
        font-size: 12px;
        color: var(--apple-text-muted);
        text-align: center;
    }

    .empty-state {
        padding: 60px 24px;
        text-align: center;
        background: var(--apple-card);
        border-radius: var(--apple-radius);
        box-shadow: var(--apple-shadow);
        border: 1px solid var(--apple-border);
    }
    .empty-state p {
        font-size: 16px;
        color: var(--apple-text-muted);
        margin-bottom: 24px;
    }

    @media (max-width: 768px) {
        .listing-source-tabs-container {
            padding: 0;
        }
        .filters-header {
            padding: 16px;
        }
        .listing-card-img {
            height: 180px;
        }
        .grid-4 {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="listing-source-tabs-container">
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
                @if(!is_null($tab['count']))
                    <span class="listing-source-count">{{ number_format($tab['count']) }}</span>
                @endif
            </a>
        @endforeach
    </div>
</div>

<div class="filters-wrapper {{ $hasFilters ? 'is-open' : '' }}" id="filtersCard">
    <div class="filters-header" onclick="document.getElementById('filtersCard').classList.toggle('is-open')">
        <h3>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
            Search & Filters
        </h3>
        <svg class="filters-toggle-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
    </div>
    <div class="filters-body">
        <form method="GET" action="{{ route('listings.index') }}" class="listing-filter-form">
            <input type="hidden" name="source" value="{{ $activeSource }}">
            <input type="text" name="search" class="form-input" placeholder="Search properties..." value="{{ request('search') }}">
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
            
            <div class="filter-actions">
                @if($hasFilters || $activeSource !== 'all')
                    <a href="{{ route('listings.index', ['source' => $activeSource]) }}" class="btn btn-secondary">Clear</a>
                @endif
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

@if($listings->count())
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

                $editParams = [
                    'id' => $listing->id,
                    'return_source' => $activeSource,
                ];

                if ($listingSource !== 'ipp') {
                    $editParams['source'] = $listingSource;
                }
            @endphp
            <article class="listing-card">
                <a href="{{ route('listings.show', $showParams) }}" class="listing-card-link">
                    <div class="listing-card-img">
                        @if($listing->photopath)
                            <img src="{{ $listing->photopath }}" alt="{{ $listing->propertyname }}">
                        @else
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--apple-text-muted); font-size:14px; font-weight:500;">No Image</div>
                        @endif
                    </div>
                    
                    <div class="listing-card-body">
                        <div class="listing-card-head">
                            <span class="badge {{ $listingSource === 'icp' ? 'badge-warning' : ($listingSource === 'condo' ? 'badge-default' : 'badge-success') }}">
                                {{ $listing->source_label ?? strtoupper($listingSource) }}
                            </span>
                        </div>
                        <div class="listing-card-price">{{ $listing->formatted_price }}</div>
                        <div class="listing-card-title">{{ $listing->propertyname }}</div>
                        <div class="listing-card-meta">
                            @if($listing->propertytype)<span>{{ $listing->propertytype }}</span>@endif
                            @if($listing->listingtype)<span>{{ $listing->listingtype }}</span>@endif
                            @if($listing->state)<span>{{ $listing->state }}</span>@endif
                        </div>
                    </div>
                </a>

                @if($canManage)
                    <div class="listing-card-actions">
                        <a href="{{ route('listings.show', $showParams) }}" class="btn btn-secondary btn-sm">View</a>
                        <a href="{{ route('listings.edit', $editParams) }}" class="btn btn-secondary btn-sm">Edit</a>
                        <form method="POST" action="{{ route('listings.destroy', $listing->id) }}" onsubmit="return confirm('Delete this listing? This action cannot be undone.');" style="margin:0; flex:1;">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="source" value="{{ $listingSource }}">
                            <input type="hidden" name="return_source" value="{{ $activeSource }}">
                            <button type="submit" class="btn btn-danger btn-sm" style="width:100%;">Delete</button>
                        </form>
                    </div>
                @else
                    <div class="listing-card-readonly">
                        <a href="{{ route('listings.show', $showParams) }}" class="btn btn-secondary btn-sm" style="width:100%;">View Details</a>
                        <span class="listing-readonly-note">{{ strtoupper($listingSource) }} listings are view-only here.</span>
                    </div>
                @endif
            </article>
        @endforeach
    </div>

    <div style="margin-top: 24px;">
        {{ $listings->withQueryString()->links('components.pagination') }}
    </div>
@else
    <div class="empty-state">
        <p>{{ $emptyCopy }}</p>
        <a href="{{ route('listings.create', ['source' => $createSource]) }}" class="btn btn-primary">Create your first listing</a>
    </div>
@endif

@endsection
