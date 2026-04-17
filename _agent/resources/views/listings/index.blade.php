@extends('layouts.app')
@section('title', 'Listings')
@section('page-title', 'Listings')
@section('content')
@php
    $filterKeys = ['search', 'listingtype', 'propertytype', 'state', 'min_price', 'max_price', 'sort', 'dir'];
    $currentFilters = request()->except('page', 'source');
    $createSource = in_array($activeSource, ['ipp', 'icp', 'condo'], true) ? $activeSource : 'ipp';
    $hasFilters = request()->hasAny($filterKeys);
    $bulkTotalCount = method_exists($listings, 'total') ? $listings->total() : $listings->count();
    $bulkStateKey = 'listing-bulk:' . sha1(auth('agent')->user()->username . '|' . http_build_query(array_filter(array_merge($currentFilters, ['source' => $activeSource]), static fn ($value) => $value !== null && $value !== '')));
    $emptyCopy = match ($activeSource) {
        'ipp' => 'No IPP listings found matching your criteria.',
        'icp' => 'No ICP listings found matching your criteria.',
        'condo' => 'No Condo listings found matching your criteria.',
        default => 'No listings found matching your criteria.',
    };
    $condoLocked = !($condoPackageSummary['enabled'] ?? false);
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

    .listings-condo-strip {
        margin: -10px auto 24px;
        width: min(100%, 980px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        padding: 16px 18px;
        border-radius: 18px;
        background: #f2f7ff;
        border: 1px solid rgba(0, 102, 204, 0.12);
        color: var(--apple-text);
    }

    .listings-condo-strip.is-locked {
        background: #fffaf1;
        border-color: #ffe1a1;
    }

    .listings-condo-copy {
        display: grid;
        gap: 4px;
    }

    .listings-condo-copy strong {
        font-size: 14px;
        font-weight: 700;
    }

    .listings-condo-copy span {
        font-size: 13px;
        color: var(--apple-text-muted);
        line-height: 1.6;
    }

    .listings-condo-metrics {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .listings-condo-metric {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(255,255,255,0.72);
        border: 1px solid rgba(0,0,0,0.08);
        font-size: 12px;
        font-weight: 600;
        color: var(--apple-text-muted);
    }

    .listings-condo-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        color: var(--apple-text);
        transition: var(--transition);
    }

    .listings-condo-link:hover {
        background: rgba(255,255,255,0.82);
        transform: translateY(-1px);
    }

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
        .listings-condo-strip {
            margin-top: 0;
            padding: 14px 16px;
        }
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

<div class="listings-condo-strip {{ $condoLocked ? 'is-locked' : '' }}">
    <div class="listings-condo-copy">
        @if($condoLocked)
            <strong>Condo tools are locked on this account.</strong>
            <span>Condo listings, Articles, and Social Media unlock with Condo Premium Package or Condo Premium Lite Package.</span>
        @else
            <strong>{{ $condoPackageSummary['package_name'] }} is active.</strong>
            <span>
                {{ number_format($condoPackageSummary['listing_used']) }} used of {{ number_format($condoPackageSummary['listing_limit']) }} listing spaces.
                Daily credits: {{ number_format($condoPackageSummary['daily_remaining']) }} of {{ number_format($condoPackageSummary['daily_limit']) }} left.
            </span>
            <div class="listings-condo-metrics">
                <span class="listings-condo-metric">Listing Space Left: {{ number_format($condoPackageSummary['listing_remaining']) }}</span>
                <span class="listings-condo-metric">Daily Credit Left: {{ number_format($condoPackageSummary['daily_remaining']) }}</span>
                @if($condoPackageSummary['article_submission_uses_credit'])
                    <span class="listings-condo-metric">Social schedules and article publish use daily credit</span>
                @else
                    <span class="listings-condo-metric">Social schedules use daily credit</span>
                    <span class="listings-condo-metric">Article publishing is unlimited</span>
                @endif
            </div>
        @endif
    </div>

    <a href="{{ route('billing.index') }}" class="listings-condo-link">
        {{ $condoLocked ? 'View Packages' : 'Manage Package' }}
    </a>
</div>

<div class="listing-source-tabs-container">
    <div class="listing-source-tabs" aria-label="Listing sources">
        @foreach($sourceTabs as $tab)
            @php
                $tabQuery = array_filter(
                    array_merge($currentFilters, ['source' => $tab['key']]),
                    static fn ($value) => !is_null($value) && $value !== ''
                );
                $tabLocked = (bool) ($tab['locked'] ?? false);
                $tabHref = $tabLocked ? route('billing.index') : route('listings.index', $tabQuery);
            @endphp
            <a
                href="{{ $tabHref }}"
                class="listing-source-tab {{ $activeSource === $tab['key'] ? 'is-active' : '' }}"
                @if($tabLocked) title="Subscribe to unlock condo listings" @endif
            >
                {{ $tab['label'] }}
                @if($tabLocked)
                    <span class="listing-source-badge">Locked</span>
                @endif
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
    <form method="POST" action="{{ route('listings.bulk') }}" class="bulk-toolbar" id="bulkListingForm">
        @csrf
        <input type="hidden" name="return_source" value="{{ $activeSource }}">
        @foreach($currentFilters as $filterKey => $filterValue)
            @if(is_array($filterValue))
                @foreach($filterValue as $nestedValue)
                    @if($nestedValue !== null && $nestedValue !== '')
                        <input type="hidden" name="return_filters[{{ $filterKey }}][]" value="{{ $nestedValue }}">
                    @endif
                @endforeach
            @elseif($filterValue !== null && $filterValue !== '')
                <input type="hidden" name="return_filters[{{ $filterKey }}]" value="{{ $filterValue }}">
            @endif
        @endforeach
        <input type="hidden" name="selection_mode" id="bulkSelectionMode" value="manual">

        <label class="bulk-checkbox-label" title="Select every listing across all pages">
            <input type="checkbox" id="bulkSelectAll">
            <span>Select all</span>
            <span class="bulk-selection-count" id="bulkSelectedCount">0</span>
        </label>
        <button type="button" class="bulk-clear-button" id="bulkClearSelection" hidden>Clear</button>

        <div class="bulk-toolbar-actions">
            <button type="button" class="btn btn-secondary" id="bulkMigrateButton" disabled aria-label="Move selected to another type">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
                Move
            </button>
            <button type="button" class="btn btn-danger" id="bulkDeleteButton" disabled aria-label="Delete selected">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"></path></svg>
                Delete
            </button>
        </div>
    </form>

    <div class="bulk-modal" id="bulkActionModal" hidden aria-hidden="true">
        <div class="bulk-modal-backdrop" data-bulk-modal-close></div>
        <div class="bulk-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="bulkModalTitle">
            <div class="bulk-modal-shell">
                <div class="bulk-modal-header">
                    <div>
                        <div class="bulk-modal-title" id="bulkModalTitle">Bulk action</div>
                        <div class="bulk-modal-subtitle" id="bulkModalSubtitle">Choose how you want to process the selected listings.</div>
                    </div>
                    <button type="button" class="bulk-modal-close" data-bulk-modal-close aria-label="Close bulk action modal">&times;</button>
                </div>

                <div class="bulk-modal-body">
                    <div class="bulk-modal-block">
                        <div class="bulk-modal-summary" id="bulkModalSummary"></div>

                        <div class="bulk-modal-block" id="bulkTargetBlock" hidden>
                            <div class="bulk-modal-subtitle">Choose where the copied listings should be created.</div>
                            <div class="bulk-target-grid">
                                @foreach($bulkTargetSources as $targetSource)
                                    <label class="bulk-target-option" data-bulk-target-option>
                                        <input type="radio" name="bulk_modal_target_source" value="{{ $targetSource['key'] }}">
                                        <div>
                                            <div class="bulk-target-name">{{ $targetSource['label'] }}</div>
                                            <div class="bulk-target-description">{{ $targetSource['description'] }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="bulk-progress" id="bulkProgressBlock" hidden>
                            <div class="bulk-progress-label" id="bulkProgressLabel">Preparing bulk action...</div>
                            <div class="bulk-progress-bar"><span id="bulkProgressFill"></span></div>
                        </div>

                        <div class="bulk-modal-block" id="bulkResultBlock" hidden>
                            <div class="bulk-modal-subtitle" id="bulkResultSummary"></div>
                            <div class="bulk-result-list" id="bulkResultList"></div>
                        </div>
                    </div>
                </div>

                <div class="bulk-modal-footer">
                    <button type="button" class="btn btn-secondary" id="bulkCancelButton" data-bulk-modal-close>Cancel</button>
                    <button type="button" class="btn btn-primary" id="bulkConfirmButton">Start</button>
                </div>
            </div>
        </div>
    </div>

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
                @if($canManage)
                    <label class="listing-select-control">
                        <input
                            type="checkbox"
                            value="{{ $listingSource }}:{{ $listing->id }}"
                            data-listing-checkbox
                        >
                        <span>Mark</span>
                    </label>
                @endif
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

<script>
    (() => {
        const bulkForm = document.getElementById('bulkListingForm');

        if (!bulkForm) {
            return;
        }

        const selectAll = document.getElementById('bulkSelectAll');
        const checkboxes = Array.from(document.querySelectorAll('[data-listing-checkbox]'));
        const countLabel = document.getElementById('bulkSelectedCount');
        const clearButton = document.getElementById('bulkClearSelection');
        const migrateButton = document.getElementById('bulkMigrateButton');
        const deleteButton = document.getElementById('bulkDeleteButton');
        const selectionModeInput = document.getElementById('bulkSelectionMode');
        const modal = document.getElementById('bulkActionModal');
        const modalTitle = document.getElementById('bulkModalTitle');
        const modalSubtitle = document.getElementById('bulkModalSubtitle');
        const modalSummary = document.getElementById('bulkModalSummary');
        const targetBlock = document.getElementById('bulkTargetBlock');
        const targetOptions = Array.from(document.querySelectorAll('[data-bulk-target-option]'));
        const targetInputs = targetOptions.map((option) => option.querySelector('input[type="radio"]')).filter(Boolean);
        const progressBlock = document.getElementById('bulkProgressBlock');
        const progressLabel = document.getElementById('bulkProgressLabel');
        const progressFill = document.getElementById('bulkProgressFill');
        const resultBlock = document.getElementById('bulkResultBlock');
        const resultSummary = document.getElementById('bulkResultSummary');
        const resultList = document.getElementById('bulkResultList');
        const confirmButton = document.getElementById('bulkConfirmButton');
        const cancelButton = document.getElementById('bulkCancelButton');
        const closeTriggers = Array.from(document.querySelectorAll('[data-bulk-modal-close]'));
        const storageKey = @json($bulkStateKey);
        const totalResults = {{ (int) $bulkTotalCount }};
        const csrfToken = @json(csrf_token());

        let activeAction = null;
        let progressTimer = null;
        let isProcessing = false;

        const uniqueTokens = (tokens) => Array.from(new Set((tokens || []).filter((value) => typeof value === 'string' && value !== '')));
        const loadState = () => {
            try {
                const parsed = JSON.parse(window.localStorage.getItem(storageKey) || '{}');

                return {
                    mode: parsed.mode === 'all_filtered' ? 'all_filtered' : 'manual',
                    selected: uniqueTokens(parsed.selected),
                    excluded: uniqueTokens(parsed.excluded),
                };
            } catch (error) {
                return { mode: 'manual', selected: [], excluded: [] };
            }
        };

        let state = loadState();

        const saveState = () => {
            window.localStorage.setItem(storageKey, JSON.stringify(state));
            selectionModeInput.value = state.mode;
        };

        const clearState = () => {
            state = { mode: 'manual', selected: [], excluded: [] };
            saveState();
            applyCheckboxState();
            updateToolbar();
        };

        const syncCardState = (checkbox) => {
            const card = checkbox.closest('[data-listing-card]');

            if (card) {
                card.classList.toggle('is-selected', checkbox.checked);
            }
        };

        const selectedCount = () => {
            if (state.mode === 'all_filtered') {
                return Math.max(0, totalResults - state.excluded.length);
            }

            return state.selected.length;
        };

        const tokenSelected = (token) => {
            if (state.mode === 'all_filtered') {
                return !state.excluded.includes(token);
            }

            return state.selected.includes(token);
        };

        const applyCheckboxState = () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = tokenSelected(checkbox.value);
                syncCardState(checkbox);
            });
        };

        const syncTargetCards = () => {
            targetOptions.forEach((option) => {
                const input = option.querySelector('input[type="radio"]');
                option.classList.toggle('is-selected', Boolean(input && input.checked));
            });
        };

        const updateToolbar = () => {
            const count = selectedCount();
            countLabel.textContent = count;

            if (state.mode === 'all_filtered') {
                const everythingMarked = state.excluded.length === 0 && totalResults > 0;
                selectAll.checked = everythingMarked;
                selectAll.indeterminate = !everythingMarked && count > 0;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = count > 0;
            }

            bulkForm.classList.toggle('has-selection', count > 0);
            if (clearButton) clearButton.hidden = count === 0;
            migrateButton.disabled = count === 0;
            deleteButton.disabled = count === 0;
        };

        const resetModal = () => {
            activeAction = null;
            isProcessing = false;
            targetInputs.forEach((input) => {
                input.checked = false;
            });
            syncTargetCards();
            targetBlock.hidden = true;
            progressBlock.hidden = true;
            resultBlock.hidden = true;
            resultList.innerHTML = '';
            progressFill.style.width = '0%';
            modalSummary.innerHTML = '';
            resultSummary.textContent = '';
            confirmButton.disabled = false;
            cancelButton.disabled = false;
            confirmButton.textContent = 'Start';
            cancelButton.textContent = 'Cancel';
            closeTriggers.forEach((trigger) => {
                trigger.disabled = false;
            });
            if (progressTimer) {
                window.clearInterval(progressTimer);
                progressTimer = null;
            }
        };

        const openModal = (action) => {
            const count = selectedCount();

            if (count === 0) {
                return;
            }

            resetModal();
            activeAction = action;
            targetBlock.hidden = action !== 'migrate';
            modalTitle.textContent = action === 'delete' ? 'Delete listings?' : 'Move listings to another type?';
            modalSubtitle.textContent = action === 'delete'
                ? 'They will go to Recently Deleted &mdash; you can restore them later.'
                : 'A copy will be made in the new type. The originals stay where they are.';
            modalSummary.innerHTML = action === 'delete'
                ? `You are about to delete <strong>${count}</strong> listing${count === 1 ? '' : 's'}.`
                : `You are about to move <strong>${count}</strong> listing${count === 1 ? '' : 's'}.`;
            confirmButton.textContent = action === 'delete' ? 'Yes, delete' : 'Yes, move';
            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        const closeModal = () => {
            if (isProcessing) {
                return;
            }

            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            resetModal();
        };

        const startProgress = (label) => {
            let progress = 12;
            progressLabel.textContent = label;
            progressBlock.hidden = false;
            progressFill.style.width = `${progress}%`;

            progressTimer = window.setInterval(() => {
                if (progress >= 88) {
                    return;
                }

                progress += progress < 48 ? 9 : 4;
                progressFill.style.width = `${Math.min(progress, 88)}%`;
            }, 170);
        };

        const stopProgress = (label, finalValue = 100) => {
            if (progressTimer) {
                window.clearInterval(progressTimer);
                progressTimer = null;
            }

            progressFill.style.width = `${finalValue}%`;
            progressLabel.textContent = label;
        };

        const buildFormData = () => {
            const formData = new FormData(bulkForm);
            formData.set('selection_mode', state.mode);

            state.selected.forEach((token) => {
                formData.append('selected[]', token);
            });

            state.excluded.forEach((token) => {
                formData.append('excluded[]', token);
            });

            return formData;
        };

        const runBulkAction = async () => {
            if (!activeAction) {
                return;
            }

            const count = selectedCount();

            if (count === 0) {
                closeModal();
                return;
            }

            const formData = buildFormData();
            formData.set('action', activeAction);

            let actionLabel = 'Deleting selected listings...';

            if (activeAction === 'migrate') {
                const selectedTarget = targetInputs.find((input) => input.checked);

                if (!selectedTarget) {
                    modalSummary.innerHTML = '<strong>Please choose where the selected listings should be migrated.</strong>';
                    return;
                }

                formData.set('target_source', selectedTarget.value);
                actionLabel = `Migrating ${count} listing${count === 1 ? '' : 's'} into ${selectedTarget.value.toUpperCase()}...`;
            }

            isProcessing = true;
            confirmButton.disabled = true;
            cancelButton.disabled = true;
            closeTriggers.forEach((trigger) => {
                trigger.disabled = true;
            });
            startProgress(actionLabel);

            try {
                const response = await window.fetch(bulkForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                    credentials: 'same-origin',
                });

                const payload = await response.json();

                if (!response.ok) {
                    const errorMessages = payload.errors
                        ? Object.values(payload.errors).flat()
                        : [payload.message || 'The bulk action could not be completed.'];
                    throw new Error(errorMessages.join(' '));
                }

                stopProgress(payload.message || 'Bulk action complete.');
                resultBlock.hidden = false;
                resultSummary.textContent = payload.message || 'Bulk action complete.';
                resultList.innerHTML = '';

                if (Array.isArray(payload.skipped) && payload.skipped.length > 0) {
                    payload.skipped.forEach((message) => {
                        const item = document.createElement('div');
                        item.className = 'bulk-result-item';
                        item.textContent = message;
                        resultList.appendChild(item);
                    });
                } else {
                    const item = document.createElement('div');
                    item.className = 'bulk-result-item';
                    item.style.background = '#eefbf3';
                    item.style.borderColor = 'rgba(24, 121, 78, 0.14)';
                    item.style.color = '#18794e';
                    item.textContent = `${payload.completed} listing${payload.completed === 1 ? '' : 's'} processed successfully.`;
                    resultList.appendChild(item);
                }

                clearState();
                const shouldRefresh = !Array.isArray(payload.skipped) || payload.skipped.length === 0;
                confirmButton.textContent = 'Refresh listings';
                confirmButton.disabled = false;
                cancelButton.textContent = 'Close';
                cancelButton.disabled = false;
                closeTriggers.forEach((trigger) => {
                    trigger.disabled = false;
                });
                isProcessing = false;
                activeAction = null;

                if (shouldRefresh) {
                    resultSummary.textContent = `${payload.message || 'Bulk action complete.'} Refreshing listings...`;
                    window.setTimeout(() => {
                        window.location.reload();
                    }, 1100);
                }
            } catch (error) {
                stopProgress('Bulk action could not finish.', 100);
                resultBlock.hidden = false;
                resultSummary.textContent = error instanceof Error ? error.message : 'The bulk action could not be completed.';
                resultList.innerHTML = '';
                confirmButton.disabled = false;
                cancelButton.disabled = false;
                closeTriggers.forEach((trigger) => {
                    trigger.disabled = false;
                });
                isProcessing = false;
            }
        };

        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const token = checkbox.value;

                if (state.mode === 'all_filtered') {
                    state.excluded = checkbox.checked
                        ? state.excluded.filter((value) => value !== token)
                        : uniqueTokens([...state.excluded, token]);
                } else {
                    state.selected = checkbox.checked
                        ? uniqueTokens([...state.selected, token])
                        : state.selected.filter((value) => value !== token);
                }

                saveState();
                updateToolbar();
                applyCheckboxState();
            });
        });

        selectAll.addEventListener('change', () => {
            if (selectAll.checked) {
                state = { mode: 'all_filtered', selected: [], excluded: [] };
            } else {
                state = { mode: 'manual', selected: [], excluded: [] };
            }

            saveState();
            applyCheckboxState();
            updateToolbar();
        });

        clearButton.addEventListener('click', clearState);
        migrateButton.addEventListener('click', () => openModal('migrate'));
        deleteButton.addEventListener('click', () => openModal('delete'));

        targetInputs.forEach((input) => {
            input.addEventListener('change', syncTargetCards);
        });

        closeTriggers.forEach((trigger) => {
            trigger.addEventListener('click', closeModal);
        });

        confirmButton.addEventListener('click', () => {
            if (activeAction === null && !isProcessing && !modal.hidden) {
                window.location.reload();
                return;
            }

            runBulkAction();
        });
        cancelButton.addEventListener('click', closeModal);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });

        saveState();
        applyCheckboxState();
        updateToolbar();
    })();
</script>

@endsection
