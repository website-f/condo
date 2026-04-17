@extends('layouts.app')
@section('title', 'Billing')
@section('page-title', 'Billing & Subscription')

@php($hiddenPlanCount = ($billingAudit['hidden_internal_packages'] ?? 0) + ($billingAudit['hidden_zero_cost_packages'] ?? 0))
@php($featureGate = session('package_feature_gate'))

@section('topbar-actions')
    @if($upgradeHistory->isNotEmpty())
        <a href="{{ route('billing.history.export') }}" class="btn btn-secondary">Export History</a>
    @endif
@endsection

@section('head')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <style>
        .billing-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
        }

        .billing-summary-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 6px;
        }

        .billing-summary-value {
            font-size: 26px;
            font-weight: 600;
            letter-spacing: -0.03em;
            color: var(--text);
        }

        .billing-summary-sub {
            margin-top: 6px;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .billing-verified {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .billing-chip {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            background: #f7f8fa;
            border: 1px solid var(--border-light);
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 600;
        }

        .billing-section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .billing-section-head .card-header {
            margin-bottom: 0;
        }

        .billing-plan-card {
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fff;
        }

        .billing-plan-card.is-current {
            border-color: var(--accent);
            background: var(--accent-light);
        }

        .billing-plan-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 10px;
        }

        .billing-plan-price {
            font-size: 36px;
            font-weight: 600;
            letter-spacing: -0.04em;
            margin-bottom: 14px;
            color: var(--text);
        }

        .billing-plan-price span {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .billing-plan-meta {
            display: grid;
            gap: 10px;
        }

        .billing-plan-meta div {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .billing-plan-meta strong {
            color: var(--text);
        }

        .billing-plan-meta-note {
            margin-top: 4px;
            font-size: 12px;
            line-height: 1.5;
            color: var(--text-secondary);
        }

        .billing-package-tag {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            background: #f5ecff;
            border: 1px solid #e1d0ff;
            color: #5f3aa5;
            font-size: 11px;
            font-weight: 700;
        }

        .billing-condo-access {
            padding: 20px;
            border-radius: 16px;
            border: 1px solid var(--border-light);
            background: #f7f9fc;
        }

        .billing-condo-access.is-active {
            background: #f2f7ff;
            border-color: rgba(0, 102, 204, 0.14);
        }

        .billing-condo-access.is-locked {
            background: #fffaf1;
            border-color: #ffe1a1;
        }

        .billing-condo-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
        }

        .billing-condo-copy {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 16px;
        }

        .billing-condo-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .billing-condo-stat {
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(255,255,255,0.84);
            border: 1px solid var(--border-light);
        }

        .billing-condo-stat strong {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .billing-condo-stat span {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
        }

        .billing-history-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .billing-history-actions .btn {
            min-width: 86px;
        }

        .billing-gate-list {
            display: grid;
            gap: 10px;
        }

        .billing-gate-item {
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid var(--border-light);
            color: var(--text-secondary);
            font-size: 13px;
            line-height: 1.6;
        }

        .billing-invoice {
            font-weight: 600;
            color: var(--text);
        }

        .billing-muted {
            color: var(--text-secondary);
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 14px;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px 12px;
            background: #fff;
            color: var(--text);
            font-family: var(--font);
            box-shadow: var(--shadow-sm);
        }

        .dataTables_wrapper .dataTables_filter input {
            min-width: 220px;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 16px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 999px !important;
            border: 1px solid transparent !important;
            min-width: 38px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--accent) !important;
            color: #fff !important;
            border-color: var(--accent) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--accent-light) !important;
            color: var(--text) !important;
            border-color: var(--border-light) !important;
        }

        .dt-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .dt-button,
        div.dt-button,
        button.dt-button {
            border-radius: 999px !important;
            border: 1px solid var(--border) !important;
            background: #fff !important;
            color: var(--text) !important;
            padding: 8px 14px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            box-shadow: var(--shadow-sm) !important;
        }

        .dt-button:hover,
        div.dt-button:hover,
        button.dt-button:hover {
            background: var(--accent-light) !important;
            border-color: var(--border-light) !important;
        }

        @media (max-width: 768px) {
            .billing-summary-value {
                font-size: 22px;
            }

            .billing-plan-price {
                font-size: 30px;
            }

            .dataTables_wrapper .dataTables_filter input {
                min-width: 160px;
            }
        }
    </style>
@endsection

@section('content')
    @if(is_array($featureGate))
        <div class="card" style="margin-bottom:24px;background:#fffaf1;border-color:#ffe1a1;">
            <div class="billing-section-head">
                <div class="card-header">{{ $featureGate['feature_title'] ?? 'Feature Locked' }}</div>
                <span class="billing-package-tag">Package Required</span>
            </div>

            <div class="billing-condo-copy" style="margin-bottom:16px;">
                {{ $featureGate['summary'] ?? 'Upgrade this account to continue.' }}
            </div>

            @if(! empty($featureGate['bullets']))
                <div class="billing-gate-list">
                    @foreach($featureGate['bullets'] as $bullet)
                        <div class="billing-gate-item">{{ $bullet }}</div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="card" style="margin-bottom:24px;">
        <div class="card-header">Current Subscription</div>
        <div class="billing-summary">
            <div>
                <div class="billing-summary-label">Plan</div>
                <div class="billing-summary-value">{{ $agent->subscription?->display_name ?? 'No Plan' }}</div>
            </div>
            <div>
                <div class="billing-summary-label">Monthly</div>
                <div class="billing-summary-value">{{ $agent->subscription?->formatted_cost ?? 'RM 0.00' }}</div>
            </div>
            <div>
                <div class="billing-summary-label">{{ ($condoPackageSummary['enabled'] ?? false) ? 'Daily Credit Left' : 'Credit' }}</div>
                <div class="billing-summary-value">
                    {{ ($condoPackageSummary['enabled'] ?? false) ? number_format($condoPackageSummary['daily_remaining']) : number_format($agent->subscription?->creditlimit ?? 0) }}
                </div>
                @if($condoPackageSummary['enabled'] ?? false)
                    <div class="billing-summary-sub">of {{ number_format($condoPackageSummary['daily_limit']) }} daily</div>
                @endif
            </div>
            @if($condoPackageSummary['enabled'] ?? false)
                <div>
                    <div class="billing-summary-label">Listing Space Left</div>
                    <div class="billing-summary-value">{{ number_format($condoPackageSummary['listing_remaining']) }}</div>
                    <div class="billing-summary-sub">{{ number_format($condoPackageSummary['listing_used']) }} used of {{ number_format($condoPackageSummary['listing_limit']) }}</div>
                </div>
            @endif
            <div>
                <div class="billing-summary-label">Expires</div>
                <div class="billing-summary-value">{{ $agent->formatted_end_date ?? 'Not set' }}</div>
            </div>
            <div>
                <div class="billing-summary-label">Status</div>
                <div style="margin-top:6px;">
                    <span class="badge {{ $agent->activated ? 'badge-success' : 'badge-danger' }}" style="font-size:13px;padding:6px 12px;">
                        {{ $agent->activated ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="billing-summary-sub">{{ $upgradeHistory->count() }} history record{{ $upgradeHistory->count() === 1 ? '' : 's' }}</div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:24px;">
        <div class="billing-section-head">
            <div class="card-header">Condo Content Access</div>
            <span class="billing-muted">{{ ($condoPackageSummary['enabled'] ?? false) ? 'Unlocked on this subscription.' : 'Locked until a condo package is active.' }}</span>
        </div>

        <div class="billing-condo-access {{ ($condoPackageSummary['enabled'] ?? false) ? 'is-active' : 'is-locked' }}">
            @if($condoPackageSummary['enabled'] ?? false)
                <div class="billing-condo-title">{{ $condoPackageSummary['package_name'] }}</div>
                <div class="billing-condo-copy">
                    Condo listings, Articles, and Social Media are unlocked. Social schedules use the daily credit pool.
                    @if($condoPackageSummary['article_submission_uses_credit'])
                        Publishing or scheduling an article also uses daily credit on this package.
                    @else
                        Article drafts, publishing, and scheduling stay unlimited on this package.
                    @endif
                </div>
                <div class="billing-condo-stats">
                    <div class="billing-condo-stat">
                        <strong>Listing Used</strong>
                        <span>{{ number_format($condoPackageSummary['listing_used']) }} / {{ number_format($condoPackageSummary['listing_limit']) }}</span>
                    </div>
                    <div class="billing-condo-stat">
                        <strong>Listing Remaining</strong>
                        <span>{{ number_format($condoPackageSummary['listing_remaining']) }}</span>
                    </div>
                    <div class="billing-condo-stat">
                        <strong>Daily Credit Left</strong>
                        <span>{{ number_format($condoPackageSummary['daily_remaining']) }} / {{ number_format($condoPackageSummary['daily_limit']) }}</span>
                    </div>
                </div>
            @else
                <div class="billing-condo-title">Condo tools are not active on this account.</div>
                <div class="billing-condo-copy">
                    Subscribe to Condo Premium Package or Condo Premium Lite Package to unlock condo listings, article posting, and social scheduling from Laravel.
                </div>
            @endif
        </div>
    </div>

    <div class="card" style="margin-bottom:24px;">
        <div class="billing-verified">
            <span class="billing-chip">Verified account-only history</span>
            <span class="billing-chip">Legacy dates converted</span>
            @if($hiddenPlanCount > 0)
                <span class="billing-chip">{{ $hiddenPlanCount }} internal / zero-cost plan{{ $hiddenPlanCount === 1 ? '' : 's' }} hidden</span>
            @endif
        </div>
    </div>

    <div class="card" style="margin-bottom:24px;">
        <div class="billing-section-head">
            <div class="card-header">Available Plans</div>
            <span class="billing-muted">Only active, billable, and unique plans are shown.</span>
        </div>

        @if($packages->isNotEmpty())
            <div class="grid-4">
                @foreach($packages as $pkg)
                    <div class="billing-plan-card {{ (int) $agent->package === (int) $pkg->id ? 'is-current' : '' }}">
                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
                            @if((int) $agent->package === (int) $pkg->id)
                                <span class="badge badge-success">Current Plan</span>
                            @endif
                            @if($pkg->is_condo_package)
                                <span class="billing-package-tag">Condo Access</span>
                            @endif
                        </div>

                        <div class="billing-plan-name">{{ $pkg->display_name }}</div>
                        <div class="billing-plan-price">
                            {{ $pkg->formatted_cost }}<span>/mo</span>
                        </div>
                        <div class="billing-plan-meta">
                            @foreach($pkg->billing_feature_rows as $row)
                                <div>
                                    {{ $row['label'] }}: <strong>{{ $row['value'] }}</strong>
                                    @if(!empty($row['note']))
                                        <div class="billing-plan-meta-note">{{ $row['note'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" style="padding:30px;">
                <p>No plans available.</p>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="billing-section-head">
            <div class="card-header">Upgrade History</div>
            @if($upgradeHistory->isNotEmpty())
                <a href="{{ route('billing.history.export') }}" class="btn btn-secondary btn-sm">CSV</a>
            @endif
        </div>

        @if($upgradeHistory->isNotEmpty())
            <div class="table-wrap">
                <table id="billing-history-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Processed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upgradeHistory as $record)
                            <tr>
                                <td data-order="{{ $record->created_timestamp }}">
                                    <span>{{ $record->formatted_created_date ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="billing-invoice">{{ $record->invoice_number }}</span>
                                </td>
                                <td>{{ $record->current_package_name }}</td>
                                <td>{{ $record->upgrade_package_name }}</td>
                                <td>{{ $record->amount_formatted }}</td>
                                <td>
                                    <span class="badge {{ $record->status_badge_class }}">
                                        {{ $record->status_label }}
                                    </span>
                                </td>
                                <td class="billing-muted">{{ $record->processed_by_label }}</td>
                                <td>
                                    <div class="billing-history-actions">
                                        <a href="{{ route('billing.history.invoice', $record->id) }}" class="btn btn-secondary btn-sm" target="_blank" rel="noopener">Invoice</a>
                                        <a href="{{ route('billing.history.invoice.export', $record->id) }}" class="btn btn-secondary btn-sm">Export</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state" style="padding:30px;">
                <p>No upgrade history.</p>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.jQuery || !jQuery.fn.DataTable) {
                return;
            }

            var table = document.getElementById('billing-history-table');

            if (!table) {
                return;
            }

            jQuery(table).DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                order: [[0, 'desc']],
                autoWidth: false,
                columnDefs: [
                    { targets: 7, orderable: false, searchable: false }
                ],
                dom: "<'billing-table-tools'Blf>rt<'billing-table-footer'ip>",
                buttons: [
                    {
                        extend: 'csvHtml5',
                        text: 'CSV',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Excel',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    }
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search history',
                    lengthMenu: '_MENU_ per page',
                    info: 'Showing _START_ to _END_ of _TOTAL_',
                    paginate: {
                        previous: 'Prev',
                        next: 'Next'
                    }
                }
            });
        });
    </script>
@endsection
