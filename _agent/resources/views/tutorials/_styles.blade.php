<style>
    /* Tutorial - friendly, big-text, accessible */
    :root {
        --tut-bg: #fbfbfd;
        --tut-card: #ffffff;
        --tut-text: #1d1d1f;
        --tut-muted: #6e6e73;
        --tut-border: rgba(0, 0, 0, 0.08);
        --tut-blue: #0066cc;
        --tut-blue-soft: #e8f1fc;
        --tut-amber: #ff9500;
        --tut-amber-soft: #fff5e5;
        --tut-green: #34c759;
        --tut-radius: 20px;
        --tut-radius-sm: 14px;
    }

    .tut-wrap { max-width: 960px; margin: 0 auto; display: grid; gap: 28px; }

    /* Hero / heading */
    .tut-hero { background: var(--tut-card); border: 1px solid var(--tut-border); border-radius: var(--tut-radius); padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
    .tut-hero-title { font-size: clamp(26px, 3.5vw, 34px); font-weight: 700; letter-spacing: -0.02em; color: var(--tut-text); margin: 0 0 10px; line-height: 1.18; }
    .tut-hero-subtitle { font-size: 17px; line-height: 1.55; color: var(--tut-muted); margin: 0; font-weight: 400; max-width: 640px; }
    .tut-hero-tags { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
    .tut-tag { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: var(--tut-blue-soft); color: var(--tut-blue); border-radius: 999px; font-size: 13px; font-weight: 600; }
    .tut-tag.amber { background: var(--tut-amber-soft); color: #b25e00; }

    /* Topic cards (index page) */
    .tut-topic-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; }
    .tut-topic-card {
        display: flex; flex-direction: column; gap: 14px;
        background: var(--tut-card); border: 1px solid var(--tut-border); border-radius: var(--tut-radius);
        padding: 28px; text-decoration: none; color: var(--tut-text);
        transition: all .25s ease;
    }
    .tut-topic-card:hover { transform: translateY(-4px); box-shadow: 0 18px 36px rgba(0,0,0,0.08); border-color: rgba(0, 102, 204, 0.25); }
    .tut-topic-icon { width: 52px; height: 52px; border-radius: 16px; background: var(--tut-blue-soft); color: var(--tut-blue); display: grid; place-items: center; }
    .tut-topic-icon svg { width: 28px; height: 28px; }
    .tut-topic-title { font-size: 19px; font-weight: 700; letter-spacing: -0.01em; margin: 0; }
    .tut-topic-desc { font-size: 15px; color: var(--tut-muted); margin: 0; line-height: 1.5; }
    .tut-topic-cta { margin-top: 8px; display: inline-flex; align-items: center; gap: 6px; color: var(--tut-blue); font-weight: 600; font-size: 14px; }

    /* Step card */
    .tut-step {
        background: var(--tut-card); border: 1px solid var(--tut-border); border-radius: var(--tut-radius);
        padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .tut-step-head { display: flex; align-items: center; gap: 16px; margin-bottom: 18px; }
    .tut-step-num { flex-shrink: 0; width: 44px; height: 44px; border-radius: 50%; background: var(--tut-text); color: #fff; display: grid; place-items: center; font-size: 18px; font-weight: 700; }
    .tut-step-heading h3 { margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.015em; line-height: 1.2; }
    .tut-step-heading p { margin: 4px 0 0; font-size: 15px; color: var(--tut-muted); line-height: 1.55; }

    .tut-step-tip { margin-top: 18px; padding: 16px 18px; background: var(--tut-amber-soft); border-radius: var(--tut-radius-sm); color: #8a4a00; font-size: 14px; line-height: 1.55; display: flex; gap: 12px; align-items: flex-start; }
    .tut-step-tip svg { flex-shrink: 0; width: 22px; height: 22px; color: var(--tut-amber); margin-top: 1px; }
    .tut-step-tip strong { display: block; color: #6f3a00; margin-bottom: 2px; font-size: 14px; font-weight: 700; }

    /* Annotated mockup container */
    .tut-mockup { position: relative; margin-top: 18px; border: 1px solid var(--tut-border); border-radius: var(--tut-radius-sm); overflow: hidden; background: #fafbfc; }
    .tut-mockup-bar { display: flex; align-items: center; gap: 6px; padding: 10px 14px; background: #f5f5f7; border-bottom: 1px solid var(--tut-border); }
    .tut-mockup-bar span { width: 11px; height: 11px; border-radius: 50%; background: #d8d8db; }
    .tut-mockup-bar span:nth-child(1) { background: #ff5f57; }
    .tut-mockup-bar span:nth-child(2) { background: #febc2e; }
    .tut-mockup-bar span:nth-child(3) { background: #28c840; }
    .tut-mockup-url { margin-left: 10px; font-size: 12px; color: var(--tut-muted); font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
    .tut-mockup-body { padding: 22px; min-height: 260px; }

    /* Mock UI tokens (reused across mockups) */
    .mck-topbar { display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; background: rgba(251,251,253,0.95); border-bottom: 1px solid var(--tut-border); border-radius: 12px 12px 0 0; }
    .mck-topbar h4 { margin: 0; font-size: 16px; font-weight: 700; }
    .mck-tabs { display: inline-flex; padding: 4px; background: rgba(120,120,128,0.12); border-radius: 10px; gap: 2px; margin: 18px 0 16px; }
    .mck-tab { padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 8px; color: var(--tut-text); }
    .mck-tab.active { background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
    .mck-tab .pill { font-size: 11px; padding: 1px 7px; border-radius: 999px; background: rgba(0,0,0,0.06); margin-left: 4px; }

    .mck-card { background: #fff; border: 1px solid var(--tut-border); border-radius: 14px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
    .mck-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
    .mck-img { width: 100%; height: 80px; background: linear-gradient(135deg, #d2d2d7, #f0f0f3); border-radius: 10px; }
    .mck-line { background: rgba(0,0,0,0.08); height: 8px; border-radius: 4px; margin: 8px 0 6px; }
    .mck-line.short { width: 60%; }
    .mck-line.tiny { width: 30%; height: 6px; }
    .mck-btn { display: inline-flex; padding: 8px 16px; border-radius: 999px; background: var(--tut-blue); color: #fff; font-size: 12px; font-weight: 600; }
    .mck-btn.alt { background: rgba(0,0,0,0.06); color: var(--tut-text); }
    .mck-btn.danger { background: #ff3b30; color: #fff; }

    .mck-form { display: grid; gap: 10px; margin-top: 8px; }
    .mck-input { padding: 12px 14px; border: 1px solid var(--tut-border); border-radius: 10px; background: #f5f5f7; color: var(--tut-muted); font-size: 13px; }
    .mck-row { display: flex; gap: 10px; flex-wrap: wrap; }
    .mck-row .mck-input { flex: 1; min-width: 120px; }

    .mck-stepper { display: flex; gap: 10px; align-items: center; margin-bottom: 16px; }
    .mck-step-dot { width: 26px; height: 26px; border-radius: 50%; background: rgba(0,0,0,0.08); color: var(--tut-muted); font-size: 12px; font-weight: 700; display: grid; place-items: center; }
    .mck-step-dot.active { background: var(--tut-text); color: #fff; }
    .mck-step-line { flex: 1; height: 2px; background: rgba(0,0,0,0.08); }

    .tut-side-menu {
        background: #fff;
        border: 1px solid var(--tut-border);
        border-radius: 12px;
        padding: 14px;
        display: grid;
        gap: 6px;
        position: relative;
        align-content: start;
    }

    .tut-side-heading {
        font-size: 12px;
        font-weight: 700;
        color: var(--tut-muted);
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .tut-side-item {
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 13px;
        color: var(--tut-muted);
    }

    .tut-side-item.active {
        background: rgba(0,0,0,0.04);
        font-weight: 700;
        color: var(--tut-text);
    }

    .tut-side-target {
        position: relative;
    }

    .tut-side-target .annot-rect {
        left: -6px;
        top: -6px;
        right: -6px;
        bottom: -6px;
    }

    .tut-side-target .annot-label {
        left: calc(100% - 28px);
        top: -12px;
    }

    /* Highlights / annotations */
    .annot {
        position: absolute;
        z-index: 5;
        pointer-events: none;
    }
    .annot-circle {
        border: 3px solid #ff3b30;
        border-radius: 999px;
        box-shadow: 0 0 0 6px rgba(255, 59, 48, 0.18);
        animation: tutPulse 1.6s ease-in-out infinite;
    }
    .annot-rect {
        border: 3px solid #ff3b30;
        border-radius: 12px;
        box-shadow: 0 0 0 6px rgba(255, 59, 48, 0.18);
        animation: tutPulse 1.6s ease-in-out infinite;
    }
    .annot-label {
        position: absolute;
        background: #ff3b30;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 10px;
        border-radius: 999px;
        white-space: nowrap;
        box-shadow: 0 6px 16px rgba(255, 59, 48, 0.35);
        pointer-events: none;
    }
    .annot-arrow {
        position: absolute;
        width: 0;
        height: 0;
        pointer-events: none;
    }
    @keyframes tutPulse {
        0%, 100% { box-shadow: 0 0 0 6px rgba(255, 59, 48, 0.18); }
        50%      { box-shadow: 0 0 0 12px rgba(255, 59, 48, 0.04); }
    }

    /* Quick checklist */
    .tut-checklist { display: grid; gap: 12px; margin-top: 18px; }
    .tut-check { display: flex; gap: 12px; align-items: flex-start; }
    .tut-check-icon { flex-shrink: 0; width: 26px; height: 26px; border-radius: 50%; background: var(--tut-green); color: #fff; display: grid; place-items: center; }
    .tut-check-icon svg { width: 14px; height: 14px; }
    .tut-check-text { font-size: 15px; line-height: 1.5; color: var(--tut-text); }

    /* Topic nav (in show pages) */
    .tut-nav { display: flex; flex-wrap: wrap; gap: 10px; padding: 14px; background: var(--tut-card); border: 1px solid var(--tut-border); border-radius: var(--tut-radius-sm); }
    .tut-nav a { padding: 8px 14px; border-radius: 999px; font-size: 13px; font-weight: 600; text-decoration: none; color: var(--tut-muted); border: 1px solid transparent; }
    .tut-nav a:hover { background: rgba(0,0,0,0.04); color: var(--tut-text); }
    .tut-nav a.active { background: var(--tut-text); color: #fff; }

    .tut-cta-row { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 24px; }
    .tut-cta-btn { display: inline-flex; align-items: center; gap: 6px; padding: 12px 22px; border-radius: 999px; font-size: 15px; font-weight: 600; text-decoration: none; transition: transform .15s ease; }
    .tut-cta-btn.primary { background: var(--tut-blue); color: #fff; }
    .tut-cta-btn.primary:hover { transform: scale(1.02); background: #0077ed; }
    .tut-cta-btn.secondary { background: rgba(0,0,0,0.05); color: var(--tut-text); }
    .tut-cta-btn.secondary:hover { background: rgba(0,0,0,0.09); }

    @media (max-width: 640px) {
        .tut-hero { padding: 24px; }
        .tut-step { padding: 22px; }
        .tut-mockup-body { padding: 16px; }
        .annot-label { font-size: 11px; padding: 4px 8px; }
    }
</style>
