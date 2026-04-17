@extends('layouts.app')
@section('title', $current['title'])
@section('page-title', 'Tutorial')
@section('topbar-actions')
    <a href="{{ route('tutorials.index') }}" class="btn btn-secondary btn-sm" style="border-radius:999px;">All Tutorials</a>
@endsection

@section('content')
@include('tutorials._styles')

<div class="tut-wrap">
    <nav class="tut-nav">
        @foreach($topics as $key => $t)
            <a href="{{ route('tutorials.show', $key) }}" class="{{ $key === $topic ? 'active' : '' }}">{{ $t['title'] }}</a>
        @endforeach
    </nav>

    <section class="tut-hero">
        <h1 class="tut-hero-title">{{ $current['title'] }}</h1>
        <p class="tut-hero-subtitle">
            {{ $current['subtitle'] }}
            <strong>Migrating</strong> means making a copy of a listing in another section &mdash; like copying it from IPP into Condo. The original stays where it is.
        </p>
    </section>

    {{-- STEP 1 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">1</div>
            <div class="tut-step-heading">
                <h3>Open the Listings page</h3>
                <p>Click <strong>Listings</strong> in the menu on the left to see all your property listings.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">/listings</span></div>
            <div class="tut-mockup-body">
                <div class="mck-tabs">
                    <span class="mck-tab active">All <span class="pill">120</span></span>
                    <span class="mck-tab">IPP <span class="pill">80</span></span>
                    <span class="mck-tab">ICP <span class="pill">25</span></span>
                    <span class="mck-tab">Condo <span class="pill">15</span></span>
                </div>
                <div class="mck-grid">
                    <div class="mck-card"><div class="mck-img"></div><div class="mck-line"></div><div class="mck-line short"></div></div>
                    <div class="mck-card"><div class="mck-img"></div><div class="mck-line"></div><div class="mck-line short"></div></div>
                    <div class="mck-card"><div class="mck-img"></div><div class="mck-line"></div><div class="mck-line short"></div></div>
                </div>
            </div>
        </div>
    </section>

    {{-- STEP 2 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">2</div>
            <div class="tut-step-heading">
                <h3>Tick the listings you want to copy</h3>
                <p>Each card has a small <strong>Mark</strong> tickbox in the top-left corner. Click it on every listing you want to move.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">/listings</span></div>
            <div class="tut-mockup-body">
                <div class="mck-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="mck-card" style="position:relative;border-color:var(--tut-blue);box-shadow:0 0 0 1px var(--tut-blue);">
                        <div style="position:absolute;top:8px;left:8px;background:#fff;border:1px solid var(--tut-border);border-radius:999px;padding:4px 10px;font-size:11px;font-weight:700;display:inline-flex;gap:6px;align-items:center;">
                            <span style="width:14px;height:14px;border-radius:3px;background:var(--tut-blue);display:inline-block;"></span> Mark
                        </div>
                        <div class="mck-img"></div>
                        <div class="mck-line"></div>
                        <div class="mck-line short"></div>
                        <div class="annot annot-rect" style="left:0;top:0;width:80px;height:32px;border-radius:999px;"></div>
                        <div class="annot annot-label" style="left:90px;top:2px;">Tick this</div>
                    </div>
                    <div class="mck-card" style="position:relative;">
                        <div style="position:absolute;top:8px;left:8px;background:#fff;border:1px solid var(--tut-border);border-radius:999px;padding:4px 10px;font-size:11px;font-weight:700;display:inline-flex;gap:6px;align-items:center;">
                            <span style="width:14px;height:14px;border-radius:3px;background:#fff;border:1px solid var(--tut-border);display:inline-block;"></span> Mark
                        </div>
                        <div class="mck-img"></div>
                        <div class="mck-line"></div>
                        <div class="mck-line short"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tut-step-tip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            <div><strong>Want to move all of them?</strong> Use the <strong>Mark all results</strong> tickbox at the top of the page.</div>
        </div>
    </section>

    {{-- STEP 3 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">3</div>
            <div class="tut-step-heading">
                <h3>Click &ldquo;Migrate selected&rdquo;</h3>
                <p>Once your listings are ticked, the gray <strong>Migrate selected</strong> button at the top will turn on. Click it.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">/listings</span></div>
            <div class="tut-mockup-body">
                <div class="mck-card" style="background:linear-gradient(135deg,#fff,#f7faff);display:flex;justify-content:space-between;align-items:center;gap:12px;padding:18px;flex-wrap:wrap;position:relative;">
                    <div>
                        <div style="font-size:15px;font-weight:700;">Bulk actions</div>
                        <div style="font-size:12px;color:var(--tut-muted);margin-top:2px;">3 selected</div>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <span class="mck-btn alt">Migrate selected</span>
                        <span class="mck-btn danger">Delete selected</span>
                    </div>
                    <div class="annot annot-rect" style="right:142px;top:14px;width:154px;height:42px;"></div>
                    <div class="annot annot-label" style="right:150px;top:60px;">Click this</div>
                </div>
            </div>
        </div>
    </section>

    {{-- STEP 4 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">4</div>
            <div class="tut-step-heading">
                <h3>Choose where to copy them</h3>
                <p>A small box appears. Tap the place you want the copies to go: <strong>IPP</strong>, <strong>ICP</strong>, or <strong>Condo</strong>.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">migrate dialog</span></div>
            <div class="tut-mockup-body" style="background:rgba(15,23,42,0.06);padding:30px;">
                <div class="mck-card" style="background:#fff;padding:20px;max-width:420px;margin:0 auto;">
                    <div style="font-size:18px;font-weight:700;margin-bottom:6px;">Migrate selected listings</div>
                    <div style="font-size:13px;color:var(--tut-muted);">You are about to copy <strong>3</strong> listings into another source.</div>

                    <div style="display:grid;gap:10px;margin-top:14px;position:relative;">
                        <div class="mck-card" style="padding:12px;display:flex;gap:10px;align-items:center;">
                            <span style="width:16px;height:16px;border-radius:50%;border:2px solid var(--tut-border);"></span>
                            <div><strong style="font-size:13px;">IPP</strong><div style="font-size:11px;color:var(--tut-muted);">My personal site</div></div>
                        </div>
                        <div class="mck-card" style="padding:12px;display:flex;gap:10px;align-items:center;border-color:var(--tut-blue);background:#f0f8ff;">
                            <span style="width:16px;height:16px;border-radius:50%;border:5px solid var(--tut-blue);"></span>
                            <div><strong style="font-size:13px;">Condo</strong><div style="font-size:11px;color:var(--tut-muted);">WordPress condo site</div></div>
                        </div>
                        <div class="annot annot-rect" style="left:-6px;top:50px;right:-6px;height:62px;"></div>
                        <div class="annot annot-label" style="right:-6px;top:118px;">Pick the destination</div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:18px;position:relative;">
                        <span class="mck-btn alt">Cancel</span>
                        <span class="mck-btn">Start migrate</span>
                        <div class="annot annot-rect" style="right:-8px;top:-8px;width:130px;height:42px;"></div>
                        <div class="annot annot-label" style="right:138px;top:-2px;">Then click Start</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- STEP 5 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">5</div>
            <div class="tut-step-heading">
                <h3>Wait for the green tick</h3>
                <p>You&rsquo;ll see a progress bar. When it shows a green message, the migration is done. The page will refresh on its own.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">finishing up</span></div>
            <div class="tut-mockup-body" style="background:rgba(15,23,42,0.06);padding:30px;">
                <div class="mck-card" style="background:#fff;padding:20px;max-width:420px;margin:0 auto;">
                    <div style="font-size:18px;font-weight:700;margin-bottom:14px;">All done</div>
                    <div style="height:10px;border-radius:999px;background:#e8edf4;overflow:hidden;margin-bottom:12px;">
                        <div style="height:100%;width:100%;background:linear-gradient(90deg,#0066cc,#34c759);"></div>
                    </div>
                    <div style="padding:12px 14px;border-radius:10px;background:#eefbf3;color:#18794e;font-size:13px;font-weight:600;">
                        ✓ 3 listings copied successfully.
                    </div>
                </div>
            </div>
        </div>

        <div class="tut-checklist">
            <div class="tut-check">
                <div class="tut-check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                <div class="tut-check-text">Your original listings are still where they were &mdash; nothing was deleted.</div>
            </div>
            <div class="tut-check">
                <div class="tut-check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                <div class="tut-check-text">The new copies appear in the destination tab (e.g. Condo).</div>
            </div>
            <div class="tut-check">
                <div class="tut-check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                <div class="tut-check-text">Photos, prices, and descriptions are copied automatically.</div>
            </div>
        </div>
    </section>

    <div class="tut-cta-row">
        <a href="{{ route('listings.index') }}" class="tut-cta-btn primary">Open my listings</a>
        <a href="{{ route('tutorials.show', 'add-listing') }}" class="tut-cta-btn secondary">← Back to: Add a listing</a>
    </div>
</div>
@endsection
