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
                <p>Click <strong>Listings</strong> in the menu on the left.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">/listings</span></div>
            <div class="tut-mockup-body" style="display:grid;grid-template-columns:200px 1fr;gap:18px;">
                <div class="tut-side-menu">
                    <div class="tut-side-heading">Content</div>
                    <div class="tut-side-item">Articles</div>
                    <div class="tut-side-target">
                        <div class="tut-side-item active">Listings</div>
                        <div class="annot annot-rect"></div>
                        <div class="annot annot-label">Click here</div>
                    </div>
                    <div class="tut-side-item">Recently Deleted</div>
                </div>
                <div style="display:grid;gap:10px;align-content:start;">
                    <div class="mck-line" style="width:30%;height:14px;"></div>
                    <div class="mck-grid">
                        <div class="mck-card"><div class="mck-img"></div><div class="mck-line"></div></div>
                        <div class="mck-card"><div class="mck-img"></div><div class="mck-line"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- STEP 2 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">2</div>
            <div class="tut-step-heading">
                <h3>Find the listing you want to change</h3>
                <p>Scroll through your listings or use the search box at the top to find it quickly.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">/listings</span></div>
            <div class="tut-mockup-body">
                <div class="mck-card" style="display:flex;gap:10px;align-items:center;padding:12px;position:relative;">
                    <div class="mck-input" style="flex:1;background:#fff;">🔍 Search by property name…</div>
                    <span class="mck-btn alt">Filters</span>
                    <div class="annot annot-rect" style="left:6px;top:6px;right:96px;height:42px;"></div>
                    <div class="annot annot-label" style="left:6px;top:54px;">Type the property name here</div>
                </div>

                <div class="mck-grid" style="margin-top:14px;">
                    <div class="mck-card"><div class="mck-img"></div><div class="mck-line"></div><div class="mck-line short"></div></div>
                    <div class="mck-card" style="border-color:var(--tut-blue);box-shadow:0 0 0 1px var(--tut-blue);position:relative;">
                        <div class="mck-img"></div>
                        <div class="mck-line"></div>
                        <div class="mck-line short"></div>
                        <div class="annot annot-circle" style="right:-6px;top:-6px;width:24px;height:24px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- STEP 3 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">3</div>
            <div class="tut-step-heading">
                <h3>Click the &ldquo;Edit&rdquo; button on the card</h3>
                <p>At the bottom of the listing card you&rsquo;ll see three buttons: <strong>View</strong>, <strong>Edit</strong>, and <strong>Delete</strong>. Click <strong>Edit</strong>.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">/listings</span></div>
            <div class="tut-mockup-body" style="display:flex;justify-content:center;">
                <div class="mck-card" style="width:280px;padding:0;overflow:hidden;position:relative;">
                    <div class="mck-img" style="height:140px;border-radius:0;"></div>
                    <div style="padding:14px;">
                        <div style="font-size:12px;font-weight:700;color:#0d47a1;background:#e3f2fd;display:inline-block;padding:3px 8px;border-radius:6px;">IPP</div>
                        <div style="font-size:18px;font-weight:700;margin:8px 0 4px;">RM 450,000</div>
                        <div class="mck-line short"></div>
                    </div>
                    <div style="padding:12px 14px;background:#fbfbfd;border-top:1px solid var(--tut-border);display:flex;gap:6px;position:relative;">
                        <span class="mck-btn alt" style="flex:1;justify-content:center;">View</span>
                        <span class="mck-btn alt" style="flex:1;justify-content:center;">Edit</span>
                        <span class="mck-btn danger" style="flex:1;justify-content:center;">Delete</span>
                        <div class="annot annot-rect" style="left:33%;top:6px;width:34%;height:34px;"></div>
                        <div class="annot annot-label" style="left:50%;top:50px;transform:translateX(-50%);">Click Edit</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- STEP 4 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">4</div>
            <div class="tut-step-heading">
                <h3>Move through the steps and change what you need</h3>
                <p>The wizard opens with everything already filled in. Click <strong>Continue →</strong> to skip steps that are fine, and only change what you want.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">/listings/123/edit</span></div>
            <div class="tut-mockup-body">
                <div class="mck-stepper">
                    <div class="mck-step-dot">✓</div>
                    <div class="mck-step-line" style="background:var(--tut-text);"></div>
                    <div class="mck-step-dot">✓</div>
                    <div class="mck-step-line"></div>
                    <div class="mck-step-dot active">3</div>
                    <div class="mck-step-line"></div>
                    <div class="mck-step-dot">4</div>
                    <div class="mck-step-line"></div>
                    <div class="mck-step-dot">5</div>
                </div>
                <div style="font-size:18px;font-weight:700;margin-bottom:6px;">Step 3 of 5 &mdash; Basic Information</div>
                <div class="mck-form" style="position:relative;">
                    <div style="font-size:13px;font-weight:600;">Price *</div>
                    <div class="mck-input" style="background:#fff;color:var(--tut-text);font-weight:600;">450,000</div>
                    <div class="annot annot-rect" style="left:0;top:30px;right:0;height:50px;"></div>
                    <div class="annot annot-label" style="right:10px;top:78px;">Change the number, then Continue</div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:24px;">
                    <span class="mck-btn alt">← Back</span>
                    <span class="mck-btn">Continue →</span>
                </div>
            </div>
        </div>
    </section>

    {{-- STEP 5 --}}
    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">5</div>
            <div class="tut-step-heading">
                <h3>Save your changes</h3>
                <p>On the last step, click the green <strong>Save Listing</strong> button. That&rsquo;s it.</p>
            </div>
        </div>

        <div class="tut-mockup">
            <div class="tut-mockup-bar"><span></span><span></span><span></span><span class="tut-mockup-url">step 5 of 5</span></div>
            <div class="tut-mockup-body">
                <div style="font-size:18px;font-weight:700;margin-bottom:10px;">Step 5 of 5 &mdash; Review &amp; Save</div>
                <div class="mck-card" style="display:grid;gap:8px;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--tut-muted);">Property name</span><strong>Sunset View Condo</strong></div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--tut-muted);">Price</span><strong>RM 480,000</strong></div>
                </div>

                <div style="display:flex;justify-content:space-between;margin-top:18px;position:relative;">
                    <span class="mck-btn alt">← Back</span>
                    <span class="mck-btn" style="background:#34c759;">✓ Save Listing</span>
                    <div class="annot annot-rect" style="right:-8px;top:-8px;width:140px;height:42px;border-color:#34c759;box-shadow:0 0 0 6px rgba(52, 199, 89, 0.2);"></div>
                    <div class="annot annot-label" style="right:150px;top:-2px;background:#34c759;box-shadow:0 6px 16px rgba(52, 199, 89, 0.35);">Click here</div>
                </div>
            </div>
        </div>

        <div class="tut-step-tip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <div><strong>Made a mistake?</strong> Don&rsquo;t worry &mdash; nothing changes until you click Save. You can always click Cancel.</div>
        </div>
    </section>

    <div class="tut-cta-row">
        <a href="{{ route('listings.index') }}" class="tut-cta-btn primary">
            Open my listings
        </a>
    </div>
</div>
@endsection
