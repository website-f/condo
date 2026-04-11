@extends('layouts.app')

@section('title', 'Manage Channels')
@section('page-title', 'Social Channels')

@section('topbar-actions')
    <div class="btn-group">
        <a href="{{ route('social.accounts.create') }}" class="btn btn-secondary btn-sm">Add Account</a>
        <a href="{{ route('social.channels.create') }}" class="btn btn-secondary btn-sm">Add Channel</a>
        <a href="{{ route('social.create') }}" class="btn btn-primary btn-sm">New Schedule</a>
    </div>
@endsection

@section('content')
<style>
    .channel-shell{display:grid;gap:24px}
    .channel-hero,.channel-card{background:var(--card-bg);border:1px solid var(--border-light);border-radius:28px;box-shadow:var(--shadow-sm)}
    .channel-hero{padding:28px;background:radial-gradient(circle at top right,rgba(184,215,255,.34),transparent 32%),linear-gradient(180deg,#fff 0%,#f8fafc 100%)}
    .channel-kicker{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px}
    .channel-hero h3{font-size:clamp(28px,3vw,40px);line-height:1.02;letter-spacing:-.04em;margin-bottom:12px}
    .channel-hero p,.channel-note,.channel-meta,.channel-empty{color:var(--text-secondary);line-height:1.65}
    .channel-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
    .channel-stat{padding:22px;border-radius:24px;background:linear-gradient(180deg,#fff 0%,#fbfbfd 100%);border:1px solid var(--border-light)}
    .channel-stat-label{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px}
    .channel-stat-value{font-size:32px;line-height:1;font-weight:700;letter-spacing:-.05em}
    .channel-grid{display:grid;gap:16px}
    .channel-grid.accounts{grid-template-columns:repeat(auto-fit,minmax(260px,1fr))}
    .channel-grid.channels{grid-template-columns:repeat(auto-fit,minmax(320px,1fr))}
    .channel-card{padding:22px}
    .channel-card-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:16px}
    .channel-card-title{font-size:18px;line-height:1.1;letter-spacing:-.02em}
    .channel-chip{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;background:#f3f4f8;border:1px solid var(--border-light);font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--text-secondary)}
    .channel-stack{display:grid;gap:10px}
    .channel-row{display:flex;justify-content:space-between;gap:14px;font-size:13px}
    .channel-row span{color:var(--text-secondary)}
    .channel-row strong{color:var(--text);text-align:right;word-break:break-word}
    .channel-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
    .channel-badges{display:flex;gap:8px;flex-wrap:wrap}
    .channel-badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
    .channel-badge.live{background:#e4f7ea;color:#18794e}
    .channel-badge.off{background:#fff3dc;color:#8a5b00}
    .channel-badge.archived{background:#ffe9e6;color:#c0392b}
    .channel-badge.auto{background:#eef2ff;color:#3730a3}
    .channel-inline-note{margin-top:14px;padding:14px 16px;border-radius:18px;border:1px solid var(--border-light);background:#f8fafc;font-size:13px;color:var(--text-secondary);line-height:1.6}
    .channel-section-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-end;flex-wrap:wrap}
    .channel-section-head h4{font-size:22px;line-height:1.1;letter-spacing:-.02em}
    .channel-empty{padding:28px;border:1px dashed var(--border);border-radius:24px;background:#fcfcfd}
    @media (max-width:960px){.channel-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width:640px){.channel-stats{grid-template-columns:1fr}.channel-card-head,.channel-row,.channel-section-head{display:grid;gap:10px}.channel-row strong{text-align:left}}
</style>

<div class="channel-shell">
    <section class="channel-hero">
        <div class="channel-kicker">FS Poster Sync</div>
        <h3>Manage the same live accounts and channels that WordPress FS Poster uses.</h3>
        <p>The schedule form only shows active channels. This manager is where we add or edit the underlying FS Poster account sessions and channel records, so Laravel and WordPress stay on the same data source.</p>
    </section>

    <section class="channel-stats">
        <article class="channel-stat">
            <div class="channel-stat-label">Connected Accounts</div>
            <div class="channel-stat-value">{{ $stats['accounts'] }}</div>
        </article>
        <article class="channel-stat">
            <div class="channel-stat-label">Visible Channels</div>
            <div class="channel-stat-value">{{ $stats['channels'] }}</div>
        </article>
        <article class="channel-stat">
            <div class="channel-stat-label">Active Now</div>
            <div class="channel-stat-value">{{ $stats['active'] }}</div>
        </article>
        <article class="channel-stat">
            <div class="channel-stat-label">Archived</div>
            <div class="channel-stat-value">{{ $stats['archived'] }}</div>
        </article>
    </section>

    <section class="channel-stack">
        <div class="channel-section-head">
            <div>
                <h4>Connected Accounts</h4>
                <div class="channel-note">These are the FS Poster account sessions. Editing them updates the same `cd_fsp_channel_sessions` rows WordPress uses.</div>
            </div>
            <a href="{{ route('social.accounts.create') }}" class="btn btn-secondary">Add Account</a>
        </div>

        @if($accounts->isEmpty())
            <div class="channel-empty">
                No FS Poster accounts are connected for this agent yet. Add an account record first, then create channels under it.
            </div>
        @else
            <div class="channel-grid accounts">
                @foreach($accounts as $account)
                    <article class="channel-card">
                        <div class="channel-card-head">
                            <div>
                                <div class="channel-chip">{{ $account['social_network_label'] }}</div>
                                <div class="channel-card-title" style="margin-top:10px;">{{ $account['name'] }}</div>
                                <div class="channel-meta" style="margin-top:6px;">{{ strtoupper($account['method']) }} account session</div>
                            </div>
                            <div class="channel-badges">
                                <span class="channel-badge live">{{ $account['active_channels'] }} active</span>
                            </div>
                        </div>

                        <div class="channel-stack">
                            <div class="channel-row"><span>Remote ID</span><strong>{{ $account['remote_id'] }}</strong></div>
                            <div class="channel-row"><span>Proxy</span><strong>{{ $account['proxy'] !== '' ? $account['proxy'] : 'No proxy' }}</strong></div>
                            <div class="channel-row"><span>Total channels</span><strong>{{ $account['total_channels'] }}</strong></div>
                            <div class="channel-row"><span>Inactive channels</span><strong>{{ $account['inactive_channels'] }}</strong></div>
                        </div>

                        <div class="channel-actions">
                            <a href="{{ route('social.accounts.edit', $account['id']) }}" class="btn btn-secondary btn-sm">Edit Account</a>
                            <a href="{{ route('social.channels.create', ['session' => $account['id']]) }}" class="btn btn-primary btn-sm">Add Channel</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="channel-stack">
        <div class="channel-section-head">
            <div>
                <h4>Channels</h4>
                <div class="channel-note">These records feed the checkbox list inside the schedule composer. Active, non-archived channels are the ones agents can queue posts to.</div>
            </div>
            <a href="{{ route('social.channels.create') }}" class="btn btn-secondary">Add Channel</a>
        </div>

        @if($channels->isEmpty())
            <div class="channel-empty">
                No channels are available yet for this agent.
            </div>
        @else
            <div class="channel-grid channels">
                @foreach($channels as $channel)
                    <article class="channel-card">
                        <div class="channel-card-head">
                            <div>
                                <div class="channel-chip">{{ $channel['social_network_label'] }}</div>
                                <div class="channel-card-title" style="margin-top:10px;">{{ $channel['name'] }}</div>
                                <div class="channel-meta" style="margin-top:6px;">{{ $channel['session_name'] }} · {{ str_replace('_', ' ', $channel['channel_type']) }}</div>
                            </div>
                            <div class="channel-badges">
                                @if($channel['is_deleted'])
                                    <span class="channel-badge archived">Archived</span>
                                @elseif($channel['status'])
                                    <span class="channel-badge live">Live</span>
                                @else
                                    <span class="channel-badge off">Disabled</span>
                                @endif
                                @if($channel['auto_share'])
                                    <span class="channel-badge auto">Auto-share</span>
                                @endif
                            </div>
                        </div>

                        <div class="channel-stack">
                            <div class="channel-row"><span>Remote ID</span><strong>{{ $channel['remote_id'] }}</strong></div>
                            <div class="channel-row"><span>Method</span><strong>{{ strtoupper($channel['session_method']) }}</strong></div>
                            <div class="channel-row"><span>Proxy</span><strong>{{ $channel['proxy'] !== '' ? $channel['proxy'] : 'No proxy' }}</strong></div>
                            <div class="channel-row"><span>Labels</span><strong>{{ $channel['label_count'] }}</strong></div>
                            <div class="channel-row"><span>Permissions</span><strong>{{ $channel['permission_count'] }}</strong></div>
                        </div>

                        <div class="channel-inline-note">
                            Editing this card changes the same FS Poster channel row WordPress reads. The account name above comes from the linked account session, not from a Laravel-only field.
                        </div>

                        <div class="channel-actions">
                            <a href="{{ route('social.channels.edit', $channel['id']) }}" class="btn btn-secondary btn-sm">Edit Channel</a>
                            <form action="{{ route('social.channels.destroy', $channel['id']) }}" method="POST" onsubmit="return confirm('Delete this FS Poster channel? Existing sent history may keep it archived instead of fully removing it.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
