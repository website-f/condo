@extends('layouts.app')

@section('title', 'Recently Deleted')

@section('content')
    <style>
        .deleted-toolbar {
            display: flex;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .deleted-toolbar form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            width: 100%;
        }
        .deleted-toolbar .form-input {
            min-width: 260px;
            flex: 1 1 320px;
        }
        .deleted-tabs {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        .deleted-tab {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
        }
        .deleted-tab.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }
        .deleted-tab-count {
            display: inline-flex;
            min-width: 22px;
            height: 22px;
            border-radius: 999px;
            align-items: center;
            justify-content: center;
            padding: 0 7px;
            background: rgba(0, 0, 0, 0.08);
            font-size: 12px;
        }
        .deleted-tab.active .deleted-tab-count {
            background: rgba(255, 255, 255, 0.18);
        }
        .sql-card {
            margin-bottom: 24px;
            border: 1px solid rgba(243, 156, 18, 0.25);
            background: linear-gradient(180deg, rgba(255, 248, 230, 0.8), rgba(255, 255, 255, 1));
        }
        .sql-card pre {
            margin-top: 16px;
            padding: 18px;
            border-radius: 16px;
            background: #171717;
            color: #f5f5f5;
            overflow-x: auto;
            font-size: 12px;
            line-height: 1.6;
        }
        .deleted-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .deleted-card {
            padding: 22px;
            border-radius: 20px;
            border: 1px solid var(--border-light);
            background:
                radial-gradient(circle at top right, rgba(0, 0, 0, 0.04), transparent 40%),
                #fff;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .deleted-card-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
        }
        .deleted-card-title {
            font-size: 18px;
            font-weight: 600;
            line-height: 1.25;
        }
        .deleted-card-source {
            font-size: 12px;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--accent-light);
            color: var(--text-secondary);
            white-space: nowrap;
        }
        .deleted-card-summary {
            color: var(--text-secondary);
            line-height: 1.6;
            min-height: 42px;
        }
        .deleted-card-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
        }
        .deleted-card-meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding-top: 14px;
            border-top: 1px solid var(--border-light);
            color: var(--text-secondary);
            font-size: 13px;
        }
        .deleted-card-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .deleted-card-actions form {
            flex: 1 1 140px;
        }
        .deleted-card-actions .btn {
            width: 100%;
        }
        @media (max-width: 640px) {
            .deleted-toolbar form {
                flex-direction: column;
                align-items: stretch;
            }
            .deleted-toolbar .btn {
                width: 100%;
            }
            .deleted-card-actions form {
                flex-basis: 100%;
            }
        }
    </style>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">All Deleted</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
            <div class="stat-sub">Everything currently waiting in recycle bin.</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Listings</div>
            <div class="stat-value">{{ number_format($stats['listings']) }}</div>
            <div class="stat-sub">IPP, ICP, and Condo listing records.</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Social</div>
            <div class="stat-value">{{ number_format($stats['social']) }}</div>
            <div class="stat-sub">Deleted FS Poster schedule groups.</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Portal Cleanup</div>
            <div class="stat-value" style="font-size:20px;">Focused</div>
            <div class="stat-sub">Recycle bin now highlights listings and social schedules only.</div>
        </div>
    </div>

    @if (! $registryAvailable)
        <div class="card sql-card">
            <div class="card-header">Run Laravel Migration</div>
            <p style="color: var(--text-secondary); line-height: 1.7;">
                Listings can still show deleted items from their existing source tables, but social schedule restore still needs one shared table in the main Laravel database.
                Since this table belongs to the Laravel CMS database, the clean setup is just running the normal migration command from the `_agent` folder.
            </p>
            <pre><code>{{ $migrationCommand }}</code></pre>
        </div>
    @endif

    <div class="deleted-toolbar">
        <form method="GET" action="{{ route('recently-deleted.index') }}">
            <input type="hidden" name="section" value="{{ $activeSection }}">
            <input type="text" name="search" class="form-input" value="{{ $search }}" placeholder="Search deleted titles, summaries, source labels">
            <button type="submit" class="btn btn-primary">Filter</button>
            @if ($search !== '')
                <a href="{{ route('recently-deleted.index', ['section' => $activeSection]) }}" class="btn btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    <div class="deleted-tabs">
        @foreach ($sections as $section)
            <a
                href="{{ route('recently-deleted.index', array_filter(['section' => $section['key'], 'search' => $search !== '' ? $search : null])) }}"
                class="deleted-tab {{ $activeSection === $section['key'] ? 'active' : '' }}"
            >
                <span>{{ $section['label'] }}</span>
                <span class="deleted-tab-count">{{ number_format($section['count']) }}</span>
            </a>
        @endforeach
    </div>

    @if ($items->count() === 0)
        <div class="empty-state">
            <p>No deleted records matched this view.</p>
            <a href="{{ route('recently-deleted.index') }}" class="btn btn-secondary">Show Everything</a>
        </div>
    @else
        <div class="deleted-grid">
            @foreach ($items as $item)
                <div class="deleted-card">
                    <div class="deleted-card-top">
                        <div>
                            <div class="deleted-card-title">{{ $item['title'] }}</div>
                            @if (! empty($item['subtitle']))
                                <div class="deleted-card-subtitle">{{ $item['subtitle'] }}</div>
                            @endif
                        </div>
                        <span class="deleted-card-source">{{ $item['source_label'] }}</span>
                    </div>

                    @if (! empty($item['summary']))
                        <div class="deleted-card-summary">{{ $item['summary'] }}</div>
                    @endif

                    <div class="deleted-card-meta">
                        <span>{{ ucfirst($item['group']) }}</span>
                        <span>Deleted {{ $item['deleted_at_label'] }}</span>
                    </div>

                    <div class="deleted-card-actions">
                        <form method="POST" action="{{ route('recently-deleted.restore') }}">
                            @csrf
                            <input type="hidden" name="type" value="{{ $item['type'] }}">
                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                            <input type="hidden" name="section" value="{{ $activeSection }}">
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="page" value="{{ $items->currentPage() }}">
                            <button type="submit" class="btn btn-primary">Recover</button>
                        </form>

                        <form method="POST" action="{{ route('recently-deleted.destroy') }}" onsubmit="return confirm('Permanently delete this item? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="type" value="{{ $item['type'] }}">
                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                            <input type="hidden" name="section" value="{{ $activeSection }}">
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="page" value="{{ $items->currentPage() }}">
                            <button type="submit" class="btn btn-danger">Delete Forever</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pagination">
            {{ $items->links() }}
        </div>
    @endif
@endsection
