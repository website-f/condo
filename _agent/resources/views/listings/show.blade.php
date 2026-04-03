@extends('layouts.app')
@section('title', $listing->propertyname)
@section('page-title', 'Listing Details')
@section('topbar-actions')
    <a href="{{ route('listings.edit', $listing->id) }}" class="btn btn-secondary btn-sm">Edit</a>
    <form method="POST" action="{{ route('listings.destroy', $listing->id) }}" onsubmit="return confirm('Delete this listing? This action cannot be undone.');" style="margin:0;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
    </form>
    <a href="{{ route('listings.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
@php
    $gallery = $listing->gallery_images;
    $generalDetails = $listing->general_details;
    $description = $listing->description_text;
    $locationLabel = $listing->location_label;
    $publishedAt = $listing->formatted_created_date ?? $listing->createddate;
    $updatedAt = $listing->formatted_updated_date ?? $listing->updateddate;
    $priceSuffix = strcasecmp((string) $listing->listingtype, 'Rent') === 0 ? ' / month' : '';
    $agent = $listing->agent;
    $agentName = trim((string) ($agent?->full_name ?: $listing->username));
    $agentPhone = $agent?->detail?->phone;
    $agentEmail = $agent?->detail?->email;
    $agentInitials = '';

    foreach (preg_split('/\s+/', $agentName) as $segment) {
        if ($segment === '') continue;
        $agentInitials .= strtoupper(substr($segment, 0, 1));
        if (strlen($agentInitials) >= 2) break;
    }

    if ($agentInitials === '') {
        $agentInitials = strtoupper(substr($listing->username, 0, 2));
    }

    $heroFacts = [];
    foreach (['Bedrooms', 'Bathrooms', 'Built Up Area', 'Land Area', 'Car Park', 'Tenure', 'Occupancy', 'Unit Type'] as $label) {
        if (! empty($generalDetails[$label])) {
            $heroFacts[$label] = $generalDetails[$label];
        }
    }

    if (empty($heroFacts)) {
        foreach ([
            'Listing Type' => $listing->listingtype,
            'Property Type' => $listing->propertytype,
            'Area' => $listing->area,
            'State' => $listing->state,
        ] as $label => $value) {
            if (! empty($value)) {
                $heroFacts[$label] = $value;
            }
        }
    }

    $locationFacts = [];
    foreach ([
        'Address' => $generalDetails['Address'] ?? null,
        'Township' => $generalDetails['Township'] ?? null,
        'Postcode' => $generalDetails['Postcode'] ?? null,
        'Area' => $listing->area,
        'State' => $listing->state,
    ] as $label => $value) {
        if (! empty($value)) {
            $locationFacts[$label] = $value;
        }
    }

    $detailFacts = $generalDetails;
    foreach (array_keys($heroFacts) as $label) {
        unset($detailFacts[$label]);
    }
    foreach (['Address', 'Township', 'Postcode'] as $label) {
        unset($detailFacts[$label]);
    }

    $otherDetails = $listing->details->reject(
        fn ($detail) => in_array($detail->meta_key, ['Photos', 'Descriptions', 'General'], true)
    );
@endphp

<style>
    .listing-detail-page {
        display: grid;
        gap: 32px;
    }

    .listing-hero-card {
        padding: 0;
        overflow: hidden;
        background: var(--card-bg);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
    }

    .listing-hero-grid {
        display: flex;
        flex-direction: column;
    }

    .listing-gallery-shell {
        padding: 32px;
        border-bottom: 1px solid var(--border-light);
        background: var(--card-bg);
    }

    .listing-stage {
        position: relative;
        display: block;
        height: 520px;
        border-radius: var(--radius-lg);
        overflow: hidden;
        background: var(--accent-light);
        border: 1px solid var(--border-light);
    }

    .listing-stage img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .listing-stage-count {
        position: absolute;
        left: 16px;
        bottom: 16px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    .listing-thumbnail-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(88px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .listing-thumb {
        border: 2px solid transparent;
        border-radius: 12px;
        overflow: hidden;
        padding: 0;
        background: var(--accent-light);
        cursor: pointer;
        transition: all 0.2s ease;
        min-height: 82px;
    }

    .listing-thumb:hover,
    .listing-thumb.is-active {
        border-color: var(--text);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .listing-thumb img {
        width: 100%;
        height: 100%;
        min-height: 82px;
        object-fit: cover;
        display: block;
    }

    .listing-gallery-placeholder {
        min-height: 520px;
        border-radius: var(--radius-lg);
        padding: 32px;
        display: grid;
        place-items: center;
        text-align: center;
        background: var(--accent-light);
        color: var(--text);
        border: 1px dashed var(--border);
    }

    .listing-gallery-placeholder span {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: inline-grid;
        place-items: center;
        font-size: 32px;
        font-weight: 500;
        background: var(--card-bg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
        color: var(--text-secondary);
    }

    .listing-gallery-placeholder p {
        max-width: 280px;
        color: var(--text-secondary);
        font-size: 15px;
        line-height: 1.5;
    }

    .listing-summary-panel {
        display: flex;
        flex-direction: column;
        gap: 24px;
        padding: 32px;
        background: var(--card-bg);
    }

    .listing-eyebrow {
        font-size: 12px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 12px;
    }

    .listing-title {
        font-size: clamp(28px, 3vw, 40px);
        line-height: 1.1;
        letter-spacing: -0.021em;
        color: var(--text);
        margin-bottom: 12px;
        font-weight: 600;
    }

    .listing-location {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        color: var(--text-secondary);
        font-size: 15px;
    }

    .listing-location-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--text-secondary);
        flex-shrink: 0;
    }

    .listing-price-panel {
        padding: 24px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-light);
        background: var(--accent-light);
    }

    .listing-price-label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }

    .listing-price-value {
        font-size: clamp(32px, 3.1vw, 48px);
        line-height: 1;
        letter-spacing: -0.025em;
        font-weight: 600;
        color: var(--text);
    }

    .listing-price-value span {
        font-size: 16px;
        letter-spacing: 0;
        color: var(--text-secondary);
        font-weight: 500;
        margin-left: 8px;
    }

    .listing-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .listing-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 999px;
        background: var(--accent-light);
        color: var(--text);
        border: 1px solid var(--border-light);
        font-size: 13px;
        font-weight: 500;
    }

    .listing-highlights-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .listing-highlight {
        padding: 16px;
        border-radius: var(--radius-sm);
        background: var(--card-bg);
        border: 1px solid var(--border-light);
    }

    .listing-highlight-label {
        display: block;
        margin-bottom: 8px;
        font-size: 11px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .listing-highlight-value {
        display: block;
        color: var(--text);
        font-size: 16px;
        font-weight: 600;
        line-height: 1.4;
        word-break: break-word;
    }

    .listing-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .listing-meta-item {
        padding: 16px;
        border-radius: var(--radius-sm);
        background: var(--card-bg);
        border: 1px solid var(--border-light);
    }

    .listing-meta-item span {
        display: block;
        font-size: 11px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }

    .listing-meta-item strong {
        display: block;
        font-size: 14px;
        color: var(--text);
        font-weight: 500;
        line-height: 1.4;
        word-break: break-word;
    }

    .listing-body-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.82fr);
        gap: 32px;
        align-items: start;
    }

    .listing-main-column,
    .listing-side-column {
        display: grid;
        gap: 24px;
    }

    .listing-side-column .listing-side-card:first-child {
        position: sticky;
        top: 88px;
    }

    .listing-section {
        display: grid;
        gap: 24px;
        padding: 32px;
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
    }

    .listing-section-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
    }

    .listing-section-kicker {
        font-size: 12px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }

    .listing-section-head h3 {
        font-size: 24px;
        line-height: 1.1;
        letter-spacing: -0.021em;
        color: var(--text);
        font-weight: 600;
    }

    .listing-section-note {
        font-size: 14px;
        color: var(--text-secondary);
    }

    .listing-copy {
        font-size: 15px;
        line-height: 1.6;
        color: var(--text);
        white-space: normal;
    }

    .listing-copy p + p {
        margin-top: 16px;
    }

    .listing-empty-copy {
        padding: 24px;
        border-radius: var(--radius-sm);
        background: var(--accent-light);
        color: var(--text-secondary);
        font-size: 15px;
        text-align: center;
        border: 1px dashed var(--border);
    }

    .listing-facts-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .listing-fact {
        padding: 16px;
        border-radius: var(--radius-sm);
        background: var(--accent-light);
        border: 1px solid var(--border-light);
    }

    .listing-fact span {
        display: block;
        margin-bottom: 8px;
        font-size: 11px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .listing-fact strong {
        display: block;
        font-size: 15px;
        color: var(--text);
        font-weight: 500;
        line-height: 1.4;
        word-break: break-word;
    }

    .listing-side-card {
        display: grid;
        gap: 24px;
        padding: 32px;
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
    }

    .listing-side-card h4 {
        font-size: 18px;
        letter-spacing: -0.021em;
        color: var(--text);
        font-weight: 600;
    }

    .listing-side-list {
        display: grid;
        gap: 16px;
    }

    .listing-side-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border-light);
    }

    .listing-side-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .listing-side-row span {
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 500;
        flex-shrink: 0;
    }

    .listing-side-row strong {
        color: var(--text);
        font-size: 14px;
        font-weight: 500;
        text-align: right;
        line-height: 1.4;
        word-break: break-word;
    }

    .listing-agent {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        border-radius: var(--radius-md);
        background: var(--accent-light);
        border: 1px solid var(--border-light);
    }

    .listing-agent-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        overflow: hidden;
        background: var(--border);
        color: var(--text);
        display: grid;
        place-items: center;
        font-size: 20px;
        font-weight: 600;
        flex-shrink: 0;
        border: 1px solid var(--border-light);
    }

    .listing-agent-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .listing-agent-label {
        font-size: 11px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 6px;
    }

    .listing-agent-name {
        font-size: 18px;
        line-height: 1.2;
        letter-spacing: -0.021em;
        color: var(--text);
        font-weight: 600;
        margin-bottom: 4px;
    }

    .listing-agent-meta {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.5;
    }

    .listing-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .listing-records-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .listing-record {
        padding: 16px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-light);
        background: var(--accent-light);
    }

    .listing-record span {
        display: block;
        margin-bottom: 8px;
        font-size: 11px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .listing-record strong {
        display: block;
        font-size: 15px;
        color: var(--text);
        line-height: 1.5;
        white-space: pre-wrap;
        word-break: break-word;
        font-weight: 500;
    }

    @media (max-width: 1120px) {
        .listing-body-grid {
            grid-template-columns: 1fr;
        }

        .listing-side-column .listing-side-card:first-child {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .listing-detail-page {
            gap: 24px;
        }
        
        .listing-summary-panel,
        .listing-gallery-shell {
            padding: 24px;
        }

        .listing-stage,
        .listing-gallery-placeholder {
            height: 360px;
            min-height: 360px;
        }

        .listing-highlights-grid,
        .listing-facts-grid,
        .listing-records-grid {
            grid-template-columns: 1fr;
        }

        .listing-meta-grid {
            grid-template-columns: 1fr;
        }
        
        .listing-section, .listing-side-card {
            padding: 24px;
        }
    }

    @media (max-width: 520px) {
        .listing-title {
            font-size: 26px;
        }

        .listing-price-value {
            font-size: 30px;
        }

        .listing-thumbnail-row {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .listing-stage,
        .listing-gallery-placeholder {
            height: 280px;
            min-height: 280px;
        }

        .listing-side-row {
            flex-direction: column;
            gap: 6px;
        }

        .listing-side-row strong {
            text-align: left;
        }
    }
</style>

<div class="listing-detail-page">
    <section class="card listing-hero-card">
        <div class="listing-hero-grid">
            <div class="listing-gallery-shell">
                @if(!empty($gallery))
                    <a id="listing-main-link" href="{{ $gallery[0] }}" target="_blank" rel="noopener" class="listing-stage">
                        <img id="listing-main-image" src="{{ $gallery[0] }}" alt="{{ $listing->propertyname }}">
                        <span class="listing-stage-count">
                            {{ count($gallery) }} {{ \Illuminate\Support\Str::plural('photo', count($gallery)) }}
                        </span>
                    </a>

                    @if(count($gallery) > 1)
                        <div class="listing-thumbnail-row" aria-label="Property gallery thumbnails">
                            @foreach($gallery as $photo)
                                <button
                                    type="button"
                                    class="listing-thumb {{ $loop->first ? 'is-active' : '' }}"
                                    data-gallery-thumb
                                    data-full="{{ $photo }}"
                                    data-alt="{{ $listing->propertyname }} photo {{ $loop->iteration }}"
                                    aria-label="Show photo {{ $loop->iteration }}"
                                >
                                    <img src="{{ $photo }}" alt="{{ $listing->propertyname }} thumbnail {{ $loop->iteration }}" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="listing-gallery-placeholder">
                        <div>
                            <span>{{ strtoupper(substr($listing->propertytype ?: $listing->propertyname, 0, 1)) }}</span>
                            <p>No listing photos are available yet, but the rest of the property details are ready below.</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="listing-summary-panel">
                <div>
                    <div class="listing-eyebrow">Property Listing</div>
                    <h3 class="listing-title">{{ $listing->propertyname }}</h3>

                    <div class="listing-location">
                        <span class="listing-location-dot"></span>
                        <span>{{ $locationLabel ?: 'Location details not available yet' }}</span>
                    </div>
                </div>

                <div class="listing-price-panel">
                    <div class="listing-price-label">Current Asking Price</div>
                    <div class="listing-price-value">
                        {{ $listing->formatted_price }}
                        @if($priceSuffix)
                            <span>{{ $priceSuffix }}</span>
                        @endif
                    </div>
                </div>

                <div class="listing-chip-row">
                    @if($listing->listingtype)
                        <span class="listing-chip">{{ $listing->listingtype }}</span>
                    @endif
                    @if($listing->propertytype)
                        <span class="listing-chip">{{ $listing->propertytype }}</span>
                    @endif
                    @if($listing->state)
                        <span class="listing-chip">{{ $listing->state }}</span>
                    @endif
                </div>

                @if(!empty($heroFacts))
                    <div class="listing-highlights-grid">
                        @foreach($heroFacts as $label => $value)
                            <div class="listing-highlight">
                                <span class="listing-highlight-label">{{ $label }}</span>
                                <strong class="listing-highlight-value">{{ $value }}</strong>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="listing-meta-grid">
                    <div class="listing-meta-item">
                        <span>Property ID</span>
                        <strong>{{ $listing->propertyid ?: 'N/A' }}</strong>
                    </div>
                    <div class="listing-meta-item">
                        <span>Published</span>
                        <strong>{{ $publishedAt ?: 'N/A' }}</strong>
                    </div>
                    <div class="listing-meta-item">
                        <span>Last Updated</span>
                        <strong>{{ $updatedAt ?: 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="listing-body-grid">
        <div class="listing-main-column">
            <section class="listing-section">
                <div class="listing-section-head">
                    <div>
                        <div class="listing-section-kicker">Overview</div>
                        <h3>Description</h3>
                    </div>
                    <div class="listing-section-note">{{ count($gallery) }} gallery image{{ count($gallery) === 1 ? '' : 's' }}</div>
                </div>

                @if($description)
                    <div class="listing-copy">{!! nl2br(e($description)) !!}</div>
                @else
                    <p class="listing-empty-copy">No description was saved for this listing.</p>
                @endif
            </section>

            @if(!empty($detailFacts))
                <section class="listing-section">
                    <div class="listing-section-head">
                        <div>
                            <div class="listing-section-kicker">Specs</div>
                            <h3>Property Facts</h3>
                        </div>
                        <div class="listing-section-note">Structured from the shared legacy metadata</div>
                    </div>

                    <div class="listing-facts-grid">
                        @foreach($detailFacts as $label => $value)
                            <div class="listing-fact">
                                <span>{{ $label }}</span>
                                <strong>{{ $value }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($otherDetails->isNotEmpty())
                <section class="listing-section">
                    <div class="listing-section-head">
                        <div>
                            <div class="listing-section-kicker">Source Fields</div>
                            <h3>Additional Records</h3>
                        </div>
                        <div class="listing-section-note">Any extra metadata attached to this listing</div>
                    </div>

                    <div class="listing-records-grid">
                        @foreach($otherDetails as $detail)
                            <article class="listing-record">
                                <span>{{ \Illuminate\Support\Str::headline($detail->meta_key) }}</span>
                                <strong>{{ $detail->meta_value }}</strong>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="listing-side-column">
            <section class="listing-side-card">
                <div>
                    <div class="listing-section-kicker">Snapshot</div>
                    <h4>Listing Summary</h4>
                </div>

                <div class="listing-side-list">
                    @foreach(array_merge([
                        'Listing Type' => $listing->listingtype,
                        'Property Type' => $listing->propertytype,
                    ], $locationFacts, [
                        'Created' => $publishedAt,
                        'Updated' => $updatedAt,
                    ]) as $label => $value)
                        @if(!empty($value))
                            <div class="listing-side-row">
                                <span>{{ $label }}</span>
                                <strong>{{ $value }}</strong>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="listing-actions">
                    <a href="{{ route('listings.edit', $listing->id) }}" class="btn btn-primary">Edit Listing</a>
                    <a href="{{ route('listings.index') }}" class="btn btn-secondary">Back to Listings</a>
                </div>
            </section>

            <section class="listing-side-card">
                <div>
                    <div class="listing-section-kicker">Agent</div>
                    <h4>Managed By</h4>
                </div>

                <div class="listing-agent">
                    <div class="listing-agent-avatar">
                        @if($agent?->photo_url)
                            <img src="{{ $agent->photo_url }}" alt="{{ $agentName }}">
                        @else
                            {{ $agentInitials }}
                        @endif
                    </div>

                    <div>
                        <div class="listing-agent-label">Listing Owner</div>
                        <div class="listing-agent-name">{{ $agentName }}</div>
                        <div class="listing-agent-meta">{{ $listing->username }}</div>
                        @if($agentPhone)
                            <div class="listing-agent-meta">{{ $agentPhone }}</div>
                        @endif
                        @if($agentEmail)
                            <div class="listing-agent-meta">{{ $agentEmail }}</div>
                        @endif
                    </div>
                </div>

                <div class="listing-actions">
                    <a href="{{ route('profile.index') }}" class="btn btn-secondary">Open Profile</a>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('[data-gallery-thumb]').forEach(function (thumbnail) {
        thumbnail.addEventListener('click', function () {
            var mainImage = document.getElementById('listing-main-image');
            var mainLink = document.getElementById('listing-main-link');

            if (!mainImage || !mainLink) {
                return;
            }

            mainImage.src = thumbnail.dataset.full;
            mainImage.alt = thumbnail.dataset.alt || mainImage.alt;
            mainLink.href = thumbnail.dataset.full;

            document.querySelectorAll('[data-gallery-thumb]').forEach(function (item) {
                item.classList.remove('is-active');
            });

            thumbnail.classList.add('is-active');
        });
    });
</script>
@endsection
