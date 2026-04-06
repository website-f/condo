@extends('layouts.app')
@section('title', 'Social Media')
@section('page-title', 'FS Poster Scheduler')
@section('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css">
@endsection
@section('topbar-actions')
    <a href="{{ route('social.create') }}" class="btn btn-primary btn-sm">New Schedule</a>
    <a href="{{ rtrim(\App\Support\CondoWordpressBridge::siteBaseUrl(), '/') . '/wp-admin/admin.php?page=fs-poster#/calendar' }}" class="btn btn-secondary btn-sm" target="_blank" rel="noreferrer">WP Calendar</a>
@endsection

@section('content')
<style>
    .social-shell{display:grid;gap:24px}
    .social-hero,.social-card,.social-stat{background:var(--card-bg);border:1px solid var(--border-light);border-radius:var(--radius-md);box-shadow:var(--shadow-sm)}
    .social-hero,.social-card{padding:24px}
    .social-hero-grid{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(280px,.8fr);gap:24px}
    .social-kicker{font-size:12px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px}
    .social-hero h3{font-size:clamp(28px,3vw,38px);line-height:1.08;letter-spacing:-.03em;margin-bottom:12px}
    .social-hero p,.social-note,.social-small{color:var(--text-secondary);line-height:1.6}
    .social-note{padding:18px;border-radius:var(--radius-sm);background:var(--accent-light);border:1px solid var(--border-light);font-size:14px}
    .social-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px}
    .social-stat{padding:22px}
    .social-stat-label{font-size:12px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-secondary);margin-bottom:10px}
    .social-stat-value{font-size:32px;line-height:1;font-weight:600;letter-spacing:-.04em}
    .social-grid{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(320px,.7fr);gap:24px;align-items:start}
    .social-filter-grid{display:grid;grid-template-columns:minmax(0,1fr) repeat(2,minmax(180px,.35fr)) auto auto;gap:12px}
    .social-calendar{padding:12px;border:1px solid var(--border-light);border-radius:var(--radius-sm);background:linear-gradient(180deg,#fff 0%,#fafafc 100%)}
    .social-card-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;margin-bottom:18px}
    .social-card-head h4{font-size:20px;line-height:1.15;letter-spacing:-.02em}
    .social-schedule-list{display:grid;gap:16px}
    .social-schedule{display:grid;gap:16px;padding:18px;border:1px solid var(--border-light);border-radius:var(--radius-md);background:linear-gradient(180deg,#fff 0%,#fafafc 100%)}
    .social-schedule-head{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;flex-wrap:wrap}
    .social-schedule-title{font-size:18px;line-height:1.15;letter-spacing:-.02em;margin-bottom:6px}
    .social-pill{display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:600;border:1px solid transparent}
    .social-pill.status-scheduled{background:#f5f5f7;color:#1d1d1f;border-color:rgba(0,0,0,.08)}
    .social-pill.status-success{background:#e3f5e9;color:#14833b;border-color:#bde6c9}
    .social-pill.status-error{background:#ffe9e6;color:#d92d20;border-color:#ffd1cd}
    .social-pill.status-draft{background:#fff2d6;color:#995c00;border-color:#ffe1a1}
    .social-pill.status-sending{background:#e5f0ff;color:#0f6bdc;border-color:#bfd6ff}
    .social-pill.status-mixed{background:#f4ecff;color:#7c3aed;border-color:#d8c7ff}
    .social-network-row,.social-meta-row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .social-network{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;background:var(--accent-light);font-size:12px;font-weight:600;border:1px solid var(--border-light)}
    .social-message{padding:16px;border-radius:var(--radius-sm);background:#fff;border:1px solid var(--border-light);line-height:1.6;color:var(--text)}
    .social-error{padding:12px;border-radius:var(--radius-sm);background:#fff5f5;border:1px solid #ffd1cd;color:#b42318;font-size:13px;line-height:1.5}
    .social-actions{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
    .social-channel-list{display:grid;gap:12px}
    .social-channel{display:flex;gap:12px;align-items:center;padding:14px;border-radius:var(--radius-sm);border:1px solid var(--border-light);background:var(--accent-light)}
    .social-avatar{width:42px;height:42px;border-radius:12px;overflow:hidden;background:#fff;border:1px solid var(--border-light);display:grid;place-items:center;font-size:12px;font-weight:700;color:var(--text-secondary)}
    .social-avatar img{width:100%;height:100%;object-fit:cover}
    .social-channel-name{font-size:14px;font-weight:600;color:var(--text)}
    .social-channel-meta{font-size:12px;color:var(--text-secondary)}
    .social-empty{padding:48px 20px;text-align:center;border:1px dashed var(--border);border-radius:var(--radius-md);background:var(--accent-light);color:var(--text-secondary)}
    .fc .fc-toolbar{flex-wrap:wrap;gap:10px}
    .fc .fc-toolbar-title{font-size:18px;font-weight:600;letter-spacing:-.02em}
    .fc .fc-button{background:var(--text)!important;border-color:var(--text)!important;border-radius:999px!important;padding:8px 14px!important;font-size:13px!important}
    .fc .fc-button-primary:not(:disabled).fc-button-active,.fc .fc-button-primary:not(:disabled):active{background:var(--accent-hover)!important;border-color:var(--accent-hover)!important}
    .fc-theme-standard td,.fc-theme-standard th,.fc-theme-standard .fc-scrollgrid{border-color:var(--border-light)}
    .fc .fc-daygrid-day-number,.fc .fc-col-header-cell-cushion{color:var(--text);text-decoration:none}
    .fc .fc-daygrid-event{border-radius:10px;padding:4px 6px}
    @media (max-width:1080px){.social-hero-grid,.social-grid,.social-filter-grid{grid-template-columns:1fr}}
</style>

<div class="social-shell">
    <section class="social-hero">
        <div class="social-hero-grid">
            <div>
                <div class="social-kicker">WordPress Social Sync</div>
                <h3>The Laravel scheduler is now writing into the same FS Poster tables the condo site uses.</h3>
                <p>Every schedule here creates real `fsp_schedules` rows tied to the condo WordPress property post, so the calendar, queue, and post-level metadata stay aligned with the WordPress side.</p>
            </div>
            <div class="social-note">
                Channel-specific defaults still come from FS Poster’s WordPress settings. The agent portal only overrides the schedule time, target listing, selected channels, and your custom social message.
            </div>
        </div>
    </section>

    <section class="social-stats">
        <article class="social-stat">
            <div class="social-stat-label">Active Channels</div>
            <div class="social-stat-value">{{ $stats['channels'] }}</div>
        </article>
        <article class="social-stat">
            <div class="social-stat-label">Queued</div>
            <div class="social-stat-value">{{ $stats['scheduled'] }}</div>
        </article>
        <article class="social-stat">
            <div class="social-stat-label">Sent</div>
            <div class="social-stat-value">{{ $stats['sent'] }}</div>
        </article>
        <article class="social-stat">
            <div class="social-stat-label">Needs Attention</div>
            <div class="social-stat-value">{{ $stats['issues'] }}</div>
        </article>
    </section>

    <section class="social-card">
        <form method="GET" class="social-filter-grid">
            <input type="text" name="search" class="form-input" value="{{ $search }}" placeholder="Search schedules by listing title or social text">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(['scheduled' => 'Scheduled', 'draft' => 'Draft', 'success' => 'Sent', 'error' => 'Failed', 'mixed' => 'Mixed', 'sending' => 'Sending'] as $value => $label)
                    <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="network" class="form-select">
                <option value="">All networks</option>
                @foreach($channels->pluck('social_network')->unique()->sort()->values() as $network)
                    <option value="{{ $network }}" @selected($networkFilter === $network)>{{ strtoupper($network) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('social.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </section>

    <div class="social-grid">
        <section class="social-card">
            <div class="social-card-head">
                <div>
                    <h4>Calendar View</h4>
                    <div class="social-small">Responsive FullCalendar view of the same FS Poster schedule groups shown below.</div>
                </div>
            </div>
            <div class="social-calendar">
                <div id="social-calendar"></div>
            </div>
        </section>

        <aside class="social-card">
            <div class="social-card-head">
                <div>
                    <h4>Connected Channels</h4>
                    <div class="social-small">{{ $channels->count() }} active FS Poster destinations from WordPress.</div>
                </div>
            </div>
            @if($channels->count())
                <div class="social-channel-list">
                    @foreach($channels as $channel)
                        <div class="social-channel">
                            <div class="social-avatar">
                                @if($channel['picture'])
                                    <img src="{{ $channel['picture'] }}" alt="{{ $channel['name'] }}">
                                @else
                                    {{ strtoupper(substr($channel['social_network'], 0, 2)) }}
                                @endif
                            </div>
                            <div>
                                <div class="social-channel-name">{{ $channel['name'] }}</div>
                                <div class="social-channel-meta">{{ strtoupper($channel['social_network']) }} · {{ $channel['session_name'] }} · {{ str_replace('_', ' ', $channel['channel_type']) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="social-empty">No active FS Poster channels were found in WordPress.</div>
            @endif
        </aside>
    </div>

    <section class="social-card">
        <div class="social-card-head">
            <div>
                <h4>Scheduled Posts</h4>
                <div class="social-small">{{ $posts->total() }} matching schedule groups across {{ $listings->count() }} condo listings.</div>
            </div>
        </div>

        @if($posts->count())
            <div class="social-schedule-list">
                @foreach($posts as $schedule)
                    <article class="social-schedule">
                        <div class="social-schedule-head">
                            <div>
                                <div class="social-schedule-title">{{ $schedule['listing_title'] }}</div>
                                <div class="social-small">{{ $schedule['scheduled_at']->format('D, d M Y h:i A') }} · {{ $schedule['total_channels'] }} channel{{ $schedule['total_channels'] === 1 ? '' : 's' }}</div>
                            </div>
                            <span class="social-pill status-{{ $schedule['status'] }}">{{ $schedule['status_label'] }}</span>
                        </div>

                        <div class="social-network-row">
                            @foreach($schedule['social_networks'] as $network)
                                <span class="social-network">{{ strtoupper($network) }}</span>
                            @endforeach
                        </div>

                        <div class="social-message">
                            {{ $schedule['message'] !== '' ? $schedule['message'] : 'This schedule is using the current FS Poster post-content template from WordPress.' }}
                        </div>

                        @if($schedule['error_messages'] !== [])
                            <div class="social-error">
                                {{ implode(' ', $schedule['error_messages']) }}
                            </div>
                        @endif

                        <div class="social-actions">
                            @if($schedule['is_mutable'])
                                <a href="{{ route('social.edit', $schedule['group_id']) }}" class="btn btn-primary btn-sm">Edit</a>
                            @endif
                            <a href="{{ route('listings.show', ['id' => $schedule['listing_id'], 'source' => 'condo', 'return_source' => 'condo']) }}" class="btn btn-secondary btn-sm">View Listing</a>
                            <form method="POST" action="{{ route('social.destroy', $schedule['group_id']) }}" onsubmit="return confirm('Remove this FS Poster schedule group?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="pagination">{{ $posts->links('components.pagination') }}</div>
        @else
            <div class="social-empty">
                <p>No schedules matched the current filters.</p>
                <a href="{{ route('social.create') }}" class="btn btn-primary">Create Schedule</a>
            </div>
        @endif
    </section>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<script>
    (function () {
        var calendarEl = document.getElementById('social-calendar');
        if (!calendarEl || typeof FullCalendar === 'undefined') {
            return;
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: window.innerWidth < 900 ? 'dayGridWeek' : 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            height: 'auto',
            events: @json($calendarEvents),
            eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
            eventDidMount: function (info) {
                var props = info.event.extendedProps || {};
                var lines = [
                    props.status || '',
                    props.networks ? props.networks.join(', ').toUpperCase() : '',
                    props.message || ''
                ].filter(Boolean);
                if (lines.length) {
                    info.el.title = lines.join('\n');
                }
            },
            eventClick: function (info) {
                if (!info.event.url) {
                    info.jsEvent.preventDefault();
                }
            },
            windowResize: function () {
                calendar.changeView(window.innerWidth < 900 ? 'dayGridWeek' : 'dayGridMonth');
            }
        });

        calendar.render();
    }());
</script>
@endsection
