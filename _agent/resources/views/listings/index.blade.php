@extends('layouts.app')
@section('title', 'Listings')
@section('page-title', 'Listings')
@section('topbar-actions')
    <a href="{{ route('listings.create') }}" class="btn btn-primary btn-sm">New Listing</a>
@endsection

@section('content')
<div class="filters">
    <form method="GET" action="{{ route('listings.index') }}" style="display:flex;gap:16px;flex-wrap:wrap;width:100%;align-items:center;">
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
            @foreach($states as $st)
                <option value="{{ $st->state }}" {{ request('state') === $st->state ? 'selected' : '' }}>{{ $st->state }}</option>
            @endforeach
        </select>
        <input type="number" name="min_price" class="form-input" placeholder="Min price" value="{{ request('min_price') }}">
        <input type="number" name="max_price" class="form-input" placeholder="Max price" value="{{ request('max_price') }}">
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->hasAny(['search', 'listingtype', 'propertytype', 'state', 'min_price', 'max_price']))
            <a href="{{ route('listings.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>
</div>

@if($listings->count())
    <div class="grid-4">
        @foreach($listings as $listing)
            <article class="listing-card">
                <a href="{{ route('listings.show', $listing->id) }}" style="display:block;text-decoration:none;color:inherit;">
                    <div class="listing-card-img">
                        @if($listing->photopath)
                            <img src="{{ $listing->photopath }}" alt="{{ $listing->propertyname }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            No Image
                        @endif
                    </div>
                    
                    <div class="listing-card-body" style="padding-bottom:0;">
                        <div class="listing-card-price" style="text-decoration:none;">{{ $listing->formatted_price }}</div>
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

                    <div class="btn-group" style="justify-content:flex-end;gap:12px;flex-wrap:wrap;border-top:1px solid var(--border-light);padding-top:16px;">
                        <a href="{{ route('listings.edit', $listing->id) }}" class="btn btn-secondary btn-sm" style="flex:1;">Edit</a>
                        <form method="POST" action="{{ route('listings.destroy', $listing->id) }}" onsubmit="return confirm('Delete this listing? This action cannot be undone.');" style="margin:0;flex:1;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" style="width:100%;">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="pagination">{{ $listings->withQueryString()->links('components.pagination') }}</div>
@else
    <div class="card">
        <div class="empty-state">
            <p>No listings found matching your criteria</p>
            <a href="{{ route('listings.create') }}" class="btn btn-primary">Create your first listing</a>
        </div>
    </div>
@endif
@endsection
