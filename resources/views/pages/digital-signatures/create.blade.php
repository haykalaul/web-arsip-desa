@extends ('layout.main')

@section('title', 'Tambah Tanda Tangan Digital')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-signature mr-2"></i>
                        Tambah Tanda Tangan Digital
                    </h3>
                </div>
                <form action="{{ route('digital-signatures.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="document_name">Nama Dokumen <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('document_name') is-invalid @enderror"
                                        id="document_name"
                                        name="document_name"
                                        value="{{ old('document_name') }}"
                                        required>
                                    @error('document_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="document_type">Jenis Dokumen</label>
                                    <select class="form-control @error('document_type') is-invalid @enderror"
                                        id="document_type"
                                        name="document_type">
                                        <option value="">Pilih Jenis Dokumen</option>
                                        <option value="Surat Keterangan" {{ old('document_type') === 'Surat Keterangan' ? 'selected' : '' }}>Surat Keterangan</option>
                                        <option value="Surat Pengantar" {{ old('document_type') === 'Surat Pengantar' ? 'selected' : '' }}>Surat Pengantar</option>
                                        <option value="Surat Keputusan" {{ old('document_type') === 'Surat Keputusan' ? 'selected' : '' }}>Surat Keputusan</option>
                                        <option value="Surat Undangan" {{ old('document_type') === 'Surat Undangan' ? 'selected' : '' }}>Surat Undangan</option>
                                        <option value="Dokumen Lainnya" {{ old('document_type') === 'Dokumen Lainnya' ? 'selected' : '' }}>Dokumen Lainnya</option>
                                    </select>
                                    @error('document_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Template Variables Section -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-edit mr-2"></i>
                                    Variabel Template Dokumen
                                </h5>
                                <small class="text-muted">Isi field berikut jika dokumen Anda menggunakan template dengan variabel ${nomor}, ${sifat}, dll.</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nomor">Nomor Surat</label>
                                            <input type="text"
                                                class="form-control @error('nomor') is-invalid @enderror"
                                                id="nomor"
                                                name="nomor"
                                                value="{{ old('nomor') }}"
                                                placeholder="Contoh: 001/DESA/2023">
                                            @error('nomor')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sifat">Sifat Surat</label>
                                            <select class="form-control @error('sifat') is-invalid @enderror"
                                                id="sifat"
                                                name="sifat">
                                                <option value="">Pilih Sifat Surat</option>
                                                <option value="Biasa" {{ old('sifat') === 'Biasa' ? 'selected' : '' }}>Biasa</option>
                                                <option value="Penting" {{ old('sifat') === 'Penting' ? 'selected' : '' }}>Penting</option>
                                                <option value="Segera" {{ old('sifat') === 'Segera' ? 'selected' : '' }}>Segera</option>
                                                <option value="Rahasia" {{ old('sifat') === 'Rahasia' ? 'selected' : '' }}>Rahasia</option>
                                            </select>
                                            @error('sifat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="lampiran">Lampiran</label>
                                            <input type="text"
                                                class="form-control @error('lampiran') is-invalid @enderror"
                                                id="lampiran"
                                                name="lampiran"
                                                value="{{ old('lampiran') }}"
                                                placeholder="Contoh: 1 berkas">
                                            @error('lampiran')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tanggal_surat">Tanggal Surat</label>
                                            <input type="date"
                                                class="form-control @error('tanggal_surat') is-invalid @enderror"
                                                id="tanggal_surat"
                                                name="tanggal_surat"
                                                value="{{ old('tanggal_surat', date('Y-m-d')) }}">
                                            @error('tanggal_surat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="perihal">Perihal</label>
                                    <input type="text"
                                        class="form-control @error('perihal') is-invalid @enderror"
                                        id="perihal"
                                        name="perihal"
                                        value="{{ old('perihal') }}"
                                        placeholder="Contoh: Undangan Peringatan HUT NU ke-100">
                                    @error('perihal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                id="description"
                                name="description"
                                rows="3"
                                placeholder="Deskripsi singkat tentang dokumen">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="document">Upload Dokumen <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file"
                                    class="custom-file-input @error('document') is-invalid @enderror"
                                    id="document"
                                    name="document"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                    required>
                                <label class="custom-file-label" for="document">Pilih file...</label>
                            </div>
                            <small class="form-text text-muted">
                                Format yang didukung: PDF Maksimal 5MB.
                            </small>
                            @error('document')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Informasi Penggunaan Template:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Jika dokumen Anda menggunakan template dengan variabel seperti <code>${nomor}</code>, <code>${sifat}</code>, <code>${perihal}</code>, dll., isi field yang sesuai di atas</li>
                                <li>Variabel <code>${qrcode}</code> akan diganti otomatis dengan QR Code untuk verifikasi</li>
                                <li>Variabel <code>${tanggal_surat}</code> akan diganti dengan tanggal yang Anda pilih</li>
                                <li>Setelah ditandatangani, dokumen akan mendapat hash unik untuk verifikasi</li>
                                <li>QR Code akan dibuat otomatis dan disematkan ke dalam dokumen</li>
                                <li>Tanda tangan digital menjamin keaslian dan integritas dokumen</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Catatan Penting:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Pastikan dokumen Word (.doc/.docx) Anda menggunakan format yang benar untuk variabel template</li>
                                <li>Gunakan format <code>${nama_variabel}</code> dalam dokumen Anda</li>
                                <li>QR Code akan menggantikan placeholder <code>${qrcode}</code> dalam dokumen</li>
                            </ul>
                        </div>

                        <!-- Preview Template Variables -->
                        <div class="card bg-light" id="template-preview" style="display: none;">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-eye mr-2"></i>
                                    Preview Variabel Template
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Nomor:</small> <span id="preview-nomor">-</span><br>
                                        <small class="text-muted">Sifat:</small> <span id="preview-sifat">-</span><br>
                                        <small class="text-muted">Lampiran:</small> <span id="preview-lampiran">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Perihal:</small> <span id="preview-perihal">-</span><br>
                                        <small class="text-muted">Tanggal:</small> <span id="preview-tanggal">-</span><br>
                                        <small class="text-muted">QR Code:</small> <span class="badge badge-success">Akan dibuat otomatis</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-signature mr-1"></i>
                            Tanda Tangani Dokumen
                        </button>
                        <a href="{{ route('digital-signatures.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Update custom file input label
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        const fileName = e.target.files[0].name;
        const nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });

    // Preview template variables
    function updatePreview() {
        const nomor = document.getElementById('nomor').value || '-';
        const sifat = document.getElementById('sifat').value || '-';
        const lampiran = document.getElementById('lampiran').value || '-';
        const perihal = document.getElementById('perihal').value || '-';
        const tanggal = document.getElementById('tanggal_surat').value || '-';

        document.getElementById('preview-nomor').textContent = nomor;
        document.getElementById('preview-sifat').textContent = sifat;
        document.getElementById('preview-lampiran').textContent = lampiran;
        document.getElementById('preview-perihal').textContent = perihal;
        document.getElementById('preview-tanggal').textContent = tanggal !== '-' ?
            new Date(tanggal).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }) : '-';

        // Show preview if any field is filled
        const hasContent = [nomor, sifat, lampiran, perihal, tanggal].some(val => val !== '-');
        document.getElementById('template-preview').style.display = hasContent ? 'block' : 'none';
    }

    // Add event listeners for preview
    ['nomor', 'sifat', 'lampiran', 'perihal', 'tanggal_surat'].forEach(id => {
        document.getElementById(id).addEventListener('input', updatePreview);
        document.getElementById(id).addEventListener('change', updatePreview);
    });

    // Initial preview update
    updatePreview();
</script>
@endsection
