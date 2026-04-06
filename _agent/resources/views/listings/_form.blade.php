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
@endphp

<style>
    /* Styling strictly minimal, clean, Apple-like */
    :root {
        --apple-bg: #fbfbfd;
        --apple-card: #ffffff;
        --apple-text: #1d1d1f;
        --apple-text-secondary: #86868b;
        --apple-border: rgba(0, 0, 0, 0.08);
        --apple-border-light: rgba(0, 0, 0, 0.04);
        --apple-accent: #f5f5f7;
        --apple-blue: #0066cc;
        --apple-blue-hover: #0077ed;
        --apple-danger: #ff3b30;
        --apple-danger-hover: #ff453a;
        --apple-radius: 20px;
        --apple-radius-sm: 12px;
        --apple-shadow: 0 4px 24px rgba(0,0,0,0.04);
        --apple-shadow-sm: 0 2px 12px rgba(0,0,0,0.03);
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    .lf-page{display:grid;gap:32px;max-width:1200px;margin:0 auto;padding-bottom:40px;color:var(--apple-text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
    .lf-hero,.lf-card{background:var(--apple-card);border:1px solid var(--apple-border);border-radius:var(--apple-radius);box-shadow:var(--apple-shadow);transition:var(--transition)}
    .lf-hero{padding:40px}
    .lf-hero-grid,.lf-grid{display:grid;gap:32px}
    .lf-hero-grid{grid-template-columns:minmax(0,1.2fr) minmax(300px,.8fr)}
    .lf-grid{grid-template-columns:minmax(0,1.15fr) minmax(360px,.85fr);align-items:start}
    .lf-main,.lf-side,.lf-fields{display:grid;gap:24px}
    .lf-card{padding:32px}
    .lf-kicker{font-size:13px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:var(--apple-blue);margin-bottom:12px}
    .lf-hero h3,.lf-card h4,.lf-side h5{color:var(--apple-text);letter-spacing:-0.02em;font-weight:700;margin:0}
    .lf-hero h3{font-size:clamp(28px,4vw,36px);line-height:1.15;margin-bottom:16px}
    .lf-card h4{font-size:24px;margin-bottom:8px}
    .lf-side h5{font-size:20px;margin-bottom:16px}
    .lf-hero p,.lf-head p,.lf-guide li,.lf-hint,.lf-note{font-size:15px;line-height:1.6;color:var(--apple-text-secondary)}
    .lf-guide{padding:24px;border:1px solid var(--apple-border);border-radius:var(--apple-radius-sm);background:var(--apple-accent)}
    .lf-guide strong{display:block;margin-bottom:12px;color:var(--apple-text);font-weight:600;font-size:16px}
    .lf-guide ul{padding-left:18px;display:grid;gap:10px}
    .lf-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:24px}
    .lf-pill{padding:8px 16px;border-radius:999px;background:var(--apple-accent);color:var(--apple-text);font-size:13px;font-weight:600;white-space:nowrap;border:1px solid rgba(0,0,0,0.04)}
    .lf-upload{display:grid;gap:16px;padding:32px;border:2px dashed var(--apple-border);border-radius:var(--apple-radius-sm);background:#fbfbfd;text-align:center;transition:var(--transition)}
    .lf-upload:hover{border-color:var(--apple-blue);background:#f0f8ff}
    .lf-upload strong{font-size:20px;color:var(--apple-text);font-weight:600;letter-spacing:-0.01em}
    .lf-source-tabs{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-top:24px}
    .lf-source-tab{position:relative;display:grid;gap:8px;padding:20px;border:1px solid var(--apple-border);border-radius:var(--apple-radius-sm);background:var(--apple-card);text-decoration:none;color:inherit;transition:var(--transition)}
    .lf-source-tab:hover{border-color:var(--apple-blue);box-shadow:var(--apple-shadow-sm);transform:translateY(-2px)}
    .lf-source-tab.is-active{border-color:var(--apple-blue);background:#f0f8ff;box-shadow:0 0 0 1px var(--apple-blue)}
    .lf-source-tab.is-disabled{opacity:0.5;background:#f5f5f7;pointer-events:none}
    .lf-source-tab.is-selectable{cursor:pointer}
    .lf-source-input{position:absolute;opacity:0;pointer-events:none}
    .lf-source-label{font-size:15px;font-weight:700;color:var(--apple-text);text-transform:uppercase;letter-spacing:0.04em}
    .lf-source-copy{font-size:14px;line-height:1.5;color:var(--apple-text-secondary)}
    .lf-source-status{font-size:13px;font-weight:600;color:var(--apple-blue);margin-top:8px}
    .lf-upload-actions{display:flex;gap:16px;flex-direction:column;align-items:center}
    .lf-file{position:relative;display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:10px 24px;border-radius:999px;background:var(--apple-blue);color:#fff;font-size:15px;font-weight:600;cursor:pointer;transition:var(--transition)}
    .lf-file:hover{background:var(--apple-blue-hover);transform:scale(1.02)}
    .lf-file input{position:absolute;opacity:0;pointer-events:none}
    .lf-gallery{display:grid;gap:20px;margin-top:32px;padding-top:32px;border-top:1px solid var(--apple-border)}
    .lf-gallery-head{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}
    .lf-gallery-title{font-size:15px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--apple-text)}
    .lf-media-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px}
    .lf-media{display:grid;gap:12px;padding:12px;border:1px solid var(--apple-border);border-radius:var(--apple-radius-sm);background:var(--apple-card);transition:var(--transition)}
    .lf-media:hover{box-shadow:var(--apple-shadow-sm);transform:translateY(-2px)}
    .lf-frame{position:relative;height:160px;border-radius:8px;overflow:hidden;background:#f5f5f7;border:1px solid rgba(0,0,0,0.04)}
    .lf-frame img{width:100%;height:100%;object-fit:cover;display:block}
    .lf-badges{position:absolute;top:10px;left:10px;display:flex;gap:8px;flex-wrap:wrap}
    .lf-badge{display:inline-flex;align-items:center;padding:6px 10px;border-radius:6px;background:rgba(255,255,255,0.9);color:var(--apple-text);font-size:11px;font-weight:700;letter-spacing:0.02em;box-shadow:0 2px 4px rgba(0,0,0,0.1);backdrop-filter:blur(4px)}
    .lf-badge.cover{background:var(--apple-text);color:#fff}
    .lf-name{font-size:14px;font-weight:600;color:var(--apple-text);line-height:1.4;word-break:break-word}
    .lf-sub{font-size:13px;color:var(--apple-text-secondary);word-break:break-word}
    .lf-remove{display:inline-flex;align-items:center;justify-content:center;min-height:36px;padding:8px 16px;border-radius:8px;border:none;background:var(--apple-accent);color:var(--apple-danger);font-size:14px;font-weight:600;cursor:pointer;transition:var(--transition)}
    .lf-remove:hover{background:#ffefef;color:var(--apple-danger-hover)}
    .lf-empty{padding:32px;border:1px dashed var(--apple-border);border-radius:var(--apple-radius-sm);background:#fbfbfd;color:var(--apple-text-secondary);font-size:15px;line-height:1.6;text-align:center;font-weight:500}
    .lf-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}
    .lf-full{grid-column:1/-1}
    .lf-check{display:flex;gap:12px;align-items:flex-start;padding:20px;border:1px solid var(--apple-border);border-radius:var(--apple-radius-sm);background:var(--apple-accent);cursor:pointer;transition:var(--transition)}
    .lf-check:hover{border-color:var(--apple-blue)}
    .lf-check input{width:20px;height:20px;accent-color:var(--apple-blue);cursor:pointer;margin-top:2px}
    .lf-check strong{display:block;font-size:15px;color:var(--apple-text);font-weight:600}
    .lf-check span{display:block;margin-top:4px;font-size:14px;color:var(--apple-text-secondary)}
    .lf-side{position:sticky;top:32px}
    .lf-summary{display:grid;gap:16px}
    .lf-row{display:flex;justify-content:space-between;gap:12px;padding-bottom:16px;border-bottom:1px solid var(--apple-border)}
    .lf-row:last-child{padding-bottom:0;border-bottom:0}
    .lf-row span{font-size:14px;font-weight:600;color:var(--apple-text-secondary)}
    .lf-row strong{font-size:15px;font-weight:600;color:var(--apple-text);text-align:right;word-break:break-word}
    .lf-note{padding:20px;border-radius:var(--apple-radius-sm);background:#f5f5f7;color:var(--apple-text-secondary);border:1px solid rgba(0,0,0,0.04);margin:24px 0}
    .lf-actions{display:flex;gap:12px;flex-wrap:wrap}
    
    .lf-actions .btn {
        flex: 1;
        padding: 14px 24px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 15px;
        text-align: center;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .lf-actions .btn-primary { background: var(--apple-blue); color: #ffffff; }
    .lf-actions .btn-primary:hover { background: var(--apple-blue-hover); transform: scale(1.02); }
    .lf-actions .btn-secondary { background: var(--apple-accent); color: var(--apple-text); }
    .lf-actions .btn-secondary:hover { background: rgba(0,0,0,0.05); }

    .form-label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--apple-text); }
    .form-input, .form-select, .form-textarea { width: 100%; border: 1px solid var(--apple-border); background: var(--apple-accent); border-radius: 12px; padding: 14px 16px; font-size: 15px; color: var(--apple-text); transition: var(--transition); font-family: inherit; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { background: #ffffff; border-color: var(--apple-blue); outline: none; box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.15); }
    .form-textarea { resize: vertical; min-height: 100px; }
    .form-hint { font-size: 13px; color: var(--apple-text-secondary); margin-top: 6px; }

    @media (max-width:1180px){.lf-hero-grid,.lf-grid{grid-template-columns:1fr}.lf-side{position:static}}
    @media (max-width:780px){
        .lf-card,.lf-hero{padding:24px}
        .lf-form-grid,.lf-media-grid{grid-template-columns:1fr}
        .lf-head{flex-direction:column}
        .lf-actions .btn {flex-basis: 100%;}
    }
</style>

<div class="lf-page">
    @if($createSourceTabs->isNotEmpty())
        <section class="lf-card">
            <div class="lf-head">
                <div>
                    <h4>Listing Source</h4>
                    <p>{{ $listing ? 'Choose where this listing should live after you save.' : 'Choose where this listing should be created before you upload photos.' }}</p>
                </div>
            </div>
            <div class="lf-source-tabs">
                @foreach($createSourceTabs as $sourceTab)
                    @php
                        $isActiveSource = $selectedFormSource === $sourceTab['key'];
                        $sourceTabClass = 'lf-source-tab' . ($isActiveSource ? ' is-active' : '') . (! $sourceTab['enabled'] ? ' is-disabled' : '') . ($listing ? ' is-selectable' : '');
                    @endphp
                    @if($listing)
                        <label class="{{ $sourceTabClass }}">
                            <input class="lf-source-input" type="radio" name="source" value="{{ $sourceTab['key'] }}" @checked($isActiveSource) @disabled(! $sourceTab['enabled'])>
                            <span class="lf-source-label">{{ $sourceTab['label'] }}</span>
                            <span class="lf-source-copy">{{ $sourceTab['description'] }}</span>
                            @if($originalFormSource === $sourceTab['key'])
                                <span class="lf-source-status">Current source</span>
                            @elseif($isActiveSource)
                                <span class="lf-source-status">Will move on save</span>
                            @endif
                        </label>
                    @elseif($sourceTab['enabled'])
                        <a href="{{ route('listings.create', ['source' => $sourceTab['key']]) }}" class="{{ $sourceTabClass }}">
                            <span class="lf-source-label">{{ $sourceTab['label'] }}</span>
                            <span class="lf-source-copy">{{ $sourceTab['description'] }}</span>
                        </a>
                    @else
                        <div class="{{ $sourceTabClass }}">
                            <span class="lf-source-label">{{ $sourceTab['label'] }}</span>
                            <span class="lf-source-copy">{{ $sourceTab['description'] }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    <section class="lf-hero">
        <div class="lf-hero-grid">
            <div>
                <div class="lf-kicker">{{ $listing ? 'Update Listing' : 'Create Listing' }}</div>
                <h3>{{ $listing ? 'Keep the gallery current and the listing polished.' : 'Launch a new listing with real uploaded images.' }}</h3>
                <p>
                    @if($listing && $selectedFormSource !== $originalFormSource)
                        Saving will move this listing from {{ strtoupper($originalFormSource) }} to {{ strtoupper($selectedFormSource) }} while keeping the same property ID and legacy image paths.
                    @elseif($listing)
                        Agents can keep the gallery current here without touching raw legacy metadata by hand.
                    @elseif($selectedFormSource === 'icp')
                        ICP uploads follow the mobile-app flow: photos are saved into legacy <code>Database/Images/&lt;propertyId&gt;/001.jpg</code> paths before the listing row is written.
                    @elseif($selectedFormSource === 'condo')
                        Condo uploads are written into WordPress properties inside <code>wp_condo</code> so Estatik, Rank Math, and future condo subdomains can read the same listing.
                    @else
                        IPP uploads now keep the same legacy <code>Database/Images/&lt;propertyId&gt;/001.jpg</code> image path format used by the existing listing databases.
                    @endif
                </p>
            </div>
            <div class="lf-guide">
                <strong>{{ $listing ? 'How photo updates work' : 'How this save flow works' }}</strong>
                <ul>
                    <li>Keep any saved image you still want on the listing.</li>
                    <li>Remove any outdated photo, then add replacements below.</li>
                    <li>The first kept or uploaded image becomes the cover photo after save.</li>
                    @if($listing)
                        <li>Changing the source here moves the listing when you save.</li>
                    @endif
                </ul>
            </div>
        </div>
    </section>

    <div class="lf-grid">
        <div class="lf-main">
            @if($listing)
                <input type="hidden" name="original_source" value="{{ $originalFormSource }}">
                <input type="hidden" name="return_source" value="{{ $formReturnSource }}">
            @else
                <input type="hidden" name="source" value="{{ $selectedFormSource }}">
            @endif

            <section class="lf-card">
                <div class="lf-head">
                    <div>
                        <h4>Media Gallery</h4>
                        <p>Existing photos stay visible until you remove them. New uploads preview instantly before you save.</p>
                    </div>
                    <div class="lf-pill"><span data-gallery-total>{{ count($existingPhotos) }}</span> saved</div>
                </div>

                <div class="lf-upload">
                    <strong>Add property images</strong>
                    <div class="lf-upload-actions">
                        <label class="lf-file">
                            Choose Images
                            <input id="listing-new-images" type="file" name="new_images[]" accept="image/*" multiple @disabled(! $selectedSourceEnabled)>
                        </label>
                        <span class="lf-hint">JPG, JPEG, PNG, WEBP, GIF, and BMP up to 10MB each.</span>
                    </div>
                    <div class="lf-hint">
                        @if($selectedFormSource === 'icp')
                            New ICP uploads are saved with sequential legacy filenames like <code>001.jpg</code>, <code>002.jpg</code>, and the DB keeps the matching <code>Database/Images</code> paths.
                        @elseif($selectedFormSource === 'condo')
                            Condo uploads are copied into WordPress media-style uploads and linked back to the property post for the condo site.
                        @else
                            New uploads are stored with legacy <code>Database/Images</code> paths so they match the existing IPP/ICP databases.
                        @endif
                    </div>
                </div>

                <div class="lf-gallery">
                    <div class="lf-gallery-head">
                        <div class="lf-gallery-title">Current Gallery</div>
                        <div class="lf-hint">Remove any photo you no longer want on the live listing.</div>
                    </div>
                    <div class="lf-media-grid" id="existing-photo-grid">
                        @forelse($existingPhotos as $photoPath)
                            @php
                                $photoUrl = \App\Support\SharedAssetUrl::listing($listingUsername, $listingPropertyId, $photoPath)
                                    ?? \App\Support\SharedAssetUrl::storage($photoPath);
                            @endphp
                            <article class="lf-media" data-existing-photo>
                                <div class="lf-frame">
                                    <div class="lf-badges">
                                        <span class="lf-badge">Saved</span>
                                        @if($loop->first)
                                            <span class="lf-badge cover">Cover</span>
                                        @endif
                                    </div>
                                    @if($photoUrl)
                                        <img src="{{ $photoUrl }}" alt="Saved photo {{ $loop->iteration }}" loading="lazy">
                                    @endif
                                </div>
                                <div>
                                    <div class="lf-name">{{ basename($photoPath) ?: 'Saved image' }}</div>
                                    <div class="lf-sub">{{ $photoPath }}</div>
                                </div>
                                <input type="hidden" name="existing_photos[]" value="{{ $photoPath }}">
                                <button type="button" class="lf-remove" data-remove-existing>Remove photo</button>
                            </article>
                        @empty
                            <div class="lf-empty" id="existing-photo-empty">No saved photos yet. Add images above and they will appear in the gallery after save.</div>
                        @endforelse
                    </div>
                </div>

                <div class="lf-gallery">
                    <div class="lf-gallery-head">
                        <div class="lf-gallery-title">New Uploads</div>
                        <div class="lf-hint">Previews are local until the listing is saved.</div>
                    </div>
                    <div class="lf-empty" id="new-photo-empty">No new files selected yet. Choose one or more images to preview them here.</div>
                    <div class="lf-media-grid" id="new-photo-grid"></div>
                </div>
            </section>

            <section class="lf-card">
                <div class="lf-head">
                    <div>
                        <h4>Core Details</h4>
                        <p>These fields power the listing card, filters, and the main detail view.</p>
                    </div>
                </div>
                <div class="lf-fields">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="propertyname">Property Name</label>
                        <input class="form-input" id="propertyname" name="propertyname" type="text" value="{{ old('propertyname', $form['propertyname']) }}" maxlength="100" required>
                    </div>
                    <div class="lf-form-grid">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="listingtype">Listing Type</label>
                            <select class="form-select" id="listingtype" name="listingtype" required>
                                @foreach($listingTypes as $listingType)
                                    <option value="{{ $listingType }}" @selected(old('listingtype', $form['listingtype']) === $listingType)>{{ $listingType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="propertytype">Property Type</label>
                            <select class="form-select" id="propertytype" name="propertytype" required>
                                @foreach($propertyTypes as $propertyType)
                                    <option value="{{ $propertyType }}" @selected(old('propertytype', $form['propertytype']) === $propertyType)>{{ $propertyType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="price">Price</label>
                            <input class="form-input" id="price" name="price" type="text" value="{{ old('price', $form['price']) }}" inputmode="decimal" required>
                            <div class="form-hint">Enter the amount only. The CMS normalizes the number when saving.</div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="state">State</label>
                            <select class="form-select" id="state" name="state" required>
                                <option value="">Select state</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->state }}" @selected(old('state', $form['state']) === $state->state)>{{ $state->state }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="area">Area</label>
                            <input class="form-input" id="area" name="area" type="text" value="{{ old('area', $form['area']) }}" maxlength="100" required>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="keywords">Keywords</label>
                            <input class="form-input" id="keywords" name="keywords" type="text" value="{{ old('keywords', $form['keywords']) }}" maxlength="500">
                            <div class="form-hint">Leave this blank to let the system generate keywords automatically.</div>
                        </div>
                    </div>
                    <label class="lf-check">
                        <input type="checkbox" name="cobroke" value="1" @checked((int) old('cobroke', $form['cobroke']) === 1)>
                        <span>
                            <strong>Enable co-broke</strong>
                            <span>Use this if the listing should be marked as co-broke in the shared database.</span>
                        </span>
                    </label>
                </div>
            </section>

            <section class="lf-card">
                <div class="lf-head">
                    <div>
                        <h4>Description</h4>
                        <p>Public-facing content shown on the listing details page.</p>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="description">Listing Description</label>
                    <textarea class="form-textarea" id="description" name="description" rows="8">{{ old('description', $form['description']) }}</textarea>
                </div>
            </section>

            @foreach($generalFieldGroups as $sectionKey => $fields)
                <section class="lf-card">
                    <div class="lf-head">
                        <div>
                            <h4>{{ $generalSectionTitles[$sectionKey] ?? \Illuminate\Support\Str::headline($sectionKey) }}</h4>
                            <p>Structured data saved back into the legacy listing metadata.</p>
                        </div>
                    </div>
                    <div class="lf-form-grid">
                        @foreach($fields as $field)
                            <div class="form-group {{ ($field['type'] ?? 'text') === 'textarea' ? 'lf-full' : '' }}" style="margin-bottom:0;">
                                <label class="form-label" for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                                @if(($field['type'] ?? 'text') === 'textarea')
                                    <textarea
                                        class="form-textarea"
                                        id="{{ $field['name'] }}"
                                        name="{{ $field['name'] }}"
                                        rows="{{ $field['rows'] ?? 4 }}"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                    >{{ old($field['name'], $form[$field['name']] ?? '') }}</textarea>
                                @else
                                    <input
                                        class="form-input"
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
                </section>
            @endforeach
        </div>

        <aside class="lf-side">
            <section class="lf-card">
                <div class="lf-kicker">Save Flow</div>
                <h5>{{ $listing ? 'Update this listing cleanly.' : 'Create the listing once the gallery looks right.' }}</h5>
                <div class="lf-summary">
                    <div class="lf-row">
                        <span>Saved Photos</span>
                        <strong data-sidebar-saved-count>{{ count($existingPhotos) }}</strong>
                    </div>
                    <div class="lf-row">
                        <span>New Uploads</span>
                        <strong data-sidebar-new-count>0</strong>
                    </div>
                    <div class="lf-row">
                        <span>Cover Photo</span>
                        <strong data-sidebar-cover>{{ count($existingPhotos) > 0 ? 'First saved photo' : 'First new upload' }}</strong>
                    </div>
                    @if($listing)
                        <div class="lf-row">
                            <span>Property ID</span>
                            <strong>{{ $listing->propertyid }}</strong>
                        </div>
                    @endif
                </div>
                <div class="lf-note">Images are stored automatically and their paths are saved for you. Removing a saved local image here removes it from the listing after save.</div>
                <div class="lf-actions">
                    <button type="submit" class="btn btn-primary" @disabled(! $selectedSourceEnabled)>{{ ! $selectedSourceEnabled ? 'Source Not Ready Yet' : $submitLabel }}</button>
                    <a href="{{ $listing ? route('listings.show', array_filter(['id' => $listing->id, 'source' => $originalFormSource === 'ipp' ? null : $originalFormSource, 'return_source' => $formReturnSource], static fn ($value) => $value !== null)) : route('listings.index', ['source' => $selectedFormSource]) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </section>
        </aside>
    </div>
</div>

<script>
    (function () {
        var input = document.getElementById('listing-new-images');
        var existingGrid = document.getElementById('existing-photo-grid');
        var newGrid = document.getElementById('new-photo-grid');
        var newEmpty = document.getElementById('new-photo-empty');
        var total = document.querySelector('[data-gallery-total]');
        var savedCount = document.querySelector('[data-sidebar-saved-count]');
        var newCount = document.querySelector('[data-sidebar-new-count]');
        var cover = document.querySelector('[data-sidebar-cover]');

        if (!input || !existingGrid || !newGrid || !newEmpty) {
            return;
        }

        var transfer = new DataTransfer();
        var previewUrls = [];

        function fileSize(bytes) {
            return bytes < 1048576 ? Math.max(1, Math.round(bytes / 1024)) + ' KB' : (bytes / 1048576).toFixed(1) + ' MB';
        }

        function revokeUrls() {
            previewUrls.forEach(function (url) { URL.revokeObjectURL(url); });
            previewUrls = [];
        }

        function ensureExistingEmptyState(count) {
            var empty = document.getElementById('existing-photo-empty');

            if (count === 0 && !empty) {
                empty = document.createElement('div');
                empty.id = 'existing-photo-empty';
                empty.className = 'lf-empty';
                empty.textContent = 'No saved photos yet. Add images above and they will appear in the gallery after save.';
                existingGrid.appendChild(empty);
            }

            if (count > 0 && empty) {
                empty.remove();
            }
        }

        function updateSummary() {
            var existingCards = existingGrid.querySelectorAll('[data-existing-photo]');
            var existingCount = existingCards.length;
            var uploadCount = transfer.files.length;

            ensureExistingEmptyState(existingCount);

            existingCards.forEach(function (card, index) {
                var oldCover = card.querySelector('.lf-badge.cover');
                if (oldCover) {
                    oldCover.remove();
                }

                if (index === 0) {
                    var badges = card.querySelector('.lf-badges');
                    var coverBadge = document.createElement('span');
                    coverBadge.className = 'lf-badge cover';
                    coverBadge.textContent = 'Cover';
                    badges.appendChild(coverBadge);
                }
            });

            if (total) {
                total.textContent = existingCount;
            }
            if (savedCount) {
                savedCount.textContent = existingCount;
            }
            if (newCount) {
                newCount.textContent = uploadCount;
            }
            if (cover) {
                cover.textContent = existingCount > 0 ? 'First saved photo' : (uploadCount > 0 ? 'First new upload' : 'No photo selected');
            }
        }

        function renderUploads() {
            revokeUrls();
            newGrid.innerHTML = '';

            Array.from(transfer.files).forEach(function (file, index) {
                var url = URL.createObjectURL(file);
                previewUrls.push(url);

                var card = document.createElement('article');
                card.className = 'lf-media';

                var frame = document.createElement('div');
                frame.className = 'lf-frame';

                var badges = document.createElement('div');
                badges.className = 'lf-badges';

                var newBadge = document.createElement('span');
                newBadge.className = 'lf-badge';
                newBadge.textContent = 'New';
                badges.appendChild(newBadge);

                if (index === 0 && existingGrid.querySelectorAll('[data-existing-photo]').length === 0) {
                    var coverBadge = document.createElement('span');
                    coverBadge.className = 'lf-badge cover';
                    coverBadge.textContent = 'Cover';
                    badges.appendChild(coverBadge);
                }

                var image = document.createElement('img');
                image.src = url;
                image.alt = file.name;

                var meta = document.createElement('div');
                var name = document.createElement('div');
                var sub = document.createElement('div');
                name.className = 'lf-name';
                sub.className = 'lf-sub';
                name.textContent = file.name;
                sub.textContent = fileSize(file.size);
                meta.appendChild(name);
                meta.appendChild(sub);

                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'lf-remove';
                remove.textContent = 'Remove upload';
                remove.addEventListener('click', function () {
                    var next = new DataTransfer();
                    Array.from(transfer.files).forEach(function (candidate, candidateIndex) {
                        if (candidateIndex !== index) {
                            next.items.add(candidate);
                        }
                    });
                    transfer = next;
                    input.files = transfer.files;
                    renderUploads();
                    updateSummary();
                });

                frame.appendChild(badges);
                frame.appendChild(image);
                card.appendChild(frame);
                card.appendChild(meta);
                card.appendChild(remove);
                newGrid.appendChild(card);
            });

            newEmpty.style.display = transfer.files.length === 0 ? 'block' : 'none';
        }

        input.addEventListener('change', function () {
            Array.from(input.files).forEach(function (file) {
                var duplicate = Array.from(transfer.files).some(function (existingFile) {
                    return existingFile.name === file.name
                        && existingFile.size === file.size
                        && existingFile.lastModified === file.lastModified;
                });

                if (!duplicate) {
                    transfer.items.add(file);
                }
            });

            input.files = transfer.files;
            renderUploads();
            updateSummary();
        });

        existingGrid.querySelectorAll('[data-remove-existing]').forEach(function (button) {
            button.addEventListener('click', function () {
                var card = button.closest('[data-existing-photo]');
                if (card) {
                    card.remove();
                    updateSummary();
                    renderUploads();
                }
            });
        });

        updateSummary();
        renderUploads();
    })();
</script>
