@extends('layouts.app')
@section('title', 'Edit SEO')
@section('page-title', 'Edit Rank Math SEO')
@section('topbar-actions')
    <a href="{{ route('listings.show', ['id' => $listing->id, 'source' => 'condo', 'return_source' => 'condo']) }}" class="btn btn-secondary btn-sm">View Listing</a>
    <a href="{{ route('seo.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<style>
    .seo-edit-shell{display:grid;gap:24px}
    .seo-edit-hero,.seo-edit-card{background:var(--card-bg);border:1px solid var(--border-light);border-radius:var(--radius-md);box-shadow:var(--shadow-sm)}
    .seo-edit-hero{padding:28px}
    .seo-edit-grid{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(320px,.9fr);gap:24px;align-items:start}
    .seo-edit-main,.seo-edit-side{display:grid;gap:24px}
    .seo-edit-card{padding:24px}
    .seo-kicker{font-size:12px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px}
    .seo-edit-hero h3{font-size:clamp(28px,3vw,38px);line-height:1.08;letter-spacing:-.03em;margin-bottom:12px}
    .seo-edit-hero p,.seo-help,.seo-preview-desc,.seo-preview-url{color:var(--text-secondary);line-height:1.6}
    .seo-property{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:flex-start}
    .seo-property-title{font-size:24px;line-height:1.15;letter-spacing:-.025em;margin-bottom:6px}
    .seo-property-meta{font-size:13px;color:var(--text-secondary)}
    .seo-pill{display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;background:var(--accent-light);font-size:12px;font-weight:600;border:1px solid var(--border-light)}
    .seo-preview{padding:20px;border-radius:var(--radius-sm);border:1px solid var(--border-light);background:linear-gradient(180deg,#fff 0%,#fafafc 100%)}
    .seo-preview-title{font-size:22px;line-height:1.25;color:#1a0dab;margin-bottom:4px}
    .seo-preview-url{font-size:13px;color:#188038;margin-bottom:6px;word-break:break-all}
    .seo-preview-desc{font-size:14px;color:#4d5156}
    .seo-section-title{font-size:18px;line-height:1.15;letter-spacing:-.02em;margin-bottom:14px}
    .seo-field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .seo-full{grid-column:1/-1}
    .seo-help{font-size:13px}
    .seo-robots{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px}
    .seo-check{display:flex;gap:12px;align-items:flex-start;padding:14px;border:1px solid var(--border-light);border-radius:var(--radius-sm);background:var(--accent-light)}
    .seo-check input{margin-top:2px;accent-color:var(--text)}
    .seo-check strong{display:block;font-size:14px;color:var(--text);margin-bottom:4px}
    .seo-check span{font-size:13px;color:var(--text-secondary);line-height:1.5}
    .seo-side-card{padding:20px;border-radius:var(--radius-sm);border:1px solid var(--border-light);background:var(--accent-light)}
    .seo-side-card h4{font-size:16px;line-height:1.15;margin-bottom:12px}
    .seo-side-list{display:grid;gap:10px}
    .seo-side-row{display:flex;justify-content:space-between;gap:12px;font-size:13px}
    .seo-side-row span{color:var(--text-secondary)}
    .seo-side-row strong{color:var(--text);text-align:right}
    @media (max-width:980px){.seo-edit-grid,.seo-field-grid{grid-template-columns:1fr}}
</style>

<div class="seo-edit-shell">
    <section class="seo-edit-hero">
        <div class="seo-kicker">Rank Math Metadata</div>
        <div class="seo-property">
            <div>
                <div class="seo-property-title">{{ $listing->propertyname }}</div>
                <div class="seo-property-meta">{{ $listing->formatted_price }} · {{ $listing->area }}{{ $listing->area && $listing->state ? ', ' : '' }}{{ $listing->state }}</div>
            </div>
            <span class="seo-pill">Condo Listing</span>
        </div>
        <p style="margin-top:14px;">These fields write directly to the condo WordPress postmeta used by Rank Math. Search, social previews, and sitemap behavior stay aligned with the live condo listing.</p>
    </section>

    <form method="POST" action="{{ route('seo.update', $listing->id) }}">
        @csrf
        @method('PUT')

        <div class="seo-edit-grid">
            <div class="seo-edit-main">
                <section class="seo-edit-card">
                    <div class="seo-section-title">Search Result</div>
                    <div class="seo-preview">
                        <div class="seo-preview-title" data-seo-preview-title>{{ old('meta_title', $seo['meta_title']) !== '' ? old('meta_title', $seo['meta_title']) : $listing->propertyname }}</div>
                        <div class="seo-preview-url" data-seo-preview-url>{{ old('canonical_url', $seo['canonical_url']) }}</div>
                        <div class="seo-preview-desc" data-seo-preview-desc>{{ old('meta_description', $seo['meta_description']) !== '' ? old('meta_description', $seo['meta_description']) : 'Add a clear summary for search results.' }}</div>
                    </div>

                    <div class="seo-field-grid" style="margin-top:18px;">
                        <div class="form-group seo-full" style="margin-bottom:0;">
                            <label class="form-label" for="meta_title">SEO Title</label>
                            <input id="meta_title" name="meta_title" type="text" class="form-input" maxlength="120" value="{{ old('meta_title', $seo['meta_title']) }}">
                            <div class="form-hint">If left blank, the listing title stays as the fallback.</div>
                        </div>
                        <div class="form-group seo-full" style="margin-bottom:0;">
                            <label class="form-label" for="meta_description">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" rows="4" class="form-textarea" maxlength="320">{{ old('meta_description', $seo['meta_description']) }}</textarea>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="focus_keyword">Focus Keyword</label>
                            <input id="focus_keyword" name="focus_keyword" type="text" class="form-input" maxlength="255" value="{{ old('focus_keyword', $seo['focus_keyword']) }}">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="canonical_url">Canonical URL</label>
                            <input id="canonical_url" name="canonical_url" type="url" class="form-input" maxlength="255" value="{{ old('canonical_url', $seo['canonical_url']) }}">
                        </div>
                    </div>
                </section>

                <section class="seo-edit-card">
                    <div class="seo-section-title">Open Graph</div>
                    <div class="seo-field-grid">
                        <div class="form-group seo-full" style="margin-bottom:0;">
                            <label class="form-label" for="og_title">Facebook Title</label>
                            <input id="og_title" name="og_title" type="text" class="form-input" maxlength="120" value="{{ old('og_title', $seo['og_title']) }}">
                        </div>
                        <div class="form-group seo-full" style="margin-bottom:0;">
                            <label class="form-label" for="og_description">Facebook Description</label>
                            <textarea id="og_description" name="og_description" rows="4" class="form-textarea" maxlength="320">{{ old('og_description', $seo['og_description']) }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="seo-edit-card">
                    <div class="seo-section-title">Twitter Preview</div>
                    <div class="seo-field-grid">
                        <div class="form-group seo-full" style="margin-bottom:0;">
                            <label class="form-label" for="twitter_title">Twitter Title</label>
                            <input id="twitter_title" name="twitter_title" type="text" class="form-input" maxlength="120" value="{{ old('twitter_title', $seo['twitter_title']) }}">
                        </div>
                        <div class="form-group seo-full" style="margin-bottom:0;">
                            <label class="form-label" for="twitter_description">Twitter Description</label>
                            <textarea id="twitter_description" name="twitter_description" rows="4" class="form-textarea" maxlength="320">{{ old('twitter_description', $seo['twitter_description']) }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="seo-edit-card">
                    <div class="seo-section-title">Robots Directives</div>
                    <div class="seo-help" style="margin-bottom:16px;">Leave everything unchecked to keep the default `index, follow` behavior.</div>
                    <div class="seo-robots">
                        @foreach([
                            'noindex' => 'Prevent search engines from indexing this listing.',
                            'nofollow' => 'Avoid passing link equity from this page.',
                            'noarchive' => 'Hide cached copies in search engines.',
                            'nosnippet' => 'Suppress rich snippets and preview text.',
                            'noimageindex' => 'Prevent images from being indexed.',
                        ] as $value => $description)
                            <label class="seo-check">
                                <input type="checkbox" name="robots[]" value="{{ $value }}" @checked(in_array($value, old('robots', $seo['robots']), true))>
                                <span>
                                    <strong>{{ strtoupper($value) }}</strong>
                                    <span>{{ $description }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>
            </div>

            <aside class="seo-edit-side">
                <section class="seo-edit-card">
                    <div class="seo-section-title">Publishing Notes</div>
                    <div class="seo-side-card">
                        <h4>What syncs automatically</h4>
                        <div class="seo-side-list">
                            <div class="seo-side-row"><span>WordPress post</span><strong>`properties`</strong></div>
                            <div class="seo-side-row"><span>SEO plugin</span><strong>Rank Math</strong></div>
                            <div class="seo-side-row"><span>Listing source</span><strong>Condo</strong></div>
                        </div>
                    </div>
                    <div class="seo-side-card" style="margin-top:16px;">
                        <h4>Current fallback data</h4>
                        <div class="seo-side-list">
                            <div class="seo-side-row"><span>Default title</span><strong>{{ $listing->propertyname }}</strong></div>
                            <div class="seo-side-row"><span>Default keyword</span><strong>{{ $listing->keywords ?: 'Generated from listing details' }}</strong></div>
                            <div class="seo-side-row"><span>Default canonical</span><strong>{{ $seo['canonical_url'] }}</strong></div>
                        </div>
                    </div>
                    <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary">Save SEO</button>
                        <a href="{{ route('social.create', ['listing' => $listing->id]) }}" class="btn btn-secondary">Schedule Social Post</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        var title = document.getElementById('meta_title');
        var description = document.getElementById('meta_description');
        var canonical = document.getElementById('canonical_url');
        var titlePreview = document.querySelector('[data-seo-preview-title]');
        var descriptionPreview = document.querySelector('[data-seo-preview-desc]');
        var urlPreview = document.querySelector('[data-seo-preview-url]');

        function syncPreview() {
            if (titlePreview) {
                titlePreview.textContent = title.value.trim() || @json($listing->propertyname);
            }

            if (descriptionPreview) {
                descriptionPreview.textContent = description.value.trim() || 'Add a clear summary for search results.';
            }

            if (urlPreview) {
                urlPreview.textContent = canonical.value.trim() || @json($seo['canonical_url']);
            }
        }

        [title, description, canonical].forEach(function (field) {
            if (!field) return;
            field.addEventListener('input', syncPreview);
        });
    }());
</script>
@endsection
