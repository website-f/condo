@extends('layouts.app')
@php
    $editListingUrl = route('listings.edit', array_filter([
        'id' => $listing->id,
        'source' => ($listing->source_key ?? 'ipp') === 'ipp' ? null : $listing->source_key,
        'return_source' => $returnSource,
    ], static fn ($value) => $value !== null));
    $backToListingsUrl = route('listings.index', ['source' => $returnSource]);
    $isCondoListing = ($listing->source_key ?? '') === 'condo';
@endphp
@section('title', $listing->propertyname)
@section('page-title', 'Listing Details')
@section('topbar-actions')
    <div class="listing-topbar-actions">
        @if($canManageListing)
            <a href="{{ $editListingUrl }}" class="btn btn-secondary btn-sm">Edit</a>
            @if($isCondoListing)
                <a href="{{ route('seo.edit', $listing->id) }}" class="btn btn-secondary btn-sm">SEO</a>
                <a href="{{ route('social.create', ['listing' => $listing->id]) }}" class="btn btn-secondary btn-sm">Schedule</a>
            @endif
            <form method="POST" action="{{ route('listings.destroy', $listing->id) }}" onsubmit="return confirm('Delete this listing? This action cannot be undone.');" style="margin:0;">
                @csrf
                @method('DELETE')
                <input type="hidden" name="source" value="{{ $listing->source_key }}">
                <input type="hidden" name="return_source" value="{{ $returnSource }}">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
        @else
            <span class="badge badge-default">{{ strtoupper($listing->source_key ?? 'ipp') }} Read Only</span>
        @endif
        <a href="{{ $backToListingsUrl }}" class="btn btn-secondary btn-sm">Back</a>
    </div>
@endsection

@section('content')
@php
    $listingSource = $listing->source_key ?? 'ipp';
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

@if(! $canManageListing)
    <div class="alert" style="background: var(--accent-light); color: var(--text); border: 1px solid var(--border-light);">
        This {{ strtoupper($listingSource) }} listing is synced in read-only mode for now.
    </div>
@endif

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
        --apple-radius: 20px;
        --apple-radius-sm: 12px;
        --apple-shadow: 0 4px 24px rgba(0,0,0,0.04);
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    body {
        background-color: var(--apple-bg);
        color: var(--apple-text);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    .listing-detail-page {
        display: grid;
        gap: 32px;
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    .listing-topbar-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .listing-topbar-actions form {
        margin: 0;
    }

    .listing-hero-card {
        padding: 0;
        overflow: hidden;
        background: var(--apple-card);
        border: 1px solid var(--apple-border);
        border-radius: var(--apple-radius);
        box-shadow: var(--apple-shadow);
    }

    .listing-hero-grid {
        display: flex;
        flex-direction: column;
    }

    .listing-gallery-shell {
        padding: 24px;
        border-bottom: 1px solid var(--apple-border);
        background: var(--apple-card);
    }

    .listing-stage {
        position: relative;
        display: block;
        height: 520px;
        border-radius: 16px;
        overflow: hidden;
        background: #f5f5f7;
        border: 1px solid var(--apple-border);
    }

    .listing-stage img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    .listing-stage-count {
        position: absolute;
        left: 20px;
        bottom: 20px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        color: var(--apple-text);
        font-size: 13px;
        font-weight: 600;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
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
        background: #f5f5f7;
        cursor: pointer;
        transition: var(--transition);
        min-height: 82px;
        aspect-ratio: 4/3;
    }

    .listing-thumb:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .listing-thumb.is-active {
        border-color: var(--apple-blue);
        box-shadow: 0 4px 12px rgba(0,102,204,0.1);
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
        border-radius: 16px;
        padding: 32px;
        display: grid;
        place-items: center;
        text-align: center;
        background: #fbfbfd;
        color: var(--apple-text);
        border: 1px dashed var(--apple-border);
    }

    .listing-gallery-placeholder span {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: inline-grid;
        place-items: center;
        font-size: 32px;
        font-weight: 500;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        color: var(--apple-text-muted);
    }

    .listing-gallery-placeholder p {
        max-width: 280px;
        color: var(--apple-text-muted);
        font-size: 15px;
        line-height: 1.5;
    }

    .listing-summary-panel {
        display: flex;
        flex-direction: column;
        gap: 28px;
        padding: 40px 32px;
        background: var(--apple-card);
    }

    .listing-eyebrow {
        font-size: 13px;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--apple-text-muted);
        margin-bottom: 12px;
    }

    .listing-title {
        font-size: clamp(28px, 4vw, 44px);
        line-height: 1.15;
        letter-spacing: -0.02em;
        color: var(--apple-text);
        margin-bottom: 16px;
        font-weight: 700;
    }

    .listing-location {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        color: var(--apple-text-muted);
        font-size: 16px;
    }

    .listing-location-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--apple-blue);
        flex-shrink: 0;
    }

    .listing-price-panel {
        padding: 24px;
        border-radius: var(--apple-radius-sm);
        border: 1px solid var(--apple-border);
        background: #f5f5f7;
    }

    .listing-price-label {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--apple-text-muted);
        margin-bottom: 8px;
    }

    .listing-price-value {
        font-size: clamp(32px, 3.5vw, 48px);
        line-height: 1;
        letter-spacing: -0.025em;
        font-weight: 700;
        color: var(--apple-text);
    }

    .listing-price-value span {
        font-size: 18px;
        letter-spacing: 0;
        color: var(--apple-text-muted);
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
        padding: 8px 16px;
        border-radius: 999px;
        background: #f5f5f7;
        color: var(--apple-text);
        border: 1px solid rgba(0,0,0,0.04);
        font-size: 14px;
        font-weight: 600;
    }

    .listing-highlights-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
    }

    .listing-highlight {
        padding: 20px;
        border-radius: var(--apple-radius-sm);
        background: var(--apple-card);
        border: 1px solid var(--apple-border);
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }

    .listing-highlight-label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--apple-text-muted);
    }

    .listing-highlight-value {
        display: block;
        color: var(--apple-text);
        font-size: 18px;
        font-weight: 700;
        line-height: 1.3;
        word-break: break-word;
    }

    .listing-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 16px;
        padding-top: 24px;
        border-top: 1px solid var(--apple-border);
    }

    .listing-meta-item span {
        display: block;
        font-size: 12px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--apple-text-muted);
        margin-bottom: 6px;
    }

    .listing-meta-item strong {
        display: block;
        font-size: 15px;
        color: var(--apple-text);
        font-weight: 600;
        line-height: 1.4;
        word-break: break-word;
    }

    .listing-body-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(360px, 1fr);
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
        top: 24px;
    }

    .listing-section, .listing-side-card {
        display: grid;
        gap: 24px;
        padding: 32px;
        background: var(--apple-card);
        border-radius: var(--apple-radius);
        border: 1px solid var(--apple-border);
        box-shadow: var(--apple-shadow);
    }

    .listing-section-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        padding-bottom: 16px;
    }

    .listing-section-kicker {
        font-size: 13px;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--apple-blue);
        margin-bottom: 8px;
    }

    .listing-section-head h3, .listing-side-card h4 {
        font-size: 26px;
        line-height: 1.1;
        letter-spacing: -0.02em;
        color: var(--apple-text);
        font-weight: 700;
        margin: 0;
    }

    .listing-side-card h4 {
        font-size: 20px;
    }

    .listing-section-note {
        font-size: 14px;
        color: var(--apple-text-muted);
        font-weight: 500;
    }

    .listing-copy {
        font-size: 16px;
        line-height: 1.7;
        color: var(--apple-text);
        white-space: normal;
    }

    .listing-copy p + p {
        margin-top: 16px;
    }

    .listing-empty-copy {
        padding: 32px;
        border-radius: var(--apple-radius-sm);
        background: #fbfbfd;
        color: var(--apple-text-muted);
        font-size: 16px;
        text-align: center;
        border: 1px dashed var(--apple-border);
        font-weight: 500;
    }

    .listing-facts-grid, .listing-records-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .listing-fact, .listing-record {
        padding: 20px;
        border-radius: var(--apple-radius-sm);
        background: #f5f5f7;
        border: 1px solid rgba(0,0,0,0.04);
    }

    .listing-fact span, .listing-record span {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--apple-text-muted);
    }

    .listing-fact strong, .listing-record strong {
        display: block;
        font-size: 16px;
        color: var(--apple-text);
        font-weight: 600;
        line-height: 1.4;
        word-break: break-word;
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
        border-bottom: 1px solid var(--apple-border);
    }

    .listing-side-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .listing-side-row span {
        color: var(--apple-text-muted);
        font-size: 14px;
        font-weight: 500;
        flex-shrink: 0;
    }

    .listing-side-row strong {
        color: var(--apple-text);
        font-size: 15px;
        font-weight: 600;
        text-align: right;
        line-height: 1.4;
        word-break: break-word;
    }

    .listing-agent {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
        border-radius: var(--apple-radius-sm);
        background: #f5f5f7;
        border: 1px solid rgba(0,0,0,0.04);
    }

    .listing-agent-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        overflow: hidden;
        background: #ffffff;
        color: var(--apple-blue);
        display: grid;
        place-items: center;
        font-size: 24px;
        font-weight: 700;
        flex-shrink: 0;
        border: 1px solid var(--apple-border);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .listing-agent-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .listing-agent-label {
        font-size: 12px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--apple-blue);
        margin-bottom: 6px;
    }

    .listing-agent-name {
        font-size: 20px;
        line-height: 1.2;
        letter-spacing: -0.02em;
        color: var(--apple-text);
        font-weight: 700;
        margin-bottom: 4px;
    }

    .listing-agent-meta {
        font-size: 14px;
        color: var(--apple-text-muted);
        line-height: 1.5;
        font-weight: 500;
    }

    .listing-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .listing-actions .btn {
        padding: 8px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        text-align: center;
        border: 1px solid var(--apple-border);
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f5f5f7;
        color: var(--apple-text);
    }

    .listing-actions .btn:hover {
        background: rgba(0,0,0,0.06);
    }

    .listing-actions .btn-primary {
        background: var(--apple-blue);
        color: #ffffff;
        border-color: var(--apple-blue);
    }

    .listing-actions .btn-primary:hover {
        background: var(--apple-blue-hover);
    }

    .listing-summary-actions {
        margin-top: 4px;
    }

    .listing-mobile-actions-panel {
        display: none;
    }

    .listing-mobile-actions-card {
        padding: 22px;
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,249,251,0.98) 100%);
        border: 1px solid var(--apple-border);
        box-shadow: var(--apple-shadow);
        display: grid;
        gap: 18px;
    }

    .listing-mobile-actions-header {
        display: grid;
        gap: 6px;
    }

    .listing-mobile-actions-kicker {
        font-size: 11px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--apple-blue);
    }

    .listing-mobile-actions-title {
        font-size: 20px;
        line-height: 1.15;
        letter-spacing: -0.03em;
        color: var(--apple-text);
        font-weight: 700;
    }

    .listing-mobile-actions-subtitle {
        font-size: 14px;
        line-height: 1.6;
        color: var(--apple-text-muted);
    }

    .listing-mobile-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .listing-mobile-actions-grid .btn,
    .listing-mobile-actions-grid form {
        width: 100%;
    }

    .listing-mobile-actions-grid .btn {
        min-height: 48px;
        border-radius: 16px;
        font-size: 14px;
        font-weight: 700;
    }

    .listing-mobile-actions-grid form {
        margin: 0;
    }

    .listing-mobile-readonly {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 0 14px;
        border-radius: 16px;
        background: rgba(0, 102, 204, 0.08);
        color: var(--apple-blue);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    /* Lightbox */
    .lightbox-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0,0,0,0.95);
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .lightbox-overlay.is-open { display: flex; }
    .lightbox-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.15);
        border: none;
        color: #fff;
        font-size: 22px;
        cursor: pointer;
        display: grid;
        place-items: center;
        z-index: 10;
        backdrop-filter: blur(8px);
    }
    .lightbox-close:hover { background: rgba(255,255,255,0.25); }
    .lightbox-counter {
        position: absolute;
        top: 20px;
        left: 20px;
        color: rgba(255,255,255,0.7);
        font-size: 14px;
        font-weight: 600;
        z-index: 10;
    }
    .lightbox-image-wrap {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        overflow: hidden;
        touch-action: pan-y;
        position: relative;
    }
    .lightbox-image-wrap img {
        max-width: 92vw;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 8px;
        user-select: none;
        -webkit-user-drag: none;
        transition: opacity 0.2s ease;
    }
    .lightbox-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(255,255,255,0.12);
        border: none;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
        display: grid;
        place-items: center;
        backdrop-filter: blur(8px);
        z-index: 10;
    }
    .lightbox-nav:hover { background: rgba(255,255,255,0.25); }
    .lightbox-prev { left: 16px; }
    .lightbox-next { right: 16px; }
    .lightbox-strip {
        display: flex;
        gap: 6px;
        padding: 12px 16px;
        overflow-x: auto;
        justify-content: center;
        scrollbar-width: none;
        max-width: 100vw;
    }
    .lightbox-strip::-webkit-scrollbar { display: none; }
    .lightbox-strip button {
        width: 52px;
        height: 40px;
        border-radius: 6px;
        overflow: hidden;
        border: 2px solid transparent;
        padding: 0;
        cursor: pointer;
        flex-shrink: 0;
        opacity: 0.5;
        transition: all 0.2s;
        background: transparent;
    }
    .lightbox-strip button.is-active { border-color: #fff; opacity: 1; }
    .lightbox-strip button:hover { opacity: 0.85; }
    .lightbox-strip button img { width: 100%; height: 100%; object-fit: cover; display: block; }

    /* Mobile gallery: horizontal scroll strip instead of grid */
    @media (max-width: 640px) {
        .listing-thumbnail-row {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding-bottom: 4px;
            margin-top: 12px;
        }
        .listing-thumbnail-row::-webkit-scrollbar { display: none; }
        .listing-thumb {
            flex-shrink: 0;
            width: 72px;
            min-height: 56px;
            aspect-ratio: 4/3;
            border-radius: 8px;
        }
        .listing-thumb img { min-height: 56px; }
        .listing-stage { cursor: pointer; }
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

        .listing-topbar-actions {
            display: none;
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

        .listing-section, .listing-side-card {
            padding: 24px;
        }

        .listing-summary-actions {
            display: none;
        }

        .listing-mobile-actions-panel {
            display: block;
        }
    }

    @media (max-width: 520px) {
        .listing-title {
            font-size: 28px;
        }

        .listing-price-value {
            font-size: 32px;
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

        .listing-mobile-actions-card {
            padding: 18px;
            border-radius: 20px;
        }

        .listing-mobile-actions-grid {
            grid-template-columns: 1fr;
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
                    <div class="listing-eyebrow">{{ strtoupper($listingSource) }} Property Listing</div>
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

                <div class="listing-actions listing-summary-actions">
                    @if($canManageListing)
                        <a href="{{ $editListingUrl }}" class="btn btn-primary">Edit</a>
                    @endif
                    <a href="{{ $backToListingsUrl }}" class="btn btn-secondary">Back</a>
                </div>

                {{-- Managed By --}}
                <div style="border-top: 1px solid var(--apple-border); padding-top: 20px;">
                    <div class="listing-agent">
                        <div class="listing-agent-avatar" style="width:48px;height:48px;font-size:16px;">
                            @if($agent?->photo_url)
                                <img src="{{ $agent->photo_url }}" alt="{{ $agentName }}">
                            @else
                                {{ $agentInitials }}
                            @endif
                        </div>
                        <div>
                            <div class="listing-agent-label" style="font-size:10px;margin-bottom:3px;">Listing Owner</div>
                            <div class="listing-agent-name" style="font-size:15px;">{{ $agentName }}</div>
                            <div class="listing-agent-meta" style="font-size:12px;">{{ $listing->username }}@if($agentPhone) · {{ $agentPhone }}@endif</div>
                            @if($agentEmail)
                                <div class="listing-agent-meta" style="font-size:12px;">{{ $agentEmail }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <section class="listing-mobile-actions-panel">
        <div class="listing-mobile-actions-card">
            <div class="listing-mobile-actions-header">
                <div class="listing-mobile-actions-kicker">Quick Actions</div>
                <div class="listing-mobile-actions-title">Manage this listing comfortably on mobile.</div>
                <div class="listing-mobile-actions-subtitle">The top bar stays clean on smaller screens, so the main actions live here with larger tap targets.</div>
            </div>

            <div class="listing-mobile-actions-grid">
                @if($canManageListing)
                    <a href="{{ $editListingUrl }}" class="btn btn-primary">Edit Listing</a>

                    @if($isCondoListing)
                        <a href="{{ route('seo.edit', $listing->id) }}" class="btn btn-secondary">SEO</a>
                        <a href="{{ route('social.create', ['listing' => $listing->id]) }}" class="btn btn-secondary">Schedule</a>
                    @endif

                    <form method="POST" action="{{ route('listings.destroy', $listing->id) }}" onsubmit="return confirm('Delete this listing? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="source" value="{{ $listing->source_key }}">
                        <input type="hidden" name="return_source" value="{{ $returnSource }}">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                @else
                    <div class="listing-mobile-readonly">{{ strtoupper($listing->source_key ?? 'ipp') }} Read Only</div>
                @endif

                <a href="{{ $backToListingsUrl }}" class="btn btn-secondary">Back to Listings</a>
            </div>
        </div>
    </section>
</div>

@if(!empty($gallery) && count($gallery) > 0)
<div class="lightbox-overlay" id="lightbox">
    <button class="lightbox-close" id="lightbox-close" aria-label="Close">&times;</button>
    <span class="lightbox-counter" id="lightbox-counter"></span>
    <button class="lightbox-nav lightbox-prev" id="lightbox-prev" aria-label="Previous">&lsaquo;</button>
    <button class="lightbox-nav lightbox-next" id="lightbox-next" aria-label="Next">&rsaquo;</button>
    <div class="lightbox-image-wrap" id="lightbox-image-wrap">
        <img id="lightbox-img" src="" alt="">
    </div>
    <div class="lightbox-strip" id="lightbox-strip">
        @foreach($gallery as $photo)
            <button type="button" data-lb-idx="{{ $loop->index }}">
                <img src="{{ $photo }}" alt="Thumbnail {{ $loop->iteration }}" loading="lazy">
            </button>
        @endforeach
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
(function () {
    var gallery = @json($gallery ?? []);
    var currentIdx = 0;

    // -- Thumbnail click -> update main image --
    document.querySelectorAll('[data-gallery-thumb]').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            var mainImg = document.getElementById('listing-main-image');
            var mainLink = document.getElementById('listing-main-link');
            if (!mainImg || !mainLink) return;
            mainImg.src = thumb.dataset.full;
            mainImg.alt = thumb.dataset.alt || mainImg.alt;
            mainLink.href = thumb.dataset.full;
            document.querySelectorAll('[data-gallery-thumb]').forEach(function (t) { t.classList.remove('is-active'); });
            thumb.classList.add('is-active');
            currentIdx = gallery.indexOf(thumb.dataset.full);
            if (currentIdx < 0) currentIdx = 0;
        });
    });

    // -- Lightbox --
    if (gallery.length === 0) return;

    var overlay = document.getElementById('lightbox');
    var lbImg = document.getElementById('lightbox-img');
    var lbCounter = document.getElementById('lightbox-counter');
    var lbStrip = document.getElementById('lightbox-strip');
    if (!overlay || !lbImg) return;

    function openLightbox(idx) {
        currentIdx = idx;
        renderLightbox();
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function renderLightbox() {
        lbImg.src = gallery[currentIdx];
        lbImg.alt = 'Photo ' + (currentIdx + 1);
        lbCounter.textContent = (currentIdx + 1) + ' / ' + gallery.length;
        var stripBtns = lbStrip.querySelectorAll('button');
        stripBtns.forEach(function (b) { b.classList.remove('is-active'); });
        if (stripBtns[currentIdx]) {
            stripBtns[currentIdx].classList.add('is-active');
            stripBtns[currentIdx].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
    }

    function goNext() { currentIdx = (currentIdx + 1) % gallery.length; renderLightbox(); }
    function goPrev() { currentIdx = (currentIdx - 1 + gallery.length) % gallery.length; renderLightbox(); }

    // Click main image -> open lightbox
    var mainLink = document.getElementById('listing-main-link');
    if (mainLink) {
        mainLink.addEventListener('click', function (e) {
            e.preventDefault();
            var src = mainLink.querySelector('img')?.src || gallery[0];
            var idx = gallery.indexOf(src);
            openLightbox(idx >= 0 ? idx : 0);
        });
    }

    document.getElementById('lightbox-close').addEventListener('click', closeLightbox);
    document.getElementById('lightbox-prev').addEventListener('click', goPrev);
    document.getElementById('lightbox-next').addEventListener('click', goNext);

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay || e.target.id === 'lightbox-image-wrap') closeLightbox();
    });

    lbStrip.querySelectorAll('button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openLightbox(parseInt(btn.dataset.lbIdx, 10));
        });
    });

    // Keyboard
    document.addEventListener('keydown', function (e) {
        if (!overlay.classList.contains('is-open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') goNext();
        if (e.key === 'ArrowLeft') goPrev();
    });

    // Swipe support
    var touchStartX = 0;
    var wrap = document.getElementById('lightbox-image-wrap');
    wrap.addEventListener('touchstart', function (e) { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
    wrap.addEventListener('touchend', function (e) {
        var diff = e.changedTouches[0].screenX - touchStartX;
        if (Math.abs(diff) > 50) {
            if (diff < 0) goNext(); else goPrev();
        }
    }, { passive: true });
})();
</script>
@endsection
