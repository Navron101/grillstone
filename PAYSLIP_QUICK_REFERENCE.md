# Payslip PDF - Quick Reference

## Quick Start

### 1. Generate PDF
```php
use App\Services\PayslipPdfService;

$service = app(PayslipPdfService::class);
$pdf = $service->generatePayslipPdf($payslipId);

return $pdf->download('payslip.pdf');  // Download
// OR
return $pdf->stream('payslip.pdf');    // View in browser
```

### 2. Email PDF
```php
use App\Services\PayslipPdfService;

$service = app(PayslipPdfService::class);
$service->emailPayslipPdf($payslipId);
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/payroll/periods/{periodId}/payslips/{payslipId}/pdf` | Download PDF |
| `POST` | `/api/payroll/periods/{periodId}/payslips/{payslipId}/email-pdf` | Email single PDF |
| `POST` | `/api/payroll/periods/{periodId}/email-pdf` | Email all PDFs in period |

## cURL Examples

```bash
# Download PDF
curl -O -J http://localhost:8000/api/payroll/periods/1/payslips/5/pdf

# Email single payslip
curl -X POST http://localhost:8000/api/payroll/periods/1/payslips/5/email-pdf

# Email all payslips in period
curl -X POST http://localhost:8000/api/payroll/periods/1/email-pdf
```

## Files Structure

```
app/Services/
  └── PayslipPdfService.php          # Main service (182 lines)

resources/views/
  ├── pdf/
  │   └── payslip.blade.php          # PDF template (405 lines)
  └── emails/
      └── payslip.blade.php          # Email template (98 lines)

app/Http/Controllers/Api/
  └── PayrollController.php          # Updated with PDF methods
```

## PDF Layout Preview

```
┌─────────────────────────────────────────┐
│          GRILLSTONE (Green)             │
│    123 Kingston Road, Kingston, JA      │
│        EMPLOYEE PAYSLIP (Blue)          │
├─────────────────────────────────────────┤
│ [Period Info Box - Blue Background]     │
│ Pay Period: Oct 01 - Oct 15, 2025      │
│ Pay Date: Oct 20, 2025                  │
├─────────────────────────────────────────┤
│ Employee: John Smith                    │
│ Employee Number: EMP001                 │
│ Position: Chef                          │
│ Department: Kitchen                     │
│ TRN: 123-456-789 | NIS: NIS123456      │
├─────────────────────────────────────────┤
│ EARNINGS (Green Header)                 │
├─────────────────────────────────────────┤
│ Description        Hours      Amount    │
│ Regular Pay        80.00   JMD 40,000.00│
│ Overtime Pay       5.00    JMD 3,750.00 │
│ Bonus              -       JMD 2,000.00 │
├─────────────────────────────────────────┤
│ GROSS PAY                 JMD 45,750.00 │
├─────────────────────────────────────────┤
│ DEDUCTIONS (Green Header)               │
├─────────────────────────────────────────┤
│ Description          Rate      Amount   │
│ NIS                  3%     JMD 1,372.50│
│ NHT                  2%     JMD 915.00  │
│ Education Tax      2.25%   JMD 1,029.38│
│ PAYE                 -      JMD 5,250.00│
├─────────────────────────────────────────┤
│ TOTAL DEDUCTIONS          JMD 8,566.88  │
├─────────────────────────────────────────┤
│ ╔═══════════════════════════════════╗   │
│ ║   NET PAY (Green Gradient Box)   ║   │
│ ║        JMD 37,183.12              ║   │
│ ╚═══════════════════════════════════╝   │
├─────────────────────────────────────────┤
│ Computer-generated payslip              │
│ Generated: 2025-10-20 14:30:00         │
└─────────────────────────────────────────┘
```

## Data Structure

The service expects this database structure:

```sql
payslips
  - id
  - employee_id
  - payroll_period_id
  - regular_hours, overtime_hours
  - regular_pay_cents, overtime_pay_cents, bonus_cents
  - gross_pay_cents
  - nis_cents, nht_cents, education_tax_cents, paye_cents
  - other_deductions_cents, total_deductions_cents
  - net_pay_cents
  - status, sent_at, paid_at

employees
  - id, employee_number, first_name, last_name
  - email, position, department_id
  - trn, nis

payroll_periods
  - id, start_date, end_date, pay_date
  - status

departments
  - id, name
```

## Key Features

### Design
- Professional green/blue color scheme
- Clean bordered tables
- Prominent net pay display
- Company branding header
- Responsive layout

### Calculations
- All amounts stored in cents (integer)
- Formatted as `JMD 12,345.67`
- Automatic totals calculation
- Jamaica tax rates:
  - NIS: 3%
  - NHT: 2%
  - Education Tax: 2.25%
  - PAYE: Progressive

### Email
- HTML formatted email
- PDF attachment
- Filename: `Payslip_{EMP_NUM}_{DATE}.pdf`
- Updates status to 'sent'
- Records sent_at timestamp

## Customization

### Change Company Info
Edit `/resources/views/pdf/payslip.blade.php` lines 87-92:
```html
<div class="company-name">YOUR COMPANY</div>
<div class="company-address">
    Your Address Here<br>
    Phone: XXX | Email: XXX
</div>
```

### Change Colors
Edit `/resources/views/pdf/payslip.blade.php` CSS:
- Primary Green: `#059669` → Your color
- Secondary Blue: `#1e40af` → Your color

### Add Logo
Add to header section:
```html
<img src="data:image/png;base64,..." style="width: 100px;">
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| PDF blank | Check Blade syntax, ensure data exists |
| Styles not working | Use inline styles or `<style>` tags only |
| Email not sending | Verify `.env` mail config |
| "Employee not found" | Check payslip exists and has employee_id |
| Slow generation | Normal for complex PDFs, consider queue |

## Testing

```bash
# Test in Laravel
php artisan tinker
>>> $service = app(\App\Services\PayslipPdfService::class);
>>> $pdf = $service->generatePayslipPdf(1);
>>> $pdf->save(storage_path('test.pdf'));

# Or use routes
curl http://localhost:8000/api/payroll/periods/1/payslips/1/pdf -o test.pdf
open test.pdf
```

## Production Checklist

- [ ] Configure mail settings in `.env`
- [ ] Update company name/address in template
- [ ] Customize colors (optional)
- [ ] Add company logo (optional)
- [ ] Test PDF generation with sample data
- [ ] Test email delivery
- [ ] Set up email queue for bulk sending
- [ ] Configure storage path for archived PDFs
- [ ] Add authentication/authorization to routes
- [ ] Test with various payslip scenarios

## Support

Full documentation: `PAYSLIP_PDF_USAGE.md`
Package docs: https://github.com/barryvdh/laravel-dompdf
