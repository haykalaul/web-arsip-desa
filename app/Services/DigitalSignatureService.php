<?php

namespace App\Services;

use App\Models\DigitalSignature;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use setasign\Fpdi\Fpdi;

class DigitalSignatureService
{
    /**
     * Process uploaded document and create digital signature with form filling
     */
    public function processDocument($file, $documentData, $user, $formFields = [])
    {
        try {
            // Store the original uploaded document
            $originalFilename = time() . '_original_' . Str::slug($documentData['document_name']) . '.' . $file->getClientOriginalExtension();
            $originalPath = $file->storeAs('documents/originals', $originalFilename, 'public');

            // Generate signature hash
            $signatureHash = $this->generateSignatureHash($documentData['document_name'], $user->id);

            // Create verification URL
            $verificationUrl = url("/verify-signature/{$signatureHash}");

            // Generate QR Code for verification
            $qrCodePath = $this->generateQRCode($verificationUrl, $signatureHash);

            // Process document based on type
            $processedPath = null;
            $formFieldsFilled = false;

            if (strtolower($file->getClientOriginalExtension()) === 'pdf') {
                $processedPath = $this->processPdfDocument(
                    storage_path('app/public/' . $originalPath),
                    $formFields,
                    $signatureHash,
                    $qrCodePath,
                    $verificationUrl
                );
                $formFieldsFilled = !empty(array_filter($formFields));
            }

            // Prepare metadata
            $metadata = [
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'form_fields' => $formFields,
                'processing_timestamp' => now()->toISOString(),
                'original_dimensions' => $this->getDocumentDimensions($file)
            ];

            return [
                'original_path' => $originalPath,
                'document_path' => $processedPath ?: $originalPath,
                'original_filename' => $file->getClientOriginalName(),
                'signature_hash' => $signatureHash,
                'verification_url' => $verificationUrl,
                'qr_code_path' => $qrCodePath,
                'metadata' => $metadata,
                'form_fields_filled' => $formFieldsFilled,
                'status' => 'success'
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error processing document: ' . $e->getMessage());
        }
    }

    /**
     * Process PDF document with QR code positioning at ${qrcode} placeholder
     */
    private function processPdfDocument($originalPdfPath, $formFields, $signatureHash, $qrCodePath, $verificationUrl)
    {
        try {
            // Extract PDF text to check for template variables and QR code placeholder
            $pdfContent = $this->extractPdfText($originalPdfPath);
            $hasTemplateVars = $this->hasTemplateVariables($pdfContent);
            $hasQRCodePlaceholder = $this->hasQRCodePlaceholder($pdfContent);
            $hasFormFields = !empty(array_filter($formFields));

            if ($hasTemplateVars || $hasFormFields || $hasQRCodePlaceholder) {
                return $this->fillPdfWithFormFieldsAndQRCode($originalPdfPath, $formFields, $signatureHash, $qrCodePath, $verificationUrl, $pdfContent);
            } else {
                return $this->addQrCodeToPdf($originalPdfPath, $signatureHash, $qrCodePath);
            }
        } catch (\Exception $e) {
            \Log::error('PDF processing failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fill PDF with form fields and replace ${qrcode} placeholder with actual QR code
     */
    private function fillPdfWithFormFieldsAndQRCode($originalPdfPath, $formFields, $signatureHash, $qrCodePath, $verificationUrl, $pdfContent)
    {
        try {
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($originalPdfPath);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                if ($size['width'] > $size['height']) {
                    $pdf->AddPage('L', [$size['width'], $size['height']]);
                } else {
                    $pdf->AddPage('P', [$size['width'], $size['height']]);
                }

                $pdf->useTemplate($templateId);

                // Fill form fields and replace QR code placeholder on first page
                if ($pageNo === 1) {
                    $this->fillFormFieldsWithExactPositioning($pdf, $formFields, $size);
                    $this->replaceQRCodePlaceholder($pdf, $qrCodePath, $size, $pdfContent);
                }
            }

            $processedFilename = 'documents/signed/' . $signatureHash . '_signed.pdf';
            $processedPath = storage_path('app/public/' . $processedFilename);

            $directory = dirname($processedPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $pdf->Output('F', $processedPath);
            return $processedFilename;
        } catch (\Exception $e) {
            throw new \Exception('Failed to process PDF with form fields and QR code: ' . $e->getMessage());
        }
    }

    /**
     * Fill form fields with exact positioning based on official document template
     */
    private function fillFormFieldsWithExactPositioning($pdf, $formFields, $pageSize)
    {
        // Set font and color for form fields
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        // Based on the official document template layout (A4 size: 210mm x 297mm)
        // Positioning sesuai dengan template surat desa yang diberikan

        // Date in top right corner - "Sidoarjo, ${tanggal_surat}"
        if (!empty($formFields['tanggal_surat'])) {
            $dateFormatted = \Carbon\Carbon::parse($formFields['tanggal_surat'])->format('d F Y');
            $pdf->SetXY(140, 50); // Posisi setelah "Sidoarjo, "
            $pdf->Cell(80, 4, $dateFormatted, 0, 0, 'L');
        }

        // Left column positioning - sesuai template surat
        $leftColumnX = 17; // Margin kiri
        $labelWidth = 25; // Lebar untuk label
        $colonX = $leftColumnX + $labelWidth; // Posisi titik dua
        $valueX = $colonX + 5; // Posisi nilai setelah titik dua
        $startY = 64; // Y awal untuk field pertama
        $lineHeight = 8; // Jarak antar baris

        // Nomor field - ${nomor}
        if (!empty($formFields['nomor'])) {
            $pdf->SetXY($leftColumnX, $startY);
            $pdf->SetXY($valueX, $startY);
            $pdf->Cell(100, 4, $formFields['nomor'], 0, 0, 'L');
        }

        // Sifat field - ${sifat}
        if (!empty($formFields['sifat'])) {
            $sifatY = $startY + $lineHeight;
            $pdf->SetXY($valueX, $sifatY);
            $pdf->Cell(100, 4, $formFields['sifat'], 0, 0, 'L');
        }

        // Lampiran field - ${lampiran}
        if (!empty($formFields['lampiran'])) {
            $lampiranY = $startY + ($lineHeight * 2);
            $pdf->SetXY($valueX, $lampiranY);
            $pdf->Cell(100, 4, $formFields['lampiran'], 0, 0, 'L');
        }

        // Perihal field - ${perihal}
        if (!empty($formFields['perihal'])) {
            $perihalY = $startY + ($lineHeight * 3);
            $pdf->SetXY($valueX, $perihalY);

            // Handle long perihal text with word wrap
            $perihalText = $formFields['perihal'];
            if (strlen($perihalText) > 60) {
                $pdf->SetXY($valueX, $perihalY);
                $pdf->MultiCell(120, 4, $perihalText, 0, 'L');
            } else {
                $pdf->Cell(120, 4, $perihalText, 0, 0, 'L');
            }
        }
    }

    /**
     * Replace ${qrcode} placeholder with actual QR code image
     */
    private function replaceQRCodePlaceholder($pdf, $qrCodePath, $pageSize, $pdfContent = '')
    {
        if (!$qrCodePath || !Storage::disk('public')->exists($qrCodePath)) {
            return;
        }

        $qrCodeFullPath = storage_path('app/public/' . $qrCodePath);

        // Check if ${qrcode} placeholder exists in the PDF content
        $hasQRCodePlaceholder = $this->hasQRCodePlaceholder($pdfContent);

        if ($hasQRCodePlaceholder) {
            // Position QR code where ${qrcode} placeholder should be
            $this->positionQRCodeAtPlaceholderLocation($pdf, $qrCodeFullPath, $pageSize);
        } else {
            // Fallback: position QR code in signature area
            $this->positionQRCodeInSignatureArea($pdf, $qrCodeFullPath, $pageSize);
        }
    }

    /**
     * Position QR code at the exact location where ${qrcode} placeholder should be
     * Based on the official document template signature area
     */
    private function positionQRCodeAtPlaceholderLocation($pdf, $qrCodeFullPath, $pageSize)
    {
        // QR code size - sesuai dengan ruang yang tersedia di area tanda tangan
        $qrSize = 15; // 15mm QR code size

        // Positioning berdasarkan template surat desa
        // QR code ditempatkan di area tanda tangan, sebelah kiri dari nama
        // Posisi: di bawah "KEPALA DESA KEBONSARI" dan di samping nama "MOHAMMAD CHUZAINI"

        $qrX = 100; // X position - di area tanda tangan kiri
        $qrY = 160; // Y position - sejajar dengan area nama kepala desa

        // Add QR code image at the placeholder position
        $pdf->Image($qrCodeFullPath, $qrX, $qrY, $qrSize, $qrSize);

        // Add verification text below QR code (smaller and lighter)
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY($qrX - 2, $qrY + $qrSize + 1);
        $pdf->Cell($qrSize + 3, 2, 'Scan untuk verifikasi', 0, 0, 'C');

        // Reset font and color
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Position QR code in signature area (fallback method)
     */
    private function positionQRCodeInSignatureArea($pdf, $qrCodeFullPath, $pageSize)
    {
        // QR code size
        $qrSize = 15; // 15mm QR code size

        // Position in signature area based on the document template
        // Place it in the signature area, left side of the name
        $qrX = 95; // X position
        $qrY = 235; // Y position - dalam area tanda tangan

        // Add QR code image
        $pdf->Image($qrCodeFullPath, $qrX, $qrY, $qrSize, $qrSize);

        // Add verification text below QR code
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetXY($qrX - 2, $qrY + $qrSize + 1);
        $pdf->Cell($qrSize + 4, 2, 'Scan untuk verifikasi', 0, 0, 'C');

        // Reset font
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Add QR code to existing PDF without form fields (fallback method)
     */
    public function addQrCodeToPdf($originalPdfPath, $signatureHash, $qrCodePath)
    {
        try {
            // First check if PDF contains ${qrcode} placeholder
            $pdfContent = $this->extractPdfText($originalPdfPath);

            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($originalPdfPath);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                if ($size['width'] > $size['height']) {
                    $pdf->AddPage('L', [$size['width'], $size['height']]);
                } else {
                    $pdf->AddPage('P', [$size['width'], $size['height']]);
                }

                $pdf->useTemplate($templateId);

                // Add QR code only to first page
                if ($pageNo === 1) {
                    if ($this->hasQRCodePlaceholder($pdfContent)) {
                        $this->positionQRCodeAtPlaceholderLocation($pdf, storage_path('app/public/' . $qrCodePath), $size);
                    } else {
                        $this->positionQRCodeInSignatureArea($pdf, storage_path('app/public/' . $qrCodePath), $size);
                    }
                }
            }

            $processedFilename = 'documents/signed/' . $signatureHash . '_qr.pdf';
            $processedPath = storage_path('app/public/' . $processedFilename);

            $directory = dirname($processedPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $pdf->Output('F', $processedPath);
            return $processedFilename;
        } catch (\Exception $e) {
            throw new \Exception('Failed to add QR code to PDF: ' . $e->getMessage());
        }
    }

    /**
     * Extract text from PDF to check for template variables
     */
    private function extractPdfText($pdfPath)
    {
        try {
            // Using simple file_get_contents to check for placeholders
            $content = file_get_contents($pdfPath);
            return $content;
        } catch (\Exception $e) {
            // Fallback: try with PDF parser if available
            try {
                if (class_exists('\Smalot\PdfParser\Parser')) {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($pdfPath);
                    return $pdf->getText();
                }
            } catch (\Exception $e2) {
                // Silent fail
            }
            return '';
        }
    }

    /**
     * Check if PDF contains template variables
     */
    private function hasTemplateVariables($content)
    {
        $templateVars = ['${nomor}', '${sifat}', '${lampiran}', '${perihal}', '${tanggal_surat}'];

        foreach ($templateVars as $var) {
            if (strpos($content, $var) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if PDF contains ${qrcode} placeholder specifically
     */
    private function hasQRCodePlaceholder($content)
    {
        return strpos($content, '${qrcode}') !== false;
    }

    /**
     * Get document dimensions
     */
    private function getDocumentDimensions($file)
    {
        try {
            if (strtolower($file->getClientOriginalExtension()) === 'pdf') {
                return ['width' => 210, 'height' => 297, 'unit' => 'mm'];
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate unique signature hash
     */
    private function generateSignatureHash($documentName, $userId)
    {
        $timestamp = now()->timestamp;
        $randomString = Str::random(32);
        return hash('sha256', $documentName . $userId . $timestamp . $randomString);
    }

    /**
     * Generate QR Code for verification
     */
    public function generateQRCode($data, $hash)
    {
        try {
            $qrCodePath = 'qrcodes/' . $hash . '.png';

            $qrCode = QrCode::format('png')
                ->size(200)
                ->margin(1)
                ->errorCorrection('M')
                ->generate($data);

            Storage::disk('public')->put($qrCodePath, $qrCode);
            return $qrCodePath;
        } catch (\Exception $e) {
            return $this->generateQRCodeFallback($data, $hash);
        }
    }

    /**
     * Fallback QR Code generation
     */
    private function generateQRCodeFallback($data, $hash)
    {
        try {
            $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/";
            $params = [
                'data' => $data,
                'size' => '200x200',
                'format' => 'png',
                'ecc' => 'M'
            ];

            $url = $qrCodeUrl . '?' . http_build_query($params);
            $qrCodeContent = file_get_contents($url);

            if ($qrCodeContent !== false) {
                $qrCodePath = 'qrcodes/' . $hash . '.png';
                Storage::disk('public')->put($qrCodePath, $qrCodeContent);
                return $qrCodePath;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Verify digital signature
     */
    public function verifySignature($hash)
    {
        $signature = DigitalSignature::where('signature_hash', $hash)
            ->with('signer')
            ->first();

        if (!$signature) {
            return [
                'valid' => false,
                'message' => 'Tanda tangan digital tidak ditemukan atau tidak valid.',
                'signature' => null
            ];
        }

        $isValid = $signature->isValid();
        $message = $isValid
            ? 'Tanda tangan digital valid dan dokumen autentik.'
            : 'Tanda tangan digital tidak valid atau dokumen telah diubah.';

        return [
            'valid' => $isValid,
            'message' => $message,
            'signature' => $signature
        ];
    }

    /**
     * Generate alternative QR code formats
     */
    public function generateQRCodeSVG($data, $hash)
    {
        try {
            $qrCodePath = 'qrcodes/' . $hash . '.svg';

            $qrCode = QrCode::format('svg')
                ->size(200)
                ->margin(1)
                ->generate($data);

            Storage::disk('public')->put($qrCodePath, $qrCode);
            return $qrCodePath;
        } catch (\Exception $e) {
            return null;
        }
    }
}
