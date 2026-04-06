@extends('layouts.app')

@section('title', 'Social Media')
@section('page-title', 'FS Poster Scheduler')

@section('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css">
@endsection

@section('topbar-actions')
    <a href="{{ route('social.create') }}" class="btn btn-primary btn-sm">New Schedule</a>
    <a href="{{ rtrim(\App\Support\CondoWordpressBridge::siteBaseUrl(), '/') . '/wp-admin/admin.php?page=fs-poster#/calendar' }}" class="btn btn-secondary btn-sm" target="_blank" rel="noreferrer">WP Calendar</a>
@endsection

@section('content')
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
@endphp

<style>
    .content {
        max-width: none;
        padding: 28px 28px 48px;
    }
    .planner-shell {
        display: grid;
        gap: 24px;
    }
    .planner-stage {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 24px;
        align-items: start;
    }
    .planner-sidebar,
    .planner-main {
        display: grid;
        gap: 24px;
    }
    .planner-sidebar {
        grid-template-columns: minmax(320px, 1.15fr) minmax(220px, 0.9fr) minmax(220px, 0.9fr);
        align-items: stretch;
    }
    .planner-sidebar > :first-child {
        grid-row: 1 / span 2;
    }
    .planner-sidebar > :last-child {
        grid-column: 2 / -1;
    }
    .planner-card {
        background: var(--card-bg);
        border: 1px solid var(--border-light);
        border-radius: 28px;
        box-shadow: var(--shadow-sm);
    }
    .planner-brand {
        padding: 28px;
        background:
            radial-gradient(circle at top right, rgba(182, 215, 168, 0.35), transparent 42%),
            radial-gradient(circle at bottom left, rgba(253, 229, 207, 0.7), transparent 38%),
            linear-gradient(180deg, #ffffff 0%, #fffaf4 100%);
    }
    .planner-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(0, 0, 0, 0.06);
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 16px;
    }
    .planner-brand h3 {
        font-size: clamp(28px, 3vw, 40px);
        line-height: 1.02;
        letter-spacing: -0.04em;
        margin-bottom: 14px;
    }
    .planner-brand p,
    .planner-note,
    .planner-subtle,
    .planner-table-note {
        color: var(--text-secondary);
        line-height: 1.65;
    }
    .planner-brand-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 22px;
    }
    .planner-panel {
        padding: 22px;
    }
    .planner-panel-head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .planner-panel-head h4 {
        font-size: 19px;
        line-height: 1.1;
        letter-spacing: -0.02em;
        margin-bottom: 6px;
    }
    .planner-mini-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .planner-mini-stat {
        padding: 18px;
        border-radius: 22px;
        background: linear-gradient(180deg, #ffffff 0%, #f8f8fb 100%);
        border: 1px solid var(--border-light);
    }
    .planner-mini-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-secondary);
        margin-bottom: 10px;
    }
    .planner-mini-value {
        font-size: 30px;
        line-height: 1;
        letter-spacing: -0.05em;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .planner-upcoming-list,
    .planner-channel-list {
        display: grid;
        gap: 12px;
    }
    .planner-upcoming-item,
    .planner-channel-item {
        padding: 14px 16px;
        border-radius: 20px;
        border: 1px solid var(--border-light);
        background: linear-gradient(180deg, #ffffff 0%, #fbfbfd 100%);
    }
    .planner-upcoming-time {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #2f6b52;
        margin-bottom: 8px;
    }
    .planner-upcoming-title,
    .planner-channel-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--text);
        line-height: 1.3;
        margin-bottom: 6px;
    }
    .planner-channel-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }
    .planner-channel-count {
        min-width: 38px;
        height: 38px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f4f4f8;
        border: 1px solid var(--border-light);
        font-weight: 700;
    }
    .planner-toolbar {
        padding: 26px;
        background:
            radial-gradient(circle at top left, rgba(204, 223, 255, 0.4), transparent 28%),
            linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }
    .planner-toolbar-row {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .planner-eyebrow {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }
    .planner-toolbar h3 {
        font-size: clamp(24px, 2.4vw, 34px);
        line-height: 1.08;
        letter-spacing: -0.04em;
        margin-bottom: 10px;
    }
    .planner-toggle {
        display: inline-flex;
        gap: 8px;
        padding: 6px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
    }
    .planner-toggle a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border-radius: 999px;
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s ease;
    }
    .planner-toggle a.active {
        background: #1f4f45;
        color: #fff;
        box-shadow: 0 10px 24px rgba(31, 79, 69, 0.22);
    }
    .planner-filter-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) repeat(2, minmax(180px, 0.4fr)) auto auto;
        gap: 12px;
    }
    .planner-view-card {
        padding: 22px;
    }
    .planner-legend {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .planner-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid var(--border-light);
        background: #fff;
        color: var(--text-secondary);
    }
    .planner-pill::before {
        content: '';
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: currentColor;
        opacity: 0.8;
    }
    .planner-pill.status-scheduled { color: #1d1d1f; }
    .planner-pill.status-success { color: #14833b; }
    .planner-pill.status-error { color: #d92d20; }
    .planner-pill.status-mixed { color: #7c3aed; }
    .planner-pill.status-sending { color: #0f6bdc; }
    .planner-calendar-frame {
        padding: 20px;
        border-radius: 24px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
        border: 1px solid var(--border-light);
        overflow: hidden;
    }
    .planner-calendar-frame .fc .fc-toolbar {
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .planner-calendar-frame .fc .fc-toolbar-title {
        font-size: clamp(20px, 2.1vw, 30px);
        font-weight: 700;
        letter-spacing: -0.04em;
    }
    .planner-calendar-frame .fc .fc-button {
        border-radius: 999px !important;
        padding: 10px 16px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        background: #fff !important;
        border-color: var(--border-light) !important;
        color: var(--text) !important;
        box-shadow: none !important;
    }
    .planner-calendar-frame .fc .fc-button-primary:not(:disabled).fc-button-active,
    .planner-calendar-frame .fc .fc-button-primary:not(:disabled):active {
        background: #1f4f45 !important;
        border-color: #1f4f45 !important;
        color: #fff !important;
    }
    .planner-calendar-frame .fc .fc-button:hover {
        background: #f6f8fb !important;
    }
    .planner-calendar-frame .fc-theme-standard td,
    .planner-calendar-frame .fc-theme-standard th,
    .planner-calendar-frame .fc-theme-standard .fc-scrollgrid {
        border-color: var(--border-light);
    }
    .planner-calendar-frame .fc .fc-daygrid-day-number,
    .planner-calendar-frame .fc .fc-col-header-cell-cushion {
        color: var(--text);
        text-decoration: none;
    }
    .planner-calendar-frame .fc .fc-day-today {
        background: rgba(223, 239, 231, 0.4) !important;
    }
    .planner-calendar-frame .fc .fc-view-harness {
        min-height: 820px;
    }
    .planner-calendar-frame .fc .fc-daygrid-day-frame {
        min-height: 162px;
    }
    .planner-calendar-frame .fc .fc-daygrid-day-top {
        padding: 6px 8px 0;
    }
    .planner-calendar-frame .fc .fc-daygrid-day-number {
        font-size: 13px;
        font-weight: 700;
    }
    .planner-calendar-frame .fc .fc-daygrid-day-events {
        display: grid;
        gap: 8px;
        margin: 8px 8px 0;
    }
    .planner-calendar-frame .fc .fc-daygrid-event-harness {
        margin-top: 0 !important;
    }
    .planner-calendar-frame .fc .fc-daygrid-more-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 2px 8px 8px;
        padding: 7px 10px;
        border-radius: 999px;
        background: #f3f6fb;
        color: #335c89;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid #d7e2f0;
    }
    .planner-calendar-frame .fc .fc-daygrid-more-link:hover {
        background: #eaf0f7;
    }
    .calendar-chip {
        display: grid;
        gap: 4px;
        padding: 10px 11px;
        border-radius: 14px;
        border: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        cursor: pointer;
    }
    .calendar-chip.schedule-status-scheduled { background: linear-gradient(180deg, #eff5ff 0%, #fbfdff 100%); }
    .calendar-chip.schedule-status-success { background: linear-gradient(180deg, #e7f7ec 0%, #f8fdf9 100%); }
    .calendar-chip.schedule-status-error { background: linear-gradient(180deg, #ffecea 0%, #fff9f9 100%); }
    .calendar-chip.schedule-status-mixed { background: linear-gradient(180deg, #f5ecff 0%, #fdf9ff 100%); }
    .calendar-chip.schedule-status-sending { background: linear-gradient(180deg, #e9f3ff 0%, #f8fbff 100%); }
    .calendar-chip-top {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        align-items: flex-start;
    }
    .calendar-chip-title {
        font-size: 12px;
        font-weight: 700;
        line-height: 1.3;
        color: #1d1d1f;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .calendar-chip-time {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        white-space: nowrap;
        color: #546273;
    }
    .calendar-chip-meta {
        font-size: 10px;
        line-height: 1.35;
        color: #5a6472;
    }
    .planner-empty {
        padding: 56px 20px;
        text-align: center;
        border-radius: 24px;
        border: 1px dashed var(--border);
        background: #fafafc;
        color: var(--text-secondary);
    }
    .planner-empty p {
        margin-bottom: 16px;
        font-size: 15px;
        font-weight: 500;
    }
    .planner-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .planner-table th {
        font-size: 11px;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: var(--text-secondary);
        font-weight: 700;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-light);
        background: #fafafc;
    }
    .planner-table td {
        padding: 18px 16px;
        border-bottom: 1px solid var(--border-light);
        vertical-align: top;
        background: #fff;
    }
    .planner-table tbody tr:hover td {
        background: #fcfcff;
    }
    .planner-table-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--text);
        line-height: 1.3;
        margin-bottom: 6px;
    }
    .planner-table-links {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
    }
    .planner-table-links a,
    .planner-table-links button {
        min-width: 108px;
    }
    .planner-table-note {
        font-size: 13px;
    }
    .planner-network-list {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .planner-network-tag,
    .planner-status-tag {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid var(--border-light);
        background: var(--accent-light);
    }
    .planner-status-tag.status-scheduled { background: #f5f5f7; color: #1d1d1f; }
    .planner-status-tag.status-success { background: #e3f5e9; color: #14833b; border-color: #bde6c9; }
    .planner-status-tag.status-error { background: #ffe9e6; color: #d92d20; border-color: #ffd1cd; }
    .planner-status-tag.status-draft { background: #fff2d6; color: #995c00; border-color: #ffe1a1; }
    .planner-status-tag.status-sending { background: #e5f0ff; color: #0f6bdc; border-color: #bfd6ff; }
    .planner-status-tag.status-mixed { background: #f4ecff; color: #7c3aed; border-color: #d8c7ff; }
    .planner-message-box {
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid var(--border-light);
        background: #fbfbfd;
        line-height: 1.6;
    }
    .calendar-modal {
        position: fixed;
        inset: 0;
        z-index: 140;
        display: none;
        align-items: center;
        justify-content: center;
        padding: clamp(12px, 3vw, 24px);
        overflow-y: auto;
        background: rgba(15, 23, 42, 0.42);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .calendar-modal.open {
        display: flex;
    }
    .calendar-modal-panel {
        width: min(920px, calc(100vw - 24px));
        max-height: min(88dvh, 920px);
        overflow: hidden;
        border-radius: 28px;
        background: #fff;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.2);
        display: grid;
        grid-template-rows: auto 1fr;
        margin: auto;
    }
    .calendar-modal-head {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: flex-start;
        padding: 24px 24px 18px;
        border-bottom: 1px solid var(--border-light);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        position: sticky;
        top: 0;
        z-index: 1;
    }
    .calendar-modal-head > div,
    .calendar-modal-item-top > div {
        min-width: 0;
        flex: 1 1 auto;
    }
    .calendar-modal-kicker {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }
    .calendar-modal-title {
        font-size: clamp(22px, 2.5vw, 32px);
        line-height: 1.08;
        letter-spacing: -0.04em;
        margin-bottom: 8px;
        overflow-wrap: anywhere;
    }
    .calendar-modal-subtitle {
        color: var(--text-secondary);
        line-height: 1.55;
        overflow-wrap: anywhere;
    }
    .calendar-modal-close {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: 1px solid var(--border-light);
        background: #fff;
        color: var(--text);
        cursor: pointer;
        font-size: 22px;
        line-height: 1;
        box-shadow: var(--shadow-sm);
        flex-shrink: 0;
    }
    .calendar-modal-body {
        overflow: auto;
        padding: 20px 24px 24px;
        display: grid;
        gap: 14px;
        background: #fbfbfd;
        overscroll-behavior: contain;
    }
    .calendar-modal-item {
        padding: 18px;
        border-radius: 22px;
        border: 1px solid var(--border-light);
        background: #fff;
        box-shadow: var(--shadow-sm);
        display: grid;
        gap: 10px;
        min-width: 0;
    }
    .calendar-modal-item-top {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    .calendar-modal-item-time {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #2f6b52;
        margin-bottom: 6px;
    }
    .calendar-modal-item-title {
        font-size: 18px;
        line-height: 1.2;
        letter-spacing: -0.03em;
        margin-bottom: 0;
        overflow-wrap: anywhere;
    }
    .calendar-modal-meta,
    .calendar-modal-error {
        color: var(--text-secondary);
        line-height: 1.55;
        overflow-wrap: anywhere;
    }
    .calendar-modal-message {
        margin-top: 12px;
        padding: 14px 16px;
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid var(--border-light);
        line-height: 1.65;
        color: var(--text);
        overflow-wrap: anywhere;
    }
    .calendar-modal-error {
        margin-top: 10px;
        color: #b42318;
    }
    .calendar-modal-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 16px;
        align-items: stretch;
    }
    @media (max-width: 1180px) {
        .planner-sidebar {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .planner-sidebar > :first-child,
        .planner-sidebar > :last-child {
            grid-column: auto;
            grid-row: auto;
        }
        .planner-filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 820px) {
        .content {
            padding: 22px 16px 38px;
        }
        .planner-sidebar {
            grid-template-columns: 1fr;
        }
        .planner-filter-grid {
            grid-template-columns: 1fr;
        }
        .planner-mini-grid {
            grid-template-columns: 1fr 1fr;
        }
        .planner-toolbar-row {
            flex-direction: column;
        }
        .planner-toggle {
            width: 100%;
            justify-content: stretch;
        }
        .planner-toggle a {
            flex: 1 1 0;
        }
        .planner-calendar-frame {
            padding: 10px;
        }
        .planner-calendar-frame .fc .fc-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .planner-calendar-frame .fc .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .planner-calendar-frame .fc .fc-view-harness {
            min-height: 640px;
        }
        .planner-calendar-frame .fc .fc-daygrid-day-frame {
            min-height: 118px;
        }
        .calendar-chip {
            padding: 8px 9px;
            gap: 2px;
        }
        .calendar-chip-title {
            font-size: 11px;
            -webkit-line-clamp: 1;
            line-clamp: 1;
        }
        .calendar-chip-meta {
            display: none;
        }
        .planner-table thead {
            display: none;
        }
        .planner-table,
        .planner-table tbody,
        .planner-table tr,
        .planner-table td {
            display: block;
            width: 100%;
        }
        .planner-table tr {
            margin-bottom: 16px;
            border: 1px solid var(--border-light);
            border-radius: 22px;
            overflow: hidden;
            background: #fff;
            box-shadow: var(--shadow-sm);
        }
        .planner-table td {
            position: relative;
            padding: 46px 16px 16px;
            border-bottom: 1px solid var(--border-light);
        }
        .planner-table td:last-child {
            border-bottom: none;
        }
        .planner-table td::before {
            content: attr(data-label);
            position: absolute;
            top: 16px;
            left: 16px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-secondary);
        }
        .planner-table-links a,
        .planner-table-links button {
            flex: 1 1 140px;
        }
        .calendar-modal {
            padding: 12px;
        }
        .calendar-modal-panel {
            width: min(820px, calc(100vw - 16px));
            max-height: calc(100dvh - 16px);
            border-radius: 22px;
        }
        .calendar-modal-head,
        .calendar-modal-body {
            padding-left: 16px;
            padding-right: 16px;
        }
        .calendar-modal-item-top {
            flex-direction: column;
        }
        .calendar-modal-close {
            align-self: flex-start;
        }
    }
    @media (max-width: 560px) {
        .planner-brand,
        .planner-toolbar,
        .planner-panel,
        .planner-view-card {
            padding: 18px;
        }
        .planner-mini-grid {
            grid-template-columns: 1fr;
        }
        .planner-brand-actions {
            flex-direction: column;
        }
        .planner-brand-actions .btn,
        .calendar-modal-actions .btn {
            width: 100%;
        }
        .calendar-modal {
            padding: 0;
            align-items: flex-end;
        }
        .calendar-modal-panel {
            width: 100%;
            max-height: min(94dvh, 100dvh);
            border-radius: 24px 24px 0 0;
            border-bottom: none;
        }
        .calendar-modal-head {
            padding-top: 18px;
        }
        .calendar-modal-head::before {
            content: '';
            width: 48px;
            height: 5px;
            border-radius: 999px;
            background: #d7dee7;
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
        }
        .calendar-modal-body {
            padding-bottom: 28px;
        }
        .calendar-modal-item {
            padding: 16px;
            border-radius: 18px;
        }
        .planner-calendar-frame .fc .fc-daygrid-day-frame {
            min-height: 104px;
        }
        .planner-calendar-frame .fc .fc-button {
            padding: 8px 12px !important;
            font-size: 12px !important;
        }
        .planner-calendar-frame .fc .fc-toolbar-title {
            font-size: 18px;
        }
    }
</style>

<div class="planner-shell">
    <div class="planner-stage">
        <aside class="planner-sidebar">
            <section class="planner-card planner-brand">
                <div class="planner-chip">FS Poster Live Sync</div>
                <h3>Review WordPress FS Poster schedules from Laravel.</h3>
                <p>Laravel reads the same FS Poster schedules, channel customization payloads, and schedule meta used by WordPress. Condo listing schedules remain editable here, while WordPress post schedules stay visible so both sides reflect the same queue.</p>
                <div class="planner-brand-actions">
                    <a href="{{ route('social.create') }}" class="btn btn-primary">Add Schedule</a>
                    <a href="{{ rtrim(\App\Support\CondoWordpressBridge::siteBaseUrl(), '/') . '/wp-admin/admin.php?page=fs-poster#/calendar' }}" class="btn btn-secondary" target="_blank" rel="noreferrer">Open WP FS Poster</a>
                </div>
            </section>

            <section class="planner-card planner-panel">
                <div class="planner-panel-head">
                    <div>
                        <h4>Queue Snapshot</h4>
                        <div class="planner-subtle">Live totals from the current FS Poster schedules linked to your WordPress content and condo listings.</div>
                    </div>
                </div>
                <div class="planner-mini-grid">
                    <article class="planner-mini-stat">
                        <div class="planner-mini-label">Active Channels</div>
                        <div class="planner-mini-value">{{ $stats['channels'] }}</div>
                        <div class="planner-subtle">Connected in WordPress.</div>
                    </article>
                    <article class="planner-mini-stat">
                        <div class="planner-mini-label">Queued</div>
                        <div class="planner-mini-value">{{ $stats['scheduled'] }}</div>
                        <div class="planner-subtle">Waiting to publish.</div>
                    </article>
                    <article class="planner-mini-stat">
                        <div class="planner-mini-label">Sent</div>
                        <div class="planner-mini-value">{{ $stats['sent'] }}</div>
                        <div class="planner-subtle">Already posted out.</div>
                    </article>
                    <article class="planner-mini-stat">
                        <div class="planner-mini-label">Issues</div>
                        <div class="planner-mini-value">{{ $stats['issues'] }}</div>
                        <div class="planner-subtle">Failed or mixed groups.</div>
                    </article>
                </div>
            </section>

            <section class="planner-card planner-panel">
                <div class="planner-panel-head">
                    <div>
                        <h4>Upcoming Queue</h4>
                        <div class="planner-subtle">The next schedule groups that will hit FS Poster from this filtered view.</div>
                    </div>
                </div>
                @if($upcomingSchedules->count())
                    <div class="planner-upcoming-list">
                        @foreach($upcomingSchedules as $schedule)
                            <article class="planner-upcoming-item">
                                <div class="planner-upcoming-time">{{ $schedule['scheduled_at']->format('D, d M Y h:i A') }}</div>
                                <div class="planner-upcoming-title">{{ $schedule['listing_title'] }}</div>
                                <div class="planner-subtle">{{ $schedule['content_type_label'] }} - {{ implode(', ', array_map('strtoupper', $schedule['social_networks'])) }}</div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="planner-empty">
                        <p>No future schedules match this filter yet.</p>
                        <a href="{{ route('social.create') }}" class="btn btn-primary">Create One</a>
                    </div>
                @endif
            </section>

            <section class="planner-card planner-panel">
                <div class="planner-panel-head">
                    <div>
                        <h4>Channel Coverage</h4>
                        <div class="planner-subtle">Active destinations discovered from the live FS Poster WordPress setup.</div>
                    </div>
                </div>
                @if($networkSummary->count())
                    <div class="planner-channel-list">
                        @foreach($networkSummary as $network)
                            <article class="planner-channel-item">
                                <div>
                                    <div class="planner-channel-title">{{ $network['label'] }}</div>
                                    <div class="planner-subtle">{{ $network['count'] }} active channel{{ $network['count'] === 1 ? '' : 's' }}</div>
                                </div>
                                <div class="planner-channel-count">{{ $network['count'] }}</div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="planner-empty">
                        <p>No active FS Poster channels were found.</p>
                    </div>
                @endif
            </section>
        </aside>

        <div class="planner-main">
            <section class="planner-card planner-toolbar">
                <div class="planner-toolbar-row">
                    <div>
                        <div class="planner-eyebrow">Social Planner</div>
                        <h3>Calendar and table views are reading the same FS Poster schedule groups from WordPress.</h3>
                        <div class="planner-note">The calendar now stays in a month view so each day shows a cleaner summary. Tap any event to review it in a modal, and use the day "see more" link to open every item on that date without being redirected away.</div>
                    </div>
                    <div class="planner-toggle">
                        <a href="{{ route('social.index', $calendarQuery) }}" class="{{ $viewMode === 'calendar' ? 'active' : '' }}">Calendar</a>
                        <a href="{{ route('social.index', $tableQuery) }}" class="{{ $viewMode === 'table' ? 'active' : '' }}">Table</a>
                    </div>
                </div>

                <form method="GET" class="planner-filter-grid">
                    <input type="hidden" name="view" value="{{ $viewMode }}">
                    <input type="text" name="search" class="form-input" value="{{ $search }}" placeholder="Search content title or social message">

                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach(['scheduled' => 'Scheduled', 'draft' => 'Draft', 'success' => 'Sent', 'error' => 'Failed', 'mixed' => 'Mixed', 'sending' => 'Sending'] as $value => $label)
                            <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <select name="network" class="form-select">
                        <option value="">All networks</option>
                        @foreach($channels->pluck('social_network')->unique()->sort()->values() as $network)
                            <option value="{{ $network }}" @selected($networkFilter === $network)>{{ strtoupper($network) }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary">Apply</button>
                    <a href="{{ route('social.index', ['view' => $viewMode]) }}" class="btn btn-secondary">Reset</a>
                </form>
            </section>

            <section class="planner-card planner-view-card">
                <div class="planner-panel-head">
                    <div>
                        <h4>{{ $viewMode === 'calendar' ? 'Schedule Calendar' : 'Schedule Table' }}</h4>
                        <div class="planner-subtle">
                            @if($viewMode === 'calendar')
                                Each day shows the first four schedule groups only. Use the "see more" link to open the full list for that day inside the detail modal.
                            @else
                                The table below is the same dataset, just rendered as a cleaner management view for scanning, filtering, and opening the linked content.
                            @endif
                        </div>
                    </div>
                    <div class="planner-legend">
                        <span class="planner-pill status-scheduled">Queued</span>
                        <span class="planner-pill status-success">Sent</span>
                        <span class="planner-pill status-error">Failed</span>
                        <span class="planner-pill status-mixed">Mixed</span>
                    </div>
                </div>

                @if($posts->total())
                    @if($viewMode === 'calendar')
                        <div class="planner-calendar-frame">
                            <div id="social-calendar"></div>
                        </div>
                    @else
                        <div class="table-wrap">
                            <table class="planner-table">
                                <thead>
                                    <tr>
                                        <th>Content</th>
                                        <th>Scheduled For</th>
                                        <th>Status</th>
                                        <th>Networks</th>
                                        <th>Message</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($posts as $schedule)
                                        <tr>
                                            <td data-label="Content">
                                                <div class="planner-table-title">{{ $schedule['listing_title'] }}</div>
                                                <div class="planner-table-note">{{ $schedule['content_type_label'] }} #{{ $schedule['listing_id'] }} - {{ $schedule['total_channels'] }} channel{{ $schedule['total_channels'] === 1 ? '' : 's' }}</div>
                                            </td>
                                            <td data-label="Scheduled For">
                                                <div class="planner-table-title">{{ $schedule['scheduled_at']->format('D, d M Y') }}</div>
                                                <div class="planner-table-note">{{ $schedule['scheduled_at']->format('h:i A') }}</div>
                                            </td>
                                            <td data-label="Status">
                                                <span class="planner-status-tag status-{{ $schedule['status'] }}">{{ $schedule['status_label'] }}</span>
                                                @if($schedule['error_messages'] !== [])
                                                    <div class="planner-table-note" style="margin-top:10px;">{{ implode(' ', $schedule['error_messages']) }}</div>
                                                @endif
                                            </td>
                                            <td data-label="Networks">
                                                <div class="planner-network-list">
                                                    @foreach($schedule['social_networks'] as $network)
                                                        <span class="planner-network-tag">{{ strtoupper($network) }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td data-label="Message">
                                                <div class="planner-message-box">
                                                    {{ $schedule['message_preview'] !== '' ? $schedule['message_preview'] : 'Using the current WordPress FS Poster template for this schedule group.' }}
                                                </div>
                                            </td>
                                            <td data-label="Actions">
                                                <div class="planner-table-links">
                                                    @if($schedule['is_mutable'])
                                                        <a href="{{ route('social.edit', $schedule['group_id']) }}" class="btn btn-primary btn-sm">Edit</a>
                                                    @endif
                                                    <a href="{{ $schedule['view_url'] }}" class="btn btn-secondary btn-sm">{{ $schedule['content_type_label'] }}</a>
                                                    @if($schedule['can_manage_in_laravel'])
                                                        <form method="POST" action="{{ route('social.destroy', $schedule['group_id']) }}" onsubmit="return confirm('Remove this FS Poster schedule group?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination">{{ $posts->links('components.pagination') }}</div>
                    @endif
                @else
                    <div class="planner-empty">
                        <p>No schedules matched the current filters.</p>
                        <a href="{{ route('social.create') }}" class="btn btn-primary">Create Schedule</a>
                    </div>
                @endif
            </section>
        </div>
    </div>

    <div class="calendar-modal" id="calendar-schedule-modal" aria-hidden="true">
        <div class="calendar-modal-panel" role="dialog" aria-modal="true" aria-labelledby="calendar-schedule-modal-title">
            <div class="calendar-modal-head">
                <div>
                    <div class="calendar-modal-kicker">Schedule Details</div>
                    <h3 class="calendar-modal-title" id="calendar-schedule-modal-title" data-modal-title>Schedule details</h3>
                    <div class="calendar-modal-subtitle" data-modal-subtitle>Review the selected schedule group.</div>
                </div>
                <button type="button" class="calendar-modal-close" data-modal-close aria-label="Close schedule details">&times;</button>
            </div>
            <div class="calendar-modal-body" data-modal-body></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<script>
    (function () {
        var calendarEl = document.getElementById('social-calendar');
        var modalEl = document.getElementById('calendar-schedule-modal');
        var modalTitleEl = modalEl ? modalEl.querySelector('[data-modal-title]') : null;
        var modalSubtitleEl = modalEl ? modalEl.querySelector('[data-modal-subtitle]') : null;
        var modalBodyEl = modalEl ? modalEl.querySelector('[data-modal-body]') : null;
        var modalCloseButtons = modalEl ? modalEl.querySelectorAll('[data-modal-close]') : [];

        if (!calendarEl || typeof FullCalendar === 'undefined') {
            return;
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function openModal(title, subtitle, bodyHtml) {
            if (!modalEl || !modalTitleEl || !modalSubtitleEl || !modalBodyEl) {
                return;
            }

            modalTitleEl.textContent = title;
            modalSubtitleEl.textContent = subtitle;
            modalBodyEl.innerHTML = bodyHtml;
            modalEl.classList.add('open');
            modalEl.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            if (!modalEl || !modalBodyEl) {
                return;
            }

            modalEl.classList.remove('open');
            modalEl.setAttribute('aria-hidden', 'true');
            modalBodyEl.innerHTML = '';
            document.body.style.overflow = '';
        }

        function sameDay(dateA, dateB) {
            return dateA instanceof Date
                && dateB instanceof Date
                && dateA.getFullYear() === dateB.getFullYear()
                && dateA.getMonth() === dateB.getMonth()
                && dateA.getDate() === dateB.getDate();
        }

        function formatModalItem(event) {
            var props = event.extendedProps || {};
            var actions = [];
            var networks = Array.isArray(props.networks) ? props.networks : [];
            var errors = Array.isArray(props.error_messages) ? props.error_messages : [];
            var metaBits = [
                props.content_type_label || 'Schedule',
                networks.length ? networks.join(', ') : '',
                props.channels ? props.channels + ' channel' + (props.channels === 1 ? '' : 's') : ''
            ].filter(Boolean);

            if (props.edit_url) {
                actions.push('<a href="' + escapeHtml(props.edit_url) + '" class="btn btn-primary btn-sm">Edit Schedule</a>');
            }

            if (props.view_url) {
                actions.push('<a href="' + escapeHtml(props.view_url) + '" class="btn btn-secondary btn-sm">Open ' + escapeHtml(props.content_type_label || 'Content') + '</a>');
            }

            return ''
                + '<article class="calendar-modal-item">'
                + '  <div class="calendar-modal-item-top">'
                + '    <div>'
                + '      <div class="calendar-modal-item-time">' + escapeHtml(props.time_label || '') + '</div>'
                + '      <h4 class="calendar-modal-item-title">' + escapeHtml(event.title || 'Untitled schedule') + '</h4>'
                + '    </div>'
                + '    <span class="planner-status-tag status-' + escapeHtml(props.status || 'scheduled') + '">' + escapeHtml(props.status_label || 'Scheduled') + '</span>'
                + '  </div>'
                + '  <div class="calendar-modal-meta">' + escapeHtml(metaBits.join(' - ')) + '</div>'
                + (props.message_full ? '<div class="calendar-modal-message">' + escapeHtml(props.message_full) + '</div>' : '')
                + (errors.length ? '<div class="calendar-modal-error">' + escapeHtml(errors.join(' ')) + '</div>' : '')
                + (actions.length ? '<div class="calendar-modal-actions">' + actions.join('') + '</div>' : '')
                + '</article>';
        }

        function openEventModal(event) {
            var props = event.extendedProps || {};
            openModal(
                event.title || 'Schedule details',
                props.action_label || 'Review the selected schedule.',
                formatModalItem(event)
            );
        }

        function openDayModal(date, calendar) {
            var events = calendar.getEvents()
                .filter(function (event) {
                    return sameDay(event.start, date);
                })
                .sort(function (left, right) {
                    return left.start - right.start;
                });

            var label = new Intl.DateTimeFormat('en-MY', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }).format(date);

            if (!events.length) {
                openModal(
                    label,
                    'No schedules were found for this day.',
                    '<article class="calendar-modal-item"><div class="calendar-modal-meta">There are no FS Poster schedule groups on this date.</div></article>'
                );
                return;
            }

            openModal(
                label,
                events.length + ' schedule group' + (events.length === 1 ? '' : 's') + ' on this day.',
                events.map(formatModalItem).join('')
            );
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            buttonText: {
                today: 'Today',
                month: 'Month'
            },
            firstDay: 1,
            fixedWeekCount: false,
            height: 'auto',
            dayMaxEvents: 4,
            dayMaxEventRows: 4,
            events: @json($calendarEvents),
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            moreLinkContent: function (args) {
                return {
                    html: 'See ' + args.num + ' more'
                };
            },
            eventContent: function (info) {
                var props = info.event.extendedProps || {};
                var wrapper = document.createElement('div');
                wrapper.className = 'calendar-chip schedule-status-' + (props.status || 'scheduled');

                var top = document.createElement('div');
                top.className = 'calendar-chip-top';

                var title = document.createElement('div');
                title.className = 'calendar-chip-title';
                title.textContent = info.event.title;

                var time = document.createElement('div');
                time.className = 'calendar-chip-time';
                time.textContent = info.timeText || '';

                var meta = document.createElement('div');
                meta.className = 'calendar-chip-meta';
                meta.textContent = (props.content_type_label || 'Schedule') + ((props.networks || []).length ? ' - ' + (props.networks || []).join(', ') : '');

                top.appendChild(title);
                top.appendChild(time);
                wrapper.appendChild(top);

                if (meta.textContent.trim() !== '') {
                    wrapper.appendChild(meta);
                }

                return { domNodes: [wrapper] };
            },
            eventDidMount: function (info) {
                var props = info.event.extendedProps || {};
                var tooltipParts = [
                    props.time_label || '',
                    props.status_label || '',
                    (props.networks || []).join(', '),
                    props.message || ''
                ].filter(Boolean);

                if (tooltipParts.length) {
                    info.el.title = tooltipParts.join('\n');
                }
            },
            moreLinkDidMount: function (arg) {
                arg.el.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    openDayModal(arg.date, calendar);
                });
            },
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                openEventModal(info.event);
            }
        });

        calendar.render();

        modalCloseButtons.forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        if (modalEl) {
            modalEl.addEventListener('click', function (event) {
                if (event.target === modalEl) {
                    closeModal();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modalEl && modalEl.classList.contains('open')) {
                closeModal();
            }
        });
    }());
</script>
@endsection
