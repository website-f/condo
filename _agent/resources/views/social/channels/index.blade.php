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
        padding: 22px;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }
    .channel-head h3 {
        font-size: clamp(28px, 3vw, 40px);
        line-height: 1.05;
        letter-spacing: -0.04em;
    }
    .channel-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 30px;
        margin-left: 10px;
        padding: 0 12px;
        border-radius: 999px;
        background: #ffe177;
        color: #1d1d1f;
        font-size: 14px;
        font-weight: 700;
    }
    .channel-head-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .channel-search-form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .channel-search-form .form-input {
        min-width: 280px;
    }
    .channel-search-form .form-select {
        min-width: 180px;
    }
    .channel-layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }
    .channel-sidebar {
        overflow: hidden;
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
        <div>
            <h3>Channels <span class="channel-count">{{ $stats['channels'] }}</span></h3>
        </div>

        <div class="channel-head-actions">
            <form method="GET" class="channel-search-form">
                @if($selectedNetwork !== '')
                    <input type="hidden" name="network" value="{{ $selectedNetwork }}">
                @endif

                <input
                    type="text"
                    name="search"
                    class="form-input"
                    value="{{ $search }}"
                    placeholder="Search"
                >

                <select name="filter" class="form-select">
                    <option value="all" @selected($filter === 'all')>All</option>
                    <option value="connected" @selected($filter === 'connected')>Connected</option>
                    <option value="auto_share" @selected($filter === 'auto_share')>Auto-share On</option>
                    <option value="disabled" @selected($filter === 'disabled')>Disabled</option>
                    <option value="archived" @selected($filter === 'archived')>Archived</option>
                </select>

                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>

            <a href="{{ route('social.channels.create') }}" class="btn btn-primary">Add Channel</a>
        </div>
    </section>

    <div class="channel-layout">
        <aside class="channel-sidebar">
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
            @endif
        </section>
    </div>
</div>
@endsection
