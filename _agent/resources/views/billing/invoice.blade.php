<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $record->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fb;
            color: #111827;
            line-height: 1.5;
        }

        .page {
            max-width: 920px;
            margin: 32px auto;
            padding: 0 20px;
        }

        .toolbar {
            display: {{ $downloadMode ? 'none' : 'flex' }};
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #111827;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary {
            background: #111827;
            border-color: #111827;
            color: #ffffff;
        }

        .invoice {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(17, 24, 39, 0.06);
            overflow: hidden;
        }

        .invoice-head {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
            padding: 28px 32px 18px;
            border-bottom: 1px solid #eef2f7;
        }

        .eyebrow {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .invoice-title {
            font-size: 34px;
            font-weight: 700;
            letter-spacing: -0.04em;
            margin: 0 0 8px;
        }

        .invoice-subtitle {
            font-size: 15px;
            color: #6b7280;
            margin: 0;
        }

        .status {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid #d1d5db;
        }

        .status-completed {
            background: #e8f7ee;
            color: #167c3b;
            border-color: #b9e5c8;
        }

        .status-pending {
            background: #fff4dc;
            color: #9a5b00;
            border-color: #ffe2a3;
        }

        .status-rejected,
        .status-cancelled {
            background: #ffe9e8;
            color: #cc2a1c;
            border-color: #ffcfc9;
        }

        .invoice-body {
            padding: 28px 32px 32px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .meta-card {
            padding: 18px 20px;
            border: 1px solid #eef2f7;
            border-radius: 16px;
            background: #fafbfc;
        }

        .meta-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .meta-value {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .meta-sub {
            margin-top: 6px;
            font-size: 14px;
            color: #6b7280;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 14px 0;
            text-align: left;
            border-bottom: 1px solid #eef2f7;
            vertical-align: top;
        }

        .invoice-table th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
        }

        .invoice-total {
            display: flex;
            justify-content: flex-end;
        }

        .invoice-total-card {
            min-width: 260px;
            padding: 18px 20px;
            border-radius: 16px;
            background: #111827;
            color: #ffffff;
        }

        .invoice-total-card .meta-label,
        .invoice-total-card .meta-sub {
            color: rgba(255, 255, 255, 0.78);
        }

        .invoice-total-value {
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.04em;
        }

        .invoice-note {
            margin-top: 20px;
            font-size: 14px;
            color: #6b7280;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .page {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .invoice {
                border: none;
                box-shadow: none;
                border-radius: 0;
            }
        }

        @media (max-width: 768px) {
            .meta-grid {
                grid-template-columns: 1fr;
            }

            .invoice-head,
            .invoice-body {
                padding: 22px;
            }

            .invoice-title {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
@php($statusClass = strtolower($record->status_label))
<div class="page">
    <div class="toolbar">
        <a href="{{ route('billing.index') }}" class="btn">Back</a>
        <a href="{{ route('billing.history.invoice.export', $record->id) }}" class="btn">Export</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
    </div>

    <section class="invoice">
        <header class="invoice-head">
            <div>
                <div class="eyebrow">Billing Invoice</div>
                <h1 class="invoice-title">{{ $record->invoice_number }}</h1>
                <p class="invoice-subtitle">{{ $record->formatted_created_date ?? 'Date unavailable' }}</p>
            </div>
            <div>
                <span class="status status-{{ $statusClass }}">{{ $record->status_label }}</span>
            </div>
        </header>

        <div class="invoice-body">
            <div class="meta-grid">
                <div class="meta-card">
                    <div class="meta-label">Account</div>
                    <div class="meta-value">{{ $record->target ?: $agent->username }}</div>
                    <div class="meta-sub">Requested by {{ $record->requested_by_label }}</div>
                </div>
                <div class="meta-card">
                    <div class="meta-label">Processed By</div>
                    <div class="meta-value">{{ $record->processed_by_label }}</div>
                    <div class="meta-sub">{{ $record->formatted_process_date ?? 'Pending processing date' }}</div>
                </div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>Upgrade Count</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $record->current_package_name }}</td>
                        <td>{{ $record->upgrade_package_name }}</td>
                        <td>{{ (int) ($record->upgradecount ?? 1) }}</td>
                        <td>{{ $record->amount_formatted }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="invoice-total">
                <div class="invoice-total-card">
                    <div class="meta-label">Total</div>
                    <div class="invoice-total-value">{{ $record->amount_formatted }}</div>
                    <div class="meta-sub">Charged for this upgrade record.</div>
                </div>
            </div>

            @if(trim((string) $record->reason) !== '')
                <div class="invoice-note">
                    <strong>Note:</strong> {{ trim((string) $record->reason) }}
                </div>
            @endif
        </div>
    </section>
</div>
</body>
</html>
