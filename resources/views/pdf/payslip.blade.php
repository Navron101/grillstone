<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payslip['employee']['employee_number'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }

        .container {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 15px 20px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #c2410c;
        }

        .company-name {
            font-size: 28pt;
            font-weight: bold;
            color: #c2410c;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .company-address {
            font-size: 9pt;
            color: #666;
            margin-top: 5px;
        }

        .payslip-title {
            font-size: 16pt;
            font-weight: bold;
            color: #c2410c;
            margin-top: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Info Sections */
        .info-section {
            margin-bottom: 20px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-row {
            display: table-row;
        }

        .info-col {
            display: table-cell;
            width: 50%;
            padding: 5px 10px;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            font-size: 9pt;
            display: inline-block;
            width: 150px;
        }

        .info-value {
            color: #333;
            font-size: 10pt;
            word-wrap: break-word;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table-header {
            background-color: #c2410c;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            font-size: 11pt;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        th {
            background-color: #fed7aa;
            color: #9a3412;
            font-weight: bold;
            padding: 6px;
            text-align: left;
            border: 1px solid #fdba74;
            font-size: 9pt;
        }

        td {
            padding: 6px;
            border: 1px solid #e5e7eb;
            font-size: 10pt;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            background-color: #fed7aa;
            font-weight: bold;
        }

        .total-row td {
            border-top: 2px solid #c2410c;
            padding: 10px 8px;
        }

        /* Net Pay Box */
        .net-pay-box {
            background: linear-gradient(135deg, #c2410c 0%, #9a3412 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin: 25px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .net-pay-label {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .net-pay-amount {
            font-size: 24pt;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }

        .footer-note {
            font-style: italic;
            margin-bottom: 5px;
        }

        .generated-info {
            color: #999;
            font-size: 7pt;
        }

        /* Divider */
        .divider {
            height: 2px;
            background-color: #e5e7eb;
            margin: 20px 0;
        }

        /* Period Box */
        .period-box {
            background-color: #fed7aa;
            border-left: 4px solid #c2410c;
            padding: 12px;
            margin-bottom: 20px;
        }

        .period-label {
            font-weight: bold;
            color: #9a3412;
            font-size: 9pt;
        }

        .period-value {
            font-size: 10pt;
            color: #333;
        }

        /* Column widths */
        .col-description {
            width: 40%;
        }

        .col-hours {
            width: 18%;
            text-align: center;
        }

        .col-amount {
            width: 42%;
            text-align: right;
            white-space: nowrap;
            padding-right: 20px !important;
            font-size: 9.5pt;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">GRILLSTONE</div>
            <div class="company-address">
                123 Kingston Road, Kingston, Jamaica<br>
                Phone: (876) 555-1234 | Email: payroll@grillstone.com
            </div>
            <div class="payslip-title">Employee Payslip</div>
        </div>

        <!-- Period Information -->
        <div class="period-box">
            <table style="margin: 0; border: none;">
                <tr style="background: none;">
                    <td style="border: none; padding: 2px 5px;">
                        <span class="period-label">Pay Period:</span>
                        <span class="period-value">{{ $payslip['period']['period_label'] }}</span>
                    </td>
                    <td style="border: none; padding: 2px 5px; text-align: right;">
                        <span class="period-label">Pay Date:</span>
                        <span class="period-value">{{ date('M d, Y', strtotime($payslip['period']['pay_date'])) }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Employee Information -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-col">
                        <span class="info-label">Employee Name:</span>
                        <span class="info-value">{{ $payslip['employee']['full_name'] }}</span>
                    </div>
                    <div class="info-col">
                        <span class="info-label">Employee Number:</span>
                        <span class="info-value">{{ $payslip['employee']['employee_number'] }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-col">
                        <span class="info-label">Position:</span>
                        <span class="info-value">{{ $payslip['employee']['position'] }}</span>
                    </div>
                    <div class="info-col">
                        <span class="info-label">Department:</span>
                        <span class="info-value">{{ $payslip['employee']['department'] }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-col">
                        <span class="info-label">TRN:</span>
                        <span class="info-value">{{ $payslip['employee']['trn'] }}</span>
                    </div>
                    <div class="info-col">
                        <span class="info-label">NIS Number:</span>
                        <span class="info-value">{{ $payslip['employee']['nis'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Earnings Section -->
        <div class="table-header">EARNINGS</div>
        <table>
            <thead>
                <tr>
                    <th class="col-description">Description</th>
                    <th class="col-hours">Hours</th>
                    <th class="col-amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if($payslip['earnings']['regular_pay_raw'] > 0)
                <tr>
                    <td class="col-description">Regular Pay</td>
                    <td class="col-hours">{{ $payslip['hours']['regular'] }}</td>
                    <td class="col-amount">{{ $payslip['earnings']['regular_pay'] }}</td>
                </tr>
                @endif

                @if($payslip['earnings']['overtime_pay_raw'] > 0)
                <tr>
                    <td class="col-description">Overtime Pay (1.5x)</td>
                    <td class="col-hours">{{ $payslip['hours']['overtime'] }}</td>
                    <td class="col-amount">{{ $payslip['earnings']['overtime_pay'] }}</td>
                </tr>
                @endif

                @if($payslip['earnings']['bonus_raw'] > 0)
                <tr>
                    <td class="col-description">Bonus</td>
                    <td class="col-hours">-</td>
                    <td class="col-amount">{{ $payslip['earnings']['bonus'] }}</td>
                </tr>
                @endif

                <tr class="total-row">
                    <td colspan="2"><strong>GROSS PAY</strong></td>
                    <td class="col-amount"><strong>{{ $payslip['earnings']['gross_pay'] }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Deductions Section -->
        <div class="table-header">DEDUCTIONS</div>
        <table>
            <thead>
                <tr>
                    <th class="col-description">Description</th>
                    <th class="col-hours">Rate</th>
                    <th class="col-amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                {{-- Statutory Deductions (only if enabled) --}}
                @if($payslip['include_statutory_deductions'])
                    @if($payslip['deductions']['nis_raw'] > 0)
                    <tr>
                        <td class="col-description">National Insurance Scheme (NIS)</td>
                        <td class="col-hours">{{ $payslip['deductions']['nis_rate'] }}</td>
                        <td class="col-amount">{{ $payslip['deductions']['nis'] }}</td>
                    </tr>
                    @endif

                    @if($payslip['deductions']['nht_raw'] > 0)
                    <tr>
                        <td class="col-description">National Housing Trust (NHT)</td>
                        <td class="col-hours">{{ $payslip['deductions']['nht_rate'] }}</td>
                        <td class="col-amount">{{ $payslip['deductions']['nht'] }}</td>
                    </tr>
                    @endif

                    @if($payslip['deductions']['education_tax_raw'] > 0)
                    <tr>
                        <td class="col-description">Education Tax</td>
                        <td class="col-hours">{{ $payslip['deductions']['education_tax_rate'] }}</td>
                        <td class="col-amount">{{ $payslip['deductions']['education_tax'] }}</td>
                    </tr>
                    @endif

                    @if($payslip['deductions']['paye_raw'] > 0)
                    <tr>
                        <td class="col-description">PAYE (Income Tax)</td>
                        <td class="col-hours">-</td>
                        <td class="col-amount">{{ $payslip['deductions']['paye'] }}</td>
                    </tr>
                    @endif
                @endif

                {{-- Tab Items (always shown) --}}
                @foreach($payslip['tab_items'] as $tabItem)
                <tr>
                    <td class="col-description">
                        {{ $tabItem['description'] }}
                        <span style="font-size: 8pt; color: #666;">({{ date('M d', strtotime($tabItem['date'])) }})</span>
                    </td>
                    <td class="col-hours">Tab</td>
                    <td class="col-amount">{{ $tabItem['amount'] }}</td>
                </tr>
                @endforeach

                @if($payslip['deductions']['other_raw'] > 0)
                <tr>
                    <td class="col-description">Other Deductions</td>
                    <td class="col-hours">-</td>
                    <td class="col-amount">{{ $payslip['deductions']['other'] }}</td>
                </tr>
                @endif

                <tr class="total-row">
                    <td colspan="2"><strong>TOTAL DEDUCTIONS</strong></td>
                    <td class="col-amount"><strong>{{ $payslip['deductions']['total'] }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Net Pay -->
        <div class="net-pay-box">
            <div class="net-pay-label">Net Pay</div>
            <div class="net-pay-amount">{{ $payslip['net_pay'] }}</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-note">
                This is a computer-generated payslip and does not require a signature.
            </div>
            <div class="generated-info">
                Generated on {{ $payslip['generated_at'] }}
            </div>
        </div>
    </div>
</body>
</html>
