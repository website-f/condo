@php
    $existingPhotos = \App\Support\ListingEditor::normalizePhotoPaths(old('existing_photos', $form['existing_photos'] ?? []));
    $listingUsername = $listing?->getRawOriginal('username') ?? $listing?->username;
    $listingPropertyId = $listing?->getRawOriginal('propertyid') ?? $listing?->propertyid;
    $selectedFormSource = old('source', $editingSource ?? $activeCreateSource ?? 'ipp');
    $originalFormSource = old('original_source', $originalSource ?? $listing?->source_key ?? $selectedFormSource);
    $formReturnSource = old('return_source', $returnSource ?? $listing?->return_source ?? $selectedFormSource);
    $createSourceTabs = collect($createSourceTabs ?? []);
    $selectedSourceMeta = $createSourceTabs->firstWhere('key', $selectedFormSource)
        ?? ['key' => 'ipp', 'label' => 'IPP', 'enabled' => true, 'description' => ''];
    $selectedSourceEnabled = (bool) ($selectedSourceMeta['enabled'] ?? false);
    $hasExtras = count($generalFieldGroups) > 0;
    // Total steps: 1) Where? 2) Photos 3) Basics 4) Description (+ extras) 5) Review
    $stepLabels = [
        1 => 'Where',
        2 => 'Photos',
        3 => 'Basics',
        4 => 'Details',
        5 => 'Review',
    ];
@endphp

<style>
    /* Apple-like minimal palette */
    :root {
        --apple-bg: #fbfbfd;
        --apple-card: #ffffff;
        --apple-text: #1d1d1f;
        --apple-text-secondary: #6e6e73;
        --apple-border: rgba(0, 0, 0, 0.08);
        --apple-accent: #f5f5f7;
        --apple-blue: #0066cc;
        --apple-blue-hover: #0077ed;
        --apple-green: #34c759;
        --apple-danger: #ff3b30;
        --apple-radius: 22px;
        --apple-radius-sm: 14px;
        --apple-shadow: 0 4px 24px rgba(0,0,0,0.04);
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }

    /* Wizard shell */
    .wiz {
        max-width: 760px;
        margin: 0 auto;
        display: grid;
        gap: 24px;
        padding-bottom: 60px;
        color: var(--apple-text);
    }

    /* Stepper */
    .wiz-stepper {
        background: var(--apple-card);
        border: 1px solid var(--apple-border);
        border-radius: var(--apple-radius);
        padding: 20px 22px;
        box-shadow: var(--apple-shadow);
    }
    .wiz-stepper-top {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 14px; gap: 12px; flex-wrap: wrap;
    }
    .wiz-stepper-title { font-size: 15px; font-weight: 700; color: var(--apple-text); }
    .wiz-stepper-progress { font-size: 13px; color: var(--apple-text-secondary); font-weight: 500; }
    .wiz-stepper-bar { display: flex; gap: 8px; align-items: center; }
    .wiz-dot {
        width: 32px; height: 32px; border-radius: 50%;
        background: var(--apple-accent);
        color: var(--apple-text-secondary);
        display: grid; place-items: center;
        font-size: 13px; font-weight: 700;
        flex-shrink: 0;
        transition: var(--transition);
    }
    .wiz-dot.is-active { background: var(--apple-text); color: #fff; transform: scale(1.05); }
    .wiz-dot.is-done { background: var(--apple-green); color: #fff; }
    .wiz-dot-label {
        font-size: 11px; font-weight: 700;
        color: var(--apple-text-secondary);
        text-align: center;
        margin-top: 4px;
    }
    .wiz-dot-wrap { display: flex; flex-direction: column; align-items: center; gap: 0; }
    .wiz-line { flex: 1; height: 3px; background: var(--apple-accent); border-radius: 3px; margin: 0 4px; transition: background .25s ease; }
    .wiz-line.is-done { background: var(--apple-green); }
    .wiz-stepper-mobile-labels { display: none; }

    /* Step card */
    .wiz-step { display: none; }
    .wiz-step.is-active { display: block; animation: wizFade 0.25s ease; }
    @keyframes wizFade { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    .wiz-card {
        background: var(--apple-card);
        border: 1px solid var(--apple-border);
        border-radius: var(--apple-radius);
        padding: 32px;
        box-shadow: var(--apple-shadow);
    }
    .wiz-step-num {
        display: inline-block;
        font-size: 12px; font-weight: 700;
        letter-spacing: 0.05em; text-transform: uppercase;
        color: var(--apple-blue);
        margin-bottom: 8px;
    }
    .wiz-step h2 {
        margin: 0 0 8px;
        font-size: clamp(22px, 3vw, 28px);
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .wiz-step .wiz-lead {
        margin: 0 0 24px;
        font-size: 16px;
        line-height: 1.5;
        color: var(--apple-text-secondary);
    }

    /* Step nav (back/next) */
    .wiz-nav {
        display: flex; gap: 12px;
        margin-top: 28px; padding-top: 24px;
        border-top: 1px solid var(--apple-border);
        align-items: center; justify-content: space-between; flex-wrap: wrap;
    }
    .wiz-nav-left, .wiz-nav-right { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .wiz-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        padding: 14px 26px;
        border-radius: 999px;
        font-size: 16px; font-weight: 600;
        border: none; cursor: pointer;
        text-decoration: none;
        transition: var(--transition);
        min-width: 130px;
    }
    .wiz-btn-primary { background: var(--apple-blue); color: #fff; }
    .wiz-btn-primary:hover:not(:disabled) { background: var(--apple-blue-hover); transform: translateY(-1px); }
    .wiz-btn-primary:disabled { opacity: 0.45; cursor: not-allowed; }
    .wiz-btn-secondary { background: var(--apple-accent); color: var(--apple-text); }
    .wiz-btn-secondary:hover { background: rgba(0,0,0,0.08); }
    .wiz-btn-success { background: var(--apple-green); color: #fff; }
    .wiz-btn-success:hover:not(:disabled) { background: #28a745; transform: translateY(-1px); }
    .wiz-btn-ghost { background: transparent; color: var(--apple-text-secondary); min-width: 0; padding: 10px 18px; }
    .wiz-btn-ghost:hover { color: var(--apple-text); }

    /* Source picker */
    .wiz-sources { display: grid; grid-template-columns: 1fr; gap: 14px; }
    @media (min-width: 560px) {
        .wiz-sources { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
    }
    .wiz-source {
        position: relative; cursor: pointer;
        padding: 22px;
        background: var(--apple-card);
        border: 2px solid var(--apple-border);
        border-radius: var(--apple-radius-sm);
        text-decoration: none; color: inherit;
        transition: var(--transition);
        display: block;
    }
    .wiz-source:hover { border-color: var(--apple-blue); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
    .wiz-source.is-active { border-color: var(--apple-blue); background: #f0f8ff; box-shadow: 0 0 0 4px rgba(0, 102, 204, 0.12); }
    .wiz-source.is-disabled { opacity: 0.45; pointer-events: none; background: var(--apple-accent); }
    .wiz-source-label { font-size: 18px; font-weight: 700; margin-bottom: 6px; letter-spacing: -0.01em; }
    .wiz-source-copy { font-size: 14px; line-height: 1.5; color: var(--apple-text-secondary); }
    .wiz-source-status { display: inline-block; margin-top: 8px; padding: 3px 10px; border-radius: 999px; background: rgba(0, 102, 204, 0.1); color: var(--apple-blue); font-size: 12px; font-weight: 700; }
    .wiz-source-input { position: absolute; opacity: 0; pointer-events: none; }

    /* Forms - bigger touch targets, softer focus */
    .wiz-field { display: grid; gap: 8px; margin-bottom: 18px; }
    .wiz-field:last-child { margin-bottom: 0; }
    .wiz-field label, .wiz-label { font-size: 15px; font-weight: 600; color: var(--apple-text); }
    .wiz-field label .req { color: var(--apple-danger); }
    .wiz-input, .wiz-select, .wiz-textarea {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid var(--apple-border);
        background: var(--apple-accent);
        border-radius: 12px;
        font-size: 16px;
        font-family: inherit;
        color: var(--apple-text);
        transition: var(--transition);
    }
    .wiz-input:focus, .wiz-select:focus, .wiz-textarea:focus {
        background: #fff; border-color: var(--apple-blue);
        outline: none; box-shadow: 0 0 0 4px rgba(0, 102, 204, 0.15);
    }
    .wiz-textarea { min-height: 140px; resize: vertical; line-height: 1.5; }
    .wiz-hint { font-size: 13px; color: var(--apple-text-secondary); }
    .wiz-row-2 { display: grid; grid-template-columns: 1fr; gap: 18px; }
    @media (min-width: 560px) { .wiz-row-2 { grid-template-columns: 1fr 1fr; } }

    /* Photo upload */
    .wiz-upload {
        text-align: center;
        padding: 36px 20px;
        background: #fbfbfd;
        border: 2px dashed var(--apple-border);
        border-radius: var(--apple-radius-sm);
        transition: var(--transition);
    }
    .wiz-upload:hover { border-color: var(--apple-blue); background: #f0f8ff; }
    .wiz-upload-title { font-size: 17px; font-weight: 700; margin-bottom: 14px; }
    .wiz-file {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 14px 26px; border-radius: 999px;
        background: var(--apple-blue); color: #fff;
        font-size: 16px; font-weight: 600; cursor: pointer;
        transition: var(--transition); position: relative;
    }
    .wiz-file:hover { background: var(--apple-blue-hover); transform: scale(1.02); }
    .wiz-file input { position: absolute; opacity: 0; pointer-events: none; width: 0; }
    .wiz-upload-hint { font-size: 13px; color: var(--apple-text-secondary); margin-top: 12px; }

    /* Photo grid */
    .wiz-gallery { margin-top: 22px; }
    .wiz-gallery-title { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--apple-text-secondary); margin-bottom: 12px; }
    .wiz-media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; }
    .wiz-media { background: var(--apple-card); border: 1px solid var(--apple-border); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; }
    .wiz-frame { position: relative; height: 130px; background: var(--apple-accent); overflow: hidden; }
    .wiz-frame img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .wiz-badges { position: absolute; top: 8px; left: 8px; display: flex; gap: 6px; flex-wrap: wrap; }
    .wiz-badge { padding: 4px 8px; background: rgba(255,255,255,0.95); border-radius: 6px; font-size: 10px; font-weight: 700; backdrop-filter: blur(4px); color: var(--apple-text); }
    .wiz-badge.cover { background: var(--apple-text); color: #fff; }
    .wiz-media-meta { padding: 10px 12px; flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .wiz-media-name { font-size: 12px; font-weight: 600; word-break: break-word; line-height: 1.3; }
    .wiz-media-sub { font-size: 11px; color: var(--apple-text-secondary); }
    .wiz-remove {
        margin: 0 12px 12px; padding: 8px 12px;
        background: var(--apple-accent); color: var(--apple-danger);
        border: none; border-radius: 8px;
        font-size: 13px; font-weight: 600; cursor: pointer;
        transition: var(--transition);
    }
    .wiz-remove:hover { background: #ffefef; }
    .wiz-empty {
        padding: 24px; border: 1px dashed var(--apple-border);
        border-radius: 12px; background: #fbfbfd;
        text-align: center; color: var(--apple-text-secondary);
        font-size: 14px;
    }

    /* Checkbox card */
    .wiz-check {
        display: flex; gap: 14px; align-items: flex-start;
        padding: 18px; background: var(--apple-accent);
        border: 1px solid var(--apple-border); border-radius: var(--apple-radius-sm);
        cursor: pointer; transition: var(--transition);
    }
    .wiz-check:hover { border-color: var(--apple-blue); }
    .wiz-check input { width: 22px; height: 22px; flex-shrink: 0; accent-color: var(--apple-blue); margin-top: 2px; cursor: pointer; }
    .wiz-check-text strong { display: block; font-size: 15px; font-weight: 600; margin-bottom: 4px; }
    .wiz-check-text span { font-size: 14px; color: var(--apple-text-secondary); line-height: 1.4; }

    /* Optional details (collapsed extras) */
    .wiz-extras {
        margin-top: 24px; padding-top: 24px;
        border-top: 1px solid var(--apple-border);
    }
    .wiz-extras-toggle {
        display: flex; align-items: center; gap: 10px;
        padding: 14px 18px; background: var(--apple-accent);
        border: 1px solid var(--apple-border); border-radius: 12px;
        font-size: 15px; font-weight: 600; cursor: pointer; width: 100%;
        text-align: left; color: var(--apple-text);
        transition: var(--transition);
    }
    .wiz-extras-toggle:hover { background: rgba(0,0,0,0.05); }
    .wiz-extras-toggle .chev { margin-left: auto; transition: transform .2s ease; }
    .wiz-extras.is-open .chev { transform: rotate(180deg); }
    .wiz-extras-body { display: none; margin-top: 18px; }
    .wiz-extras.is-open .wiz-extras-body { display: block; }
    .wiz-extras-section { margin-bottom: 24px; }
    .wiz-extras-section h4 { margin: 0 0 14px; font-size: 16px; font-weight: 700; color: var(--apple-text); }

    /* Review */
    .wiz-review { display: grid; gap: 14px; }
    .wiz-review-row {
        display: grid; grid-template-columns: 130px 1fr auto; gap: 12px;
        padding: 14px 16px; background: var(--apple-accent);
        border-radius: 12px; align-items: center;
    }
    .wiz-review-row.full { grid-template-columns: 130px 1fr; }
    .wiz-review-key { font-size: 13px; font-weight: 600; color: var(--apple-text-secondary); }
    .wiz-review-val { font-size: 15px; font-weight: 600; color: var(--apple-text); word-break: break-word; }
    .wiz-review-val.muted { color: var(--apple-text-secondary); font-weight: 500; font-style: italic; }
    .wiz-review-edit {
        background: transparent; border: none; cursor: pointer;
        font-size: 13px; font-weight: 600; color: var(--apple-blue);
        padding: 6px 12px; border-radius: 999px;
        transition: var(--transition);
    }
    .wiz-review-edit:hover { background: rgba(0, 102, 204, 0.1); }
    .wiz-review-photos { display: flex; gap: 8px; flex-wrap: wrap; }
    .wiz-review-photo {
        width: 48px; height: 48px; border-radius: 8px;
        background: var(--apple-accent) center/cover no-repeat;
        border: 1px solid var(--apple-border);
    }
    .wiz-review-photo.more {
        display: grid; place-items: center;
        font-size: 12px; font-weight: 700; color: var(--apple-text-secondary);
    }

    /* Help link below stepper */
    .wiz-help {
        text-align: center; padding-top: 6px;
        font-size: 13px; color: var(--apple-text-secondary);
    }
    .wiz-help a { color: var(--apple-blue); text-decoration: none; font-weight: 600; }
    .wiz-help a:hover { text-decoration: underline; }

    @media (max-width: 640px) {
        .wiz-card { padding: 22px 18px; }
        .wiz-stepper { padding: 16px; }
        .wiz-btn { padding: 14px 22px; min-width: 110px; font-size: 15px; }
        .wiz-nav-left, .wiz-nav-right { width: 100%; }
        .wiz-nav-right { justify-content: flex-end; }
        .wiz-nav { gap: 16px; }
        .wiz-dot { width: 28px; height: 28px; font-size: 12px; }
        .wiz-review-row { grid-template-columns: 1fr; }
        .wiz-review-row.full { grid-template-columns: 1fr; }
    }
</style>

<div class="wiz" id="wizard"
     data-edit="{{ $listing ? '1' : '0' }}"
     data-source-enabled="{{ $selectedSourceEnabled ? '1' : '0' }}">

    {{-- Stepper --}}
    <div class="wiz-stepper">
        <div class="wiz-stepper-top">
            <div class="wiz-stepper-title" id="wiz-step-title">Step 1 of 5</div>
            <div class="wiz-stepper-progress"><span id="wiz-step-pct">20</span>% complete</div>
        </div>
        <div class="wiz-stepper-bar">
            @foreach($stepLabels as $stepNum => $label)
                <div class="wiz-dot-wrap">
                    <div class="wiz-dot" data-dot="{{ $stepNum }}">{{ $stepNum }}</div>
                </div>
                @if(!$loop->last)
                    <div class="wiz-line" data-line="{{ $stepNum }}"></div>
                @endif
            @endforeach
        </div>
        <div class="wiz-help">
            Need help? <a href="{{ route('tutorials.show', $listing ? 'edit-listing' : 'add-listing') }}">See the picture guide</a>
        </div>
    </div>

    @if($listing)
        <input type="hidden" name="original_source" value="{{ $originalFormSource }}">
        <input type="hidden" name="return_source" value="{{ $formReturnSource }}">
    @else
        <input type="hidden" name="source" id="wiz-source-input" value="{{ $selectedFormSource }}">
    @endif

    {{-- ============ STEP 1: SOURCE ============ --}}
    <section class="wiz-step is-active" data-step="1">
        <div class="wiz-card">
            <span class="wiz-step-num">Step 1 of 5</span>
            <h2>{{ $listing ? 'Where does this listing belong?' : 'Where would you like to add this listing?' }}</h2>
            <p class="wiz-lead">
                {{ $listing
                    ? 'You can keep it where it is, or move it somewhere else when you save.'
                    : 'Pick one. If you\'re not sure, choose IPP &mdash; you can always move it later.' }}
            </p>

            @if($createSourceTabs->isNotEmpty())
                <div class="wiz-sources">
                    @foreach($createSourceTabs as $sourceTab)
                        @php
                            $isActiveSource = $selectedFormSource === $sourceTab['key'];
                            $cls = 'wiz-source' . ($isActiveSource ? ' is-active' : '') . (! $sourceTab['enabled'] ? ' is-disabled' : '');
                        @endphp
                        @if($listing)
                            <label class="{{ $cls }}">
                                <input class="wiz-source-input" type="radio" name="source" value="{{ $sourceTab['key'] }}" @checked($isActiveSource) @disabled(! $sourceTab['enabled'])>
                                <div class="wiz-source-label">{{ $sourceTab['label'] }}</div>
                                <div class="wiz-source-copy">{{ $sourceTab['description'] }}</div>
                                @if($originalFormSource === $sourceTab['key'])
                                    <span class="wiz-source-status">Currently here</span>
                                @elseif($isActiveSource)
                                    <span class="wiz-source-status">Will move on save</span>
                                @endif
                            </label>
                        @elseif($sourceTab['enabled'])
                            <a href="{{ route('listings.create', ['source' => $sourceTab['key']]) }}" class="{{ $cls }}">
                                <div class="wiz-source-label">{{ $sourceTab['label'] }}</div>
                                <div class="wiz-source-copy">{{ $sourceTab['description'] }}</div>
                                @if($isActiveSource)
                                    <span class="wiz-source-status">Selected</span>
                                @endif
                            </a>
                        @else
                            <div class="{{ $cls }}">
                                <div class="wiz-source-label">{{ $sourceTab['label'] }}</div>
                                <div class="wiz-source-copy">{{ $sourceTab['description'] }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if(! $selectedSourceEnabled)
                <div class="wiz-empty" style="margin-top:18px;color:#b25e00;background:#fff5e5;border-color:#ffd9a8;border-style:solid;">
                    This source isn&rsquo;t available right now. Pick a different one above.
                </div>
            @endif
        </div>

        <div class="wiz-nav">
            <div class="wiz-nav-left">
                <a href="{{ $listing ? route('listings.show', array_filter(['id' => $listing->id, 'source' => $originalFormSource === 'ipp' ? null : $originalFormSource, 'return_source' => $formReturnSource], static fn ($v) => $v !== null)) : route('listings.index', ['source' => $selectedFormSource]) }}" class="wiz-btn wiz-btn-ghost">Cancel</a>
            </div>
            <div class="wiz-nav-right">
                <button type="button" class="wiz-btn wiz-btn-primary" data-wiz-next>Continue &rarr;</button>
            </div>
        </div>
    </section>

    {{-- ============ STEP 2: PHOTOS ============ --}}
    <section class="wiz-step" data-step="2">
        <div class="wiz-card">
            <span class="wiz-step-num">Step 2 of 5</span>
            <h2>Add property photos</h2>
            <p class="wiz-lead">
                The first photo becomes the cover image. You can add as many as you like.
            </p>

            <div class="wiz-upload">
                <div class="wiz-upload-title">Add photos from your device</div>
                <label class="wiz-file">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Choose Images
                    <input id="listing-new-images" type="file" name="new_images[]" accept="image/*" multiple @disabled(! $selectedSourceEnabled)>
                </label>
                <div class="wiz-upload-hint">JPG, PNG, WEBP, GIF, BMP &mdash; up to 10MB each.</div>
            </div>

            <div class="wiz-gallery">
                <div class="wiz-gallery-title">
                    Saved photos &middot; <span data-gallery-total>{{ count($existingPhotos) }}</span>
                </div>
                <div class="wiz-media-grid" id="existing-photo-grid">
                    @forelse($existingPhotos as $photoPath)
                        @php
                            $photoUrl = \App\Support\SharedAssetUrl::listing($listingUsername, $listingPropertyId, $photoPath)
                                ?? \App\Support\SharedAssetUrl::storage($photoPath);
                        @endphp
                        <article class="wiz-media" data-existing-photo>
                            <div class="wiz-frame">
                                <div class="wiz-badges">
                                    <span class="wiz-badge">Saved</span>
                                    @if($loop->first)<span class="wiz-badge cover">Cover</span>@endif
                                </div>
                                @if($photoUrl)<img src="{{ $photoUrl }}" alt="Saved photo" loading="lazy">@endif
                            </div>
                            <div class="wiz-media-meta">
                                <div class="wiz-media-name">{{ basename($photoPath) ?: 'Saved image' }}</div>
                            </div>
                            <input type="hidden" name="existing_photos[]" value="{{ $photoPath }}">
                            <button type="button" class="wiz-remove" data-remove-existing>Remove</button>
                        </article>
                    @empty
                        <div class="wiz-empty" id="existing-photo-empty" style="grid-column:1/-1;">No saved photos yet. Add some above.</div>
                    @endforelse
                </div>
            </div>

            <div class="wiz-gallery">
                <div class="wiz-gallery-title">New uploads &middot; <span data-new-total>0</span></div>
                <div class="wiz-empty" id="new-photo-empty">Photos you pick will preview here before saving.</div>
                <div class="wiz-media-grid" id="new-photo-grid"></div>
            </div>
        </div>

        <div class="wiz-nav">
            <div class="wiz-nav-left">
                <button type="button" class="wiz-btn wiz-btn-secondary" data-wiz-back>&larr; Back</button>
            </div>
            <div class="wiz-nav-right">
                <button type="button" class="wiz-btn wiz-btn-primary" data-wiz-next>Continue &rarr;</button>
            </div>
        </div>
    </section>

    {{-- ============ STEP 3: BASICS ============ --}}
    <section class="wiz-step" data-step="3">
        <div class="wiz-card">
            <span class="wiz-step-num">Step 3 of 5</span>
            <h2>The basics</h2>
            <p class="wiz-lead">
                Just a few things about the property. Fields with a <span style="color:var(--apple-danger);">*</span> are required.
            </p>

            <div class="wiz-field">
                <label for="propertyname">Property name <span class="req">*</span></label>
                <input class="wiz-input" id="propertyname" name="propertyname" type="text" value="{{ old('propertyname', $form['propertyname']) }}" maxlength="100" required placeholder="e.g. Sunset View Condo">
            </div>

            <div class="wiz-row-2">
                <div class="wiz-field">
                    <label for="listingtype">Listing type <span class="req">*</span></label>
                    <select class="wiz-select" id="listingtype" name="listingtype" required>
                        @foreach($listingTypes as $listingType)
                            <option value="{{ $listingType }}" @selected(old('listingtype', $form['listingtype']) === $listingType)>{{ $listingType }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="wiz-field">
                    <label for="propertytype">Property type <span class="req">*</span></label>
                    <select class="wiz-select" id="propertytype" name="propertytype" required>
                        @foreach($propertyTypes as $propertyType)
                            <option value="{{ $propertyType }}" @selected(old('propertytype', $form['propertytype']) === $propertyType)>{{ $propertyType }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="wiz-row-2">
                <div class="wiz-field">
                    <label for="price">Price <span class="req">*</span></label>
                    <input class="wiz-input" id="price" name="price" type="text" value="{{ old('price', $form['price']) }}" inputmode="decimal" required placeholder="e.g. 450000">
                    <div class="wiz-hint">Just the number. No commas needed.</div>
                </div>
                <div class="wiz-field">
                    <label for="state">State <span class="req">*</span></label>
                    <select class="wiz-select" id="state" name="state" required>
                        <option value="">Select state</option>
                        @foreach($states as $state)
                            <option value="{{ $state->state }}" @selected(old('state', $form['state']) === $state->state)>{{ $state->state }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="wiz-field">
                <label for="area">Area <span class="req">*</span></label>
                <input class="wiz-input" id="area" name="area" type="text" value="{{ old('area', $form['area']) }}" maxlength="100" required placeholder="e.g. Mont Kiara">
            </div>
        </div>

        <div class="wiz-nav">
            <div class="wiz-nav-left">
                <button type="button" class="wiz-btn wiz-btn-secondary" data-wiz-back>&larr; Back</button>
            </div>
            <div class="wiz-nav-right">
                <button type="button" class="wiz-btn wiz-btn-primary" data-wiz-next>Continue &rarr;</button>
            </div>
        </div>
    </section>

    {{-- ============ STEP 4: DESCRIPTION + EXTRAS ============ --}}
    <section class="wiz-step" data-step="4">
        <div class="wiz-card">
            <span class="wiz-step-num">Step 4 of 5</span>
            <h2>Tell us more (optional)</h2>
            <p class="wiz-lead">
                Add a short description so people understand what makes this place special. Everything on this step is optional.
            </p>

            <div class="wiz-field">
                <label for="description">Description</label>
                <textarea class="wiz-textarea" id="description" name="description" rows="6" placeholder="A few sentences about the property...">{{ old('description', $form['description']) }}</textarea>
            </div>

            <div class="wiz-field">
                <label for="keywords">Keywords</label>
                <input class="wiz-input" id="keywords" name="keywords" type="text" value="{{ old('keywords', $form['keywords']) }}" maxlength="500" placeholder="e.g. luxury, near LRT, freehold">
                <div class="wiz-hint">Leave blank and we&rsquo;ll fill them in for you.</div>
            </div>

            <label class="wiz-check">
                <input type="checkbox" name="cobroke" value="1" @checked((int) old('cobroke', $form['cobroke']) === 1)>
                <div class="wiz-check-text">
                    <strong>Allow co-broke</strong>
                    <span>Other agents can help sell or rent this property.</span>
                </div>
            </label>

            @if($hasExtras)
                <div class="wiz-extras" id="wiz-extras">
                    <button type="button" class="wiz-extras-toggle" id="wiz-extras-toggle">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Add more details (rooms, size, facilities…)
                        <svg class="chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="wiz-extras-body">
                        @foreach($generalFieldGroups as $sectionKey => $fields)
                            <div class="wiz-extras-section">
                                <h4>{{ $generalSectionTitles[$sectionKey] ?? \Illuminate\Support\Str::headline($sectionKey) }}</h4>
                                <div class="wiz-row-2">
                                    @foreach($fields as $field)
                                        <div class="wiz-field" @if(($field['type'] ?? 'text') === 'textarea') style="grid-column:1/-1;" @endif>
                                            <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                                            @if(($field['type'] ?? 'text') === 'textarea')
                                                <textarea
                                                    class="wiz-textarea"
                                                    id="{{ $field['name'] }}"
                                                    name="{{ $field['name'] }}"
                                                    rows="{{ $field['rows'] ?? 4 }}"
                                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                                >{{ old($field['name'], $form[$field['name']] ?? '') }}</textarea>
                                            @else
                                                <input
                                                    class="wiz-input"
                                                    id="{{ $field['name'] }}"
                                                    name="{{ $field['name'] }}"
                                                    type="text"
                                                    value="{{ old($field['name'], $form[$field['name']] ?? '') }}"
                                                    maxlength="{{ $field['max'] ?? 255 }}"
                                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                                >
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="wiz-nav">
            <div class="wiz-nav-left">
                <button type="button" class="wiz-btn wiz-btn-secondary" data-wiz-back>&larr; Back</button>
            </div>
            <div class="wiz-nav-right">
                <button type="button" class="wiz-btn wiz-btn-primary" data-wiz-next>Continue &rarr;</button>
            </div>
        </div>
    </section>

    {{-- ============ STEP 5: REVIEW ============ --}}
    <section class="wiz-step" data-step="5">
        <div class="wiz-card">
            <span class="wiz-step-num">Step 5 of 5</span>
            <h2>Review &amp; save</h2>
            <p class="wiz-lead">
                Take one last look. If something needs fixing, tap <strong>Edit</strong> next to it.
                Otherwise, click the green <strong>Save</strong> button below.
            </p>

            <div class="wiz-review">
                <div class="wiz-review-row">
                    <div class="wiz-review-key">Listing area</div>
                    <div class="wiz-review-val" id="wiz-review-source">{{ strtoupper($selectedFormSource) }}</div>
                    <button type="button" class="wiz-review-edit" data-wiz-jump="1">Edit</button>
                </div>

                <div class="wiz-review-row">
                    <div class="wiz-review-key">Photos</div>
                    <div class="wiz-review-val" id="wiz-review-photos-count">{{ count($existingPhotos) }} photo{{ count($existingPhotos) === 1 ? '' : 's' }}</div>
                    <button type="button" class="wiz-review-edit" data-wiz-jump="2">Edit</button>
                </div>

                <div class="wiz-review-row full" style="grid-template-columns: 130px 1fr auto;">
                    <div class="wiz-review-key">Preview</div>
                    <div class="wiz-review-photos" id="wiz-review-photos"></div>
                    <button type="button" class="wiz-review-edit" data-wiz-jump="2">Edit</button>
                </div>

                <div class="wiz-review-row">
                    <div class="wiz-review-key">Property name</div>
                    <div class="wiz-review-val" id="wiz-review-name">—</div>
                    <button type="button" class="wiz-review-edit" data-wiz-jump="3">Edit</button>
                </div>

                <div class="wiz-review-row">
                    <div class="wiz-review-key">Listing type</div>
                    <div class="wiz-review-val" id="wiz-review-listingtype">—</div>
                    <button type="button" class="wiz-review-edit" data-wiz-jump="3">Edit</button>
                </div>

                <div class="wiz-review-row">
                    <div class="wiz-review-key">Property type</div>
                    <div class="wiz-review-val" id="wiz-review-propertytype">—</div>
                    <button type="button" class="wiz-review-edit" data-wiz-jump="3">Edit</button>
                </div>

                <div class="wiz-review-row">
                    <div class="wiz-review-key">Price</div>
                    <div class="wiz-review-val" id="wiz-review-price">—</div>
                    <button type="button" class="wiz-review-edit" data-wiz-jump="3">Edit</button>
                </div>

                <div class="wiz-review-row">
                    <div class="wiz-review-key">Location</div>
                    <div class="wiz-review-val" id="wiz-review-location">—</div>
                    <button type="button" class="wiz-review-edit" data-wiz-jump="3">Edit</button>
                </div>

                <div class="wiz-review-row">
                    <div class="wiz-review-key">Description</div>
                    <div class="wiz-review-val" id="wiz-review-description">—</div>
                    <button type="button" class="wiz-review-edit" data-wiz-jump="4">Edit</button>
                </div>
            </div>
        </div>

        <div class="wiz-nav">
            <div class="wiz-nav-left">
                <button type="button" class="wiz-btn wiz-btn-secondary" data-wiz-back>&larr; Back</button>
            </div>
            <div class="wiz-nav-right">
                <button type="submit" class="wiz-btn wiz-btn-success" id="wiz-submit" @disabled(! $selectedSourceEnabled)>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    {{ ! $selectedSourceEnabled ? 'Source not ready' : ($listing ? 'Save changes' : 'Save listing') }}
                </button>
            </div>
        </div>
    </section>
</div>

<script>
(function () {
    const wizard = document.getElementById('wizard');
    if (!wizard) return;

    const steps = Array.from(wizard.querySelectorAll('.wiz-step'));
    const dots  = Array.from(wizard.querySelectorAll('.wiz-dot'));
    const lines = Array.from(wizard.querySelectorAll('.wiz-line'));
    const titleEl = document.getElementById('wiz-step-title');
    const pctEl   = document.getElementById('wiz-step-pct');
    const stepLabels = @json($stepLabels);
    const totalSteps = steps.length;

    let current = 1;

    function showStep(n) {
        n = Math.max(1, Math.min(totalSteps, n));
        current = n;
        steps.forEach((step) => {
            const idx = parseInt(step.dataset.step, 10);
            step.classList.toggle('is-active', idx === n);
        });
        dots.forEach((d) => {
            const idx = parseInt(d.dataset.dot, 10);
            d.classList.toggle('is-active', idx === n);
            d.classList.toggle('is-done', idx < n);
            if (idx < n) d.innerHTML = '✓';
            else d.textContent = String(idx);
        });
        lines.forEach((l) => {
            const idx = parseInt(l.dataset.line, 10);
            l.classList.toggle('is-done', idx < n);
        });
        titleEl.textContent = `Step ${n} of ${totalSteps} — ${stepLabels[n] || ''}`;
        pctEl.textContent = Math.round((n / totalSteps) * 100);
        if (n === totalSteps) refreshReview();
        window.scrollTo({ top: wizard.offsetTop - 16, behavior: 'smooth' });
    }

    function validateCurrentStep() {
        const step = steps.find((s) => parseInt(s.dataset.step, 10) === current);
        if (!step) return true;
        const required = step.querySelectorAll('input[required], select[required], textarea[required]');
        for (const field of required) {
            if (field.disabled) continue;
            if (!field.checkValidity()) {
                field.reportValidity();
                field.focus();
                return false;
            }
        }
        return true;
    }

    wizard.addEventListener('click', (e) => {
        const next = e.target.closest('[data-wiz-next]');
        const back = e.target.closest('[data-wiz-back]');
        const jump = e.target.closest('[data-wiz-jump]');
        if (next) {
            e.preventDefault();
            if (validateCurrentStep()) showStep(current + 1);
        } else if (back) {
            e.preventDefault();
            showStep(current - 1);
        } else if (jump) {
            e.preventDefault();
            showStep(parseInt(jump.dataset.wizJump, 10));
        }
    });

    /* Source selection (edit mode radios) */
    wizard.querySelectorAll('.wiz-source-input').forEach((input) => {
        input.addEventListener('change', () => {
            wizard.querySelectorAll('.wiz-source').forEach((el) => el.classList.remove('is-active'));
            input.closest('.wiz-source')?.classList.add('is-active');
        });
    });

    /* Optional extras toggle */
    const extras = document.getElementById('wiz-extras');
    document.getElementById('wiz-extras-toggle')?.addEventListener('click', () => {
        extras.classList.toggle('is-open');
    });

    /* Photo upload handling */
    const input = document.getElementById('listing-new-images');
    const existingGrid = document.getElementById('existing-photo-grid');
    const newGrid = document.getElementById('new-photo-grid');
    const newEmpty = document.getElementById('new-photo-empty');
    const total = document.querySelector('[data-gallery-total]');
    const newTotal = document.querySelector('[data-new-total]');

    let transfer = new DataTransfer();
    let previewUrls = [];

    function fileSize(bytes) {
        return bytes < 1048576 ? Math.max(1, Math.round(bytes / 1024)) + ' KB' : (bytes / 1048576).toFixed(1) + ' MB';
    }
    function revokeUrls() {
        previewUrls.forEach((u) => URL.revokeObjectURL(u));
        previewUrls = [];
    }
    function ensureExistingEmpty(count) {
        let empty = document.getElementById('existing-photo-empty');
        if (count === 0 && !empty) {
            empty = document.createElement('div');
            empty.id = 'existing-photo-empty';
            empty.className = 'wiz-empty';
            empty.style.gridColumn = '1/-1';
            empty.textContent = 'No saved photos yet. Add some above.';
            existingGrid.appendChild(empty);
        }
        if (count > 0 && empty) empty.remove();
    }
    function updateSummary() {
        if (!existingGrid || !newGrid) return;
        const existingCards = existingGrid.querySelectorAll('[data-existing-photo]');
        const existingCount = existingCards.length;
        const uploadCount = transfer.files.length;
        ensureExistingEmpty(existingCount);
        existingCards.forEach((card, idx) => {
            const oldCover = card.querySelector('.wiz-badge.cover');
            if (oldCover) oldCover.remove();
            if (idx === 0) {
                const cover = document.createElement('span');
                cover.className = 'wiz-badge cover';
                cover.textContent = 'Cover';
                card.querySelector('.wiz-badges')?.appendChild(cover);
            }
        });
        if (total) total.textContent = existingCount;
        if (newTotal) newTotal.textContent = uploadCount;
    }
    function renderUploads() {
        if (!newGrid) return;
        revokeUrls();
        newGrid.innerHTML = '';
        Array.from(transfer.files).forEach((file, idx) => {
            const url = URL.createObjectURL(file);
            previewUrls.push(url);
            const card = document.createElement('article');
            card.className = 'wiz-media';
            card.innerHTML = `
                <div class="wiz-frame">
                    <div class="wiz-badges"><span class="wiz-badge">New</span>${idx === 0 && existingGrid.querySelectorAll('[data-existing-photo]').length === 0 ? '<span class="wiz-badge cover">Cover</span>' : ''}</div>
                    <img src="${url}" alt="">
                </div>
                <div class="wiz-media-meta">
                    <div class="wiz-media-name"></div>
                    <div class="wiz-media-sub"></div>
                </div>
                <button type="button" class="wiz-remove">Remove</button>
            `;
            card.querySelector('.wiz-media-name').textContent = file.name;
            card.querySelector('.wiz-media-sub').textContent = fileSize(file.size);
            card.querySelector('.wiz-remove').addEventListener('click', () => {
                const next = new DataTransfer();
                Array.from(transfer.files).forEach((f, i) => { if (i !== idx) next.items.add(f); });
                transfer = next;
                input.files = transfer.files;
                renderUploads();
                updateSummary();
            });
            card.querySelector('img').src = url;
            newGrid.appendChild(card);
        });
        if (newEmpty) newEmpty.style.display = transfer.files.length === 0 ? 'block' : 'none';
    }

    input?.addEventListener('change', () => {
        Array.from(input.files).forEach((file) => {
            const dup = Array.from(transfer.files).some((existing) =>
                existing.name === file.name && existing.size === file.size && existing.lastModified === file.lastModified
            );
            if (!dup) transfer.items.add(file);
        });
        input.files = transfer.files;
        renderUploads();
        updateSummary();
    });

    existingGrid?.querySelectorAll('[data-remove-existing]').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.closest('[data-existing-photo]')?.remove();
            updateSummary();
            renderUploads();
        });
    });

    /* Review summary */
    function getVal(id) {
        const el = document.getElementById(id);
        if (!el) return '';
        if (el.tagName === 'SELECT') {
            return el.options[el.selectedIndex]?.text || el.value || '';
        }
        return (el.value || '').trim();
    }
    function refreshReview() {
        const rev = (id, val, fallback) => {
            const el = document.getElementById(id);
            if (!el) return;
            const trimmed = (val || '').toString().trim();
            if (trimmed) {
                el.textContent = trimmed;
                el.classList.remove('muted');
            } else {
                el.textContent = fallback || '— not added —';
                el.classList.add('muted');
            }
        };

        // Source
        const sourceInput = document.getElementById('wiz-source-input');
        let source = '';
        if (sourceInput) {
            source = sourceInput.value;
        } else {
            const checkedRadio = wizard.querySelector('.wiz-source-input:checked');
            source = checkedRadio ? checkedRadio.value : '';
        }
        rev('wiz-review-source', source ? source.toUpperCase() : '');

        // Name & types
        rev('wiz-review-name', getVal('propertyname'));
        rev('wiz-review-listingtype', getVal('listingtype'));
        rev('wiz-review-propertytype', getVal('propertytype'));
        const price = getVal('price');
        rev('wiz-review-price', price ? price : '', '— not added —');
        const area = getVal('area');
        const state = getVal('state');
        const loc = [area, state].filter(Boolean).join(', ');
        rev('wiz-review-location', loc);
        rev('wiz-review-description', getVal('description'));

        // Photos count
        const existingCount = existingGrid ? existingGrid.querySelectorAll('[data-existing-photo]').length : 0;
        const newCount = transfer.files.length;
        const totalCount = existingCount + newCount;
        const photoLabel = document.getElementById('wiz-review-photos-count');
        if (photoLabel) {
            photoLabel.textContent = totalCount === 0
                ? 'No photos yet'
                : `${totalCount} photo${totalCount === 1 ? '' : 's'} (${existingCount} saved + ${newCount} new)`;
        }

        // Photo previews
        const photoBox = document.getElementById('wiz-review-photos');
        if (photoBox) {
            photoBox.innerHTML = '';
            const max = 6;
            const sources = [];
            existingGrid?.querySelectorAll('[data-existing-photo] img').forEach((img) => sources.push(img.src));
            previewUrls.forEach((u) => sources.push(u));
            sources.slice(0, max).forEach((src) => {
                const div = document.createElement('div');
                div.className = 'wiz-review-photo';
                div.style.backgroundImage = `url("${src.replace(/"/g, '\\"')}")`;
                photoBox.appendChild(div);
            });
            if (sources.length > max) {
                const more = document.createElement('div');
                more.className = 'wiz-review-photo more';
                more.textContent = `+${sources.length - max}`;
                photoBox.appendChild(more);
            }
            if (sources.length === 0) {
                photoBox.innerHTML = '<span class="wiz-review-val muted">No photos selected</span>';
            }
        }
    }

    updateSummary();
    renderUploads();
    showStep(1);
})();
</script>
