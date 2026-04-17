@extends('layouts.app')

@section('title', $pageTitle)
@section('page-title', 'Social Media')

@section('content')
@include('social.partials.tabs', ['socialActiveTab' => 'calendar'])

@php
    $selectedChannelIds = array_map('intval', (array) ($schedule['channel_ids'] ?? []));
    $selectedListing = collect($listings)->firstWhere('id', (int) ($schedule['listing_id'] ?? 0));
    $messageValue = (string) ($schedule['message'] ?? '');
    $stepLabels = [1 => 'Channels', 2 => 'Listing', 3 => 'Message', 4 => 'Schedule', 5 => 'Review'];
@endphp

<style>
    .content { max-width: none; padding: 24px; }
    .swiz { max-width: 720px; margin: 0 auto; display: grid; gap: 20px; padding-bottom: 40px; }

    /* Stepper */
    .swiz-stepper { background: #fff; border: 1px solid var(--border-light); border-radius: 16px; padding: 16px; box-shadow: var(--shadow-sm); }
    .swiz-stepper-top { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 12px; }
    .swiz-stepper-title { font-size: 14px; font-weight: 700; }
    .swiz-stepper-pct { font-size: 13px; color: var(--text-secondary); }
    .swiz-bar { display: flex; gap: 6px; align-items: center; }
    .swiz-dot { width: 28px; height: 28px; border-radius: 50%; background: var(--accent-light); color: var(--text-secondary); display: grid; place-items: center; font-size: 12px; font-weight: 700; flex-shrink: 0; transition: all .2s ease; }
    .swiz-dot.active { background: #1d1d1f; color: #fff; transform: scale(1.05); }
    .swiz-dot.done { background: #34c759; color: #fff; }
    .swiz-line { flex: 1; height: 2px; background: var(--accent-light); transition: background .2s ease; }
    .swiz-line.done { background: #34c759; }

    /* Step */
    .swiz-step { display: none; }
    .swiz-step.active { display: block; animation: swizFade .25s ease; }
    @keyframes swizFade { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    .swiz-card { background: #fff; border: 1px solid var(--border-light); border-radius: 18px; padding: 24px; box-shadow: var(--shadow-sm); }
    .swiz-step-num { display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: #ff5d91; margin-bottom: 6px; }
    .swiz-step h2 { margin: 0 0 6px; font-size: 22px; font-weight: 700; letter-spacing: -0.02em; }
    .swiz-lead { margin: 0 0 20px; font-size: 15px; color: var(--text-secondary); line-height: 1.5; }

    /* Nav */
    .swiz-nav { display: flex; gap: 10px; margin-top: 22px; padding-top: 18px; border-top: 1px solid var(--border-light); justify-content: space-between; flex-wrap: wrap; }
    .swiz-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 12px 22px; border-radius: 999px; font-size: 15px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; transition: all .2s ease; min-width: 120px; }
    .swiz-btn-primary { background: #ff5d91; color: #fff; }
    .swiz-btn-primary:hover:not(:disabled) { background: #ff4480; }
    .swiz-btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }
    .swiz-btn-back { background: var(--accent-light); color: var(--text); }
    .swiz-btn-back:hover { background: rgba(0,0,0,0.08); }
    .swiz-btn-save { background: #34c759; color: #fff; }
    .swiz-btn-save:hover:not(:disabled) { background: #28a745; }
    .swiz-btn-ghost { background: transparent; color: var(--text-secondary); min-width: 0; padding: 10px 16px; }

    /* Channel grid */
    .swiz-ch-grid { display: grid; gap: 10px; }
    .swiz-ch { display: flex; gap: 12px; align-items: center; padding: 12px 14px; border: 1px solid var(--border-light); border-radius: 14px; background: #fafbfc; cursor: pointer; transition: all .2s ease; }
    .swiz-ch:hover { border-color: #ff5d91; }
    .swiz-ch input { width: 18px; height: 18px; accent-color: #ff5d91; cursor: pointer; }
    .swiz-ch-ava { width: 34px; height: 34px; border-radius: 50%; border: 1px solid var(--border-light); background: #f3f5f9; display: grid; place-items: center; font-size: 13px; font-weight: 700; color: #3867d6; overflow: hidden; flex-shrink: 0; }
    .swiz-ch-ava img { width: 100%; height: 100%; object-fit: cover; }
    .swiz-ch-name { font-size: 14px; font-weight: 600; }
    .swiz-ch-meta { font-size: 12px; color: var(--text-secondary); margin-top: 2px; }
    .swiz-ch-badge { display: inline-block; padding: 2px 7px; border-radius: 999px; background: #eef2ff; color: #3f4b7a; font-size: 10px; font-weight: 700; text-transform: uppercase; margin-right: 4px; }
    .swiz-empty-note { padding: 20px; border: 1px dashed var(--border); border-radius: 12px; text-align: center; color: var(--text-secondary); font-size: 14px; }

    /* Listing grid */
    .swiz-ls-grid { display: grid; grid-template-columns: 1fr; gap: 10px; }
    @media (min-width: 560px) { .swiz-ls-grid { grid-template-columns: 1fr 1fr; } }
    .swiz-ls { display: flex; gap: 12px; align-items: center; padding: 12px; border: 1px solid var(--border-light); border-radius: 14px; background: #fafbfc; cursor: pointer; transition: all .2s ease; }
    .swiz-ls:hover { border-color: #ff5d91; }
    .swiz-ls input { width: 18px; height: 18px; accent-color: #ff5d91; cursor: pointer; }
    .swiz-ls-thumb { width: 56px; height: 56px; border-radius: 12px; overflow: hidden; background: #eef2f7; flex-shrink: 0; display: grid; place-items: center; font-size: 10px; font-weight: 700; color: var(--text-secondary); }
    .swiz-ls-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .swiz-ls-title { font-size: 14px; font-weight: 600; line-height: 1.3; }
    .swiz-ls-price { font-size: 12px; color: var(--text-secondary); margin-top: 2px; }

    /* Message */
    .swiz-textarea { width: 100%; min-height: 160px; padding: 14px 16px; border: 1px solid var(--border-light); border-radius: 12px; background: var(--accent-light); font-size: 15px; font-family: inherit; color: var(--text); resize: vertical; transition: all .2s ease; line-height: 1.5; }
    .swiz-textarea:focus { background: #fff; border-color: #ff5d91; outline: none; box-shadow: 0 0 0 3px rgba(255,93,145,0.15); }
    .swiz-counter { text-align: right; font-size: 12px; color: var(--text-secondary); margin-top: 6px; }
    .swiz-check { display: flex; gap: 10px; align-items: center; padding: 14px; background: var(--accent-light); border: 1px solid var(--border-light); border-radius: 12px; cursor: pointer; margin-bottom: 14px; }
    .swiz-check input { width: 18px; height: 18px; accent-color: #ff5d91; cursor: pointer; }
    .swiz-check span { font-size: 14px; font-weight: 600; }
    .swiz-media-box { border: 1px dashed var(--border); border-radius: 14px; background: #fafbfc; min-height: 140px; display: grid; place-items: center; overflow: hidden; margin-top: 10px; }
    .swiz-media-box img { width: 100%; height: 160px; object-fit: cover; }
    .swiz-media-empty { color: var(--text-secondary); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; }

    /* Schedule */
    .swiz-time-input { width: 100%; padding: 14px 16px; border: 1px solid var(--border-light); border-radius: 12px; background: var(--accent-light); font-size: 16px; font-family: inherit; color: var(--text); }
    .swiz-time-input:focus { background: #fff; border-color: #ff5d91; outline: none; box-shadow: 0 0 0 3px rgba(255,93,145,0.15); }
    .swiz-time-hint { font-size: 13px; color: var(--text-secondary); margin-top: 8px; }

    /* Review */
    .swiz-review { display: grid; gap: 10px; }
    .swiz-review-row { display: grid; grid-template-columns: 100px 1fr auto; gap: 10px; padding: 12px 14px; background: var(--accent-light); border-radius: 10px; align-items: center; }
    .swiz-review-key { font-size: 12px; font-weight: 700; color: var(--text-secondary); }
    .swiz-review-val { font-size: 14px; font-weight: 600; word-break: break-word; }
    .swiz-review-val.muted { color: var(--text-secondary); font-style: italic; }
    .swiz-review-edit { background: transparent; border: none; cursor: pointer; font-size: 12px; font-weight: 600; color: #ff5d91; padding: 4px 10px; border-radius: 999px; }
    .swiz-review-edit:hover { background: rgba(255,93,145,0.1); }

    @media (max-width: 640px) {
        .content { padding: 16px 12px; }
        .swiz-card { padding: 18px 16px; }
        .swiz-step h2 { font-size: 19px; }
        .swiz-btn { padding: 12px 18px; font-size: 14px; min-width: 100px; }
        .swiz-review-row { grid-template-columns: 1fr; }
    }
    [hidden] { display: none !important; }
</style>

<div class="swiz" id="swiz">
    {{-- Stepper --}}
    <div class="swiz-stepper">
        <div class="swiz-stepper-top">
            <div class="swiz-stepper-title" id="swiz-title">Step 1 of 5</div>
            <div class="swiz-stepper-pct"><span id="swiz-pct">20</span>%</div>
        </div>
        <div class="swiz-bar">
            @foreach($stepLabels as $n => $label)
                <div class="swiz-dot" data-dot="{{ $n }}">{{ $n }}</div>
                @if(!$loop->last)<div class="swiz-line" data-line="{{ $n }}"></div>@endif
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ $formAction }}" id="swiz-form">
        @csrf
        @if($formMethod !== 'POST') @method($formMethod) @endif

        {{-- STEP 1: Channels --}}
        <section class="swiz-step active" data-step="1">
            <div class="swiz-card">
                <span class="swiz-step-num">Step 1 of 5</span>
                <h2>Which channels?</h2>
                <p class="swiz-lead">Tick the channels you want to post to.</p>

                @if($channels->isEmpty())
                    <div class="swiz-empty-note">No channels yet. <a href="{{ route('social.channels.create') }}">Add one first</a>.</div>
                @else
                    <div class="swiz-ch-grid">
                        @foreach($channels as $channel)
                            <label class="swiz-ch">
                                <input type="checkbox" name="channel_ids[]" value="{{ $channel['id'] }}"
                                    class="swiz-ch-input"
                                    data-channel-id="{{ $channel['id'] }}"
                                    data-channel-name="{{ $channel['name'] }}"
                                    @checked(in_array((int) $channel['id'], $selectedChannelIds, true))>
                                <div class="swiz-ch-ava">
                                    @if($channel['picture'] !== '')
                                        <img src="{{ $channel['picture'] }}" alt="">
                                    @else
                                        {{ strtoupper(substr($channel['name'], 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="swiz-ch-name">{{ $channel['name'] }}</div>
                                    <div class="swiz-ch-meta"><span class="swiz-ch-badge">{{ strtoupper($channel['social_network']) }}</span>{{ ucwords(str_replace('_', ' ', $channel['channel_type'])) }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="swiz-nav">
                <a href="{{ route('social.index') }}" class="swiz-btn swiz-btn-ghost">Cancel</a>
                <button type="button" class="swiz-btn swiz-btn-primary" data-next>Continue &rarr;</button>
            </div>
        </section>

        {{-- STEP 2: Listing --}}
        <section class="swiz-step" data-step="2">
            <div class="swiz-card">
                <span class="swiz-step-num">Step 2 of 5</span>
                <h2>Which listing?</h2>
                <p class="swiz-lead">Pick the property you want to promote.</p>

                <div class="swiz-ls-grid">
                    @foreach($listings as $listing)
                        <label class="swiz-ls">
                            <input type="radio" name="listing_id" value="{{ $listing['id'] }}"
                                class="swiz-ls-input"
                                data-listing-title="{{ $listing['title'] }}"
                                data-listing-image="{{ $listing['image_url'] }}"
                                @checked((int) ($schedule['listing_id'] ?? 0) === (int) $listing['id'])>
                            <div class="swiz-ls-thumb">
                                @if($listing['image_url'])
                                    <img src="{{ $listing['image_url'] }}" alt="">
                                @else
                                    No img
                                @endif
                            </div>
                            <div>
                                <div class="swiz-ls-title">{{ $listing['title'] }}</div>
                                <div class="swiz-ls-price">{{ $listing['formatted_price'] }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="swiz-nav">
                <button type="button" class="swiz-btn swiz-btn-back" data-back>&larr; Back</button>
                <button type="button" class="swiz-btn swiz-btn-primary" data-next>Continue &rarr;</button>
            </div>
        </section>

        {{-- STEP 3: Message --}}
        <section class="swiz-step" data-step="3">
            <div class="swiz-card">
                <span class="swiz-step-num">Step 3 of 5</span>
                <h2>Write your post</h2>
                <p class="swiz-lead">Type the message. Leave blank to use the channel template.</p>

                @if($existingGroup && $existingGroup['has_mixed_messages'])
                    <div class="swiz-empty-note" style="margin-bottom:14px;">Leave empty to keep each channel's current copy.</div>
                @endif

                <label class="swiz-check">
                    <input type="hidden" name="upload_media" value="0">
                    <input type="checkbox" name="upload_media" value="1" data-media-toggle @checked((string) ($schedule['upload_media'] ?? '0') === '1')>
                    <span>Attach listing image</span>
                </label>

                <div class="swiz-media-box" data-media-box>
                    @if(is_array($selectedListing) && !empty($selectedListing['image_url']))
                        <img src="{{ $selectedListing['image_url'] }}" alt="" data-media-image>
                    @else
                        <div class="swiz-media-empty" data-media-empty>No image</div>
                    @endif
                </div>

                <div style="margin-top: 18px;">
                    <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 8px;">Post content</label>
                    <textarea id="message" name="message" class="swiz-textarea" maxlength="4000" data-message-box>{{ $messageValue }}</textarea>
                    <div class="swiz-counter"><span data-message-count>{{ mb_strlen($messageValue) }}</span>/4000</div>
                </div>

                <div style="margin-top: 14px;">
                    <label class="swiz-check">
                        <input type="hidden" name="first_comment_enabled" value="0">
                        <input type="checkbox" name="first_comment_enabled" value="1" data-fc-toggle @checked((string) ($schedule['first_comment_enabled'] ?? '0') === '1')>
                        <span>Add first comment</span>
                    </label>
                    <textarea name="first_comment" rows="4" class="swiz-textarea" data-fc-box @if((string) ($schedule['first_comment_enabled'] ?? '0') !== '1') hidden @endif>{{ $schedule['first_comment'] ?? '' }}</textarea>
                </div>
            </div>
            <div class="swiz-nav">
                <button type="button" class="swiz-btn swiz-btn-back" data-back>&larr; Back</button>
                <button type="button" class="swiz-btn swiz-btn-primary" data-next>Continue &rarr;</button>
            </div>
        </section>

        {{-- STEP 4: When --}}
        <section class="swiz-step" data-step="4">
            <div class="swiz-card">
                <span class="swiz-step-num">Step 4 of 5</span>
                <h2>When should it go out?</h2>
                <p class="swiz-lead">Pick a date and time.</p>

                <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 8px;">Schedule date &amp; time</label>
                <input id="scheduled_at" name="scheduled_at" type="datetime-local" class="swiz-time-input" value="{{ $schedule['scheduled_at_form'] }}" required>
                <div class="swiz-time-hint">Timezone: {{ now()->format('P') }}</div>
            </div>
            <div class="swiz-nav">
                <button type="button" class="swiz-btn swiz-btn-back" data-back>&larr; Back</button>
                <button type="button" class="swiz-btn swiz-btn-primary" data-next>Continue &rarr;</button>
            </div>
        </section>

        {{-- STEP 5: Review --}}
        <section class="swiz-step" data-step="5">
            <div class="swiz-card">
                <span class="swiz-step-num">Step 5 of 5</span>
                <h2>Review &amp; schedule</h2>
                <p class="swiz-lead">Check everything. Tap Edit to fix anything.</p>

                <div class="swiz-review">
                    <div class="swiz-review-row">
                        <div class="swiz-review-key">Channels</div>
                        <div class="swiz-review-val" id="rev-channels">—</div>
                        <button type="button" class="swiz-review-edit" data-jump="1">Edit</button>
                    </div>
                    <div class="swiz-review-row">
                        <div class="swiz-review-key">Listing</div>
                        <div class="swiz-review-val" id="rev-listing">—</div>
                        <button type="button" class="swiz-review-edit" data-jump="2">Edit</button>
                    </div>
                    <div class="swiz-review-row">
                        <div class="swiz-review-key">Image</div>
                        <div class="swiz-review-val" id="rev-media">—</div>
                        <button type="button" class="swiz-review-edit" data-jump="3">Edit</button>
                    </div>
                    <div class="swiz-review-row">
                        <div class="swiz-review-key">Message</div>
                        <div class="swiz-review-val" id="rev-message">—</div>
                        <button type="button" class="swiz-review-edit" data-jump="3">Edit</button>
                    </div>
                    <div class="swiz-review-row">
                        <div class="swiz-review-key">When</div>
                        <div class="swiz-review-val" id="rev-when">—</div>
                        <button type="button" class="swiz-review-edit" data-jump="4">Edit</button>
                    </div>
                </div>
            </div>
            <div class="swiz-nav">
                <button type="button" class="swiz-btn swiz-btn-back" data-back>&larr; Back</button>
                <button type="submit" class="swiz-btn swiz-btn-save">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    {{ $submitLabel }}
                </button>
            </div>
        </section>
    </form>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var swiz = document.getElementById('swiz');
    if (!swiz) return;
    var steps = Array.from(swiz.querySelectorAll('.swiz-step'));
    var dots = Array.from(swiz.querySelectorAll('.swiz-dot'));
    var lines = Array.from(swiz.querySelectorAll('.swiz-line'));
    var titleEl = document.getElementById('swiz-title');
    var pctEl = document.getElementById('swiz-pct');
    var labels = @json($stepLabels);
    var total = steps.length, cur = 1;

    function show(n) {
        n = Math.max(1, Math.min(total, n)); cur = n;
        steps.forEach(function(s){ var i = parseInt(s.dataset.step); s.classList.toggle('active', i === n); });
        dots.forEach(function(d){ var i = parseInt(d.dataset.dot); d.classList.toggle('active', i===n); d.classList.toggle('done', i<n); d.textContent = i<n ? '✓' : i; });
        lines.forEach(function(l){ l.classList.toggle('done', parseInt(l.dataset.line)<n); });
        titleEl.textContent = 'Step '+n+' of '+total+' — '+(labels[n]||'');
        pctEl.textContent = Math.round((n/total)*100);
        if (n === total) refreshReview();
        window.scrollTo({ top: swiz.offsetTop - 12, behavior: 'smooth' });
    }

    swiz.addEventListener('click', function(e) {
        var next = e.target.closest('[data-next]');
        var back = e.target.closest('[data-back]');
        var jump = e.target.closest('[data-jump]');
        if (next) { e.preventDefault(); show(cur+1); }
        else if (back) { e.preventDefault(); show(cur-1); }
        else if (jump) { e.preventDefault(); show(parseInt(jump.dataset.jump)); }
    });

    /* Media toggle */
    var mediaToggle = document.querySelector('[data-media-toggle]');
    var mediaBox = document.querySelector('[data-media-box]');
    var listingInputs = Array.from(document.querySelectorAll('.swiz-ls-input'));

    function renderMedia() {
        if (!mediaBox) return;
        mediaBox.innerHTML = '';
        if (!mediaToggle || !mediaToggle.checked) { mediaBox.innerHTML = '<div class="swiz-media-empty">Off</div>'; return; }
        var sel = listingInputs.find(function(i){ return i.checked; });
        if (sel && sel.dataset.listingImage) { mediaBox.innerHTML = '<img src="'+sel.dataset.listingImage+'" alt="">'; return; }
        mediaBox.innerHTML = '<div class="swiz-media-empty">No image</div>';
    }
    if (mediaToggle) mediaToggle.addEventListener('change', renderMedia);
    listingInputs.forEach(function(i){ i.addEventListener('change', renderMedia); });

    /* First comment toggle */
    var fcToggle = document.querySelector('[data-fc-toggle]');
    var fcBox = document.querySelector('[data-fc-box]');
    function syncFC() { if (fcToggle && fcBox) fcBox.hidden = !fcToggle.checked; }
    if (fcToggle) fcToggle.addEventListener('change', syncFC);

    /* Message counter */
    var msgBox = document.querySelector('[data-message-box]');
    var msgCount = document.querySelector('[data-message-count]');
    if (msgBox && msgCount) msgBox.addEventListener('input', function(){ msgCount.textContent = msgBox.value.length; });

    /* Review */
    function refreshReview() {
        var chEls = Array.from(document.querySelectorAll('.swiz-ch-input:checked'));
        var chNames = chEls.map(function(i){ return i.dataset.channelName; }).join(', ');
        setText('rev-channels', chNames || 'None selected');

        var ls = listingInputs.find(function(i){ return i.checked; });
        setText('rev-listing', ls ? ls.dataset.listingTitle : 'None selected');

        var mediaOn = mediaToggle && mediaToggle.checked;
        setText('rev-media', mediaOn ? 'Listing image attached' : 'No image');

        var msg = msgBox ? msgBox.value.trim() : '';
        setText('rev-message', msg || 'Using channel template');

        var when = document.getElementById('scheduled_at');
        if (when && when.value) {
            try { setText('rev-when', new Date(when.value).toLocaleString()); }
            catch(e) { setText('rev-when', when.value); }
        } else {
            setText('rev-when', 'Not set');
        }
    }
    function setText(id, v) {
        var el = document.getElementById(id);
        if (!el) return;
        el.textContent = v;
        el.classList.toggle('muted', !v || v === 'None selected' || v === 'Not set');
    }

    renderMedia();
    syncFC();
    show(1);
}());
</script>
@endsection
