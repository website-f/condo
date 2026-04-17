@extends('layouts.app')

@section('title', $pageTitle)
@section('page-title', 'Social Media')

@section('content')
@include('social.partials.tabs', ['socialActiveTab' => 'channels'])

@php
    $showWizard = $selectedNetwork !== '' || ! $isCreate;
    $showAdvanced = ! $isCreate
        || trim((string) ($channel['data_json'] ?? '')) !== ''
        || trim((string) ($channel['custom_settings_json'] ?? '')) !== ''
        || trim((string) ($quickConnect['proxy'] ?? '')) !== ''
        || (string) ($quickConnect['method'] ?? 'app') !== 'app';
    $defaultSessionMode = $sessionMode ?? (($networkAccounts->isNotEmpty() ?? false) ? 'existing' : 'new');
    $showQuickConnectShell = $isCreate && $oauthConnectUrl && ! $readonlySession;
    $showManualBuilder = ! $showQuickConnectShell
        || old('manual_builder', $errors->any() ? '1' : '0') === '1';
@endphp

<style>
    .content { max-width: none; padding: 24px; }
    .cwiz { max-width: 720px; margin: 0 auto; display: grid; gap: 20px; padding-bottom: 40px; }

    .cwiz-card { background: #fff; border: 1px solid var(--border-light); border-radius: 18px; padding: 24px; box-shadow: var(--shadow-sm); }
    .cwiz-card h2 { margin: 0 0 6px; font-size: 22px; font-weight: 700; letter-spacing: -0.02em; }
    .cwiz-lead { margin: 0 0 20px; font-size: 15px; color: var(--text-secondary); line-height: 1.5; }

    /* Network picker */
    .cwiz-net-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; }
    .cwiz-net {
        display: flex; align-items: center; gap: 10px;
        padding: 14px; border: 2px solid var(--border-light);
        border-radius: 14px; text-decoration: none; color: var(--text);
        transition: all .2s ease;
    }
    .cwiz-net:hover { border-color: #ff5d91; transform: translateY(-2px); }
    .cwiz-net.active { border-color: #ff5d91; background: #fff5f8; box-shadow: 0 0 0 3px rgba(255,93,145,0.12); }
    .cwiz-net-icon {
        width: 36px; height: 36px; border-radius: 50%;
        border: 1px solid var(--border-light); background: #f4f5f8;
        display: grid; place-items: center;
        font-size: 14px; font-weight: 700; flex-shrink: 0;
    }
    .cwiz-net-label { font-size: 14px; font-weight: 600; }

    /* Form fields */
    .cwiz-field { display: grid; gap: 6px; margin-bottom: 16px; }
    .cwiz-field:last-child { margin-bottom: 0; }
    .cwiz-field label { font-size: 14px; font-weight: 600; }
    .cwiz-input, .cwiz-select, .cwiz-ta {
        width: 100%; padding: 12px 14px;
        border: 1px solid var(--border-light); background: var(--accent-light);
        border-radius: 10px; font-size: 15px; font-family: inherit; color: var(--text);
        transition: all .2s ease;
    }
    .cwiz-input:focus, .cwiz-select:focus, .cwiz-ta:focus { background: #fff; border-color: #ff5d91; outline: none; box-shadow: 0 0 0 3px rgba(255,93,145,0.12); }
    .cwiz-ta { min-height: 120px; resize: vertical; }
    .cwiz-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    /* Account picker */
    .cwiz-acc-grid { display: grid; gap: 10px; margin-bottom: 14px; }
    .cwiz-acc {
        display: flex; gap: 12px; align-items: flex-start;
        padding: 12px 14px; border: 1px solid var(--border-light);
        border-radius: 12px; background: #fafbfc; cursor: pointer;
        transition: all .2s ease;
    }
    .cwiz-acc:hover { border-color: #ff5d91; }
    .cwiz-acc input { width: 18px; height: 18px; margin-top: 2px; accent-color: #ff5d91; }
    .cwiz-acc-name { font-size: 14px; font-weight: 600; }
    .cwiz-acc-meta { font-size: 12px; color: var(--text-secondary); margin-top: 2px; }

    /* Toggle cards */
    .cwiz-toggles { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 14px; }
    .cwiz-toggle-card { padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-light); background: #fafbfc; }
    .cwiz-toggle-card label { display: inline-flex; align-items: center; gap: 10px; font-weight: 600; cursor: pointer; }
    .cwiz-toggle-card input { accent-color: #ff5d91; }

    .cwiz-oauth {
        margin-bottom: 16px; padding: 16px 18px; border-radius: 16px;
        border: 1px solid rgba(255, 93, 145, 0.22);
        background: linear-gradient(135deg, rgba(255, 93, 145, 0.08), rgba(255, 255, 255, 0.96));
        display: grid; gap: 12px;
    }
    .cwiz-oauth-title { font-size: 15px; font-weight: 700; letter-spacing: -0.01em; }
    .cwiz-oauth-copy { font-size: 13px; line-height: 1.6; color: var(--text-secondary); }
    .cwiz-oauth-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .cwiz-oauth-status { min-height: 20px; font-size: 13px; color: var(--text-secondary); }
    .cwiz-oauth-status.error { color: #c2410c; }
    .cwiz-oauth-status.success { color: #166534; }
    .cwiz-connect-shell { overflow: hidden; }
    .cwiz-connect-layout { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(220px, .85fr); gap: 20px; align-items: stretch; }
    .cwiz-connect-visual {
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(255, 93, 145, 0.22), rgba(142, 214, 255, 0.28));
        display: grid; place-items: center; min-height: 210px; padding: 20px;
    }
    .cwiz-connect-badge {
        width: 84px; height: 84px; border-radius: 24px; background: rgba(255,255,255,.92);
        display: grid; place-items: center; font-size: 30px; font-weight: 800; color: #ff5d91;
        box-shadow: 0 18px 40px rgba(53, 64, 84, 0.12);
    }
    .cwiz-connect-arrow { font-size: 28px; color: rgba(15, 23, 42, 0.55); }
    .cwiz-connect-stack { display: flex; align-items: center; gap: 14px; }
    .cwiz-connect-meta { display: grid; gap: 10px; align-content: start; }
    .cwiz-connect-kicker { font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--text-secondary); }
    .cwiz-connect-note {
        padding: 12px 14px; border-radius: 12px; background: #fafbfc;
        border: 1px solid var(--border-light); color: var(--text-secondary); font-size: 13px; line-height: 1.55;
    }
    .cwiz-switch-link {
        display: inline-flex; align-items: center; gap: 8px; background: transparent; border: none;
        padding: 0; color: var(--text-secondary); font-size: 14px; font-weight: 600; cursor: pointer;
    }
    .cwiz-switch-link:hover { color: var(--text); }
    .cwiz-inline-proxy { display: grid; gap: 10px; margin-top: 4px; }
    .cwiz-manual-wrap { display: grid; gap: 20px; }

    /* Existing channels */
    .cwiz-existing { display: grid; gap: 8px; }
    .cwiz-existing-item { padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border-light); background: #fff; }
    .cwiz-existing-item strong { display: block; font-size: 13px; margin-bottom: 2px; }
    .cwiz-subtle { color: var(--text-secondary); line-height: 1.6; font-size: 12px; }

    /* Actions */
    .cwiz-actions { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
    .cwiz-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 12px 22px; border-radius: 999px; font-size: 15px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; transition: all .2s ease; }
    .cwiz-btn-primary { background: #ff5d91; color: #fff; }
    .cwiz-btn-primary:hover { background: #ff4480; }
    .cwiz-btn-back { background: var(--accent-light); color: var(--text); }
    .cwiz-btn-back:hover { background: rgba(0,0,0,0.08); }
    .cwiz-btn-ghost { background: transparent; color: var(--text-secondary); padding: 10px 16px; }

    /* Extras toggle */
    .cwiz-extras-toggle {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 16px; background: var(--accent-light);
        border: 1px solid var(--border-light); border-radius: 10px;
        font-size: 14px; font-weight: 600; cursor: pointer; width: 100%;
        text-align: left; color: var(--text-secondary); margin-top: 16px;
    }
    .cwiz-extras-toggle:hover { background: rgba(0,0,0,0.05); }
    .cwiz-extras-body { display: none; margin-top: 14px; }
    .cwiz-extras-body.open { display: block; }

    .cwiz-empty { min-height: 200px; display: grid; place-items: center; text-align: center; color: var(--text-secondary); font-size: 16px; padding: 40px; }

    /* Responsive */
    @media (max-width: 640px) {
        .content { padding: 16px 12px; }
        .cwiz-card { padding: 18px 16px; }
        .cwiz-card h2 { font-size: 19px; }
        .cwiz-row, .cwiz-toggles { grid-template-columns: 1fr; }
        .cwiz-connect-layout { grid-template-columns: 1fr; }
        .cwiz-net-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
        .cwiz-net { padding: 10px; gap: 8px; }
        .cwiz-actions .cwiz-btn { flex: 1; }
    }
    [hidden] { display: none !important; }
</style>

<div class="cwiz">
    @if(! $showWizard)
        {{-- Step 0: Choose network --}}
        <div class="cwiz-card">
            <h2>Pick a social network</h2>
            <p class="cwiz-lead">Which platform do you want to connect?</p>

            <div class="cwiz-net-grid">
                @foreach($networkOptions as $network)
                    <a href="{{ route('social.channels.create', ['network' => $network['key']]) }}"
                       class="cwiz-net {{ $selectedNetwork === $network['key'] ? 'active' : '' }}">
                        <span class="cwiz-net-icon">{{ strtoupper(substr($network['label'], 0, 1)) }}</span>
                        <span class="cwiz-net-label">{{ $network['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="cwiz-actions">
            <a href="{{ route('social.channels.index') }}" class="cwiz-btn cwiz-btn-ghost">Cancel</a>
        </div>
    @else
        {{-- Network selector (compact) --}}
        <div class="cwiz-card" style="padding: 14px 18px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                <span style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Network:</span>
                @foreach($networkOptions as $network)
                    @if($isCreate)
                        <a href="{{ route('social.channels.create', ['network' => $network['key']]) }}"
                           class="cwiz-net {{ $selectedNetwork === $network['key'] ? 'active' : '' }}" style="padding: 8px 14px; border-radius: 999px; border-width: 1px; flex: 0;">
                            <span class="cwiz-net-label" style="font-size: 13px;">{{ $network['label'] }}</span>
                        </a>
                    @else
                        <span class="cwiz-net {{ $selectedNetwork === $network['key'] ? 'active' : '' }}" style="padding: 8px 14px; border-radius: 999px; border-width: 1px; flex: 0; pointer-events: none;">
                            <span class="cwiz-net-label" style="font-size: 13px;">{{ $network['label'] }}</span>
                        </span>
                    @endif
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ $formAction }}">
            @csrf
            @if($formMethod !== 'POST') @method($formMethod) @endif
            <input type="hidden" name="social_network" value="{{ $selectedNetwork }}">
            <input type="hidden" name="manual_builder" value="{{ $showManualBuilder ? '1' : '0' }}" id="cwiz-manual-builder-input">

            <div class="cwiz" style="padding-bottom: 0;">
                @if($showQuickConnectShell)
                    <div class="cwiz-card cwiz-connect-shell">
                        <div class="cwiz-connect-layout">
                            <div class="cwiz-connect-meta">
                                <div class="cwiz-connect-kicker">Quick connect</div>
                                <h2>{{ $selectedNetworkLabel }}</h2>
                                <p class="cwiz-lead">You should not need to fill the channel fields for the normal sign-in flow. Connect the account first, let FS Poster return the channels, and Laravel will import them automatically.</p>

                                <div
                                    class="cwiz-oauth"
                                    data-oauth-shell
                                    data-oauth-url="{{ $oauthConnectUrl }}"
                                    data-oauth-network="{{ $selectedNetwork }}"
                                >
                                    <div class="cwiz-oauth-title">Connect with {{ $selectedNetworkLabel }}</div>
                                    <div class="cwiz-oauth-copy">This uses FS Poster’s own auth window and saves the account on the WordPress side first, just like the plugin flow.</div>
                                    <div class="cwiz-oauth-actions">
                                        <button type="button" class="cwiz-btn cwiz-btn-primary" data-oauth-connect>Continue with {{ $selectedNetworkLabel }}</button>
                                    </div>
                                    <label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer;">
                                        <input type="checkbox" data-oauth-proxy-toggle style="accent-color:#ff5d91;">
                                        Enable proxy
                                    </label>
                                    <div class="cwiz-inline-proxy" data-oauth-proxy-field hidden>
                                        <input id="oauth_proxy" type="text" class="cwiz-input" value="{{ $quickConnect['proxy'] ?? '' }}" placeholder="http://user:pass@1.2.3.4:1234">
                                    </div>
                                    <div class="cwiz-oauth-status" data-oauth-status>Sign in in the popup, then the connected channels will be imported here automatically.</div>
                                </div>

                                @if($networkAccounts->isNotEmpty())
                                    <div class="cwiz-connect-note">{{ $networkAccounts->count() }} saved account{{ $networkAccounts->count() === 1 ? '' : 's' }} already exist for {{ $selectedNetworkLabel }}. Use manual mode only if you need to edit or create rows directly.</div>
                                @endif

                                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                                    <button type="button" class="cwiz-switch-link" id="cwiz-manual-toggle">
                                        <span>{{ $showManualBuilder ? 'Hide manual options' : 'More options' }}</span>
                                        <span aria-hidden="true">{{ $showManualBuilder ? '↑' : '→' }}</span>
                                    </button>
                                    @if(! $showManualBuilder)
                                        <a href="{{ route('social.channels.index', array_filter(['network' => $selectedNetwork !== '' ? $selectedNetwork : null])) }}" class="cwiz-btn cwiz-btn-back">Cancel</a>
                                    @endif
                                </div>
                            </div>

                            <div class="cwiz-connect-visual">
                                <div class="cwiz-connect-stack" aria-hidden="true">
                                    <div class="cwiz-connect-badge">F</div>
                                    <div class="cwiz-connect-arrow">↔</div>
                                    <div class="cwiz-connect-badge">{{ strtoupper(substr($selectedNetworkLabel !== '' ? $selectedNetworkLabel : $selectedNetwork, 0, 1)) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="cwiz-manual-wrap" id="cwiz-manual-builder" @if(! $showManualBuilder) hidden @endif>
                {{-- Account section --}}
                <div class="cwiz-card">
                    <h2>Account</h2>
                    <p class="cwiz-lead">Connect an existing account or create a new one.</p>

                    @if($readonlySession)
                        <div class="cwiz-existing-item">
                            <strong>{{ $readonlySession['name'] }}</strong>
                            <div class="cwiz-subtle">{{ $readonlySession['social_network_label'] }} &middot; {{ strtoupper($readonlySession['method']) }} &middot; {{ $readonlySession['remote_id'] }}</div>
                        </div>
                        <input type="hidden" name="channel_session_id" value="{{ $channel['channel_session_id'] }}">
                    @else
                        @if($networkAccounts->isNotEmpty())
                            <div class="cwiz-acc-grid">
                                <label class="cwiz-acc">
                                    <input type="radio" name="session_mode" value="existing" data-session-mode @checked($defaultSessionMode === 'existing')>
                                    <div>
                                        <div class="cwiz-acc-name">Use a saved account</div>
                                        <div class="cwiz-acc-meta">{{ $networkAccounts->count() }} account{{ $networkAccounts->count() === 1 ? '' : 's' }}</div>
                                    </div>
                                </label>
                                <label class="cwiz-acc">
                                    <input type="radio" name="session_mode" value="new" data-session-mode @checked($defaultSessionMode === 'new')>
                                    <div>
                                        <div class="cwiz-acc-name">Connect new account</div>
                                        <div class="cwiz-acc-meta">Fill in the details below.</div>
                                    </div>
                                </label>
                            </div>

                            <div class="cwiz-acc-grid" data-existing-block @if($defaultSessionMode !== 'existing') hidden @endif>
                                @foreach($networkAccounts as $account)
                                    <label class="cwiz-acc">
                                        <input type="radio" name="channel_session_id" value="{{ $account['id'] }}" @checked((string) ($quickConnect['channel_session_id'] ?? '') === (string) $account['id'])>
                                        <div>
                                            <div class="cwiz-acc-name">{{ $account['name'] }}</div>
                                            <div class="cwiz-acc-meta">{{ strtoupper($account['method']) }} &middot; {{ $account['remote_id'] }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <input type="hidden" name="session_mode" value="new">
                        @endif

                        <div data-new-block @if($networkAccounts->isNotEmpty() && $defaultSessionMode !== 'new') hidden @endif>
                            <div class="cwiz-row">
                                <div class="cwiz-field">
                                    <label for="account_name">Account name</label>
                                    <input id="account_name" name="account_name" type="text" class="cwiz-input" value="{{ $quickConnect['account_name'] ?? '' }}">
                                </div>
                                <div class="cwiz-field">
                                    <label for="account_remote_id">Account ID</label>
                                    <input id="account_remote_id" name="account_remote_id" type="text" class="cwiz-input" value="{{ $quickConnect['account_remote_id'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($isCreate)
                        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; margin-top: 14px;">
                            <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                                <input type="hidden" name="proxy_enabled" value="0">
                                <input type="checkbox" name="proxy_enabled" value="1" data-proxy-trigger style="accent-color: #ff5d91;" @checked((string) ($quickConnect['proxy_enabled'] ?? '0') === '1')>
                                Enable proxy
                            </label>
                        </div>
                    @else
                        <input type="hidden" name="proxy_enabled" value="{{ (string) ($quickConnect['proxy_enabled'] ?? '0') === '1' ? '1' : '0' }}">
                    @endif
                </div>

                {{-- Channel info --}}
                <div class="cwiz-card">
                    <h2>Channel details</h2>
                    <p class="cwiz-lead">Name and identify this channel.</p>

                    <div class="cwiz-row">
                        <div class="cwiz-field">
                            <label for="name">Channel name</label>
                            <input id="name" name="name" type="text" class="cwiz-input" value="{{ $channel['name'] }}" required>
                        </div>
                        <div class="cwiz-field">
                            <label for="channel_type">Channel type</label>
                            <select id="channel_type" name="channel_type" class="cwiz-select" required>
                                <option value="">Choose type</option>
                                @foreach($channelTypeOptions as $type)
                                    <option value="{{ $type['key'] }}" @selected($channel['channel_type'] === $type['key'])>{{ $type['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="cwiz-row">
                        <div class="cwiz-field">
                            <label for="remote_id">Channel ID</label>
                            <input id="remote_id" name="remote_id" type="text" class="cwiz-input" value="{{ $channel['remote_id'] }}" required>
                        </div>
                        <div class="cwiz-field">
                            <label for="picture">Picture URL</label>
                            <input id="picture" name="picture" type="text" class="cwiz-input" value="{{ $channel['picture'] }}">
                        </div>
                    </div>

                    <div class="cwiz-toggles">
                        <div class="cwiz-toggle-card">
                            <label>
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" name="status" value="1" @checked((string) $channel['status'] === '1')>
                                <span>Active</span>
                            </label>
                        </div>
                        <div class="cwiz-toggle-card">
                            <label>
                                <input type="hidden" name="auto_share" value="0">
                                <input type="checkbox" name="auto_share" value="1" @checked((string) $channel['auto_share'] === '1')>
                                <span>Auto-share</span>
                            </label>
                        </div>
                    </div>

                    {{-- Advanced options (collapsed) --}}
                    <button type="button" class="cwiz-extras-toggle" id="cwiz-adv-toggle">
                        Advanced options
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:auto;transition:transform .2s ease;" id="cwiz-adv-chev"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="cwiz-extras-body" id="cwiz-adv-body" @if($showAdvanced) class="cwiz-extras-body open" @endif>
                        <div class="cwiz-row">
                            <div class="cwiz-field">
                                <label for="method">Method</label>
                                <select id="method" name="method" class="cwiz-select">
                                    @foreach($methodOptions as $method)
                                        <option value="{{ $method['key'] }}" @selected(($quickConnect['method'] ?? 'app') === $method['key'])>{{ $method['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="cwiz-field" data-proxy-field @if((string) ($quickConnect['proxy_enabled'] ?? '0') !== '1') hidden @endif>
                                <label for="proxy">Proxy</label>
                                <input id="proxy" name="proxy" type="text" class="cwiz-input" value="{{ $quickConnect['proxy'] ?? '' }}">
                            </div>
                        </div>

                        @if(! $isCreate || trim((string) ($channel['data_json'] ?? '')) !== '' || trim((string) ($channel['custom_settings_json'] ?? '')) !== '')
                            <div class="cwiz-field">
                                <label for="data_json">Channel JSON</label>
                                <textarea id="data_json" name="data_json" rows="6" class="cwiz-ta" spellcheck="false">{{ $channel['data_json'] ?? '' }}</textarea>
                            </div>
                            <div class="cwiz-field">
                                <label for="custom_settings_json">Custom Settings JSON</label>
                                <textarea id="custom_settings_json" name="custom_settings_json" rows="6" class="cwiz-ta" spellcheck="false">{{ $channel['custom_settings_json'] ?? '' }}</textarea>
                            </div>
                        @endif
                    </div>
                </div>

                @if($networkChannels->isNotEmpty())
                    <div class="cwiz-card">
                        <h2>Existing {{ $selectedNetworkLabel }} channels</h2>
                        <div class="cwiz-existing">
                            @foreach($networkChannels->take(4) as $existingChannel)
                                <div class="cwiz-existing-item">
                                    <strong>{{ $existingChannel['name'] }}</strong>
                                    <div class="cwiz-subtle">{{ $existingChannel['session_name'] }} &middot; {{ ucwords(str_replace('_', ' ', $existingChannel['channel_type'])) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="cwiz-actions">
                    <a href="{{ route('social.channels.index', array_filter(['network' => $selectedNetwork !== '' ? $selectedNetwork : null])) }}" class="cwiz-btn cwiz-btn-back">Cancel</a>
                    <button type="submit" class="cwiz-btn cwiz-btn-primary">{{ $submitLabel }}</button>
                </div>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection

@section('scripts')
<script>
(function () {
    var manualBuilder = document.getElementById('cwiz-manual-builder');
    var manualBuilderInput = document.getElementById('cwiz-manual-builder-input');
    var manualToggle = document.getElementById('cwiz-manual-toggle');
    var oauthShell = document.querySelector('[data-oauth-shell]');
    var oauthButton = document.querySelector('[data-oauth-connect]');
    var oauthStatus = document.querySelector('[data-oauth-status]');
    var oauthProxyToggle = document.querySelector('[data-oauth-proxy-toggle]');
    var oauthProxyField = document.querySelector('[data-oauth-proxy-field]');
    var oauthProxyInput = document.getElementById('oauth_proxy');
    var sessionModes = Array.from(document.querySelectorAll('[data-session-mode]'));
    var existingBlock = document.querySelector('[data-existing-block]');
    var newBlock = document.querySelector('[data-new-block]');
    var advToggle = document.getElementById('cwiz-adv-toggle');
    var advBody = document.getElementById('cwiz-adv-body');
    var advChev = document.getElementById('cwiz-adv-chev');
    var proxyTrigger = document.querySelector('[data-proxy-trigger]');
    var proxyField = document.querySelector('[data-proxy-field]');

    function syncSession() {
        var mode = (sessionModes.find(function(i){ return i.checked; }) || {}).value || 'new';
        if (existingBlock) { existingBlock.hidden = mode !== 'existing'; }
        if (newBlock) { newBlock.hidden = mode !== 'new'; }
    }
    sessionModes.forEach(function(i){ i.addEventListener('change', syncSession); });
    if (sessionModes.length) syncSession();

    if (advToggle && advBody) {
        advToggle.addEventListener('click', function () {
            var open = advBody.classList.toggle('open');
            if (advChev) advChev.style.transform = open ? 'rotate(180deg)' : '';
        });
    }

    function setManualBuilder(open) {
        if (!manualBuilder) return;

        manualBuilder.hidden = !open;

        if (manualBuilderInput) {
            manualBuilderInput.value = open ? '1' : '0';
        }

        if (manualToggle) {
            var parts = manualToggle.querySelectorAll('span');

            if (parts[0]) {
                parts[0].textContent = open ? 'Hide manual options' : 'More options';
            }

            if (parts[1]) {
                parts[1].innerHTML = open ? '&uarr;' : '&rarr;';
            }
        }
    }

    if (manualToggle && manualBuilder) {
        manualToggle.addEventListener('click', function () {
            setManualBuilder(manualBuilder.hidden);
        });
    }

    function syncOauthProxy() {
        if (oauthProxyField && oauthProxyToggle) {
            oauthProxyField.hidden = !oauthProxyToggle.checked;
        }
    }
    if (oauthProxyToggle) {
        oauthProxyToggle.addEventListener('change', syncOauthProxy);
        syncOauthProxy();
    }
    if (oauthProxyInput) {
        oauthProxyInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();

                if (oauthButton) {
                    oauthButton.click();
                }
            }
        });
    }

    function syncProxy() {
        if (proxyField && proxyTrigger) proxyField.hidden = !proxyTrigger.checked;

        if (proxyTrigger && proxyTrigger.checked && advBody) {
            advBody.classList.add('open');
            if (advChev) advChev.style.transform = 'rotate(180deg)';
        }
    }
    if (proxyTrigger) { proxyTrigger.addEventListener('change', syncProxy); syncProxy(); }

    function setOauthStatus(message, tone) {
        if (!oauthStatus) return;
        oauthStatus.textContent = message || '';
        oauthStatus.classList.remove('error', 'success');
        if (tone) oauthStatus.classList.add(tone);
    }

    function handleOauthPayload(payload) {
        if (!oauthShell || !payload || payload.origin !== 'FS_POSTER' || handleOauthPayload.pending) {
            return;
        }

        if (payload.error) {
            setOauthStatus(payload.error, 'error');
            return;
        }

        if (!Array.isArray(payload.channels) || payload.channels.length === 0) {
            setOauthStatus('FS Poster finished, but no channels were returned to import.', 'error');
            return;
        }

        handleOauthPayload.pending = true;
        if (oauthButton) oauthButton.disabled = true;
        setOauthStatus('Importing connected channels...', null);

        fetch(@json(route('social.channels.oauth-import')), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token()),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                social_network: oauthShell.getAttribute('data-oauth-network'),
                channels: payload.channels
            })
        })
        .then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (data) {
                if (!response.ok) {
                    var errorMessage = data.message || 'The OAuth import could not be completed.';

                    if (data.errors) {
                        Object.keys(data.errors).some(function (key) {
                            if (Array.isArray(data.errors[key]) && data.errors[key][0]) {
                                errorMessage = data.errors[key][0];
                                return true;
                            }

                            return false;
                        });
                    }

                    throw new Error(errorMessage);
                }

                return data;
            });
        })
        .then(function (data) {
            setOauthStatus(data.message || 'Connected successfully. Redirecting...', 'success');
            window.location.href = data.redirect_url || @json(route('social.channels.index', ['network' => $selectedNetwork]));
        })
        .catch(function (error) {
            handleOauthPayload.pending = false;
            if (oauthButton) oauthButton.disabled = false;
            setOauthStatus(error.message || 'The OAuth import could not be completed.', 'error');
        });
    }

    handleOauthPayload.pending = false;

    if (oauthButton && oauthShell) {
        oauthButton.addEventListener('click', function () {
            var authUrl = oauthShell.getAttribute('data-oauth-url') || '';
            var proxyValue = oauthProxyToggle && oauthProxyToggle.checked && oauthProxyInput
                ? oauthProxyInput.value.trim()
                : '';

            if (oauthProxyToggle && oauthProxyToggle.checked && proxyValue === '') {
                setOauthStatus('Enter the proxy before opening the FS Poster login window.', 'error');
                return;
            }

            if (proxyValue !== '') {
                authUrl += (authUrl.indexOf('?') === -1 ? '?' : '&') + 'proxy=' + encodeURIComponent(proxyValue);
            }

            handleOauthPayload.pending = false;
            setOauthStatus('Waiting for FS Poster to finish the authorization...', null);

            var popup = window.open(authUrl, '_blank', 'width=700,height=650');

            if (!popup) {
                setOauthStatus('The browser blocked the popup. Allow popups for this site and try again.', 'error');
            }
        });
    }

    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin) {
            return;
        }

        handleOauthPayload(event.data);
    });

    if ('BroadcastChannel' in window) {
        var authChannel = new BroadcastChannel('fs_poster_auth');
        authChannel.addEventListener('message', function (event) {
            handleOauthPayload(event.data);
        });
    }
}());
</script>
@endsection
