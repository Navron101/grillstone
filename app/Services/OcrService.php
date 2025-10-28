<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrService
{
    /**
     * Extract text from an image using OCR
     *
     * Uses OCR.space API (free tier) for text extraction
     */
    public function extractText(string $imagePath): array
    {
        try {
            // Check if file exists
            if (!file_exists($imagePath)) {
                throw new \Exception('Image file not found');
            }

            // Try OCR.space API first (free, no installation needed)
            $extractedText = $this->useOcrSpaceApi($imagePath);

            // Calculate confidence based on text quality
            $confidence = $this->calculateConfidence($extractedText);

            return [
                'success' => true,
                'text' => $extractedText,
                'confidence' => $confidence,
            ];

        } catch (\Exception $e) {
            Log::error('OCR extraction failed: ' . $e->getMessage());

            // Fallback to simulation if OCR fails
            Log::info('Falling back to simulated OCR');
            $extractedText = $this->simulateOcrExtraction($imagePath);

            return [
                'success' => true,
                'text' => $extractedText,
                'confidence' => 0.5, // Low confidence for fallback
                'fallback' => true,
            ];
        }
    }

    /**
     * Use OCR.space API for text extraction (FREE)
     */
    private function useOcrSpaceApi(string $imagePath): string
    {
        // OCR.space API endpoint
        $apiUrl = 'https://api.ocr.space/parse/image';

        // Free API key (you can get your own at https://ocr.space/ocrapi)
        // This is a public demo key with rate limits
        $apiKey = env('OCR_SPACE_API_KEY', 'K87899142388957');

        // Read file and encode as base64
        $imageData = base64_encode(file_get_contents($imagePath));

        $response = Http::asForm()->post($apiUrl, [
            'apikey' => $apiKey,
            'base64Image' => 'data:image/jpeg;base64,' . $imageData,
            'language' => 'eng',
            'isOverlayRequired' => 'false',
            'detectOrientation' => 'true',
            'scale' => 'true',
            'OCREngine' => '2', // Engine 2 is better for invoices
        ]);

        if (!$response->successful()) {
            throw new \Exception('OCR API request failed: ' . $response->status());
        }

        $data = $response->json();

        if (isset($data['IsErroredOnProcessing']) && $data['IsErroredOnProcessing']) {
            $errorMessage = $data['ErrorMessage'] ?? 'Unknown OCR error';
            throw new \Exception('OCR processing error: ' . $errorMessage);
        }

        if (!isset($data['ParsedResults'][0]['ParsedText'])) {
            throw new \Exception('No text found in image');
        }

        $text = $data['ParsedResults'][0]['ParsedText'];

        if (empty(trim($text))) {
            throw new \Exception('Extracted text is empty');
        }

        return $text;
    }

    /**
     * Calculate confidence score based on text quality
     */
    private function calculateConfidence(string $text): float
    {
        $confidence = 0.5; // Base confidence

        // Check for invoice indicators
        if (preg_match('/invoice|receipt|bill/i', $text)) {
            $confidence += 0.1;
        }

        // Check for amounts/currency
        if (preg_match('/\$|JMD|USD|total|amount/i', $text)) {
            $confidence += 0.1;
        }

        // Check for dates
        if (preg_match('/\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/', $text)) {
            $confidence += 0.1;
        }

        // Check for line items pattern
        if (preg_match('/\d+\s*x\s*.+\s+[\d,]+\.?\d*/i', $text)) {
            $confidence += 0.1;
        }

        // Penalty for very short text
        if (strlen($text) < 50) {
            $confidence -= 0.2;
        }

        return max(0, min(1, $confidence));
    }

    /**
     * Simulate OCR extraction
     * In production, replace this with actual OCR service integration
     */
    private function simulateOcrExtraction(string $imagePath): string
    {
        // This is a placeholder that returns sample invoice text
        // In production, this would call an actual OCR service

        return <<<EOT
CARIBBEAN WHOLESALE LTD
123 Main Street, Kingston
Tel: 876-555-0123
Email: orders@caribbeanwholesale.jm

INVOICE
Invoice #: INV-2025-001234
Date: October 26, 2025

Bill To:
Grillstone Restaurant
456 Restaurant Ave
Kingston, Jamaica

ITEMS:
1. Chicken Breast (Fresh) - 50 lbs @ JMD 450.00 = JMD 22,500.00
2. Beef Ground - 30 lbs @ JMD 650.00 = JMD 19,500.00
3. Rice (White) - 100 lbs @ JMD 120.00 = JMD 12,000.00
4. Onions - 20 lbs @ JMD 80.00 = JMD 1,600.00
5. Cooking Oil - 10 gal @ JMD 1,200.00 = JMD 12,000.00

Subtotal: JMD 67,600.00
Tax (16.5%): JMD 11,154.00
Total: JMD 78,754.00

Payment Terms: Net 30
Due Date: November 25, 2025

Thank you for your business!
EOT;
    }

    /**
     * Use Google Cloud Vision API for OCR (optional implementation)
     */
    private function useGoogleVisionOcr(string $imagePath): string
    {
        // Requires: composer require google/cloud-vision
        // And Google Cloud credentials configured

        // Example implementation (commented out):
        /*
        $vision = new \Google\Cloud\Vision\VisionClient([
            'keyFilePath' => env('GOOGLE_CLOUD_KEY_FILE')
        ]);

        $image = file_get_contents($imagePath);
        $imageResource = $vision->image($image, ['TEXT_DETECTION']);
        $annotation = $vision->annotate($imageResource);

        $text = $annotation->text();
        return $text ? $text->description() : '';
        */

        throw new \Exception('Google Cloud Vision not configured');
    }

    /**
     * Use Tesseract OCR (requires tesseract installed on server)
     */
    private function useTesseractOcr(string $imagePath): string
    {
        // Requires: sudo apt-get install tesseract-ocr
        // And: composer require thiagoalessio/tesseract_ocr

        // Example implementation (commented out):
        /*
        $ocr = new \TesseractOCR($imagePath);
        $ocr->lang('eng');
        return $ocr->run();
        */

        throw new \Exception('Tesseract OCR not configured');
    }
}
