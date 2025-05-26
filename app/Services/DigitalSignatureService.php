<?php
// app/Services/DigtalSignatureService.php

namespace App\Services;

use App\Models\DigitalSignature;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DigitalSignatureService
{
    /**
     * Process uploaded document and create digital signature
     */
    public function processDocument($file, $documentData, $user)
    {
        try {
            // Store the uploaded document
            $filename = time() . '_' . Str::slug($documentData['document_name']) . '.' . $file->getClientOriginalExtension();
            $documentPath = $file->storeAs('documents/signed', $filename, 'public');

            // Generate signature hash
            $signatureHash = $this->generateSignatureHash($documentData['document_name'], $user->id);

            // Create verification URL
            $verificationUrl = url("/verify-signature/{$signatureHash}");

            // Generate QR Code for verification
            $qrCodePath = $this->generateQRCode($verificationUrl, $signatureHash);

            // Prepare metadata
            $metadata = [
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ];

            return [
                'document_path' => $documentPath,
                'original_filename' => $file->getClientOriginalName(),
                'signature_hash' => $signatureHash,
                'verification_url' => $verificationUrl,
                'qr_code_path' => $qrCodePath,
                'metadata' => $metadata,
                'status' => 'success'
            ];

        } catch (\Exception $e) {
            throw new \Exception('Error processing document: ' . $e->getMessage());
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
    private function generateQRCode($data, $hash)
    {
        try {
            $qrCodePath = 'qrcodes/' . $hash . '.png';

            // Generate QR Code using SimpleSoftwareIO
            $qrCode = QrCode::format('png')
                ->size(200)
                ->margin(2)
                ->errorCorrection('M')
                ->generate($data);

            // Save QR Code to storage
            Storage::disk('public')->put($qrCodePath, $qrCode);

            return $qrCodePath;

        } catch (\Exception $e) {
            // Fallback: use online QR code generator
            return $this->generateQRCodeFallback($data, $hash);
        }
    }

    /**
     * Fallback QR Code generation using online service
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
            // Return null if all methods fail
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
                ->margin(2)
                ->generate($data);

            Storage::disk('public')->put($qrCodePath, $qrCode);

            return $qrCodePath;

        } catch (\Exception $e) {
            return null;
        }
    }
}
