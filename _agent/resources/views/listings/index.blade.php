@extends('layouts.app')
@section('title', 'Listings')
@section('page-title', 'Listings')
@section('content')
@php
    $filterKeys = ['search', 'listingtype', 'propertytype', 'state', 'min_price', 'max_price', 'sort', 'dir'];
    $currentFilters = request()->except('page', 'source');
    $createSource = in_array($activeSource, ['ipp', 'icp'], true) ? $activeSource : 'ipp';
    $hasFilters = request()->hasAny($filterKeys);
    $bulkTotalCount = method_exists($listings, 'total') ? $listings->total() : $listings->count();
    $bulkStateKey = 'listing-bulk:' . sha1(auth('agent')->user()->username . '|' . http_build_query(array_filter(array_merge($currentFilters, ['source' => $activeSource]), static fn ($value) => $value !== null && $value !== '')));
    $emptyCopy = match ($activeSource) {
        'ipp' => 'No IPP listings found matching your criteria.',
        'icp' => 'No ICP listings found matching your criteria.',
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

    .listing-source-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 999px;
        background: rgba(153, 92, 0, 0.12);
        color: #995c00;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
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

    .bulk-toolbar {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        padding: 12px 16px;
        margin-bottom: 24px;
        background: var(--apple-card);
        border: 1px solid var(--apple-border);
        border-radius: 14px;
        box-shadow: var(--apple-shadow);
    }

    .bulk-toolbar.has-selection {
        background: #f0f8ff;
        border-color: rgba(0, 102, 204, 0.25);
    }

    .bulk-checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 600;
        color: var(--apple-text);
        cursor: pointer;
        user-select: none;
    }

    .bulk-checkbox-label input {
        width: 18px;
        height: 18px;
        accent-color: var(--apple-blue);
        cursor: pointer;
    }

    .bulk-selection-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        padding: 2px 9px;
        border-radius: 999px;
        background: rgba(0, 102, 204, 0.12);
        color: var(--apple-blue);
        font-size: 12px;
        font-weight: 700;
        margin-left: 4px;
    }

    .bulk-toolbar-actions {
        display: flex;
        gap: 8px;
        margin-left: auto;
        align-items: center;
    }

    .bulk-toolbar-actions .btn {
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 600;
        gap: 6px;
    }

    .bulk-toolbar-actions .btn:disabled,
    .bulk-toolbar-actions .btn[disabled] {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .bulk-clear-button {
        background: transparent;
        border: none;
        color: var(--apple-text-muted);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        padding: 4px 10px;
        border-radius: 999px;
        transition: var(--transition);
    }

    .bulk-clear-button:hover {
        color: var(--apple-text);
        background: rgba(0,0,0,0.04);
    }

    .bulk-modal[hidden] {
        display: none;
    }

    .bulk-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: grid;
        place-items: center;
        padding: 24px;
    }

    .bulk-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.42);
        backdrop-filter: blur(12px);
    }

    .bulk-modal-dialog {
        position: relative;
        width: min(640px, 100%);
        background: #ffffff;
        border-radius: 28px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 28px 80px rgba(15, 23, 42, 0.22);
        overflow: hidden;
    }

    .bulk-modal-shell {
        display: grid;
        gap: 12px;
    }

    .bulk-modal-header,
    .bulk-modal-body,
    .bulk-modal-footer {
        padding: 24px 28px;
    }

    .bulk-modal-header {
        border-bottom: 1px solid var(--apple-border);
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
    }

    .bulk-modal-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -0.03em;
        color: var(--apple-text);
        margin-bottom: 6px;
    }

    .bulk-modal-subtitle {
        font-size: 14px;
        color: var(--apple-text-muted);
        line-height: 1.6;
    }

    .bulk-modal-close {
        border: none;
        background: rgba(0,0,0,0.05);
        width: 40px;
        height: 40px;
        border-radius: 999px;
        cursor: pointer;
        font-size: 20px;
        color: var(--apple-text);
    }

    .bulk-modal-block {
        display: grid;
        gap: 14px;
    }

    .bulk-modal-summary {
        padding: 16px 18px;
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid var(--apple-border);
        font-size: 14px;
        color: var(--apple-text-muted);
        line-height: 1.7;
    }

    .bulk-modal-summary strong {
        color: var(--apple-text);
    }

    .bulk-target-grid {
        display: grid;
        gap: 12px;
    }

    .bulk-target-option {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 14px;
        align-items: flex-start;
        padding: 16px 18px;
        border-radius: 20px;
        border: 1px solid var(--apple-border);
        background: #ffffff;
        cursor: pointer;
        transition: var(--transition);
    }

    .bulk-target-option:hover {
        border-color: rgba(0, 102, 204, 0.24);
        box-shadow: 0 16px 36px rgba(0, 102, 204, 0.08);
    }

    .bulk-target-option input {
        margin-top: 3px;
        accent-color: var(--apple-blue);
    }

    .bulk-target-option.is-selected {
        border-color: rgba(0, 102, 204, 0.42);
        background: rgba(0, 102, 204, 0.04);
        box-shadow: 0 18px 38px rgba(0, 102, 204, 0.10);
    }

    .bulk-target-name {
        font-size: 15px;
        font-weight: 700;
        color: var(--apple-text);
        margin-bottom: 6px;
    }

    .bulk-target-description {
        font-size: 13px;
        color: var(--apple-text-muted);
        line-height: 1.6;
    }

    .bulk-progress {
        display: grid;
        gap: 12px;
    }

    .bulk-progress-bar {
        width: 100%;
        height: 12px;
        border-radius: 999px;
        background: #e8edf4;
        overflow: hidden;
    }

    .bulk-progress-bar span {
        display: block;
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #0066cc 0%, #35a4ff 100%);
        border-radius: inherit;
        transition: width 0.22s ease;
    }

    .bulk-progress-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--apple-text);
    }

    .bulk-result-list {
        display: grid;
        gap: 8px;
        max-height: 180px;
        overflow: auto;
    }

    .bulk-result-item {
        padding: 12px 14px;
        border-radius: 14px;
        background: #fff5f5;
        border: 1px solid rgba(255, 59, 48, 0.12);
        font-size: 13px;
        color: #8a2c27;
        line-height: 1.6;
    }

    .bulk-modal-footer {
        border-top: 1px solid var(--apple-border);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
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
        position: relative;
    }

    .listing-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.08);
    }

    .listing-card.is-selected {
        border-color: rgba(0, 102, 204, 0.35);
        box-shadow: 0 18px 40px rgba(0, 102, 204, 0.12);
    }

    .listing-select-control {
        position: absolute;
        top: 14px;
        left: 14px;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        backdrop-filter: blur(14px);
    }

    .listing-select-control input {
        width: 16px;
        height: 16px;
        accent-color: var(--apple-blue);
    }

    .listing-select-control span {
        font-size: 12px;
        font-weight: 700;
        color: var(--apple-text);
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

    /* Friendly welcome strip */
    .listings-welcome {
        background: var(--apple-card);
        border: 1px solid var(--apple-border);
        border-radius: 16px;
        padding: 16px 20px;
        display: flex;
        gap: 16px;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        margin-bottom: 24px;
        box-shadow: var(--apple-shadow);
    }
    .listings-welcome h2 { margin: 0; font-size: 17px; font-weight: 700; letter-spacing: -0.01em; }
    .listings-welcome-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .listings-welcome-actions a {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 16px; border-radius: 999px;
        font-size: 14px; font-weight: 600; text-decoration: none;
        background: rgba(0,0,0,0.05); color: var(--apple-text);
        transition: var(--transition);
    }
    .listings-welcome-actions a:hover { background: rgba(0,0,0,0.09); }
    .listings-welcome-actions a.primary { background: var(--apple-blue); color: #fff; }
    .listings-welcome-actions a.primary:hover { background: var(--apple-blue-hover); }

    @media (max-width: 768px) {
        .listing-source-tabs-container {
            padding: 0;
            margin-bottom: 20px;
        }
        .listing-source-tab { padding: 8px 10px; font-size: 13px; }
        .listings-welcome {
            padding: 14px 16px;
            gap: 10px;
        }
        .listings-welcome h2 { font-size: 16px; flex: 1; min-width: 0; }
        .listings-welcome-actions a { padding: 8px 14px; font-size: 13px; }
        .filters-header {
            padding: 14px 16px;
        }
        .filters-header h3 { font-size: 16px; }
        .bulk-toolbar {
            padding: 12px;
            gap: 10px;
        }
        .bulk-checkbox-label { font-size: 13px; gap: 8px; }
        .bulk-toolbar-actions { gap: 6px; }
        .bulk-toolbar-actions .btn { padding: 9px 14px; font-size: 13px; }
        .listing-card-img {
            height: 180px;
        }
        .grid-4 {
            grid-template-columns: 1fr;
        }
        .bulk-modal {
            align-items: end;
            padding: 0;
        }
        .bulk-modal-dialog {
            width: 100%;
            max-height: min(88vh, 760px);
            border-radius: 28px 28px 0 0;
        }
        .bulk-modal-header,
        .bulk-modal-body,
        .bulk-modal-footer {
            padding: 20px;
        }
    }
    @media (max-width: 420px) {
        .listings-welcome h2 { font-size: 15px; }
        .listings-welcome-actions a { padding: 7px 12px; font-size: 12px; gap: 4px; }
        .listing-source-tab { padding: 7px 6px; font-size: 12px; gap: 4px; }
        .listing-source-count { font-size: 10px; padding: 0 5px; }
        .bulk-toolbar-actions .btn { padding: 8px 12px; font-size: 12px; }
    }
</style>

<div class="listings-welcome">
    <h2>Your listings</h2>
    <div class="listings-welcome-actions">
        <a href="{{ route('tutorials.show', 'add-listing') }}" title="Open the picture guide">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75"></path><line x1="12" y1="17.25" x2="12.01" y2="17.25"></line></svg>
            Help
        </a>
        <a href="{{ route('listings.create', ['source' => $createSource]) }}" class="primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            New Listing
        </a>
    </div>
</div>

<div class="listing-source-tabs-container">
    <div class="listing-source-tabs" aria-label="Listing sources">
        @foreach($sourceTabs as $tab)
            @php
                $tabQuery = array_filter(
                    array_merge($currentFilters, ['source' => $tab['key']]),
                    static fn ($value) => !is_null($value) && $value !== ''
                );
                $tabHref = route('listings.index', $tabQuery);
            @endphp
            <a
                href="{{ $tabHref }}"
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
            <article class="listing-card" data-listing-card>
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
                            <span class="badge {{ $listingSource === 'icp' ? 'badge-warning' : 'badge-success' }}">
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
                        <a href="{{ route('listings.show', $showParams) }}" class="btn btn-secondary btn-sm" title="View this listing" aria-label="View">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            View
                        </a>
                        <a href="{{ route('listings.edit', $editParams) }}" class="btn btn-secondary btn-sm" title="Edit this listing" aria-label="Edit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            Edit
                        </a>
                        <form method="POST" action="{{ route('listings.destroy', $listing->id) }}" onsubmit="return confirm('Move this listing to Recently Deleted?');" style="margin:0; flex:1;">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="source" value="{{ $listingSource }}">
                            <input type="hidden" name="return_source" value="{{ $activeSource }}">
                            <button type="submit" class="btn btn-danger btn-sm" title="Move to Recently Deleted" aria-label="Delete" style="width:100%;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"></path></svg>
                                Delete
                            </button>
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
