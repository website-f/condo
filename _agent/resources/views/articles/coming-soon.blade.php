@extends('layouts.app')

@section('title', 'Articles')
@section('page-title', 'Articles')

@section('content')
<style>
    .coming-soon-shell {
        display: flex;
        justify-content: center;
    }

    .coming-soon-card {
        width: min(760px, 100%);
        padding: 36px;
        border-radius: 28px;
        border: 1px solid var(--border-light);
        background:
            radial-gradient(circle at top right, rgba(0, 102, 204, 0.08), transparent 30%),
            linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: var(--shadow-md);
    }

    .coming-soon-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(0, 102, 204, 0.08);
        color: #0056b3;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 18px;
    }

    .coming-soon-card h3 {
        font-size: clamp(28px, 4vw, 40px);
        line-height: 1.08;
        letter-spacing: -0.03em;
        margin-bottom: 14px;
        color: var(--text);
    }

    .coming-soon-card p {
        color: var(--text-secondary);
        font-size: 15px;
        line-height: 1.75;
        max-width: 60ch;
    }

    .coming-soon-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-top: 28px;
    }

    .coming-soon-panel {
        padding: 18px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .coming-soon-panel strong {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        color: var(--text);
    }

    .coming-soon-panel span {
        display: block;
        font-size: 13px;
        line-height: 1.65;
        color: var(--text-secondary);
    }

    .coming-soon-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 28px;
    }

    @media (max-width: 760px) {
        .coming-soon-card {
            padding: 24px;
            border-radius: 24px;
        }

        .coming-soon-grid {
            grid-template-columns: 1fr;
        }

        .coming-soon-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="coming-soon-shell">
    <section class="coming-soon-card">
        <div class="coming-soon-chip">Coming Soon</div>
        <h3>Articles are being reshaped for the next portal release.</h3>
        <p>
            We’ve paused the old article tools while we streamline the content flow around listings, SEO, and social publishing.
            This section will come back in a cleaner form once the new publishing experience is ready.
        </p>

        <div class="coming-soon-grid">
            <article class="coming-soon-panel">
                <strong>What stays live</strong>
                <span>Listings, SEO, social scheduling, reports, and profile tools continue to work as normal.</span>
            </article>
            <article class="coming-soon-panel">
                <strong>What changed</strong>
                <span>The old article CRUD screens are intentionally paused so agents don’t end up using an outdated flow.</span>
            </article>
            <article class="coming-soon-panel">
                <strong>What’s next</strong>
                <span>The article experience will return once the new content workflow is aligned with the rest of the portal.</span>
            </article>
        </div>

        <div class="coming-soon-actions">
            <a href="{{ route('listings.index') }}" class="btn btn-primary">Go To Listings</a>
            <a href="{{ route('social.index') }}" class="btn btn-secondary">Open Social Media</a>
        </div>
    </section>
</div>
@endsection
