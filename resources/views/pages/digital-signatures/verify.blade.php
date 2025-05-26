
@section('title', 'Verifikasi Tanda Tangan Digital')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h3 class="card-title">
                        <i class="fas fa-shield-check mr-2"></i>
                        Verifikasi Tanda Tangan Digital
                    </h3>
                </div>
                <div class="card-body text-center">
                    @if($valid)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h4 class="text-success">Dokumen Valid</h4>
                        <p class="mb-0">{{ $message }}</p>
                    </div>

                    @if($signature)
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Detail Dokumen</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Nama:</strong></td>
                                            <td>{{ $signature->document_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jenis:</strong></td>
                                            <td>{{ $signature->document_type ?: 'Umum' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ditandatangani:</strong></td>
                                            <td>{{ $signature->formatted_signed_date }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Oleh:</strong></td>
                                            <td>{{ $signature->signer->name }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Hash Verifikasi</h6>
                                    <p class="text-monospace small bg-white p-2 rounded">
                                        {{ $signature->signature_hash }}
                                    </p>
                                    <small class="text-muted">
                                        Hash ini menjamin integritas dokumen
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle fa-3x mb-3"></i>
                        <h4 class="text-danger">Dokumen Tidak Valid</h4>
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="fas fa-home mr-1"></i>
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h5>Tentang Verifikasi Digital</h5>
                    <p class="text-muted">
                        Sistem verifikasi tanda tangan digital ini menggunakan teknologi hash SHA-256
                        yang menjamin keaslian dan integritas dokumen. Setiap dokumen memiliki
                        identitas unik yang tidak dapat dipalsukan.
                    </p>

                    <div class="row">
                        <div class="col-md-4 text-center">
                            <i class="fas fa-lock fa-2x text-primary mb-2"></i>
                            <h6>Aman</h6>
                            <small class="text-muted">Menggunakan enkripsi tingkat militer</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-check-double fa-2x text-success mb-2"></i>
                            <h6>Terverifikasi</h6>
                            <small class="text-muted">Keaslian terjamin 100%</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-eye fa-2x text-info mb-2"></i>
                            <h6>Transparan</h6>
                            <small class="text-muted">Dapat diverifikasi kapan saja</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
