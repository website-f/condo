@extends('layouts.app')

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('topbar-actions')
    <div class="btn-group">
        <a href="{{ route('social.channels.index') }}" class="btn btn-secondary btn-sm">Manage Channels</a>
        <a href="{{ route('social.create') }}" class="btn btn-primary btn-sm">New Schedule</a>
    </div>
@endsection

@section('content')
<style>
    .channel-form-shell{display:grid;gap:24px}
    .channel-form-hero,.channel-form-card{background:var(--card-bg);border:1px solid var(--border-light);border-radius:28px;box-shadow:var(--shadow-sm)}
    .channel-form-hero,.channel-form-card{padding:28px}
    .channel-form-hero{background:radial-gradient(circle at top right,rgba(182,215,168,.28),transparent 34%),linear-gradient(180deg,#fff 0%,#f8fafc 100%)}
    .channel-form-kicker{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px}
    .channel-form-hero h3{font-size:clamp(28px,3vw,40px);line-height:1.02;letter-spacing:-.04em;margin-bottom:12px}
    .channel-form-hero p,.channel-form-note,.channel-form-hint{color:var(--text-secondary);line-height:1.65}
    .channel-form-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(320px,.85fr);gap:24px}
    .channel-form-side{display:grid;gap:16px;align-content:start}
    .channel-form-panel{padding:18px;border:1px solid var(--border-light);border-radius:20px;background:#f8fafc}
    .channel-form-panel h4{font-size:16px;line-height:1.15;margin-bottom:10px}
    .channel-form-row{display:flex;justify-content:space-between;gap:12px;font-size:13px;margin-bottom:10px}
    .channel-form-row:last-child{margin-bottom:0}
    .channel-form-row span{color:var(--text-secondary)}
    .channel-form-row strong{color:var(--text);text-align:right;word-break:break-word}
    .channel-form-toggle{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .channel-toggle-card{padding:16px;border:1px solid var(--border-light);border-radius:18px;background:#fbfbfd}
    .channel-toggle-card label{display:flex;align-items:flex-start;gap:10px;font-weight:600;color:var(--text)}
    .channel-toggle-card p{font-size:13px;color:var(--text-secondary);line-height:1.6;margin-top:8px}
    .channel-form-actions{display:flex;gap:12px;flex-wrap:wrap}
    @media (max-width:980px){.channel-form-grid{grid-template-columns:1fr}}
    @media (max-width:640px){.channel-form-toggle{grid-template-columns:1fr}.channel-form-row{display:grid;gap:8px}.channel-form-row strong{text-align:left}}
</style>

<div class="channel-form-shell">
    <section class="channel-form-hero">
        <div class="channel-form-kicker">Channel Record</div>
        <h3>Save the exact FS Poster channel row that the schedule composer reads.</h3>
        <p>This form writes into the same channel record WordPress uses. Active channels show up in Laravel schedule creation immediately, and the same record remains visible in FS Poster on the WordPress side.</p>
    </section>

    <form method="POST" action="{{ $formAction }}">
        @csrf
        @if($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <div class="channel-form-grid">
            <section class="channel-form-card">
                @if($readonlySession)
                    <div class="form-group">
                        <label class="form-label">Connected Account</label>
                        <div class="channel-form-panel">
                            <div class="channel-form-row"><span>Network</span><strong>{{ $readonlySession['social_network_label'] }}</strong></div>
                            <div class="channel-form-row"><span>Account</span><strong>{{ $readonlySession['name'] }}</strong></div>
                            <div class="channel-form-row"><span>Remote ID</span><strong>{{ $readonlySession['remote_id'] }}</strong></div>
                            <div class="channel-form-row"><span>Method</span><strong>{{ strtoupper($readonlySession['method']) }}</strong></div>
                        </div>
                        <input type="hidden" name="channel_session_id" value="{{ $channel['channel_session_id'] }}">
                    </div>
                @else
                    <div class="form-group">
                        <label class="form-label" for="channel_session_id">Connected Account</label>
                        <select id="channel_session_id" name="channel_session_id" class="form-select" required>
                            @foreach($accounts as $account)
                                <option value="{{ $account['id'] }}" @selected((int) $channel['channel_session_id'] === (int) $account['id'])>
                                    {{ $account['social_network_label'] }} · {{ $account['name'] }} · {{ strtoupper($account['method']) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-hint">Channels live under an existing FS Poster account session.</div>
                    </div>
                @endif

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="name">Channel Name</label>
                        <input id="name" name="name" type="text" class="form-input" value="{{ $channel['name'] }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="channel_type">Channel Type</label>
                        <input id="channel_type" name="channel_type" type="text" class="form-input" value="{{ $channel['channel_type'] }}" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="remote_id">Remote ID</label>
                        <input id="remote_id" name="remote_id" type="text" class="form-input" value="{{ $channel['remote_id'] }}" @if($readonlySession) readonly @endif required>
                        <div class="form-hint">@if($readonlySession) This stays read-only here to match FS Poster’s normal edit behavior for an existing channel. @else This should match the target ID FS Poster expects for the board, page, account, or location. @endif</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="picture">Picture URL</label>
                        <input id="picture" name="picture" type="text" class="form-input" value="{{ $channel['picture'] }}">
                    </div>
                </div>

                <div class="channel-form-toggle form-group">
                    <div class="channel-toggle-card">
                        <label>
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox" name="status" value="1" @checked((string) $channel['status'] === '1')>
                            <span>Channel is active</span>
                        </label>
                        <p>Active channels appear in the schedule composer. Turning this off hides the channel without deleting it.</p>
                    </div>
                    <div class="channel-toggle-card">
                        <label>
                            <input type="hidden" name="auto_share" value="0">
                            <input type="checkbox" name="auto_share" value="1" @checked((string) $channel['auto_share'] === '1')>
                            <span>Auto-share enabled</span>
                        </label>
                        <p>This updates the same per-channel auto-share switch WordPress FS Poster stores.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="proxy">Proxy</label>
                    <input id="proxy" name="proxy" type="text" class="form-input" value="{{ $channel['proxy'] }}">
                    <div class="form-hint">Proxy lives on the linked account session, so editing it here updates the shared session too.</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="data_json">Channel Data JSON</label>
                    <textarea id="data_json" name="data_json" rows="12" class="form-textarea" spellcheck="false">{{ $channel['data_json'] }}</textarea>
                    <div class="form-hint">This is the raw `data` payload stored on the channel row.</div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="custom_settings_json">Custom Settings JSON</label>
                    <textarea id="custom_settings_json" name="custom_settings_json" rows="12" class="form-textarea" spellcheck="false">{{ $channel['custom_settings_json'] }}</textarea>
                    <div class="form-hint">This mirrors the `custom_settings` column FS Poster uses for auto-share filters and per-network defaults.</div>
                </div>
            </section>

            <aside class="channel-form-side">
                <section class="channel-form-card">
                    <div class="channel-form-panel">
                        <h4>What this changes</h4>
                        <div class="channel-form-row"><span>WordPress table</span><strong>`cd_fsp_channels`</strong></div>
                        <div class="channel-form-row"><span>Shared with</span><strong>FS Poster and Laravel</strong></div>
                        <div class="channel-form-row"><span>Schedule visibility</span><strong>Active + not archived</strong></div>
                    </div>

                    <div class="channel-form-panel" style="margin-top:16px;">
                        <h4>Before you save</h4>
                        <div class="channel-form-note">If you need a brand-new social account, add or edit the account session first. If you just need another page, board, or target under an existing account, this channel form is the right place.</div>
                    </div>

                    <div class="channel-form-actions" style="margin-top:20px;">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
                        <a href="{{ route('social.channels.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>
</div>
@endsection
