@php
    $socialActiveTab = $socialActiveTab ?? 'calendar';
@endphp

<style>
    .fsp-nav {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .fsp-nav-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 16px;
        border-radius: 14px;
        border: 1px solid var(--border-light);
        background: #fff;
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        box-shadow: var(--shadow-sm);
    }
    .fsp-nav-link.active {
        color: var(--text);
        background: #f3f4f8;
        border-color: rgba(29, 29, 31, 0.12);
    }
    .fsp-nav-link.is-muted {
        opacity: 0.52;
        cursor: default;
    }
    @media (max-width: 640px) {
        .fsp-nav {
            gap: 8px;
        }
        .fsp-nav-link {
            padding: 0 14px;
            min-height: 40px;
            font-size: 13px;
        }
    }
</style>

<nav class="fsp-nav" aria-label="Social navigation">
    <a href="{{ route('social.index') }}" class="fsp-nav-link {{ $socialActiveTab === 'calendar' ? 'active' : '' }}">Calendar</a>
    <span class="fsp-nav-link is-muted">Analytics</span>
    <a href="{{ route('social.channels.index') }}" class="fsp-nav-link {{ $socialActiveTab === 'channels' ? 'active' : '' }}">Channels</a>
    <span class="fsp-nav-link is-muted">Planners</span>
    <span class="fsp-nav-link is-muted">Settings</span>
</nav>
