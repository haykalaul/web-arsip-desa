@extends('layout.main')

@section('content')
<x-breadcrumb
    :values="[__('menu.transaction.menu'), __('menu.transaction.outgoing_letter')]">
    <a href="{{ route('transaction.outgoing.create') }}" class="btn btn-primary">{{ __('menu.general.create') }}</a>
</x-breadcrumb>

{{-- Filter Section --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('transaction.outgoing.index') }}" class="row g-3">
            {{-- Search Input --}}
            <div class="col-md-6">
                <label for="search" class="form-label">Cari Surat Keluar</label>
                <input type="text"
                    class="form-control"
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari surat...">
            </div>

            {{-- Classification Filter --}}
            <div class="col-md-4">
                <label for="classification_code" class="form-label">Klasifikasi Surat</label>
                <select class="form-select" id="classification_code" name="classification_code">
                    <option value="all" {{ ($classification_code == 'all' || !$classification_code) ? 'selected' : '' }}>
                        Semua Klasifikasi
                    </option>
                    @foreach($classifications as $classification)
                    <option value="{{ $classification->code }}"
                        {{ $classification_code == $classification->code ? 'selected' : '' }}>
                        {{ $classification->code }} - {{ $classification->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Button --}}
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-filter"></i> Saring
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Results Info --}}
@if($search || ($classification_code && $classification_code != 'all'))
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    Menampilkan hasil untuk:
    @if($search)
    <strong>Pencarian:</strong> "{{ $search }}"
    @endif
    @if($classification_code && $classification_code != 'all')
    @php
    $selectedClassification = $classifications->where('code', $classification_code)->first();
    @endphp
    @if($search) | @endif
    <strong>Klasifikasi:</strong> {{ $selectedClassification->code }} - {{ $selectedClassification->name }}
    @endif
</div>
@endif

{{-- Letters List --}}
@forelse($data as $letter)
<x-letter-card :letter="$letter" />
@empty
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    Tidak ada surat yang ditemukan.
</div>
@endforelse

{{-- Pagination --}}
{!! $data->appends([
'search' => $search,
'classification_code' => $classification_code
])->links() !!}
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto submit form when classification changes
        const classificationSelect = document.getElementById('classification_code');
        if (classificationSelect) {
            classificationSelect.addEventListener('change', function() {
                this.form.submit();
            });
        }

        // Enter key submit for search
        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.form.submit();
                }
            });
        }
    });
</script>
@endpush
