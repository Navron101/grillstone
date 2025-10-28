<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobLetter;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class JobLetterController extends Controller
{
    /**
     * List all job letters
     */
    public function index(Request $request)
    {
        $query = JobLetter::with(['employee', 'generatedBy']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by employee name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('employee_name', 'like', "%{$search}%");
        }

        $letters = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['letters' => $letters]);
    }

    /**
     * Autocomplete employee search
     */
    public function searchEmployees(Request $request)
    {
        $search = $request->get('query', '');

        $employees = Employee::where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        })
        ->limit(10)
        ->get()
        ->map(function($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $employee->email,
                'position' => $employee->position ?? 'N/A',
                'address' => $employee->address ?? '',
            ];
        });

        return response()->json(['employees' => $employees]);
    }

    /**
     * Generate an employment verification letter
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'recipient_name' => 'nullable|string',
            'recipient_organization' => 'nullable|string',
            'recipient_address' => 'nullable|string',
            'letter_purpose' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        $employeeName = $employee->first_name . ' ' . $employee->last_name;

        // Determine salutation (Mr./Ms.)
        $salutation = $this->getSalutation($employee->gender);

        // Generate letter HTML
        $letterHtml = $this->generateLetterHtml([
            'employee_name' => $employeeName,
            'last_name' => $employee->last_name,
            'salutation' => $salutation,
            'position' => $employee->position,
            'hire_date' => $employee->hire_date->format('F j, Y'),
            'employment_status' => ucfirst($employee->employment_status),
            'recipient_name' => $data['recipient_name'] ?? null,
            'recipient_organization' => $data['recipient_organization'] ?? null,
            'recipient_address' => $data['recipient_address'] ?? null,
        ]);

        $letter = JobLetter::create([
            'employee_id' => $data['employee_id'],
            'employee_name' => $employeeName,
            'recipient_name' => $data['recipient_name'] ?? null,
            'recipient_organization' => $data['recipient_organization'] ?? null,
            'recipient_address' => $data['recipient_address'] ?? null,
            'letter_purpose' => $data['letter_purpose'] ?? 'employment_verification',
            'letter_date' => now(),
            'letter_html' => $letterHtml,
            'status' => 'draft',
            'sent_to_email' => $employee->email,
            'generated_by' => auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Employment verification letter generated successfully',
            'letter' => $letter->load(['employee', 'generatedBy'])
        ], 201);
    }

    /**
     * Show a single job letter
     */
    public function show($id)
    {
        $letter = JobLetter::with(['employee', 'generatedBy'])->findOrFail($id);

        return response()->json(['letter' => $letter]);
    }

    /**
     * Send job letter to employee's email
     */
    public function sendEmail(Request $request, $id)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $letter = JobLetter::findOrFail($id);

        try {
            // Send email with letter
            Mail::send([], [], function ($message) use ($letter, $data) {
                $message->to($data['email'])
                    ->subject('Job Appointment Letter - ' . $letter->employee_name)
                    ->html($this->getEmailHtml($letter));
            });

            // Update letter
            $letter->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sent_to_email' => $data['email'],
            ]);

            return response()->json([
                'message' => 'Job letter sent successfully',
                'letter' => $letter
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send email',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update letter status
     */
    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:draft,sent,acknowledged',
        ]);

        $letter = JobLetter::findOrFail($id);
        $letter->update(['status' => $data['status']]);

        return response()->json([
            'message' => 'Letter status updated',
            'letter' => $letter
        ]);
    }

    /**
     * Delete a job letter
     */
    public function destroy($id)
    {
        $letter = JobLetter::findOrFail($id);
        $letter->delete();

        return response()->json(['message' => 'Job letter deleted successfully']);
    }

    /**
     * Generate employment verification letter HTML template
     */
    private function generateLetterHtml(array $data): string
    {
        $todayDate = date('F j, Y');

        // Get signature image from settings
        $signaturePath = \App\Models\Setting::get('hr_signature_image');
        $signatureHtml = '';
        if ($signaturePath) {
            $signatureUrl = asset('storage/' . $signaturePath);
            $signatureHtml = '<img src="' . $signatureUrl . '" alt="HR Signature" style="max-height: 60px; margin-bottom: 10px;" />';
        }

        // Recipient section
        $recipientSection = '';
        if (!empty($data['recipient_name']) || !empty($data['recipient_organization'])) {
            $recipientSection .= '<div class="recipient">';
            if (!empty($data['recipient_name'])) {
                $recipientSection .= '<p><strong>' . htmlspecialchars($data['recipient_name']) . '</strong></p>';
            }
            if (!empty($data['recipient_organization'])) {
                $recipientSection .= '<p>' . htmlspecialchars($data['recipient_organization']) . '</p>';
            }
            if (!empty($data['recipient_address'])) {
                $recipientSection .= '<p>' . nl2br(htmlspecialchars($data['recipient_address'])) . '</p>';
            }
            $recipientSection .= '</div>';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.8;
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
            font-size: 24px;
        }
        .date {
            text-align: right;
            margin-bottom: 30px;
        }
        .recipient {
            margin-bottom: 30px;
        }
        .content {
            margin-bottom: 30px;
            text-align: justify;
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
        .highlight {
            background-color: #f0f8ff;
            padding: 15px;
            border-left: 4px solid #2c3e50;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GRILLSTONE RESTAURANT</h1>
        <p>Kingston, Jamaica</p>
        <p>Phone: +1-876-XXX-XXXX | Email: hr@grillstone.com</p>
    </div>

    <div class="date">
        <p><strong>Date:</strong> {$todayDate}</p>
    </div>

    {$recipientSection}

    <div class="content">
        <h2 style="color: #2c3e50; font-size: 18px; margin: 30px 0 20px 0; text-align: center;">
            TO WHOM IT MAY CONCERN
        </h2>

        <h3 style="color: #2c3e50; font-size: 16px; margin: 20px 0;">
            RE: EMPLOYMENT VERIFICATION LETTER
        </h3>

        <p>
            This letter is to confirm that <strong>{$data['employee_name']}</strong> is currently employed
            with Grillstone Restaurant as a <strong>{$data['position']}</strong>.
        </p>

        <div class="highlight">
            <p style="margin: 0;"><strong>Employee Name:</strong> {$data['employee_name']}</p>
            <p style="margin: 10px 0 0 0;"><strong>Position:</strong> {$data['position']}</p>
            <p style="margin: 10px 0 0 0;"><strong>Employment Start Date:</strong> {$data['hire_date']}</p>
            <p style="margin: 10px 0 0 0;"><strong>Employment Status:</strong> {$data['employment_status']}</p>
        </div>

        <p>
            {$data['salutation']} {$data['last_name']} has been a valuable member of our team since {$data['hire_date']}.
            {$data['salutation']} {$data['last_name']} has consistently demonstrated professionalism, reliability,
            and dedication to the responsibilities of the position.
        </p>

        <p>
            This letter is issued upon the request of the employee for official purposes. If you require
            any additional information or verification, please do not hesitate to contact our Human Resources
            department.
        </p>

        <p>
            We can be reached at:
        </p>

        <p style="padding-left: 20px;">
            <strong>Grillstone Restaurant</strong><br>
            Kingston, Jamaica<br>
            Phone: +1-876-XXX-XXXX<br>
            Email: hr@grillstone.com
        </p>

        <p>Yours faithfully,</p>
    </div>

    <div class="signature-section">
        {$signatureHtml}
        <div class="signature-line">
            <p><strong>Human Resources Department</strong></p>
            <p>Grillstone Restaurant</p>
        </div>
    </div>

    <div style="margin-top: 60px; padding-top: 20px; border-top: 1px solid #ddd;">
        <p style="font-size: 11px; color: #666; text-align: center; font-style: italic;">
            This is an official document issued by Grillstone Restaurant. For verification purposes,
            please contact our HR department at the details provided above.
        </p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get email HTML wrapper for employment verification letter
     */
    private function getEmailHtml(JobLetter $letter): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2c3e50;">Employment Verification Letter</h2>
        <p>Dear {$letter->employee_name},</p>
        <p>Please find your employment verification letter attached below. This letter confirms your current employment with Grillstone Restaurant.</p>
        <p>You may present this letter to any organization or institution requiring proof of employment.</p>
        <hr style="margin: 30px 0;">
        {$letter->letter_html}
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get salutation based on gender
     */
    private function getSalutation(?string $gender): string
    {
        return match($gender) {
            'male' => 'Mr.',
            'female' => 'Ms.',
            default => 'Mr./Ms.',
        };
    }

    /**
     * Download job letter as PDF
     */
    public function downloadPdf($id)
    {
        $letter = JobLetter::with('employee')->findOrFail($id);

        $pdf = Pdf::loadHTML($letter->letter_html)
            ->setPaper('a4', 'portrait');

        $filename = 'employment_verification_' . str_replace(' ', '_', strtolower($letter->employee_name)) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download job letter as Word document
     */
    public function downloadWord($id)
    {
        $letter = JobLetter::with('employee')->findOrFail($id);

        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        // Add section
        $section = $phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1000,
            'marginRight' => 1000,
        ]);

        // Header - Company Name
        $section->addText(
            'GRILLSTONE RESTAURANT',
            ['bold' => true, 'size' => 16, 'color' => '2c3e50'],
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
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 300]
        );

        // Date
        $section->addText(
            'Date: ' . now()->format('F j, Y'),
            ['size' => 11],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END, 'spaceAfter' => 300]
        );

        // Recipient details if present
        if ($letter->recipient_name || $letter->recipient_organization) {
            if ($letter->recipient_name) {
                $section->addText($letter->recipient_name, ['bold' => true, 'size' => 11]);
            }
            if ($letter->recipient_organization) {
                $section->addText($letter->recipient_organization, ['size' => 11]);
            }
            if ($letter->recipient_address) {
                $section->addText($letter->recipient_address, ['size' => 11]);
            }
            $section->addTextBreak();
        }

        // Subject
        $section->addText(
            'TO WHOM IT MAY CONCERN',
            ['bold' => true, 'size' => 12],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addText(
            'RE: EMPLOYMENT VERIFICATION LETTER',
            ['bold' => true, 'size' => 11, 'underline' => 'single'],
            ['spaceAfter' => 200]
        );

        // Get employee data
        $employee = $letter->employee;
        $salutation = $this->getSalutation($employee->gender);

        // Body paragraphs
        $section->addText(
            "This letter is to confirm that {$letter->employee_name} is currently employed with Grillstone Restaurant as a {$employee->position}.",
            ['size' => 11],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 200]
        );

        // Employee details box
        $section->addText('Employee Name: ' . $letter->employee_name, ['size' => 11], ['spaceAfter' => 100]);
        $section->addText('Position: ' . $employee->position, ['size' => 11], ['spaceAfter' => 100]);
        $section->addText('Employment Start Date: ' . $employee->hire_date->format('F j, Y'), ['size' => 11], ['spaceAfter' => 100]);
        $section->addText('Employment Status: ' . ucfirst($employee->employment_status), ['size' => 11], ['spaceAfter' => 200]);

        $section->addText(
            "{$salutation} {$employee->last_name} has been a valuable member of our team since {$employee->hire_date->format('F j, Y')}. {$salutation} {$employee->last_name} has consistently demonstrated professionalism, reliability, and dedication to the responsibilities of the position.",
            ['size' => 11],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 200]
        );

        $section->addText(
            'This letter is issued upon the request of the employee for official purposes. If you require any additional information or verification, please do not hesitate to contact our Human Resources department.',
            ['size' => 11],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 200]
        );

        $section->addText('We can be reached at:', ['size' => 11], ['spaceAfter' => 100]);
        $section->addText('Grillstone Restaurant', ['bold' => true, 'size' => 11]);
        $section->addText('Kingston, Jamaica', ['size' => 11]);
        $section->addText('Phone: +1-876-XXX-XXXX', ['size' => 11]);
        $section->addText('Email: hr@grillstone.com', ['size' => 11], ['spaceAfter' => 300]);

        $section->addText('Yours faithfully,', ['size' => 11], ['spaceAfter' => 600]);

        // Signature
        $section->addText('_______________________', ['size' => 11]);
        $section->addText('Human Resources Department', ['bold' => true, 'size' => 11]);
        $section->addText('Grillstone Restaurant', ['size' => 11]);

        // Save to temp file and download
        $filename = 'employment_verification_' . str_replace(' ', '_', strtolower($letter->employee_name)) . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'phpword');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
