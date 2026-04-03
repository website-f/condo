@extends('layouts.app')
@section('title', 'Billing')
@section('page-title', 'Billing & Subscription')

@section('content')
<!-- Current Plan -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">Current Subscription</div>
    <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
        <div>
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-secondary);font-weight:500;">Plan</div>
            <div style="font-size:24px;font-weight:600;letter-spacing:-0.5px;">{{ $agent->subscription?->name ?? 'No Plan' }}</div>
        </div>
        <div>
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-secondary);font-weight:500;">Monthly Cost</div>
            <div style="font-size:24px;font-weight:600;letter-spacing:-0.5px;">{{ $agent->subscription?->formatted_cost ?? 'N/A' }}</div>
        </div>
        <div>
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-secondary);font-weight:500;">Credit Limit</div>
            <div style="font-size:24px;font-weight:600;letter-spacing:-0.5px;">{{ $agent->subscription?->creditlimit ?? 'N/A' }}</div>
        </div>
        <div>
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-secondary);font-weight:500;">Expires</div>
            <div style="font-size:24px;font-weight:600;letter-spacing:-0.5px;">{{ $agent->enddate ?? 'N/A' }}</div>
        </div>
        <div>
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-secondary);font-weight:500;">Status</div>
            <div style="margin-top:4px;">
                @if($agent->activated)
                    <span class="badge badge-success" style="font-size:13px;padding:5px 12px;">Active</span>
                @else
                    <span class="badge badge-danger" style="font-size:13px;padding:5px 12px;">Inactive</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Available Plans -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">Available Plans</div>
    <div class="grid-4">
        @foreach($packages as $pkg)
        <div style="padding:20px;border:1px solid {{ $agent->package == $pkg->id ? 'var(--accent)' : 'var(--border)' }};border-radius:12px;{{ $agent->package == $pkg->id ? 'background:var(--accent-light);' : '' }}">
            @if($agent->package == $pkg->id)
                <span class="badge badge-success" style="margin-bottom:8px;">Current Plan</span>
            @endif
            <div style="font-size:16px;font-weight:600;margin-bottom:4px;">{{ $pkg->name }}</div>
            <div style="font-size:24px;font-weight:600;letter-spacing:-0.5px;margin-bottom:12px;">{{ $pkg->formatted_cost }}<span style="font-size:13px;font-weight:400;color:var(--text-secondary);">/mo</span></div>
            <div style="font-size:13px;line-height:2;color:var(--text-secondary);">
                <div>Credit Limit: <strong style="color:var(--text);">{{ $pkg->creditlimit }}</strong></div>
                <div>Max Accounts: <strong style="color:var(--text);">{{ $pkg->maxaccount }}</strong></div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Upgrade History -->
<div class="card">
    <div class="card-header">Upgrade History</div>
    @if($upgradeHistory->count())
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Date</th><th>From</th><th>To</th><th>Status</th><th>Processed By</th></tr>
            </thead>
            <tbody>
            @foreach($upgradeHistory as $record)
                <tr>
                    <td style="color:var(--text-secondary);">{{ $record->createddate }}</td>
                    <td>{{ $record->currentpackage ?? '—' }}</td>
                    <td style="font-weight:500;">{{ $record->upgradepackage ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $record->status == 1 ? 'badge-success' : 'badge-warning' }}">
                            {{ $record->status == 1 ? 'Completed' : 'Pending' }}
                        </span>
                    </td>
                    <td style="color:var(--text-secondary);">{{ $record->processby ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state" style="padding:30px;"><p>No upgrade history</p></div>
    @endif
</div>
@endsection
