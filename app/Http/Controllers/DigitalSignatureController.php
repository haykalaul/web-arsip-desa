<?php

namespace App\Http\Controllers;

use App\Models\DigitalSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class DigitalSignatureController extends Controller
{
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
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB max
            'document_name' => 'required|string|max:255',
            'document_type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000'
        ]);

        try {
            // Store the uploaded document
            $file = $request->file('document');
            $filename = time() . '_' . Str::slug($request->document_name) . '.' . $file->getClientOriginalExtension();
            $documentPath = $file->storeAs('documents/signed', $filename, 'public');

            // Generate signature hash
            $signatureHash = DigitalSignature::generateSignatureHash(
                $request->document_name,
                Auth::id()
            );

            // Create digital signature record
            $digitalSignature = DigitalSignature::create([
                'document_name' => $request->document_name,
                'document_path' => $documentPath,
                'original_filename' => $file->getClientOriginalName(),
                'signature_hash' => $signatureHash,
                'barcode_data' => '',
                'barcode_path' => '',
                'verification_url' => url("/verify-signature/{$signatureHash}"),
                'signed_at' => now(),
                'signed_by' => Auth::id(),
                'document_type' => $request->document_type,
                'description' => $request->description,
                'metadata' => [
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            ]);

            // Generate QR Code for verification
            $qrCodeData = $digitalSignature->verification_url;
            $qrCodePath = 'qrcodes/' . $signatureHash . '.png';

            // Create QR code and save it
            $qrCode = QrCode::format('png')
                ->size(200)
                ->margin(2)
                ->generate($qrCodeData);

            Storage::disk('public')->put($qrCodePath, $qrCode);

            // Update the digital signature with barcode info
            $digitalSignature->update([
                'barcode_data' => $qrCodeData,
                'barcode_path' => $qrCodePath
            ]);

            return redirect()->route('digital-signatures.show', $digitalSignature)
                ->with('success', 'Dokumen berhasil ditandatangani secara digital!');
        } catch (\Exception $e) {
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
        return view('pages.digital-signatures.show', compact('digitalSignature'));
    }

    /**
     * Verify digital signature
     */
    public function verify($hash)
    {
        $signature = DigitalSignature::where('signature_hash', $hash)
            ->with('signer')
            ->first();

        if (!$signature) {
            return view('pages.digital-signatures.verify', [
                'valid' => false,
                'message' => 'Tanda tangan digital tidak ditemukan atau tidak valid.',
                'signature' => null
            ]);
        }

        $isValid = $signature->isValid();
        $message = $isValid
            ? 'Tanda tangan digital valid dan dokumen autentik.'
            : 'Tanda tangan digital tidak valid atau dokumen telah diubah.';

        return view('pages.digital-signatures.verify', [
            'valid' => $isValid,
            'message' => $message,
            'signature' => $signature
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

        return response()->download($filePath, $digitalSignature->original_filename);
    }

    /**
     * Generate certificate of authenticity
     */
    public function generateCertificate(DigitalSignature $digitalSignature)
    {
        $digitalSignature->load('signer');

        $pdf = Pdf::loadView('digital-signatures.certificate', compact('digitalSignature'));

        return $pdf->download('Sertifikat_Keaslian_' . Str::slug($digitalSignature->document_name) . '.pdf');
    }

    /**
     * Revoke digital signature
     */
    public function revoke(DigitalSignature $digitalSignature)
    {
        $digitalSignature->update(['status' => 'revoked']);

        return back()->with('success', 'Tanda tangan digital telah dicabut.');
    }

    /**
     * API endpoint for verification
     */
    public function apiVerify($hash)
    {
        $signature = DigitalSignature::where('signature_hash', $hash)
            ->with('signer:id,name,email')
            ->first();

        if (!$signature) {
            return response()->json([
                'valid' => false,
                'message' => 'Digital signature not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'valid' => $signature->isValid(),
            'message' => $signature->isValid() ? 'Valid digital signature' : 'Invalid or revoked signature',
            'data' => [
                'document_name' => $signature->document_name,
                'signed_at' => $signature->signed_at->toISOString(),
                'signed_by' => $signature->signer->name,
                'status' => $signature->status,
                'verification_url' => $signature->verification_url
            ]
        ]);
    }
}
