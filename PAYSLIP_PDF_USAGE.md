# Payslip PDF Generator - Usage Guide

## Overview

The Payslip PDF Service generates beautiful, professional PDF payslips for Grillstone employees using the barryvdh/laravel-dompdf package.

## Files Created

### Service Layer
- **`/app/Services/PayslipPdfService.php`** - Main service for generating and emailing PDF payslips

### Views
- **`/resources/views/pdf/payslip.blade.php`** - Beautiful PDF template with company branding
- **`/resources/views/emails/payslip.blade.php`** - Email template for sending payslips

### Controller Integration
- **`/app/Http/Controllers/Api/PayrollController.php`** - Added PDF methods to existing controller

## Features

### PayslipPdfService Methods

#### 1. `generatePayslipPdf($payslipId)`
Generates a beautiful PDF payslip document.

**Returns:** `\Barryvdh\DomPDF\PDF` object

**Example:**
```php
use App\Services\PayslipPdfService;

$service = new PayslipPdfService();
$pdf = $service->generatePayslipPdf(123);

// Download
return $pdf->download('payslip.pdf');

// Stream in browser
return $pdf->stream('payslip.pdf');

// Save to file
$pdf->save(storage_path('app/payslips/payslip_123.pdf'));
```

#### 2. `emailPayslipPdf($payslipId)`
Generates and emails the PDF payslip to the employee.

**Returns:** `bool` (true on success)

**Features:**
- Automatically attaches PDF to email
- Updates payslip status to 'sent'
- Records sent_at timestamp
- Validates employee has email address

**Example:**
```php
use App\Services\PayslipPdfService;

$service = new PayslipPdfService();

try {
    $service->emailPayslipPdf(123);
    echo "Payslip sent successfully!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## API Endpoints

### Download Single Payslip PDF
```http
GET /api/payroll/periods/{periodId}/payslips/{payslipId}/pdf
```

**Response:** PDF file download

**Example:**
```bash
curl -O -J http://localhost:8000/api/payroll/periods/1/payslips/5/pdf
```

### Email Single Payslip PDF
```http
POST /api/payroll/periods/{periodId}/payslips/{payslipId}/email-pdf
```

**Response:**
```json
{
  "success": true,
  "message": "Payslip sent successfully"
}
```

**Example:**
```bash
curl -X POST http://localhost:8000/api/payroll/periods/1/payslips/5/email-pdf
```

### Email All Payslips in Period as PDFs
```http
POST /api/payroll/periods/{periodId}/email-pdf
```

**Response:**
```json
{
  "sent": 15,
  "errors": []
}
```

**Example:**
```bash
curl -X POST http://localhost:8000/api/payroll/periods/1/email-pdf
```

## PDF Design Features

### Visual Design
- **Professional Layout**: Clean, organized structure with clear sections
- **Company Branding**: GRILLSTONE header with company address
- **Color Scheme**: Green (#059669) and blue (#1e40af) theme
- **Typography**: Helvetica/Arial with proper hierarchy
- **Responsive Tables**: Bordered tables with alternating row colors

### Content Sections

1. **Header**
   - Company name: "GRILLSTONE" (large, bold, green)
   - Company address and contact info
   - "EMPLOYEE PAYSLIP" title

2. **Period Information Box**
   - Pay period dates (highlighted in blue box)
   - Pay date

3. **Employee Information**
   - Full name and employee number
   - Position and department
   - TRN and NIS numbers

4. **Earnings Table**
   - Regular pay with hours
   - Overtime pay (1.5x) with hours
   - Bonus (if applicable)
   - **Gross Pay** (highlighted total row)

5. **Deductions Table**
   - NIS (3%)
   - NHT (2%)
   - Education Tax (2.25%)
   - PAYE (income tax)
   - Other deductions
   - **Total Deductions** (highlighted total row)

6. **Net Pay Box**
   - Prominent green gradient box
   - Large, bold net pay amount
   - Centered and eye-catching

7. **Footer**
   - "Computer-generated payslip" notice
   - Generation timestamp

### Currency Formatting
All monetary values are formatted as:
- `JMD 12,345.67` (with commas and 2 decimal places)

## Email Features

### Email Template
The email includes:
- Professional HTML layout with company branding
- Summary of key payslip information
- PDF attachment
- Instructions for questions

### Email Content
- Employee name personalization
- Pay period and pay date
- Quick summary (Gross, Deductions, Net)
- PDF attachment with descriptive filename

### Filename Format
```
Payslip_{employee_number}_{end_date}.pdf
Example: Payslip_EMP001_2025-10-31.pdf
```

## Usage Examples

### Example 1: Generate PDF in Controller
```php
use App\Services\PayslipPdfService;

class MyController extends Controller
{
    public function downloadPayslip($payslipId)
    {
        $service = app(PayslipPdfService::class);
        $pdf = $service->generatePayslipPdf($payslipId);

        return $pdf->download("payslip_{$payslipId}.pdf");
    }
}
```

### Example 2: Email Payslip After Approval
```php
use App\Services\PayslipPdfService;
use Illuminate\Support\Facades\DB;

public function approveAndEmail($payslipId)
{
    // Update status
    DB::table('payslips')
        ->where('id', $payslipId)
        ->update(['status' => 'approved']);

    // Send email with PDF
    $service = app(PayslipPdfService::class);
    $service->emailPayslipPdf($payslipId);

    return response()->json(['message' => 'Approved and sent']);
}
```

### Example 3: Batch Email All Payslips
```php
use App\Services\PayslipPdfService;
use Illuminate\Support\Facades\DB;

public function emailAllPayslips($periodId)
{
    $payslips = DB::table('payslips')
        ->where('payroll_period_id', $periodId)
        ->where('status', 'approved')
        ->pluck('id');

    $service = app(PayslipPdfService::class);
    $sent = 0;
    $errors = [];

    foreach ($payslips as $payslipId) {
        try {
            $service->emailPayslipPdf($payslipId);
            $sent++;
        } catch (\Exception $e) {
            $errors[] = ['payslip_id' => $payslipId, 'error' => $e->getMessage()];
        }
    }

    return response()->json([
        'sent' => $sent,
        'errors' => $errors
    ]);
}
```

### Example 4: Save PDF to Storage
```php
use App\Services\PayslipPdfService;

public function archivePayslip($payslipId)
{
    $service = app(PayslipPdfService::class);
    $pdf = $service->generatePayslipPdf($payslipId);

    $filename = "payslip_{$payslipId}_" . date('Y-m-d') . ".pdf";
    $path = storage_path("app/payslips/{$filename}");

    $pdf->save($path);

    return response()->json([
        'message' => 'Payslip archived',
        'path' => $path
    ]);
}
```

## Configuration

### Mail Configuration
Ensure your `.env` file has mail settings configured:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="payroll@grillstone.com"
MAIL_FROM_NAME="Grillstone Payroll"
```

### PDF Options (Optional)
You can customize PDF options in the service:

```php
// In PayslipPdfService.php
$pdf = Pdf::loadView('pdf.payslip', ['payslip' => $payslipData]);
$pdf->setPaper('a4', 'portrait');
$pdf->setOption('enable_html5_parser', true);
$pdf->setOption('enable_remote', false);
```

## Company Customization

To customize the company information, edit:

**`/resources/views/pdf/payslip.blade.php`** (lines 87-92):

```html
<div class="company-name">GRILLSTONE</div>
<div class="company-address">
    123 Kingston Road, Kingston, Jamaica<br>
    Phone: (876) 555-1234 | Email: payroll@grillstone.com
</div>
```

## Troubleshooting

### Issue: "Employee not found" error
**Solution:** Ensure the payslip ID exists and is linked to an employee.

### Issue: "Employee does not have an email address"
**Solution:** Update the employee record with a valid email before sending.

### Issue: PDF not displaying properly
**Solution:**
- Check that the Blade template syntax is correct
- Ensure dompdf package is installed: `composer require barryvdh/laravel-dompdf`
- Clear Laravel cache: `php artisan cache:clear`

### Issue: Email not sending
**Solution:**
- Verify mail configuration in `.env`
- Test mail with: `php artisan tinker` then `Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'))`
- Check mail logs for errors

### Issue: Styles not rendering in PDF
**Solution:**
- Use inline styles or `<style>` tags (not external CSS files)
- Avoid advanced CSS (flexbox, grid) - use tables for layout
- Test with simple HTML first

## Testing

### Manual Testing
```bash
# Start Laravel server
php artisan serve

# Test PDF generation in browser
curl http://localhost:8000/api/payroll/periods/1/payslips/1/pdf -o test.pdf

# Open the PDF
open test.pdf  # macOS
xdg-open test.pdf  # Linux
start test.pdf  # Windows
```

### Unit Test Example
```php
// tests/Feature/PayslipPdfTest.php
use App\Services\PayslipPdfService;
use Tests\TestCase;

class PayslipPdfTest extends TestCase
{
    public function test_generates_pdf_successfully()
    {
        $service = new PayslipPdfService();
        $pdf = $service->generatePayslipPdf(1);

        $this->assertNotNull($pdf);
        $this->assertInstanceOf(\Barryvdh\DomPDF\PDF::class, $pdf);
    }
}
```

## Support

For issues or questions:
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Review dompdf documentation: https://github.com/barryvdh/laravel-dompdf
- Check mail queue: `php artisan queue:work`

## License
Part of the Grillstone POS & Payroll System.
