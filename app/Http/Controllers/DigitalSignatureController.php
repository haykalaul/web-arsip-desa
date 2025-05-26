<?php

namespace App\Http\Controllers;

use App\Models\DigitalSignature;
use App\Services\DigitalSignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Section;
use setasign\Fpdi\Tcpdf\Fpdi;
use setasign\Fpdi\PdfReader;

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
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB max
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
            // Use service to process document
            $result = $this->digitalSignatureService->processDocument(
                $request->file('document'),
                $request->only(['document_name', 'document_type', 'description']),
                Auth::user()
            );

            // Process document with QR code injection
            $processedDocumentPath = $this->processDocumentWithQR(
                $request->file('document'),
                $result,
                $request->all()
            );

            // Create digital signature record
            $digitalSignature = DigitalSignature::create([
                'document_name' => $request->document_name,
                'document_path' => $processedDocumentPath ?: $result['document_path'],
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
                    'nomor' => $request->nomor,
                    'sifat' => $request->sifat,
                    'lampiran' => $request->lampiran,
                    'perihal' => $request->perihal,
                    'tanggal_surat' => $request->tanggal_surat,
                ]),
                'status' => 'active'
            ]);

            return redirect()->route('digital-signatures.show', $digitalSignature)
                ->with('success', 'Dokumen berhasil ditandatangani secara digital dengan QR Code!');
        } catch (\Exception $e) {
            \Log::error('Digital signature creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memproses dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Process document and inject QR code
     */
    private function processDocumentWithQR($file, $result, $formData)
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());

            if (in_array($extension, ['doc', 'docx'])) {
                return $this->processWordDocument($file, $result, $formData);
            } elseif ($extension === 'pdf') {
                return $this->processPDFDocument($file, $result, $formData);
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Document processing failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process Word document and inject QR code
     */
    private function processWordDocument($file, $result, $formData)
    {
        $tempPath = $file->storeAs('temp', uniqid() . '.' . $file->getClientOriginalExtension());
        $fullTempPath = storage_path('app/' . $tempPath);

        try {
            $phpWord = IOFactory::load($fullTempPath);
            $qrCodeFullPath = storage_path('app/public/' . $result['qr_code_path']);

            foreach ($phpWord->getSections() as $section) {
                $this->replaceTextInSection($section, $formData, $result, $qrCodeFullPath);
            }

            $processedFilename = 'signed_' . time() . '_' . Str::slug($formData['document_name']) . '.docx';
            $processedPath = 'documents/processed/' . $processedFilename;
            $fullProcessedPath = storage_path('app/public/' . $processedPath);

            $directory = dirname($fullProcessedPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($fullProcessedPath);

            Storage::delete($tempPath);

            return $processedPath;
        } catch (\Exception $e) {
            if (Storage::exists($tempPath)) {
                Storage::delete($tempPath);
            }
            throw $e;
        }
    }

    /**
     * Process PDF document and inject QR code
     */
    private function processPDFDocument($file, $result, $formData)
    {
        $tempPath = $file->storeAs('temp', uniqid() . '.pdf');
        $fullTempPath = storage_path('app/' . $tempPath);

        try {
            // Read original PDF content as text to check for template variables
            $pdfContent = $this->extractPDFText($fullTempPath);

            // Check if PDF contains template variables
            $hasTemplateVariables = $this->hasTemplateVariables($pdfContent);

            if ($hasTemplateVariables) {
                // Process PDF with template variable replacement
                return $this->processPDFWithTemplates($fullTempPath, $result, $formData, $tempPath);
            } else {
                // Simply add QR code to existing PDF
                return $this->addQRCodeToPDF($fullTempPath, $result, $formData, $tempPath);
            }
        } catch (\Exception $e) {
            if (Storage::exists($tempPath)) {
                Storage::delete($tempPath);
            }
            throw $e;
        }
    }

    /**
     * Extract text from PDF
     */
    private function extractPDFText($pdfPath)
    {
        try {
            // Using simple PDF text extraction
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            return $pdf->getText();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Check if PDF contains template variables
     */
    private function hasTemplateVariables($content)
    {
        $templateVars = ['${nomor}', '${sifat}', '${lampiran}', '${perihal}', '${tanggal_surat}', '${qrcode}'];

        foreach ($templateVars as $var) {
            if (strpos($content, $var) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process PDF with template variables using TCPDF
     */
    private function processPDFWithTemplates($pdfPath, $result, $formData, $tempPath)
    {
        try {
            // Create new PDF with replaced content
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator('Digital Signature System');
            $pdf->SetAuthor('Desa Kebonsari');
            $pdf->SetTitle($formData['document_name']);

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Set margins
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);

            // Set font
            $pdf->SetFont('helvetica', '', 10);

            // Add a page
            $pdf->AddPage();

            // Generate HTML content based on the exact template format
            $html = $this->generatePDFContentFromTemplate($formData, $result);

            // Write HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            // Add QR Code at the bottom right position (after signature)
            $qrCodePath = storage_path('app/public/' . $result['qr_code_path']);
            if (file_exists($qrCodePath)) {
                // Position QR code at bottom right, after signature section
                $pdf->Image($qrCodePath, 160, 245, 20, 20, 'PNG');

                // Add verification text below QR code
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetXY(155, 267);
                $pdf->Cell(30, 4, 'Scan untuk verifikasi', 0, 0, 'C');
            }

            // Save processed PDF
            $processedFilename = 'signed_' . time() . '_' . Str::slug($formData['document_name']) . '.pdf';
            $processedPath = 'documents/processed/' . $processedFilename;
            $fullProcessedPath = storage_path('app/public/' . $processedPath);

            $directory = dirname($fullProcessedPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $pdf->Output($fullProcessedPath, 'F');

            Storage::delete($tempPath);

            return $processedPath;
        } catch (\Exception $e) {
            throw new \Exception('Failed to process PDF with templates: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF content from template with exact formatting matching the uploaded image
     */
    private function generatePDFContentFromTemplate($formData, $result)
    {
        $tanggalSurat = $formData['tanggal_surat'] ?
            \Carbon\Carbon::parse($formData['tanggal_surat'])->format('d F Y') :
            now()->format('d F Y');

        return '
        <style>
            body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.3; }
            .header { text-align: center; margin-bottom: 25px; }
            .header h1 { font-size: 12px; font-weight: bold; margin: 3px 0; }
            .header h2 { font-size: 11px; font-weight: bold; margin: 2px 0; }
            .header p { font-size: 8px; margin: 1px 0; }
            .underline { border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 15px; }
            .letter-info { margin: 20px 0; }
            .letter-info table { width: 100%; }
            .letter-info td { padding: 2px 0; vertical-align: top; font-size: 9px; }
            .date-location { text-align: right; margin: 10px 0; font-size: 9px; }
            .recipient { margin: 15px 0; font-size: 9px; }
            .content { margin: 15px 0; text-align: justify; font-size: 9px; line-height: 1.4; }
            .signature-section { margin-top: 40px; text-align: center; font-size: 9px; }
            .electronic-signature {
                font-size: 7px;
                text-align: justify;
                margin-bottom: 15px;
                border: 1px solid #333;
                padding: 8px;
                background-color: #f9f9f9;
                line-height: 1.2;
            }
        </style>

        <div class="electronic-signature">
            Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh BSrE sesuai dengan Undang Undang No 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik, tandatangan secara elektronik memiliki kekuatan hukum dan akibat hukum yang sah.
        </div>

        <div class="header">
            <h1>PEMERINTAH KABUPATEN SIDOARJO</h1>
            <h2>DESA KEBONSARI, KEC. CANDI</h2>
            <p>JL. PANDAWA No.26 RT 02 RW 02 DESA KEBONSARI KECAMATAN CANDI</p>
            <p>Email : pemkebonsari@gmail.com Website : sidoarjokab.go.id</p>
        </div>

        <div class="underline"></div>

        <table style="width: 100%; margin: 20px 0;">
            <tr>
                <td style="width: 50px; font-size: 9px;">Nomor</td>
                <td style="width: 10px; font-size: 9px;">:</td>
                <td style="font-size: 9px;">' . ($formData['nomor'] ?? '${nomor}') . '</td>
                <td style="text-align: right; font-size: 9px;">Sidoarjo, ' . $tanggalSurat . '</td>
            </tr>
            <tr><td colspan="4" style="height: 3px;"></td></tr>
            <tr>
                <td style="font-size: 9px;">Sifat</td>
                <td style="font-size: 9px;">:</td>
                <td style="font-size: 9px;">' . ($formData['sifat'] ?? '${sifat}') . '</td>
                <td></td>
            </tr>
            <tr><td colspan="4" style="height: 3px;"></td></tr>
            <tr>
                <td style="font-size: 9px;">Lampiran</td>
                <td style="font-size: 9px;">:</td>
                <td style="font-size: 9px;">' . ($formData['lampiran'] ?? '${lampiran}') . '</td>
                <td></td>
            </tr>
            <tr><td colspan="4" style="height: 3px;"></td></tr>
            <tr>
                <td style="font-size: 9px;">Perihal</td>
                <td style="font-size: 9px;">:</td>
                <td style="font-size: 9px;">' . ($formData['perihal'] ?? '${perihal}') . '</td>
                <td></td>
            </tr>
        </table>

        <div style="margin: 20px 0;">
            <p style="font-size: 9px; margin-bottom: 5px;">Kepada</p>
            <p style="font-size: 9px; margin-bottom: 3px;">Yth</p>
            <p style="font-size: 9px; margin-bottom: 3px;">Bapak/Ibu.</p>
            <p style="font-size: 9px; margin-bottom: 3px;">_______________________</p>
            <p style="font-size: 9px; margin-bottom: 10px;">di</p>
            <p style="font-size: 9px; margin-left: 200px;">SIDOARJO</p>
        </div>

        <div class="content">
            <p style="margin-bottom: 10px;">Assalamu\'alaikum wr wb</p>

            <p style="margin-bottom: 10px;">Dalam rangka memperingati HUT NU yang ke 100, dimohon kepada Bapak Ketua RT, menyampaikan kepada Warga untuk memberikan bantuan NASI BUNGKUS sebanyak 2 BUNGKUS / Rumah.</p>

            <p style="margin-bottom: 5px;">pada :</p>
            <p style="margin-bottom: 3px;">HARI&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: SELASA</p>
            <p style="margin-bottom: 3px;">TANGGAL&nbsp;&nbsp;: 07 FEBRUARI 2023</p>
            <p style="margin-bottom: 10px;">JAM&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: 12.00 WIB ( SIANG )</p>

            <p style="margin-bottom: 10px;">Yang mana nasi tersebut di kumpulkan di RT Masing-masing dan akan di ambil oleh pengurus NU Kebonsari.</p>

            <p style="margin-bottom: 20px;">Demikian yg dapat disampaikan, atas partisipasi dan bantuannya kami ucapkan terima kasih.</p>
        </div>

        <div style="margin-top: 40px; text-align: center;">
            <p style="margin-bottom: 3px; font-size: 9px;"><strong>KEPALA DESA KEBONSARI</strong></p>
            <p style="margin-bottom: 50px; font-size: 9px;">${qrcode}</p>
            <p style="font-size: 9px;"><u><strong>MOHAMMAD CHUZAINI</strong></u></p>
        </div>';
    }

    /**
     * Add QR code to existing PDF using FPDI - improved positioning
     */
    private function addQRCodeToPDF($pdfPath, $result, $formData, $tempPath)
    {
        try {
            $pdf = new Fpdi();

            // Get page count
            $pageCount = $pdf->setSourceFile($pdfPath);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                // Import page
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                // Add page with same orientation
                if ($size['width'] > $size['height']) {
                    $pdf->AddPage('L', array($size['width'], $size['height']));
                } else {
                    $pdf->AddPage('P', array($size['width'], $size['height']));
                }

                // Use imported page as template
                $pdf->useTemplate($templateId);

                // Add QR code to first page
                if ($pageNo === 1) {
                    $qrCodePath = storage_path('app/public/' . $result['qr_code_path']);
                    if (file_exists($qrCodePath)) {
                        // Position QR code at signature area (bottom right)
                        $qrX = $size['width'] - 50; // 50 units from right edge
                        $qrY = $size['height'] - 60; // 60 units from bottom

                        $pdf->Image($qrCodePath, $qrX, $qrY, 20, 20, 'PNG');

                        // Add verification text
                        $pdf->SetFont('helvetica', '', 7);
                        $pdf->SetXY($qrX - 5, $qrY + 22);
                        $pdf->Cell(30, 4, 'Scan untuk verifikasi', 0, 0, 'C');
                    }
                }
            }

            // Save processed PDF
            $processedFilename = 'signed_' . time() . '_' . Str::slug($formData['document_name']) . '.pdf';
            $processedPath = 'documents/processed/' . $processedFilename;
            $fullProcessedPath = storage_path('app/public/' . $processedPath);

            $directory = dirname($fullProcessedPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $pdf->Output($fullProcessedPath, 'F');

            Storage::delete($tempPath);

            return $processedPath;
        } catch (\Exception $e) {
            throw new \Exception('Failed to add QR code to PDF: ' . $e->getMessage());
        }
    }

    /**
     * Replace text and inject QR code in Word section
     */
    private function replaceTextInSection($section, $formData, $result, $qrCodePath)
    {
        $elements = $section->getElements();

        foreach ($elements as $element) {
            if (method_exists($element, 'getElements')) {
                $this->processElementRecursively($element, $formData, $result, $qrCodePath);
            } elseif (method_exists($element, 'getText')) {
                $text = $element->getText();
                if (is_string($text)) {
                    $newText = $this->replaceTemplateVariables($text, $formData, $result);
                    if ($newText !== $text) {
                        $element->setText($newText);
                    }
                }
            }
        }
    }

    /**
     * Process elements recursively
     */
    private function processElementRecursively($element, $formData, $result, $qrCodePath)
    {
        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $childElement) {
                if (method_exists($childElement, 'getText')) {
                    $text = $childElement->getText();
                    if (is_string($text)) {
                        if (strpos($text, '${qrcode}') !== false) {
                            $this->replaceQRCodeInElement($childElement, $qrCodePath, $element);
                        } else {
                            $newText = $this->replaceTemplateVariables($text, $formData, $result);
                            if ($newText !== $text) {
                                $childElement->setText($newText);
                            }
                        }
                    }
                } elseif (method_exists($childElement, 'getElements')) {
                    $this->processElementRecursively($childElement, $formData, $result, $qrCodePath);
                }
            }
        }
    }

    /**
     * Replace QR code placeholder with image
     */
    private function replaceQRCodeInElement($textElement, $qrCodePath, $parentElement)
    {
        try {
            $textElement->setText('');

            if (file_exists($qrCodePath) && method_exists($parentElement, 'addImage')) {
                $parentElement->addImage($qrCodePath, [
                    'width' => 60,
                    'height' => 60,
                    'positioning' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE,
                ]);
            }
        } catch (\Exception $e) {
            $textElement->setText('[QR Code]');
        }
    }

    /**
     * Replace template variables in text
     */
    private function replaceTemplateVariables($text, $formData, $result)
    {
        $replacements = [
            '${nomor}' => $formData['nomor'] ?? '',
            '${sifat}' => $formData['sifat'] ?? '',
            '${lampiran}' => $formData['lampiran'] ?? '',
            '${perihal}' => $formData['perihal'] ?? '',
            '${tanggal_surat}' => $formData['tanggal_surat'] ?
                \Carbon\Carbon::parse($formData['tanggal_surat'])->format('d F Y') :
                now()->format('d F Y'),
            '${qrcode}' => '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    // ... (rest of the methods remain the same)

    /**
     * Display the specified digital signature
     */
    public function show(DigitalSignature $digitalSignature)
    {
        $digitalSignature->load('signer');
        return view('pages.digital-signatures.show', compact('digitalSignature'));
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

        return response()->download($filePath, $digitalSignature->original_filename);
    }

    /**
     * Generate certificate of authenticity
     */
    public function generateCertificate(DigitalSignature $digitalSignature)
    {
        $digitalSignature->load('signer');

        $pdf = Pdf::loadView('pages.digital-signatures.certificate', compact('digitalSignature'));

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
                'verification_url' => $signature->verification_url
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
                $digitalSignature->update(['barcode_path' => $newQRCodePath]);
                return back()->with('success', 'QR Code berhasil dibuat ulang.');
            } else {
                return back()->with('error', 'Gagal membuat ulang QR Code.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
