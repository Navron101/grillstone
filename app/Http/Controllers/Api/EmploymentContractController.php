<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmploymentContract;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class EmploymentContractController extends Controller
{
    /**
     * List all employment contracts
     */
    public function index(Request $request)
    {
        $query = EmploymentContract::with(['employee', 'generatedBy']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by employee name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('employee_name', 'like', "%{$search}%");
        }

        $contracts = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['contracts' => $contracts]);
    }

    /**
     * Generate a new employment contract
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'employee_name' => 'required|string',
            'employee_address' => 'required|string',
            'position' => 'required|string',
            'salary_amount_cents' => 'nullable|integer|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'contract_type' => 'required|in:employment,probation,permanent',
        ]);

        // Generate contract HTML
        $contractHtml = $this->generateContractHtml($data);

        // Get employee email if employee_id is provided
        $employeeEmail = null;
        if ($data['employee_id']) {
            $employee = Employee::find($data['employee_id']);
            $employeeEmail = $employee->email ?? null;
        }

        $contract = EmploymentContract::create([
            'employee_id' => $data['employee_id'] ?? null,
            'contract_type' => $data['contract_type'],
            'employee_name' => $data['employee_name'],
            'employee_address' => $data['employee_address'],
            'position' => $data['position'],
            'salary_amount_cents' => $data['salary_amount_cents'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'contract_html' => $contractHtml,
            'status' => 'draft',
            'sent_to_email' => $employeeEmail,
            'generated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Contract generated successfully',
            'contract' => $contract->load(['employee', 'generatedBy'])
        ], 201);
    }

    /**
     * Show a single contract
     */
    public function show($id)
    {
        $contract = EmploymentContract::with(['employee', 'generatedBy'])->findOrFail($id);

        return response()->json(['contract' => $contract]);
    }

    /**
     * Send contract to employee's email
     */
    public function sendEmail(Request $request, $id)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $contract = EmploymentContract::findOrFail($id);

        try {
            // Send email with contract
            Mail::send([], [], function ($message) use ($contract, $data) {
                $message->to($data['email'])
                    ->subject('Employment Contract - ' . $contract->employee_name)
                    ->html($this->getEmailHtml($contract));
            });

            // Update contract
            $contract->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sent_to_email' => $data['email'],
            ]);

            return response()->json([
                'message' => 'Contract sent successfully',
                'contract' => $contract
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send email',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update contract status
     */
    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:draft,sent,signed,cancelled',
        ]);

        $contract = EmploymentContract::findOrFail($id);
        $contract->update(['status' => $data['status']]);

        return response()->json([
            'message' => 'Contract status updated',
            'contract' => $contract
        ]);
    }

    /**
     * Delete a contract
     */
    public function destroy($id)
    {
        $contract = EmploymentContract::findOrFail($id);

        if ($contract->status === 'signed') {
            return response()->json([
                'error' => 'Cannot delete signed contracts'
            ], 400);
        }

        $contract->delete();

        return response()->json(['message' => 'Contract deleted successfully']);
    }

    /**
     * Generate contract HTML template
     */
    private function generateContractHtml(array $data): string
    {
        $salary = isset($data['salary_amount_cents'])
            ? 'JMD $' . number_format($data['salary_amount_cents'] / 100, 2)
            : 'As per agreed terms';

        $endDate = $data['end_date']
            ? date('F j, Y', strtotime($data['end_date']))
            : 'Permanent';

        // Get signature image from settings
        $signaturePath = \App\Models\Setting::get('hr_signature_image');
        $signatureHtml = '';
        if ($signaturePath) {
            $signatureUrl = asset('storage/' . $signaturePath);
            $signatureHtml = '<img src="' . $signatureUrl . '" alt="HR Signature" style="max-height: 60px; margin-bottom: 10px;" />';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .company-info {
            margin-bottom: 30px;
            text-align: center;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        .signature-section {
            margin-top: 60px;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 250px;
            margin-top: 50px;
            padding-top: 5px;
        }
        table {
            width: 100%;
            margin: 20px 0;
        }
        td {
            padding: 8px 0;
        }
        .label {
            font-weight: bold;
            width: 200px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EMPLOYMENT CONTRACT</h1>
        <p>Grillstone Restaurant</p>
    </div>

    <div class="company-info">
        <p><strong>Grillstone Restaurant</strong></p>
        <p>Kingston, Jamaica</p>
        <p>Phone: +1-876-XXX-XXXX | Email: hr@grillstone.com</p>
    </div>

    <div class="section">
        <p>This Employment Contract is made on <strong>{$data['start_date']}</strong></p>
    </div>

    <div class="section">
        <div class="section-title">BETWEEN:</div>
        <p><strong>Grillstone Restaurant</strong> (hereinafter referred to as "the Employer")</p>
        <p>AND</p>
        <p><strong>{$data['employee_name']}</strong><br>
        {$data['employee_address']}<br>
        (hereinafter referred to as "the Employee")</p>
    </div>

    <div class="section">
        <div class="section-title">1. POSITION AND DUTIES</div>
        <p>The Employee is hired for the position of <strong>{$data['position']}</strong>.</p>
        <p>The Employee agrees to perform all duties and responsibilities associated with this position as assigned by the Employer.</p>
    </div>

    <div class="section">
        <div class="section-title">2. EMPLOYMENT PERIOD</div>
        <table>
            <tr>
                <td class="label">Start Date:</td>
                <td>{$data['start_date']}</td>
            </tr>
            <tr>
                <td class="label">Contract Type:</td>
                <td>{$data['contract_type']}</td>
            </tr>
            <tr>
                <td class="label">End Date:</td>
                <td>{$endDate}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">3. COMPENSATION</div>
        <p>The Employee will receive a salary of <strong>{$salary}</strong> per month, subject to applicable deductions and withholdings as required by law.</p>
    </div>

    <div class="section">
        <div class="section-title">4. WORKING HOURS</div>
        <p>The Employee's normal working hours will be as scheduled by the Employer, typically 40 hours per week. The Employee may be required to work additional hours as needed by the business.</p>
    </div>

    <div class="section">
        <div class="section-title">5. BENEFITS</div>
        <p>The Employee will be entitled to benefits as per company policy, including but not limited to:</p>
        <ul>
            <li>Annual leave as per Jamaican labor laws</li>
            <li>Sick leave as per company policy</li>
            <li>Public holidays</li>
            <li>Statutory benefits as required by law</li>
        </ul>
    </div>

    <div class="section">
        <div class="section-title">6. TERMINATION</div>
        <p>Either party may terminate this agreement by providing written notice as required by Jamaican labor law and company policy.</p>
    </div>

    <div class="section">
        <div class="section-title">7. CONFIDENTIALITY</div>
        <p>The Employee agrees to maintain confidentiality of all proprietary information, trade secrets, and business practices of the Employer during and after employment.</p>
    </div>

    <div class="signature-section">
        <p>By signing below, both parties acknowledge and agree to the terms and conditions outlined in this employment contract.</p>

        <div style="display: flex; justify-content: space-between; margin-top: 60px;">
            <div>
                {$signatureHtml}
                <div class="signature-line">Employer Signature</div>
                <p><strong>Grillstone Restaurant</strong></p>
                <p>Date: _________________</p>
            </div>

            <div>
                <div class="signature-line">Employee Signature</div>
                <p><strong>{$data['employee_name']}</strong></p>
                <p>Date: _________________</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get email HTML wrapper for contract
     */
    private function getEmailHtml(EmploymentContract $contract): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2c3e50;">Employment Contract</h2>
        <p>Dear {$contract->employee_name},</p>
        <p>Please find your employment contract attached below. Please review, sign, and return a copy to HR.</p>
        <p>If you have any questions, please don't hesitate to contact us.</p>
        <hr style="margin: 30px 0;">
        {$contract->contract_html}
    </div>
</body>
</html>
HTML;
    }

    /**
     * Download contract as PDF
     */
    public function downloadPdf($id)
    {
        $contract = EmploymentContract::with('employee')->findOrFail($id);

        $pdf = Pdf::loadHTML($contract->contract_html)
            ->setPaper('a4', 'portrait');

        $filename = 'employment_contract_' . str_replace(' ', '_', strtolower($contract->employee_name)) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download contract as Word document
     */
    public function downloadWord($id)
    {
        $contract = EmploymentContract::with('employee')->findOrFail($id);

        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        // Add section with margins
        $section = $phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1000,
            'marginRight' => 1000,
        ]);

        // Header - Company Name
        $section->addText(
            'EMPLOYMENT CONTRACT',
            ['bold' => true, 'size' => 16, 'color' => '2c3e50'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $section->addText(
            'Grillstone Restaurant',
            ['size' => 12],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $section->addText(
            'Kingston, Jamaica',
            ['size' => 10],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $section->addText(
            'Phone: +1-876-XXX-XXXX | Email: hr@grillstone.com',
            ['size' => 10],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 400]
        );

        // Contract date
        $section->addText(
            'This Employment Contract is made on ' . date('F j, Y', strtotime($contract->start_date)),
            ['size' => 11],
            ['spaceAfter' => 300]
        );

        // BETWEEN section
        $section->addText('BETWEEN:', ['bold' => true, 'size' => 11], ['spaceAfter' => 100]);
        $section->addText(
            'Grillstone Restaurant (hereinafter referred to as "the Employer")',
            ['size' => 11],
            ['spaceAfter' => 100]
        );
        $section->addText('AND', ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100]);
        $section->addText(
            $contract->employee_name,
            ['bold' => true, 'size' => 11],
            ['spaceAfter' => 50]
        );
        $section->addText(
            $contract->employee_address,
            ['size' => 11],
            ['spaceAfter' => 100]
        );
        $section->addText(
            '(hereinafter referred to as "the Employee")',
            ['size' => 11],
            ['spaceAfter' => 300]
        );

        // 1. POSITION AND DUTIES
        $section->addText('1. POSITION AND DUTIES', ['bold' => true, 'size' => 11, 'underline' => 'single'], ['spaceAfter' => 100]);
        $section->addText(
            "The Employee is hired for the position of {$contract->position}.",
            ['size' => 11],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 100]
        );
        $section->addText(
            'The Employee agrees to perform all duties and responsibilities associated with this position as assigned by the Employer.',
            ['size' => 11],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 200]
        );

        // 2. EMPLOYMENT PERIOD
        $section->addText('2. EMPLOYMENT PERIOD', ['bold' => true, 'size' => 11, 'underline' => 'single'], ['spaceAfter' => 100]);
        $section->addText('Start Date: ' . date('F j, Y', strtotime($contract->start_date)), ['size' => 11], ['spaceAfter' => 50]);
        $section->addText('Contract Type: ' . ucfirst($contract->contract_type), ['size' => 11], ['spaceAfter' => 50]);
        $endDate = $contract->end_date ? date('F j, Y', strtotime($contract->end_date)) : 'Permanent';
        $section->addText('End Date: ' . $endDate, ['size' => 11], ['spaceAfter' => 200]);

        // 3. COMPENSATION
        $section->addText('3. COMPENSATION', ['bold' => true, 'size' => 11, 'underline' => 'single'], ['spaceAfter' => 100]);
        $salary = $contract->salary_amount_cents
            ? 'JMD $' . number_format($contract->salary_amount_cents / 100, 2)
            : 'As per agreed terms';
        $section->addText(
            "The Employee will receive a salary of {$salary} per month, subject to applicable deductions and withholdings as required by law.",
            ['size' => 11],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 200]
        );

        // 4. WORKING HOURS
        $section->addText('4. WORKING HOURS', ['bold' => true, 'size' => 11, 'underline' => 'single'], ['spaceAfter' => 100]);
        $section->addText(
            "The Employee's normal working hours will be as scheduled by the Employer, typically 40 hours per week. The Employee may be required to work additional hours as needed by the business.",
            ['size' => 11],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 200]
        );

        // 5. BENEFITS
        $section->addText('5. BENEFITS', ['bold' => true, 'size' => 11, 'underline' => 'single'], ['spaceAfter' => 100]);
        $section->addText(
            'The Employee will be entitled to benefits as per company policy, including but not limited to:',
            ['size' => 11],
            ['spaceAfter' => 100]
        );
        $section->addListItem('Annual leave as per Jamaican labor laws', 0, ['size' => 11]);
        $section->addListItem('Sick leave as per company policy', 0, ['size' => 11]);
        $section->addListItem('Public holidays', 0, ['size' => 11]);
        $section->addListItem('Statutory benefits as required by law', 0, ['size' => 11], ['spaceAfter' => 200]);

        // 6. TERMINATION
        $section->addText('6. TERMINATION', ['bold' => true, 'size' => 11, 'underline' => 'single'], ['spaceAfter' => 100]);
        $section->addText(
            'Either party may terminate this agreement by providing written notice as required by Jamaican labor law and company policy.',
            ['size' => 11],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 200]
        );

        // 7. CONFIDENTIALITY
        $section->addText('7. CONFIDENTIALITY', ['bold' => true, 'size' => 11, 'underline' => 'single'], ['spaceAfter' => 100]);
        $section->addText(
            'The Employee agrees to maintain confidentiality of all proprietary information, trade secrets, and business practices of the Employer during and after employment.',
            ['size' => 11],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 400]
        );

        // Signature section
        $section->addText(
            'By signing below, both parties acknowledge and agree to the terms and conditions outlined in this employment contract.',
            ['size' => 11],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 600]
        );

        // Create table for signatures
        $table = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 100
        ]);

        $table->addRow();
        $cellEmployer = $table->addCell(5000);
        $cellEmployer->addText('_______________________', ['size' => 11]);
        $cellEmployer->addText('Employer Signature', ['size' => 11]);
        $cellEmployer->addText('Grillstone Restaurant', ['bold' => true, 'size' => 11]);
        $cellEmployer->addText('Date: _________________', ['size' => 11]);

        $cellEmployee = $table->addCell(5000);
        $cellEmployee->addText('_______________________', ['size' => 11]);
        $cellEmployee->addText('Employee Signature', ['size' => 11]);
        $cellEmployee->addText($contract->employee_name, ['bold' => true, 'size' => 11]);
        $cellEmployee->addText('Date: _________________', ['size' => 11]);

        // Save to temp file and download
        $filename = 'employment_contract_' . str_replace(' ', '_', strtolower($contract->employee_name)) . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'phpword');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
