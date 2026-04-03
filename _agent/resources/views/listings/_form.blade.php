@php
    $existingPhotos = \App\Support\ListingEditor::normalizePhotoPaths(old('existing_photos', $form['existing_photos'] ?? []));
    $listingUsername = $listing?->getRawOriginal('username') ?? $listing?->username;
    $listingPropertyId = $listing?->getRawOriginal('propertyid') ?? $listing?->propertyid;
@endphp

<style>
    .lf-page{display:grid;gap:24px}
    .lf-hero,.lf-card{background:var(--card-bg);border:1px solid var(--border-light);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);transition:box-shadow 0.3s ease}
    .lf-hero{padding:32px;background:var(--card-bg)}
    .lf-hero:hover,.lf-card:hover{box-shadow:var(--shadow-md)}
    .lf-hero-grid,.lf-grid{display:grid;gap:24px}
    .lf-hero-grid{grid-template-columns:minmax(0,1.2fr) minmax(280px,.8fr)}
    .lf-grid{grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);align-items:start}
    .lf-main,.lf-side,.lf-fields{display:grid;gap:24px}
    .lf-card{padding:24px}
    .lf-kicker{font-size:12px;font-weight:600;letter-spacing:0.04em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:12px}
    .lf-hero h3,.lf-card h4,.lf-side h5{color:var(--text);letter-spacing:-0.021em;font-weight:600}
    .lf-hero h3{font-size:clamp(26px,3vw,34px);line-height:1.1;margin-bottom:12px}
    .lf-hero p,.lf-head p,.lf-guide li,.lf-hint,.lf-note{font-size:14px;line-height:1.5;color:var(--text-secondary)}
    .lf-guide{padding:20px;border:1px solid var(--border-light);border-radius:var(--radius-sm);background:var(--accent-light)}
    .lf-guide strong{display:block;margin-bottom:12px;color:var(--text);font-weight:600;font-size:15px}
    .lf-guide ul{padding-left:18px;display:grid;gap:8px}
    .lf-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start}
    .lf-head h4{font-size:22px;line-height:1.1}
    .lf-pill{padding:6px 12px;border-radius:999px;background:var(--accent-light);color:var(--text);font-size:12px;font-weight:600;letter-spacing:0;white-space:nowrap;border:1px solid var(--border-light)}
    .lf-upload{display:grid;gap:14px;padding:24px;border:1px dashed var(--border);border-radius:var(--radius-md);background:var(--accent-light)}
    .lf-upload strong{font-size:18px;color:var(--text);font-weight:600;letter-spacing:-0.01em}
    .lf-upload-actions{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
    .lf-file{position:relative;display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:8px 16px;border-radius:999px;background:var(--accent);color:#fff;font-size:14px;font-weight:500;cursor:pointer;transition:all 0.2s ease}
    .lf-file:hover{background:var(--accent-hover)}
    .lf-file input{position:absolute;opacity:0;pointer-events:none}
    .lf-gallery{display:grid;gap:16px}
    .lf-gallery-head{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}
    .lf-gallery-title{font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.02em;color:var(--text)}
    .lf-media-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px}
    .lf-media{display:grid;gap:12px;padding:12px;border:1px solid var(--border-light);border-radius:var(--radius-md);background:var(--card-bg)}
    .lf-frame{position:relative;height:140px;border-radius:var(--radius-sm);overflow:hidden;background:var(--accent-light);border:1px solid var(--border-light)}
    .lf-frame img{width:100%;height:100%;object-fit:cover;display:block}
    .lf-badges{position:absolute;top:10px;left:10px;display:flex;gap:8px;flex-wrap:wrap}
    .lf-badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:6px;background:rgba(255,255,255,0.9);color:var(--text);font-size:11px;font-weight:600;letter-spacing:0;box-shadow:0 1px 2px rgba(0,0,0,0.1)}
    .lf-badge.cover{background:var(--text);color:#fff}
    .lf-name{font-size:13px;font-weight:500;color:var(--text);line-height:1.4;word-break:break-word}
    .lf-sub{font-size:12px;color:var(--text-secondary);word-break:break-word}
    .lf-remove{display:inline-flex;align-items:center;justify-content:center;min-height:36px;padding:6px 12px;border-radius:8px;border:1px solid var(--border-light);background:var(--card-bg);color:var(--danger);font-size:13px;font-weight:500;cursor:pointer;transition:all 0.2s}
    .lf-remove:hover{background:#fff5f5;border-color:#ffb3b0}
    .lf-empty{padding:24px;border:1px dashed var(--border);border-radius:var(--radius-sm);background:var(--accent-light);color:var(--text-secondary);font-size:14px;line-height:1.5;text-align:center}
    .lf-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .lf-full{grid-column:1/-1}
    .lf-check{display:flex;gap:12px;align-items:flex-start;padding:16px;border:1px solid var(--border-light);border-radius:var(--radius-sm);background:var(--accent-light);cursor:pointer}
    .lf-check input{width:18px;height:18px;accent-color:var(--text);cursor:pointer;margin-top:2px}
    .lf-check strong{display:block;font-size:14px;color:var(--text);font-weight:500}
    .lf-check span{display:block;margin-top:4px;font-size:13px;color:var(--text-secondary)}
    .lf-side{position:sticky;top:88px}
    .lf-side h5{font-size:20px;line-height:1.1}
    .lf-summary{display:grid;gap:12px}
    .lf-row{display:flex;justify-content:space-between;gap:12px;padding-bottom:12px;border-bottom:1px solid var(--border-light)}
    .lf-row:last-child{padding-bottom:0;border-bottom:0}
    .lf-row span{font-size:13px;font-weight:500;color:var(--text-secondary)}
    .lf-row strong{font-size:14px;font-weight:600;color:var(--text);text-align:right;word-break:break-word}
    .lf-note{padding:16px;border-radius:var(--radius-sm);background:var(--accent-light);color:var(--text-secondary);border:1px solid var(--border-light)}
    .lf-actions{display:flex;gap:12px;flex-wrap:wrap}
    @media (max-width:1180px){.lf-hero-grid,.lf-grid{grid-template-columns:1fr}.lf-side{position:static}}
    @media (max-width:780px){.lf-card,.lf-hero{padding:20px;border-radius:var(--radius-sm)}.lf-form-grid,.lf-media-grid{grid-template-columns:1fr}.lf-head{flex-direction:column}}
</style>

<div class="lf-page">
    <section class="lf-hero">
        <div class="lf-hero-grid">
            <div>
                <div class="lf-kicker">{{ $listing ? 'Update Listing' : 'Create Listing' }}</div>
                <h3>{{ $listing ? 'Keep the gallery current and the listing polished.' : 'Launch a new listing with real uploaded images.' }}</h3>
                <p>Agents can now upload actual listing photos here. The CMS saves the image paths automatically, so there is no need to paste manual URLs or legacy server paths anymore.</p>
            </div>
            <div class="lf-guide">
                <strong>How photo updates work</strong>
                <ul>
                    <li>Keep any saved image you still want on the listing.</li>
                    <li>Remove any outdated photo, then add replacements below.</li>
                    <li>The first kept or uploaded image becomes the cover photo after save.</li>
                </ul>
            </div>
        </div>
    </section>

    <div class="lf-grid">
        <div class="lf-main">
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
                            <input id="listing-new-images" type="file" name="new_images[]" accept="image/*" multiple>
                        </label>
                        <span class="lf-hint">JPG, PNG, WEBP, GIF, BMP, and SVG up to 10MB each.</span>
                    </div>
                    <div class="lf-hint">This replaces the old raw image-path entry flow.</div>
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
                    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
                    <a href="{{ $listing ? route('listings.show', $listing->id) : route('listings.index') }}" class="btn btn-secondary">Cancel</a>
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
