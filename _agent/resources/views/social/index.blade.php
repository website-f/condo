@extends('layouts.app')

@section('title', 'Social Media')
@section('page-title', 'Social Scheduler')

@section('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css">
@endsection

@section('topbar-actions')
    <div class="btn-group">
        <a href="{{ route('social.channels.index') }}" class="btn btn-secondary btn-sm">Manage Channels</a>
        <a href="{{ route('social.create') }}" class="btn btn-primary btn-sm">New Schedule</a>
    </div>
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
    $channelPreview = $networkSummary->take(4);
    $channelOverflow = $networkSummary->slice(4)->values();
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
        grid-template-columns: repeat(3, minmax(0, 1fr));
        align-items: stretch;
    }
    .planner-sidebar > :first-child {
        grid-column: 1 / -1;
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
    .planner-panel-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .planner-panel {
        padding: 22px;
        height: 100%;
        display: flex;
        flex-direction: column;
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
        align-content: start;
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
    .planner-inline-more {
        margin-top: 12px;
        border: 1px solid var(--border-light);
        border-radius: 18px;
        background: #f9fbfd;
        overflow: hidden;
    }
    .planner-inline-more summary {
        list-style: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
    }
    .planner-inline-more summary::-webkit-details-marker {
        display: none;
    }
    .planner-inline-more summary::after {
        content: 'See more';
        color: #335c89;
        font-size: 12px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .planner-inline-more[open] summary::after {
        content: 'See less';
    }
    .planner-inline-more-body {
        display: grid;
        gap: 12px;
        padding: 0 16px 16px;
        border-top: 1px solid var(--border-light);
        background: #fff;
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
    }
    .planner-calendar-scroll {
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .planner-calendar-scroll::-webkit-scrollbar {
        height: 6px;
    }
    .planner-calendar-scroll::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.02);
        border-radius: 6px;
    }
    .planner-calendar-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 6px;
    }
    .planner-calendar-scroll #social-calendar {
        min-width: 800px;
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
        overflow: hidden !important;
        max-width: 100% !important;
        min-width: 0 !important;
    }
    .planner-calendar-frame .fc .fc-event {
        background: transparent !important;
        border: none !important;
        margin: 0 4px !important;
        overflow: hidden !important;
        max-width: 100% !important;
        min-width: 0 !important;
    }
    .planner-calendar-frame .fc .fc-daygrid-more-link {
        display: inline-block;
        margin: 6px auto 10px;
        font-size: 11px;
        font-weight: 700;
        color: #0f172a;
        text-decoration: none;
        background: transparent;
        border: none;
        text-align: center;
        width: 100%;
    }
    .planner-calendar-frame .fc .fc-daygrid-more-link:hover {
        text-decoration: underline;
    }
    .calendar-chip {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 6px;
        border-radius: 8px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        border: 1.5px solid;
        box-shadow: 0 -3px 0 0 #fff, 0 -4px 0 0 #cbd5e1, 0 -6px 0 0 #fff, 0 -7px 0 0 #cbd5e1;
        margin-top: 8px;
        margin-bottom: 2px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
        overflow: hidden; /* Force clipping */
    }
    .calendar-chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 -2px 0 0 #fff, 0 -3px 0 0 #cbd5e1, 0 -4px 0 0 #fff, 0 -5px 0 0 #cbd5e1, 0 8px 12px rgba(15, 23, 42, 0.1);
    }
    .calendar-chip.schedule-status-scheduled { border-color: #cbd5e1; }
    .calendar-chip.schedule-status-success { border-color: #1ed760; }
    .calendar-chip.schedule-status-error { border-color: #ff3333; }
    .calendar-chip.schedule-status-mixed { border-color: #a855f7; }
    .calendar-chip.schedule-status-sending { border-color: #3b82f6; }
    
    .calendar-chip-thumbnail {
        height: 54px;
        border-radius: 4px;
        background: #f1f5f9;
        overflow: hidden;
        position: relative;
    }
    .calendar-chip-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .calendar-chip-thumbnail-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .calendar-chip-title {
        font-size: 11px;
        font-weight: 700;
        line-height: 1.25;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 0 4px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        display: block;
        box-sizing: border-box;
    }
    .calendar-chip-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 2px;
        min-width: 0;
    }
    .calendar-chip-time {
        font-size: 9px;
        font-weight: 700;
        color: inherit;
        display: flex;
        align-items: center;
        gap: 3px;
    }
    .calendar-chip.schedule-status-success .calendar-chip-time { color: #1ed760; }
    .calendar-chip.schedule-status-error .calendar-chip-time { color: #ff3333; }
    .calendar-chip.schedule-status-scheduled .calendar-chip-time,
    .calendar-chip.schedule-status-sending .calendar-chip-time,
    .calendar-chip.schedule-status-mixed .calendar-chip-time { color: #64748b; }
    
    .calendar-chip-networks {
        display: flex;
        align-items: center;
    }
    .calendar-chip-network-icon {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8px;
        font-weight: 800;
        color: #0f172a;
        margin-left: -6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        z-index: 1;
    }
    .calendar-chip-network-icon:first-child {
        margin-left: 0;
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
        display: flex;
        gap: 16px;
        align-items: flex-start;
        min-width: 0;
    }
    .calendar-modal-image {
        width: 100px;
        height: 100px;
        border-radius: 12px;
        overflow: hidden;
        background: #f1f5f9;
        flex-shrink: 0;
    }
    .calendar-modal-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .calendar-modal-content {
        flex: 1;
        min-width: 0;
        display: grid;
        gap: 10px;
    }
    .calendar-modal-item-top {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
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
    .calendar-message-layout {
        display: grid;
        gap: 14px;
    }
    .calendar-message-tags,
    .calendar-message-links {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .calendar-message-tag {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #eef2ff;
        border: 1px solid #dbe4ff;
        color: #3f4b7a;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.2;
    }
    .calendar-message-copy {
        display: grid;
        gap: 10px;
    }
    .calendar-message-copy p,
    .calendar-message-notes p {
        margin: 0;
        white-space: pre-wrap;
    }
    .calendar-message-lead {
        font-size: 15px;
        font-weight: 600;
        line-height: 1.7;
        color: #172033;
    }
    .calendar-message-bullets {
        margin: 0;
        padding-left: 18px;
        display: grid;
        gap: 6px;
    }
    .calendar-message-bullets li {
        line-height: 1.6;
    }
    .calendar-message-facts {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin: 0;
    }
    .calendar-message-fact {
        padding: 12px 14px;
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid var(--border-light);
        min-width: 0;
    }
    .calendar-message-fact dt {
        margin: 0 0 4px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-secondary);
    }
    .calendar-message-fact dd {
        margin: 0;
        font-weight: 600;
        line-height: 1.55;
        color: var(--text);
        overflow-wrap: anywhere;
    }
    .calendar-message-notes {
        display: grid;
        gap: 8px;
    }
    .calendar-message-note {
        padding: 12px 14px;
        border-radius: 16px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        font-weight: 600;
    }
    .calendar-message-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 0 14px;
        border-radius: 999px;
        background: #ffffff;
        border: 1px solid #c7d2fe;
        color: #374151;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
    }
    .calendar-message-link:hover {
        background: #eef2ff;
        text-decoration: none;
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
        .planner-sidebar > :first-child {
            grid-column: 1 / -1;
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
        /* calendar chips retain design on mobile due to horizontal scroll wrapper */
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
        .planner-inline-more summary {
            flex-direction: column;
            align-items: flex-start;
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
            flex-direction: column;
            align-items: stretch;
            padding: 16px;
            border-radius: 18px;
        }
        .calendar-modal-image {
            width: 100%;
            height: 160px;
        }
        .calendar-message-facts {
            grid-template-columns: 1fr;
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
                <div class="planner-chip">Agent Social Queue</div>
                <h3>Manage your live social schedule without leaving the agent portal.</h3>
                <p>This page shows only the channels and schedule groups that can be tied back to the current agent. Condo listing schedules stay editable here, while anything without clear ownership stays hidden.</p>
                <div class="planner-brand-actions">
                    <a href="{{ route('social.channels.create') }}" class="btn btn-secondary">Add Channel</a>
                    <a href="{{ route('social.create') }}" class="btn btn-primary">Add Schedule</a>
                </div>
            </section>

            <section class="planner-card planner-panel">
                <div class="planner-panel-head">
                    <div>
                        <h4>Queue Snapshot</h4>
                        <div class="planner-subtle">Live totals from the current agent-owned schedules and channels visible in this portal.</div>
                    </div>
                </div>
                <div class="planner-mini-grid">
                    <article class="planner-mini-stat">
                        <div class="planner-mini-label">Active Channels</div>
                        <div class="planner-mini-value">{{ $stats['channels'] }}</div>
                        <div class="planner-subtle">Available to this agent.</div>
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
                        <div class="planner-subtle">The next schedule groups queued from this filtered view.</div>
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
                        <div class="planner-subtle">Active destinations currently linked to this agent.</div>
                    </div>
                    <div class="planner-panel-actions">
                        <a href="{{ route('social.channels.create') }}" class="btn btn-secondary btn-sm">Add Channel</a>
                    </div>
                </div>
                @if($networkSummary->count())
                    <div class="planner-channel-list">
                        @foreach($channelPreview as $network)
                            <article class="planner-channel-item">
                                <div>
                                    <div class="planner-channel-title">{{ $network['label'] }}</div>
                                    <div class="planner-subtle">{{ $network['count'] }} active channel{{ $network['count'] === 1 ? '' : 's' }}</div>
                                </div>
                                <div class="planner-channel-count">{{ $network['count'] }}</div>
                            </article>
                        @endforeach
                    </div>
                    @if($channelOverflow->isNotEmpty())
                        <details class="planner-inline-more">
                            <summary>{{ $channelOverflow->count() }} more network{{ $channelOverflow->count() === 1 ? '' : 's' }}</summary>
                            <div class="planner-inline-more-body">
                                @foreach($channelOverflow as $network)
                                    <article class="planner-channel-item">
                                        <div>
                                            <div class="planner-channel-title">{{ $network['label'] }}</div>
                                            <div class="planner-subtle">{{ $network['count'] }} active channel{{ $network['count'] === 1 ? '' : 's' }}</div>
                                        </div>
                                        <div class="planner-channel-count">{{ $network['count'] }}</div>
                                    </article>
                                @endforeach
                            </div>
                        </details>
                    @endif
                @else
                    <div class="planner-empty">
                        <p>No active social channels were found for this agent yet.</p>
                        <div class="planner-brand-actions" style="margin-top: 16px;">
                            <a href="{{ route('social.channels.create') }}" class="btn btn-secondary">Add Channel</a>
                        </div>
                    </div>
                @endif
            </section>
        </aside>

        <div class="planner-main">
            <section class="planner-card planner-toolbar">
                <div class="planner-toolbar-row">
                    <div>
                        <div class="planner-eyebrow">Social Planner</div>
                        <h3>Calendar and table views are reading the same live schedule groups used by this portal.</h3>
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
                            <div class="planner-calendar-scroll">
                                <div id="social-calendar"></div>
                            </div>
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
                                                    {{ $schedule['message_preview'] !== '' ? $schedule['message_preview'] : 'Using the saved channel template for this schedule group.' }}
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

        function escapeRegExp(value) {
            return String(value || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function uniqueStrings(values) {
            var seen = Object.create(null);

            return (Array.isArray(values) ? values : [])
                .map(function (value) {
                    return String(value || '').trim();
                })
                .filter(function (value) {
                    if (!value || seen[value]) {
                        return false;
                    }

                    seen[value] = true;

                    return true;
                });
        }

        function parseScheduleMessage(title, message) {
            var text = String(message || '').replace(/\r\n?/g, '\n').trim();
            var titleText = String(title || '').trim();

            if (!text) {
                return {
                    paragraphs: [],
                    bullets: [],
                    facts: [],
                    notes: [],
                    tags: [],
                    links: []
                };
            }

            if (titleText) {
                text = text.replace(new RegExp('^' + escapeRegExp(titleText) + '\\s*', 'i'), '');
            }

            var links = uniqueStrings(text.match(/https?:\/\/[^\s<>"']+/g) || []);
            links.forEach(function (link) {
                text = text.split(link).join(' ');
            });

            var tags = uniqueStrings(text.match(/#[^\s#]+/g) || []);
            tags.forEach(function (tag) {
                text = text.split(tag).join(' ');
            });

            text = text
                .replace(/\s*={2,}\s*/g, '\n')
                .replace(/\s*=\s*(?=(Owner asking|For more info & viewing arrangement:|Whatsapp me now|View more photo|Show more photo|About this agent))/gi, '\n')
                .replace(/\s*\|\s*/g, ' | ')
                .replace(/Owner asking\s+(?=RM|MYR|USD)/gi, 'Owner asking: ')
                .replace(/For More Info & Viewing Arrangement,?/gi, 'For more info & viewing arrangement:')
                .replace(/\s+(?=(Size:|Bedroom:|Bathroom:|Carpark:|Condition:|Owner asking:|Location:|Built Up\s*:|Land Area\s*:|Agent name\s*:|Contact No\s*:|Agency\s*:|Tenure\s*:|Furnishing\s*:|Property Type\s*:|For more info & viewing arrangement:|Whatsapp me now|View more photo|Show more photo|About this agent))/gi, '\n')
                .replace(/(\S)(?=(Location:|Built Up\s*:|Land Area\s*:|Agent name\s*:|Contact No\s*:|Agency\s*:))/gi, '$1\n')
                .replace(/(\S)(?=(Whatsapp me now|View more photo|Show more photo|About this agent))/gi, '$1\n')
                .replace(/[ \t]+/g, ' ')
                .replace(/ *\n */g, '\n')
                .replace(/\n{3,}/g, '\n\n')
                .trim();

            var lines = text.split('\n').map(function (line) {
                return line.trim();
            }).filter(Boolean);

            var paragraphs = [];
            var bullets = [];
            var facts = [];
            var notes = [];

            lines.forEach(function (line) {
                if (!line) {
                    return;
                }

                if (/^[-*•]\s*/.test(line)) {
                    bullets.push(line.replace(/^[-*•]\s*/, '').trim());
                    return;
                }

                var factMatch = line.match(/^([A-Za-z][A-Za-z0-9 &/()'-]+?)\s*:\s*(.+)$/);

                if (factMatch) {
                    facts.push({
                        label: factMatch[1].trim(),
                        value: factMatch[2].replace(/[=|]+$/g, '').trim()
                    });
                    return;
                }

                if (/(for more info|whatsapp me now|view more photo|show more photo|about this agent|call\b)/i.test(line)) {
                    notes.push(line);
                    return;
                }

                paragraphs.push(line);
            });

            return {
                paragraphs: paragraphs,
                bullets: bullets,
                facts: facts,
                notes: notes,
                tags: tags,
                links: links
            };
        }

        function renderScheduleMessage(title, message) {
            var parsed = parseScheduleMessage(title, message);
            var parts = [];

            if (parsed.tags.length) {
                parts.push(
                    '<div class="calendar-message-tags">'
                    + parsed.tags.map(function (tag) {
                        return '<span class="calendar-message-tag">' + escapeHtml(tag) + '</span>';
                    }).join('')
                    + '</div>'
                );
            }

            if (parsed.paragraphs.length) {
                parts.push(
                    '<div class="calendar-message-copy">'
                    + parsed.paragraphs.map(function (paragraph, index) {
                        return '<p' + (index === 0 ? ' class="calendar-message-lead"' : '') + '>' + escapeHtml(paragraph) + '</p>';
                    }).join('')
                    + '</div>'
                );
            }

            if (parsed.bullets.length) {
                parts.push(
                    '<ul class="calendar-message-bullets">'
                    + parsed.bullets.map(function (item) {
                        return '<li>' + escapeHtml(item) + '</li>';
                    }).join('')
                    + '</ul>'
                );
            }

            if (parsed.facts.length) {
                parts.push(
                    '<dl class="calendar-message-facts">'
                    + parsed.facts.map(function (fact) {
                        return ''
                            + '<div class="calendar-message-fact">'
                            + '  <dt>' + escapeHtml(fact.label) + '</dt>'
                            + '  <dd>' + escapeHtml(fact.value) + '</dd>'
                            + '</div>';
                    }).join('')
                    + '</dl>'
                );
            }

            if (parsed.notes.length) {
                parts.push(
                    '<div class="calendar-message-notes">'
                    + parsed.notes.map(function (note) {
                        return '<p class="calendar-message-note">' + escapeHtml(note) + '</p>';
                    }).join('')
                    + '</div>'
                );
            }

            if (parsed.links.length) {
                parts.push(
                    '<div class="calendar-message-links">'
                    + parsed.links.map(function (link, index) {
                        return '<a class="calendar-message-link" href="' + escapeHtml(link) + '" target="_blank" rel="noopener noreferrer">'
                            + escapeHtml(index === 0 ? 'Open public page' : 'Open link ' + (index + 1))
                            + '</a>';
                    }).join('')
                    + '</div>'
                );
            }

            if (!parts.length) {
                return '<div class="calendar-message-copy"><p>' + escapeHtml(message) + '</p></div>';
            }

            return '<div class="calendar-message-layout">' + parts.join('') + '</div>';
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

            var imageHtml = '';
            if (props.image_url) {
                imageHtml = '<div class="calendar-modal-image"><img src="' + escapeHtml(props.image_url) + '" alt=""></div>';
            }

            return ''
                + '<article class="calendar-modal-item">'
                + imageHtml
                + '  <div class="calendar-modal-content">'
                + '    <div class="calendar-modal-item-top">'
                + '      <div>'
                + '        <div class="calendar-modal-item-time">' + escapeHtml(props.time_label || '') + '</div>'
                + '        <h4 class="calendar-modal-item-title">' + escapeHtml(event.title || 'Untitled schedule') + '</h4>'
                + '      </div>'
                + '      <span class="planner-status-tag status-' + escapeHtml(props.status || 'scheduled') + '">' + escapeHtml(props.status_label || 'Scheduled') + '</span>'
                + '    </div>'
                + '    <div class="calendar-modal-meta">' + escapeHtml(metaBits.join(' - ')) + '</div>'
                + (props.message_full ? '<div class="calendar-modal-message">' + renderScheduleMessage(event.title || '', props.message_full) + '</div>' : '')
                + (errors.length ? '<div class="calendar-modal-error">' + escapeHtml(errors.join(' ')) + '</div>' : '')
                + (actions.length ? '<div class="calendar-modal-actions">' + actions.join('') + '</div>' : '')
                + '  </div>'
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

        function openDayModal(date, events) {
            events.sort(function (left, right) {
                return (left.start || 0) - (right.start || 0);
            });

            var label = new Intl.DateTimeFormat('en-US', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }).format(date);

            if (!events || !events.length) {
                openModal(
                    label,
                    'No schedules were found for this day.',
                    '<article class="calendar-modal-item"><div class="calendar-modal-meta">There are no schedule groups on this date.</div></article>'
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
            firstDay: 0,
            fixedWeekCount: false,
            height: 'auto',
            dayMaxEvents: 4,
            dayMaxEventRows: 4,
            eventDisplay: 'block',
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

                if (props.image_url) {
                    var thumbnail = document.createElement('div');
                    thumbnail.className = 'calendar-chip-thumbnail';
                    thumbnail.innerHTML = '<img src="' + escapeHtml(props.image_url) + '" alt="">';
                    wrapper.appendChild(thumbnail);
                } else {
                    var thumbnail = document.createElement('div');
                    thumbnail.className = 'calendar-chip-thumbnail';
                    thumbnail.innerHTML = '<div class="calendar-chip-thumbnail-placeholder">No Image</div>';
                    wrapper.appendChild(thumbnail);
                }

                var title = document.createElement('div');
                title.className = 'calendar-chip-title';
                title.textContent = info.event.title;

                var bottomRow = document.createElement('div');
                bottomRow.className = 'calendar-chip-bottom';

                var time = document.createElement('div');
                time.className = 'calendar-chip-time';
                time.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 10px; height: 10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span>' + escapeHtml(info.timeText || '') + '</span>';

                var networks = document.createElement('div');
                networks.className = 'calendar-chip-networks';
                
                var showCount = 2;
                var netList = props.networks || [];
                var displayNets = netList.slice(0, showCount);
                displayNets.forEach(function(net) {
                    var icon = document.createElement('div');
                    icon.className = 'calendar-chip-network-icon';
                    icon.textContent = net.substring(0, 1).toUpperCase();
                    icon.title = net;
                    networks.appendChild(icon);
                });
                if (netList.length > showCount) {
                    var plus = document.createElement('div');
                    plus.className = 'calendar-chip-network-icon';
                    plus.textContent = '+' + (netList.length - showCount);
                    networks.appendChild(plus);
                }

                bottomRow.appendChild(time);
                bottomRow.appendChild(networks);

                wrapper.appendChild(title);
                wrapper.appendChild(bottomRow);

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
            moreLinkClick: function (arg) {
                if (arg.jsEvent) {
                    arg.jsEvent.preventDefault();
                    arg.jsEvent.stopPropagation();
                }
                var events = [];
                if (arg.allSegs) {
                    events = arg.allSegs.map(function(seg) { return seg.event; });
                }
                openDayModal(arg.date, events);
                return 'none';
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
