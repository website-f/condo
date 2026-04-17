@extends('layouts.app')

@section('title', 'SEO')
@section('page-title', 'SEO')

@section('content')
<style>
    .seo-soon-shell {
        display: flex;
        justify-content: center;
    }

    .seo-soon-card {
        width: min(760px, 100%);
        padding: 34px;
        border-radius: 28px;
        border: 1px solid var(--border-light);
        background:
            radial-gradient(circle at top right, rgba(0, 0, 0, 0.04), transparent 34%),
            linear-gradient(180deg, #ffffff 0%, #fafbfd 100%);
        box-shadow: var(--shadow-md);
    }

    .seo-soon-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: #fff6dc;
        border: 1px solid #f2df9d;
        color: #8b6a00;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 18px;
    }

    .seo-soon-card h3 {
        font-size: clamp(28px, 4vw, 40px);
        line-height: 1.08;
        letter-spacing: -0.03em;
        margin-bottom: 14px;
        color: var(--text);
    }

    .seo-soon-card p {
        color: var(--text-secondary);
        font-size: 15px;
        line-height: 1.75;
        max-width: 62ch;
    }

    .seo-soon-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-top: 28px;
    }

    .seo-soon-panel {
        padding: 18px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .seo-soon-panel strong {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        color: var(--text);
    }

    .seo-soon-panel span {
        display: block;
        font-size: 13px;
        line-height: 1.65;
        color: var(--text-secondary);
    }

    .seo-soon-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 28px;
    }

    @media (max-width: 760px) {
        .seo-soon-card {
            padding: 24px;
            border-radius: 24px;
        }

        .seo-soon-grid {
            grid-template-columns: 1fr;
        }

        .seo-soon-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="seo-soon-shell">
    <section class="seo-soon-card">
        <div class="seo-soon-tag">Coming Soon</div>
        <h3>SEO tools are paused for now.</h3>
        <p>
            We are not using the `/agent/seo` section at the moment, so this area is intentionally disabled to keep the portal simpler and avoid sending users into an unfinished flow.
        </p>

        <div class="seo-soon-grid">
            <article class="seo-soon-panel">
                <strong>What stays active</strong>
                <span>Listings, articles, social media scheduling, reports, and account tools continue to work as normal.</span>
            </article>
            <article class="seo-soon-panel">
                <strong>What changed</strong>
                <span>The old SEO editing screens are hidden for now, including the listing shortcuts that used to open them.</span>
            </article>
            <article class="seo-soon-panel">
                <strong>What users see</strong>
                <span>The sidebar now clearly marks SEO as coming soon instead of letting users open a broken or incomplete page.</span>
            </article>
        </div>

        <div class="seo-soon-actions">
            <a href="{{ route('listings.index') }}" class="btn btn-primary">Go To Listings</a>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back To Dashboard</a>
        </div>
    </section>
</div>
@endsection
