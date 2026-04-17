@extends('layouts.app')

@section('title', 'Social Media')
@section('page-title', 'Social Media')

@section('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css">
@endsection

@section('content')
@include('social.partials.tabs', ['socialActiveTab' => 'calendar'])

@php
    $calendarQuery = array_filter([
        'view' => 'calendar',
        'search' => $search !== '' ? $search : null,
        'status' => $statusFilter !== '' ? $statusFilter : null,
        'network' => $networkFilter !== '' ? $networkFilter : null,
    ]);
    $tableQuery = array_filter([
        'view' => 'table',
        'search' => $search !== '' ? $search : null,
        'status' => $statusFilter !== '' ? $statusFilter : null,
        'network' => $networkFilter !== '' ? $networkFilter : null,
    ]);
    $tableViewUrl = route('social.index', $tableQuery);
@endphp

<style>
    .content { max-width: none; padding: 24px 24px 40px; }
    .social-page { display: grid; gap: 16px; }

    /* Welcome strip */
    .social-welcome {
        background: var(--card-bg);
        border: 1px solid var(--border-light);
        border-radius: 16px;
        padding: 14px 18px;
        display: flex; gap: 14px; align-items: center; justify-content: space-between; flex-wrap: wrap;
        box-shadow: var(--shadow-sm);
    }
    .social-welcome h2 { margin: 0; font-size: 17px; font-weight: 700; }
    .social-welcome-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .social-welcome-actions a {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 16px; border-radius: 999px;
        font-size: 14px; font-weight: 600; text-decoration: none;
        background: rgba(0,0,0,0.05); color: var(--text); transition: all .2s ease;
    }
    .social-welcome-actions a:hover { background: rgba(0,0,0,0.09); }
    .social-welcome-actions a.primary { background: #ff5d91; color: #fff; }
    .social-welcome-actions a.primary:hover { background: #ff4480; }

    /* Stats */
    .social-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .social-stat {
        background: var(--card-bg); border: 1px solid var(--border-light);
        border-radius: 14px; padding: 14px 16px; box-shadow: var(--shadow-sm);
    }
    .social-stat span { display: block; color: var(--text-secondary); font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 6px; }
    .social-stat strong { display: block; font-size: 26px; line-height: 1; letter-spacing: -0.04em; }

    /* Controls */
    .social-controls {
        background: var(--card-bg); border: 1px solid var(--border-light);
        border-radius: 16px; padding: 14px 16px; box-shadow: var(--shadow-sm);
        display: grid; gap: 12px;
    }
    .social-controls-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .social-toggle { display: inline-flex; gap: 4px; padding: 4px; background: rgba(120,120,128,0.12); border-radius: 999px; }
    .social-toggle a {
        min-width: 80px; padding: 6px 14px; border-radius: 999px;
        display: inline-flex; align-items: center; justify-content: center;
        text-decoration: none; font-size: 13px; font-weight: 600; color: var(--text);
    }
    .social-toggle a.active { background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
    .social-filter-form {
        display: grid; grid-template-columns: minmax(0,1.4fr) minmax(120px,.8fr) minmax(120px,.8fr) auto; gap: 8px; align-items: center;
    }
    .social-filter-form .form-input,
    .social-filter-form .form-select { padding: 9px 12px; font-size: 14px; border-radius: 10px; background: var(--accent-light); border: 1px solid var(--border-light); }
    .social-filter-form .btn { padding: 9px 14px; font-size: 13px; }
    .social-filter-actions { display: flex; gap: 6px; }

    /* Board */
    .social-board {
        background: var(--card-bg); border: 1px solid var(--border-light);
        border-radius: 16px; padding: 16px; box-shadow: var(--shadow-sm);
    }
    .social-board-head { display: flex; justify-content: space-between; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 14px; }
    .social-board-head h4 { font-size: 16px; letter-spacing: -0.01em; margin: 0; }
    .social-board-meta { font-size: 13px; color: var(--text-secondary); }

    .social-empty { padding: 50px 20px; text-align: center; color: var(--text-secondary); border: 1px dashed var(--border); border-radius: 14px; background: #fafbfc; }
    .social-empty p { margin-bottom: 14px; font-size: 14px; font-weight: 500; }

    /* Calendar */
    .social-cal .fc { font-family: inherit; }
    .social-cal .fc .fc-toolbar { margin-bottom: 14px; gap: 8px; flex-wrap: wrap; }
    .social-cal .fc .fc-toolbar-title { font-size: clamp(16px,2.2vw,22px); font-weight: 700; letter-spacing: -0.02em; }
    .social-cal .fc .fc-button { border-radius: 10px!important; padding: 7px 12px!important; background: #fff!important; border-color: var(--border-light)!important; color: var(--text)!important; font-weight: 600!important; font-size: 13px!important; box-shadow: none!important; }
    .social-cal .fc .fc-button-primary:not(:disabled).fc-button-active,
    .social-cal .fc .fc-button-primary:not(:disabled):active { background: #ff5d91!important; border-color: #ff5d91!important; color: #fff!important; }
    .social-cal .fc-theme-standard td,
    .social-cal .fc-theme-standard th,
    .social-cal .fc-theme-standard .fc-scrollgrid { border-color: var(--border-light); }
    .social-cal .fc .fc-day-today { background: rgba(255,93,145,0.06)!important; }
    .social-cal .fc .fc-daygrid-day-frame { min-height: 100px; }
    .social-cal .fc .fc-daygrid-day-events { display: grid; gap: 3px; margin: 3px 3px 0; overflow: hidden; }
    .social-cal .fc .fc-daygrid-event-harness { overflow: hidden; min-width: 0; }
    .social-cal .fc .fc-event { background: transparent!important; border: none!important; margin: 0!important; cursor: pointer; overflow: hidden; min-width: 0; max-width: 100%; }
    .social-cal .fc .fc-daygrid-day-number { font-size: 13px; padding: 4px 6px; }
    .social-cal .fc .fc-col-header-cell-cushion { font-size: 11px; padding: 8px 4px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-secondary); font-weight: 700; }
    .social-cal .fc .fc-daygrid-more-link { font-size: 11px; font-weight: 700; color: #ff5d91; padding: 2px 6px; }

    /* List view (FullCalendar) */
    .social-cal .fc .fc-list { border: 1px solid var(--border-light); border-radius: 12px; overflow: hidden; }
    .social-cal .fc-list-event:hover td { background: #f5f5f7!important; cursor: pointer; }
    .social-cal .fc-list-day-cushion { background: #fafbfc!important; padding: 8px 12px!important; font-weight: 700; font-size: 13px; }
    .social-cal .fc-list-event-time { font-weight: 600; color: var(--text-secondary); font-size: 13px; }
    .social-cal .fc-list-event-title { font-size: 14px; }
    .social-cal .fc .fc-list-empty-cushion { padding: 40px 20px; font-size: 14px; color: var(--text-secondary); }

    /* Event chip */
    .social-chip {
        display: flex; flex-direction: column; gap: 3px;
        padding: 4px 6px; border-radius: 8px; border: 1px solid;
        background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.04); overflow: hidden;
        min-width: 0; max-width: 100%; box-sizing: border-box;
    }
    .social-chip.schedule-status-scheduled { border-color: #d7dce5; }
    .social-chip.schedule-status-success { border-color: #8ad5a1; background: #f3fbf5; }
    .social-chip.schedule-status-error { border-color: #f5b3ae; background: #fff5f4; }
    .social-chip.schedule-status-mixed { border-color: #d9c3ff; background: #faf6ff; }
    .social-chip.schedule-status-sending { border-color: #bfd6ff; background: #f5f9ff; }
    .social-chip-title { font-size: 11px; font-weight: 700; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text); }
    .social-chip-foot { display: flex; justify-content: space-between; gap: 4px; align-items: center; }
    .social-chip-time { font-size: 10px; font-weight: 700; color: var(--text-secondary); white-space: nowrap; }
    .social-chip-nets { display: flex; align-items: center; }
    .social-chip-net { width: 14px; height: 14px; margin-left: -3px; border-radius: 50%; border: 1px solid #e2e8f0; background: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 7px; font-weight: 800; color: var(--text); }
    .social-chip-net:first-child { margin-left: 0; }

    /* Legend */
    .social-legend { display: flex; gap: 6px; flex-wrap: wrap; }
    .social-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; border: 1px solid var(--border-light); font-size: 11px; font-weight: 700; background: #fff; color: var(--text-secondary); }
    .social-pill:before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .social-pill.status-scheduled { color: #1d1d1f; }
    .social-pill.status-success { color: #14833b; }
    .social-pill.status-error { color: #d92d20; }
    .social-pill.status-mixed { color: #7c3aed; }

    /* Table — compact rows */
    .social-table { width: 100%; border-collapse: collapse; }
    .social-table th { padding: 10px 14px; background: #fafbfc; border-bottom: 1px solid var(--border-light); font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-secondary); text-align: left; }
    .social-table td { padding: 10px 14px; border-bottom: 1px solid var(--border-light); vertical-align: middle; }
    .social-table tbody tr { cursor: pointer; transition: background .15s ease; }
    .social-table tbody tr:hover { background: #f8f9fb; }
    .social-status { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; border: 1px solid var(--border-light); font-size: 11px; font-weight: 700; white-space: nowrap; }
    .social-status.status-scheduled { background: #f5f5f7; color: #1d1d1f; }
    .social-status.status-success { background: #e3f5e9; color: #14833b; border-color: #bde6c9; }
    .social-status.status-error { background: #ffe9e6; color: #d92d20; border-color: #ffd1cd; }
    .social-status.status-mixed { background: #f4ecff; color: #7c3aed; border-color: #d8c7ff; }
    .social-status.status-sending { background: #e5f0ff; color: #0f6bdc; border-color: #bfd6ff; }
    .social-network { background: #f4f5f8; color: var(--text-secondary); font-size: 10px; padding: 3px 8px; border-radius: 999px; font-weight: 700; text-transform: uppercase; }
    .social-nets,.social-table-actions { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
    .social-title { font-size: 14px; font-weight: 600; line-height: 1.3; }
    .social-note { color: var(--text-secondary); font-size: 12px; line-height: 1.4; }
    .social-row-listing { display: flex; gap: 10px; align-items: center; min-width: 0; }
    .social-row-thumb { width: 40px; height: 40px; border-radius: 10px; overflow: hidden; background: #eef2f7; border: 1px solid var(--border-light); flex-shrink: 0; }
    .social-row-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .social-row-thumb span { width: 100%; height: 100%; display: grid; place-items: center; font-size: 9px; font-weight: 700; color: var(--text-secondary); }
    .social-row-info { min-width: 0; }
    .social-row-info .social-title { font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
    .social-row-nets { display: flex; gap: 3px; flex-wrap: wrap; }
    .social-row-date { white-space: nowrap; font-size: 13px; font-weight: 600; }
    .social-row-time { font-size: 12px; color: var(--text-secondary); white-space: nowrap; }
    .social-pager { display: flex; justify-content: space-between; gap: 8px; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border-light); align-items: center; flex-wrap: wrap; }

    /* Modal */
    .social-modal { position: fixed; inset: 0; z-index: 140; display: none; align-items: center; justify-content: center; padding: 16px; background: rgba(15,23,42,.42); backdrop-filter: blur(8px); }
    .social-modal.open { display: flex; }
    .social-modal-panel { width: min(700px,100%); max-height: 88vh; overflow: auto; border-radius: 20px; background: #fff; border: 1px solid var(--border-light); box-shadow: 0 20px 60px rgba(15,23,42,.18); }
    .social-modal-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; padding: 18px 20px; border-bottom: 1px solid var(--border-light); position: sticky; top: 0; background: #fff; z-index: 1; }
    .social-modal-title { font-size: 18px; font-weight: 700; letter-spacing: -0.01em; margin-bottom: 4px; }
    .social-modal-subtle { font-size: 12px; color: var(--text-secondary); font-weight: 600; }
    .social-modal-close { width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--border-light); background: #fff; font-size: 20px; line-height: 1; cursor: pointer; flex-shrink: 0; }
    .social-modal-body { padding: 16px; display: grid; gap: 12px; background: #fbfbfd; }
    .social-modal-item { display: flex; gap: 14px; align-items: flex-start; padding: 14px; border-radius: 14px; border: 1px solid var(--border-light); background: #fff; }
    .social-modal-image { width: 80px; height: 80px; border-radius: 10px; overflow: hidden; background: #eef2f7; flex-shrink: 0; }
    .social-modal-image img { width: 100%; height: 100%; object-fit: cover; }
    .social-modal-content { min-width: 0; display: grid; gap: 8px; flex: 1; }
    .social-modal-content h4 { font-size: 16px; line-height: 1.25; margin: 0; }
    .social-modal-error { color: #b42318; font-size: 12px; line-height: 1.5; }
    .social-message-rich { display: grid; gap: 10px; }
    .social-message-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .social-message-tag { display: inline-flex; align-items: center; padding: 4px 9px; border-radius: 999px; background: #f4ecff; border: 1px solid #d8c7ff; color: #7c3aed; font-size: 11px; font-weight: 700; }
    .social-message-copy { display: grid; gap: 6px; }
    .social-message-copy p { margin: 0; color: var(--text); font-size: 13px; line-height: 1.55; }
    .social-message-heading { margin: 0; color: var(--text-secondary); font-size: 10px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
    .social-message-list { margin: 0; padding-left: 18px; display: grid; gap: 6px; color: var(--text); font-size: 13px; line-height: 1.5; }
    .social-message-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    .social-message-field { padding: 9px 10px; border-radius: 12px; background: #fafbfc; border: 1px solid var(--border-light); }
    .social-message-field strong { display: block; margin-bottom: 3px; color: var(--text-secondary); font-size: 10px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }
    .social-message-field span { display: block; color: var(--text); font-size: 13px; line-height: 1.45; }
    .social-message-links { display: flex; flex-wrap: wrap; gap: 8px; }
    .social-message-link { display: inline-flex; align-items: center; max-width: 100%; padding: 7px 10px; border-radius: 999px; border: 1px solid var(--border-light); background: #fff; color: var(--text); font-size: 12px; font-weight: 600; text-decoration: none; }
    .social-message-link:hover { background: #f5f5f7; }

    /* Accordion modal items */
    .social-modal-item { flex-wrap: wrap; cursor: pointer; transition: background .15s ease; }
    .social-modal-item:hover { background: #fafbfc; }
    .social-modal-toggle { display: flex; gap: 14px; align-items: center; width: 100%; min-width: 0; }
    .social-modal-toggle .social-modal-image { flex-shrink: 0; }
    .social-modal-toggle-info { display: flex; flex: 1; min-width: 0; gap: 10px; align-items: center; flex-wrap: wrap; }
    .social-modal-toggle-info h4 { font-size: 15px; line-height: 1.25; margin: 0; flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .social-modal-chevron { width: 24px; height: 24px; flex-shrink: 0; color: var(--text-secondary); transition: transform .25s ease; }
    .social-modal-item.is-open .social-modal-chevron { transform: rotate(180deg); }
    .social-modal-detail { width: 100%; display: grid; gap: 8px; max-height: 0; overflow: hidden; transition: max-height .3s ease, padding .3s ease; padding-top: 0; }
    .social-modal-item.is-open .social-modal-detail { max-height: 2000px; padding-top: 14px; }

    /* Responsive */
    @media (max-width: 1100px) {
        .social-stats { grid-template-columns: repeat(2,minmax(0,1fr)); }
        .social-filter-form { grid-template-columns: 1fr 1fr; }
        .social-filter-actions { grid-column: 1/-1; }
    }
    @media (max-width: 768px) {
        .content { padding: 16px 12px 28px; }
        .social-page { gap: 10px; }
        .social-welcome { padding: 12px 14px; }
        .social-welcome h2 { font-size: 15px; }
        .social-welcome-actions a { padding: 8px 14px; font-size: 13px; }
        .social-stats { gap: 8px; }
        .social-stat { padding: 12px 14px; }
        .social-stat strong { font-size: 22px; }
        .social-stat span { font-size: 10px; margin-bottom: 4px; }
        .social-controls { padding: 12px; }
        .social-toggle a { min-width: 60px; padding: 5px 10px; font-size: 12px; }
        .social-filter-form { grid-template-columns: 1fr; gap: 6px; }
        .social-filter-actions { grid-column: 1; }
        .social-filter-actions .btn { flex: 1; }
        .social-board { padding: 12px; }
        .social-legend { display: none; }
        /* Calendar mobile */
        .social-cal .fc .fc-toolbar-title { font-size: 15px; }
        .social-cal .fc .fc-button { padding: 5px 8px!important; font-size: 11px!important; border-radius: 8px!important; }
        .social-cal .fc .fc-daygrid-day-frame { min-height: 56px; }
        .social-cal .fc .fc-col-header-cell-cushion { font-size: 10px; padding: 5px 2px; }
        .social-cal .fc .fc-daygrid-day-number { font-size: 11px; padding: 2px 4px; }
        .social-cal .fc .fc-daygrid-day-events { gap: 2px; margin: 2px 2px 0; }
        .social-chip { padding: 2px 3px; }
        .social-chip-title { font-size: 9px; }
        .social-chip-foot { display: none; }
        /* Table cards on mobile */
        .social-table thead { display: none; }
        .social-table,.social-table tbody,.social-table tr,.social-table td { display: block; width: 100%; }
        .social-table tr { border: 1px solid var(--border-light); border-radius: 14px; margin-bottom: 8px; background: #fff; overflow: hidden; padding: 12px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .social-table td { border: none; padding: 0; }
        .social-table td.td-listing { flex: 1; min-width: 0; }
        .social-table td.td-date { font-size: 12px; }
        .social-table td.td-status { order: -1; width: 100%; }
        .social-table td.td-nets { display: none; }
        .social-row-info .social-title { max-width: none; }
        .social-modal { padding: 0; align-items: flex-end; }
        .social-modal-panel { width: 100%; max-height: 90vh; border-radius: 20px 20px 0 0; }
        .social-modal-toggle { flex-direction: column; }
        .social-modal-toggle .social-modal-image { width: 100%; height: 140px; }
        .social-modal-toggle-info { gap: 8px; }
        .social-modal-toggle-info h4 { white-space: normal; font-size: 14px; }
        .social-message-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 420px) {
        .social-stats { grid-template-columns: repeat(2,minmax(0,1fr)); gap: 6px; }
        .social-cal .fc .fc-toolbar-title { font-size: 14px; }
    }
</style>

<div class="social-page">
    <section class="social-welcome">
        <h2>Social media</h2>
        <div class="social-welcome-actions">
            <a href="{{ route('social.channels.index') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                Channels
            </a>
            <a href="{{ route('social.create') }}" class="primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Post
            </a>
        </div>
    </section>

    <section class="social-stats">
        <article class="social-stat"><span>Channels</span><strong>{{ $stats['channels'] }}</strong></article>
        <article class="social-stat"><span>Queued</span><strong>{{ $stats['scheduled'] }}</strong></article>
        <article class="social-stat"><span>Sent</span><strong>{{ $stats['sent'] }}</strong></article>
        <article class="social-stat"><span>Issues</span><strong>{{ $stats['issues'] }}</strong></article>
    </section>

    <section class="social-controls">
        <div class="social-controls-row">
            <div class="social-toggle">
                <a href="{{ route('social.index', $calendarQuery) }}" class="{{ $viewMode === 'calendar' ? 'active' : '' }}">Calendar</a>
                <a href="{{ route('social.index', $tableQuery) }}" class="{{ $viewMode === 'table' ? 'active' : '' }}">List</a>
            </div>
        </div>
        <form method="GET" class="social-filter-form">
            <input type="hidden" name="view" value="{{ $viewMode }}">
            <input type="text" name="search" class="form-input" value="{{ $search }}" placeholder="Search posts">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(['scheduled' => 'Scheduled', 'success' => 'Sent', 'error' => 'Failed', 'mixed' => 'Mixed', 'sending' => 'Sending'] as $value => $label)
                    <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="network" class="form-select">
                <option value="">All networks</option>
                @foreach($networkSummary as $network)
                    <option value="{{ $network['key'] }}" @selected($networkFilter === $network['key'])>{{ $network['label'] }}</option>
                @endforeach
            </select>
            <div class="social-filter-actions">
                <button type="submit" class="btn btn-secondary">Apply</button>
                <a href="{{ route('social.index', ['view' => $viewMode]) }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="social-board">
        <div class="social-board-head">
            <div>
                <h4>{{ $viewMode === 'calendar' ? 'Scheduled posts' : 'Schedule table' }}</h4>
                <div class="social-board-meta">{{ $posts->total() }} result{{ $posts->total() === 1 ? '' : 's' }}</div>
            </div>
            <div class="social-legend">
                <span class="social-pill status-scheduled">Queued</span>
                <span class="social-pill status-success">Sent</span>
                <span class="social-pill status-error">Failed</span>
                <span class="social-pill status-mixed">Mixed</span>
            </div>
        </div>

        @if($posts->total() === 0)
            <div class="social-empty">
                <p>No schedules found.</p>
                <a href="{{ route('social.create') }}" class="btn btn-primary">Schedule your first post</a>
            </div>
        @elseif($viewMode === 'calendar')
            <div class="social-cal">
                <div id="social-calendar"></div>
            </div>
        @else
            <div class="table-wrap" style="margin:0;padding:0;overflow-x:auto;">
                <table class="social-table">
                    <thead><tr><th>Listing</th><th>Date</th><th>Status</th><th>Channels</th><th></th></tr></thead>
                    <tbody>
                        @foreach($posts as $schedule)
                            @php($rowData = json_encode([
                                'title' => $schedule['listing_title'],
                                'image_url' => $schedule['image_url'] ?? '',
                                'formatted_price' => $schedule['formatted_price'] ?? '',
                                'content_type_label' => $schedule['content_type_label'] ?? '',
                                'time_label' => $schedule['scheduled_at_full'] ?? '',
                                'status' => $schedule['status'],
                                'status_label' => $schedule['status_label'],
                                'network_labels' => $schedule['network_badges'] ?? [],
                                'channels' => $schedule['channels'] ?? 0,
                                'channel_names' => $schedule['channel_names'] ?? [],
                                'message_full' => $schedule['message_preview'] ?? '',
                                'error_messages' => $schedule['error_messages'] ?? [],
                                'edit_url' => $schedule['edit_url'] ?? '',
                                'view_url' => $schedule['view_url'] ?? '',
                            ]))
                            <tr data-schedule="{{ $rowData }}">
                                <td class="td-listing">
                                    <div class="social-row-listing">
                                        <div class="social-row-thumb">
                                            @if($schedule['image_url'])
                                                <img src="{{ $schedule['image_url'] }}" alt="">
                                            @else
                                                <span>—</span>
                                            @endif
                                        </div>
                                        <div class="social-row-info">
                                            <div class="social-title">{{ $schedule['listing_title'] }}</div>
                                            <div class="social-note">{{ $schedule['content_type_label'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="td-date">
                                    <div class="social-row-date">{{ $schedule['scheduled_at_date'] }}</div>
                                    <div class="social-row-time">{{ $schedule['scheduled_at_time'] }}</div>
                                </td>
                                <td class="td-status">
                                    <span class="social-status status-{{ $schedule['status'] }}">{{ $schedule['status_label'] }}</span>
                                </td>
                                <td class="td-nets">
                                    <div class="social-row-nets">
                                        @foreach(array_slice($schedule['network_badges'], 0, 3) as $network)
                                            <span class="social-network">{{ $network }}</span>
                                        @endforeach
                                        @if(count($schedule['network_badges']) > 3)
                                            <span class="social-network">+{{ count($schedule['network_badges']) - 3 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($posts->hasPages())
                <div class="social-pager">
                    <div class="social-note">Page {{ $posts->currentPage() }} of {{ $posts->lastPage() }}</div>
                    <div class="social-table-actions">
                        @if($posts->onFirstPage())<span class="btn btn-secondary btn-sm" style="pointer-events:none;opacity:.5;">Previous</span>@else<a href="{{ $posts->previousPageUrl() }}" class="btn btn-secondary btn-sm">Previous</a>@endif
                        @if($posts->hasMorePages())<a href="{{ $posts->nextPageUrl() }}" class="btn btn-secondary btn-sm">Next</a>@else<span class="btn btn-secondary btn-sm" style="pointer-events:none;opacity:.5;">Next</span>@endif
                    </div>
                </div>
            @endif
        @endif
    </section>

    <div class="social-modal" id="social-modal" aria-hidden="true">
        <div class="social-modal-panel" role="dialog" aria-modal="true" aria-labelledby="social-modal-title">
            <div class="social-modal-head">
                <div>
                    <div class="social-modal-subtle" data-modal-subtitle>Schedule details</div>
                    <h3 class="social-modal-title" id="social-modal-title" data-modal-title>Schedule</h3>
                </div>
                <button type="button" class="social-modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
            <div class="social-modal-body" data-modal-body></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<script>
(function () {
    var calendarEl = document.getElementById('social-calendar');
    var modalEl = document.getElementById('social-modal');
    var titleEl = document.querySelector('[data-modal-title]');
    var subtitleEl = document.querySelector('[data-modal-subtitle]');
    var bodyEl = document.querySelector('[data-modal-body]');
    var closeButtons = Array.prototype.slice.call(document.querySelectorAll('[data-modal-close]'));
    var tableViewUrl = @json($tableViewUrl);

    function esc(v) { return String(v||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function openModal(t, s, h) {
        if (!modalEl) return;
        titleEl.textContent = t; subtitleEl.textContent = s; bodyEl.innerHTML = h;
        modalEl.classList.add('open'); modalEl.setAttribute('aria-hidden','false');
        document.documentElement.style.overflow = 'hidden';
        bindAccordion();
    }
    function closeModal() {
        if (!modalEl) return;
        modalEl.classList.remove('open'); modalEl.setAttribute('aria-hidden','true');
        bodyEl.innerHTML = ''; document.documentElement.style.overflow = '';
    }

    function uniqueStrings(items) {
        var seen = Object.create(null);
        return (items || []).filter(function (item) {
            var key = String(item || '').toLowerCase();
            if (!key || seen[key]) {
                return false;
            }
            seen[key] = true;
            return true;
        });
    }

    function normalizeUrl(url) {
        var value = String(url || '').trim();
        if (!value) return '';
        return /^https?:\/\//i.test(value) ? value : 'https://' + value.replace(/^\/+/, '');
    }

    function escapeAttr(value) {
        return esc(value).replace(/'/g, '&#39;');
    }

    function linkLabel(url) {
        try {
            var parsed = new URL(normalizeUrl(url));
            return parsed.hostname.replace(/^www\./i, '');
        } catch (error) {
            return String(url || '').replace(/^https?:\/\//i, '').replace(/^www\./i, '');
        }
    }

    function normalizeScheduleMessage(message) {
        var value = String(message || '').replace(/\u00a0/g, ' ').trim();

        if (!value) {
            return '';
        }

        value = value.replace(/([^\s])#/g, '$1 #');
        value = value.replace(/\b(CIEH Force Sale\/ Bank Lelong)\b/gi, '\n$1');
        value = value.replace(/(https?:\/\/\S+|www\.\S+)/gi, '\n$1\n');
        value = value.replace(/([a-z0-9)])(?=(Location|Built\s*Up|Land\s*Area|Agent name|Contact No|Agency|Whatsapp me now for more details|View more photo|About this agent|Show more photo)\b)/gi, '$1\n');

        [
            /(?:^|\s)(BANK\s+LEL[O0]NG\s+DATE\b)/gi,
            /(?:^|\s)(DETAILS:)/gi,
            /(?:^|\s)(Auction Sale:)/gi,
            /(?:^|\s)(Kindly take note:)/gi,
            /(?:^|\s)(Auction Specialist:)/gi,
            /(?:^|\s)(Location\s*:)/gi,
            /(?:^|\s)(Built\s*Up\s*:)/gi,
            /(?:^|\s)(Land\s*Area\s*:)/gi,
            /(?:^|\s)(Agent name\s*:)/gi,
            /(?:^|\s)(Contact No\s*:)/gi,
            /(?:^|\s)(Agency\s*:)/gi,
            /(?:^|\s)(Whatsapp me now for more details\b)/gi,
            /(?:^|\s)(View more photo\s*>*)/gi,
            /(?:^|\s)(About this agent - Profile\b)/gi,
            /(?:^|\s)(Show more photo\b)/gi
        ].forEach(function (pattern) {
            value = value.replace(pattern, '\n$1');
        });

        value = value.replace(/DETAILS:\s*-\s*/i, 'DETAILS:\n- ');
        value = value.replace(/Kindly take note:\s*\+\s*/i, 'Kindly take note:\n+ ');
        value = value.replace(/\s-\s(?=[A-Za-z])/g, '\n- ');
        value = value.replace(/\s\+\s(?=[A-Za-z])/g, '\n+ ');
        value = value.replace(/[ \t]{2,}/g, ' ');
        value = value.replace(/ *\n */g, '\n');
        value = value.replace(/\n{3,}/g, '\n\n');

        return value.trim();
    }

    function formatScheduleMessage(message) {
        var normalized = normalizeScheduleMessage(message);

        if (!normalized) {
            return '';
        }

        var tags = [];
        normalized = normalized.replace(/(^|[\s(])(#([A-Za-z0-9_]+))/g, function (match, prefix, fullTag) {
            tags.push(fullTag);
            return prefix || ' ';
        });
        tags = uniqueStrings(tags);

        var lines = normalized
            .split(/\n+/)
            .map(function (line) { return String(line || '').trim(); })
            .filter(Boolean);

        var intro = [];
        var detailItems = [];
        var noteItems = [];
        var fields = [];
        var links = [];
        var misc = [];

        lines.forEach(function (line) {
            if (/^(Whatsapp me now for more details|View more photo\s*>*|About this agent - Profile|Show more photo)$/i.test(line)) {
                return;
            }

            if (/^(https?:\/\/\S+|www\.\S+)$/i.test(line)) {
                links.push(line);
                return;
            }

            if (/^DETAILS:?$/i.test(line) || /^Kindly take note:?$/i.test(line)) {
                return;
            }

            if (/^- /.test(line)) {
                detailItems.push(line.replace(/^- /, '').trim());
                return;
            }

            if (/^\+ /.test(line)) {
                noteItems.push(line.replace(/^\+ /, '').trim());
                return;
            }

            var fieldMatch = line.match(/^([A-Za-z][A-Za-z0-9 /&().-]{1,30})\s*:\s*(.+)$/);
            if (fieldMatch) {
                fields.push({
                    label: fieldMatch[1].trim(),
                    value: fieldMatch[2].trim()
                });
                return;
            }

            if (intro.length < 2) {
                intro.push(line);
                return;
            }

            misc.push(line);
        });

        var html = '<div class="social-message-rich">';

        if (tags.length) {
            html += '<div class="social-message-tags">' + tags.map(function (tag) {
                return '<span class="social-message-tag">' + esc(tag) + '</span>';
            }).join('') + '</div>';
        }

        if (intro.length || misc.length) {
            html += '<div class="social-message-copy">';
            intro.concat(misc).forEach(function (line) {
                html += '<p>' + esc(line) + '</p>';
            });
            html += '</div>';
        }

        if (detailItems.length) {
            html += '<div><div class="social-message-heading">Details</div><ul class="social-message-list">';
            detailItems.forEach(function (item) {
                html += '<li>' + esc(item) + '</li>';
            });
            html += '</ul></div>';
        }

        if (noteItems.length) {
            html += '<div><div class="social-message-heading">Notes</div><ul class="social-message-list">';
            noteItems.forEach(function (item) {
                html += '<li>' + esc(item) + '</li>';
            });
            html += '</ul></div>';
        }

        if (fields.length) {
            html += '<div class="social-message-grid">';
            fields.forEach(function (field) {
                html += '<div class="social-message-field"><strong>' + esc(field.label) + '</strong><span>' + esc(field.value) + '</span></div>';
            });
            html += '</div>';
        }

        if (links.length) {
            html += '<div class="social-message-links">';
            uniqueStrings(links).forEach(function (link) {
                var href = normalizeUrl(link);
                html += '<a class="social-message-link" href="' + escapeAttr(href) + '" target="_blank" rel="noopener">' + esc(linkLabel(link)) + '</a>';
            });
            html += '</div>';
        }

        html += '</div>';

        return html;
    }

    function renderItem(event, autoOpen) {
        var p = event.extendedProps || {}, acts = [],
            img = p.image_url ? '<div class="social-modal-image"><img src="'+esc(p.image_url)+'" alt=""></div>' : '',
            openClass = autoOpen ? ' is-open' : '';
        if (p.edit_url) acts.push('<a href="'+esc(p.edit_url)+'" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">Edit</a>');
        if (p.view_url) acts.push('<a href="'+esc(p.view_url)+'" class="btn btn-secondary btn-sm" onclick="event.stopPropagation()">Listing</a>');
        return '<article class="social-modal-item'+openClass+'">'
            +'<div class="social-modal-toggle">'+img
            +'<div class="social-modal-toggle-info">'
            +'<h4>'+esc(event.title||'Untitled')+'</h4>'
            +'<span class="social-status status-'+esc(p.status||'scheduled')+'">'+esc(p.status_label||'Scheduled')+'</span>'
            +'</div>'
            +'<svg class="social-modal-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>'
            +'</div>'
            +'<div class="social-modal-detail">'
            +'<div class="social-modal-subtle">'+esc(p.time_label||'')+'</div>'
            +'<div class="social-note">'+esc((p.network_labels||[]).join(', '))+(p.channels?' &middot; '+esc(p.channels)+' ch':'')+'</div>'
            +(p.formatted_price?'<div class="social-note">'+esc(p.formatted_price)+'</div>':'')
            +(p.message_full?formatScheduleMessage(p.message_full):'')
            +((p.error_messages||[]).length?'<div class="social-modal-error">'+esc((p.error_messages||[]).join(' '))+'</div>':'')
            +(acts.length?'<div class="social-table-actions" style="margin-top:6px;">'+acts.join('')+'</div>':'')
            +'</div></article>';
    }

    function bindAccordion() {
        var items = bodyEl.querySelectorAll('.social-modal-item');
        items.forEach(function(item) {
            item.addEventListener('click', function(e) {
                if (e.target.closest('a,button')) return;
                item.classList.toggle('is-open');
            });
        });
    }

    if (calendarEl && typeof FullCalendar !== 'undefined') {
        var cal = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            customButtons: {
                openTable: {
                    text: 'List',
                    click: function () {
                        window.location.href = tableViewUrl;
                    }
                }
            },
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'openTable' },
            buttonText: { today: 'Today' },
            firstDay: 0, fixedWeekCount: false, height: 'auto',
            dayMaxEvents: 2, dayMaxEventRows: 2, eventDisplay: 'block',
            events: @json($calendarEvents),
            eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
            noEventsContent: 'No posts scheduled this period.',
            moreLinkContent: function (a) { return { html: '+'+a.num+' more' }; },
            eventContent: function (info) {
                var p = info.event.extendedProps||{}, w = document.createElement('div');
                w.className = 'social-chip schedule-status-'+(p.status||'scheduled');
                w.innerHTML = '<div class="social-chip-title">'+esc(info.event.title)+'</div>';
                var f = document.createElement('div'); f.className = 'social-chip-foot';
                f.innerHTML = '<div class="social-chip-time">'+esc(info.timeText||'')+'</div>';
                var n = document.createElement('div'); n.className = 'social-chip-nets';
                (p.networks||[]).slice(0,2).forEach(function(net){
                    var s = document.createElement('span'); s.className='social-chip-net';
                    s.textContent=String(net).substring(0,1).toUpperCase(); n.appendChild(s);
                });
                if ((p.networks||[]).length>2){ var m=document.createElement('span'); m.className='social-chip-net'; m.textContent='+'+(p.networks.length-2); n.appendChild(m); }
                f.appendChild(n); w.appendChild(f);
                return { domNodes: [w] };
            },
            eventDidMount: function (info) {
                var p = info.event.extendedProps||{};
                info.el.title = [p.time_label||'',p.status_label||'',(p.networks||[]).join(', ')].filter(Boolean).join('\n');
            },
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                info.jsEvent.stopPropagation();
                var p = info.event.extendedProps||{};
                openModal(info.event.title||'Schedule', p.time_label||'Details', renderItem(info.event, true));
            },
            moreLinkClick: function (arg) {
                if (arg.jsEvent) { arg.jsEvent.preventDefault(); arg.jsEvent.stopPropagation(); }
                var evts = (arg.allSegs||[]).map(function(s){return s.event;});
                var label = new Intl.DateTimeFormat('en-US',{weekday:'long',day:'numeric',month:'long',year:'numeric'}).format(arg.date);
                if (!evts.length) { openModal(label,'No schedules','<div class="social-empty"><p>Nothing here.</p></div>'); return 'none'; }
                openModal(label, evts.length+' schedule'+(evts.length===1?'':'s'), evts.map(renderItem).join(''));
                return 'none';
            }
        });
        cal.render();
    }

    /* Table row click → modal */
    var tableRows = document.querySelectorAll('.social-table tbody tr[data-schedule]');
    tableRows.forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (e.target.closest('a,button,form')) return;
            var d;
            try { d = JSON.parse(row.getAttribute('data-schedule')); } catch(err) { return; }
            var fakeEvent = {
                title: d.title || 'Schedule',
                extendedProps: {
                    image_url: d.image_url,
                    formatted_price: d.formatted_price,
                    time_label: d.time_label,
                    status: d.status,
                    status_label: d.status_label,
                    network_labels: d.network_labels,
                    channels: d.channels,
                    message_full: d.message_full,
                    error_messages: d.error_messages,
                    edit_url: d.edit_url,
                    view_url: d.view_url
                }
            };
            openModal(d.title || 'Schedule', d.time_label || 'Details', renderItem(fakeEvent, true));
        });
    });

    closeButtons.forEach(function(b){ b.addEventListener('click', closeModal); });
    if (modalEl) modalEl.addEventListener('click', function(e){ if(e.target===modalEl) closeModal(); });
    document.addEventListener('keydown', function(e){ if(e.key==='Escape'&&modalEl&&modalEl.classList.contains('open')) closeModal(); });
}());
</script>
@endsection
