@extends('layouts.app')
@section('title', 'SEO')
@section('page-title', 'Rank Math SEO')
@section('topbar-actions')
    <a href="{{ route('listings.index', ['source' => 'condo']) }}" class="btn btn-secondary btn-sm">Condo Listings</a>
@endsection

@section('content')
<style>
    .seo-shell{display:grid;gap:24px}
    .seo-hero,.seo-card{background:var(--card-bg);border:1px solid var(--border-light);border-radius:var(--radius-md);box-shadow:var(--shadow-sm)}
    .seo-hero{padding:28px}
    .seo-card{padding:24px}
    .seo-hero-grid{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(280px,.8fr);gap:24px;align-items:start}
    .seo-kicker{font-size:12px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px}
    .seo-hero h3{font-size:clamp(28px,3vw,38px);line-height:1.08;letter-spacing:-.03em;margin-bottom:12px}
    .seo-hero p,.seo-note,.seo-meta{color:var(--text-secondary);line-height:1.6}
    .seo-note{padding:18px;border-radius:var(--radius-sm);background:var(--accent-light);border:1px solid var(--border-light);font-size:14px}
    .seo-search{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:12px;align-items:center}
    .seo-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:18px}
    .seo-listing{display:grid;gap:18px;padding:22px;border:1px solid var(--border-light);border-radius:var(--radius-md);background:linear-gradient(180deg,#fff 0%,#fafafc 100%)}
    .seo-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start}
    .seo-title{font-size:20px;line-height:1.15;letter-spacing:-.02em;margin-bottom:6px}
    .seo-location{font-size:13px;color:var(--text-secondary)}
    .seo-pill{display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;background:var(--accent-light);font-size:12px;font-weight:600;border:1px solid var(--border-light)}
    .seo-preview{padding:18px;border-radius:var(--radius-sm);border:1px solid var(--border-light);background:#fff}
    .seo-preview-title{font-size:20px;line-height:1.3;color:#1a0dab;margin-bottom:4px}
    .seo-preview-url{font-size:13px;color:#188038;margin-bottom:6px;word-break:break-all}
    .seo-preview-desc{font-size:14px;line-height:1.5;color:#4d5156}
    .seo-meta-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .seo-meta-card{padding:14px;border-radius:var(--radius-sm);background:var(--accent-light);border:1px solid var(--border-light)}
    .seo-meta-label{font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:600;color:var(--text-secondary);margin-bottom:8px}
    .seo-meta-value{font-size:14px;color:var(--text);line-height:1.5;word-break:break-word}
    .seo-actions{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}
    .seo-foot{font-size:13px;color:var(--text-secondary)}
    @media (max-width:920px){.seo-hero-grid,.seo-search,.seo-meta-grid{grid-template-columns:1fr}.seo-search .btn{width:100%;justify-content:center}}
</style>

<div class="seo-shell">
    <section class="seo-hero">
        <div class="seo-hero-grid">
            <div>
                <div class="seo-kicker">WordPress Sync</div>
                <h3>Rank Math metadata now follows the condo listings stored in WordPress.</h3>
                <p>The SEO screen is no longer saving into a separate Laravel table. Every edit here writes straight into the same `wp_condo` postmeta that Rank Math reads on the condo site.</p>
            </div>
            <div class="seo-note">
                Listings still auto-fill sensible SEO defaults from the property title, description, and keywords. Manual edits here stay in place unless you intentionally change them again.
            </div>
        </div>
    </section>

    <section class="seo-card">
        <form method="GET" class="seo-search">
            <input type="text" name="search" class="form-input" value="{{ $search }}" placeholder="Search condo listings by title, area, state, or keyword">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ route('seo.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </section>

    @if($settings->count())
        <section class="seo-grid">
            @foreach($settings as $row)
                @php
                    $listing = $row['listing'];
                    $seo = $row['seo'];
                    $robots = $seo['robots'] === [] ? 'Index, Follow' : collect($seo['robots'])->map(fn ($value) => strtoupper($value))->implode(', ');
                @endphp
                <article class="seo-listing">
                    <div class="seo-head">
                        <div>
                            <div class="seo-title">{{ $listing->propertyname }}</div>
                            <div class="seo-location">{{ $listing->area }}{{ $listing->area && $listing->state ? ', ' : '' }}{{ $listing->state }}</div>
                        </div>
                        <span class="seo-pill">Condo</span>
                    </div>

                    <div class="seo-preview">
                        <div class="seo-preview-title">{{ $seo['meta_title'] !== '' ? $seo['meta_title'] : $listing->propertyname }}</div>
                        <div class="seo-preview-url">{{ $seo['canonical_url'] }}</div>
                        <div class="seo-preview-desc">{{ $seo['meta_description'] !== '' ? $seo['meta_description'] : 'Add a meta description to improve the search result snippet.' }}</div>
                    </div>

                    <div class="seo-meta-grid">
                        <div class="seo-meta-card">
                            <div class="seo-meta-label">Focus Keyword</div>
                            <div class="seo-meta-value">{{ $seo['focus_keyword'] !== '' ? $seo['focus_keyword'] : 'Not set yet' }}</div>
                        </div>
                        <div class="seo-meta-card">
                            <div class="seo-meta-label">Robots</div>
                            <div class="seo-meta-value">{{ $robots }}</div>
                        </div>
                    </div>

                    <div class="seo-actions">
                        <div class="seo-foot">Updated {{ $listing->formatted_updated_date ?? $listing->updateddate ?? 'recently' }}</div>
                        <a href="{{ route('seo.edit', $listing->id) }}" class="btn btn-primary btn-sm">Edit SEO</a>
                    </div>
                </article>
            @endforeach
        </section>

        <div class="pagination">{{ $settings->links('components.pagination') }}</div>
    @else
        <div class="empty-state">
            <p>No condo listings matched your search.</p>
            <a href="{{ route('listings.create', ['source' => 'condo']) }}" class="btn btn-primary">Create Condo Listing</a>
        </div>
    @endif
</div>
@endsection
