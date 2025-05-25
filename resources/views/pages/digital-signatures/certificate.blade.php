{{-- resources/views/digital-signatures/certificate.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sertifikat Keaslian Digital</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 40px;
            line-height: 1.6;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }

        .header h2 {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 18px;
            font-weight: normal;
        }

        .document-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .info-row {
            display: flex;
            margin-bottom: 10px;
        }

        .info-label {
            width: 200px;
            font-weight: bold;
            color: #495057;
        }

        .info-value {
            flex: 1;
            color: #212529;
        }

        .signature-hash {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
            margin: 20px 0;
        }

        .verification-section {
            border: 2px solid #28a745;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            background-color: #d4edda;
        }

        .verification-section h3 {
            color: #155724;
            margin-top: 0;
        }

        .footer {
            margin-top: 40px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-revoked {
            background-color: #f8d7da;
            color: #721c24;
        }

        .qr-info {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #fff3cd;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>SERTIFIKAT KEASLIAN DIGITAL</h1>
        <h2>Digital Authenticity Certificate</h2>
        <p>Sistem Arsip Desa</p>
    </div>

    <div class="document-info">
        <h3 style="margin-top: 0; color: #007bff;">Informasi Dokumen</h3>

        <div class="info-row">
            <div class="info-label">Nama Dokumen:</div>
            <div class="info-value">{{ $digitalSignature->document_name }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">File Asli:</div>
            <div class="info-value">{{ $digitalSignature->original_filename }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Jenis Dokumen:</div>
            <div class="info-value">{{ $digitalSignature->document_type ?: 'Umum' }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Ukuran File:</div>
            <div class="info-value">{{ $digitalSignature->document_size }}</div>
        </div>

        @if($digitalSignature->description)
        <div class="info-row">
            <div class="info-label">Deskripsi:</div>
            <div class="info-value">{{ $digitalSignature->description }}</div>
        </div>
        @endif
    </div>

    <div class="document-info">
        <h3 style="margin-top: 0; color: #007bff;">Informasi Tanda Tangan</h3>

        <div class="info-row">
            <div class="info-label">Ditandatangani Oleh:</div>
            <div class="info-value">{{ $digitalSignature->signer->name }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Email:</div>
            <div class="info-value">{{ $digitalSignature->signer->email }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Tanggal Tanda Tangan:</div>
            <div class="info-value">{{ $digitalSignature->formatted_signed_date }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Status:</div>
            <div class="info-value">
                <span class="status-badge {{ $digitalSignature->status === 'active' ? 'status-active' : 'status-revoked' }}">
                    {{ $digitalSignature->status === 'active' ? 'AKTIF' : 'DICABUT' }}
                </span>
            </div>
        </div>
    </div>

    <div class="verification-section">
        <h3>🛡️ Verifikasi Keaslian</h3>
        <p><strong>Hash Tanda Tangan Digital:</strong></p>
        <div class="signature-hash">{{ $digitalSignature->signature_hash }}</div>

        <p><strong>URL Verifikasi:</strong></p>
        <p style="color: #007bff; font-weight: bold;">{{ $digitalSignature->verification_url }}</p>

        <div class="qr-info">
            <p><strong>📱 Cara Verifikasi:</strong></p>
            <p>Scan QR Code yang tersedia pada dokumen atau kunjungi URL verifikasi di atas untuk memastikan keaslian dokumen ini.</p>
        </div>
    </div>

    <div style="margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
        <h3 style="color: #007bff; margin-top: 0;">Tentang Tanda Tangan Digital</h3>
        <p>Dokumen ini telah ditandatangani secara digital menggunakan teknologi hash SHA-256 yang menjamin:</p>
        <ul>
            <li><strong>Keaslian (Authenticity):</strong> Memastikan dokumen berasal dari penandatangan yang sah</li>
            <li><strong>Integritas (Integrity):</strong> Memastikan dokumen tidak diubah setelah ditandatangani</li>
            <li><strong>Non-repudiation:</strong> Penandatangan tidak dapat menyangkal telah menandatangani dokumen</li>
        </ul>
    </div>

    @if($digitalSignature->metadata)
    <div class="document-info">
        <h3 style="margin-top: 0; color: #007bff;">Metadata Teknis</h3>

        <div class="info-row">
            <div class="info-label">Tipe MIME:</div>
            <div class="info-value">{{ $digitalSignature->metadata['mime_type'] ?? 'N/A' }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">IP Address:</div>
            <div class="info-value">{{ $digitalSignature->metadata['ip_address'] ?? 'N/A' }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Dibuat Pada:</div>
            <div class="info-value">{{ $digitalSignature->created_at->format('d F Y H:i:s') }}</div>
        </div>
    </div>
    @endif

    <div class="footer">
        <p><strong>Sistem Arsip Desa - Digital Signature System</strong></p>
        <p>Sertifikat ini dibuat secara otomatis pada {{ now()->format('d F Y H:i:s') }}</p>
        <p>Untuk verifikasi lebih lanjut, silakan hubungi administrator sistem.</p>
        <hr style="margin: 20px 0;">
        <p style="font-size: 10px; color: #999;">
            Dokumen ini dilindungi oleh sistem keamanan digital. Pemalsuan atau manipulasi dokumen ini dapat ditindak sesuai hukum yang berlaku.
        </p>
    </div>
</body>

</html>
