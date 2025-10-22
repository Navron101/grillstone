<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Settlement Report #{{ $settlement->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
        }

        .container {
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #c2410c;
        }

        .header h1 {
            font-size: 24pt;
            color: #c2410c;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 16pt;
            color: #666;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10pt;
            color: #888;
        }

        .alert-box {
            padding: 15px;
            margin-bottom: 20px;
            border: 2px solid;
            border-radius: 8px;
            font-weight: bold;
        }

        .alert-success {
            background-color: #dcfce7;
            border-color: #22c55e;
            color: #166534;
        }

        .alert-warning {
            background-color: #fef3c7;
            border-color: #eab308;
            color: #854d0e;
        }

        .alert-danger {
            background-color: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #c2410c;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #fed7aa;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th {
            background-color: #fed7aa;
            color: #7c2d12;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #fdba74;
        }

        table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }

        table tr:nth-child(even) {
            background-color: #fef3c7;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .total-row {
            background-color: #fed7aa !important;
            font-weight: bold;
            font-size: 11pt;
        }

        .variance-row {
            background-color: #c2410c !important;
            color: white;
            font-weight: bold;
            font-size: 12pt;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            width: 40%;
            padding: 5px 0;
            color: #666;
        }

        .info-value {
            display: table-cell;
            width: 60%;
            padding: 5px 0;
            font-weight: bold;
        }

        .summary-boxes {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-box {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 8px;
        }

        .summary-box + .summary-box {
            border-left: none;
        }

        .summary-label {
            font-size: 9pt;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 14pt;
            font-weight: bold;
            color: #c2410c;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            color: #888;
            font-size: 9pt;
        }

        .page-break {
            page-break-before: always;
        }

        .text-green {
            color: #16a34a;
        }

        .text-red {
            color: #dc2626;
        }

        .notes-box {
            background-color: #fef3c7;
            border: 2px solid #eab308;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Grillstone POS</h1>
            <h2>Till Settlement Report</h2>
            <p>Settlement #{{ $settlement->id }} | {{ date('F j, Y g:i A', strtotime($settlement->settlement_date)) }}</p>
        </div>

        <!-- Variance Alert -->
        @if($settlement->cash_variance_cents != 0)
            <div class="alert-box {{ $settlement->cash_variance_cents > 0 ? 'alert-success' : 'alert-danger' }}">
                <div style="font-size: 14pt; margin-bottom: 5px;">
                    {{ $settlement->cash_variance_cents > 0 ? '✓ CASH OVER' : '⚠ CASH SHORT' }}
                </div>
                <div style="font-size: 12pt;">
                    Variance: {{ $settlement->cash_variance_cents > 0 ? '+' : '' }}JMD {{ number_format($settlement->cash_variance_cents / 100, 2) }}
                </div>
            </div>
        @else
            <div class="alert-box alert-success">
                <div style="font-size: 14pt; margin-bottom: 5px;">✓ PERFECT MATCH!</div>
                <div style="font-size: 11pt;">Cash count matches expected amount exactly</div>
            </div>
        @endif

        <!-- Period Information -->
        <div class="section">
            <div class="section-title">Settlement Period</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Period Start:</div>
                    <div class="info-value">{{ date('F j, Y g:i A', strtotime($settlement->period_start)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Period End:</div>
                    <div class="info-value">{{ date('F j, Y g:i A', strtotime($settlement->period_end)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Cashier:</div>
                    <div class="info-value">{{ $settlement->user_name ?? 'Unknown' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value" style="text-transform: uppercase;">{{ $settlement->status }}</div>
                </div>
            </div>
        </div>

        <!-- Cash Breakdown -->
        <div class="section">
            <div class="section-title">Cash Breakdown</div>
            <table>
                <tbody>
                    <tr>
                        <td>Expected Cash Sales:</td>
                        <td class="text-right">JMD {{ number_format($settlement->expected_cash_cents / 100, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Paid Out (Payouts):</td>
                        <td class="text-right text-red">-JMD {{ number_format($settlement->paid_out_cents / 100, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Paid In:</td>
                        <td class="text-right text-green">+JMD {{ number_format($settlement->paid_in_cents / 100, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Net Cash Expected:</td>
                        <td class="text-right">JMD {{ number_format($settlement->net_cash_cents / 100, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Actual Cash Counted:</td>
                        <td class="text-right">JMD {{ number_format($settlement->actual_cash_cents / 100, 2) }}</td>
                    </tr>
                    <tr class="variance-row">
                        <td>VARIANCE:</td>
                        <td class="text-right">{{ $settlement->cash_variance_cents > 0 ? '+' : '' }}JMD {{ number_format($settlement->cash_variance_cents / 100, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Payment Methods Summary -->
        <div class="section">
            <div class="section-title">Payment Methods Summary</div>
            <table>
                <tbody>
                    <tr>
                        <td>Cash:</td>
                        <td class="text-right font-bold">JMD {{ number_format($settlement->net_cash_cents / 100, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Credit/Debit Cards:</td>
                        <td class="text-right font-bold">JMD {{ number_format($settlement->expected_card_cents / 100, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Gift Cards:</td>
                        <td class="text-right font-bold">JMD {{ number_format(($settlement->expected_gift_card_cents ?? 0) / 100, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL SALES:</td>
                        <td class="text-right">JMD {{ number_format($settlement->total_sales_cents / 100, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Sales Summary -->
        <div class="section">
            <div class="section-title">Sales Summary</div>
            <table>
                <tbody>
                    <tr>
                        <td>Total Sales:</td>
                        <td class="text-right">JMD {{ number_format($settlement->total_sales_cents / 100, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Number of Transactions:</td>
                        <td class="text-right">{{ $settlement->num_transactions }}</td>
                    </tr>
                    <tr>
                        <td>Average Transaction:</td>
                        <td class="text-right">JMD {{ $settlement->num_transactions > 0 ? number_format($settlement->total_sales_cents / $settlement->num_transactions / 100, 2) : '0.00' }}</td>
                    </tr>
                    <tr>
                        <td>Cost of Goods Sold:</td>
                        <td class="text-right">JMD {{ number_format(($settlement->cogs_cents ?? 0) / 100, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>GROSS PROFIT:</td>
                        <td class="text-right text-green">JMD {{ number_format($settlement->profit_cents / 100, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Credit Card Transactions (if any) -->
        @if($card_transactions && count($card_transactions) > 0)
            <div class="section page-break">
                <div class="section-title">Credit Card Transactions ({{ count($card_transactions) }})</div>
                <p style="margin-bottom: 10px; font-size: 9pt; color: #666;">For bank deposit reconciliation</p>
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Order #</th>
                            <th>Reference</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($card_transactions as $txn)
                            <tr>
                                <td>{{ date('g:i A', strtotime($txn->created_at)) }}</td>
                                <td>#{{ $txn->order_id }}</td>
                                <td>{{ $txn->reference ?? '-' }}</td>
                                <td class="text-right">JMD {{ number_format($txn->amount_cents / 100, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3">TOTAL</td>
                            <td class="text-right">JMD {{ number_format($settlement->expected_card_cents / 100, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- Notes -->
        @if($settlement->notes)
            <div class="notes-box">
                <div class="font-bold" style="margin-bottom: 8px; color: #854d0e;">Notes:</div>
                <div>{{ $settlement->notes }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Powered by Grillstone POS</strong></p>
            <p>Generated: {{ date('F j, Y g:i A') }}</p>
            <p style="margin-top: 10px;">This is a computer-generated document. No signature required.</p>
        </div>
    </div>
</body>
</html>
