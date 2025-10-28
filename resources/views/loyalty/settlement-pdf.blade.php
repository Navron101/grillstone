<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Settlement Report - {{ $settlement->period }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #7c3aed;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #7c3aed;
            font-size: 24px;
        }
        .header h2 {
            margin: 5px 0;
            color: #666;
            font-size: 16px;
            font-weight: normal;
        }
        .company-info {
            background: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .company-info h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #7c3aed;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .summary-box {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .summary-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #92400e;
        }
        .summary-amount {
            font-size: 32px;
            font-weight: bold;
            color: #b45309;
        }
        .employee-section {
            margin-bottom: 25px;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            overflow: hidden;
        }
        .employee-header {
            background: #ede9fe;
            padding: 10px 15px;
            font-weight: bold;
        }
        .employee-name {
            font-size: 14px;
            color: #5b21b6;
        }
        .employee-id {
            font-size: 11px;
            color: #7c3aed;
            margin-left: 10px;
        }
        .employee-total {
            float: right;
            font-size: 14px;
            color: #7c3aed;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 2px solid #d1d5db;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-draft {
            background: #e5e7eb;
            color: #4b5563;
        }
        .status-finalized {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-sent {
            background: #fef3c7;
            color: #92400e;
        }
        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Grillstone Loyalty Program</h1>
        <h2>Settlement Report</h2>
    </div>

    <div class="company-info">
        <h3>{{ $settlement->company->name }}</h3>
        <div class="info-row">
            <span class="info-label">Contact:</span>
            <span>{{ $settlement->company->contact_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span>{{ $settlement->company->contact_email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span>{{ $settlement->company->contact_phone }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Discount Rate:</span>
            <span>{{ $settlement->company->discount_percentage }}%</span>
        </div>
        <div class="info-row">
            <span class="info-label">Period:</span>
            <span>{{ \Carbon\Carbon::parse($settlement->period_start)->format('F Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="status-badge status-{{ $settlement->status }}">{{ strtoupper($settlement->status) }}</span>
        </div>
    </div>

    <div class="summary-box">
        <h3>Total Amount Due</h3>
        <div class="summary-amount">${{ number_format($settlement->total_amount, 2) }}</div>
        <div style="margin-top: 10px; font-size: 11px; color: #92400e;">
            {{ $settlement->transaction_count }} transaction(s)
        </div>
    </div>

    @foreach($transactionsByEmployee as $emp)
    <div class="employee-section">
        <div class="employee-header">
            <span class="employee-name">{{ $emp['employee_name'] }}</span>
            <span class="employee-id">Employee #{{ $emp['employee_number'] }}</span>
            <span class="employee-total">${{ number_format($emp['total_discount'], 2) }}</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Order #</th>
                    <th class="text-right">Order Subtotal</th>
                    <th class="text-right">Discount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($emp['transactions'] as $txn)
                <tr>
                    <td>{{ $txn['date'] }}</td>
                    <td>#{{ $txn['order_id'] }}</td>
                    <td class="text-right">${{ number_format($txn['order_subtotal'], 2) }}</td>
                    <td class="text-right">${{ number_format($txn['discount_amount'], 2) }}</td>
                </tr>
                @endforeach
                <tr style="background: #f9fafb; font-weight: bold;">
                    <td colspan="3" class="text-right">Employee Subtotal:</td>
                    <td class="text-right">${{ number_format($emp['total_discount'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="footer">
        <p><strong>Grillstone Restaurant</strong></p>
        <p>Generated on {{ \Carbon\Carbon::now()->format('F d, Y \a\t g:i A') }}</p>
        <p>This is an official settlement report for the loyalty program partnership.</p>
        <p>Please remit payment within 30 days of receipt.</p>
    </div>
</body>
</html>
