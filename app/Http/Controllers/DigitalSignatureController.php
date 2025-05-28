<?php

namespace App\Http\Controllers;

use App\Models\DigitalSignature;
use App\Services\DigitalSignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Tcpdf\Fpdi;

class DigitalSignatureController extends Controller
{
    protected $digitalSignatureService;

    public function __construct(DigitalSignatureService $digitalSignatureService)
    {
        $this->digitalSignatureService = $digitalSignatureService;
    }

    /**
     * Display a listing of digital signatures
     */
    public function index()
    {
        $signatures = DigitalSignature::with('signer')
            ->orderByDesc('signed_at')
            ->paginate(10);

        return view('pages.digital-signatures.index', compact('signatures'));
    }

    /**
     * Show the form for creating a new digital signature
     */
    public function create()
    {
        return view('pages.digital-signatures.create');
    }

    /**
     * Store a newly created digital signature
     */
    public function store(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'document_name' => 'required|string|max:255',
            'document_type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'nomor' => 'nullable|string|max:100',
            'sifat' => 'nullable|string|max:100',
            'lampiran' => 'nullable|string|max:100',
            'perihal' => 'nullable|string|max:255',
            'tanggal_surat' => 'nullable|date'
        ]);

        try {
            // Prepare form fields for processing
            $formFields = [
                'nomor' => $request->nomor,
                'sifat' => $request->sifat,
                'lampiran' => $request->lampiran,
                'perihal' => $request->perihal,
                'tanggal_surat' => $request->tanggal_surat
            ];

            // Use service to process document with form fields and QR code placement
            $result = $this->digitalSignatureService->processDocument(
                $request->file('document'),
                $request->only(['document_name', 'document_type', 'description']),
                Auth::user(),
                $formFields
            );

            // Create digital signature record
            $digitalSignature = DigitalSignature::create([
                'document_name' => $request->document_name,
                'document_path' => $result['document_path'],
                'original_filename' => $result['original_filename'],
                'signature_hash' => $result['signature_hash'],
                'barcode_data' => $result['verification_url'],
                'barcode_path' => $result['qr_code_path'],
                'verification_url' => $result['verification_url'],
                'signed_at' => now(),
                'signed_by' => Auth::id(),
                'document_type' => $request->document_type,
                'description' => $request->description,
                'metadata' => array_merge($result['metadata'], [
                    'form_fields' => $formFields,
                    'processing_method' => $result['form_fields_filled'] ? 'template_fill' : 'qr_overlay',
                    'qr_code_positioned' => true
                ]),
                'status' => 'active'
            ]);

            $successMessage = $result['form_fields_filled']
                ? 'Dokumen berhasil ditandatangani secara digital dengan pengisian form otomatis dan QR code!'
                : 'Dokumen berhasil ditandatangani secara digital dengan QR code verifikasi!';

            return redirect()->route('digital-signatures.show', $digitalSignature)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            \Log::error('Digital signature creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memproses dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified digital signature
     */
    public function show(DigitalSignature $digitalSignature)
    {
        $digitalSignature->load('signer');

        // Add additional info about QR code positioning
        $processingInfo = [
            'has_qr_code' => !empty($digitalSignature->barcode_path),
            'processing_method' => $digitalSignature->metadata['processing_method'] ?? 'unknown',
            'qr_positioned' => $digitalSignature->metadata['qr_code_positioned'] ?? false,
            'form_fields_filled' => !empty($digitalSignature->metadata['form_fields'])
        ];

        return view('pages.digital-signatures.show', compact('digitalSignature', 'processingInfo'));
    }

    /**
     * Verify digital signature using service
     */
    public function verify($hash)
    {
        $result = $this->digitalSignatureService->verifySignature($hash);

        return view('pages.digital-signatures.verify', [
            'valid' => $result['valid'],
            'message' => $result['message'],
            'signature' => $result['signature']
        ]);
    }

    /**
     * Download the signed document
     */
    public function download(DigitalSignature $digitalSignature)
    {
        if (!$digitalSignature->isValid()) {
            return back()->with('error', 'Dokumen tidak dapat diunduh karena tidak valid.');
        }

        $filePath = storage_path('app/public/' . $digitalSignature->document_path);

        if (!file_exists($filePath)) {
            return back()->with('error', 'File dokumen tidak ditemukan.');
        }

        // Generate filename with processing info
        $processingMethod = $digitalSignature->metadata['processing_method'] ?? 'signed';
        $downloadFilename = pathinfo($digitalSignature->original_filename, PATHINFO_FILENAME)
            . '_' . $processingMethod . '_signed.'
            . pathinfo($digitalSignature->original_filename, PATHINFO_EXTENSION);

        return response()->download($filePath, $downloadFilename);
    }

    /**
     * Generate certificate of authenticity
     */
    public function generateCertificate(DigitalSignature $digitalSignature)
    {
        $digitalSignature->load('signer');

        $certificateData = [
            'digitalSignature' => $digitalSignature,
            'qr_code_info' => [
                'positioned' => $digitalSignature->metadata['qr_code_positioned'] ?? false,
                'verification_url' => $digitalSignature->verification_url,
                'hash' => $digitalSignature->signature_hash
            ]
        ];

        $pdf = Pdf::loadView('pages.digital-signatures.certificate', $certificateData);

        return $pdf->download('Sertifikat_Keaslian_' . Str::slug($digitalSignature->document_name) . '.pdf');
    }

    /**
     * Revoke digital signature
     */
    public function revoke(DigitalSignature $digitalSignature)
    {
        $digitalSignature->update([
            'status' => 'revoked',
            'metadata' => array_merge($digitalSignature->metadata ?? [], [
                'revoked_at' => now()->toISOString(),
                'revoked_by' => Auth::id(),
                'revocation_reason' => 'Manual revocation'
            ])
        ]);

        return back()->with('success', 'Tanda tangan digital telah dicabut.');
    }

    /**
     * API endpoint for verification
     */
    public function apiVerify($hash)
    {
        $result = $this->digitalSignatureService->verifySignature($hash);

        if (!$result['signature']) {
            return response()->json([
                'valid' => false,
                'message' => 'Digital signature not found',
                'data' => null
            ], 404);
        }

        $signature = $result['signature'];

        return response()->json([
            'valid' => $result['valid'],
            'message' => $result['valid'] ? 'Valid digital signature' : 'Invalid or revoked signature',
            'data' => [
                'document_name' => $signature->document_name,
                'signed_at' => $signature->signed_at->toISOString(),
                'signed_by' => $signature->signer->name,
                'status' => $signature->status,
                'verification_url' => $signature->verification_url,
                'qr_code_positioned' => $signature->metadata['qr_code_positioned'] ?? false,
                'processing_method' => $signature->metadata['processing_method'] ?? 'unknown',
                'has_form_fields' => !empty($signature->metadata['form_fields'])
            ]
        ]);
    }

    /**
     * Regenerate QR Code (if needed)
     */
    public function regenerateQRCode(DigitalSignature $digitalSignature)
    {
        try {
            $newQRCodePath = $this->digitalSignatureService->generateQRCodeSVG(
                $digitalSignature->verification_url,
                $digitalSignature->signature_hash
            );

            if ($newQRCodePath) {
                // Update signature record with new QR code
                $digitalSignature->update([
                    'barcode_path' => $newQRCodePath,
                    'metadata' => array_merge($digitalSignature->metadata ?? [], [
                        'qr_code_regenerated_at' => now()->toISOString(),
                        'qr_code_regenerated_by' => Auth::id()
                    ])
                ]);

                // Reprocess document with new QR code if it's a template-based document
                if (($digitalSignature->metadata['processing_method'] ?? '') === 'template_fill') {
                    $this->reprocessDocumentWithNewQRCode($digitalSignature, $newQRCodePath);
                }

                return back()->with('success', 'QR Code berhasil dibuat ulang dan dokumen telah diperbarui.');
            } else {
                return back()->with('error', 'Gagal membuat ulang QR Code.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reprocess document with new QR code
     */
    private function reprocessDocumentWithNewQRCode(DigitalSignature $digitalSignature, $newQRCodePath)
    {
        try {
            // Get original form fields from metadata
            $formFields = $digitalSignature->metadata['form_fields'] ?? [];

            // Get original document path
            $originalPath = storage_path('app/public/documents/originals/') .
                pathinfo($digitalSignature->document_path, PATHINFO_BASENAME);

            if (file_exists($originalPath)) {
                // Reprocess the document with new QR code
                $result = $this->digitalSignatureService->processDocument(
                    new \Illuminate\Http\UploadedFile($originalPath, $digitalSignature->original_filename),
                    [
                        'document_name' => $digitalSignature->document_name,
                        'document_type' => $digitalSignature->document_type,
                        'description' => $digitalSignature->description
                    ],
                    $digitalSignature->signer,
                    $formFields
                );

                // Update document path if processing was successful
                if ($result['status'] === 'success') {
                    $digitalSignature->update(['document_path' => $result['document_path']]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to reprocess document with new QR code: ' . $e->getMessage());
        }
    }

    /**
     * Preview QR code positioning
     */
    public function previewQRCodePosition(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf',
            'position' => 'nullable|string|in:bottom-right,bottom-center,custom'
        ]);

        try {
            // Generate temporary QR code for preview
            $tempHash = 'preview_' . uniqid();
            $tempUrl = url("/verify-signature/{$tempHash}");
            $tempQRPath = $this->digitalSignatureService->generateQRCode($tempUrl, $tempHash);

            // Process document with preview QR code
            $previewPath = $this->createPreviewDocument($request->file('document'), $tempQRPath);

            return response()->json([
                'success' => true,
                'preview_url' => asset('storage/' . $previewPath),
                'message' => 'Preview berhasil dibuat'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create preview document with QR code positioning
     */
    private function createPreviewDocument($file, $qrCodePath)
    {
        $tempFilename = 'preview_' . uniqid() . '.pdf';
        $tempPath = $file->storeAs('temp', $tempFilename, 'public');

        // Add QR code to preview
        $previewPath = $this->digitalSignatureService->addQrCodeToPdf(
            storage_path('app/public/' . $tempPath),
            'preview',
            $qrCodePath
        );

        return $previewPath;
    }
}
