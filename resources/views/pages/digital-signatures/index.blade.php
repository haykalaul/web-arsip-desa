@extends('layout.main')

@section('title', 'Tanda Tangan Digital')

@push('script')
<script>
    $(document).ready(function() {
        $('.custom-file-input').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
    });
</script>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-signature mr-2"></i>
                        Manajemen Tanda Tangan Digital
                    </h3>
                    <a href="{{ route('digital-signatures.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i>
                        Tambah Tanda Tangan
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Dokumen</th>
                                    <th>Jenis</th>
                                    <th>Ditandatangani Oleh</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($signatures as $signature)
                                <tr>
                                    <td>{{ $loop->iteration + ($signatures->currentPage() - 1) * $signatures->perPage() }}</td>
                                    <td>
                                        <strong>{{ $signature->document_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $signature->original_filename }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $signature->document_type ?: 'Umum' }}
                                        </span>
                                    </td>
                                    <td>{{ $signature->signer->name }}</td>
                                    <td>{{ $signature->formatted_signed_date }}</td>
                                    <td>
                                        @if($signature->status === 'active')
                                        <span class="badge badge-success">Aktif</span>
                                        @else
                                        <span class="badge badge-danger">Dicabut</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('digital-signatures.show', $signature) }}"
                                                class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('digital-signatures.download', $signature) }}"
                                                class="btn btn-sm btn-success" title="Unduh">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="{{ route('digital-signatures.certificate', $signature) }}"
                                                class="btn btn-sm btn-warning" title="Sertifikat">
                                                <i class="fas fa-certificate"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Belum ada dokumen yang ditandatangani</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $signatures->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
