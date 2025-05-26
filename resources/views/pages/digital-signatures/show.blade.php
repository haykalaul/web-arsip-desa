@extends('layout.main')

@section('title', 'Detail Tanda Tangan Digital')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-signature mr-2"></i>
                        Detail Tanda Tangan Digital
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Nama Dokumen:</strong></td>
                                    <td>{{ $digitalSignature->document_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>File Asli:</strong></td>
                                    <td>{{ $digitalSignature->original_filename }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Dokumen:</strong></td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $digitalSignature->document_type ?: 'Umum' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Ukuran File:</strong></td>
                                    <td>{{ $digitalSignature->document_size }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ditandatangani Oleh:</strong></td>
                                    <td>{{ $digitalSignature->signer->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $digitalSignature->signer->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal:</strong></td>
                                    <td>{{ $digitalSignature->formatted_signed_date }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($digitalSignature->status === 'active')
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-danger">Dicabut</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h5>QR Code Verifikasi</h5>
                                <img src="{{ asset('storage/' . $digitalSignature->barcode_path) }}"
                                     alt="QR Code Verifikasi"
                                     class="img-fluid border p-2"
                                     style="max-width: 200px;">
                                <p class="text-muted mt-2">
                                    Scan untuk verifikasi keaslian
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($digitalSignature->description)
                    <div class="mt-3">
                        <strong>Deskripsi:</strong>
                        <p class="text-muted">{{ $digitalSignature->description }}</p>
                    </div>
                    @endif

                    <div class="mt-3">
                        <strong>Hash Tanda Tangan:</strong>
                        <p class="text-monospace bg-light p-2 rounded">{{ $digitalSignature->signature_hash }}</p>
                    </div>

                    <div class="mt-3">
                        <strong>URL Verifikasi:</strong>
                        <p>
                            <a href="{{ $digitalSignature->verification_url }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt mr-1"></i>
                                {{ $digitalSignature->verification_url }}
                            </a>
                        </p>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('digital-signatures.download', $digitalSignature) }}"
                       class="btn btn-success">
                        <i class="fas fa-download mr-1"></i>
                        Unduh Dokumen
                    </a>
                    <a href="{{ route('digital-signatures.certificate', $digitalSignature) }}"
                       class="btn btn-warning ml-2">
                        <i class="fas fa-certificate mr-1"></i>
                        Unduh Sertifikat
                    </a>
                    <a href="{{ route('digital-signatures.index') }}"
                       class="btn btn-secondary ml-2">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Kembali
                    </a>
                    @if($digitalSignature->status === 'active' && auth()->user()->id === $digitalSignature->signed_by)
                    <form method="POST"
                          action="{{ route('digital-signatures.revoke', $digitalSignature) }}"
                          class="d-inline ml-2"
                          onsubmit="return confirm('Apakah Anda yakin ingin mencabut tanda tangan digital ini?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-ban mr-1"></i>
                            Cabut Tanda Tangan
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Informasi Keamanan
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-check-circle mr-1"></i> Dokumen Terverifikasi</h6>
                        <p class="mb-0">Dokumen ini telah ditandatangani secara digital dan terjamin keasliannya.</p>
                    </div>

                    <h6>Cara Verifikasi:</h6>
                    <ol class="small">
                        <li>Scan QR Code dengan smartphone</li>
                        <li>Atau kunjungi URL verifikasi</li>
                        <li>Sistem akan menampilkan status keaslian dokumen</li>
                    </ol>

                    <h6>Metadata Dokumen:</h6>
                    <ul class="list-unstyled small">
                        @if($digitalSignature->metadata)
                            <li><strong>Tipe MIME:</strong> {{ $digitalSignature->metadata['mime_type'] ?? 'N/A' }}</li>
                            <li><strong>IP Address:</strong> {{ $digitalSignature->metadata['ip_address'] ?? 'N/A' }}</li>
                            <li><strong>Dibuat:</strong> {{ $digitalSignature->created_at->format('d F Y H:i:s') }}</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

