@extends('public.layout')

@section('title', $listing->propertyname)
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $listing->description_text), 160))

@section('content')
<p style="margin:0 0 14px"><a href="/listings">&larr; Back to listings</a></p>

<h1 class="section-title" style="margin-top:0">{{ $listing->propertyname }}</h1>
<p class="card-meta" style="margin-top:-8px">
    <span class="badge {{ $source }}">{{ strtoupper($source) }}</span>
    {{ $listing->location_label }}
</p>

<div class="listing-detail-grid">
    <div>
        @if($listing->gallery_images)
            <div class="listing-gallery">
                @foreach(array_slice($listing->gallery_images, 0, 7) as $img)
                    <img src="{{ $img }}" alt="{{ $listing->propertyname }}" loading="lazy">
                @endforeach
            </div>
        @elseif($listing->image_url)
            <img src="{{ $listing->image_url }}" alt="{{ $listing->propertyname }}" style="width:100%;border-radius:12px">
        @endif

        @if($listing->description_text)
            <div style="margin-top:18px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px">
                <h3 style="margin:0 0 10px;font-size:16px">Description</h3>
                <div style="white-space:pre-line;color:#334155">{{ $listing->description_text }}</div>
            </div>
        @endif
    </div>

    <aside>
        <div class="detail-panel" style="margin-bottom:16px">
            <div class="price" style="font-size:22px;margin-bottom:6px">{{ $listing->formatted_price }}</div>
            <div style="color:#64748b;font-size:13px">{{ $listing->propertytype }} &middot; {{ $listing->listingtype }}</div>
        </div>

        @if($listing->general_details)
            <div class="detail-panel">
                <h3 style="margin:0 0 12px;font-size:15px">Property Details</h3>
                <dl>
                    @foreach($listing->general_details as $label => $value)
                        <dt>{{ $label }}</dt><dd>{{ $value }}</dd>
                    @endforeach
                </dl>
            </div>
        @endif
    </aside>
</div>
@endsection
