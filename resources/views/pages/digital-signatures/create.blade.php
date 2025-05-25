@extends('layout.main')

@section('title', 'Tambah Tanda Tangan Digital')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
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
                                Format yang didukung: PDF, DOC, DOCX, JPG, JPEG, PNG. Maksimal 5MB.
                            </small>
                            @error('document')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Informasi:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Setelah ditandatangani, dokumen akan mendapat hash unik untuk verifikasi</li>
                                <li>QR Code akan dibuat otomatis untuk memudahkan verifikasi</li>
                                <li>Tanda tangan digital menjamin keaslian dan integritas dokumen</li>
                            </ul>
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
</script>
@endsection
