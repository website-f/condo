@extends('public.layout')

@section('title', 'Listings — ' . ($agent->full_name ?? $agent->username))

@section('content')
<h1 class="section-title" style="margin-top:0">Listings</h1>

<div class="filter-bar">
    @foreach(['all'=>'All','ipp'=>'IPP','icp'=>'ICP'] as $key=>$label)
        <a href="/listings{{ $key === 'all' ? '' : '?source='.$key }}" class="{{ $sourceFilter === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

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

    <div class="pagination">
        {{ $listings->links() }}
    </div>
@endif
@endsection
