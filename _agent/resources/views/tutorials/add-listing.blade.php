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
        <p class="tut-hero-subtitle">{{ $current['subtitle'] }} The pictures below show <strong>red circles</strong> on the buttons you need to tap or click.</p>
    </section>

    {{-- STEP 1 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">1</div>
            <div class="tut-step-heading">
                <h3>Open the Listings page</h3>
                <p>On the left side of the screen, find the menu and click <strong>Listings</strong>.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">/listings</span></div>
            <div class="tut-mockup-body" style="display:grid;grid-template-columns:200px 1fr;gap:18px;">
                {{-- mock sidebar --}}
                <div class="tut-side-menu">
                    <div class="tut-side-heading">Content</div>
                    <div class="tut-side-item">Articles</div>
                    <div class="tut-side-target">
                        <div id="annot-anchor-1" class="tut-side-item active">Listings</div>
                        <div class="annot annot-rect"></div>
                        <div class="annot annot-label">Click here</div>
                    </div>
                    <div class="tut-side-item">Recently Deleted</div>
                </div>
                {{-- mock content --}}
                <div style="display:grid;gap:10px;align-content:start;">
                    <div class="mck-line" style="width:30%;height:14px;"></div>
                    <div class="mck-line short"></div>
                    <div class="mck-grid">
                        <div class="mck-card"><div class="mck-img"></div><div class="mck-line"></div><div class="mck-line short"></div></div>
                        <div class="mck-card"><div class="mck-img"></div><div class="mck-line"></div><div class="mck-line short"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tut-step-tip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <div><strong>On a phone?</strong> Tap the three lines (☰) at the top to open the menu.</div>
        </div>
    </section>

    {{-- STEP 2 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">2</div>
            <div class="tut-step-heading">
                <h3>Click the blue &ldquo;New Listing&rdquo; button</h3>
                <p>Look at the top-right corner of the page. Click the blue button.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">/listings</span></div>
            <div class="tut-mockup-body">
                <div class="mck-topbar">
                    <h4>Listings</h4>
                    <div style="position:relative;">
                        <span class="mck-btn">＋ New Listing</span>
                        <div class="annot annot-rect" style="left:-8px;top:-8px;width:140px;height:42px;"></div>
                        <div class="annot annot-label" style="left:135px;top:-2px;">Click this</div>
                    </div>
                </div>
                <div class="mck-tabs">
                    <span class="mck-tab active">All <span class="pill">120</span></span>
                    <span class="mck-tab">IPP <span class="pill">80</span></span>
                    <span class="mck-tab">ICP <span class="pill">25</span></span>
                </div>
                <div class="mck-grid">
                    <div class="mck-card"><div class="mck-img"></div><div class="mck-line"></div><div class="mck-line short"></div></div>
                    <div class="mck-card"><div class="mck-img"></div><div class="mck-line"></div><div class="mck-line short"></div></div>
                    <div class="mck-card"><div class="mck-img"></div><div class="mck-line"></div><div class="mck-line short"></div></div>
                </div>
            </div>
        </div>
    </section>

    {{-- STEP 3 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">3</div>
            <div class="tut-step-heading">
                <h3>Choose where the listing belongs</h3>
                <p>Tap one of the cards: <strong>IPP</strong>, <strong>ICP</strong>, or <strong>Condo</strong>. If you&rsquo;re not sure &mdash; pick <strong>IPP</strong>. You can change it later.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">/listings/create</span></div>
            <div class="tut-mockup-body">
                <div class="mck-stepper">
                    <div class="mck-step-dot active">1</div>
                    <div class="mck-step-line"></div>
                    <div class="mck-step-dot">2</div>
                    <div class="mck-step-line"></div>
                    <div class="mck-step-dot">3</div>
                    <div class="mck-step-line"></div>
                    <div class="mck-step-dot">4</div>
                    <div class="mck-step-line"></div>
                    <div class="mck-step-dot">5</div>
                </div>
                <div style="font-size:18px;font-weight:700;margin-bottom:6px;">Step 1 of 5 &mdash; Where does this listing belong?</div>
                <div class="mck-line short"></div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:14px;position:relative;">
                    <div class="mck-card" style="text-align:center;padding:18px 12px;">
                        <div style="font-weight:700;font-size:14px;">IPP</div>
                        <div style="font-size:12px;color:var(--tut-muted);margin-top:4px;">My personal site</div>
                    </div>
                    <div class="mck-card" style="text-align:center;padding:18px 12px;border-color:var(--tut-blue);box-shadow:0 0 0 1px var(--tut-blue);background:#f0f8ff;">
                        <div style="font-weight:700;font-size:14px;">ICP</div>
                        <div style="font-size:12px;color:var(--tut-muted);margin-top:4px;">Co-broke pool</div>
                    </div>
                    <div class="mck-card" style="text-align:center;padding:18px 12px;">
                        <div style="font-weight:700;font-size:14px;">Condo</div>
                        <div style="font-size:12px;color:var(--tut-muted);margin-top:4px;">Condo site</div>
                    </div>

                    <div class="annot annot-rect" style="left:0;top:0;width:100%;height:78px;border-color:transparent;box-shadow:none;animation:none;"></div>
                    <div class="annot annot-circle" style="left:36%;top:6px;width:24%;height:64px;border-radius:14px;"></div>
                    <div class="annot annot-label" style="left:38%;top:78px;">Pick one</div>
                </div>

                <div style="display:flex;justify-content:space-between;margin-top:24px;position:relative;">
                    <span class="mck-btn alt">← Back</span>
                    <span class="mck-btn">Continue →</span>
                    <div class="annot annot-rect" style="right:-8px;top:-8px;width:120px;height:42px;"></div>
                    <div class="annot annot-label" style="right:130px;top:-2px;">Then this</div>
                </div>
            </div>
        </div>
    </section>

    {{-- STEP 4 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">4</div>
            <div class="tut-step-heading">
                <h3>Add property photos</h3>
                <p>Click the big <strong>Choose Images</strong> button and pick the pictures from your phone or computer.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">step 2 of 5</span></div>
            <div class="tut-mockup-body">
                <div style="font-size:18px;font-weight:700;margin-bottom:6px;">Step 2 of 5 &mdash; Add photos</div>
                <div class="mck-line short"></div>

                <div style="margin-top:14px;border:2px dashed var(--tut-border);border-radius:14px;padding:32px;text-align:center;background:#fbfbfd;position:relative;">
                    <div style="font-size:16px;font-weight:700;margin-bottom:8px;">Add property images</div>
                    <span class="mck-btn">Choose Images</span>
                    <div style="font-size:12px;color:var(--tut-muted);margin-top:10px;">JPG, PNG, WEBP up to 10MB each</div>
                    <div class="annot annot-rect" style="left:50%;top:64px;transform:translateX(-50%);width:170px;height:42px;"></div>
                    <div class="annot annot-label" style="left:50%;top:114px;transform:translateX(-50%);">Click here to add photos</div>
                </div>
            </div>
        </div>

        <div class="tut-step-tip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            <div><strong>Tip:</strong> The first photo you add becomes the cover image. You can pick more than one photo at the same time.</div>
        </div>
    </section>

    {{-- STEP 5 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">5</div>
            <div class="tut-step-heading">
                <h3>Fill in the basic information</h3>
                <p>Type the property name, pick the type, enter the price, and choose the state. That&rsquo;s it &mdash; only the things with a star (*) are required.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">step 3 of 5</span></div>
            <div class="tut-mockup-body">
                <div style="font-size:18px;font-weight:700;margin-bottom:6px;">Step 3 of 5 &mdash; Basic Information</div>
                <div class="mck-form" style="position:relative;">
                    <div style="font-size:13px;font-weight:600;">Property Name *</div>
                    <div class="mck-input">e.g. Sunset View Condo</div>
                    <div class="mck-row">
                        <div style="flex:1;">
                            <div style="font-size:13px;font-weight:600;margin-bottom:6px;">Listing Type *</div>
                            <div class="mck-input">For Sale</div>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:13px;font-weight:600;margin-bottom:6px;">Price *</div>
                            <div class="mck-input">450,000</div>
                        </div>
                    </div>
                    <div class="annot annot-rect" style="left:0;top:30px;right:0;height:54px;"></div>
                    <div class="annot annot-label" style="right:10px;top:80px;">Type the name first</div>
                </div>

                <div style="display:flex;justify-content:space-between;margin-top:18px;">
                    <span class="mck-btn alt">← Back</span>
                    <span class="mck-btn">Continue →</span>
                </div>
            </div>
        </div>
    </section>

    {{-- STEP 6 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">6</div>
            <div class="tut-step-heading">
                <h3>Review and click Save</h3>
                <p>You&rsquo;ll see a summary of what you&rsquo;re about to save. If everything looks right, click the big <strong>Save Listing</strong> button. Done!</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">step 5 of 5</span></div>
            <div class="tut-mockup-body">
                <div style="font-size:18px;font-weight:700;margin-bottom:6px;">Step 5 of 5 &mdash; Review &amp; Save</div>
                <div class="mck-card" style="margin-top:12px;display:grid;gap:8px;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--tut-muted);">Source</span><strong>IPP</strong></div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--tut-muted);">Photos</span><strong>4 added</strong></div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--tut-muted);">Property name</span><strong>Sunset View Condo</strong></div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--tut-muted);">Price</span><strong>RM 450,000</strong></div>
                </div>

                <div style="display:flex;justify-content:space-between;margin-top:18px;position:relative;">
                    <span class="mck-btn alt">← Back</span>
                    <span class="mck-btn" style="background:#34c759;">✓ Save Listing</span>
                    <div class="annot annot-rect" style="right:-8px;top:-8px;width:140px;height:42px;border-color:#34c759;box-shadow:0 0 0 6px rgba(52, 199, 89, 0.2);"></div>
                    <div class="annot annot-label" style="right:150px;top:-2px;background:#34c759;box-shadow:0 6px 16px rgba(52, 199, 89, 0.35);">Save here</div>
                </div>
            </div>
        </div>

        <div class="tut-checklist">
            <div class="tut-check">
                <div class="tut-check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                <div class="tut-check-text">You can always go back to fix something with the <strong>← Back</strong> button.</div>
            </div>
            <div class="tut-check">
                <div class="tut-check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                <div class="tut-check-text">Nothing is saved until you click the green Save button.</div>
            </div>
            <div class="tut-check">
                <div class="tut-check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                <div class="tut-check-text">If you cancel, no changes are made.</div>
            </div>
        </div>
    </section>

    <div class="tut-cta-row">
        <a href="{{ route('listings.create', ['source' => 'ipp']) }}" class="tut-cta-btn primary">
            ＋ Try it now
        </a>
        <a href="{{ route('tutorials.show', 'edit-listing') }}" class="tut-cta-btn secondary">
            Next: How to edit a listing →
        </a>
    </div>
</div>
@endsection
