@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)
@section('topbar-actions')
    <div class="btn-group">
        <a href="{{ route('social.channels.index') }}" class="btn btn-secondary btn-sm">Manage Channels</a>
        @if($existingGroup)
            <a href="{{ route('social.index') }}" class="btn btn-secondary btn-sm">Back</a>
        @else
            <a href="{{ route('social.index') }}" class="btn btn-secondary btn-sm">All Schedules</a>
        @endif
    </div>
@endsection

@section('content')
<style>
    .social-form-shell{display:grid;gap:24px}
    .social-form-hero,.social-form-card{background:var(--card-bg);border:1px solid var(--border-light);border-radius:var(--radius-md);box-shadow:var(--shadow-sm)}
    .social-form-hero,.social-form-card{padding:24px}
    .social-form-grid{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(320px,.9fr);gap:24px;align-items:start}
    .social-form-main,.social-form-side{display:grid;gap:24px}
    .social-kicker{font-size:12px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px}
    .social-form-hero h3{font-size:clamp(28px,3vw,38px);line-height:1.08;letter-spacing:-.03em;margin-bottom:12px}
    .social-form-hero p,.social-help,.social-small{color:var(--text-secondary);line-height:1.6}
    .social-form-title{font-size:18px;line-height:1.15;letter-spacing:-.02em;margin-bottom:14px}
    .social-listings{display:grid;gap:12px}
    .social-listing{display:flex;gap:14px;align-items:center;padding:16px;border:1px solid var(--border-light);border-radius:var(--radius-sm);background:linear-gradient(180deg,#fff 0%,#fafafc 100%);cursor:pointer}
    .social-listing input{margin-top:0;accent-color:var(--text)}
    .social-listing-thumb{width:72px;height:72px;border-radius:14px;overflow:hidden;background:var(--accent-light);border:1px solid var(--border-light);display:grid;place-items:center;color:var(--text-secondary);font-size:12px;font-weight:700;flex-shrink:0}
    .social-listing-thumb img{width:100%;height:100%;object-fit:cover}
    .social-listing-title{font-size:15px;font-weight:600;color:var(--text);margin-bottom:4px}
    .social-listing-meta{font-size:13px;color:var(--text-secondary)}
    .social-channel-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
    .social-channel{display:flex;gap:12px;align-items:flex-start;padding:16px;border:1px solid var(--border-light);border-radius:var(--radius-sm);background:var(--accent-light)}
    .social-channel input{margin-top:2px;accent-color:var(--text)}
    .social-channel-name{font-size:14px;font-weight:600;color:var(--text);margin-bottom:4px}
    .social-channel-meta{font-size:12px;color:var(--text-secondary);line-height:1.5}
    .social-sidebar-box{padding:18px;border:1px solid var(--border-light);border-radius:var(--radius-sm);background:var(--accent-light)}
    .social-sidebar-box h4{font-size:16px;line-height:1.15;margin-bottom:12px}
    .social-sidebar-row{display:flex;justify-content:space-between;gap:12px;font-size:13px;margin-bottom:10px}
    .social-sidebar-row:last-child{margin-bottom:0}
    .social-sidebar-row span{color:var(--text-secondary)}
    .social-sidebar-row strong{color:var(--text);text-align:right}
    .social-actions{display:flex;gap:12px;flex-wrap:wrap}
    .social-inline-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;background:#eef2ff;color:#3730a3;font-size:11px;font-weight:700;letter-spacing:.02em;text-transform:uppercase;margin-top:8px}
    .social-callout{padding:14px 16px;border:1px solid #d6dae8;border-radius:16px;background:#f6f7fb;color:var(--text-secondary);font-size:13px;line-height:1.6}
    @media (max-width:980px){.social-form-grid,.social-channel-grid{grid-template-columns:1fr}}
</style>

<div class="social-form-shell">
    <section class="social-form-hero">
        <div class="social-kicker">Social Composer</div>
        <h3>{{ $existingGroup ? 'Update the queued social schedule without leaving the agent portal.' : 'Queue a condo listing into the live social scheduler from Laravel.' }}</h3>
        <p>{{ $existingGroup ? 'This editor updates the same live schedule group used by the portal, so your listing and social queue stay aligned.' : 'Choose a condo listing, select the connected channels you want, and the portal will create or update the live schedule group for that property.' }}</p>
    </section>

    <form method="POST" action="{{ $formAction }}">
        @csrf
        @if($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <div class="social-form-grid">
            <div class="social-form-main">
                <section class="social-form-card">
                    <div class="social-form-title">Choose Condo Listing</div>
                    <div class="social-listings">
                        @foreach($listings as $listing)
                            <label class="social-listing">
                                <input type="radio" name="listing_id" value="{{ $listing['id'] }}" @checked((int) $schedule['listing_id'] === (int) $listing['id'])>
                                <div class="social-listing-thumb">
                                    @if($listing['image_url'])
                                        <img src="{{ $listing['image_url'] }}" alt="{{ $listing['title'] }}">
                                    @else
                                        CONDO
                                    @endif
                                </div>
                                <div>
                                    <div class="social-listing-title">{{ $listing['title'] }}</div>
                                    <div class="social-listing-meta">{{ $listing['formatted_price'] }}</div>
                                    @if(!empty($listing['has_active_schedule']))
                                        <div class="social-inline-badge">Live WP schedule linked</div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="social-form-card">
                    <div class="social-form-title">Pick Channels</div>
                    <div class="social-help" style="margin-bottom:16px;">Each selected channel keeps its current saved configuration. Laravel updates the schedule time and only replaces the message when you explicitly type one below.</div>
                    <div class="social-callout" style="margin-bottom:16px;">
                        These checkboxes come from the live FS Poster channel records. If you need to add another social target or edit the connected channel setup, use <a href="{{ route('social.channels.index') }}" style="font-weight:700;color:inherit;">Manage Channels</a> and the same data will show up here automatically.
                    </div>
                    <div class="social-channel-grid">
                        @foreach($channels as $channel)
                            <label class="social-channel">
                                <input type="checkbox" name="channel_ids[]" value="{{ $channel['id'] }}" @checked(in_array($channel['id'], array_map('intval', (array) $schedule['channel_ids']), true))>
                                <div>
                                    <div class="social-channel-name">{{ $channel['name'] }}</div>
                                    <div class="social-channel-meta">{{ strtoupper($channel['social_network']) }} - {{ $channel['session_name'] }} - {{ str_replace('_', ' ', $channel['channel_type']) }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @if($channels->isEmpty())
                        <div class="social-callout" style="margin-top:16px;">
                            No active channels are available for this agent yet. Add an account or channel first, then come back to queue schedules.
                        </div>
                    @endif
                </section>

                <section class="social-form-card">
                    <div class="social-form-title">Schedule Details</div>
                    @if($existingGroup && $existingGroup['has_mixed_messages'])
                        <div class="social-callout" style="margin-bottom:18px;">
                            This schedule group currently has channel-specific copy. Leave the message box empty to preserve those per-channel settings. If you type a new message here, Laravel will replace the post text across all selected channels.
                        </div>
                    @elseif($existingGroup)
                        <div class="social-callout" style="margin-bottom:18px;">
                            This form edits the same live schedule group. Leaving the message blank keeps the current copy intact.
                        </div>
                    @endif
                    <div class="form-group">
                        <label class="form-label" for="scheduled_at">Post Date & Time</label>
                        <input id="scheduled_at" name="scheduled_at" type="datetime-local" class="form-input" value="{{ $schedule['scheduled_at_form'] }}" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="message">Custom Social Message</label>
                        <textarea id="message" name="message" rows="8" class="form-textarea" maxlength="4000" placeholder="Write the post text you want the scheduler to use. Leave blank to keep the current saved content.">{{ $schedule['message'] }}</textarea>
                        <div class="form-hint">
                            @if($existingGroup && $existingGroup['has_mixed_messages'])
                                Leave this empty to preserve the current channel-specific copy. Enter text only if you want to replace it on every selected channel.
                            @elseif($existingGroup)
                                Leave this empty to keep the current copy for this schedule group.
                            @else
                                Leave this empty if you want the scheduler to use the current saved template for each selected channel.
                            @endif
                        </div>
                    </div>
                </section>
            </div>

            <aside class="social-form-side">
                <section class="social-form-card">
                    <div class="social-form-title">Queue Summary</div>
                    <div class="social-sidebar-box">
                        <h4>What this saves</h4>
                        <div class="social-sidebar-row"><span>Source of truth</span><strong>Agent social queue</strong></div>
                        <div class="social-sidebar-row"><span>Linked post type</span><strong>`properties`</strong></div>
                        <div class="social-sidebar-row"><span>Selected channels</span><strong>{{ count((array) $schedule['channel_ids']) }}</strong></div>
                    </div>

                    @if($existingGroup)
                        <div class="social-sidebar-box" style="margin-top:16px;">
                            <h4>Existing Group</h4>
                            <div class="social-sidebar-row"><span>Group ID</span><strong>{{ $existingGroup['group_id'] }}</strong></div>
                            <div class="social-sidebar-row"><span>Status</span><strong>{{ $existingGroup['status_label'] }}</strong></div>
                            <div class="social-sidebar-row"><span>Queued for</span><strong>{{ $existingGroup['scheduled_at']->format('d M Y h:i A') }}</strong></div>
                            <div class="social-sidebar-row"><span>Linked group</span><strong>{{ $existingGroup['is_primary_group'] ? 'Primary group' : 'Secondary group' }}</strong></div>
                        </div>
                    @endif

                    <div class="social-actions" style="margin-top:20px;">
                        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
                        <a href="{{ route('social.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>
</div>
@endsection
