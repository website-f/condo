@extends('layouts.app')

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('topbar-actions')
    <div class="btn-group">
        <a href="{{ route('social.channels.index') }}" class="btn btn-secondary btn-sm">Back</a>
    </div>
@endsection

@section('content')
<style>
    .account-form-shell{display:grid;gap:24px}
    .account-hero,.account-card{background:var(--card-bg);border:1px solid var(--border-light);border-radius:24px;box-shadow:var(--shadow-sm)}
    .account-hero,.account-card{padding:24px}
    .account-hero{background:radial-gradient(circle at top right,rgba(204,223,255,.34),transparent 34%),linear-gradient(180deg,#fff 0%,#f8fafc 100%)}
    .account-kicker{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px}
    .account-hero h3{font-size:clamp(26px,3vw,38px);line-height:1.04;letter-spacing:-.04em;margin-bottom:10px}
    .account-copy,.account-note{color:var(--text-secondary);line-height:1.6}
    .account-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(290px,.78fr);gap:24px}
    .account-side{display:grid;gap:16px;align-content:start}
    .account-panel{padding:18px;border:1px solid var(--border-light);border-radius:18px;background:#f8fafc}
    .account-panel h4{font-size:16px;line-height:1.15;margin-bottom:10px}
    .account-row{display:flex;justify-content:space-between;gap:12px;font-size:13px;margin-bottom:10px}
    .account-row:last-child{margin-bottom:0}
    .account-row span{color:var(--text-secondary)}
    .account-row strong{color:var(--text);text-align:right}
    .account-actions{display:flex;gap:12px;flex-wrap:wrap}
    @media (max-width:900px){.account-grid{grid-template-columns:1fr}}
</style>

<div class="account-form-shell">
    <section class="account-hero">
        <div class="account-kicker">Advanced</div>
        <h3>{{ $isCreate ? 'Manual account row.' : 'Edit account row.' }}</h3>
        <p class="account-copy">Use this only when you need direct control over the FS Poster session record.</p>
    </section>

    <form method="POST" action="{{ $formAction }}">
        @csrf
        @if($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <div class="account-grid">
            <section class="account-card">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="social_network">Network</label>
                        <select id="social_network" name="social_network" class="form-select" required>
                            @foreach($networkOptions as $network)
                                <option value="{{ $network['key'] }}" @selected($account['social_network'] === $network['key'])>{{ $network['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="method">Method</label>
                        <select id="method" name="method" class="form-select" required>
                            @foreach($methodOptions as $method)
                                <option value="{{ $method['key'] }}" @selected($account['method'] === $method['key'])>{{ $method['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="name">Account Name</label>
                        <input id="name" name="name" type="text" class="form-input" value="{{ $account['name'] }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="remote_id">Remote ID</label>
                        <input id="remote_id" name="remote_id" type="text" class="form-input" value="{{ $account['remote_id'] }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="proxy">Proxy</label>
                    <input id="proxy" name="proxy" type="text" class="form-input" value="{{ $account['proxy'] }}" placeholder="Optional">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="data_json">Session JSON</label>
                    <textarea id="data_json" name="data_json" rows="18" class="form-textarea" spellcheck="false" required>{{ $account['data_json'] }}</textarea>
                    <div class="form-hint">Keep this valid. FS Poster reads it directly.</div>
                </div>
            </section>

            <aside class="account-side">
                <section class="account-card">
                    <div class="account-panel">
                        <h4>Shared Record</h4>
                        <div class="account-row"><span>Table</span><strong>`cd_fsp_channel_sessions`</strong></div>
                        <div class="account-row"><span>Used by</span><strong>WordPress and Laravel</strong></div>
                    </div>

                    <div class="account-panel" style="margin-top:16px;">
                        <h4>Normal Flow</h4>
                        <div class="account-note">For normal connection, use the quick connect flow on Add Channel. Use this screen only when you need to edit the raw FS Poster session row directly.</div>
                    </div>

                    <div class="account-actions" style="margin-top:20px;">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
                        <a href="{{ route('social.channels.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>
</div>
@endsection
