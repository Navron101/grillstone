<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Payslip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #059669;
        }
        .info-row {
            margin: 8px 0;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GRILLSTONE</h1>
        <p style="margin: 5px 0 0 0;">Payslip Notification</p>
    </div>

    <div class="content">
        <p>Dear {{ $payslip['employee']['full_name'] }},</p>

        <p>Your payslip for the period <strong>{{ $payslip['period']['period_label'] }}</strong> is now available.</p>

        <div class="info-box">
            <div class="info-row">
                <span class="label">Pay Period:</span> {{ $payslip['period']['period_label'] }}
            </div>
            <div class="info-row">
                <span class="label">Pay Date:</span> {{ date('M d, Y', strtotime($payslip['period']['pay_date'])) }}
            </div>
            <div class="info-row">
                <span class="label">Gross Pay:</span> {{ $payslip['earnings']['gross_pay'] }}
            </div>
            <div class="info-row">
                <span class="label">Total Deductions:</span> {{ $payslip['deductions']['total'] }}
            </div>
            <div class="info-row" style="font-size: 18px; margin-top: 15px; padding-top: 15px; border-top: 2px solid #059669;">
                <span class="label">Net Pay:</span> <strong style="color: #059669;">{{ $payslip['net_pay'] }}</strong>
            </div>
        </div>

        <p>Please find your detailed payslip attached to this email as a PDF document.</p>

        <p>If you have any questions or concerns about your payslip, please contact the payroll department.</p>

        <p>Thank you,<br>
        <strong>Grillstone Payroll Department</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} Grillstone. All rights reserved.</p>
    </div>
</body>
</html>
