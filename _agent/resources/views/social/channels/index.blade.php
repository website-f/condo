@extends('layouts.app')

@section('title', 'Channels')
@section('page-title', 'Social Media')

@section('topbar-actions')
    <div class="btn-group">
        <a href="{{ route('social.index') }}" class="btn btn-secondary btn-sm">Calendar</a>
        <a href="{{ route('social.create') }}" class="btn btn-primary btn-sm">Schedule New Post</a>
    </div>
@endsection

@section('content')
@include('social.partials.tabs', ['socialActiveTab' => 'channels'])

@php
    $allUrl = route('social.channels.index', array_filter([
        'search' => $search !== '' ? $search : null,
        'filter' => $filter !== 'all' ? $filter : null,
    ]));
@endphp

<style>
    .content {
        max-width: none;
        padding: 28px 28px 44px;
    }
    .channel-page {
        display: grid;
        gap: 18px;
    }
    .channel-card,
    .channel-sidebar,
    .channel-table-card {
        background: var(--card-bg);
        border: 1px solid var(--border-light);
        border-radius: 22px;
        box-shadow: var(--shadow-sm);
    }
    .channel-head {
        padding: 20px 22px;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }
    .channel-head-title {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }
    .channel-head h3 {
        font-size: clamp(22px, 2.2vw, 28px);
        line-height: 1.1;
        letter-spacing: -0.03em;
        margin: 0;
    }
    .channel-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 26px;
        padding: 0 10px;
        border-radius: 999px;
        background: #ffe177;
        color: #1d1d1f;
        font-size: 13px;
        font-weight: 700;
    }
    .channel-head-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
        flex: 1;
        justify-content: flex-end;
    }
    .channel-search-form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .channel-search-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .channel-search-wrap svg {
        position: absolute;
        left: 12px;
        width: 16px;
        height: 16px;
        color: var(--text-secondary);
        pointer-events: none;
    }
    .channel-search-form .form-input {
        min-width: 260px;
        padding-left: 36px;
    }
    .channel-search-form .form-select {
        min-width: 170px;
    }
    .channel-filter-clear {
        color: var(--text-secondary);
        font-size: 13px;
        text-decoration: underline;
        white-space: nowrap;
    }
    .channel-filter-clear:hover {
        color: var(--text);
    }
    .channel-layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 18px;
        align-items: start;
        transition: grid-template-columns 0.25s ease;
    }
    .channel-layout.sidebar-collapsed {
        grid-template-columns: 64px minmax(0, 1fr);
    }
    .channel-sidebar {
        overflow: hidden;
    }
    .channel-sidebar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-light);
        background: #fafbfd;
    }
    .channel-sidebar-title {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-secondary);
    }
    .channel-sidebar-toggle {
        background: #fff;
        border: 1px solid var(--border-light);
        border-radius: 10px;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--text-secondary);
        transition: background 0.15s ease, color 0.15s ease;
        flex-shrink: 0;
    }
    .channel-sidebar-toggle:hover {
        background: #f2f4f8;
        color: var(--text);
    }
    .channel-sidebar-toggle svg {
        width: 14px;
        height: 14px;
        transition: transform 0.25s ease;
    }
    .channel-layout.sidebar-collapsed .channel-sidebar-toggle svg {
        transform: rotate(180deg);
    }
    .channel-layout.sidebar-collapsed .channel-sidebar-title,
    .channel-layout.sidebar-collapsed .channel-sidebar-label,
    .channel-layout.sidebar-collapsed .channel-sidebar-value {
        display: none;
    }
    .channel-layout.sidebar-collapsed .channel-sidebar-header {
        justify-content: center;
        padding: 12px 8px;
    }
    .channel-layout.sidebar-collapsed .channel-sidebar-link {
        justify-content: center;
        padding: 14px 8px;
    }
    .channel-layout.sidebar-collapsed .channel-sidebar-left {
        justify-content: center;
    }
    .channel-sidebar-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        color: var(--text);
        text-decoration: none;
        border-bottom: 1px solid var(--border-light);
        background: #fff;
        transition: background 0.2s ease;
    }
    .channel-sidebar-link:last-child {
        border-bottom: none;
    }
    .channel-sidebar-link:hover {
        background: #f8fafc;
    }
    .channel-sidebar-link.active {
        background: #eef6ff;
        box-shadow: inset 0 0 0 1px rgba(93, 135, 255, 0.18);
    }
    .channel-sidebar-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }
    .channel-sidebar-icon {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        border: 1px solid var(--border-light);
        background: #f4f5f8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        flex-shrink: 0;
    }
    .channel-sidebar-label {
        font-size: 16px;
        font-weight: 600;
        line-height: 1.2;
    }
    .channel-sidebar-value {
        min-width: 32px;
        height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        background: #edf7ee;
        color: #0a7a33;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
    }
    .channel-table-card {
        overflow: hidden;
    }
    .channel-table-head {
        padding: 18px 22px;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        border-bottom: 1px solid var(--border-light);
        background: linear-gradient(180deg, #fff 0%, #fafbfd 100%);
    }
    .channel-table-head h4 {
        font-size: 18px;
        line-height: 1.15;
        letter-spacing: -0.02em;
    }
    .channel-table-meta {
        color: var(--text-secondary);
        font-size: 13px;
    }
    .channel-table {
        width: 100%;
        border-collapse: collapse;
    }
    .channel-table th {
        padding: 14px 22px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-secondary);
        background: #fff;
        border-bottom: 1px solid var(--border-light);
    }
    .channel-table td {
        padding: 18px 22px;
        border-bottom: 1px solid var(--border-light);
        vertical-align: middle;
        background: #fff;
    }
    .channel-table tr:last-child td {
        border-bottom: none;
    }
    .channel-table tr:hover td {
        background: #fcfcff;
    }
    .channel-row-main {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }
    .channel-row-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        overflow: hidden;
        border: 1px solid var(--border-light);
        background: #f2f4f8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #3867d6;
        flex-shrink: 0;
    }
    .channel-row-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .channel-row-name {
        font-size: 16px;
        font-weight: 600;
        line-height: 1.2;
        color: var(--text);
    }
    .channel-row-meta {
        margin-top: 4px;
        color: var(--text-secondary);
        font-size: 13px;
        line-height: 1.45;
    }
    .channel-network-chip {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        margin-right: 8px;
        border-radius: 999px;
        background: #f4f5f8;
        border: 1px solid var(--border-light);
        color: var(--text-secondary);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .channel-auto {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .channel-auto::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
    }
    .channel-auto.on {
        background: #ebfaf0;
        color: #118847;
    }
    .channel-auto.off {
        background: #f3f4f8;
        color: #6b7280;
    }
    .channel-auto.archived {
        background: #fff0ef;
        color: #d14343;
    }
    .channel-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .channel-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 22px;
        border-top: 1px solid var(--border-light);
        background: #fafbfd;
        flex-wrap: wrap;
    }
    .channel-pagination-info {
        font-size: 13px;
        color: var(--text-secondary);
    }
    .channel-pagination-links {
        display: inline-flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .channel-pagination-links a,
    .channel-pagination-links span {
        min-width: 34px;
        height: 34px;
        padding: 0 10px;
        border-radius: 10px;
        border: 1px solid var(--border-light);
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        text-decoration: none;
    }
    .channel-pagination-links a:hover {
        background: #f2f4f8;
    }
    .channel-pagination-links .is-current {
        background: #1d1d1f;
        color: #fff;
        border-color: #1d1d1f;
    }
    .channel-pagination-links .is-disabled {
        color: #c1c4cc;
        background: #fff;
        cursor: not-allowed;
    }
    .channel-empty {
        padding: 54px 20px;
        text-align: center;
        color: var(--text-secondary);
    }
    .channel-empty p {
        font-size: 15px;
        margin-bottom: 16px;
        font-weight: 500;
    }
    .channel-mini-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .channel-mini-stat {
        min-width: 120px;
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid var(--border-light);
        background: #fff;
    }
    .channel-mini-stat span {
        display: block;
        color: var(--text-secondary);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .channel-mini-stat strong {
        font-size: 24px;
        line-height: 1;
        letter-spacing: -0.04em;
    }
    @media (max-width: 1100px) {
        .channel-layout {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 900px) {
        .channel-search-form .form-input,
        .channel-search-form .form-select {
            min-width: 0;
            width: 100%;
        }
        .channel-search-form {
            width: 100%;
        }
        .channel-search-form .btn {
            width: 100%;
        }
    }
    @media (max-width: 760px) {
        .content {
            padding: 22px 16px 34px;
        }
        .channel-table thead {
            display: none;
        }
        .channel-table,
        .channel-table tbody,
        .channel-table tr,
        .channel-table td {
            display: block;
            width: 100%;
        }
        .channel-table tr {
            border-bottom: 1px solid var(--border-light);
        }
        .channel-table td {
            padding: 14px 18px;
        }
        .channel-table td:last-child {
            padding-top: 6px;
        }
        .channel-actions {
            justify-content: flex-start;
        }
        .channel-mini-stats {
            width: 100%;
        }
        .channel-mini-stat {
            flex: 1 1 140px;
        }
    }
</style>

<div class="channel-page">
    <section class="channel-card channel-head">
        <div class="channel-head-title">
            <h3>Channels</h3>
            <span class="channel-count">{{ $stats['channels'] }}</span>
        </div>

        <div class="channel-head-actions">
            <form method="GET" class="channel-search-form" id="channelFilterForm">
                @if($selectedNetwork !== '')
                    <input type="hidden" name="network" value="{{ $selectedNetwork }}">
                @endif

                <label class="channel-search-wrap" aria-label="Search channels">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input
                        type="text"
                        name="search"
                        class="form-input"
                        value="{{ $search }}"
                        placeholder="Search channels…"
                    >
                </label>

                <select name="filter" class="form-select" onchange="document.getElementById('channelFilterForm').submit();">
                    <option value="all" @selected($filter === 'all')>Show: All</option>
                    <option value="connected" @selected($filter === 'connected')>Show: Connected</option>
                    <option value="auto_share" @selected($filter === 'auto_share')>Show: Auto-share On</option>
                    <option value="disabled" @selected($filter === 'disabled')>Show: Disabled</option>
                    <option value="archived" @selected($filter === 'archived')>Show: Archived</option>
                </select>

                @if($search !== '' || $filter !== 'all')
                    <a href="{{ route('social.channels.index', array_filter(['network' => $selectedNetwork !== '' ? $selectedNetwork : null])) }}" class="channel-filter-clear">Clear</a>
                @endif
            </form>

            <a href="{{ route('social.channels.create') }}" class="btn btn-primary">+ Add Channel</a>
        </div>
    </section>

    <div class="channel-layout" id="channelLayout">
        <aside class="channel-sidebar">
            <div class="channel-sidebar-header">
                <span class="channel-sidebar-title">Networks</span>
                <button type="button" class="channel-sidebar-toggle" id="channelSidebarToggle" aria-label="Toggle sidebar" title="Collapse sidebar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
            </div>
            <a href="{{ $allUrl }}" class="channel-sidebar-link {{ $selectedNetwork === '' ? 'active' : '' }}">
                <div class="channel-sidebar-left">
                    <div class="channel-sidebar-icon">All</div>
                    <div class="channel-sidebar-label">All</div>
                </div>
                <div class="channel-sidebar-value">{{ $allStats['channels'] > 0 ? $allStats['channels'] : '-' }}</div>
            </a>

            @foreach($networkCards as $network)
                @php
                    $networkUrl = route('social.channels.index', array_filter([
                        'network' => $network['key'],
                        'search' => $search !== '' ? $search : null,
                        'filter' => $filter !== 'all' ? $filter : null,
                    ]));
                @endphp
                <a href="{{ $networkUrl }}" class="channel-sidebar-link {{ $selectedNetwork === $network['key'] ? 'active' : '' }}">
                    <div class="channel-sidebar-left">
                        <div class="channel-sidebar-icon">{{ strtoupper(substr($network['label'], 0, 1)) }}</div>
                        <div class="channel-sidebar-label">{{ $network['label'] }}</div>
                    </div>
                    <div class="channel-sidebar-value">{{ $network['channels'] > 0 ? $network['channels'] : '-' }}</div>
                </a>
            @endforeach
        </aside>

        <section class="channel-table-card">
            <div class="channel-table-head">
                <div>
                    <h4>{{ $selectedNetworkLabel }} Channels</h4>
                    <div class="channel-table-meta">{{ $stats['active'] }} active, {{ $stats['archived'] }} archived</div>
                </div>

                <div class="channel-mini-stats">
                    <div class="channel-mini-stat">
                        <span>Accounts</span>
                        <strong>{{ $stats['accounts'] }}</strong>
                    </div>
                    <div class="channel-mini-stat">
                        <span>Live</span>
                        <strong>{{ $stats['active'] }}</strong>
                    </div>
                </div>
            </div>

            @if($channels->isEmpty())
                <div class="channel-empty">
                    <p>No channels found in this view.</p>
                    <a href="{{ route('social.channels.create', array_filter(['network' => $selectedNetwork !== '' ? $selectedNetwork : null])) }}" class="btn btn-primary">Add Channel</a>
                </div>
            @else
                <div class="table-wrap" style="margin:0;padding:0;">
                    <table class="channel-table">
                        <thead>
                            <tr>
                                <th>Channel</th>
                                <th>Auto-share</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($channels as $channel)
                                <tr>
                                    <td>
                                        <div class="channel-row-main">
                                            <div class="channel-row-avatar">
                                                @if($channel['picture'] !== '')
                                                    <img src="{{ $channel['picture'] }}" alt="{{ $channel['name'] }}">
                                                @else
                                                    {{ strtoupper(substr($channel['name'], 0, 1)) }}
                                                @endif
                                            </div>
                                            <div>
                                                <div class="channel-row-name">{{ $channel['name'] }}</div>
                                                <div class="channel-row-meta">
                                                    <span class="channel-network-chip">{{ $channel['social_network_label'] }}</span>
                                                    {{ $channel['session_name'] }} - {{ ucwords(str_replace('_', ' ', $channel['channel_type'])) }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($channel['is_deleted'])
                                            <span class="channel-auto archived">Archived</span>
                                        @elseif($channel['auto_share'])
                                            <span class="channel-auto on">On</span>
                                        @else
                                            <span class="channel-auto off">Off</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="channel-actions">
                                            @if(! $channel['is_deleted'])
                                                <form action="{{ route('social.channels.refresh', $channel['id']) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary btn-sm">Refresh</button>
                                                </form>
                                            @endif
                                            @if(! $channel['is_deleted'] && in_array($channel['social_network'], $popupAuthNetworks, true))
                                                <a href="{{ route('social.channels.create', ['network' => $channel['social_network'], 'session' => $channel['channel_session_id']]) }}" class="btn btn-secondary btn-sm">Reconnect</a>
                                            @endif
                                            <a href="{{ route('social.accounts.edit', $channel['channel_session_id']) }}" class="btn btn-secondary btn-sm">Account</a>
                                            <a href="{{ route('social.channels.edit', $channel['id']) }}" class="btn btn-secondary btn-sm">Edit</a>
                                            <form action="{{ route('social.channels.destroy', $channel['id']) }}" method="POST" onsubmit="return confirm('Delete this channel?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($channelsPaginator->lastPage() > 1)
                    @php
                        $from = $channelsPaginator->firstItem();
                        $to = $channelsPaginator->lastItem();
                        $total = $channelsPaginator->total();
                        $current = $channelsPaginator->currentPage();
                        $last = $channelsPaginator->lastPage();
                        $window = 2;
                        $pageStart = max(1, $current - $window);
                        $pageEnd = min($last, $current + $window);
                    @endphp
                    <div class="channel-pagination">
                        <div class="channel-pagination-info">
                            Showing {{ $from }}–{{ $to }} of {{ $total }} channels
                        </div>
                        <div class="channel-pagination-links">
                            @if($channelsPaginator->onFirstPage())
                                <span class="is-disabled">Prev</span>
                            @else
                                <a href="{{ $channelsPaginator->previousPageUrl() }}" rel="prev">Prev</a>
                            @endif

                            @if($pageStart > 1)
                                <a href="{{ $channelsPaginator->url(1) }}">1</a>
                                @if($pageStart > 2)
                                    <span class="is-disabled">…</span>
                                @endif
                            @endif

                            @for($p = $pageStart; $p <= $pageEnd; $p++)
                                @if($p === $current)
                                    <span class="is-current">{{ $p }}</span>
                                @else
                                    <a href="{{ $channelsPaginator->url($p) }}">{{ $p }}</a>
                                @endif
                            @endfor

                            @if($pageEnd < $last)
                                @if($pageEnd < $last - 1)
                                    <span class="is-disabled">…</span>
                                @endif
                                <a href="{{ $channelsPaginator->url($last) }}">{{ $last }}</a>
                            @endif

                            @if($channelsPaginator->hasMorePages())
                                <a href="{{ $channelsPaginator->nextPageUrl() }}" rel="next">Next</a>
                            @else
                                <span class="is-disabled">Next</span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </section>
    </div>
</div>

<script>
    (function () {
        var layout = document.getElementById('channelLayout');
        var toggle = document.getElementById('channelSidebarToggle');
        if (!layout || !toggle) return;

        var storageKey = 'socialChannelsSidebarCollapsed';
        try {
            if (localStorage.getItem(storageKey) === '1') {
                layout.classList.add('sidebar-collapsed');
            }
        } catch (e) {}

        toggle.addEventListener('click', function () {
            layout.classList.toggle('sidebar-collapsed');
            var collapsed = layout.classList.contains('sidebar-collapsed');
            toggle.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            try {
                localStorage.setItem(storageKey, collapsed ? '1' : '0');
            } catch (e) {}
        });
    })();
</script>
@endsection
