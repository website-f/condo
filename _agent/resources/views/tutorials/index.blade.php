@extends('layouts.app')
@section('title', 'Tutorial')
@section('page-title', 'Tutorial & Help')

@section('content')
@include('tutorials._styles')

<div class="tut-wrap">
    <section class="tut-hero">
        <h1 class="tut-hero-title">Hi there! How can we help today?</h1>
        <p class="tut-hero-subtitle">
            Pick a topic below. Each guide shows you exactly which button to click,
            with pictures and big arrows. Take your time &mdash; you can&rsquo;t break anything.
        </p>
        <div class="tut-hero-tags">
            <span class="tut-tag">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                Short reads
            </span>
            <span class="tut-tag amber">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                Beginner friendly
            </span>
        </div>
    </section>

    <section class="tut-topic-grid">
        @foreach($topics as $key => $topic)
            <a href="{{ route('tutorials.show', $key) }}" class="tut-topic-card">
                <div class="tut-topic-icon">
                    @if($topic['icon'] === 'plus')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    @elseif($topic['icon'] === 'pencil')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
                    @endif
                </div>
                <h3 class="tut-topic-title">{{ $topic['title'] }}</h3>
                <p class="tut-topic-desc">{{ $topic['subtitle'] }}</p>
                <span class="tut-topic-cta">
                    Open guide
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </span>
            </a>
        @endforeach
    </section>

    <section class="tut-step">
        <div class="tut-step-head">
            <div class="tut-step-num">i</div>
            <div class="tut-step-heading">
                <h3>Need a real person?</h3>
                <p>If something still feels confusing, just call your office support team. We&rsquo;re happy to walk you through it on the phone.</p>
            </div>
        </div>
    </section>
</div>
@endsection
